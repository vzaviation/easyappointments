<link rel="stylesheet" type="text/css" href="<?= asset_url('/assets/ext/jquery-fullcalendar/fullcalendar.min.css') ?>">

<script src="<?= asset_url('assets/ext/jquery-fullcalendar/fullcalendar.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-jeditable/jquery.jeditable.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.min.js') ?>"></script>
<script src="<?= asset_url('assets/js/working_plan_exceptions_modal.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_calendar.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_calendar_default_view.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_calendar_table_view.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_calendar_google_sync.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_calendar_appointments_modal.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_calendar_unavailability_events_modal.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_calendar_api.js') ?>"></script>
<script src="<?= asset_url('assets/js/upload_documents.js') ?>"></script>
<script>
    var GlobalVariables = {
        csrfToken: <?= json_encode($this->security->get_csrf_hash()) ?>,
        availableProviders: <?= json_encode($available_providers) ?>,
        availableServices: <?= json_encode($available_services) ?>,
        baseUrl: <?= json_encode($base_url) ?>,
        dateFormat: <?= json_encode($date_format) ?>,
        timeFormat: <?= json_encode($time_format) ?>,
        firstWeekday: <?= json_encode($first_weekday) ?>,
        editAppointment: <?= json_encode($edit_appointment) ?>,
        customers: <?= json_encode($customers) ?>,
        secretaryProviders: <?= json_encode($secretary_providers) ?>,
        calendarView: <?= json_encode($calendar_view) ?>,
        timezones: <?= json_encode($timezones) ?>,
        user: {
            id: <?= $user_id ?>,
            email: <?= json_encode($user_email) ?>,
            timezone: <?= json_encode($timezone) ?>,
            role_slug: <?= json_encode($role_slug) ?>,
            privileges: <?= json_encode($privileges) ?>,
        }
    };

    $(function () {
        BackendCalendar.initialize(GlobalVariables.calendarView);
    });
</script>

<div class="container-fluid backend-page" id="calendar-page">
    <div class="row" id="calendar-toolbar">
        <div id="calendar-filter" class="col-12 col-sm-5">
            <div class="form-group calendar-filter-items">
                <select id="select-filter-item" class="form-control col"
                        data-tippy-content="<?= lang('select_filter_item_hint') ?>">
                </select>
            </div>
        </div>

        <div id="calendar-actions" class="col-12 col-sm-7">
            <?php if (($role_slug == DB_SLUG_ADMIN || $role_slug == DB_SLUG_PROVIDER)
                && config('google_sync_feature') == TRUE): ?>
                <button id="google-sync" class="btn btn-primary"
                        data-tippy-content="<?= lang('trigger_google_sync_hint') ?>">
                    <i class="fas fa-sync-alt"></i>
                    <span><?= lang('synchronize') ?></span>
                </button>

                <button id="enable-sync" class="btn btn-light" data-toggle="button"
                        data-tippy-content="<?= lang('enable_appointment_sync_hint') ?>">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <span><?= lang('enable_sync') ?></span>
                </button>
            <?php endif ?>

            <?php if ($privileges[PRIV_APPOINTMENTS]['add'] == TRUE): ?>
                <div class="btn-group">
                    <button class="btn btn-light" id="insert-appointment">
                        <i class="fas fa-plus-square mr-2"></i>
                        <?= lang('appointment') ?>
                    </button>

                    <button class="btn btn-light dropdown-toggle" id="insert-dropdown" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>

                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" id="insert-unavailable">
                            <i class="fas fa-plus-square mr-2"></i>
                            <?= lang('unavailable') ?>
                        </a>
                        <a class="dropdown-item" href="#" id="insert-working-plan-exception"
                            <?= $this->session->userdata('role_slug') !== 'admin' ? 'hidden' : '' ?>>
                            <i class="fas fa-plus-square mr-2"></i>
                            <?= lang('working_plan_exception') ?>
                        </a>
                    </div>
                </div>
            <?php endif ?>

            <button id="reload-appointments" class="btn btn-light"
                    data-tippy-content="<?= lang('reload_appointments_hint') ?>">
                <i class="fas fa-sync-alt"></i>
            </button>

            <?php if ($calendar_view === 'default'): ?>
                <a class="btn btn-light" href="<?= site_url('backend?view=table') ?>"
                   data-tippy-content="<?= lang('table') ?>">
                    <i class="fas fa-table"></i>
                </a>
            <?php endif ?>

            <?php if ($calendar_view === 'table'): ?>
                <a class="btn btn-light" href="<?= site_url('backend?view=default') ?>"
                   data-tippy-content="<?= lang('default') ?>">
                    <i class="fas fa-calendar-alt"></i>
                </a>
            <?php endif ?>
        </div>
    </div>

    <div id="calendar"><!-- Dynamically Generated Content --></div>
</div>


<!-- MANAGE APPOINTMENT MODAL -->

<div id="manage-appointment" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= lang('edit_appointment_title') ?></h3>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="modal-message alert d-none"></div>

                <form>
                    <fieldset>
                        <legend><?= lang('appointment_details_title') ?></legend>

                        <input id="appointment-id" type="hidden">

                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label for="select-service" class="control-label">
                                        <?= lang('service') ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="select-service" class="required form-control">
                                        <?php
                                        // Group services by category, only if there is at least one service
                                        // with a parent category.
                                        $has_category = FALSE;
                                        foreach ($available_services as $service)
                                        {
                                            if ($service['category_id'] != NULL)
                                            {
                                                $has_category = TRUE;
                                                break;
                                            }
                                        }

                                        if ($has_category)
                                        {
                                            $grouped_services = [];

                                            foreach ($available_services as $service)
                                            {
                                                if ($service['category_id'] != NULL)
                                                {
                                                    if ( ! isset($grouped_services[$service['category_name']]))
                                                    {
                                                        $grouped_services[$service['category_name']] = [];
                                                    }

                                                    $grouped_services[$service['category_name']][] = $service;
                                                }
                                            }

                                            // We need the uncategorized services at the end of the list so we will use
                                            // another iteration only for the uncategorized services.
                                            $grouped_services['uncategorized'] = [];
                                            foreach ($available_services as $service)
                                            {
                                                if ($service['category_id'] == NULL)
                                                {
                                                    $grouped_services['uncategorized'][] = $service;
                                                }
                                            }

                                            foreach ($grouped_services as $key => $group)
                                            {
                                                $group_label = ($key != 'uncategorized')
                                                    ? $group[0]['category_name'] : 'Uncategorized';

                                                if (count($group) > 0)
                                                {
                                                    echo '<optgroup label="' . $group_label . '">';
                                                    foreach ($group as $service)
                                                    {
                                                        echo '<option value="' . $service['id'] . '">'
                                                            . $service['name'] . '</option>';
                                                    }
                                                    echo '</optgroup>';
                                                }
                                            }
                                        }
                                        else
                                        {
                                            foreach ($available_services as $service)
                                            {
                                                echo '<option value="' . $service['id'] . '">'
                                                    . $service['name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="select-provider" class="control-label">
                                        <?= lang('provider') ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="select-provider" class="required form-control"></select>
                                </div>

                                <div class="form-group">
                                    <label for="appointment-location" class="control-label">
                                        <?= lang('location') ?>
                                    </label>
                                    <input id="appointment-location" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="appointment-notes" class="control-label"><?= lang('notes') ?></label>
                                    <textarea id="appointment-notes" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                
                                    
                                <div class="form-group">
                                    <label for="inmate_flag" class="control-label">
                                        <?= lang('inmate_flag') ?>
                                    </label>
                                    <input id="inmate-flag" class="form-control">
                                </div>
                                    
                                <div class="form-group">
                                    <label for="inmate_name" class="control-label">
                                        <?= lang('inmate_name') ?>
                                    </label>
                                    <input id="inmate-name" class="form-control">
                                </div>
                                    
                                <div class="form-group">    
                                    <label for="start-datetime"
                                           class="control-label"><?= lang('start_date_time') ?></label>
                                    <input id="start-datetime" class="required form-control">
                                </div>

                                <div class="form-group">
                                    <label for="end-datetime" class="control-label"><?= lang('end_date_time') ?></label>
                                    <input id="end-datetime" class="required form-control">
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?= lang('timezone') ?></label>

                                    <ul>
                                        <li>
                                            <?= lang('provider') ?>:
                                            <span class="provider-timezone">
                                                -
                                            </span>
                                        </li>
                                        <li>
                                            <?= lang('current_user') ?>:
                                            <span>
                                                <?= $timezones[$timezone] ?>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <br>

                    <fieldset>
                        <legend>
                            <?= lang('customer_details_title') ?>
                            <button id="new-customer" class="btn btn-outline-secondary btn-sm" type="button"
                                    data-tippy-content="<?= lang('clear_fields_add_existing_customer_hint') ?>">
                                <i class="fas fa-plus-square mr-2"></i>
                                <?= lang('new') ?>
                            </button>
                            <button id="select-customer" class="btn btn-outline-secondary btn-sm" type="button"
                                    data-tippy-content="<?= lang('pick_existing_customer_hint') ?>">
                                <i class="fas fa-hand-pointer mr-2"></i>
                                <span>
                                    <?= lang('select') ?>
                                </span>
                            </button>
                            <input id="filter-existing-customers"
                                   placeholder="<?= lang('type_to_filter_customers') ?>"
                                   style="display: none;" class="input-sm form-control">
                            <div id="existing-customers-list" style="display: none;"></div>
                        </legend>

                        <input id="customer-id" type="hidden">

                        <div class="row">
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label for="first-name" class="control-label">
                                        <?= lang('first_name') ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input id="first-name" class="required form-control">
                                </div>

                                <div class="form-group">
                                    <label for="last-name" class="control-label">
                                        <?= lang('last_name') ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input id="last-name" class="required form-control">
                                </div>

                                <div class="form-group">
                                    <label for="email" class="control-label">
                                        <?= lang('email') ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input id="email" class="required form-control">
                                </div>

                                <div class="form-group">
                                    <label for="phone-number" class="control-label">
                                        <?= lang('phone_number') ?>
                                        <?php if ($require_phone_number === '1'): ?>
                                            <span class="text-danger">*</span>
                                        <?php endif ?>
                                    </label>
                                    <input id="phone-number"
                                           class="form-control <?= $require_phone_number === '1' ? 'required' : '' ?>">
                                </div>
                                
                                <div class="form-group">
                                <label for="visitor_1_dl_state" class="control-label">
                                    <?= lang('visitor_1_dl_state') ?>
                                </label>
                                <select id="visitor_1_dl_state" class="form-control">
                                            <option value="Select">Select</option>
                                            <option value="Alabama">Alabama</option>
                                            <option value="Alaska">Alaska</option>
                                            <option value="Arizona">Arizona</option>
                                            <option value="Arkansas">Arkansas</option>
                                            <option value="California">California</option>
                                            <option value="Colorado">Colorado</option>
                                            <option value="Connecticut">Connecticut</option>
                                            <option value="Delaware">Delaware</option>
                                            <option value="Florida">Florida</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Hawaii">Hawaii</option>
                                            <option value="Idaho">Idaho</option>
                                            <option value="Illinois">Illinois</option>
                                            <option value="Indiana">Indiana</option>
                                            <option value="Iowa">Iowa</option>
                                            <option value="Kansas">Kansas</option>
                                            <option value="Kentucky">Kentucky</option>
                                            <option value="Louisiana">Louisiana</option>
                                            <option value="Maine">Maine</option>
                                            <option value="Maryland">Maryland</option>
                                            <option value="Massachusetts">Massachusetts</option>
                                            <option value="Michigan">Michigan</option>
                                            <option value="Minnesota">Minnesota</option>
                                            <option value="Mississippi">Mississippi</option>
                                            <option value="Missouri">Missouri</option>
                                            <option value="Montana">Montana</option>
                                            <option value="Nebraska">Nebraska</option>
                                            <option value="Nevada">Nevada</option>
                                            <option value="New Hampshire">New Hampshire</option>
                                            <option value="New Jersey">New Jersey</option>
                                            <option value="New Mexico">New Mexico</option>
                                            <option value="New York">New York</option>
                                            <option value="North Carolina">North Carolina</option>
                                            <option value="North Dakota">North Dakota</option>
                                            <option value="Ohio">Ohio</option>
                                            <option value="Oklahoma">Oklahoma</option>
                                            <option value="Oregon">Oregon</option>
                                            <option value="Pennsylvania">Pennsylvania</option>
                                            <option value="Rhode Island">Rhode Island</option>
                                            <option value="South Carolina">South Carolina</option>
                                            <option value="South Dakota">South Dakota</option>
                                            <option value="Tennessee">Tennessee</option>
                                            <option value="Texas">Texas</option>
                                            <option value="Utah">Utah</option>
                                            <option value="Vermont">Vermont</option>
                                            <option value="Virginia">Virginia</option>
                                            <option value="Washington">Washington</option>
                                            <option value="West Virginia">West Virginia</option>
                                            <option value="Wisconsin">Wisconsin</option>
                                            <option value="Wyoming">Wyoming</option>
                                        </select>
                            </div> 
                             
                            <div class="form-group">
                                <label for="notes" class="control-label">
                                    <?= lang('notes') ?>
                                </label>
                                <textarea id="notes" maxlength="500" class="form-control" rows="1"></textarea>
                            </div>       
                            
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label for="address" class="control-label">
                                        <?= lang('address') ?>
                                    </label>
                                    <input id="address" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="city" class="control-label">
                                        <?= lang('city') ?>
                                    </label>
                                    <input id="city" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="zip-code" class="control-label">
                                        <?= lang('zip_code') ?>
                                    </label>
                                    <input id="zip-code" class="form-control">
                                </div>
                                
                            <form>
                                <div class="form-group">
                                    <label for="visitor_1_dl" class="control-label">
                                        <?= lang('visitor_1_dl') ?>
                                    </label> <span><a id="visitor-1-dl-link" href="" target="_blank">View Doc</a></span>
				    <input type="file" id="visitor-1-dl" class="form-control" onchange="uploadDocument('visitor-1-dl')" maxlength="120"/>
					<input type="hidden" id="visitor-1-dl-file-name" value="" />
                                </div>
                            </form>
                            
                            <div class="form-group">
                                <label for="visitor_1_dl_number" class="control-label">
                                    <?= lang('visitor_1_dl_number') ?>
                                </label>
                                <input type="text" id="visitor-1-dl-number" class="form-control" maxlength="500"/>
                            </div>
                                                                
                            </div>
                        </div>    
                         <h2 class="frame-title"><?= lang('additional_customer_information') ?></h2> 
                
                    <div class="row frame-content">
                        <div class="col-12 col-md-6">
                
                            <div class="form-group">
                                <label for="visitor_2_name" class="control-label">
                                    <?= lang('visitor_2_name') ?>
                                </label>
                                <input type="text" id="visitor-2-name" class="form-control" maxlength="500"/>
                            </div>
                            
                             <div class="form-group">
                                <label for="visitor_2_dl_number" class="control-label">
                                    <?= lang('visitor_2_dl_number') ?>
                                </label>
                                <input type="text" id="visitor-2-dl-number" class="form-control" maxlength="500"/>
                            </div>        
                            
                            <div class="form-group">
                                <label for="visitor_3_name" class="control-label">
                                    <?= lang('visitor_3_name') ?>
                                </label>
                                <input type="text" id="visitor-3-name" class="form-control" maxlength="500"/>
                            </div>
                            
                              <div class="form-group">
                                <label for="visitor_3_dl_number" class="control-label">
                                    <?= lang('visitor_3_dl_number') ?>
                                </label>
                                <input type="text" id="visitor-3-dl-number" class="required form-control" maxlength="500"/>
                            </div>
                            

                            
                            <div class="form-group">
                                <label for="visitor_4_name" class="control-label">
                                    <?= lang('visitor_4_name') ?>
                                </label>
                                <input type="text" id="visitor-4-name" class="form-control" maxlength="500"/>
                            </div>
                            
                              <div class="form-group">
                                <label for="visitor_4_dl_number" class="control-label">
                                    <?= lang('visitor_4_dl_number') ?>
                                </label>
                                <input type="text" id="visitor-4-dl-number" class="form-control" maxlength="500"/>
                            </div>
                        
                        </div>
                
                        <div class="col-12 col-md-6">
                
                            <form>
                                <div class="form-group">
                                <label for="visitor_2_dl" class="control-label">
                                    <?= lang('visitor_2_dl') ?>
                                </label><span><a id="visitor-2-dl-link" href="" target="_blank">View Doc</a></span>
				    <input type="file" onchange="uploadDocument('visitor-2-dl')" class="form-control" id="visitor-2-dl">
					<input type="hidden" id="visitor-2-dl-file-name" value="" />
                                </div>
                            </form>   
                
                            <div class="form-group">
                                <label for="visitor_2_dl_state" class="control-label">
                                    <?= lang('visitor_2_dl_state') ?>
                                </label>
                                <select id="visitor-2-dl-state" class="form-control">
                                            <option value="Select">Select</option>
                                            <option value="Alabama">Alabama</option>
                                            <option value="Alaska">Alaska</option>
                                            <option value="Arizona">Arizona</option>
                                            <option value="Arkansas">Arkansas</option>
                                            <option value="California">California</option>
                                            <option value="Colorado">Colorado</option>
                                            <option value="Connecticut">Connecticut</option>
                                            <option value="Delaware">Delaware</option>
                                            <option value="Florida">Florida</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Hawaii">Hawaii</option>
                                            <option value="Idaho">Idaho</option>
                                            <option value="Illinois">Illinois</option>
                                            <option value="Indiana">Indiana</option>
                                            <option value="Iowa">Iowa</option>
                                            <option value="Kansas">Kansas</option>
                                            <option value="Kentucky">Kentucky</option>
                                            <option value="Louisiana">Louisiana</option>
                                            <option value="Maine">Maine</option>
                                            <option value="Maryland">Maryland</option>
                                            <option value="Massachusetts">Massachusetts</option>
                                            <option value="Michigan">Michigan</option>
                                            <option value="Minnesota">Minnesota</option>
                                            <option value="Mississippi">Mississippi</option>
                                            <option value="Missouri">Missouri</option>
                                            <option value="Montana">Montana</option>
                                            <option value="Nebraska">Nebraska</option>
                                            <option value="Nevada">Nevada</option>
                                            <option value="New Hampshire">New Hampshire</option>
                                            <option value="New Jersey">New Jersey</option>
                                            <option value="New Mexico">New Mexico</option>
                                            <option value="New York">New York</option>
                                            <option value="North Carolina">North Carolina</option>
                                            <option value="North Dakota">North Dakota</option>
                                            <option value="Ohio">Ohio</option>
                                            <option value="Oklahoma">Oklahoma</option>
                                            <option value="Oregon">Oregon</option>
                                            <option value="Pennsylvania">Pennsylvania</option>
                                            <option value="Rhode Island">Rhode Island</option>
                                            <option value="South Carolina">South Carolina</option>
                                            <option value="South Dakota">South Dakota</option>
                                            <option value="Tennessee">Tennessee</option>
                                            <option value="Texas">Texas</option>
                                            <option value="Utah">Utah</option>
                                            <option value="Vermont">Vermont</option>
                                            <option value="Virginia">Virginia</option>
                                            <option value="Washington">Washington</option>
                                            <option value="West Virginia">West Virginia</option>
                                            <option value="Wisconsin">Wisconsin</option>
                                            <option value="Wyoming">Wyoming</option>
                                        </select>
                            </div>
                            
                            <form>
                                <div class="form-group">
                                <label for="visitor_3_dl" class="control-label">
                                    <?= lang('visitor_3_dl') ?>
                                </label> <span><a id="visitor-3-dl-link" href="" target="_blank">View Doc</a></span>
				    <input type="file" class="form-control" id="visitor-3-dl" onchange="uploadDocument('visitor-3-dl')">
				<input type="hidden" id="visitor-3-dl-file-name" value="" />
                                </div>
                            </form> 
                            
                            <div class="form-group">
                                <label for="visitor_3_dl_state" class="control-label">
                                    <?= lang('visitor_3_dl_state') ?>
                                </label>
                                <select id="visitor-3-dl-state" class="form-control">
                                            <option value="Select">Select</option>
                                            <option value="Alabama">Alabama</option>
                                            <option value="Alaska">Alaska</option>
                                            <option value="Arizona">Arizona</option>
                                            <option value="Arkansas">Arkansas</option>
                                            <option value="California">California</option>
                                            <option value="Colorado">Colorado</option>
                                            <option value="Connecticut">Connecticut</option>
                                            <option value="Delaware">Delaware</option>
                                            <option value="Florida">Florida</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Hawaii">Hawaii</option>
                                            <option value="Idaho">Idaho</option>
                                            <option value="Illinois">Illinois</option>
                                            <option value="Indiana">Indiana</option>
                                            <option value="Iowa">Iowa</option>
                                            <option value="Kansas">Kansas</option>
                                            <option value="Kentucky">Kentucky</option>
                                            <option value="Louisiana">Louisiana</option>
                                            <option value="Maine">Maine</option>
                                            <option value="Maryland">Maryland</option>
                                            <option value="Massachusetts">Massachusetts</option>
                                            <option value="Michigan">Michigan</option>
                                            <option value="Minnesota">Minnesota</option>
                                            <option value="Mississippi">Mississippi</option>
                                            <option value="Missouri">Missouri</option>
                                            <option value="Montana">Montana</option>
                                            <option value="Nebraska">Nebraska</option>
                                            <option value="Nevada">Nevada</option>
                                            <option value="New Hampshire">New Hampshire</option>
                                            <option value="New Jersey">New Jersey</option>
                                            <option value="New Mexico">New Mexico</option>
                                            <option value="New York">New York</option>
                                            <option value="North Carolina">North Carolina</option>
                                            <option value="North Dakota">North Dakota</option>
                                            <option value="Ohio">Ohio</option>
                                            <option value="Oklahoma">Oklahoma</option>
                                            <option value="Oregon">Oregon</option>
                                            <option value="Pennsylvania">Pennsylvania</option>
                                            <option value="Rhode Island">Rhode Island</option>
                                            <option value="South Carolina">South Carolina</option>
                                            <option value="South Dakota">South Dakota</option>
                                            <option value="Tennessee">Tennessee</option>
                                            <option value="Texas">Texas</option>
                                            <option value="Utah">Utah</option>
                                            <option value="Vermont">Vermont</option>
                                            <option value="Virginia">Virginia</option>
                                            <option value="Washington">Washington</option>
                                            <option value="West Virginia">West Virginia</option>
                                            <option value="Wisconsin">Wisconsin</option>
                                            <option value="Wyoming">Wyoming</option>
                                        </select>
                            </div>
                            
                            <form>
                                <div class="form-group">
                                <label for="visitor_4_dl" class="control-label">
                                    <?= lang('visitor_4_dl') ?>
                                </label> <span><a id="visitor-4-dl-link" href="" target="_blank">View Doc</a></span>
				    <input type="file" class="form-control" id="visitor-4-dl" onchange="uploadDocument('visitor-4-dl')">
					<input type="hidden" id="visitor-4-dl-file-name" value="" />
                                </div>
                            </form>
                            
                            <div class="form-group">
                                <label for="visitor_4_dl_state" class="control-label">
                                    <?= lang('visitor_4_dl_state') ?>
                                </label>
                                <select id="visitor-4-dl-state" class="form-control">
                                            <option value="Select">Select</option>
                                            <option value="Alabama">Alabama</option>
                                            <option value="Alaska">Alaska</option>
                                            <option value="Arizona">Arizona</option>
                                            <option value="Arkansas">Arkansas</option>
                                            <option value="California">California</option>
                                            <option value="Colorado">Colorado</option>
                                            <option value="Connecticut">Connecticut</option>
                                            <option value="Delaware">Delaware</option>
                                            <option value="Florida">Florida</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Hawaii">Hawaii</option>
                                            <option value="Idaho">Idaho</option>
                                            <option value="Illinois">Illinois</option>
                                            <option value="Indiana">Indiana</option>
                                            <option value="Iowa">Iowa</option>
                                            <option value="Kansas">Kansas</option>
                                            <option value="Kentucky">Kentucky</option>
                                            <option value="Louisiana">Louisiana</option>
                                            <option value="Maine">Maine</option>
                                            <option value="Maryland">Maryland</option>
                                            <option value="Massachusetts">Massachusetts</option>
                                            <option value="Michigan">Michigan</option>
                                            <option value="Minnesota">Minnesota</option>
                                            <option value="Mississippi">Mississippi</option>
                                            <option value="Missouri">Missouri</option>
                                            <option value="Montana">Montana</option>
                                            <option value="Nebraska">Nebraska</option>
                                            <option value="Nevada">Nevada</option>
                                            <option value="New Hampshire">New Hampshire</option>
                                            <option value="New Jersey">New Jersey</option>
                                            <option value="New Mexico">New Mexico</option>
                                            <option value="New York">New York</option>
                                            <option value="North Carolina">North Carolina</option>
                                            <option value="North Dakota">North Dakota</option>
                                            <option value="Ohio">Ohio</option>
                                            <option value="Oklahoma">Oklahoma</option>
                                            <option value="Oregon">Oregon</option>
                                            <option value="Pennsylvania">Pennsylvania</option>
                                            <option value="Rhode Island">Rhode Island</option>
                                            <option value="South Carolina">South Carolina</option>
                                            <option value="South Dakota">South Dakota</option>
                                            <option value="Tennessee">Tennessee</option>
                                            <option value="Texas">Texas</option>
                                            <option value="Utah">Utah</option>
                                            <option value="Vermont">Vermont</option>
                                            <option value="Virginia">Virginia</option>
                                            <option value="Washington">Washington</option>
                                            <option value="West Virginia">West Virginia</option>
                                            <option value="Wisconsin">Wisconsin</option>
                                            <option value="Wyoming">Wyoming</option>
                                        </select>
                            </div>            
                           
                        </div>   
        
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-ban"></i>
                    <?= lang('cancel') ?>
                </button>
                <button id="save-appointment" class="btn btn-primary">
                    <i class="fas fa-check-square mr-2"></i>
                    <?= lang('save') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MANAGE UNAVAILABLE MODAL -->

<div id="manage-unavailable" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= lang('new_unavailable_title') ?></h3>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-message alert d-none"></div>

                <form>
                    <fieldset>
                        <input id="unavailable-id" type="hidden">

                        <div class="form-group">
                            <label for="unavailable-provider" class="control-label">
                                <?= lang('provider') ?>
                            </label>
                            <select id="unavailable-provider" class="form-control"></select>
                        </div>

                        <div class="form-group">
                            <label for="unavailable-start" class="control-label">
                                <?= lang('start') ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input id="unavailable-start" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="unavailable-end" class="control-label">
                                <?= lang('end') ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input id="unavailable-end" class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?= lang('timezone') ?></label>

                            <ul>
                                <li>
                                    <?= lang('provider') ?>:
                                    <span class="provider-timezone">
                                        -
                                    </span>
                                </li>
                                <li>
                                    <?= lang('current_user') ?>:
                                    <span>
                                        <?= $timezones[$timezone] ?>
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="unavailable-notes" class="control-label">
                                <?= lang('notes') ?>
                            </label>
                            <textarea id="unavailable-notes" rows="3" class="form-control"></textarea>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-ban"></i>
                    <?= lang('cancel') ?>
                </button>
                <button id="save-unavailable" class="btn btn-primary">
                    <i class="fas fa-check-square mr-2"></i>
                    <?= lang('save') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SELECT GOOGLE CALENDAR MODAL -->

<div id="select-google-calendar" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= lang('select_google_calendar') ?></h3>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="google-calendar" class="control-label">
                        <?= lang('select_google_calendar_prompt') ?>
                    </label>
                    <select id="google-calendar" class="form-control"></select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-ban mr-2"></i>
                    <?= lang('cancel') ?>
                </button>
                <button id="select-calendar" class="btn btn-primary">
                    <i class="fas fa-check-square mr-2"></i>
                    <?= lang('select') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- WORKING PLAN EXCEPTIONS MODAL -->

<?php require __DIR__ . '/working_plan_exceptions_modal.php' ?>

