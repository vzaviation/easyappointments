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
        this.filterLimit = 2000;
        this.pageLimit = 20;
    }

    /*
     *  Create a relationship select object
     */
    InmatesHelper.prototype.relationshipSelect = function (index) {
        var instance = this;
        // Set up a relationship select object
        let relationshipSelect = "<select id='relationships-" + index + "'>";
        relationshipSelect += "<option value='-1'>-- Please select a relationship --</option>";
        GlobalVariables.relationships.forEach(function(relationship) {
            let option = "<option value='" + relationship.id + "'>" + relationship.visitor_relationship + "</option>";
            relationshipSelect +=  option;
        });
        relationshipSelect += "</select>";
        return relationshipSelect;
    };

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
            const housed = $('#filter-by-housed').is(':checked');
            event.preventDefault();
            var key = $('#filter-inmates .key').val();
            $('#filter-inmates .selected').removeClass('selected');
            instance.resetForm();
            instance.filter(key,housed);
        });

        /**
         * Event: Filter Inmates Clear Button "Click"
         */
        $('#inmates').on('click', '#filter-inmates .clear', function () {
            $('#filter-by-housed').prop('checked',false);
            $('#filter-inmates .key').val('');
            instance.filter('');
            instance.resetForm();
        });

        /**
         * Event: Filter by Housed Checkbox
         *
         * Display only the inmates that are currently resident
         */
        $('#filter-inmates').on('change', '#filter-by-housed', function () {
            const housed = $(this).is(':checked');

            event.preventDefault();
            var key = $('#filter-inmates .key').val();
            $('#filter-inmates .selected').removeClass('selected');
            instance.resetForm();
            instance.filter(key,housed);
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

            instance.inmateVisitors(inmateId);
        });

        /**
         * Event: Flag "Click"
         */
        $('#inmate-details').on('change', '#inmate-flag-check', function () {
            const checked = $(this).is(':checked');
            const inmate_id = $(this).data('id');

            // Save the flag notes here also, if any
            const flag_notes = $('[name="inmate-flag-notes-' + inmate_id + '"]').val();
//            console.log("*** FLAG Inmate ID: " + inmate_id + " / checked " + checked);

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
                    const housed = $('#filter-by-housed').is(':checked');
                    const key = $('#filter-inmates .key').val();
                    $('#filter-inmates .selected').removeClass('selected');
                    instance.resetForm();
                    instance.filter(key,housed);

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
                    const housed = $('#filter-by-housed').is(':checked');
                    const key = $('#filter-inmates .key').val();
                    $('#filter-inmates .selected').removeClass('selected');
                    instance.resetForm();
                    instance.filter(key,housed);

                    instance.displayInmate(inmate);

                    const inmate_id = inmate.ID;
                    const flagCBname = "inmate-flag-check-" + inmate_id;
                    const flagCBchecked = (inmate.inmate_flag && inmate.inmate_flag == 1) ? true : false;
                    $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
                }.bind(this));
            //
        });

        /*
         * Save the inmate visitors
         */
        $('#inmate-info-visitors').on('click', '#visitor-button', function () {
            // There are 5 form field rows
            // Loop through and build a visitor object array from each
            let visitors = new Array();
            let inmateId = -1;
            for (let i = 0; i < 5; i++) {
                const inmate_id = $('#visitor-inmate-id-' + i).val();
                const vid = $('#visitor-inmate-id-' + i).data('id');
                const first_name = $('#visitor-first-name-' + i).val();
                const last_name = $('#visitor-last-name-' + i).val();
                if ((first_name != "") && (last_name != "")) {
                    const number = i + 1;
                    const relationship_id = $("select#relationships-" + i).find(":selected").val();
                    //console.log("** TEST: " + i + ":" + inmate_id + "/" + vid + "/" + first_name + "/" + last_name + "/" + number + "/RID " + relationship_id);
                    let effective_date = $('#visitor-ed-datepicker-' + i).val();

                    let visitor = new Object();
                    visitor.inmate_id = inmate_id;
                    inmateId = inmateId == -1 ? inmate_id : inmateId;
                    visitor.id = vid != -1 ? vid : null;
                    visitor.effective_date = Date.parse(effective_date).toString('yyyy-MM-dd');
                    let obsolete_date = new Date(effective_date);
                    obsolete_date.setMonth(obsolete_date.getMonth() + 6);
                    visitor.obsolete_date = Date.parse(obsolete_date).toString('yyyy-MM-dd');
                    visitor.visitor_first_name = first_name;
                    visitor.visitor_last_name = last_name;
                    visitor.visitor_number = number == null ? number : 1;
                    visitor.visitor_relationship_id = parseInt(relationship_id);

                    visitors.push(visitor);
                }
            }
            //console.log("Visitors: " + JSON.stringify(visitors));

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_inmate_visitors';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                visitors: JSON.stringify(visitors)
            };
    
            $.post(url, data)
                .done(function (response) {
                    const visitor = response.visitor;
                    //console.log("*** Inmate Visitor after save for inmate_id=" + inmateId + " - " + JSON.stringify(visitor));
                    // refresh visitor list
                    instance.inmateVisitors(inmateId);
                }.bind(this));
        });

        /**
         * Event: Visitor delete
         */
        $('#inmate-info-visitors').on('click', '[name="visitor-delete"]', function () {
            const visitorId = $(this).data('id');
            const inmateId = $(this).data('inmateid');
            console.log("IID: " + inmateId + " VID: " + visitorId);

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_delete_inmate_visitor';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                visitor_id: visitorId
            };

            $.post(url, data)
                .done(function (response) {
                        console.log("DEL VIS: " + JSON.stringify(response));

                    // refresh the visitor list
                    instance.inmateVisitors(inmateId);

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

        const housed = $('#filter-by-housed').is(':checked');

        $.post(url, data)
            .done(function (response) {
                Backend.displayNotification(EALang.inmate_saved);
                this.resetForm();
                $('#filter-inmates .key').val('');
                this.filter('', housed, response.id, true);
            }.bind(this));
    };

    /**
     * Delete an inmate record from database.
     *
     * @param {Number} id Record id to be deleted.
     */
    InmatesHelper.prototype.delete = function (id) {
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_delete_inmate';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            inmate_id: id
        };
        const housed = $('#filter-by-housed').is(':checked');

        $.post(url, data)
            .done(function () {
                Backend.displayNotification(EALang.inmate_deleted);
                this.resetForm();
                this.filter($('#filter-inmates .key').val(),housed);
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
     * @param {Boolean} housed Optional (false), if true then only currently resident inmates will be shown
     * @param {Number} selectId Optional, if set then after the filter operation the record with the given
     * ID will be selected (but not displayed).
     * @param {Boolean} display Optional (false), if true then the selected record will be displayed on the form.
     */
    InmatesHelper.prototype.filter = function (key, housed, selectId, display) {
        var instance = this;

        housed = housed || false;
        display = display || false;

        // Clear selected and remove any current data
        $('#filter-inmates .selected').removeClass('selected');
        $('#inmate-details-row').empty();

        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_filter_inmates';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            key: key,
            limit: instance.filterLimit,
            housed
        };

        $.post(url, data)
            .done(function (response) {
                this.filterResults = response;

                $('#filter-inmates .results').empty();

                // Break name into last name first, first middle
                let inmateLFM = new Array();
                response.forEach(function (inmate) {
                    let fmlname = inmate.inmate_name.trim().toLowerCase();
                    fmlname = fmlname.replace('/\s+/', ' ');
                    const nameparts = fmlname.split(' ');
                    let inmateLastFirst = fmlname;
                    if (nameparts.length > 3) {
                        inmateLastFirst = instance.ucfirst(nameparts[nameparts.length - 1]) + ", ";
                        for (let i = 0; i < nameparts.length - 1; i++) {
                            inmateLastFirst += instance.ucfirst(nameparts[i]) + " ";
                        }
                    } else if (nameparts.length == 3) {
                        inmateLastFirst = instance.ucfirst(nameparts[2]) + ", " + instance.ucfirst(nameparts[0]) + " " + instance.ucfirst(nameparts[1]);
                    } else if (nameparts.length == 2) {
                        inmateLastFirst = instance.ucfirst(nameparts[1]) + ", " + instance.ucfirst(nameparts[0]);
                    }
                    inmate.inmate_name = inmateLastFirst;
                    inmateLFM.push(inmate);
                }.bind(this));

                // Sort alpha by name
                const inmatesSorted = inmateLFM.sort(function(a,b) {
                    if (a.inmate_name > b.inmate_name) { return 1; }
                    if (a.inmate_name < b.inmate_name) { return -1; }
                    return 0;
                });
                const housed = $('#filter-by-housed').is(':checked');

                inmatesSorted.slice(0, this.pageLimit).forEach(function (inmate) {
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
                } else {
                    $('<button/>', {
                        'type': 'button',
                        'class': 'btn btn-block btn-outline-secondary load-more text-center',
                        'text': EALang.load_more,
                        'click': function () {
                            this.pageLimit += this.pageLimit;
                            this.filter(key, housed, selectId, display);
                        }.bind(this)
                    })
                        .appendTo('#filter-inmates .results');
                }

                if (selectId) {
                    this.select(selectId, display);
                }

            }.bind(this));
    };

    InmatesHelper.prototype.ucfirst = function (instr) {
        return instr.charAt(0).toUpperCase() + instr.slice(1);
    }

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
        const housed = inmate.booking_status == 1 ? "HOUSED" : "";

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
                $('<span/>', {
                    'style': 'padding-left:10px;text-align:right;color:darkblue;',
                    'text': housed
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
            const inmateId = id;

            // NOTE: in inmates table, id is ID (capitalized)
            const inmate_data = instance.filterResults.find(function (filterResult) {
                return Number(filterResult.ID) === Number(inmateId);
            });

            $('#inmates .inmate-row').removeClass('selected');
            $(this).addClass('selected');

            instance.displayInmate(inmate_data);

            // Check the checkboxes approriately
            const flagCBname = "inmate-flag-check-" + inmateId;
            const flagCBchecked = (inmate_data.inmate_flag && inmate_data.inmate_flag == 1) ? true : false;
            $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);

            instance.inmateVisitors(inmateId);
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
                                    $('<div/>', {
                                        'style': 'text-align:center;',
                                        'html': [
                                            $('<input/>', {
                                                'id': 'inmate-flag-notes-save',
                                                'name': 'inmate-flag-notes-save-' + inmate_id,
                                                'data-id': inmate_id,
                                                'type': 'button',
                                                'value': 'Save Notes',
                                                'style': 'text-align:right;'
                                            })
                                        ]
                                    })
                                ]
                            }),
                        ]
                    })
                ]
            }).appendTo('#inmate-details-row');
        }
    };

    InmatesHelper.prototype.inmateVisitors = function (inmate_id) {
        const instance = this;
        // Fetch the inmate_visitor list and display
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_fetch_inmate_visitors';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            inmate_id: inmate_id
        };

        $.post(url, data)
            .done(function (response) {
                // Reset existing grid
                for (let i = 0;i < 5;i++) {
                    $('#inmate-visitor-' + i).remove();
                }
                $('#inmate-visitor-save').remove();
                // now show new / data
                $('#inmate-info-visitors').show();
                $('#visitor-save-button').show();
                $('#visitor-save-button').val("Update / Save");
                $('#visitor-save-button').removeAttr('disabled');
                const visitors = response.visitors;
                //console.log("*** Inmate Visitors for inmate_id=" + inmate_id + " - " + JSON.stringify(visitors));
                /* KPB 2023-08-28 comment out for per-visitor dates
                if ((visitors.length > 0) && (visitors[0].effective_date != null)) {
                    $('#visitor-ed-datepicker').val(Date.parse(visitors[0].effective_date).toString('MM/dd/yyyy'));
                    $('#visitor-od-date').text(Date.parse(visitors[0].obsolete_date).toString('MM/dd/yyyy'));
                } else {
                    $('#visitor-ed-datepicker').val(new Date().toString('MM/dd/yyyy'));
                }
                */
                for (let i = 0; i < 5; i++) {
                    if (visitors[i]) {
                        const visitor = visitors[i];
                        instance.displayInmateVisitors(inmate_id, visitor, i);
                        $('#visitor-ed-datepicker-' + i).val(Date.parse(visitors[i].effective_date).toString('MM/dd/yyyy'));
                        $('#visitor-od-date-' + i).text(Date.parse(visitors[i].obsolete_date).toString('MM/dd/yyyy'));
                    } else {
                        instance.displayInmateVisitors(inmate_id,null, i);
                        $('#visitor-ed-datepicker-' + i).val(new Date().toString('MM/dd/yyyy'));
                    }
                    $('#visitor-ed-datepicker-' + i).datepicker();
                    $('#visitor-ed-datepicker-' + i).removeAttr('disabled');
                }

            }.bind(this));
            
    };

    /**
     * Display the inmate visitor records
     *
     * @param {Object} visitor Contains the inmate visitor data.
     */
    InmatesHelper.prototype.displayInmateVisitors = function (inmate_id, visitor, index) {
        if (!visitor) {
            const vis_id = -1;
            // display a blank form
            $('<div/>', {
                'id': 'inmate-visitor-' + index,
                'style': 'width:800px;',
                'html': [
                    $('<span/>', {
                        'id': 'visitor-index-' + index,
                        'name': 'visitor-index-' + index,
                        'data-id': vis_id,
                        'text': (index + 1) + ". "
                    }),
                    // First Name
                    $('<span/>', {
                        'id': 'visitor-fn-label-' + index,
                        'name': 'visitor-fn-label-' + index,
                        'data-id': vis_id,
                        'text': "First Name: "
                    }),
                    $('<input/>', {
                        'id': 'visitor-first-name-' + index,
                        'name': 'visitor-first-name-' + index,
                        'data-id': vis_id,
                        'type': 'text',
                        'size': 12,
                        'style': 'margin-left:6px;margin-right:10px;'
                    }),
                    // Last Name
                    $('<span/>', {
                        'id': 'visitor-ln-label-' + index,
                        'name': 'visitor-ln-label-' + index,
                        'data-id': vis_id,
                        'text': "Last Name: "
                    }),
                    $('<input/>', {
                        'id': 'visitor-last-name-' + index,
                        'name': 'visitor-last-name-' + index,
                        'data-id': vis_id,
                        'type': 'text',
                        'size': 20,
                        'style': 'margin-left:6px;margin-right:10px;'
                    }),
                    // Relationship
                    $('<span/>', {
                        'id': 'visitor-rel-label-' + index,
                        'name': 'visitor-rel-label-' + index,
                        'data-id': vis_id,
                        'text': "Relationship: "
                    }),
                    $('<span/>', {
                        'style': 'margin:4px;'
                    }).append(this.relationshipSelect(index)),
                    $('<br/>'),
                    // Effective Date
                    $('<span/>', {
                        'id': 'visitor-ed-label-' + index,
                        'name': 'visitor-ed-label-' + index,
                        'data-id': vis_id,
                        'text': "Effective: ",
                        'style': 'margin-left:198px;'
                    }),
                    $('<input/>', {
                        'id': 'visitor-ed-datepicker-' + index,
                        'name': 'visitor-ed-datepicker-' + index,
                        'data-id': vis_id,
                        'type': 'text',
                        'size': 10,
                        'style': 'margin:6px;'
                    }),
                    // Obsolete Date
                    $('<span/>', {
                        'id': 'visitor-od-label-' + index,
                        'name': 'visitor-od-label-' + index,
                        'data-id': vis_id,
                        'text': "Obsolete: ",
                        'style': 'margin-left:6px;margin-right:10px;'
                    }),
                    $('<span/>', {
                        'id': 'visitor-od-date-' + index,
                        'name': 'visitor-od-date-' + index,
                        'data-id': vis_id,
                        'text': ""
                    }),
                    // inmate_id (hidden)
                    $('<input/>', {
                        'id': 'visitor-inmate-id-' + index,
                        'name': 'visitor-inmate-id-' + index,
                        'data-id': vis_id,
                        'type': 'hidden',
                        'value': inmate_id
                    }),
                    // visitor_number (hidden)
                    $('<input/>', {
                        'id': 'visitor-number-' + index,
                        'name': 'visitor-number-' + index,
                        'data-id': vis_id,
                        'type': 'hidden',
                        'value': (index + 1)
                    })
                ]
            }).appendTo('#inmate-info-visitors');
        } else {
            const vis_id = visitor.id;
            $('<div/>', {
                'id': 'inmate-visitor-' + index,
                'style': 'width:800px;',
                'html': [
                    $('<span/>', {
                        'id': 'visitor-index-' + index,
                        'name': 'visitor-index-' + index,
                        'data-id': vis_id,
                        'text': (index + 1) + ". "
                    }),
                    // First Name
                    $('<span/>', {
                        'id': 'visitor-fn-label-' + index,
                        'name': 'visitor-fn-label-' + index,
                        'data-id': vis_id,
                        'text': "First Name: "
                    }),
                    $('<input/>', {
                        'id': 'visitor-first-name-' + index,
                        'name': 'visitor-first-name-' + index,
                        'data-id': vis_id,
                        'type': 'text',
                        'size': 12,
                        'value': visitor.visitor_first_name,
                        'style': 'margin-left:6px;margin-right:10px;'
                    }),
                    // Last Name
                    $('<span/>', {
                        'id': 'visitor-ln-label-' + index,
                        'name': 'visitor-ln-label-' + index,
                        'data-id': vis_id,
                        'text': "Last Name: "
                    }),
                    $('<input/>', {
                        'id': 'visitor-last-name-' + index,
                        'name': 'visitor-last-name-' + index,
                        'data-id': vis_id,
                        'type': 'text',
                        'size': 20,
                        'value': visitor.visitor_last_name,
                        'style': 'margin-left:6px;margin-right:10px;'
                    }),
                    // Relationship
                    $('<span/>', {
                        'id': 'visitor-rel-label-' + index,
                        'name': 'visitor-rel-label-' + index,
                        'data-id': vis_id,
                        'text': "Relationship: "
                    }),
                    $('<span/>', {
                        'style': 'margin:4px;'
                    }).append(this.relationshipSelect(index)),
                    // Delete
                    $('<a/>', {
                        'id': 'visitor-delete-' + index,
                        'name': 'visitor-delete',
                        'href': '#',
                        'data-id': vis_id,
                        'data-inmateid': inmate_id,
                        'text': "X",
                        'style': 'padding-left:10px;font-weight:bold;color:darkred;'
                    }),
                    $('<br/>'),
                    // Effective Date
                    $('<span/>', {
                        'id': 'visitor-ed-label-' + index,
                        'name': 'visitor-ed-label-' + index,
                        'data-id': vis_id,
                        'text': "Effective: ",
                        'style': 'margin-left:198px;'
                    }),
                    $('<input/>', {
                        'id': 'visitor-ed-datepicker-' + index,
                        'name': 'visitor-ed-datepicker-' + index,
                        'data-id': vis_id,
                        'type': 'text',
                        'size': 10,
                        'style': 'margin:6px;'
                    }),
                    // Obsolete Date
                    $('<span/>', {
                        'id': 'visitor-od-label-' + index,
                        'name': 'visitor-od-label-' + index,
                        'data-id': vis_id,
                        'text': "Obsolete: ",
                        'style': 'margin-left:6px;margin-right:10px;'
                    }),
                    $('<span/>', {
                        'id': 'visitor-od-date-' + index,
                        'name': 'visitor-od-date-' + index,
                        'data-id': vis_id,
                        'text': ""
                    }),
                    // inmate_id (hidden)
                    $('<input/>', {
                        'id': 'visitor-inmate-id-' + index,
                        'name': 'visitor-inmate-id-' + index,
                        'data-id': vis_id,
                        'type': 'hidden',
                        'value': inmate_id
                    }),
                    // visitor_number (hidden)
                    $('<input/>', {
                        'id': 'visitor-number-' + index,
                        'name': 'visitor-number-' + index,
                        'data-id': vis_id,
                        'type': 'hidden',
                        'value': visitor.visitor_number

                    })
                ]
            }).appendTo('#inmate-info-visitors');
            // Set the selected relationship value
            const relId = "select#relationships-" + index + " option";
            $(`${relId}`).filter(function () {
                return this.value == visitor.visitor_relationship_id;
            }).attr('selected', true);
        }
        if (index == 4) {
            $('<div/>', {
                'id': 'inmate-visitor-save',
                'class': 'col-md-6',
                'style': 'text-align:right;padding:4px;',
                'html': [
                    // update button
                    $('<input/>', {
                        'id': 'visitor-button',
                        'name': 'visitor-button',
                        'type': 'button',
                        'value': 'Update / Save'
                    })
                ]
            }).appendTo('#inmate-info-visitors');
        }
    };
    
    window.InmatesHelper = InmatesHelper;
})();
