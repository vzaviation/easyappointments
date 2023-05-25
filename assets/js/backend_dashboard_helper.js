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
     * DashboardHelper Class
     *
     * This class contains the methods that are used in the backend dashboard page.
     *
     * @class DashboardHelper
     */
    function DashboardHelper() {
        this.filterResults = {};
        this.filterLimit = 20;
    }

    /**
     * Binds the default event handlers of the backend customers page.
     */
    DashboardHelper.prototype.bindEventHandlers = function () {
        var instance = this;

        /**
         * Event: Click on an appointment to display the visitor details
         */
        $('#appointments').on('click', '.appointment-row', function () {
            $('#appointments .appointment-row').removeClass('selected');
            $(this).addClass('selected');

            const appointment_id = $(this).data('id');

            // Fetch the visitor info for this appointment ID
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_get_appointment_visitors';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                appointment_id: appointment_id
            };
            
            $.post(url, data)
                .done(function (response) {
                    const visitors = response;
                    //console.log("AV: " + JSON.stringify(visitors));
                    instance.displayVisitors(visitors);

                    // Check the checkboxes approriately
                    for (let visitor of visitors) {
                        //console.log("V: " + JSON.stringify(visitor));
                        const visitor_id = visitor.visitor_id;
                        const arrCBname = "visitor-arrived-check-" + visitor_id;
                        const arrCBchecked = (visitor.visitor_arrived && visitor.visitor_arrived == 1) ? true : false;
                        $('[name="' + arrCBname + '"]').prop('checked',arrCBchecked);
                        const flagCBname = "visitor-flag-check-" + visitor_id;
                        const flagCBchecked = (visitor.flag && visitor.flag == 1) ? true : false;
                        $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
                    }
                }.bind(this));

            //
        });

        /**
         * Event: Arrived "Click"
         */
        $('#appointment-visitor-row').on('change', '#visitor-arrived-check', function () {
            const checked = $(this).is(':checked');
            const app_visitor_id = $(this).data('id');
            const appVis = app_visitor_id.split("|");
            const appointment_id = appVis[0];
            const visitor_id = appVis[1];

            //console.log("*** ARRIVED AID: " + appointment_id + " / VID: " + visitor_id + " / checked " + checked);

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_set_visitor_arrived';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                appointment_id: appointment_id,
                visitor_id: visitor_id,
                checked: checked
            };
    
            $.post(url, data)
                .done(function (response) {
                    //console.log("*** Update Arrived - " + JSON.stringify(response));
                }.bind(this));
            //
        });

        /**
         * Event: Flag "Click"
         */
        $('#appointment-visitor-row').on('change', '#visitor-flag-check', function () {
            const checked = $(this).is(':checked');
            const app_visitor_id = $(this).data('id');
            const appVis = app_visitor_id.split("|");
            const appointment_id = appVis[0];
            const visitor_id = appVis[1];

            // Save the flag notes here also, if any
            const flag_notes = $('[name="visitor-flag-notes-' + visitor_id + '"]').val();
            //console.log("*** FLAG Visitor ID: " + visitor_id + " / checked " + checked);

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_set_visitor_flag';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                appointment_id: appointment_id,
                visitor_id: visitor_id,
                checked: checked,
                flag_notes: flag_notes
            };
    
            $.post(url, data)
                .done(function (response) {
                    const visitors = response;
                    //console.log("AV: " + JSON.stringify(visitors));
                    instance.displayVisitors(visitors);

                    // Check the checkboxes approriately
                    for (let visitor of visitors) {
                        //console.log("V: " + JSON.stringify(visitor));
                        const visitor_id = visitor.visitor_id;
                        const arrCBname = "visitor-arrived-check-" + visitor_id;
                        const arrCBchecked = (visitor.visitor_arrived && visitor.visitor_arrived == 1) ? true : false;
                        $('[name="' + arrCBname + '"]').prop('checked',arrCBchecked);
                        const flagCBname = "visitor-flag-check-" + visitor_id;
                        const flagCBchecked = (visitor.flag && visitor.flag == 1) ? true : false;
                        $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
                    }
                }.bind(this));
            //
        });

        /**
         * Event: Save Flag Notes
         */
        $('#appointment-visitor-row').on('click', '#visitor-flag-notes-save', function () {
            const app_visitor_id = $(this).data('id');
            const appVis = app_visitor_id.split("|");
            const appointment_id = appVis[0];
            const visitor_id = appVis[1];
            const flag_notes = $('[name="visitor-flag-notes-' + visitor_id + '"]').val();

            //console.log("AID: " + appointment_id + " / VID: " + visitor_id + " / FN: " + flag_notes);

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_visitor_flag_notes';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                appointment_id: appointment_id,
                visitor_id: visitor_id,
                flag_notes: flag_notes
            };
    
            $.post(url, data)
                .done(function (response) {
                    const visitors = response;
                    //console.log("AV: " + JSON.stringify(visitors));
                    instance.displayVisitors(visitors);

                    // Check the checkboxes approriately
                    for (let visitor of visitors) {
                        //console.log("V: " + JSON.stringify(visitor));
                        const visitor_id = visitor.visitor_id;
                        const arrCBname = "visitor-arrived-check-" + visitor_id;
                        const arrCBchecked = (visitor.visitor_arrived && visitor.visitor_arrived == 1) ? true : false;
                        $('[name="' + arrCBname + '"]').prop('checked',arrCBchecked);
                        const flagCBname = "visitor-flag-check-" + visitor_id;
                        const flagCBchecked = (visitor.flag && visitor.flag == 1) ? true : false;
                        $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
                    }
                }.bind(this));
            //
        });
    };

    /**
     * Save a visitor record to the database (via ajax post).
     *
     * @param {Object} visitor Contains the visitor data.
     */
    DashboardHelper.prototype.save = function (visitor) {
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_visitor';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            visitor: JSON.stringify(visitor)
        };

        $.post(url, data)
            .done(function (response) {
                Backend.displayNotification(EALang.customer_saved);
                this.resetForm();
            }.bind(this));
    };


    /**
     * Bring the dashboard form back to its initial state.
     */
    DashboardHelper.prototype.resetForm = function () {
        var instance = this;
        $('#appointments').empty();

        if (! GlobalVariables.appointments.length) {
            $('<p/>', {
                'text': 'No appointments scheduled for this date'
            })
                .appendTo('#appointments');
        }

        let sel_appt_id = 0;
        GlobalVariables.appointments.forEach(function (appointment) {

            var start = Date.parse(appointment.start_datetime).toString('HH:mm');
            var end = Date.parse(appointment.end_datetime).toString('HH:mm');

            $('<div/>', {
                'class': 'appointment-row',
                'name': 'appointment-row-' + appointment.id,
                'data-id': appointment.id,
                'html': [
                    // Time and Service
                    $('<div/>', {
                        'text': start + ' to ' + end + ' -- ' + appointment.service_name
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
            })
            .appendTo('#appointments');

            // If we are coming in from the calendar, there may be a selected appointment
            if ((GlobalVariables.sel_appt) && (GlobalVariables.sel_appt == appointment.id)) {
                sel_appt_id = appointment.id;
            }
        });

        // select the appointment that was passed in and load the visitor data
        if (sel_appt_id !== 0) {
            $('#appointments.appointment-row').removeClass('selected');
            $('[name="appointment-row-' + sel_appt_id + '"]').addClass('selected');

            const appointment_id = sel_appt_id;

            // Fetch the visitor info for this appointment ID
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_get_appointment_visitors';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                appointment_id: appointment_id
            };
            
            $.post(url, data)
                .done(function (response) {
                    const visitors = response;
                    //console.log("AV: " + JSON.stringify(visitors));
                    instance.displayVisitors(visitors);

                    // Check the checkboxes approriately
                    for (let visitor of visitors) {
                        //console.log("V: " + JSON.stringify(visitor));
                        const visitor_id = visitor.visitor_id;
                        const arrCBname = "visitor-arrived-check-" + visitor_id;
                        const arrCBchecked = (visitor.visitor_arrived && visitor.visitor_arrived == 1) ? true : false;
                        $('[name="' + arrCBname + '"]').prop('checked',arrCBchecked);
                        const flagCBname = "visitor-flag-check-" + visitor_id;
                        const flagCBchecked = (visitor.flag && visitor.flag == 1) ? true : false;
                        $('[name="' + flagCBname + '"]').prop('checked',flagCBchecked);
                    }
                }.bind(this));
        }
    };

    /**
     * Display the visitor records into the form for an appointment.
     *  NOTE: vistor data includes appointment_visitor columns
     *
     * @param {Object} appointment Contains the appointment record data.
     */
    DashboardHelper.prototype.displayVisitors = function (visitors) {
        $('#appointment-visitor-row').empty();

        if (!visitors.length) {
            $('<p/>', {
                'text': EALang.no_records_found
            })
                .appendTo('#appointment-visitor-row');
        }

        visitors.forEach(function (visitor, ind) {
            const appointment_id = visitor.appointment_id;
            const visitor_id = visitor.visitor_id;
            const vIDNum = visitor.id_number ? visitor.id_number : "N/A";
            const vIDState = visitor.id_state ? visitor.id_state : "N/A";
            const vIDImageSrc = visitor.id_image_filename ? '/storage/uploads/user_doc/' + visitor.id_image_filename : '/assets/img/no-ID.jpg';
            const vFlagDate = visitor.flag_date ? Date.parse(visitor.flag_date).toString('MM/dd/yyyy') : "N/A";
            if (ind === 0) {
                $('<div/>', {
                    'class': 'visitor-info col-md-12',
                    'data-id': visitor_id,
                    'html': [
                        $('<div/>', {
                            'class': 'visitor-info-left col-md-3',
                            'style': 'float:left;',
                            'data-id': visitor_id,
                            'html': [
                                $('<div/>', {
                                    'class': 'visitor-arrived',
                                    'html': [
                                        $('<strong/>', {
                                            'text': 'Arrived: '
                                        }),
                                        $('<input/>', {
                                            'id': 'visitor-arrived-check',
                                            'name': 'visitor-arrived-check-' + visitor_id,
                                            'data-id': appointment_id + '|' + visitor_id,
                                            'type': 'checkbox'
                                        })        
                                    ]
                                }),
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
                                    'text': visitor.phone
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
                                })
                            ]
                        }),
                        $('<div/>', {
                            'class': 'visitor-id col-md-3',
                            'style': 'float:left;',
                            'data-id': visitor_id,
                            'html': [
                                // ID Image
                                $('<img/>', {
                                    'id': 'v1-id-image-file',
                                    'width': '250',
                                    'src': vIDImageSrc
                                })
                            ]
                        }),
                        $('<div/>', {
                            'class': 'visitor-notes col-md-5',
                            'data-id': visitor_id,
                            'html': [
                                $('<div/>', {
                                    'class': 'visitor-flag col-md-2',
                                    'data-id': visitor_id,
                                    'html': [
                                        $('<span/>', {
                                            'class': 'visitor-flag-check',
                                            'text': 'Flag: '
                                        }),
                                        $('<input/>', {
                                            'id': 'visitor-flag-check',
                                            'name': 'visitor-flag-check-' + visitor_id,
                                            'data-id': appointment_id + '|' + visitor_id,
                                            'type': 'checkbox'
                                        })
                                    ]
                                }),
                                $('<div/>', {
                                    'class': 'visitor-flag-notes col-md-4',
                                    'data-id': visitor_id,
                                    'html': [
                                        $('<span/>', {
                                            'class': 'visitor-flag-check',
                                            'text': 'Date Flagged: ' + vFlagDate
                                        }),
                                        $('<br/>'),
                                        $('<span/>', {
                                            'class': 'visitor-flag-check',
                                            'text': 'Flag Notes: '
                                        }),
                                        $('<textarea/>', {
                                            'id': 'visitor-flag-notes',
                                            'name': 'visitor-flag-notes-' + visitor_id,
                                            'text': visitor.flag_notes,
                                            'rows': 4,
                                            'cols': 30
                                        }),
                                        $('<input/>', {
                                            'id': 'visitor-flag-notes-save',
                                            'name': 'visitor-flag-notes-save-' + visitor_id,
                                            'data-id': appointment_id + '|' + visitor_id,
                                            'type': 'button',
                                            'value': 'Save Notes'
                                        })
                                    ]
                                })
                            ]
                        })
                    ]
                }).appendTo('#appointment-visitor-row');



            } else {
                $('<div/>', {
                    'class': 'visitor-info col-md-12',
                    'data-id': visitor_id,
                    'html': [
                        $('<div/>', {
                            'class': 'visitor-info-left col-md-3',
                            'style': 'float:left;',
                            'data-id': visitor_id,
                            'html': [
                                $('<div/>', {
                                    'class': 'visitor-arrived',
                                    'html': [
                                        $('<strong/>', {
                                            'text': 'Arrived: '
                                        }),
                                        $('<input/>', {
                                            'id': 'visitor-arrived-check',
                                            'name': 'visitor-arrived-check-' + visitor_id,
                                            'data-id': appointment_id + '|' + visitor_id,
                                            'type': 'checkbox'
                                        })        
                                    ]
                                }),
                                // Name
                                $('<div/>', {
                                    'text': visitor.last_name + ', ' + visitor.first_name
                                }),
                                // DOB
                                $('<div/>', {
                                    'text': 'Birth Date: ' + Date.parse(visitor.birthdate).toString('MM/dd/yyyy')
                                }),
                                $('<br/>'),
                                // ID Number
                                $('<div/>', {
                                    'text': 'ID Number: ' + vIDNum
                                }),
                                // ID State
                                $('<div/>', {
                                    'text': 'ID State: ' + vIDState
                                })
                            ]
                        }),
                        $('<div/>', {
                            'class': 'visitor-id col-md-3',
                            'style': 'float:left;',
                            'data-id': visitor_id,
                            'html': [
                                // ID Image
                                $('<img/>', {
                                    'id': 'v1-id-image-file',
                                    'width': '250',
                                    'src': vIDImageSrc
                                })
                            ]
                        }),
                        $('<div/>', {
                            'class': 'visitor-notes col-md-5',
                            'data-id': visitor_id,
                            'html': [
                                $('<div/>', {
                                    'class': 'visitor-flag col-md-2',
                                    'data-id': visitor_id,
                                    'html': [
                                        $('<span/>', {
                                            'class': 'visitor-flag-check',
                                            'text': 'Flag: '
                                        }),
                                        $('<input/>', {
                                            'id': 'visitor-flag-check',
                                            'name': 'visitor-flag-check-' + visitor_id,
                                            'data-id': appointment_id + '|' + visitor_id,
                                            'type': 'checkbox'
                                        })
                                    ]
                                }),
                                $('<div/>', {
                                    'class': 'visitor-flag-notes col-md-4',
                                    'data-id': visitor_id,
                                    'html': [
                                        $('<span/>', {
                                            'class': 'visitor-flag-check',
                                            'text': 'Date Flagged: ' + vFlagDate
                                        }),
                                        $('<br/>'),
                                        $('<span/>', {
                                            'class': 'visitor-flag-check',
                                            'text': 'Flag Notes: '
                                        }),
                                        $('<textarea/>', {
                                            'id': 'visitor-flag-notes',
                                            'name': 'visitor-flag-notes-' + visitor_id,
                                            'text': visitor.flag_notes,
                                            'rows': 4,
                                            'cols': 30
                                        }),
                                        $('<input/>', {
                                            'id': 'visitor-flag-notes-save',
                                            'name': 'visitor-flag-notes-save-' + visitor_id,
                                            'data-id': appointment_id + '|' + visitor_id,
                                            'type': 'button',
                                            'value': 'Save Notes'
                                        })
                                    ]
                                })
                            ]
                        })
                    ]
                }).appendTo('#appointment-visitor-row');
            }
        });
    };

    window.DashboardHelper = DashboardHelper;
})();
