/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2020, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

(function () {

    'use strict';

    /**
     * This class contains the AgencyAdmins helper class declaration, along with the "Agency Admins" tab
     * event handlers. By dividing the backend/users tab functionality into separate files
     * it is easier to maintain the code.
     *
     * @class AgencyAdminsHelper
     */
    var AgencyAdminsHelper = function () {
        this.filterResults = []; // Store the results for later use.
        this.filterLimit = 20;
    };

    /**
     * Bind the event handlers for the backend/users "AgencyAdmins" tab.
     */
    AgencyAdminsHelper.prototype.bindEventHandlers = function () {
        /**
         * Event: Filter AgencyAdmins Form "Submit"
         *
         * Filter the admin records with the given key string.
         *
         * @param {jQuery.Event} event
         */
        $('#agency-admins').on('submit', '#filter-agency-admins form', function (event) {
            event.preventDefault();
            var key = $('#filter-agency-admins .key').val();
            $('#filter-agency-admins .selected').removeClass('selected');
            this.resetForm();
            this.filter(key);
        }.bind(this));

        /**
         * Event: Clear Filter Results Button "Click"
         */
        $('#agency-admins').on('click', '#filter-agency-admins .clear', function () {
            this.filter('');
            $('#filter-agency-admins .key').val('');
            this.resetForm();
        }.bind(this));

        /**
         * Event: Filter Admin Row "Click"
         *
         * Display the selected admin data to the user.
         */
        $('#agency-admins').on('click', '.agency-admin-row', function (event) {
            if ($('#filter-agency-admins .filter').prop('disabled')) {
                $('#filter-agency-admins .results').css('color', '#AAA');
                return; // exit because we are currently on edit mode
            }

            var adminId = $(event.currentTarget).attr('data-id');

            var admin = this.filterResults.find(function (filterResult) {
                return Number(filterResult.id) === Number(adminId);
            });

            this.display(admin);
            $('#filter-agency-admins .selected').removeClass('selected');
            $(event.currentTarget).addClass('selected');
            $('#edit-agency-admin, #delete-agency-admin').prop('disabled', false);
        }.bind(this));

        /**
         * Event: Add New Admin Button "Click"
         */
        $('#agency-admins').on('click', '#add-agency-admin', function () {
            this.resetForm();
            $('#agency-admins .add-edit-delete-group').hide();
            $('#agency-admins .save-cancel-group').show();
            $('#agency-admins .record-details').find('input, textarea').prop('disabled', false);
            $('#agency-admins .record-details').find('select').prop('disabled', false);
            $('#agency-admin-password, #agency-admin-password-confirm').addClass('required');
            $('#filter-agency-admins button').prop('disabled', true);
            $('#filter-agency-admins .results').css('color', '#AAA');
        }.bind(this));

        /**
         * Event: Edit Admin Button "Click"
         */
        $('#agency-admins').on('click', '#edit-agency-admin', function () {
            $('#agency-admins .add-edit-delete-group').hide();
            $('#agency-admins .save-cancel-group').show();
            $('#agency-admins .record-details').find('input, textarea').prop('disabled', false);
            $('#agency-admins .record-details').find('select').prop('disabled', false);
            $('#agency-admin-password, #agency-admin-password-confirm').removeClass('required');
            $('#filter-agency-admins button').prop('disabled', true);
            $('#filter-agency-admins .results').css('color', '#AAA');
        });

        /**
         * Event: Delete Admin Button "Click"
         */
        $('#agency-admins').on('click', '#delete-agency-admin', function () {
            var adminId = $('#agency-admin-id').val();

            var buttons = [
                {
                    text: EALang.cancel,
                    click: function () {
                        $('#message-box').dialog('close');
                    }
                },
                {
                    text: EALang.delete,
                    click: function () {
                        this.delete(adminId);
                        $('#message-box').dialog('close');
                    }.bind(this)
                }
            ];

            GeneralFunctions.displayMessageBox(EALang.delete_agency_admin, EALang.delete_record_prompt, buttons);
        }.bind(this));

        /**
         * Event: Save Admin Button "Click"
         */
        $('#agency-admins').on('click', '#save-agency-admin', function () {

            var admin = {
                first_name: $('#agency-admin-first-name').val(),
                last_name: $('#agency-admin-last-name').val(),
                email: $('#agency-admin-email').val(),
                mobile_number: $('#agency-admin-mobile-number').val(),
                phone_number: $('#agency-admin-phone-number').val(),
                address: $('#agency-admin-address').val(),
                city: $('#agency-admin-city').val(),
                state: $('#agency-admin-state').val(),
                zip_code: $('#agency-admin-zip-code').val(),
                notes: $('#agency-admin-notes').val(),
                timezone: $('#agency-admin-timezone').val(),
                settings: {
                    username: $('#agency-admin-username').val(),
                    notifications: $('#agency-admin-notifications').prop('checked'),
                    calendar_view: $('#agency-admin-calendar-view').val()
                }
            };

            // Include password if changed.
            if ($('#agency-admin-password').val() !== '') {
                admin.settings.password = $('#agency-admin-password').val();
            }

            // Include id if changed.
            if ($('#agency-admin-id').val() !== '') {
                admin.id = $('#agency-admin-id').val();
            }

            if (!this.validate()) {
                return;
            }
            console.log("save-agency-admin: first_name: " + JSON.stringify(admin));

            this.save(admin);
        }.bind(this));

        /**
         * Event: Cancel Admin Button "Click"
         *
         * Cancel add or edit of an admin record.
         */
        $('#agency-admins').on('click', '#cancel-agency-admin', function () {
            var id = $('#agency-admin-id').val();
            this.resetForm();
            if (id) {
                this.select(id, true);
            }
        }.bind(this));
    };

    /**
     * Remove the previously registered event handlers.
     */
    AgencyAdminsHelper.prototype.unbindEventHandlers = function () {
        $('#agency-admins')
            .off('submit', '#filter-agency-admins form')
            .off('click', '#filter-agency-admins .clear')
            .off('click', '.agency-admin-row')
            .off('click', '#add-agency-admin')
            .off('click', '#edit-agency-admin')
            .off('click', '#delete-agency-admin')
            .off('click', '#save-agency-admin')
            .off('click', '#cancel-agency-admin');
    };

    /**
     * Save admin record to database.
     *
     * @param {Object} admin Contains the admin record data. If an 'id' value is provided
     * then the update operation is going to be executed.
     */
    AgencyAdminsHelper.prototype.save = function (admin) {
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_agency_admin';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            agency_admin: JSON.stringify(admin)
        };

        $.post(url, data)
            .done(function (response) {
                Backend.displayNotification(EALang.admin_saved);
                this.resetForm();
                $('#filter-agency-admins .key').val('');
                this.filter('', response.id, true);
            }.bind(this));
    };

    /**
     * Delete an admin record from database.
     *
     * @param {Number} id Record id to be deleted.
     */
    AgencyAdminsHelper.prototype.delete = function (id) {
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_delete_agency_admin';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            agency_admin_id: id
        };

        $.post(url, data)
            .done(function (response) {
                Backend.displayNotification(EALang.agency_admin_deleted);
                this.resetForm();
                this.filter($('#filter-agency-admins .key').val());
            }.bind(this));
    };

    /**
     * Validates an admin record.
     *
     * @return {Boolean} Returns the validation result.
     */
    AgencyAdminsHelper.prototype.validate = function () {
        $('#agency-admins .has-error').removeClass('has-error');

        try {
            // Validate required fields.
            var missingRequired = false;

            $('#agency-admins .required').each(function (index, requiredField) {
                if (!$(requiredField).val()) {
                    $(requiredField).closest('.form-group').addClass('has-error');
                    missingRequired = true;
                }
            });

            if (missingRequired) {
                throw new Error('Fields with * are  required.');
            }

            // Validate passwords.
            if ($('#agency-admin-password').val() !== $('#agency-admin-password-confirm').val()) {
                $('#agency-admin-password, #agency-admin-password-confirm').closest('.form-group').addClass('has-error');
                throw new Error(EALang.passwords_mismatch);
            }

            if ($('#agency-admin-password').val().length < BackendUsers.MIN_PASSWORD_LENGTH
                && $('#agency-admin-password').val() !== '') {
                $('#agency-admin-password, #agency-admin-password-confirm').closest('.form-group').addClass('has-error');
                throw new Error(EALang.password_length_notice.replace('$number', BackendUsers.MIN_PASSWORD_LENGTH));
            }

            // Validate user email.
            if (!GeneralFunctions.validateEmail($('#agency-admin-email').val())) {
                $('#agency-admin-email').closest('.form-group').addClass('has-error');
                throw new Error(EALang.invalid_email);
            }

            // Check if username exists
            if ($('#agency-admin-username').attr('already-exists') === 'true') {
                $('#agency-admin-username').closest('.form-group').addClass('has-error');
                throw new Error(EALang.username_already_exists);
            }

            return true;
        } catch (error) {
            $('#agency-admins .form-message')
                .addClass('alert-danger')
                .text(error.message)
                .show();
            return false;
        }
    };

    /**
     * Resets the admin form back to its initial state.
     */
    AgencyAdminsHelper.prototype.resetForm = function () {
        $('#filter-agency-admins .selected').removeClass('selected');
        $('#filter-agency-admins button').prop('disabled', false);
        $('#filter-agency-admins .results').css('color', '');

        $('#agency-admins .add-edit-delete-group').show();
        $('#agency-admins .save-cancel-group').hide();
        $('#agency-admins .record-details')
            .find('input, select, textarea')
            .val('')
            .prop('disabled', true);
        $('#agency-admins .record-details #agency-admin-calendar-view').val('default');
        $('#agency-admins .record-details #agency-admin-timezone').val('UTC');
        $('#edit-agency-admin, #delete-agency-admin').prop('disabled', true);

        $('#agency-admins .has-error').removeClass('has-error');
        $('#agency-admins .form-message').hide();
    };

    /**
     * Display an admin record into the admin form.
     *
     * @param {Object} admin Contains the admin record data.
     */
    AgencyAdminsHelper.prototype.display = function (admin) {
        $('#agency-admin-id').val(admin.id);
        $('#agency-admin-first-name').val(admin.first_name);
        $('#agency-admin-last-name').val(admin.last_name);
        $('#agency-admin-email').val(admin.email);
        $('#agency-admin-mobile-number').val(admin.mobile_number);
        $('#agency-admin-phone-number').val(admin.phone_number);
        $('#agency-admin-address').val(admin.address);
        $('#agency-admin-city').val(admin.city);
        $('#agency-admin-state').val(admin.state);
        $('#agency-admin-zip-code').val(admin.zip_code);
        $('#agency-admin-notes').val(admin.notes);
        $('#agency-admin-timezone').val(admin.timezone);

        $('#agency-admin-username').val(admin.settings.username);
        $('#agency-admin-calendar-view').val(admin.settings.calendar_view);
        $('#agency-admin-notifications').prop('checked', Boolean(Number(admin.settings.notifications)));
    };

    /**
     * Filters admin records depending a key string.
     *
     * @param {String} key This string is used to filter the admin records of the database.
     * @param {Number} selectId (OPTIONAL = undefined) This record id will be selected when
     * the filter operation is finished.
     * @param {Boolean} display (OPTIONAL = false) If true the selected record data are going
     * to be displayed on the details column (requires a selected record though).
     */
    AgencyAdminsHelper.prototype.filter = function (key, selectId, display) {
        display = display || false;

        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_filter_agency_admins';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            key: key,
            limit: this.filterLimit
        };

        $.post(url, data)
            .done(function (response) {
                this.filterResults = response;

                $('#filter-agency-admins .results').empty();

                response.forEach(function (admin) {
                    $('#filter-agency-admins .results')
                        .append(this.getFilterHtml(admin))
                        .append($('<hr/>'));
                }.bind(this));

                if (!response.length) {
                    $('#filter-agency-admins .results').append(
                        $('<em/>', {
                            'text': EALang.no_records_found
                        })
                    );
                } else if (response.length === this.filterLimit) {
                    $('<button/>', {
                        'type': 'button',
                        'class': 'btn btn-block btn-outline-secondary load-more text-center',
                        'text': EALang.load_more,
                        'click': function () {
                            this.filterLimit += 20;
                            this.filter(key, selectId, display);
                        }.bind(this)
                    })
                        .appendTo('#filter-agency-admins .results');
                }

                if (selectId) {
                    this.select(selectId, display);
                }
            }.bind(this));
    };

    /**
     * Get an admin row html code that is going to be displayed on the filter results list.
     *
     * @param {Object} admin Contains the admin record data.
     *
     * @return {String} The html code that represents the record on the filter results list.
     */
    AgencyAdminsHelper.prototype.getFilterHtml = function (admin) {
        var name = admin.first_name + ' ' + admin.last_name;

        var info = admin.email;

        info = admin.mobile_number ? info + ', ' + admin.mobile_number : info;

        info = admin.phone_number ? info + ', ' + admin.phone_number : info;

        return $('<div/>', {
            'class': 'agency-admin-row entry',
            'data-id': admin.id,
            'html': [
                $('<strong/>', {
                    'text': name
                }),
                $('<br/>'),
                $('<span/>', {
                    'text': info
                }),
                $('<br/>'),
            ]
        });
    };

    /**
     * Select a specific record from the current filter results. If the admin id does not exist
     * in the list then no record will be selected.
     *
     * @param {Number} id The record id to be selected from the filter results.
     * @param {Boolean} display Optional (false), if true then the method will display the record
     * on the form.
     */
    AgencyAdminsHelper.prototype.select = function (id, display) {
        display = display || false;

        $('#filter-agency-admins .selected').removeClass('selected');

        $('#filter-agency-admins .agency-admin-row[data-id="' + id + '"]').addClass('selected');

        if (display) {
            var admin = this.filterResults.find(function (filterResult) {
                return Number(filterResult.id) === Number(id);
            });

            this.display(admin);

            $('#edit-agency-admin, #delete-agency-admin').prop('disabled', false);
        }
    };

    window.AgencyAdminsHelper = AgencyAdminsHelper;

})();
