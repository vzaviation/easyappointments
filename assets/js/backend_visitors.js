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

window.BackendVisitors = window.BackendVisitors || {};

/**
 * Backend Visitors
 *
 * Backend Visitors javascript namespace. Contains the main functionality of the backend visitors
 * page. If you need to use this namespace in a different page, do not bind the default event handlers
 * during initialization.
 *
 * @module BackendVisitors
 */
(function (exports) {

    'use strict';

    /**
     * The page helper contains methods that implement each record type functionality
     * (for now there is only the VisitorsHelper).
     *
     * @type {Object}
     */
    var helper = {};

    /**
     * This method initializes the backend visitors page. If you use this namespace
     * in a different page do not use this method.
     *
     * @param {Boolean} defaultEventHandlers Optional (false), whether to bind the default
     * event handlers or not.
     */
    exports.initialize = function (defaultEventHandlers) {
        defaultEventHandlers = defaultEventHandlers || false;

        // Add the available languages to the language dropdown.
        availableLanguages.forEach(function (language) {
            $('#language').append(new Option(language, language));
        });

        helper = new VisitorsHelper();
        helper.resetForm();
        helper.filter('');
        helper.bindEventHandlers();

        if (defaultEventHandlers) {
            bindEventHandlers();
        }
    };

    /**
     * Default event handlers declaration for backend visitors page.
     */
    function bindEventHandlers() {
        //
    }

})(window.BackendVisitors);
