<script src="<?= asset_url('assets/ext/jquery-fullcalendar/fullcalendar.min.js') ?>"></script>
<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.min.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_inmates_helper.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_inmates.js') ?>"></script>
<script>
    var GlobalVariables = {
        csrfToken: <?= json_encode($this->security->get_csrf_hash()) ?>,
        dateFormat: <?= json_encode($date_format) ?>,
        timeFormat: <?= json_encode($time_format) ?>,
        baseUrl: <?= json_encode($base_url) ?>,
        inmates: <?= json_encode($inmates) ?>,
        relationships: <?= json_encode($relationships) ?>,
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
        BackendInmates.initialize(true);
    });
</script>

<div class="container-fluid backend-page" id="inmates-page">
    <div class="row" id="inmates">
        <div id="filter-inmates" class="filter-records col col-12 col-md-5">
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
                    <div style="padding:10px;">
                        <input type="checkbox" id="filter-by-housed" checked="true"/>
                        <span>Filter by currently housed</span>
                    </div>
                </div>
            </form>

            <h3><?= lang('inmates') ?></h3>
            <div class="results"></div>
        </div>

        <div class="record-details col-12 col-md-7">
            <div class="btn-toolbar mb-4">
                <div id="add-edit-delete-group" class="btn-group"><span style="height:37px;"></span></div>
            </div>

            <input id="inmate-id" type="hidden">

            <div id="inmate-details">
                <form id="inmate-update-form" action="#" method="POST">
                <div id="form-message" class="alert" style="display:none;"></div>
                <div class="col-8 col-md-12" style="margin-left: 0;">
                    <h3>Inmate <?= lang('details') ?></h3>

                    <div id="inmate-details-row"></div>

                    <div id="inmate-info-visitors" class="col-md-12" style="float:left;padding-top:30px;display:none;">
                        <span style="font-weight:bold;font-size:+2;">Approved Visitor List</span>
                        <div id="visitor-dates" style="width:450px;padding:10px;">
                            <div id="visitor-ed" style="display:inline;width:170px;padding-left:10px;padding-right:30px;">Effective Date:</div>
                            <input id="visitor-ed-datepicker" type="text" />
                            <br/>
                            <div id="visitor-od" style="display:inline;width:170px;padding-left:10px;padding-right:30px;">Obsolete Date:</div>
                            <div id="visitor-od-date" style="display:inline;width:200px;"></div>
                        </div>
                        <div id="visitor-names" style="width:800px;">
                            <div id="visitor-fn" style="display:inline;width:170px;padding-right:110px;">First Name</div>
                            <div id="visitor-ln" style="display:inline;width:170px;padding-right:120px;">Last Name</div>
                            <div id="visitor-ln" style="display:inline;width:380px;">Relationship to Inmate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
