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

    DashboardHelper.prototype.ucfirst = function (instr) {
        return instr.charAt(0).toUpperCase() + instr.slice(1);
    }

    /*
     *  Create a services select object
     */
    DashboardHelper.prototype.servicesSelect = function () {
        var instance = this;
        // Set up a provider select object
        let serviceSelect = "<select id='services'>";
        serviceSelect += "<option value='-1'>-- Please select a service --</option>";
        GlobalVariables.services.forEach(function(service) {
            let option = "<option value='" + service.id + "'>" + service.name + "</option>";
            serviceSelect +=  option;
        });
        serviceSelect += "</select>";
        return serviceSelect;
    };

    /*
     *  Create a provider select object
     */
    DashboardHelper.prototype.providerSelect = function () {
        var instance = this;
        // Set up a provider select object
        let provSelect = "<select id='providers'>";
        provSelect += "<option value='-1'>-- Please select a provider --</option>";
        GlobalVariables.providers.forEach(function(prov) {
            let option = "<option value='" + prov.provider_id + "'>" + prov.provider_first_name + " " + prov.provider_last_name + "</option>";
            provSelect +=  option;
        });
        provSelect += "</select>";
        return provSelect;
    };

    /*
     *  Create a inmate select object
     */
    DashboardHelper.prototype.inmateSelect = function () {
        var instance = this;

        // Break name into last name first, first middle
        let inmateLFM = new Array();
        GlobalVariables.inmates.forEach(function (inmate) {
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

        // Set up an inmate select object
        let inmateSelect = "<select id='inmates'>";
        inmateSelect += "<option value='-1'>-- Please select an inmate --</option>";
        inmatesSorted.forEach(function(inmate) {
            let option = "<option value='" + inmate.ID + "'>" + inmate.inmate_name + " (SO: " + inmate.SO + ")</option>";
            inmateSelect +=  option;
        });
        inmateSelect += "</select>";
        return inmateSelect;
    };

    /*
     *  Create a visitor select object
     */
    DashboardHelper.prototype.visitorSelect = function () {
        var instance = this;

        // Sort name into last, first alpha and format birthdate
        let visitorLF = new Array();
        GlobalVariables.visitors.forEach(function (visitor) {
            const lfname = instance.ucfirst(visitor.last_name.trim()) + ", " + instance.ucfirst(visitor.first_name.trim());
            visitor.lfname = lfname;
            const bdate = Date.parse(visitor.birthdate).toString('MM/dd/yyyy');
            visitor.bdate = bdate;
            visitorLF.push(visitor);
        }.bind(this));

        // Sort alpha by name
        const visitorsSorted = visitorLF.sort(function(a,b) {
            if (a.lfname > b.lfname) { return 1; }
            if (a.lfname < b.lfname) { return -1; }
            return 0;
        });

        // Set up a visitor select object
        let visitorSelect = "<select id='visitors'>";
        visitorSelect += "<option value='-1'>-- Please select a visitor --</option>";
        visitorsSorted.forEach(function(visitor) {
            let option = "<option value='" + visitor.id + "'>" + visitor.lfname + " (Birthdate: " + visitor.bdate + ")</option>";
            visitorSelect +=  option;
        });
        visitorSelect += "</select>";
        return visitorSelect;
    };

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

            // Hide any new appointment stuff
            $('#new-appointment-form').hide();
            $('#new-appointment-visitor-form').hide();

            const appointment_id = $(this).data('id');
            GlobalVariables.appointments.forEach(function (appointment) {
                if (appointment.id == appointment_id) {
//                    console.log("** APPT: " + JSON.stringify(appointment));
                    instance.displayAppointmentDetails(appointment);
                    $( "#appt-datepicker" ).datepicker();
                    $( "#start-timepicker" ).timepicker();
                    $( "#end-timepicker" ).timepicker();
                    const optText = appointment.provider_first_name.trim() + " " + appointment.provider_last_name.trim();
                    $("select#providers option").filter(function() {
                        return this.text == optText;
                    }).attr('selected', true);
                }
            });

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

        /**
         * Event: Click Add Appt button
         */
        $('#add-appointment').on('click', () => {
            // clear out any selected appointment
            $('#appointments .appointment-row').removeClass('selected');
            $('#appointment-details-row').empty();
            $('#appointment-visitor-row').empty();
            instance.resetForm();

            // display the informational form
            $('#new-appointment-form').show();
            $('#new-appointment-visitor-form').show();
            const today = new Date().toString('MM/dd/yyyy');
            $('#add-appt-datepicker').val(today);
            $('#add-appt-datepicker').datepicker();
            $('#add-appt-start-timepicker').timepicker();
            $('#add-appt-end-timepicker').timepicker();
            $('#add-appt-services').append(this.servicesSelect());
            $('#add-appt-providers').append(this.providerSelect());
            $('#add-appt-inmates').append(this.inmateSelect());
            $('#add-appt-select-existing-visitor').append(this.visitorSelect())

        });

        /**
         * Event: Add exisiting visitor
         */
        $('#add-appt-add-existing-visitor').on('click', () => {
            const visitor_id = $("select#visitors").find(":selected").val();
            let first_name = "";
            let last_name = "";
            let bdate = new Date().toString('MM/dd/yyyy');
            GlobalVariables.visitors.forEach(function(visitor) {
                if (visitor.id == visitor_id) {
                    first_name = visitor.first_name;
                    last_name = visitor.last_name;
                    bdate = Date.parse(visitor.birthdate).toString('MM/dd/yyyy');
                }
            });
            $('<div/>', {
                'style': 'white-space:nowrap;',
                'html': [
                    $('<div/>', {
                        'id': 'add-appt-new-visitor',
                        'name': 'add-appt-new-visitor-' + visitor_id,
                        'data-id': visitor_id,
                        'text': 'Name: ' + last_name + ', ' + first_name + ' (Birthdate: ' + bdate + ')'
                    }),
                    $('<input/>', {
                        'id': 'remove-new-visitor',
                        'name': 'remove-new-visitor-' + visitor_id,
                        'data-id': visitor_id,
                        'type': 'button',
                        'value': 'Remove'
                    })
                ]
            }).appendTo('#add-appt-current-visitor-list');
        });

        /**
         * Event: Add Appt save
         */
        $('#add-appt-save-button').on('click', () => {
            let appointment = new Object();
            const service_id = $("select#services").find(":selected").val();
            appointment.id_services = service_id;
            let duration = 20;
            GlobalVariables.services.forEach(function(service) {
                if (service.id == service_id) {
                    duration = service.duration;
                }
            });
            const start_date = $('#add-appt-datepicker').val();
            const start_time = $('#add-appt-start-timepicker').val();
            const end_time = Date.parse(start_time).addMinutes(duration);
            appointment.start_datetime = Date.parse(start_date).toString('yyyy-MM-dd') + " " + Date.parse(start_time).toString('HH:mm') + ":00";
            appointment.end_datetime = Date.parse(start_date).toString('yyyy-MM-dd') + " " + Date.parse(end_time).toString('HH:mm') + ":00";
            appointment.id_users_provider = $("select#providers").find(":selected").val();
            const inmate_id = $("select#inmates").find(":selected").val();
            appointment.id_inmate = inmate_id;
            let inmate_name = "";
            GlobalVariables.inmates.forEach(function(inmate) {
                if (inmate.id == inmate_id) {
                    inmate_name = inmate.inmate_name;
                }
            });
            appointment.inmate_name = inmate_name;

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_add_update_appointment';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                appointment_data: JSON.stringify(appointment)
            };

            $.post(url, data)
                .done(function (response) {
                    //console.log("*** Appointment Updated - " + JSON.stringify(response));
                    instance.refreshAppointments(appointment_id);
                }.bind(this));
            //
        });

        /**
         * Event: Add Appt Cancel
         */
        $('#add-appt-cancel-button').on('click', () => {
            // Hide any new appointment stuff
            $('#new-appointment-form').hide();
            $('#new-appointment-visitor-form').hide();
            $('#appointment-details-row').empty();
            $('#appointment-visitor-row').empty();
            instance.resetForm();
        });
    
        /**
         * Event: Click Update Appt button
         */
        $('#appointment-details-row').on('click', '#appt-update-button', function () {
            const appointment_id = $(this).data('id');

            let appointment = new Object();
            appointment.id = appointment_id;
            const start_date = $('#appt-datepicker').val();
            const start_time = $('#start-timepicker').val();
            const end_time = $('#end-timepicker').val();
            appointment.start_datetime = Date.parse(start_date).toString('yyyy-MM-dd') + " " + Date.parse(start_time).toString('HH:mm') + ":00";
            appointment.end_datetime = Date.parse(start_date).toString('yyyy-MM-dd') + " " + Date.parse(end_time).toString('HH:mm') + ":00";
            appointment.id_users_provider = $("select#providers").find(":selected").val();
            //console.log("*** Update Clicked: " + JSON.stringify(appointment));

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_add_update_appointment';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                appointment_data: JSON.stringify(appointment)
            };

            $.post(url, data)
                .done(function (response) {
                    //console.log("*** Appointment Updated - " + JSON.stringify(response));
                    instance.refreshAppointments(appointment_id);
                }.bind(this));
            //
        });

        /**
         * Event: Click Cancel Button
         */
        $('#appointment-details-row').on('click', '#appt-cancel-button', function () {
            const appointment_id = $(this).data('id');

            //console.log("*** Cancel Clicked: " + appointment_id);

            // Call to update the DB
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_toggle_appointment_canceled';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                appointment_id: appointment_id
            };
    
            $.post(url, data)
                .done(function (response) {
                    //console.log("*** Cancel Toggled - " + response.appointment.canceled);
                    instance.refreshAppointments(appointment_id);
                }.bind(this));
            //
        });
    };

    /**
     * Refresh the list of appointments
     */
    DashboardHelper.prototype.refreshAppointments = function (appointment_id) {
        const instance = this;

        // Call to reload the appointments list
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_get_appointments_by_date';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            appt_date: GlobalVariables.appt_date
        };

        $.post(url, data)
            .done(function (response) {
                //console.log("*** Appointments refreshed - " + JSON.stringify(response.appointments));
                GlobalVariables.appointments = response.appointments;
                GlobalVariables.sel_appt = appointment_id;
                // Clear out any existing data in case selected appointment has moved dates
                $('#appointment-details-row').empty();
                $('#appointment-visitor-row').empty();
                instance.resetForm();
            }.bind(this));
        //
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

            let canceledDiv = "<div></div>";
            if (appointment.canceled != null) {
                canceledDiv = "<div style='float:right;font-size:16px;color:darkred;'>CANCELED</div>";
            }

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
                    }).append(canceledDiv)
                ]
            })
            .appendTo('#appointments');

            // If we are coming in from the calendar, there may be a selected appointment
            if ((GlobalVariables.sel_appt) && (GlobalVariables.sel_appt == appointment.id)) {
                sel_appt_id = appointment.id;
            }
        });

        // select the appointment that was passed in and load the details && visitor data
        if (sel_appt_id !== 0) {
            $('#appointments.appointment-row').removeClass('selected');
            $('[name="appointment-row-' + sel_appt_id + '"]').addClass('selected');

            const appointment_id = sel_appt_id;
            GlobalVariables.appointments.forEach(function (appointment) {
                if (appointment.id == appointment_id) {
                    instance.displayAppointmentDetails(appointment);
                    $( "#appt-datepicker" ).datepicker();
                    $( "#start-timepicker" ).timepicker();
                    $( "#end-timepicker" ).timepicker();
                    let optText = appointment.provider_first_name + " " + appointment.provider_last_name;
                    $("select#providers option").filter(function() {
                        return this.text == optText;
                    }).attr('selected', true);
                }
            });

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
     * Display the appointment details in an editable form
     *
     * @param {Object} appointment Contains the appointment record data.
     */
    DashboardHelper.prototype.displayAppointmentDetails = function (appointment) {
        $('#appointment-details-row').empty();

        const appointment_id = appointment.id;

        $('<div/>', {
            'class': 'appointment-details col-md-12',
            'data-id': appointment_id,
            'html': [
                // Date
                $('<div/>', {                    
                    'text': 'Date: '
                }),
                $('<input/>', {
                    'id': 'appt-datepicker',
                    'type': 'text',
                    'value': Date.parse(appointment.start_datetime).toString('MM/dd/yyyy')
                }),
                // Start time
                $('<div/>', {
                    'text': 'Start Time: '
                }),
                $('<input/>', {
                    'id': 'start-timepicker',
                    'type': 'text',
                    'value': Date.parse(appointment.start_datetime).toString('HH:mm')
                }),
                // End Time
                $('<div/>', {
                    'text': 'End Time: '
                }),
                $('<input/>', {
                    'id': 'end-timepicker',
                    'type': 'text',
                    'value': Date.parse(appointment.end_datetime).toString('HH:mm')
                }),
                // Providers
                $('<div/>', {
                    'text': 'Phone: '
                }).append(this.providerSelect()),
                $('<div/>', {
                    'text': (appointment.canceled) ? 'Canceled Date: ' + Date.parse(appointment.canceled).toString('MM/dd/yyyy') : 'Canceled Date: N/A'
                }),
                $('<br/>'),
                $('<div/>', {
                    'id': 'appointment-details-buttons',
                    'html': [
                        // update button
                        $('<input/>', {
                            'id': 'appt-update-button',
                            'name': 'appt-update-button',
                            'data-id': appointment_id,
                            'type': 'button',
                            'value': 'Update',
                            'style': 'margin-right:10px;'
                        }),
                        // cancel button
                        $('<input/>', {
                            'id': 'appt-cancel-button',
                            'name': 'appt-cancel-button',
                            'data-id': appointment_id,
                            'type': 'button',
                            'value': (appointment.canceled) ? 'Un-cancel Appointment' : 'Cancel Appointment',
                            'class': 'btn-danger'
                        })
                    ]
                })
            ]
        }).appendTo('#appointment-details-row');
    
    };

    /**
     * Display the visitor records into the form for an appointment.
     *  NOTE: vistor data includes appointment_visitor columns
     *
     * @param {Object} visitors Contains the appointment visitors data.
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
                                                'data-id': appointment_id + '|' + visitor_id,
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
                                                'data-id': appointment_id + '|' + visitor_id,
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
            }).appendTo('#appointment-visitor-row');
        });
    };

    window.DashboardHelper = DashboardHelper;
})();
