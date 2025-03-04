<script src="<?= asset_url('assets/js/backend_service_group_resources.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_service_group_resources_helper.js') ?>"></script>
<script>
    var GlobalVariables = {
        csrfToken: <?= json_encode($this->security->get_csrf_hash()) ?>,
        baseUrl: <?= json_encode($base_url) ?>,
        dateFormat: <?= json_encode($date_format) ?>,
        firstWeekday: <?= json_encode($first_weekday); ?>,
        timeFormat: <?= json_encode($time_format) ?>,
        userSlug: <?= json_encode($role_slug) ?>,
        timezones: <?= json_encode($timezones) ?>,
        allResources: <?= json_encode($resources) ?>,
        settings: {
            system: <?= json_encode($system_settings) ?>,
            user: <?= json_encode($user_settings) ?>
        },
        user: {
            id: <?= $user_id ?>,
            email: <?= json_encode($user_email) ?>,
            timezone: <?= json_encode($timezone) ?>,
            role_slug: <?= json_encode($role_slug) ?>,
            privileges: <?= json_encode($privileges) ?>
        }
    };

    $(function () {
        BackendServiceGroupResources.initialize(true);
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
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
                <a class="nav-link active" href="#service_group_resources" data-toggle="tab"><?= lang('service_group_resources_title') ?></a>
            </li>
        <?php endif ?>
        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('backend/service_group_schedules'); ?>"><?= lang('service_group_schedules_title') ?></a>
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
        <div class="tab-pane active <?= $hidden ?>" id="service_group_resources">
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

                <div class="select-resources column col-12 col-md-7">
                    <div id="hidden-fields" style="display:none">
                        <input type="text" id="service-group-id" value="" />
                    </div>
                    <div class="row">
                        <div class="column col-6 col-md-4">
                            <br/><br/><br/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="column col-5 col-md-4">
                            <h4><?= lang('available_resources') ?></h4>
                            <span style="font-size:smaller"><?= lang('available_resources_hint') ?></span><br/><br/>
                            <select id="available_resources" name="available_resources" style="width:95%" multiple>
                                <?php
                                foreach ($resources as $resource) {
                                ?>
                                <option value="<?= $resource['resource_id'] ?>"><?= $resource['resource_name'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="column col-1 col-md-1">
                            <br/><br/><br/><br/>
                            <button name="select_resources" id="select_resources">&RightTeeArrow;</button>
                            <br/><br/>
                            <button name="unselect_resources" id="unselect_resources">&LeftTeeArrow;</button>
                        </div>
                        <div class="column col-5 col-md-6">
                            <h4><?= lang('selected_resources') ?></h4>
                            <span style="font-size:smaller"><?= lang('selected_resources_hint_1') ?></span><br/>
                            <span style="font-size:smaller"><?= lang('selected_resources_hint_2') ?></span><br/>
                            <select id="selected_resources" name="selected_resources" style="width:70%" multiple>
                            </select>
                            <br/><br/>
                            <div class="save-cancel-group btn-group" style="display:none;padding-left:25%;">
                                <button id="save-servicegroupresources" class="btn btn-primary">
                                    <i class="fas fa-check-square mr-2"></i>
                                    <?= lang('save') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        </div>
    </div>
</div>

