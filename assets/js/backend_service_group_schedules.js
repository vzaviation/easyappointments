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

window.BackendServiceGroupSchedules = window.BackendServiceGroupSchedules || {};

/**
 * Backend ServiceGroupSchedules
 *
 * Backend ServiceGroupSchedules javascript namespace. Contains the main functionality of the backend ServiceGroupSchedules
 * page. If you need to use this namespace in a different page, do not bind the default event handlers
 * during initialization.
 *
 * @module BackendServiceGroupSchedules
 */
(function (exports) {

    'use strict';

    /**
     * The page helper contains methods that implement each record type functionality
     * (for now there is only the CustomersHelper).
     *
     * @type {Object}
     */
    var helper = {};

    /**
     * Use this class instance for performing actions on the working plan.
     *
     * @type {WorkingPlan}
     */
    exports.wp = {};

    /**
     * This method initializes the backend Service Group Schedules page. If you use this namespace
     * in a different page do not use this method.
     *
     * @param {Boolean} defaultEventHandlers Optional (false), whether to bind the default
     * event handlers or not.
     */
    exports.initialize = function (defaultEventHandlers) {
        defaultEventHandlers = defaultEventHandlers || false;

        exports.wp = new WorkingPlan();
        exports.wp.bindEventHandlers();

        helper = new ServiceGroupSchedulesHelper();
        helper.resetForm();
        helper.filter('',true);
        helper.bindEventHandlers();

        if (defaultEventHandlers) {
            bindEventHandlers();
        }
    };

    /**
     * Default event handlers declaration for backend ServiceGroupSchedules page.
     */
    function bindEventHandlers() {
        //
    }

})(window.BackendServiceGroupSchedules);
