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
     * ServiceGroupResources Helper
     *
     * This class contains the ServiceGroupResources helper class declaration, along with the "ServiceGroupResources" tab
     * event handlers. By dividing the backend/users tab functionality into separate files
     * it is easier to maintain the code.
     *
     * @class ServiceGroupResourcesHelper
     */
    var ServiceGroupResourcesHelper = function () {
        this.filterResults = {}; // Store the results for later use.
        this.filterLimit = 20;
    };

    /**
     * Bind the event handlers for the backend/users "ServiceGroupResources" tab.
     */
    ServiceGroupResourcesHelper.prototype.bindEventHandlers = function () {
        /**
         * Event: Filter ServiceGroupResources Form "Submit"
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

            $('#available_resources option').each(function() {
                let resId = $(this).val();
                $('#available_resources option[value="' + resId + '"]').remove();
            });

            GlobalVariables.allResources.forEach(function(resource, index, arr) {
                const newOption = $('<option>', {
                    value: resource.resource_id,
                    text: resource.resource_name
                });
                $('#available_resources').append(newOption);
            });

            $('#selected_resources option').each(function() {
                let resId = $(this).val();
                $('#selected_resources option[value="' + resId + '"]').remove();
            });

            var serviceGroupId = $(event.currentTarget).attr('data-id');
            var serviceGroup = this.filterResults.find(function (filterResult) {
                return Number(filterResult.service_group_id) === Number(serviceGroupId);
            });

            this.display(serviceGroup);

            $('.save-cancel-group').show();

            $('#filter-servicegroups .selected').removeClass('selected');
            $(event.currentTarget).addClass('selected');
        }.bind(this));

        /**
         * Event: Save Service Group Resources Button "Click"
         */
        $('#servicegroups').on('click', '#select_resources', function () {
            // Move any selected values from available to selected list
            $('#available_resources option:selected').each(function() {
                let resId = $(this).val();
                $('#available_resources option[value="' + resId + '"]').remove().appendTo('#selected_resources');
            });
        }.bind(this));

        /**
         * Event: Left-button click
         */
        $('#servicegroups').on('click', '#unselect_resources', function () {
            // Move any selected values from available to selected list
            $('#selected_resources option:selected').each(function() {
                let resId = $(this).val();
                $('#selected_resources option[value="' + resId + '"]').remove().appendTo('#available_resources');
            });
        }.bind(this));

        /**
         * Event: Save-button click
         */
        $('#servicegroups').on('click', '#save-servicegroupresources', function () {
            // Move any selected values from available to selected list
            let resources = new Array();
            $('#selected_resources option').each(function() {
                let resId = $(this).val();
                resources.push(resId);
            });

            this.save(resources);

        }.bind(this));
    };

    /**
     * Remove the previously registered event handlers.
     */
    ServiceGroupResourcesHelper.prototype.unbindEventHandlers = function () {
        $('#servicegroups')
            .off('submit', '#filter-servicegroups form')
            .off('click', '#filter-servicegroups .clear')
            .off('click', '.servicegroups-row')
            .off('click', '#save-servicegroupresource')
            .off('shown.bs.tab', 'a[data-toggle="tab"]')
    };

    /**
     * Save service group resources records to database.
     *
     * @param {Object} resources Contains the admin record data. If an 'id' value is provided
     * then the update operation is going to be executed.
     */
    ServiceGroupResourcesHelper.prototype.save = function (resources) {
        const serviceGroupId = $('#service-group-id').val();

        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_service_group_resources';
        var data = {
            csrfToken: GlobalVariables.csrfToken,
            service_group_id: serviceGroupId,
            resources: JSON.stringify(resources)
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
    ServiceGroupResourcesHelper.prototype.resetForm = function () {
        $('#filter-servicegroups .selected').removeClass('selected');
        $('#filter-servicegroups button').prop('disabled', false);
        $('#filter-servicegroups .results').css('color', '');

        $('.save-cancel-group').hide();
    };

    /**
     * Display a service group record into the admin form.
     *
     * @param {Object} serviceGroup Contains the service_group record data.
     */
    ServiceGroupResourcesHelper.prototype.display = function (serviceGroup) {
        $('#service-group-id').val(serviceGroup.service_group_id);

        // Display associated resources info
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_get_service_group_resources_from_service_group_id';
        var data = {
            csrfToken: GlobalVariables.csrfToken,
            service_group_id: serviceGroup.service_group_id
        };

        $.post(url, data)
            .done(function (response) {
                if (response) {
                    // Move any selected values from available to selected list
                    $('#available_resources option').each(function() {
                        let resId = $(this).val();
                        for (const resp of response) {
                            if (resId == resp.resource_id) {
                                $('#available_resources option[value="' + resId + '"]').remove().appendTo('#selected_resources');
                                break;
                            }
                        }
                    });
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
    ServiceGroupResourcesHelper.prototype.filter = function (key, selectId, display) {
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
    ServiceGroupResourcesHelper.prototype.getFilterHtml = function (serviceGroup) {

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
     * Select and display a ServiceGroupResources filter result on the form.
     *
     * @param {Number} id Record id to be selected.
     * @param {Boolean} display Optional (false), if true the record will be displayed on the form.
     */
    ServiceGroupResourcesHelper.prototype.select = function (id, display) {
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

    window.ServiceGroupResourcesHelper = ServiceGroupResourcesHelper;

})();
