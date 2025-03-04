<script src="<?= asset_url('assets/js/backend_settings_system.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_settings_user.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_settings.js') ?>"></script>
<script src="<?= asset_url('assets/js/working_plan.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-jeditable/jquery.jeditable.min.js') ?>"></script>
<script>
    var GlobalVariables = {
        csrfToken: <?= json_encode($this->security->get_csrf_hash()) ?>,
        baseUrl: <?= json_encode($base_url) ?>,
        dateFormat: <?= json_encode($date_format) ?>,
        firstWeekday: <?= json_encode($first_weekday); ?>,
        timeFormat: <?= json_encode($time_format) ?>,
        userSlug: <?= json_encode($role_slug) ?>,
        timezones: <?= json_encode($timezones) ?>,
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
        BackendSettings.initialize(true);
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $("#action").val('');
        $("#resid").val('');

        $("button[name='add']").on('click', function () {
            $("#action").val('add');
            return true;
        });

        $("button[name='update']").on('click', function () {
            const resId = $(this).data('id');
            $("#action").val('update');
            $("#resid").val(resId);
            return true;
        });

        $("button[name='delete']").on('click', function () {
            const resId = $(this).data('id');
            if (confirm("Delete this item - are you sure?")) {
                $("#action").val('delete');
                $("#resid").val(resId);
                return true;
            } else {
                return false;
            }
        });
    });
</script>

<div id="settings-page" class="container-fluid backend-page">
    <ul class="nav nav-pills">
        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE): ?>
            <li class="nav-item">
                <a class="nav-link active" href="#service_groups" data-toggle="tab"><?= lang('service_groups_title') ?></a>
            </li>
        <?php endif ?>
        <?php if ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('backend/service_group_resources'); ?>"><?= lang('service_group_resources_title') ?></a>
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
        <div class="tab-pane active <?= $hidden ?>" id="service_groups">
                <fieldset>
                    <legend class="border-bottom mb-4">
                        <?= lang('service_groups_title') ?>
                    </legend>

                    <div class="row">
                        <div class="col-12 col-sm-7 resources-wrapper">
                            <h4><?= lang('service_groups') ?></h4>
                            <span class="form-text text-muted mb-4">
                                <?= lang('service_groups_hint') ?>
                            </span>
                            <form action="<?= site_url('backend/service_groups'); ?>" method="post" id="service_group_update_form">
                                <input type="hidden" id="action" name="action" value="" />
                                <input type="hidden" id="resid" name="resid" value="" />
                            <table class="cell-range table table-striped">
                                <thead>
                                <tr>
                                    <th><?= lang('service_group_name') ?></th>
                                    <th><?= lang('description') ?></th>
                                    <th><?= lang('service_group_service') ?></th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" size="20" name="group_name_0" value="" />
                                        </td>
                                        <td>
                                            <input type="text" size="35" name="group_description_0" value="" />
                                        </td>
                                        <td>
                                            <select name="service_id_0">
                                                <?php
                                                foreach($services as $service) {
                                                ?>
                                                    <option value="<?= $service['id'] ?>"><?= $service['name'] ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <div style="width:150px;">
                                                <button type="submit" id="add_button" name="add" class="btn btn-primary btn-sm mb-2">Add <?= lang('service_group') ?></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                foreach ($service_groups as $service_group) {
                                ?>
                                    <tr>
                                        <td>
                                            <input type="text" size="20" name="<?= 'group_name_'. $service_group['service_group_id'] ?>" value="<?= $service_group['group_name'] ?>" />
                                        </td>
                                        <td>
                                            <input type="text" size="35" name="<?= 'group_description_'. $service_group['service_group_id'] ?>" value="<?= @$service_group['group_description'] ?>" />
                                        </td>
                                        <td>
                                            <select name="<?= 'service_id_'. $service_group['service_group_id'] ?>">
                                                <?php
                                                foreach($services as $service) {
                                                    $selected = ($service_group['service_id'] == $service['id']) ? "selected" : "";
                                                ?>
                                                    <option value="<?= $service['id'] ?>" <?= $selected ?> ><?= $service['name'] ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="submit" id="update_button" name="update" class="btn btn-secondary btn-sm mb-2" data-id="<?= @$service_group['service_group_id'] ?>">Update</button>
                                            <button type="submit" id="delete_button" name="delete" class="btn btn-warning btn-sm mb-2" data-id="<?= @$service_group['service_group_id'] ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                            </form>

                        </div>
                    </div>
                </fieldset>
            </form>
        </div>

    </div>
</div>
