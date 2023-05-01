<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.min.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_visitors_helper.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_visitors.js') ?>"></script>
<script>
    var GlobalVariables = {
        csrfToken: <?= json_encode($this->security->get_csrf_hash()) ?>,
        availableProviders: <?= json_encode($available_providers) ?>,
        availableServices: <?= json_encode($available_services) ?>,
        secretaryProviders: <?= json_encode($secretary_providers) ?>,
        dateFormat: <?= json_encode($date_format) ?>,
        timeFormat: <?= json_encode($time_format) ?>,
        baseUrl: <?= json_encode($base_url) ?>,
        visitors: <?= json_encode($visitors) ?>,
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
        BackendVisitors.initialize(true);
    });
</script>

<div class="container-fluid backend-page" id="visitors-page">
    <div class="row" id="visitors">
        <div id="filter-visitors" class="filter-records col col-12 col-md-4">
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

            <h3><?= lang('visitors') ?></h3>
            <div class="results"></div>
        </div>

        <div class="visitor-details-outer col-12 col-md-7">
            <div class="btn-toolbar mb-4">
                <div id="add-edit-delete-group" class="btn-group"><span style="height:37px;"></span></div>
            </div>

            <div id="visitor-details" class="row">
                <div class="col-8 col-md-5">
                    <h3><?= lang('appointments') ?></h3>
                    <div id="visitor-appointments"></div>
                    <div id="appointment-details" class="card bg-light border-light d-none"></div>
                </div>

                <div class="col-8 col-md-7">
                    <form id="visitor-update-form" action="#" method="POST">
                    <div id="form-message" class="alert" style="display:none;"></div>
                    <div class="col-8 col-md-12" style="margin-left: 0;">
                        <h3>Visitor <?= lang('details') ?></h3>

                        <div id="visitor-details-row"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
