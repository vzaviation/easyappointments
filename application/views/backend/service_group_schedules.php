<script src="<?= asset_url('assets/js/backend_service_group_schedules.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_service_group_schedules_helper.js') ?>"></script>
<script src="<?= asset_url('assets/js/working_plan.js') ?>"></script>
<script src="<?= asset_url('assets/js/working_plan_exceptions_modal.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-jeditable/jquery.jeditable.min.js') ?>"></script>
<script>
    var GlobalVariables = {
        csrfToken: <?= json_encode($this->security->get_csrf_hash()) ?>,
        baseUrl: <?= json_encode($base_url) ?>,
        dateFormat: <?= json_encode($date_format) ?>,
        firstWeekday: <?= json_encode($first_weekday); ?>,
        timeFormat: <?= json_encode($time_format) ?>,
        timezones: <?= json_encode($timezones) ?>,
        user: {
            id: <?= $user_id ?>,
            email: <?= json_encode($user_email) ?>,
            timezone: <?= json_encode($timezone) ?>,
            role_slug: <?= json_encode($role_slug) ?>,
            privileges: <?= json_encode($privileges) ?>
        }
    };

    $(function () {
        BackendServiceGroupSchedules.initialize(true);
    });
</script>

<div id="settings-page" class="container-fluid backend-page">
    <ul class="nav nav-pills">
        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('backend/service_groups'); ?>"><?= lang('service_groups_title') ?></a>
            </li>
        <?php endif ?>
        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('backend/service_group_resources'); ?>"><?= lang('service_group_resources_title') ?></a>
            </li>
        <?php endif ?>
        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE): ?>
            <li class="nav-item">
                <a class="nav-link active" href="#service_group_schedules" data-toggle="tab"><?= lang('service_group_schedules_title') ?></a>
            </li>
        <?php endif ?>
        <?php if ($privileges[PRIV_USER_SETTINGS]['view'] == TRUE): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('backend/settings'); ?>"><?= lang('backtomain') ?></a>
            </li>
        <?php endif ?>
    </ul>

    <div class="tab-content">
        <?php $hidden = ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE) ? '' : 'd-none' ?>
        <div id="servicegroups">

        <div class="row">
            <div id="filter-servicegroups" class="filter-records column col-12 col-md-5">
                <form class="mb-4">
                    <div class="input-group">
                        <input type="text" class="key form-control">

                        <div class="input-group-addon">
                            <div>
                                <button class="filter btn btn-outline-secondary" type="submit"
                                        data-tippy-content="<?= lang('filter') ?>">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button class="clear btn btn-outline-secondary" type="button"
                                        data-tippy-content="<?= lang('clear') ?>">
                                    <i class="fas fa-redo-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <h3><?= lang('service_groups') ?></h3>
                <div class="results"></div>
            </div>

            <?php
            // This form message is outside the details view, so that it can be
            // visible when the user has working plan view active.
            ?>

            <div class="form-message alert" style="display:none;"></div>

            <div class="record-details column col-12 col-md-7">
                <div id="hidden-fields" style="display:none">
                    <input type="text" id="service-group-id" value="" />
                    <input type="text" id="service-group-schedule-id" value="" />
                </div>
                <div class="float-md-left mb-4 mr-4">
                    <div class="save-cancel-group btn-group" style="display:none">
                        <button id="save-servicegroupschedule" class="btn btn-primary">
                            <i class="fas fa-check-square mr-2"></i>
                            <?= lang('save') ?>
                        </button>
                        <button id="cancel-servicegroupschedule" class="btn btn-outline-secondary" style="display:none">
                            <i class="fas fa-ban mr-2"></i>
                            <?= lang('cancel') ?>
                        </button>
                    </div>
                    <br/><br/>

                    <div class="working-plan-view" id="working-plan">
                        <h3><?= lang('working_plan') ?></h3>
                        <button id="reset-working-plan" class="btn btn-primary"
                                data-tippy-content="<?= lang('reset_working_plan') ?>" style="display:none">
                            <i class="fas fa-redo-alt mr-2"></i>
                            <?= lang('reset_plan') ?></button>
                        <table class="working-plan table table-striped mt-2">
                            <thead>
                            <tr>
                                <th><?= lang('day') ?></th>
                                <th><?= lang('start') ?></th>
                                <th><?= lang('end') ?></th>
                            </tr>
                            </thead>
                            <tbody><!-- Dynamic Content --></tbody>
                        </table>

                        <br>

                        <h3><?= lang('breaks') ?></h3>

                        <p>
                            <?= lang('add_breaks_during_each_day') ?>
                        </p>

                        <div>
                            <button type="button" class="add-break btn btn-primary">
                                <i class="fas fa-plus-square mr-2"></i>
                                <?= lang('add_break') ?>
                            </button>
                        </div>

                        <br>

                        <table class="breaks table table-striped">
                            <thead>
                            <tr>
                                <th><?= lang('day') ?></th>
                                <th><?= lang('start') ?></th>
                                <th><?= lang('end') ?></th>
                                <th><?= lang('actions') ?></th>
                            </tr>
                            </thead>
                            <tbody><!-- Dynamic Content --></tbody>
                        </table>

                        <br>

                        <h3><?= lang('working_plan_exceptions') ?></h3>

                        <p>
                            <?= lang('add_working_plan_exceptions_during_each_day') ?>
                        </p>

                        <div>
                            <button type="button" class="add-working-plan-exception btn btn-primary mr-2">
                                <i class="fas fa-plus-square"></i>
                                <?= lang('add_working_plan_exception') ?>
                            </button>
                        </div>

                        <br>

                        <table class="working-plan-exceptions table table-striped">
                            <thead>
                            <tr>
                                <th><?= lang('day') ?></th>
                                <th><?= lang('start') ?></th>
                                <th><?= lang('end') ?></th>
                                <th><?= lang('actions') ?></th>
                            </tr>
                            </thead>
                            <tbody><!-- Dynamic Content --></tbody>
                        </table>

                        <?php require __DIR__ . '/working_plan_exceptions_modal.php' ?>
                    </div>
                </div>
            </div>

        </div>
        </div>
    </div>
</div>
