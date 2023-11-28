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

window.FrontendBookApi = window.FrontendBookApi || {};

/**
 * Frontend Book API
 *
 * This module serves as the API consumer for the booking wizard of the app.
 *
 * @module FrontendBookApi
 */
(function (exports) {

    'use strict';

    var unavailableDatesBackup;
    var selectedDateStringBackup;
    var processingUnavailabilities = false;

    /**
     * Get Available Hours
     *
     * This function makes an AJAX call and returns the available hours for the selected service,
     * provider and date.
     *
     * @param {String} selectedDate The selected date of the available hours we need.
     */
    exports.getAvailableHours = function (selectedDate) {
        $('#available-hours').empty();

        // Find the selected service duration (it is going to be send within the "data" object).
        var serviceId = $('#select-service').val();

        // Default value of duration (in minutes).
        var serviceDuration = 20;

        var service = GlobalVariables.availableServices.find(function (availableService) {
            return Number(availableService.id) === Number(serviceId);
        });

        if (service) {
            serviceDuration = service.duration;
        }

        // If the manage mode is true then the appointment's start date should return as available too.
        var appointmentId = FrontendBook.manageMode ? GlobalVariables.appointmentData.id : null;

        // Make ajax post request and get the available hours.
        var url = GlobalVariables.baseUrl + '/index.php/appointments/ajax_get_available_hours';

        var inmateId = $('#select-inmate').val();

        const providerId = $('#select-provider').val() ? $('#select-provider').val() : -1;
        var data = {
            csrfToken: GlobalVariables.csrfToken,
            service_id: serviceId,
            provider_id: providerId,
            selected_date: selectedDate,
            inmate_id: inmateId,
            service_duration: serviceDuration,
            manage_mode: FrontendBook.manageMode,
            appointment_id: appointmentId
        };
        $("#loading").css("display", "");
        $.post(url, data)
            .done(function (response) {
                // The response contains the available hours for the selected provider and
                // service. Fill the available hours div with response data.
                if (response.length > 0) {
                    var providerId = $('#select-provider').val();

                    if ((!providerId) || (providerId === 'any-provider')) {
                        providerId = GlobalVariables.availableProviders[0].id; // Use first available provider.
                    }

                    var provider = GlobalVariables.availableProviders.find(function (availableProvider) {
                        return Number(providerId) === Number(availableProvider.id);
                    });

                    if (!provider) {
                        throw new Error('Could not find provider.');
                    }

                    var providerTimezone = provider.timezone;
                    var selectedTimezone = $('#select-timezone').val();
                    var timeFormat = GlobalVariables.timeFormat === 'regular' ? 'h:mm a' : 'HH:mm';

                    response.forEach(function (availableHour) {
                        var availableHourMoment = moment
                            .tz(selectedDate + ' ' + availableHour + ':00', providerTimezone)
                            .tz(selectedTimezone);

                        $('#available-hours').append(
                            $('<button/>', {
                                'class': 'btn btn-outline-secondary btn-block shadow-none available-hour',
                                'data': {
                                    'value': availableHour
                                },
                                'text': availableHourMoment.format(timeFormat)
                            })
                        );
                    });

                    if (FrontendBook.manageMode) {
                        // Set the appointment's start time as the default selection.
                        $('.available-hour')
                            .removeClass('selected-hour')
                            .filter(function () {
                                return $(this).text() === Date.parseExact(
                                    GlobalVariables.appointmentData.start_datetime,
                                    'yyyy-MM-dd HH:mm:ss').toString(timeFormat);
                            })
                            .addClass('selected-hour');
                    } else {
                        // Set the first available hour as the default selection.
                        $('.available-hour:eq(0)').addClass('selected-hour');
                    }

                    //FrontendBook.updateConfirmFrame();

                } else {
                    $('#available-hours').text(EALang.no_available_hours);
                }
                $("#loading").css("display", "none");
            });
    };

    /**
     * Check existing appointments by inmate for date
     *
     * @param {String} inmate_id
     * @param {Date} appointment_date
     */
    exports.checkExistingAppointmentByInmate = function (inmate_id, start_datetime) {
        // Make ajax post request and get the available hours.
        const url = GlobalVariables.baseUrl + '/index.php/appointments/ajax_fetch_appointments_by_date_for_inmate';

        const data = {
            csrfToken: GlobalVariables.csrfToken,
            inmate_id: inmate_id,
            appt_date: start_datetime
        };
        console.log('IID: ' + inmate_id + ' date: ' + start_datetime);
        $.post(url, data)
            .done(function (response) {
                return response.appointments;
            })
            .fail(function (jqxhr, textStatus, errorThrown) {
                return null;
            });
    };

    /**
     * Check visitor authorization
     *
     * Verify that the named visitor is on the inmates allowed list
     *
     * @param {String} visitor
     * @param {String} inmate_id
     * @param {String} first_name
     * @param {String} last_name
     * @param {String} birthdate (format mm/dd/yyyy)
     */
    exports.checkVisitorAuthorization = function (service_id, visitor, inmate_id, first_name, last_name, birthdate) {
        // Make ajax post request and get the available hours.
        const url = GlobalVariables.baseUrl + '/index.php/appointments/ajax_check_visitor_authorization';

        const data = {
            csrfToken: GlobalVariables.csrfToken,
            inmate_id: inmate_id,
            first_name: first_name,
            last_name: last_name
        };
        $.post(url, data)
            .done(function (response) {
                const authResult = response.check_visitor_authorization;
                //console.log("*** Response from check_visitor_authorization: " + authResult);
//                if ((authResult) || (service_id == GeneralFunctions.ATTORNEY_SERVICE_ID())) {   -- Comment out until inmate approved visitor lists are ready - for now, always true
                if ((true) || (service_id == GeneralFunctions.ATTORNEY_SERVICE_ID())) {
                    // visitor is on inmates list of allowed visitors - yay
                    // Using the name and email, check for the visitor record in the DB
                    const visitorUrl = GlobalVariables.baseUrl + '/index.php/appointments/ajax_fetch_visitor_information';

                    // Change birthdate to YYYY-MM-DD format
                    let ymdDate = new Date()
                    const dateregex = /(\d{1,2})\/(\d{1,2})\/(\d{4})/;
                    const matches = birthdate.match(dateregex);
                    if ((matches == null) || (matches.length == 0)) {
                        // This is weird and should not happen - use with today's date
                        // Which will fail the check, but that's okay for now
                    } else {
                        ymdDate = new Date(matches[3], matches[1] - 1, matches[2])
                    }

                    // If this is Visitor 1, require age to be 16 or over
                    let visitorOk = true;
                    if (visitor == 'visitor-1') {
                        const today = new Date();
                        let age = today.getFullYear() - ymdDate.getFullYear();
                        var m = today.getMonth() - ymdDate.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < ymdDate.getDate())) {
                            age--;
                        }
                        if (age < 16) {
                            $('#authorize-' + visitor + '-message').text("The first visitor must be at least 16 years old. Please contact the jurisdiction with any questions.");
                            visitorOk = false;
                        }
                    }
                    if (visitor == 'visitor-2') {
                        $('#button-add-visitor-3').show();
                    }

                    if (visitorOk) {
                        const ymdBirthdate = ymdDate.toString('yyyy-MM-dd');
                        const selected_date = $('#select-date').datepicker('getDate').toString('yyyy-MM-dd');
                        
                        var vData = {
                            csrfToken: GlobalVariables.csrfToken,
                            inmate_id: inmate_id,
                            appt_date: selected_date,
                            first_name: first_name,
                            last_name: last_name,
                            birthdate: ymdBirthdate
                        };
                        $.post(visitorUrl, vData)
                            .done(function (response) {
                                //console.log("*** AUTH: " + inmate_id + " / " + selected_date + " / BDATE " + ymdBirthdate + " RESP=" + JSON.stringify(response));
                                $('#authorize-' + visitor).hide();
                                if (response.visitor) {
                                    //Make sure that the visitor is not flagged
                                    if (response.visitor.flag && response.visitor.flag == 1) {
                                        $('#authorize-' + visitor + '-message').text("This visitor is currently restricted from visitation. Please contact the jurisdiction with any questions.");
                                    } else {
                                        // make sure the visitor does not already have an appointment for the date
                                        let curApptVisitor = false;
                                        if (response.appointment_visitors) {
                                            response.appointment_visitors.forEach( (visitor) => {
                                                if (visitor.visitor_id == response.visitor.id) {
                                                    curApptVisitor = true;
                                                }
                                            });
                                        }

                                        if ((service_id != GeneralFunctions.ATTORNEY_SERVICE_ID()) && (curApptVisitor)) {
                                                $('#authorize-' + visitor + '-message').text("This visitor already has a visit scheduled with this inmate on this date. Please contact the jurisdiction with any questions.");
                                        } else {
                                            // All good, display the info
                                            $('#authorize-' + visitor + '-message').text("Please enter / edit your information below");        

                                            const vbdate = new Date(response.visitor.birthdate);
                                            const vbmonth = (vbdate.getMonth() + 1).toString().padStart(2, "0");
                                            const vbdom = vbdate.getDate().toString().padStart(2, "0");
                                            const bdate = vbmonth + "/" + vbdom + "/" + vbdate.getFullYear();
                                            $('#' + visitor + '-birth-date').val(bdate);
//                                            $('#' + visitor + '-dl-box').show();
//                                            $('#' + visitor + '-dl-number-box').show();
//                                            $('#' + visitor + '-dl-state-box').show();
                                            $('#' + visitor + '-dl-number').val(response.visitor.id_number);
                                            $('#' + visitor + '-dl-state').val(response.visitor.id_state);
                                            if (response.visitor.id_image_filename != '') {
                                                $('#' + visitor + '-existing-file').text('Keep existing file ' + response.visitor.id_image_filename + ' or Choose new');
                                                $('#' + visitor + '-existing-file').show();
                                            }
                                            $('#' + visitor + '-dl-file-name').val(response.visitor.id_image_filename);
                                            $('#' + visitor + '-email').val(response.visitor.email);
                                            $('#' + visitor + '-phone-number').val(response.visitor.phone_number);
                                            $('#' + visitor + '-address').val(response.visitor.address);
                                            $('#' + visitor + '-city').val(response.visitor.city);
                                            $('#' + visitor + '-state').val(response.visitor.state);
                                            $('#' + visitor + '-zip-code').val(response.visitor.zip_code);

                                            // Make the rest of the visitor form visible
                                            $('.' + visitor + '-information').show();
                                            $('#button-next-3').show();

                                            // Attorney Fields
                                            if (service_id == GeneralFunctions.ATTORNEY_SERVICE_ID()) {
                                                const caVal = (response.visitor.court_appointed && response.visitor.court_appointed == 1) ? true : false;
                                                if (caVal) {
                                                    $('#' + visitor + '-court-appointed-yes').prop('checked',true);
                                                } else {
                                                    $('#' + visitor + '-court-appointed-no').prop('checked',true);
                                                }
                                                $('#' + visitor + '-cause-number').val(response.visitor.cause_number);
                                                $('#' + visitor + '-law-firm').val(response.visitor.law_firm);
                                                $('#' + visitor + '-law-firm').addClass('required');
                                                $('#' + visitor + '-attorney-type').val(response.visitor.attorney_type);
                                                $('#' + visitor + '-attorney-type').addClass('required');
                                                $('#' + visitor + '-attorney-information').show();
                                            } else {
                                                $('#' + visitor + '-law-firm').removeClass('required');
                                                $('#' + visitor + '-attorney-type').removeClass('required');
                                                $('#' + visitor + '-attorney-information').hide();
                                            }

                                            // Trigger the birthdate focusout event to properly handle that
                                            $('#' + visitor + '-birth-date').trigger("focusout");
                                        }
                                    }
                                } else {
                                    // Clear any existing values
                                    $('#' + visitor + '-dl-number').val('');
                                    $('#' + visitor + '-dl-state').val('');
                                    $('#' + visitor + '-dl-file-name').val('');
                                    $('#' + visitor + '-email').val('');
                                    $('#' + visitor + '-phone-number').val('');
                                    $('#' + visitor + '-address').val('');
                                    $('#' + visitor + '-city').val('');
                                    $('#' + visitor + '-state').val('');
                                    $('#' + visitor + '-zip-code').val('');
                                    $('#' + visitor + '-existing-file').text('');
                                    $('#' + visitor + '-existing-file').hide();

                                    // Trigger the birthdate focusout event to properly handle that
                                    $('#' + visitor + '-birth-date').trigger("focusout");
                    
                                    // Let them enter new information
                                    $('#authorize-' + visitor + '-message').text("Please enter your information below");        
                                    $('.' + visitor + '-information').show();
                                    $('#button-next-3').show();

                                    // Attorney Fields
                                    $('#' + visitor + '-cause-number').val('');
                                    $('#' + visitor + '-law-firm').val('');
                                    $('#' + visitor + '-attorney-type').val('');
                                    if (service_id == GeneralFunctions.ATTORNEY_SERVICE_ID()) {
                                        $('#' + visitor + '-law-firm').addClass('required');
                                        $('#' + visitor + '-attorney-type').addClass('required');
                                        $('#' + visitor + '-attorney-information').show();
                                    } else {
                                        $('#' + visitor + '-law-firm').removeClass('required');
                                        $('#' + visitor + '-attorney-type').removeClass('required');
                                        $('#' + visitor + '-attorney-information').hide();
                                    }
                                }
                            })
                            .fail(function (jqxhr, textStatus, errorThrown) {
                                //console.log('Visitor not matched: ' + first_name + " " + last_name + " " + email);
                                // Make the rest of the visitor form visible
                                $('.' + visitor + '-information').show();
                                $('#button-add-visitor-3').show();
                                $('#button-next-3').show();
                            });
                        }   
                } else {
                    $('#authorize-' + visitor + '-message').text("This visitor name is not on this inmate's list of authorized visitors. Please contact the jurisdiction with any questions.");
                }
            })
            .fail(function (jqxhr, textStatus, errorThrown) {
                $('#authorize-' + visitor + '-message').text('There was an error looking up the visitor authorization. Please contact the jurisdiction with any questions.');
            });
    };

    exports.appointmentVisitorCountForDate = function (inmate_id, selected_date, service_id) {

        // Reset any previous count notices
        $('#no-visitor-slots').hide();
        $('#visitor-1-basic-info').show();
        $('#button-add-visitor-2').attr('disabled',false);
        $('#no-visitor-slots-2').hide();
        $('#button-add-visitor-3').attr('disabled',false);
        $('#no-visitor-slots-3').hide();

        if (service_id == GeneralFunctions.ATTORNEY_SERVICE_ID()) {
            // Attorney visit handled differently
            // We can ignore any visitor count for non-attorney visitors
            // Second, there are additional fields to show on the form
            // Clear / re-hide any info relating to visitor authorization
            $('#authorize-visitor-1').show();
            $('.visitor-1-information').hide();
            $('#authorize-visitor-1-message').text("");
            $('#authorize-visitor-2').show();
            $('.visitor-2-information').hide();
            $('#authorize-visitor-2-message').text("");
            $('#authorize-visitor-3').show();
            $('.visitor-3-information').hide();
            $('#authorize-visitor-3-message').text("");
        } else {
            var data = {
                csrfToken: GlobalVariables.csrfToken,
                inmate_id: inmate_id,
                selected_date: selected_date
            };

            var url = GlobalVariables.baseUrl + '/index.php/appointments/ajax_inmate_visitor_count';

            $.ajax({
                url: url,
                method: 'post',
                data: data,
                dataType: 'json',
                })
                .done(function (response) {
                    const curVisitorCountForDate = response.visitor_slots_used;

                    // Clear / re-hide any info relating to visitor authorization
                    $('#authorize-visitor-1').show();
                    $('.visitor-1-information').hide();
                    $('#authorize-visitor-1-message').text("");
                    $('#authorize-visitor-2').show();
                    $('.visitor-2-information').hide();
                    $('#authorize-visitor-2-message').text("");
                    $('#authorize-visitor-3').show();
                    $('.visitor-3-information').hide();
                    $('#authorize-visitor-3-message').text("");

                    if (curVisitorCountForDate >= 3) {
                        // This should not have happened, but we will not allow any further visits
                        $('#no-visitor-slots').show();
                        $('#visitor-1-basic-info').hide();
                    } else if (curVisitorCountForDate == 2) {
                        // Room for one
                        $('#button-add-visitor-2').attr('disabled','disabled');
                        $('#no-visitor-slots-2').show();
                    } else if (curVisitorCountForDate == 1) {
                        // Room for two
                        $('#button-add-visitor-3').attr('disabled','disabled');
                        $('#no-visitor-slots-3').show();
                    } else {
                        // Room for 3 - normal operations
                    }
                })
                .fail(function (jqxhr, textStatus, errorThrown) {
                    // Clear / re-hide any info relating to visitor authorization
                    $('#authorize-visitor-1').show();
                    $('.visitor-1-information').hide();
                    $('#authorize-visitor-1-message').text("");
                    $('.visitor-2-information').hide();
                    $('#authorize-visitor-2-message').text("");
                    $('.visitor-3-information').hide();
                    $('#authorize-visitor-3-message').text("");
                });
        }
    };

    /**
     * checkVisitorAppointmentRestrictions
     *
     * This method will make an ajax call to the appointments controller that will
     * check for any visitor restrictions
     * 
     * If the restrictions pass, move right into registering the appointment
     * 
     * If they fail, return the user to the confirmation page with a message
     * 
     */
    exports.checkVisitorAppointmentRestrictions = function () {
        var formData = JSON.parse($('input[name="post_data"]').val());

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            post_data: formData
        };

        var url = GlobalVariables.baseUrl + '/index.php/appointments/ajax_check_visitor_appointment_restrictions';

        $.ajax({
            url: url,
            method: 'post',
            data: data,
            dataType: 'json',
        })
            .done(function (response) {
                //console.log("*** Response from check_visitor_appointment_restrictions: " + JSON.stringify(response));
                if (response.check_visitor_appointment_restrictions) {
                    FrontendBookApi.registerAppointment();
                } else {
                    if (response.restricted) {
                        $('span.visitor-restriction-message').show();
                    } else {
                        $('span.visitor-restriction-message-dates').show();
                    }
                    return false;
                }
            })
            .fail(function (jqxhr, textStatus, errorThrown) {
                $('span.visitor-restriction-message').show();
                return false;
            });
    }

    /**
     * Register an appointment to the database.
     *
     * This method will make an ajax call to the appointments controller that will register
     * the appointment to the database.
     */
    exports.registerAppointment = function () {
        var $captchaText = $('.captcha-text');

        if ($captchaText.length > 0) {
            $captchaText.closest('.form-group').removeClass('has-error');
            if ($captchaText.val() === '') {
                $captchaText.closest('.form-group').addClass('has-error');
                return;
            }
        }

        var formData = JSON.parse($('input[name="post_data"]').val());

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            post_data: formData
        };

        if ($captchaText.length > 0) {
            data.captcha = $captchaText.val();
        }

        if (GlobalVariables.manageMode) {
            data.exclude_appointment_id = GlobalVariables.appointmentData.id;
        }

        var url = GlobalVariables.baseUrl + '/index.php/appointments/ajax_register_appointment';

        var $layer = $('<div/>');

        $.ajax({
            url: url,
            method: 'post',
            data: data,
            dataType: 'json',
            beforeSend: function (jqxhr, settings) {
                $layer
                    .appendTo('body')
                    .css({
                        background: 'white',
                        position: 'fixed',
                        top: '0',
                        left: '0',
                        height: '100vh',
                        width: '100vw',
                        opacity: '0.5'
                    });
            }
        })
            .done(function (response) {
                if (response.captcha_verification === false) {
                    $('#captcha-hint')
                        .text(EALang.captcha_is_wrong)
                        .fadeTo(400, 1);

                    setTimeout(function () {
                        $('#captcha-hint').fadeTo(400, 0);
                    }, 3000);

                    $('.captcha-title button').trigger('click');

                    $captchaText.closest('.form-group').addClass('has-error');

                    return false;
                }

                window.location.href = GlobalVariables.baseUrl
                    + '/index.php/appointments/book_success/' + response.appointment_hash;
            })
            .fail(function (jqxhr, textStatus, errorThrown) {
                $('.captcha-title button').trigger('click');
            })
            .always(function () {
                $layer.remove();
            });
    };

    /**
     * Get the unavailable dates of a provider.
     *
     * This method will fetch the unavailable dates of the selected provider and service and then it will
     * select the first available date (if any). It uses the "FrontendBookApi.getAvailableHours" method to
     * fetch the appointment* hours of the selected date.
     *
     * @param {Number} providerId The selected provider ID.
     * @param {Number} serviceId The selected service ID.
     * @param {String} selectedDateString Y-m-d value of the selected date.
     */
    exports.getUnavailableDates = function (providerId, serviceId, selectedDateString, selectedInmateId = null) {
        if (processingUnavailabilities) {
            return;
        }

        if (!providerId) {
            providerId = 'any-provider';
        }
        
        if (!serviceId) {
            return;
        }

        var appointmentId = FrontendBook.manageMode ? GlobalVariables.appointmentData.id : null;

        var url = GlobalVariables.baseUrl + '/index.php/appointments/ajax_get_unavailable_dates';

        var data = {
            provider_id: providerId,
            service_id: serviceId,
            selected_date: encodeURIComponent(selectedDateString),
            csrfToken: GlobalVariables.csrfToken,
            manage_mode: FrontendBook.manageMode,
            appointment_id: appointmentId,
            selectedInmateId
        };

        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            dataType: 'json',
            beforeSend: function () {
                $("#loading").css("display", "");
            },
            complete: function () {
                $("#loading").css("display", "none");
            },
        })
            .done(function (response) {
                // Check for restricted and handle
                if ((response.length === 1) && (response[0] == "restricted")) {
                    showRestriction(selectedDateString);
                } else {
                    unavailableDatesBackup = response;
                    selectedDateStringBackup = selectedDateString;
                    applyUnavailableDates(response, selectedDateString, true);
                }
            });
    };

    exports.applyPreviousUnavailableDates = function () {
        applyUnavailableDates(unavailableDatesBackup, selectedDateStringBackup);
    };

    function showRestriction(selectedDateString) {
        processingUnavailabilities = true;

        // Grey out unavailable dates.
        var selectedDate = Date.parse(selectedDateString);
        var numberOfDays = new Date(selectedDate.getFullYear(), selectedDate.getMonth() + 1, 0).getDate();
        $('#select-date .ui-datepicker-calendar td:not(.ui-datepicker-other-month)').each(function (index, td) {
            selectedDate.set({ day: index + 1 });
            $(td).addClass('ui-datepicker-unselectable ui-state-disabled');
        });

        // Show restricted message
        $('#available-hours').text("You cannot schedule a visitation with this inmate at this time.  Please contact the jurisdiction for information.");

        // Disable the Next button
        $('#button-next-2').hide();

        processingUnavailabilities = false;
    }

    function applyUnavailableDates(unavailableDates, selectedDateString, setDate) {
        setDate = setDate || false;

        processingUnavailabilities = true;

        // Select first enabled date.
        var selectedDate = Date.parse(selectedDateString);
        var numberOfDays = new Date(selectedDate.getFullYear(), selectedDate.getMonth() + 1, 0).getDate();

        if (setDate && !GlobalVariables.manageMode) {
            for (var i = 1; i <= numberOfDays; i++) {
                var currentDate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), i);
                if (unavailableDates.indexOf(currentDate.toString('yyyy-MM-dd')) === -1) {
                    $('#select-date').datepicker('setDate', currentDate);
                    FrontendBookApi.getAvailableHours(currentDate.toString('yyyy-MM-dd'));
                    break;
                }
            }
        }

        // If all the days are unavailable then hide the appointments hours.
        if (unavailableDates.length === numberOfDays) {
            $('#available-hours').text(EALang.no_available_hours);
        }

        // Grey out unavailable dates.
        $('#select-date .ui-datepicker-calendar td:not(.ui-datepicker-other-month)').each(function (index, td) {
            selectedDate.set({ day: index + 1 });
            if (unavailableDates.indexOf(selectedDate.toString('yyyy-MM-dd')) !== -1) {
                $(td).addClass('ui-datepicker-unselectable ui-state-disabled');
            }
        });

        // Enable the Next button in case it had been hidden previously
        $('#button-next-2').show();


        processingUnavailabilities = false;
    }

    /**
     * Save the user's consent.
     *
     * @param {Object} consent Contains user's consents.
     */
    exports.saveConsent = function (consent) {
        var url = GlobalVariables.baseUrl + '/index.php/consents/ajax_save_consent';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            consent: consent
        };

        $.post(url, data);
    };

    /**
     * Delete personal information.
     *
     * @param {Number} customerToken Customer unique token.
     */
    exports.deletePersonalInformation = function (customerToken) {
        var url = GlobalVariables.baseUrl + '/index.php/privacy/ajax_delete_personal_information';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            customer_token: customerToken
        };

        $.post(url, data)
            .done(function () {
                window.location.href = GlobalVariables.baseUrl;
            });
    };

})(window.FrontendBookApi);
