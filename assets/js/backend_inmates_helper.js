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
     * InmatesHelper Class
     *
     * This class contains the methods that are used in the backend inmates page.
     *
     * @class InmatesHelper
     */
    function InmatesHelper() {
        this.filterResults = {};
        this.filterLimit = 20;
    }

    /**
     * Binds the default event handlers of the backend inmates page.
     */
    InmatesHelper.prototype.bindEventHandlers = function () {
        var instance = this;

        /**
         * Event: Filter Inmates Form "Submit"
         *
         * @param {jQuery.Event} event
         */
        $('#inmates').on('submit', '#filter-inmates form', function (event) {
            event.preventDefault();
            var key = $('#filter-inmates .key').val();
            $('#filter-inmates .selected').removeClass('selected');
            instance.filterLimit = 20;
            instance.resetForm();
            instance.filter(key);
        });

        /**
         * Event: Filter Inmates Clear Button "Click"
         */
        $('#inmates').on('click', '#filter-inmates .clear', function () {
            $('#filter-inmates .key').val('');
            instance.filterLimit = 20;
            instance.filter('');
            instance.resetForm();
        });

        /**
         * Event: Filter Entry "Click"
         *
         * Display the inmate data of the selected row.
         */
        $('#inmates').on('click', '.inmate-row', function () {
            if ($('#filter-inmates .filter').prop('disabled')) {
                return; // Do nothing when user edits a inmate record.
            }

            // NOTE: in inmates table, id is ID (capitalized)
            var inmateId = $(this).attr('data-id');
            var inmate = instance.filterResults.find(function (filterResult) {
                return Number(filterResult.ID) === Number(inmateId);
            });

            instance.displayInmate(inmate);
            $('#filter-inmates .selected').removeClass('selected');
            $(this).addClass('selected');

            // Check the checkboxes approriately
            const flagCBname = "inmate-flag-check-" + inmateId;
            const flagCBchecked = (inmate.inmate_flag && inmate.inmate_flag == 1) ? true : false;
            $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
        });
    
        /**
         * Event: Flag "Click"
         */
        $('#inmate-details').on('change', '#inmate-flag-check', function () {
            const checked = $(this).is(':checked');
            const inmate_id = $(this).data('id');

            // Save the flag notes here also, if any
            const flag_notes = $('[name="inmate-flag-notes-' + inmate_id + '"]').val();
            console.log("*** FLAG Inmate ID: " + inmate_id + " / checked " + checked);

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_set_inmate_flag_inmates';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                inmate_id: inmate_id,
                checked: checked,
                flag_notes: flag_notes
            };
    
            $.post(url, data)
                .done(function (response) {
                    const inmate = response;
//                    console.log("I: " + JSON.stringify(inmate));

                    // reset the form to get updated data
                    const key = $('#filter-inmates .key').val();
                    $('#filter-inmates .selected').removeClass('selected');
                    instance.filterLimit = 20;
                    instance.resetForm();
                    instance.filter(key);

                    instance.displayInmate(inmate);

                    const inmate_id = inmate.ID;
                    const flagCBname = "inmate-flag-check-" + inmate_id;
                    const flagCBchecked = (inmate.inmate_flag && inmate.inmate_flag == 1) ? true : false;
                    $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
                }.bind(this));
            //
        });

        /**
         * Event: Save Flag Notes
         */
        $('#inmate-details').on('click', '#inmate-flag-notes-save', function () {
            const inmate_id = $(this).data('id');
            const flag_notes = $('[name="inmate-flag-notes-' + inmate_id + '"]').val();

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_inmate_flag_notes_inmates';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                inmate_id: inmate_id,
                flag_notes: flag_notes
            };
    
            $.post(url, data)
                .done(function (response) {
                    const inmate = response;
//                    console.log("OUT I: " + JSON.stringify(inmate));

                    // reset the form to get updated data
                    const key = $('#filter-inmates .key').val();
                    $('#filter-inmates .selected').removeClass('selected');
                    instance.filterLimit = 20;
                    instance.resetForm();
                    instance.filter(key);

                    instance.displayInmate(inmate);

                    const inmate_id = inmate.ID;
                    const flagCBname = "inmate-flag-check-" + inmate_id;
                    const flagCBchecked = (inmate.inmate_flag && inmate.inmate_flag == 1) ? true : false;
                    $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
                }.bind(this));
            //
        });
    };

    /**
     * Save a inmate record to the database (via ajax post).
     *
     * @param {Object} inmate Contains the inmate data.
     */
    InmatesHelper.prototype.save = function (inmate) {
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_inmate';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            inmate: JSON.stringify(inmate)
        };

        $.post(url, data)
            .done(function (response) {
                Backend.displayNotification(EALang.inmate_saved);
                this.resetForm();
                $('#filter-inmates .key').val('');
                this.filter('', response.id, true);
            }.bind(this));
    };

    /**
     * Delete a inmate record from database.
     *
     * @param {Number} id Record id to be deleted.
     */
    InmatesHelper.prototype.delete = function (id) {
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_delete_inmate';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            inmate_id: id
        };

        $.post(url, data)
            .done(function () {
                Backend.displayNotification(EALang.inmate_deleted);
                this.resetForm();
                this.filter($('#filter-inmates .key').val());
            }.bind(this));
    };

    /**
     * Validate inmate data before save (insert or update).
     */
    InmatesHelper.prototype.validate = function () {
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
     * Bring the inmate form back to its initial state.
     */
    InmatesHelper.prototype.resetForm = function () {
        $('.record-details')
            .find('input, select, textarea')
            .val('')
            .prop('disabled', true);
        $('.record-details #timezone').val('UTC');

        $('#language').val('english');

        $('#inmate-appointments').empty();
        $('#edit-inmate, #delete-inmate').prop('disabled', true);
        $('#add-edit-delete-group').show();
        $('#save-cancel-group').hide();

        $('.record-details .has-error').removeClass('has-error');
        $('.record-details #form-message').hide();

        $('#filter-inmates button').prop('disabled', false);
        $('#filter-inmates .selected').removeClass('selected');
        $('#filter-inmates .results').css('color', '');
    };


    /**
     * Filter inmate records.
     *
     * @param {String} key This key string is used to filter the inmate records.
     * @param {Number} selectId Optional, if set then after the filter operation the record with the given
     * ID will be selected (but not displayed).
     * @param {Boolean} display Optional (false), if true then the selected record will be displayed on the form.
     */
    InmatesHelper.prototype.filter = function (key, selectId, display) {
        display = display || false;

        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_filter_inmates';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            key: key,
            limit: this.filterLimit
        };

        $.post(url, data)
            .done(function (response) {
                this.filterResults = response;

                $('#filter-inmates .results').empty();

                response.forEach(function (inmate) {
                    $('#filter-inmates .results')
                        .append(this.getFilterHtml(inmate))
                        .append($('<hr/>'));
                }.bind(this));

                if (!response.length) {
                    $('#filter-inmates .results').append(
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
                        .appendTo('#filter-inmates .results');
                }

                if (selectId) {
                    this.select(selectId, display);
                }

            }.bind(this));
    };

    /**
     * Get the filter results row HTML code.
     *
     * @param {Object} inmate Contains the inmate data.
     *
     * @return {String} Returns the record HTML code.
     */
    InmatesHelper.prototype.getFilterHtml = function (inmate) {
        const id = inmate.ID;
        const name = inmate.inmate_name;

        let info = inmate.gender;
        const dobLen = inmate.DOB.length;
        const dob = dobLen === 8 ? inmate.DOB.substring(0,2) + "/" + inmate.DOB.substring(2,4) + "/" + inmate.DOB.substring(dobLen - 4, dobLen) : '';

        info = dob != '' ? info + ' DOB: ' + dob : info;

        return $('<div/>', {
            'class': 'inmate-row entry',
            'data-id': id,
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
     * If the inmate id does not exist in the list then no record will be selected.
     *
     * @param {Number} id The record id to be selected from the filter results.
     * @param {Boolean} display Optional (false), if true then the method will display the record
     * on the form.
     */
    InmatesHelper.prototype.select = function (id, display) {
        display = display || false;

        $('#filter-inmates .selected').removeClass('selected');

        $('#filter-inmates .entry[data-id="' + id + '"]').addClass('selected');

        if (display) {
            const inmate_id = id;

            // NOTE: in inmates table, id is ID (capitalized)
            const inmate_data = instance.filterResults.find(function (filterResult) {
                return Number(filterResult.ID) === Number(inmate_id);
            });

            $('#inmates .inmate-row').removeClass('selected');
            $(this).addClass('selected');

            instance.displayInmate(inmate_data);

            // Check the checkboxes approriately
            const flagCBname = "inmate-flag-check-" + inmate_id;
            const flagCBchecked = (inmate_data.inmate_flag && inmate_data.inmate_flag == 1) ? true : false;
            $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
        }
    };

    /**
     * Display the inmate record
     *
     * @param {Object} inmate Contains the inmate record data.
     */
    InmatesHelper.prototype.displayInmate = function (inmate) {
        $('#inmate-details-row').empty();

        if (!inmate) {
            $('<p/>', {
                'text': EALang.no_records_found
            })
                .appendTo('#inmate-details-row');
        } else {

            const inmate_id = inmate.ID;
            const vFlagDate = inmate.inmate_flag_date ? Date.parse(inmate.inmate_flag_date).toString('MM/dd/yyyy') : "N/A";

            $('<div/>', {
                'id': 'inmate-info-outer',
                'class': 'col-md-12',
                'data-id': inmate_id,
                'html': [
                    $('<div/>', {
                        'class': 'inmate-info-left col-md-5',
                        'style': 'float:left',
                        'data-id': inmate_id,
                        'html': [
                            // Name
                            $('<div/>', {
                                'text': 'Name: ' + inmate.inmate_name
                            }),
                            // SO
                            $('<div/>', {
                                'text': 'SO: ' + inmate.SO
                            }),
                            // DOB
                            $('<div/>', {
                                'text': 'Birth Date: ' + Date.parse(inmate.DOB).toString('MM/dd/yyyy')
                            }),
                            // Gender
                            $('<div/>', {
                                'text': inmate.gender ? 'Gender: ' + inmate.gender : 'Gender: N/A'
                            }),
                            // Booking Date
                            $('<div/>', {
                                'text': (inmate.Booking_Date && inmate.Booking_Date != "") ? 'Booking Date: ' + Date.parse(inmate.Booking_Date).toString('MM/dd/yyyy') : 'Booking Date: N/A'
                            }),
                            // Booking Status
                            $('<div/>', {
                                'text': (inmate.booking_status && inmate.booking_status == "1") ? 'Currently Housed: YES' : 'Currently Housed: NO'
                            }),
                            // Release Date
                            $('<div/>', {
                                'text': (inmate.Release_Date && inmate.Release_Date != "") ? 'Release Date: ' + Date.parse(inmate.Release_Date).toString('MM/dd/yyyy') : 'Release Date: N/A'
                            })
                        ]
                    }),
                    $('<br/>'),
                    $('<div/>', {
                        'id': 'inmate-info-right',
                        'style': 'float:right',
                        'class': 'col-md-7',
                        'data-id': inmate_id,
                        'html': [
                            $('<div/>', {
                                'class': 'inmate-flag-info',
                                'data-id': inmate_id,
                                'html': [
                                    $('<span/>', {
                                        'class': 'inmate-flag-check',
                                        'text': 'Flag: '
                                    }),
                                    $('<input/>', {
                                        'id': 'inmate-flag-check',
                                        'name': 'inmate-flag-check-' + inmate_id,
                                        'data-id': inmate_id,
                                        'type': 'checkbox'
                                    }),
                                    $('<span/>', {
                                        'class': 'inmate-flag-check',
                                        'style': 'padding-left:10px',
                                        'text': 'Date Flagged: ' + vFlagDate
                                    }),
                                    $('<div/>', {
                                        'style': 'padding-top:5px;',
                                        'html': [
                                            $('<span/>', {
                                                'class': 'inmate-flag-check',
                                                'style': 'vertical-align:top',
                                                'text': 'Flag Notes: '
                                            }),
                                            $('<textarea/>', {
                                                'id': 'inmate-flag-notes',
                                                'name': 'inmate-flag-notes-' + inmate_id,
                                                'text': inmate.inmate_flag_notes,
                                                'rows': 4,
                                                'cols': 30
                                            })
                                        ]
                                    }),
                                    $('<input/>', {
                                        'id': 'inmate-flag-notes-save',
                                        'name': 'inmate-flag-notes-save-' + inmate_id,
                                        'data-id': inmate_id,
                                        'type': 'button',
                                        'value': 'Save Notes'
                                    })
                                ]
                            }),
                        ]
                    })
                ]
            }).appendTo('#inmate-details-row');
        }
    };

    window.InmatesHelper = InmatesHelper;
})();
