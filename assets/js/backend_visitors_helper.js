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
     * VisitorsHelper Class
     *
     * This class contains the methods that are used in the backend visitors page.
     *
     * @class VisitorsHelper
     */
    function VisitorsHelper() {
        this.filterResults = {};
        this.filterLimit = 20;
    }

    /**
     * Binds the default event handlers of the backend visitors page.
     */
    VisitorsHelper.prototype.bindEventHandlers = function () {
        var instance = this;

        /**
         * Event: Filter Visitors Form "Submit"
         *
         * @param {jQuery.Event} event
         */
        $('#visitors').on('submit', '#filter-visitors form', function (event) {
            event.preventDefault();
            var key = $('#filter-visitors .key').val();
            $('#filter-visitors .selected').removeClass('selected');
            instance.filterLimit = 20;
            instance.resetForm();
            instance.filter(key);
        });

        /**
         * Event: Filter Visitors Clear Button "Click"
         */
        $('#visitors').on('click', '#filter-visitors .clear', function () {
            $('#filter-visitors .key').val('');
            instance.filterLimit = 20;
            instance.filter('');
            instance.resetForm();
        });

        /**
         * Event: Filter Entry "Click"
         *
         * Display the visitor data of the selected row.
         */
        $('#visitors').on('click', '.visitor-row', function () {
            if ($('#filter-visitors .filter').prop('disabled')) {
                return; // Do nothing when user edits a visitor record.
            }

            const visitor_id = $(this).attr('data-id');

            const visitor_appointments = instance.filterResults.find(function (filterResult) {
                return Number(filterResult.id) === Number(visitor_id);
            });
            //console.log("*** APPT: " + JSON.stringify(visitor_appointments));

            instance.display(visitor_appointments);

            $('#visitors .visitor-row').removeClass('selected');
            $(this).addClass('selected');
            $('#edit-customer, #delete-customer').prop('disabled', false);

            instance.displayVisitor(visitor_appointments);

            // Check the checkboxes approriately
            const flagCBname = "visitor-flag-check-" + visitor_id;
            const flagCBchecked = (visitor_appointments.flag && visitor_appointments.flag == 1) ? true : false;
            $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
        });

        /**
         * Event: Add Visitor Button "Click"
         */
        $('#visitors').on('click', '#add-visitor', function () {
            instance.resetForm();
            $('#add-edit-delete-group').hide();
            $('#save-cancel-group').show();
            $('.record-details')
                .find('input, select, textarea')
                .prop('disabled', false);
            $('#filter-visitors button').prop('disabled', true);
            $('#filter-visitors .results').css('color', '#AAA');
        });

        /**
         * Event: Edit Visitor Button "Click"
         */
        $('#visitors').on('click', '#edit-visitor', function () {
            $('.record-details')
                .find('input, select, textarea')
                .prop('disabled', false);
            $('#add-edit-delete-group').hide();
            $('#save-cancel-group').show();
            $('#filter-visitors button').prop('disabled', true);
            $('#filter-visitors .results').css('color', '#AAA');
        });

        /**
         * Event: Cancel Visitor Add/Edit Operation Button "Click"
         */
        $('#visitors').on('click', '#cancel-visitor', function () {
            var id = $('#visitor-id').val();
            instance.resetForm();
            if (id) {
                instance.select(id, true);
            }
        });

        /**
         * Event: Save Add/Edit Visitor Operation "Click"
         */
        $('#visitors').on('click', '#save-visitor', function () {
            var visitor = {
                first_name: $('#first-name').val(),
                last_name: $('#last-name').val(),
                email: $('#email').val(),
                phone_number: $('#phone-number').val(),
                address: $('#address').val(),
                city: $('#city').val(),
                zip_code: $('#zip-code').val(),
                notes: $('#notes').val(),
                timezone: $('#timezone').val(),
                language: $('#language').val() || 'english'
            };

            if ($('#visitor-id').val()) {
                visitor.id = $('#visitor-id').val();
            }

            if (!instance.validate()) {
                return;
            }

            instance.save(visitor);
        });

        /**
         * Event: Delete Visitor Button "Click"
         */
        $('#visitors').on('click', '#delete-visitor', function () {
            var visitorId = $('#visitor-id').val();
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
                        instance.delete(visitorId);
                        $('#message-box').dialog('close');
                    }
                }
            ];

            GeneralFunctions.displayMessageBox(EALang.delete_visitor,
                EALang.delete_record_prompt, buttons);
        });

        /**
         * Event: Flag "Click"
         */
        $('#visitor-details').on('change', '#visitor-flag-check', function () {
            const checked = $(this).is(':checked');
            const visitor_id = $(this).data('id');

            // Save the flag notes here also, if any
            const flag_notes = $('[name="visitor-flag-notes-' + visitor_id + '"]').val();

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_set_visitor_flag_visitors';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                visitor_id: visitor_id,
                checked: checked,
                flag_notes: flag_notes
            };
    
            $.post(url, data)
                .done(function (response) {
                    const visitor = response;
                    const visitor_id = visitor.id;

                    // reset the form to get updated data
                    const key = $('#filter-visitors .key').val();
                    instance.resetForm();
                    instance.filter(key,visitor_id,true);
       
                    const flagCBname = "visitor-flag-check-" + visitor_id;
                    const flagCBchecked = (visitor.flag && visitor.flag == 1) ? true : false;
                    $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
                }.bind(this));
            //
        });

        /**
         * Event: Save Flag Notes
         */
        $('#visitor-details').on('click', '#visitor-flag-notes-save', function () {
            const visitor_id = $(this).data('id');
            const flag_notes = $('[name="visitor-flag-notes-' + visitor_id + '"]').val();

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_visitor_flag_notes_visitors';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                visitor_id: visitor_id,
                flag_notes: flag_notes
            };
    
            $.post(url, data)
                .done(function (response) {
                    const visitor = response;
                    const visitor_id = visitor.id;

                    // reset the form to get updated data
                    const key = $('#filter-visitors .key').val();
                    instance.resetForm();
                    instance.filter(key,visitor_id,true);
       
                    const flagCBname = "visitor-flag-check-" + visitor_id;
                    const flagCBchecked = (visitor.flag && visitor.flag == 1) ? true : false;
                    $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
                }.bind(this));
            //
        });
    };

    /**
     * Save a visitor record to the database (via ajax post).
     *
     * @param {Object} visitor Contains the visitor data.
     */
    VisitorsHelper.prototype.save = function (visitor) {
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_visitor';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            visitor: JSON.stringify(visitor)
        };

        $.post(url, data)
            .done(function (response) {
                Backend.displayNotification(EALang.visitor_saved);
                this.resetForm();
                $('#filter-visitors .key').val('');
                this.filter('', response.id, true);
            }.bind(this));
    };

    /**
     * Delete a visitor record from database.
     *
     * @param {Number} id Record id to be deleted.
     */
    VisitorsHelper.prototype.delete = function (id) {
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_delete_visitor';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            visitor_id: id
        };

        $.post(url, data)
            .done(function () {
                Backend.displayNotification(EALang.visitor_deleted);
                this.resetForm();
                this.filter($('#filter-visitors .key').val());
            }.bind(this));
    };

    /**
     * Validate visitor data before save (insert or update).
     */
    VisitorsHelper.prototype.validate = function () {
        $('#form-message')
            .removeClass('alert-danger')
            .hide();
        $('.has-error').removeClass('has-error');

        try {
            // Validate required fields.
            var missingRequired = false;

            $('.required').each(function (index, requiredField) {
                if ($(requiredField).val() === '') {
                    $(requiredField).closest('.form-group').addClass('has-error');
                    missingRequired = true;
                }
            });

            if (missingRequired) {
                throw new Error(EALang.fields_are_required);
            }

            // Validate email address.
            if (!GeneralFunctions.validateEmail($('#email').val())) {
                $('#email').closest('.form-group').addClass('has-error');
                throw new Error(EALang.invalid_email);
            }

            return true;
        } catch (error) {
            $('#form-message')
                .addClass('alert-danger')
                .text(error.message)
                .show();
            return false;
        }
    };

    /**
     * Bring the visitor form back to its initial state.
     */
    VisitorsHelper.prototype.resetForm = function () {
        $('.record-details')
            .find('input, select, textarea')
            .val('')
            .prop('disabled', true);
        $('.record-details #timezone').val('UTC');

        $('#language').val('english');

        $('#visitor-appointments').empty();
        $('#edit-visitor, #delete-visitor').prop('disabled', true);
        $('#add-edit-delete-group').show();
        $('#save-cancel-group').hide();

        $('.record-details .has-error').removeClass('has-error');
        $('.record-details #form-message').hide();

        $('#filter-visitors button').prop('disabled', false);
        $('#filter-visitors .selected').removeClass('selected');
        $('#filter-visitors .results').css('color', '');
    };

    /**
     * Display a visitor record into the form.
     *
     * @param {Object} visitor Contains the visitor record data.
     */
    VisitorsHelper.prototype.display = function (visitor) {
        $('#visitor-id').val(visitor.id);
        $('#first-name').val(visitor.first_name);
        $('#last-name').val(visitor.last_name);
        $('#email').val(visitor.email);
        $('#phone-number').val(visitor.phone_number);
        $('#address').val(visitor.address);
        $('#city').val(visitor.city);
        $('#zip-code').val(visitor.zip_code);
        $('#notes').val(visitor.notes);
        $('#timezone').val(visitor.timezone);
        $('#language').val(visitor.language || 'english');

        $('#visitor-appointments').empty();

        if (!visitor.appointments.length) {
            $('<p/>', {
                'text': EALang.no_records_found
            })
                .appendTo('#visitor-appointments');
        }

        visitor.appointments.forEach(function (appointment) {
            var start = GeneralFunctions.formatDate(Date.parse(appointment.start_datetime), GlobalVariables.dateFormat, true);
            var end = GeneralFunctions.formatDate(Date.parse(appointment.end_datetime), GlobalVariables.dateFormat, true);

            $('<div/>', {
                'class': 'appointment-row',
                'name': 'appointment-row-' + appointment.id,
                'data-id': appointment.id,
                'html': [
                    // Time and Service
                    $('<div/>', {
                        'text': start + ' to ' + end
                    }),
                    $('<div/>', {
                        'text': appointment.service_name
                    }),
                    // Inmate
                    $('<div/>', {
                        'style': 'padding-left:14px;font-weight:bold;',
                        'text': 'Inmate: ' + appointment.inmate_name
                    }),
                    // Provider
                    $('<div/>', {
                        'style': 'padding-left:14px;font-weight:bold;',
                        'text': 'Phone: ' + appointment.provider_first_name + " " + appointment.provider_last_name
                    })
                ]
            }).appendTo('#visitor-appointments');
        });
    };

    /**
     * Filter visitor records.
     *
     * @param {String} key This key string is used to filter the visitor records.
     * @param {Number} selectId Optional, if set then after the filter operation the record with the given
     * ID will be selected (but not displayed).
     * @param {Boolean} display Optional (false), if true then the selected record will be displayed on the form.
     */
    VisitorsHelper.prototype.filter = function (key, selectId, display) {
        display = display || false;

        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_filter_visitors';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            key: key,
            limit: this.filterLimit
        };

        $.post(url, data)
            .done(function (response) {
                this.filterResults = response;

                $('#filter-visitors .results').empty();

                response.forEach(function (visitor) {
                    $('#filter-visitors .results')
                        .append(this.getFilterHtml(visitor))
                        .append($('<hr/>'));
                }.bind(this));

                if (!response.length) {
                    $('#filter-visitors .results').append(
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
                        .appendTo('#filter-visitors .results');
                }

                if (selectId) {
                    this.select(selectId, display);
                }

            }.bind(this));
    };

    /**
     * Get the filter results row HTML code.
     *
     * @param {Object} visitor Contains the visitor data.
     *
     * @return {String} Returns the record HTML code.
     */
    VisitorsHelper.prototype.getFilterHtml = function (visitor) {
        var name = 'Name: ' + visitor.last_name + ', ' + visitor.first_name;
        var info = 'Birth Date:' + Date.parse(visitor.birthdate).toString('MM/dd/yyyy');

        return $('<div/>', {
            'class': 'visitor-row entry',
            'name': 'visitor-row-' + visitor.id,
            'data-id': visitor.id,
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
     * Select a specific record from the current filter results.
     *
     * If the visitor id does not exist in the list then no record will be selected.
     *
     * @param {Number} id The record id to be selected from the filter results.
     * @param {Boolean} display Optional (false), if true then the method will display the record
     * on the form.
     */
    VisitorsHelper.prototype.select = function (id, display) {
        const instance = this;

        display = display || false;

        if (display) {
            const visitor_id = id;

            const visitor_appointments = instance.filterResults.find(function (filterResult) {
                return Number(filterResult.id) === Number(visitor_id);
            });

            instance.display(visitor_appointments);

            $('#visitors .visitor-row').removeClass('selected');
            $('#visitors .visitor-row[name="visitor-row-' + id + '"]').addClass('selected');
            $('#edit-customer, #delete-customer').prop('disabled', false);

            instance.displayVisitor(visitor_appointments);

            // Check the checkboxes approriately
            const flagCBname = "visitor-flag-check-" + visitor_id;
            const flagCBchecked = (visitor_appointments.flag && visitor_appointments.flag == 1) ? true : false;
            $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
        }
    };

    /**
     * Display the visitor record
     *  NOTE: vistor data includes appointment_visitor columns
     *
     * @param {Object} visitor Contains the visitor record data.
     */
    VisitorsHelper.prototype.displayVisitor = function (visitor) {
        $('#visitor-details-row').empty();

        if (!visitor) {
            $('<p/>', {
                'text': EALang.no_records_found
            })
                .appendTo('#visitor-details-row');
        } else {

            const visitor_id = visitor.id;
            const vIDNum = visitor.id_number ? visitor.id_number : "N/A";
            const vIDState = visitor.id_state ? visitor.id_state : "N/A";
            const vIDImageSrc = visitor.id_image_filename ? '/storage/uploads/user_doc/' + visitor.id_image_filename : '/assets/img/no-ID.jpg';
            const vFlagDate = visitor.flag_date ? Date.parse(visitor.flag_date).toString('MM/dd/yyyy') : "N/A";

            // Create sections for the attorney info, if they exist
            let vAttorneyInfo = "<br/>";
            if (visitor.attorney_type) {
                const vcauseNumText = visitor.cause_number ? 'Cause Number: ' + visitor.cause_number : 'Cause Number: N/A';
                const vca = visitor.court_appointed == 1 ? 'yes' : 'no';
                vAttorneyInfo = "<br/><br/><div>Court Appointed Attorney: " + vca + "</div>" +
                "<div>" + vcauseNumText + "</div>" +
                "<div>Law Firm: " + visitor.law_firm + "</div>" +
                "<div>Attorney Type: " + visitor.attorney_type + "</div>";
            }

            $('<div/>', {
                'class': 'visitor-info col-md-12',
                'data-id': visitor_id,
                'html': [
                    $('<div/>', {
                        'class': 'visitor-info-left',
                        'style': 'float:left;width:50%;',
                        'data-id': visitor_id,
                        'html': [
                            // Name
                            $('<div/>', {
                                'text': visitor.last_name + ', ' + visitor.first_name
                            }),
                            // DOB
                            $('<div/>', {
                                'text': 'Birth Date: ' + Date.parse(visitor.birthdate).toString('MM/dd/yyyy')
                            }),
                            // Email
                            $('<div/>', {
                                'text': visitor.email
                            }),
                            // Phone
                            $('<div/>', {
                                'text': visitor.phone_number
                            }),
                            // Address
                            $('<div/>', {
                                'text': visitor.address 
                            }),
                            $('<div/>', {
                                'text': visitor.city + ', ' + visitor.state + ' ' + visitor.zip_code
                            }),
                            $('<br/>'),
                            // ID Number
                            $('<div/>', {
                                'text': 'ID Number: ' + vIDNum
                            }),
                            // ID State
                            $('<div/>', {
                                'text': 'ID State: ' + vIDState
                            }).append(vAttorneyInfo)
                        ]
                    }),
                    $('<div/>', {
                        'class': 'visitor-info-right',
                        'style': 'float:right;width:50%;',
                        'data-id': visitor_id,
                        'html': [
                            $('<div/>', {
                                'class': 'visitor-flag-notes',
                                'data-id': visitor_id,
                                'html': [
                                    $('<div/>', {
                                        'class': 'visitor-flag',
                                        'data-id': visitor_id,
                                        'html': [
                                            $('<span/>', {
                                                'class': 'visitor-flag-check',
                                                'text': 'Flag: '
                                            }),
                                            $('<input/>', {
                                                'id': 'visitor-flag-check',
                                                'name': 'visitor-flag-check-' + visitor_id,
                                                'data-id': visitor_id,
                                                'type': 'checkbox'
                                            }),
                                            $('<span/>', {
                                                'class': 'visitor-flag-check',
                                                'text': 'Date Flagged: ' + vFlagDate
                                            }),
                                            $('<br/>'),
                                            $('<span/>', {
                                                'class': 'visitor-flag-check',
                                                'text': 'Flag Notes: '
                                            }),
                                            $('<br/>'),
                                            $('<textarea/>', {
                                                'id': 'visitor-flag-notes',
                                                'name': 'visitor-flag-notes-' + visitor_id,
                                                'text': visitor.flag_notes,
                                                'rows': 3,
                                                'cols': 24
                                            }),
                                            $('<br/>'),
                                            $('<input/>', {
                                                'id': 'visitor-flag-notes-save',
                                                'name': 'visitor-flag-notes-save-' + visitor_id,
                                                'data-id': visitor_id,
                                                'type': 'button',
                                                'value': 'Save Notes'
                                            }),
                                            $('<br/>'),
                                            $('<div/>', {
                                                'class': 'visitor-id',
                                                'data-id': visitor_id,
                                                'html': [
                                                    // ID Image
                                                    $('<img/>', {
                                                        'id': 'v1-id-image-file',
                                                        'width': '250',
                                                        'src': vIDImageSrc
                                                    })
                                                ]
                                            })
                                        ]
                                    })
                                ]
                            })
                        ]
                    })
                ]
            }).appendTo('#visitor-details-row');
        }
    };

    window.VisitorsHelper = VisitorsHelper;
})();
