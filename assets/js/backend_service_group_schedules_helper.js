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
     * ServiceGroupSchedules Helper
     *
     * This class contains the ServiceGroupSchedules helper class declaration, along with the "ServiceGroupSchedules" tab
     * event handlers. By dividing the backend/users tab functionality into separate files
     * it is easier to maintain the code.
     *
     * @class ServiceGroupSchedulesHelper
     */
    var ServiceGroupSchedulesHelper = function () {
        this.filterResults = {}; // Store the results for later use.
        this.filterLimit = 20;
    };

    /**
     * Bind the event handlers for the backend/users "ServiceGroupSchedules" tab.
     */
    ServiceGroupSchedulesHelper.prototype.bindEventHandlers = function () {
        /**
         * Event: Filter ServiceGroupSchedules Form "Submit"
         *
         * Filter the provider records with the given key string.
         *
         * @param {jQuery.Event} event
         */
        $('#servicegroups').on('submit', '#filter-servicegroups form', function (event) {
            event.preventDefault();
            var key = $('#filter-servicegroups .key').val();
            $('.selected').removeClass('selected');
            this.resetForm();
            this.filter(key);
        }.bind(this));

        /**
         * Event: Clear Filter Button "Click"
         */
        $('#servicegroups').on('click', '#filter-servicegroups .clear', function () {
            this.filter('');
            $('#filter-servicegroups .key').val('');
            this.resetForm();
        }.bind(this));

        /**
         * Event: Filter Service Groups Row "Click"
         *
         * Display the selected service group data to the user.
         */
        $('#servicegroups').on('click', '.servicegroup-row', function (event) {
            if ($('#filter-servicegroups .filter').prop('disabled')) {
                $('#filter-servicegroups .results').css('color', '#AAA');
                return; // Exit because we are currently on edit mode.
            }

            var serviceGroupId = $(event.currentTarget).attr('data-id');
            var serviceGroup = this.filterResults.find(function (filterResult) {
                return Number(filterResult.service_group_id) === Number(serviceGroupId);
            });

            this.display(serviceGroup);

            $('.save-cancel-group').show();
            $('#servicegroups .add-break, .add-working-plan-exception, #reset-working-plan').prop('disabled', false);
            $('.breaks').find('.edit-break, .delete-break').prop('disabled', false);
            $('.working-plan-exceptions').find('.edit-working-plan-exception, .delete-working-plan-exception').prop('disabled', false);
            $('#servicegroups .working-plan input:checkbox').prop('disabled', false);
            BackendServiceGroupSchedules.wp.timepickers(false);

            $('#filter-servicegroups .selected').removeClass('selected');
            $(event.currentTarget).addClass('selected');
        }.bind(this));

        /**
         * Event: Save Service Group Schedule Button "Click"
         */
        $('#servicegroups').on('click', '#save-servicegroupschedule', function () {
            const schedule = {
                service_group_schedule_id: $('#service-group-schedule-id').val(),
                service_group_id: $('#service-group-id').val(),
                working_plan: JSON.stringify(BackendServiceGroupSchedules.wp.get()),
                working_plan_exceptions: JSON.stringify(BackendServiceGroupSchedules.wp.getWorkingPlanExceptions())
            };

            this.save(schedule);
        }.bind(this));

    };

    /**
     * Remove the previously registered event handlers.
     */
    ServiceGroupSchedulesHelper.prototype.unbindEventHandlers = function () {
        $('#servicegroups')
            .off('submit', '#filter-servicegroups form')
            .off('click', '#filter-servicegroups .clear')
            .off('click', '.servicegroups-row')
            .off('click', '#save-servicegroupschedule')
            .off('shown.bs.tab', 'a[data-toggle="tab"]')
            .off('click', '#reset-working-plan');
    };

    /**
     * Save service group schedule record to database.
     *
     * @param {Object} schedule Contains the admin record data. If an 'id' value is provided
     * then the update operation is going to be executed.
     */
    ServiceGroupSchedulesHelper.prototype.save = function (schedule) {
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_service_group_schedule';
        var data = {
            csrfToken: GlobalVariables.csrfToken,
            schedule: JSON.stringify(schedule)
        };

        $.post(url, data)
            .done(function (response) {
//                Backend.displayNotification(EALang.provider_saved);
//                this.resetForm();
//                $('#filter-servicegroups .key').val('');
//                this.filter('', response.id, true);
            }.bind(this));
    };

    /**
     * Resets the admin tab form back to its initial state.
     */
    ServiceGroupSchedulesHelper.prototype.resetForm = function () {
        $('#filter-servicegroups .selected').removeClass('selected');
        $('#filter-servicegroups button').prop('disabled', false);
        $('#filter-servicegroups .results').css('color', '');

        $('.save-cancel-group').hide();

        $('#servicegroups .add-break, .add-working-plan-exception, #reset-working-plan').prop('disabled', true);
        BackendServiceGroupSchedules.wp.timepickers(true);
        $('#servicegroups .working-plan input:text').timepicker('destroy');
        $('#servicegroups .working-plan input:checkbox').prop('disabled', true);
        $('.breaks').find('.edit-break, .delete-break').prop('disabled', true);
        $('.working-plan-exceptions').find('.edit-working-plan-exception, .delete-working-plan-exception').prop('disabled', true);

        $('#servicegroups .working-plan tbody').empty();
        $('#servicegroups .breaks tbody').empty();
        $('#servicegroups .working-plan-exceptions tbody').empty();
    };

    /**
     * Display a service group record into the admin form.
     *
     * @param {Object} serviceGroup Contains the service_group record data.
     */
    ServiceGroupSchedulesHelper.prototype.display = function (serviceGroup) {
        $('#service-group-id').val(serviceGroup.service_group_id);

        // Display working plan info
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_get_service_group_schedule_from_service_group_id';
        var data = {
            csrfToken: GlobalVariables.csrfToken,
            service_group_id: serviceGroup.service_group_id
        };

        $.post(url, data)
            .done(function (response) {
                let working_plan = '{"sunday":null,"monday":null,"tuesday":null,"wednesday":null,"thursday":null,"friday":null,"saturday":null}';
                let working_plan_exceptions = '';

                if (response) {
                    $('#service-group-schedule-id').val(response.service_group_schedule_id);
                    working_plan = response.working_plan;
                    working_plan_exceptions = response.working_plan_exceptions;
                }
                const workingPlan = $.parseJSON(working_plan);
                BackendServiceGroupSchedules.wp.setup(workingPlan);
                if (working_plan_exceptions !== '') {
                    const workingPlanExceptions = $.parseJSON(working_plan_exceptions);
                    BackendServiceGroupSchedules.wp.setupWorkingPlanExceptions(workingPlanExceptions);
                }
                Backend.placeFooterToBottom();

            }.bind(this));
    };

    /**
     * Filters serviceGroup records depending on a string key.
     *
     * @param {string} key This is used to filter the serviceGroup records of the database.
     * @param {numeric} selectId Optional, if set, when the function is complete a result row can be set as selected.
     * @param {bool} display Optional (false), if true the selected record will be also displayed.
     */
    ServiceGroupSchedulesHelper.prototype.filter = function (key, selectId, display) {
        display = display || false;

        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_filter_servicegroups';
        var data = {
            csrfToken: GlobalVariables.csrfToken,
            key: key,
            limit: this.filterLimit
        };

        $.post(url, data)
            .done(function (response) {
                this.filterResults = response;

                $('#filter-servicegroups .results').empty();
                response.forEach(function (result) {
                    $('#filter-servicegroups .results')
                        .append(this.getFilterHtml(result))
                        .append($('<hr/>'));
                }.bind(this));

                if (!response.length) {
                    $('#filter-servicegroups .results').append(
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
                        .appendTo('#filter-servicegroups .results');
                }

                if (selectId) {
                    this.select(selectId, display);
                }
            }.bind(this));
    };

    /**
     * Get a serviceGroup row html code that is going to be displayed on the filter results list.
     *
     * @param {Object} serviceGroup Contains the serviceGroup record data.
     *
     * @return {String} The html code that represents the record on the filter results list.
     */
    ServiceGroupSchedulesHelper.prototype.getFilterHtml = function (serviceGroup) {

        return $('<div/>', {
            'class': 'servicegroup-row entry',
            'data-id': serviceGroup.service_group_id,
            'html': [
                $('<strong/>', {
                    'text': serviceGroup.group_name
                }),
                $('<br/>'),
                $('<span/>', {
                    'style': 'font-size: smaller;',
                    'text': serviceGroup.group_description
                }),
                $('<br/>'),
                $('<span/>', {
                    'style': 'font-size: smaller;',
                    'text': 'Service:' + serviceGroup.service_name
                }),
            ]
        });
    };

    /**
     * Initialize the editable functionality to the break day table cells.
     *
     * @param {Object} $selector The cells to be initialized.
     */
    ServiceGroupSchedulesHelper.prototype.editableDayCell = function ($selector) {
        var weekDays = {};
        weekDays[EALang.monday] = 'Monday';
        weekDays[EALang.tuesday] = 'Tuesday';
        weekDays[EALang.wednesday] = 'Wednesday';
        weekDays[EALang.thursday] = 'Thursday';
        weekDays[EALang.friday] = 'Friday';
        weekDays[EALang.saturday] = 'Saturday';
        weekDays[EALang.sunday] = 'Sunday';


        $selector.editable(function (value, settings) {
            return value;
        }, {
            type: 'select',
            data: weekDays,
            event: 'edit',
            height: '30px',
            submit: '<button type="button" class="d-none submit-editable">Submit</button>',
            cancel: '<button type="button" class="d-none cancel-editable">Cancel</button>',
            onblur: 'ignore',
            onreset: function (settings, td) {
                if (!BackendUsers.enableCancel) {
                    return false; // disable ESC button
                }
            },
            onsubmit: function (settings, td) {
                if (!BackendUsers.enableSubmit) {
                    return false; // disable Enter button
                }
            }
        });
    };

    /**
     * Initialize the editable functionality to the break time table cells.
     *
     * @param {jQuery} $selector The cells to be initialized.
     */
    ServiceGroupSchedulesHelper.prototype.editableTimeCell = function ($selector) {
        $selector.editable(function (value, settings) {
            // Do not return the value because the user needs to press the "Save" button.
            return value;
        }, {
            event: 'edit',
            height: '25px',
            submit: '<button type="button" class="d-none submit-editable">Submit</button>',
            cancel: '<button type="button" class="d-none cancel-editable">Cancel</button>',
            onblur: 'ignore',
            onreset: function (settings, td) {
                if (!BackendUsers.enableCancel) {
                    return false; // disable ESC button
                }
            },
            onsubmit: function (settings, td) {
                if (!BackendUsers.enableSubmit) {
                    return false; // disable Enter button
                }
            }
        });
    };

    /**
     * Select and display a ServiceGroupSchedules filter result on the form.
     *
     * @param {Number} id Record id to be selected.
     * @param {Boolean} display Optional (false), if true the record will be displayed on the form.
     */
    ServiceGroupSchedulesHelper.prototype.select = function (id, display) {
        display = display || false;

        // Select record in filter results.
        $('#filter-servicegroups .servicegroup-row[data-id="' + id + '"]').addClass('selected');

        // Display record in form (if display = true).
        if (display) {
            var provider = this.filterResults.find(function (filterResult) {
                return Number(filterResult.id) === Number(id);
            }.bind(this));

            this.display(provider);

            $('#edit-provider, #delete-provider').prop('disabled', false);
        }
    };

    window.ServiceGroupSchedulesHelper = ServiceGroupSchedulesHelper;

})();
