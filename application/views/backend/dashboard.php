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
            <div class="fc-button-group" style="margin-bottom:6px;padding-left:40px;">
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
            <div style="padding-bottom:10px;">
                <br/>
            </div>

            <div id="appointment-details" class="row">
                <div class="col-8 col-md-5">
                    <h3 class="appointments-title">Appointment <?= lang('details') ?></h3>
                    <div id="appointment-message" class="alert" style="display:none;"></div>
                    <div id="appointment-details-row"></div>
                </div>

                <div class="col-8 col-md-7">
                    <div class="col-8 col-md-12" style="margin-left: 0;">
                        <h3>Visitor <?= lang('details') ?></h3>
                        <div id="appointment-visitor-row"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
