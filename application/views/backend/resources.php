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
                <a class="nav-link" href="#resources" data-toggle="tab"><?= lang('resources') ?></a>
            </li>
        <?php endif ?>
        <?php if ($privileges[PRIV_USER_SETTINGS]['view'] == TRUE): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('backend/settings'); ?>"><?= lang('general') ?></a>
            </li>
        <?php endif ?>
        <?php if ($privileges[PRIV_USER_SETTINGS]['view'] == TRUE): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('backend/settings'); ?>"><?= lang('current_user') ?></a>
            </li>
        <?php endif ?>
    </ul>

    <div class="tab-content">

        <!-- RESOURCE MANAGEMENT TAB -->

        <?php $hidden = ($privileges[PRIV_SYSTEM_SETTINGS]['view'] == TRUE) ? '' : 'd-none' ?>
        <div class="tab-pane active <?= $hidden ?>" id="resources">
                <fieldset>
                    <legend class="border-bottom mb-4">
                        <?= lang('resources') ?>
                    </legend>

                    <div class="row">
                        <div class="col-12 col-sm-7 resources-wrapper">
                            <h4><?= lang('resources_title') ?></h4>
                            <span class="form-text text-muted mb-4">
                                <?= lang('resources_hint') ?>
                            </span>
                            <form action="<?= site_url('backend/resources'); ?>" method="post" id="resource_update_form">
                                <input type="hidden" id="action" name="action" value="" />
                                <input type="hidden" id="resid" name="resid" value="" />
                            <table class="resources table table-striped">
                                <thead>
                                <tr>
                                    <th><?= lang('resource_name') ?></th>
                                    <th><?= lang('resource_description') ?></th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" size="20" name="resource_name_0" value="" />
                                        </td>
                                        <td>
                                            <input type="text" size="50" name="resource_description_0" value="" />
                                        </td>
                                        <td>
                                            <div style="width:150px;">
                                                <button type="submit" id="add_button" name="add" class="btn btn-primary btn-sm mb-2">Add Resource</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                foreach (@$resources as $resource) {
                                ?>
                                    <tr>
                                        <td>
                                            <input type="text" size="20" name="<?= 'resource_name_'. @$resource['resource_id'] ?>" value="<?= @$resource['resource_name'] ?>" />
                                        </td>
                                        <td>
                                            <input type="text" size="50" name="<?= 'description_'. @$resource['resource_id'] ?>" value="<?= @$resource['description'] ?>" />
                                        </td>
                                        <td>
                                            <button type="submit" id="update_button" name="update" class="btn btn-secondary btn-sm mb-2" data-id="<?= @$resource['resource_id'] ?>">Update</button>
                                            <button type="submit" id="delete_button" name="delete" class="btn btn-warning btn-sm mb-2" data-id="<?= @$resource['resource_id'] ?>">Delete</button>
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
