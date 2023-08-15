<link rel="stylesheet" type="text/css" href="<?= asset_url('/assets/ext/jquery-fullcalendar/fullcalendar.min.css') ?>">

<script src="<?= asset_url('assets/ext/jquery-fullcalendar/fullcalendar.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.min.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_dashboard_helper.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_dashboard.js') ?>"></script>
<script>
    var GlobalVariables = {
        csrfToken: <?= json_encode($this->security->get_csrf_hash()) ?>,
        today: <?= json_encode($today) ?>,
        appt_date: <?= json_encode($appt_date) ?>,
        prev_date: <?= json_encode($prev_date) ?>,
        next_date: <?= json_encode($next_date) ?>,
        appointments: <?= json_encode($appointments) ?>,
        sel_appt: <?= json_encode($sel_appt) ?>,
        providers: <?= json_encode($providers) ?>,
        services: <?= json_encode($services) ?>,
        inmates: <?= json_encode($inmates) ?>,
        visitors: <?= json_encode($visitors) ?>,
        dateFormat: <?= json_encode($date_format) ?>,
        timeFormat: <?= json_encode($time_format) ?>,
        baseUrl: <?= json_encode($base_url) ?>,
        user: {
            id: <?= $user_id ?>,
            email: <?= json_encode($user_email) ?>,
            timezone: <?= json_encode($timezone) ?>,
            role_slug: <?= json_encode($role_slug) ?>,
            privileges: <?= json_encode($privileges) ?>
        }
    };

    $(function () {
        BackendDashboard.initialize(true);
    });
</script>

<div class="container-fluid backend-page" id="dashboard-page">
    <div class="row" id="dashboard">
        <div id="dashboard-date" class="col col-12 col-md-3">
            <div class="fc-button-group" style="margin-bottom:13px;padding-left:40px;">
                <a href="<?= site_url('backend/dashboard?date='.$prev_date) ?>">
                    <button type="button" class="fc-prev-button fc-button fc-state-default fc-corner-left" aria-label="prev">
                        <span class="fc-icon fc-icon-left-single-arrow"></span>
                    </button>
                </a>
                <span style="font-size=1.5rem;">&nbsp;<?= $appt_date ?>&nbsp;</span>
                <a href="<?= site_url('backend/dashboard?date='.$next_date) ?>">
                    <button type="button" class="fc-next-button fc-button fc-state-default fc-corner-right" aria-label="next">
                        <span class="fc-icon fc-icon-right-single-arrow"></span>
                    </button>
                </a>
                <a href="<?= site_url('backend/dashboard?date='.$today) ?>">
                    <button type="button" class="fc-today-button fc-button fc-state-default fc-corner-left fc-corner-right">Today</button>
                </a>
            </div>

            <h3 class="appointments-title"><?= lang('appointments') ?></h3>
            <div id="appointments" class="card"></div>
        </div>

        <div class="visitor-details-outer col-12 col-md-8">
            <div style="padding-left:50px;padding-bottom:10px;">
                <!--
                <button type="button" id="add-appointment" class="fc-button fc-corner-left fc-corner-right">
                    <span>Add New Appointment</span>
                </button>
                -->
                <br/>
            </div>

            <div id="appointment-details" class="row">
                <div class="col-8 col-md-5">
                    <h3 class="appointments-title">Appointment <?= lang('details') ?></h3>
                    <div id="appointment-message" class="alert" style="display:none;"></div>
                    <div id="new-appointment-form" class="col-md-12" style="display:none;">
                        <div id="add-appt-services">Service:<br/>
                        </div>
                        <div>Date: </div>
                        <input id="add-appt-datepicker" type="text" />
                        <div>Start Time: </div>
                        <input id="add-appt-start-timepicker" type="text" />
                        <div id="add-appt-inmates">Inmate:<br/>
                        </div>
                        <div id="add-appt-providers">Phone:<br/>
                        </div>
                        <br/>
                        <div id="add-appointment-details-buttons">
                            <input id="add-appt-save-button" name="add-appt-save-button" type="button" value="Save" style="margin-right:10px;">
                            <input id="add-appt-cancel-button" name="add-appt-cancel-button" type="button" value="Cancel" class="btn-danger">
                        </div>
                    </div>
                    <div id="appointment-details-row"></div>
                </div>

                <div class="col-8 col-md-7" style="margin-left: 0;">
                    <h3>Visitor <?= lang('details') ?></h3>
                    <div id="appointment-visitor-row"></div>
                    <div id="new-appointment-visitor-form" style="display:none;">
                        <div>Visitors to add to appointment:</div>
                        <div id="add-appt-current-visitor-list"></div>
                        <br/>
                        <div id="add-appt-select-existing-visitor">Select Existing Visitor:<br/>
                        </div>
                        <div class="col-md-7">
                            <input type="button" id="add-appt-add-existing-visitor" value="Add Existing"/>
                        </div>
                        <div>Or Enter New:<br/>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="visitor-1-first-name" class="control-label">
                                        <?= lang('first_name') ?>
                                    </label>
                                    <input type="text" id="visitor-1-first-name" class="form-control" maxlength="100"/>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="visitor-1-last-name" class="control-label">
                                        <?= lang('last_name') ?>
                                    </label>
                                    <input type="text" id="visitor-1-last-name" class="form-control" maxlength="120"/>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <!-- Display birth date box - if age is certain values, require further information -->
                                <div class="form-group">
                                    <label for="visitor-1-birth-date" class="control-label">
                                        <?= lang('birth_date') ?>
                                    </label>
                                    <input type="text" id="visitor-1-birth-date" maxlength="10" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="visitor-1-address" class="control-label">
                                        <?= lang('address') ?>
                                    </label>
                                    <input type="text" id="visitor-1-address" class="form-control" maxlength="120"/>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="visitor-1-city" class="control-label">
                                        <?= lang('city') ?>
                                    </label>
                                    <input type="text" id="visitor-1-city" class="form-control" maxlength="120"/>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group" id="visitor-1-state-box">
                                    <label for="visitor-1-state" class="control-label">
                                        <?= lang('state') ?>
                                    </label>
                                    
                                    <select id="visitor-1-state" class="form-control">
                                        <option value="">Select</option>
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
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="visitor-1-zip-code" class="control-label">
                                        <?= lang('zip_code') ?>
                                    </label>
                                    <input type="text" id="visitor-1-zip-code" class="form-control" maxlength="120"/>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="visitor-1-email" class="control-label">
                                        <?= lang('email') ?>
                                    </label>
                                    <input type="text" id="visitor-1-email" class="form-control" maxlength="120"/>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="visitor-2-phone-number" class="control-label">
                                        <?= lang('phone_number') ?>
                                    </label>
                                    <input type="text" id="visitor-1-phone-number" class="form-control" maxlength="60"/>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <input type="button" id="add-appt-add-new-visitor" value="Add New" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
