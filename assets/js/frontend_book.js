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

window.FrontendBook = window.FrontendBook || {};

/**
 * Frontend Book
 *
 * This module contains functions that implement the book appointment page functionality. Once the
 * initialize() method is called the page is fully functional and can serve the appointment booking
 * process.
 *
 * @module FrontendBook
 */
(function (exports) {

    'use strict';

    /**
     * Contains terms and conditions consent.
     *
     * @type {Object}
     */
    var termsAndConditionsConsent;

    /**
     * Contains privacy policy consent.
     *
     * @type {Object}
     */
    var privacyPolicyConsent;

    /**
     * Determines the functionality of the page.
     *
     * @type {Boolean}
     */
    exports.manageMode = false;

    /**
     * This method initializes the book appointment page.
     *
     * @param {Boolean} defaultEventHandlers (OPTIONAL) Determines whether the default
     * event handlers will be bound to the dom elements.
     * @param {Boolean} manageMode (OPTIONAL) Determines whether the customer is going
     * to make  changes to an existing appointment rather than booking a new one.
     */
    exports.initialize = function (defaultEventHandlers, manageMode) {
        defaultEventHandlers = defaultEventHandlers || true;
        manageMode = manageMode || false;

        if (GlobalVariables.displayCookieNotice) {
            cookieconsent.initialise({
                palette: {
                    popup: {
                        background: '#ffffffbd',
                        text: '#666666'
                    },
                    button: {
                        background: '#429a82',
                        text: '#ffffff'
                    }
                },
                content: {
                    message: EALang.website_using_cookies_to_ensure_best_experience,
                    dismiss: 'OK'
                },
            });

            $('.cc-link').replaceWith(
                $('<a/>', {
                    'data-toggle': 'modal',
                    'data-target': '#cookie-notice-modal',
                    'href': '#',
                    'class': 'cc-link',
                    'text': $('.cc-link').text()
                })
            );
        }

        FrontendBook.manageMode = manageMode;

        // Initialize page's components (tooltips, datepickers etc).
        tippy('[data-tippy-content]');

        var weekDayId = GeneralFunctions.getWeekDayId(GlobalVariables.firstWeekday);

        $('#select-date').datepicker({
            dateFormat: 'dd-mm-yy',
            firstDay: weekDayId,
            minDate: 0,
            defaultDate: Date.today(),

            dayNames: [
                EALang.sunday, EALang.monday, EALang.tuesday, EALang.wednesday,
                EALang.thursday, EALang.friday, EALang.saturday],
            dayNamesShort: [EALang.sunday.substr(0, 3), EALang.monday.substr(0, 3),
                EALang.tuesday.substr(0, 3), EALang.wednesday.substr(0, 3),
                EALang.thursday.substr(0, 3), EALang.friday.substr(0, 3),
                EALang.saturday.substr(0, 3)],
            dayNamesMin: [EALang.sunday.substr(0, 2), EALang.monday.substr(0, 2),
                EALang.tuesday.substr(0, 2), EALang.wednesday.substr(0, 2),
                EALang.thursday.substr(0, 2), EALang.friday.substr(0, 2),
                EALang.saturday.substr(0, 2)],
            monthNames: [EALang.january, EALang.february, EALang.march, EALang.april,
                EALang.may, EALang.june, EALang.july, EALang.august, EALang.september,
                EALang.october, EALang.november, EALang.december],
            prevText: EALang.previous,
            nextText: EALang.next,
            currentText: EALang.now,
            closeText: EALang.close,

            onSelect: function (dateText, instance) {
                FrontendBookApi.getAvailableHours($(this).datepicker('getDate').toString('yyyy-MM-dd'));
                FrontendBook.updateConfirmFrame();
            },

            onChangeMonthYear: function (year, month, instance) {
                var currentDate = new Date(year, month - 1, 1);
                FrontendBookApi.getUnavailableDates($('#select-provider').val(), $('#select-service').val(),
                    currentDate.toString('yyyy-MM-dd'), $('#select-inmate').val());
            }
        });

        $('#select-timezone').val(Intl.DateTimeFormat().resolvedOptions().timeZone);

        // Bind the event handlers (might not be necessary every time we use this class).
        if (defaultEventHandlers) {
            bindEventHandlers();
        }

        // If the manage mode is true, the appointments data should be loaded by default.
        if (FrontendBook.manageMode) {
            applyAppointmentData(GlobalVariables.appointmentData,
                GlobalVariables.providerData, GlobalVariables.customerData);
        } else {
            var $selectProvider = $('#select-provider');
            var $selectService = $('#select-service');
            var $selectInmate = $('#select-inmate');

            // Check if a specific service was selected (via URL parameter).
            var selectedServiceId = GeneralFunctions.getUrlParameter(location.href, 'service');

            if (selectedServiceId && $selectService.find('option[value="' + selectedServiceId + '"]').length > 0) {
                $selectService.val(selectedServiceId);
            }
            
            // Check if a specific inmate was selected (via URL parameter).
            var selectedinmateid = GeneralFunctions.getUrlParameter(location.href, 'inmate');

            if (selectedinmateid && $selectInmate.find('option[value="' + selectedinmateid + '"]').length > 0) {
                $selectInmate.val(selectedinmateid);
            }


            $selectService.trigger('change'); // Load the available hours.

            // Check if a specific provider was selected.
            var selectedProviderId = GeneralFunctions.getUrlParameter(location.href, 'provider');

            if (selectedProviderId && $selectProvider.find('option[value="' + selectedProviderId + '"]').length === 0) {
                // Select a service of this provider in order to make the provider available in the select box.
                for (var index in GlobalVariables.availableProviders) {
                    var provider = GlobalVariables.availableProviders[index];

                    if (provider.id === selectedProviderId && provider.services.length > 0) {
                        $selectService
                            .val(provider.services[0])
                            .trigger('change');
                    }
                }
            }

            if (selectedProviderId && $selectProvider.find('option[value="' + selectedProviderId + '"]').length > 0) {
                $selectProvider
                    .val(selectedProviderId)
                    .trigger('change');
            }

        }
    };

    /**
     * This method binds the necessary event handlers for the book appointments page.
     */
    function bindEventHandlers() {
        /**
         * Event: Timezone "Changed"
         */
        $('#select-timezone').on('change', function () {
            var date = $('#select-date').datepicker('getDate');

            if (!date) {
                return;
            }

            FrontendBookApi.getAvailableHours(date.toString('yyyy-MM-dd'));

            FrontendBook.updateConfirmFrame();
        });

        /**
         * Event: Selected Provider "Changed"
         *
         * Whenever the provider changes the available appointment date - time periods must be updated.
         */
        $('#select-provider').on('change', function () {
            // KPB 2022-11-28 We don't need to get the dates here
            //FrontendBookApi.getUnavailableDates($(this).val(), $('#select-service').val(),
            //    $('#select-date').datepicker('getDate').toString('yyyy-MM-dd'));
            FrontendBook.updateConfirmFrame();
        });
        
         /**
         * Event: Selected Inmate "Changed"
         *
         * Whenever the Inmate changes the security level updates for the provider.
         */
        $('#select-inmate').on('change', function () {
            var inmateID = $('#select-inmate').val();
            var inmate;
            var serviceId = $('#select-service').val();
         
            
            $('#select-provider').empty();
            
            // Select a inmate of this provider in order to make the provider available in the select box.
                for (var index in GlobalVariables.availableInmates) {
                    var myinmate = GlobalVariables.availableInmates[index];
                    if (inmateID === myinmate.id)
                    {
                        inmate=myinmate;
                        break;
                        }
                
                }

                if (typeof inmate!=="undefined") {
            GlobalVariables.availableProviders.forEach(function (provider) {
                // If the current provider is able to provide the selected service, add him to the list box.
               //let filteredarray = provider.services.filter( (provider) =>
                
                
                var canServeService1 = provider.inmate_classification_level === inmate.inmate_classification_level;
    
                var canServeService2 = provider.services.filter(function (providerServiceId) {
                    return Number(providerServiceId) === Number(serviceId);
                }).length > 0;
                
                //filteredarray.length > 0;
                
                
                if (canServeService1 && canServeService2) {
                    $('#select-provider').append(new Option(provider.first_name + ' ' + provider.last_name, provider.id));
                }
            });
                }
                
                 // Add the "Any Provider" entry.
            if ($('#select-provider option').length >= 1 && GlobalVariables.displayAnyProvider === '1') {
                $('#select-provider').prepend(new Option('- ' + EALang.any_provider + ' -', 'any-provider',true,true));
            }
            
            // KPB 2022-11-28 We don't need to get the dates here
            //FrontendBookApi.getUnavailableDates($('#select-provider').val(), $('#select-service').val(),
            //    $('#select-date').datepicker('getDate').toString('yyyy-MM-dd'), $('#select-inmate').val());
            FrontendBook.updateConfirmFrame();
            //updateServiceDescription(serviceId);
        });


        /**
         * Event: Selected Service "Changed"
         *
         * When the user clicks on a service, its available providers should
         * become visible.
         */
        $('#select-service').on('change', function () {
            var serviceId = $('#select-service').val();

            $('#select-provider').empty();
           

            GlobalVariables.availableProviders.forEach(function (provider) {
                // If the current provider is able to provide the selected service, add him to the list box.
                var canServeService = provider.services.filter(function (providerServiceId) {
                    return Number(providerServiceId) === Number(serviceId);
                }).length > 0;

                if (canServeService) {
                    $('#select-provider').append(new Option(provider.first_name + ' ' + provider.last_name, provider.id));
                }
            });

            // Add the "Any Provider" entry.
            if ($('#select-provider option').length >= 1 && GlobalVariables.displayAnyProvider === '1') {
                $('#select-provider').prepend(new Option('- ' + EALang.any_provider + ' -', 'any-provider',true,true));
            }
            

            // KPB 2022-11-28 We don't need to get the dates here
            //FrontendBookApi.getUnavailableDates($('#select-provider').val(), $(this).val(),
            //    $('#select-date').datepicker('getDate').toString('yyyy-MM-dd'));
            FrontendBook.updateConfirmFrame();
            updateServiceDescription(serviceId);
        });

        /**
         * Add a "reset" button that just reloads the start page
         */
        $('.button-reset').on('click', function () {
            window.location.href = window.location.href;
            window.location.reload();
        });

        /**
         * Event: Next Step Button "Clicked"
         *
         * This handler is triggered every time the user pressed the "next" button on the book wizard.
         * Some special tasks might be performed, depending the current wizard step.
         */
        $('.button-next').on('click', function () {
            // If we are on the first step and there is not an inmate selected do not continue with the next step.
            if ($(this).attr('data-step_index') === '1') {
                // Add check for inmate selected as well
                if (!$('#select-inmate').val() || 
                    ($('#select-inmate').val() === '0')) {
                        alert("Please select an Inmate");
                        return;
                } else {
                    // if all good, now we get the unavailable dates
                    FrontendBookApi.getUnavailableDates($('#select-provider').val(), $('#select-service').val(),
                        $('#select-date').datepicker('getDate').toString('yyyy-MM-dd'), $('#select-inmate').val());
                }
            }

            // If we are on the 2nd tab then the user should have an appointment hour selected.
            if ($(this).attr('data-step_index') === '2') {
                if (!$('.selected-hour').length) {
                    if (!$('#select-hour-prompt').length) {
                        $('<div/>', {
                            'id': 'select-hour-prompt',
                            'class': 'text-danger mb-4',
                            'text': EALang.appointment_hour_missing,
                        })
                            .prependTo('#available-hours');
                    }
                    return;
                }
            }

            // If we are on the 3rd tab then we will need to validate the user's input before proceeding to the next
            // step.
            if ($(this).attr('data-step_index') === '3') {
                if (!validateCustomerForm()) {
                    return; // Validation failed, do not continue.
                } else {
                    FrontendBook.updateConfirmFrame();

                    var $acceptToTermsAndConditions = $('#accept-to-terms-and-conditions');
                    if ($acceptToTermsAndConditions.length && $acceptToTermsAndConditions.prop('checked') === true) {
                        var newTermsAndConditionsConsent = {
                            first_name: $('#first-name').val(),
                            last_name: $('#last-name').val(),
                            email: $('#email').val(),
                            type: 'terms-and-conditions'
                        };

                        if (JSON.stringify(newTermsAndConditionsConsent) !== JSON.stringify(termsAndConditionsConsent)) {
                            termsAndConditionsConsent = newTermsAndConditionsConsent;
                            FrontendBookApi.saveConsent(termsAndConditionsConsent);
                        }
                    }

                    var $acceptToPrivacyPolicy = $('#accept-to-privacy-policy');
                    if ($acceptToPrivacyPolicy.length && $acceptToPrivacyPolicy.prop('checked') === true) {
                        var newPrivacyPolicyConsent = {
                            first_name: $('#first-name').val(),
                            last_name: $('#last-name').val(),
                            email: $('#email').val(),
                            type: 'privacy-policy'
                        };

                        if (JSON.stringify(newPrivacyPolicyConsent) !== JSON.stringify(privacyPolicyConsent)) {
                            privacyPolicyConsent = newPrivacyPolicyConsent;
                            FrontendBookApi.saveConsent(privacyPolicyConsent);
                        }
                    }
                }
            }

            // Display the next step tab (uses jquery animation effect).
            var nextTabIndex = parseInt($(this).attr('data-step_index')) + 1;

            $(this).parents().eq(1).hide('fade', function () {
                $('.active-step').removeClass('active-step');
                $('#step-' + nextTabIndex).addClass('active-step');
                $('#wizard-frame-' + nextTabIndex).show('fade');
            });
        });

        /**
         * Event: Back Step Button "Clicked"
         *
         * This handler is triggered every time the user pressed the "back" button on the
         * book wizard.
         */
        $('.button-back').on('click', function () {
            var prevTabIndex = parseInt($(this).attr('data-step_index')) - 1;

            $(this).parents().eq(1).hide('fade', function () {
                $('.active-step').removeClass('active-step');
                $('#step-' + prevTabIndex).addClass('active-step');
                $('#wizard-frame-' + prevTabIndex).show('fade');
            });
        });

        /**
         * Event: Available Hour "Click"
         *
         * Triggered whenever the user clicks on an available hour
         * for his appointment.
         */
        $('#available-hours').on('click', '.available-hour', function () {
            $('.selected-hour').removeClass('selected-hour');
            $(this).addClass('selected-hour');
            FrontendBook.updateConfirmFrame();
        });

        if (FrontendBook.manageMode) {
            /**
             * Event: Cancel Appointment Button "Click"
             *
             * When the user clicks the "Cancel" button this form is going to be submitted. We need
             * the user to confirm this action because once the appointment is cancelled, it will be
             * delete from the database.
             *
             * @param {jQuery.Event} event
             */
            $('#cancel-appointment').on('click', function (event) {
                var buttons = [
                    {
                        text: EALang.cancel,
                        click: function () {
                            $('#message-box').dialog('close');
                        }
                    },
                    {
                        text: 'OK',
                        click: function () {
                            if ($('#cancel-reason').val() === '') {
                                $('#cancel-reason').css('border', '2px solid #DC3545');
                                return;
                            }
                            $('#cancel-appointment-form textarea').val($('#cancel-reason').val());
                            $('#cancel-appointment-form').submit();
                        }
                    }
                ];

                GeneralFunctions.displayMessageBox(EALang.cancel_appointment_title,
                    EALang.write_appointment_removal_reason, buttons);

                $('<textarea/>', {
                    'class': 'form-control',
                    'id': 'cancel-reason',
                    'rows': '3',
                    'css': {
                        'width': '100%'
                    }
                })
                    .appendTo('#message-box');

                return false;
            });

            $('#delete-personal-information').on('click', function () {
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
                            FrontendBookApi.deletePersonalInformation(GlobalVariables.customerToken);
                        }
                    }
                ];

                GeneralFunctions.displayMessageBox(EALang.delete_personal_information,
                    EALang.delete_personal_information_prompt, buttons);
            });
        }

        /**
         * Event: Book Appointment Form "Submit"
         *
         * Before the form is submitted to the server we need to make sure that
         * in the meantime the selected appointment date/time wasn't reserved by
         * another customer or event.
         *
         * @param {jQuery.Event} event
         */
        $('#book-appointment-submit').on('click', function () {
            FrontendBookApi.registerAppointment();
        });

        /**
         * Event: Refresh captcha image.
         *
         * @param {jQuery.Event} event
         */
        $('.captcha-title button').on('click', function (event) {
            $('.captcha-image').attr('src', GlobalVariables.baseUrl + '/index.php/captcha?' + Date.now());
        });


        $('#select-date').on('mousedown', '.ui-datepicker-calendar td', function (event) {
            setTimeout(function () {
                FrontendBookApi.applyPreviousUnavailableDates(); // New jQuery UI version will replace the td elements.
            }, 300); // There is no draw event unfortunately.
        })
    }

    /**
     * This function validates the customer's data input. The user cannot continue
     * without passing all the validation checks.
     *
     * @return {Boolean} Returns the validation result.
     */
    function validateCustomerForm() {
        $('#wizard-frame-3 .has-error').removeClass('has-error');
        $('#wizard-frame-3 label.text-danger').removeClass('text-danger');

        try {
            // Validate required fields.
            var missingRequiredField = false;
            $('.required').each(function (index, requiredField) {
                if (!$(requiredField).val()) {
                    $(requiredField).parents('.form-group').addClass('has-error');
                    missingRequiredField = true;
                } else if ($(requiredField).val() == "Select") {
                    $(requiredField).parents('.form-group').addClass('has-error');
                    missingRequiredField = true;
                }
            });
            if (missingRequiredField) {
                throw new Error(EALang.fields_are_required);
            }

            var $acceptToTermsAndConditions = $('#accept-to-terms-and-conditions');
            if ($acceptToTermsAndConditions.length && !$acceptToTermsAndConditions.prop('checked')) {
                $acceptToTermsAndConditions.parents('.form-check').addClass('text-danger');
                throw new Error(EALang.fields_are_required);
            }

            var $acceptToPrivacyPolicy = $('#accept-to-privacy-policy');
            if ($acceptToPrivacyPolicy.length && !$acceptToPrivacyPolicy.prop('checked')) {
                $acceptToPrivacyPolicy.parents('.form-check').addClass('text-danger');
                throw new Error(EALang.fields_are_required);
            }


            // Validate email address.
            if (!GeneralFunctions.validateEmail($('#email').val())) {
                $('#email').parents('.form-group').addClass('has-error');
                throw new Error(EALang.invalid_email);
            }

            return true;
        } catch (error) {
            $('#form-message').text(error.message);
            return false;
        }
    }

    /**
     * Every time this function is executed, it updates the confirmation page with the latest
     * customer settings and input for the appointment booking.
     */
    exports.updateConfirmFrame = function () {
        if ($('.selected-hour').text() === '') {
            return;
        }

        // Appointment Details
        
        var selectedDate = $('#select-date').datepicker('getDate');

        // Visitor 1 Details
        var v1firstName = GeneralFunctions.escapeHtml($('#first-name').val());
        var v1lastName = GeneralFunctions.escapeHtml($('#last-name').val());
        var v1phoneNumber = GeneralFunctions.escapeHtml($('#phone-number').val());
        var v1email = GeneralFunctions.escapeHtml($('#email').val());
        var v1address = GeneralFunctions.escapeHtml($('#address').val());
        var v1city = GeneralFunctions.escapeHtml($('#city').val());
        var v1zipCode = GeneralFunctions.escapeHtml($('#zip-code').val());
        var v1notes = GeneralFunctions.escapeHtml($('#notes').val());
        var v1birthdate = GeneralFunctions.escapeHtml($('#visitor-1-birth-date').val());
        v1birthdate = GeneralFunctions.dateToDBFormat(v1birthdate);
        var v1idfilename = GeneralFunctions.escapeHtml($('#visitor-1-dl-file-name').val());
        var v1idnumber = GeneralFunctions.escapeHtml($('#visitor-1-dl-number').val());
        var v1idstate = GeneralFunctions.escapeHtml($('#visitor-1-dl-state').find(":selected").val());

        // Visitor 2 Details
        var v2firstName = GeneralFunctions.escapeHtml($('#visitor-2-first-name').val());
        var v2lastName = GeneralFunctions.escapeHtml($('#visitor-2-last-name').val());
        var v2birthdate = GeneralFunctions.escapeHtml($('#visitor-2-birth-date').val());
        v2birthdate = GeneralFunctions.dateToDBFormat(v2birthdate);
        var v2idfilename = GeneralFunctions.escapeHtml($('#visitor-2-dl-file-name').val());
        var v2idnumber = GeneralFunctions.escapeHtml($('#visitor-2-dl-number').val());
        var v2idstate = GeneralFunctions.escapeHtml($('#visitor-2-dl-state').find(":selected").val());

        // Visitor 3 Details       
        var v3firstName = GeneralFunctions.escapeHtml($('#visitor-3-first-name').val());
        var v3lastName = GeneralFunctions.escapeHtml($('#visitor-3-last-name').val());
        var v3birthdate = GeneralFunctions.escapeHtml($('#visitor-3-birth-date').val());
        v3birthdate = GeneralFunctions.dateToDBFormat(v3birthdate);
        var v3idfilename = GeneralFunctions.escapeHtml($('#visitor-3-dl-file-name').val());
        var v3idnumber = GeneralFunctions.escapeHtml($('#visitor-3-dl-number').val());
        var v3idstate = GeneralFunctions.escapeHtml($('#visitor-3-dl-state').find(":selected").val());

        if (selectedDate !== null) {
            selectedDate = GeneralFunctions.formatDate(selectedDate, GlobalVariables.dateFormat);
        }

        var serviceId = $('#select-service').val();
        var servicePrice = '';
        var serviceCurrency = '';
        
        GlobalVariables.availableServices.forEach(function (service, index) {
            if (Number(service.id) === Number(serviceId) && Number(service.price) > 0) {
                servicePrice = service.price;
                serviceCurrency = service.currency;
                return false; // break loop
            }
        });

        $('#appointment-details').empty();

        $('<div/>', {
            'html': [
                $('<h4/>', {
                    'text': EALang.appointment
                }),
                $('<p/>', {
                    'html': [
                        $('<span/>', {
                            'text': EALang.service + ': ' + $('#select-service option:selected').text()
                        }),
                        $('<br/>'),
                        $('<span/>', {
                            'text': EALang.provider + ': ' + $('#select-provider option:selected').text()
                        }),
                        $('<br/>'),
                        $('<span/>', {
                            'text': EALang.inmate + ': ' + $('#select-inmate option:selected').text()
                        }),
                        $('<br/>'),
                        $('<span/>', {
                            'text': EALang.start + ': ' + selectedDate + ' ' + $('.selected-hour').text()
                        }),
//                        $('<br/>'),
//                        $('<span/>', {
//                            'text': EALang.timezone + ': ' + $('#select-timezone option:selected').text()
//                        }),
                        $('<br/>'),
                        $('<span/>', {
                            'text': EALang.price + ': ' + servicePrice + ' ' + serviceCurrency,
                            'prop': {
                                'hidden': !servicePrice
                            }
                        }),
                    ]
                })
            ]
        })
            .appendTo('#appointment-details');


        $('#customer-details').empty();
        

        $('<div/>', {
            'html': [
                $('<h4/>)', {
                    'text': EALang.customers
                }),
                $('<p/>', {
                    'html': [
                        $('<span/>', {
                            'text': EALang.visitor_1_name + ': ' + v1firstName + ' ' + v1lastName
                        }),
                        $('<br/>'),
                        $('<span/>', {
                            'text': EALang.phone_number + ': ' + v1phoneNumber
                        }),
                        $('<br/>'),
                        $('<span/>', {
                            'text': EALang.email + ': ' + v1email
                        }),
                        $('<br/>'),
                        $('<span/>', {
                            'text': v1address ? EALang.address + ': ' + v1address : EALang.address + ': '
                        }),
                        $('<br/>'),
                        $('<span/>', {
                            'text': v1city ? EALang.city + ': ' + v1city : EALang.city + ': '
                        }),
                        $('<br/>'),
                        $('<span/>', {
                            'text': v1zipCode ? EALang.zip_code + ': ' + v1zipCode : EALang.zip_code + ': '
                        }),
                        $('<br/>'),
						$('<span/>', {
                            'text': EALang.birth_date_ymd + ': ' + v1birthdate
                        }),
                        $('<br/>'),
						$('<span/>', {
                            'text': v1idstate ? EALang.visitor_1_dl_state + ': ' + v1idstate : EALang.visitor_1_dl_state + ': N/A'
                        }),
                        $('<br/>'),
						$('<span/>', {
                            'text': v1idnumber ? EALang.visitor_1_dl_number + ': ' + v1idnumber : EALang.visitor_1_dl_number + ': N/A'
                        }),
                        $('<br/>'),
						$('<span/>', {
                            'text': v1notes ? EALang.notes + ': ' + v1notes : EALang.notes + ': '
                        }),
                        $('<br/>'),
                        $('<br/>'),
						$('<span/>', {
                            'text': v2firstName ? EALang.visitor_2_name + ': ' + v2firstName + ' ' + v2lastName : EALang.visitor_2_name + ': N/A'
                        }),
                        $('<br/>'),
						$('<span/>', {
                            'text': v2birthdate ? EALang.birth_date_ymd + ': ' + v2birthdate : EALang.birth_date_ymd + ': N/A'
                        }),
                        $('<br/>'),
						$('<span/>', {
                            'text': v2idstate ? EALang.visitor_2_dl_state + ': ' + v2idstate : EALang.visitor_2_dl_state + ': N/A'
                        }),
                        $('<br/>'),
						$('<span/>', {
                            'text': v2idnumber ? EALang.visitor_2_dl_number + ': ' + v2idnumber : EALang.visitor_2_dl_number + ': N/A'
                        }),
                        $('<br/>'),
                        $('<br/>'),
						$('<span/>', {
                            'text': v3firstName ? EALang.visitor_3_name + ': ' + v3firstName + ' ' + v3lastName : EALang.visitor_3_name + ': N/A'
                        }),
                        $('<br/>'),
						$('<span/>', {
                            'text': v3birthdate ? EALang.birth_date_ymd + ': ' + v3birthdate : EALang.birth_date_ymd + ': N/A'
                        }),
                        $('<br/>'),
						$('<span/>', {
                            'text': v3idstate ? EALang.visitor_3_dl_state + ': ' + v3idstate : EALang.visitor_3_dl_state + ': N/A'
                        }),
                        $('<br/>'),
						$('<span/>', {
                            'text': v3idnumber ? EALang.visitor_3_dl_number + ': ' + v3idnumber : EALang.visitor_3_dl_number + ': N/A'
                        })
                    ]
                })
            ]
        })
            .appendTo('#customer-details');


        // Update appointment form data for submission to server when the user confirms the appointment.
        var data = {};

        data.visitor1 = {
            last_name: v1lastName,
            first_name: v1firstName,
            email: v1email,
            phone_number: v1phoneNumber,
            address: v1address,
            city: v1city,
            zip_code: v1zipCode,
            notes: v1notes,
            birthdate: v1birthdate,
            id_image_filename: v1idfilename,
            id_number: v1idnumber,
            id_state: v1idstate
        };

        if (v2firstName != null) {
            data.visitor2 = {
                last_name: v2lastName,
                first_name: v2firstName,
                birthdate: v2birthdate,
                id_image_filename: v2idfilename,
                id_number: v2idnumber,
                id_state: v2idstate
            };
        }

        if (v3firstName != null) {
            data.visitor3 = {
                last_name: v3lastName,
                first_name: v3firstName,
                birthdate: v3birthdate,
                id_image_filename: v3idfilename,
                id_number: v3idnumber,
                id_state: v3idstate
            };
        }

        // Remove number from inmate name for database
        var inmateName = $('#select-inmate option:selected').text();
        if (inmateName.indexOf("-") >= 0) {
            inmateName = inmateName.substring(inmateName.indexOf("-") + 1);
        }

        data.appointment = {
            start_datetime: $('#select-date').datepicker('getDate').toString('yyyy-MM-dd')
                + ' ' + Date.parse($('.selected-hour').data('value') || '').toString('HH:mm') + ':00',
            end_datetime: calculateEndDatetime(),
            notes: $('#notes').val(),
            is_unavailable: false,
            id_users_provider: $('#select-provider').val(),
            id_services: $('#select-service').val(),
            id_inmate: $('#select-inmate').val(),
            inmate_name: inmateName
        };

        data.manage_mode = FrontendBook.manageMode;

        if (FrontendBook.manageMode) {
            data.appointment.id = GlobalVariables.appointmentData.id;
            data.customer.id = GlobalVariables.customerData.id;
        }
        $('input[name="csrfToken"]').val(GlobalVariables.csrfToken);
        $('input[name="post_data"]').val(JSON.stringify(data));
    };

    /**
     * This method calculates the end datetime of the current appointment.
     * End datetime is depending on the service and start datetime fields.
     *
     * @return {String} Returns the end datetime in string format.
     */
    function calculateEndDatetime() {
        // Find selected service duration.
        var serviceId = $('#select-service').val();

        var service = GlobalVariables.availableServices.find(function (availableService) {
            return Number(availableService.id) === Number(serviceId);
        });

        // Add the duration to the start datetime.
        var startDatetime = $('#select-date').datepicker('getDate').toString('dd-MM-yyyy')
            + ' ' + Date.parse($('.selected-hour').data('value') || '').toString('HH:mm');
        startDatetime = Date.parseExact(startDatetime, 'dd-MM-yyyy HH:mm');
        var endDatetime;

        if (service.duration && startDatetime) {
            endDatetime = startDatetime.add({'minutes': parseInt(service.duration)});
        } else {
            endDatetime = new Date();
        }

        return endDatetime.toString('yyyy-MM-dd HH:mm:ss');
    }

    /**
     * This method applies the appointment's data to the wizard so
     * that the user can start making changes on an existing record.
     *
     * @param {Object} appointment Selected appointment's data.
     * @param {Object} provider Selected provider's data.
     * @param {Object} customer Selected customer's data.
     *
     * @return {Boolean} Returns the operation result.
     */
    function applyAppointmentData(appointment, provider, customer) {
        try {
            // Select Service & Provider
            $('#select-service').val(appointment.id_services).trigger('change');
            $('#select-provider').val(appointment.id_users_provider);
            $('#select-inmate').val(appointment.inmate_name);
            $('#select-user').val(appointment.users.id);

            // Set Appointment Date
            $('#select-date').datepicker('setDate',
                Date.parseExact(appointment.start_datetime, 'yyyy-MM-dd HH:mm:ss'));
            FrontendBookApi.getAvailableHours(moment(appointment.start_datetime).format('YYYY-MM-DD'));
            
            // Apply Additional Appointment Data
            $('#visitor-2-name').val(appointment.visitor_2_name);

            // Apply Customer's Data
            $('#last-name').val(customer.last_name);
            $('#first-name').val(customer.first_name);
            $('#email').val(customer.email);
            $('#phone-number').val(customer.phone_number);
            $('#address').val(customer.address);
            $('#city').val(customer.city);
            $('#zip-code').val(customer.zip_code);
           

            
            if (customer.timezone) {
                $('#select-timezone').val(customer.timezone)
            }
            var appointmentNotes = (appointment.notes !== null)
                ? appointment.notes : '';
            $('#notes').val(appointmentNotes);
            
           

            FrontendBook.updateConfirmFrame();

            return true;
        } catch (exc) {
            return false;
        }
    }

    /**
     * This method updates a div's html content with a brief description of the
     * user selected service (only if available in db). This is useful for the
     * customers upon selecting the correct service.
     *
     * @param {Number} serviceId The selected service record id.
     */
    function updateServiceDescription(serviceId) {
        var $serviceDescription = $('#service-description');

        $serviceDescription.empty();

        var service = GlobalVariables.availableServices.find(function (availableService) {
            return Number(availableService.id) === Number(serviceId);
        });

        if (!service) {
            return;
        }

        $('<strong/>', {
            'text': service.name
        })
            .appendTo($serviceDescription);

        if (service.description) {
            $('<br/>')
                .appendTo($serviceDescription);

            $('<span/>', {
                'text': service.description
            })
                .appendTo($serviceDescription);
        }

        if (service.duration || Number(service.price) > 0 || service.location) {
            $('<br/>')
                .appendTo($serviceDescription);
        }

        if (service.duration) {
            $('<span/>', {
                'text': '[' + EALang.duration + ' ' + service.duration + ' ' + EALang.minutes + ']'
            })
                .appendTo($serviceDescription);
        }

        if (Number(service.price) > 0) {
            $('<span/>', {
                'text': '[' + EALang.price + ' ' + service.price + ' ' + service.currency + ']'
            })
                .appendTo($serviceDescription);
        }

        if (service.location) {
            $('<span/>', {
                'text': '[' + EALang.location + ' ' + service.location + ']'
            })
                .appendTo($serviceDescription);
        }
    }

})(window.FrontendBook);
