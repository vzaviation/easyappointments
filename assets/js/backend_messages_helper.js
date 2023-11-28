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
     * MessagesHelper Class
     *
     * This class contains the methods that are used in the backend messages page.
     *
     * @class MessagesHelper
     */
    function MessagesHelper() {
        this.filterResults = {};
        this.filterLimit = 20;
    }

    /**
     * Binds the default event handlers of the backend customers page.
     */
    MessagesHelper.prototype.bindEventHandlers = function () {
        var instance = this;

        /**
         * Event: Change day span
         */
        $('#messages').on('change', '#daySpan', function () {
            const optionSelected = $(this).find("option:selected");
            const newDaySpan  = optionSelected.val();

            // Call to get the messages from the DB for the new day span
            var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_get_messages';

            var data = {
                csrfToken: GlobalVariables.csrfToken,
                day_span: newDaySpan
            };

            $.post(url, data)
                .done(function (response) {
                    instance.refreshMessages(newDaySpan, response)
                }.bind(this));
            //
        });
    };

    /*
     *  Create a day span select object
     */
    MessagesHelper.prototype.daySpanSelect = function () {
        var instance = this;

        // Set up a select object
        let daysSelect = "<select id='daySpan'>";
        for (let i = 7; i < 29; i += 7) {
            daysSelect += "<option value=" + i + ">" + i + "</option>";
        }
        daysSelect += "</select>";
        return daysSelect;
    };


    /**
     * Bring the messages form back to its initial state.
     */
    MessagesHelper.prototype.resetForm = function () {
        var instance = this;

        $('#day-span-select').append(this.daySpanSelect());

        $('#messages-note').empty();
        $('#messages-details-row').empty();

        if (! GlobalVariables.messages.length) {
            $('<p/>', {
                'style': 'margin:20px;',
                'text': 'No messages available for the last 7 days'
            })
                .appendTo('#messages-note');
        }

        $('<div/>', {
            'class': 'message-row col-md-12',
            'html': [
                // Updated time
                $('<span/>', {
                    'class': 'col-md-2 message-header',
                    'text': 'Message Time'
                }),
                // Inmate
                $('<span/>', {
                    'class': 'col-md-1 message-header',
                    'text': 'Inmate SO'
                }),
                // Message
                $('<span/>', {
                    'class': 'col-md-8 message-header',
                    'text': 'Message'
                })
            ]
        }).appendTo('#messages-details-row');

        GlobalVariables.messages.forEach(function (message) {

            var updated_date = Date.parse(message.update_datetime).toString('MM/dd/yyyy HH:mm:ss');
            // Check each message to see if the inmate has an upcoming appointment and flag it
            let messageHtml = message.message;
            if ("appointment_notice" in message) {
                messageHtml += '<br/><span style="color:darkred;font-weight:bold;">' + message.appointment_notice + '</span>';
            }
            $('<div/>', {
                'class': 'message-row col-md-12',
                'id': 'message-row-' + message.id,
                'data-id': message.id,
                'html': [
                    // Updated time
                    $('<span/>', {
                        'class': 'col-md-2 message-cell',
                        'text': updated_date
                    }),
                    // Inmate
                    $('<span/>', {
                        'class': 'col-md-1 message-cell',
                        'text': message.inmate_so_num
                    }),
                    // Message
                    $('<span/>', {
                        'class': 'col-md-8 message-cell',
                        'html': messageHtml
                    })
                ]
            })
            .appendTo('#messages-details-row');
        });

    };

    /**
     * Refresh the messages list
     */
    MessagesHelper.prototype.refreshMessages = function (daySpan, messages) {
        var instance = this;

        $('#messages-note').empty();
        $('#messages-details-row').empty();

        if (! messages.length) {
            $('<p/>', {
                'style': 'margin:20px;',
                'text': 'No messages available for the last ' + daySpan + ' days'
            })
                .appendTo('#messages-note');
        }

        $('<div/>', {
            'class': 'message-row col-md-12',
            'html': [
                // Updated time
                $('<span/>', {
                    'class': 'col-md-2 message-header',
                    'text': 'Message Time'
                }),
                // Inmate
                $('<span/>', {
                    'class': 'col-md-1 message-header',
                    'text': 'Inmate SO'
                }),
                // Message
                $('<span/>', {
                    'class': 'col-md-8 message-header',
                    'text': 'Message'
                })
            ]
        }).appendTo('#messages-details-row');

        messages.forEach(function (message) {

            var updated_date = Date.parse(message.update_datetime).toString('MM/dd/yyyy HH:mm:ss');
            // Check each message to see if the inmate has an upcoming appointment and flag it
            let messageHtml = message.message;
            if ("appointment_notice" in message) {
                messageHtml += '<br/><span style="color:darkred;font-weight:bold;">' + message.appointment_notice + '</span>';
            }
            $('<div/>', {
                'class': 'message-row col-md-12',
                'id': 'message-row-' + message.id,
                'data-id': message.id,
                'html': [
                    // Updated time
                    $('<span/>', {
                        'class': 'col-md-2 message-cell',
                        'text': updated_date
                    }),
                    // Inmate
                    $('<span/>', {
                        'class': 'col-md-1 message-cell',
                        'text': message.inmate_so_num
                    }),
                    // Message
                    $('<span/>', {
                        'class': 'col-md-8 message-cell',
                        'html': messageHtml
                    })
                ]
            })
                .appendTo('#messages-details-row');
        });

    };

    /**
     * Update the entire unread list for a user
     */
    MessagesHelper.prototype.updateUserMessageUnreadList = function (messages) {
        // Call to reload the appointments list
        var url = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_update_user_message_unread_list';

        var data = {
            csrfToken: GlobalVariables.csrfToken,
            messages: messages,
            user_id: GlobalVariables.user.id
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
    };

    window.MessagesHelper = MessagesHelper;
})();
