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

<div class="container-fluid backend-page" id="visitors-page">
    <div class="row" id="visitors-date">
        <div class="col-8 col-md-6" style="text-align: center">
<!--            <div class="dashboard-button-group">
                <button type="button" class="dashboard-prev-button dashboard-button" aria-label="prev">
                    <span class="dashboard-icon dashboard-icon-left-single-arrow"></span>
                </button>
                <button type="button" class="fc-next-button fc-button fc-state-default fc-corner-right" aria-label="next">
                    <span class="fc-icon fc-icon-right-single-arrow"></span>
                </button>
            </div> -->

            <a href="<?= site_url('backend/dashboard?date='.$prev_date) ?>">&nbsp;&lt;&nbsp;</a>
            <span style="font-size=1.5rem;">&nbsp;<?= $appt_date ?>&nbsp;</span>
            <a href="<?= site_url('backend/dashboard?date='.$next_date) ?>">&nbsp;&gt;&nbsp;</a>
        </div>
    </div>
    <div class="row" id="appointment-visitors">
        <div class="col-8 col-md-3">
            <h3 class="appointments-title"><?= lang('appointments') ?></h3>
            <div id="appointments" class="card"></div>
        </div>

        <div class="appointment-visitors-rows col-9 col-md-9">
            <form id="appointment-visitor-form" action="#" method="POST">
            <div id="form-message" class="alert" style="display:none;"></div>
            <div class="col-8 col-md-12" style="margin-left: 0;">
                <h3>Visitor <?= lang('details') ?></h3>

                <div id="appointment-visitor-row"></div>
            </div>
        </div>
    </div>
</div>
