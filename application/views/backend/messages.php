<link rel="stylesheet" type="text/css" href="<?= asset_url('/assets/ext/jquery-fullcalendar/fullcalendar.min.css') ?>">

<script src="<?= asset_url('assets/ext/jquery-fullcalendar/fullcalendar.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.min.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_messages_helper.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_messages.js') ?>"></script>
<script>
    var GlobalVariables = {
        csrfToken: <?= json_encode($this->security->get_csrf_hash()) ?>,
        today: <?= json_encode($today) ?>,
        messages: <?= json_encode($messages) ?>,
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
        BackendMessages.initialize(true);
    });
</script>

<div class="container-fluid backend-page" id="messages-page">
    <div class="row" id="messages">

        <br/><br/>

        <div class="col-4 col-md-4" id="day-span">
            <span>See messages from the past</span>
            <span id="day-span-select"></span>
            <span>days</span>
        </div>

        <br/><br/>

        <div class="messages-list col-12 col-md-12">
            <div id="messages-details-row"></div>
            <div id="messages-note"></div>
        </div>

    </div>
</div>
