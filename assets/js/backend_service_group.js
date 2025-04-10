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

window.BackendServiceGroup = window.BackendServiceGroup || {};

/**
 * Backend ServiceGroup
 *
 * Backend ServiceGroup javascript namespace. Contains the main functionality of the backend ServiceGroup
 * page. If you need to use this namespace in a different page, do not bind the default event handlers
 * during initialization.
 *
 * @module BackendServiceGroup
 */
(function (exports) {

    'use strict';

    /**
     * This method initializes the backend Service Group page. If you use this namespace
     * in a different page do not use this method.
     *
     * @param {Boolean} defaultEventHandlers Optional (false), whether to bind the default
     * event handlers or not.
     */
    exports.initialize = function (defaultEventHandlers, globalVars) {
        defaultEventHandlers = defaultEventHandlers || false;

        // Initialize TimeZone drop downs
        // Default to Chicago, but override if set
        $('#timezone_0').val("America/Chicago");
        const sgArr = globalVars.serviceGroups;
        for (const serviceGroup of sgArr) {
            const sgTzName = 'name="timezone_' + serviceGroup.service_group_id + '"';
            $('select[' + sgTzName + ']').val(serviceGroup.timezone);
        }
    };

    /**
     * Default event handlers declaration for backend ServiceGroup page.
     */
    function bindEventHandlers() {
        //
    }

})(window.BackendServiceGroup);
