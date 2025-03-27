<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2020, Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Appointments Controller
 *
 * @package Controllers
 */
class Appointments extends EA_Controller {
    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('installation');
        $this->load->helper('google_analytics');

        $this->load->model('appointments_model');
//        $this->load->model('providers_model');
        $this->load->model('service_group_model');
        $this->load->model('resources_model');
        $this->load->model('visitors_model');
        $this->load->model('inmates_model');
        $this->load->model('inmate_visitor_model');
        $this->load->model('admins_model');
        $this->load->model('secretaries_model');
        $this->load->model('services_model');
//        $this->load->model('customers_model');
        $this->load->model('visitors_model');
        $this->load->model('settings_model');

        $this->load->library('timezones');
        $this->load->library('synchronization');
        $this->load->library('notifications');
        $this->load->library('availability');
        $this->load->driver('cache', ['adapter' => 'file']);
    }

    /**
     * Default callback method of the application.
     *
     * This method creates the appointment book wizard. If an appointment hash is provided then it means that the
     * customer followed the appointment manage link that was send with the book success email.
     *
     * @param string $appointment_hash The appointment hash identifier.
     */
    public function index($appointment_hash = '')
    {
        try
        {
            if ( ! is_app_installed())
            {
                redirect('installation/index');
                return;
            }

            $available_services = $this->services_model->get_available_services();
            $company_name = $this->settings_model->get_setting('company_name');
            $company_address = $this->settings_model->get_setting('company_address_html');
            $company_phone = $this->settings_model->get_setting('company_phone');
            $book_advance_timeout = $this->settings_model->get_setting('book_advance_timeout');
            $date_format = $this->settings_model->get_setting('date_format');
            $time_format = $this->settings_model->get_setting('time_format');
            $first_weekday = $this->settings_model->get_setting('first_weekday');
            $require_phone_number = $this->settings_model->get_setting('require_phone_number');
            $display_cookie_notice = $this->settings_model->get_setting('display_cookie_notice');
            $cookie_notice_content = $this->settings_model->get_setting('cookie_notice_content');
            $display_terms_and_conditions = $this->settings_model->get_setting('display_terms_and_conditions');
            $terms_and_conditions_content = $this->settings_model->get_setting('terms_and_conditions_content');
            $display_privacy_policy = $this->settings_model->get_setting('display_privacy_policy');
            $privacy_policy_content = $this->settings_model->get_setting('privacy_policy_content');
            $timezones = $this->timezones->to_array();
            $available_inmates = $this->inmates_model->get_available_inmates();
            $visitors_allowed = $this->settings_model->get_setting('visitors_allowed');

            // If an appointment hash is provided then it means that the customer is trying to edit a registered
            // appointment record.
            if ($appointment_hash !== '')
            {
                // Load the appointments data and enable the manage mode of the page.
                $manage_mode = TRUE;

                $results = $this->appointments_model->get_batch(['hash' => $appointment_hash]);

                if (empty($results))
                {
                    // The requested appointment doesn't exist in the database. Display a message to the customer.
                    $variables = [
                        'message_title' => lang('appointment_not_found'),
                        'message_text' => lang('appointment_does_not_exist_in_db'),
                        'message_icon' => base_url('assets/img/error.png')
                    ];

                    $this->load->view('appointments/message', $variables);

                    return;
                }

                // If the requested appointment begin date is lower than book_advance_timeout. Display a message to the
                // customer.
                $startDate = strtotime($results[0]['start_datetime']);
                $limit = strtotime('+' . $book_advance_timeout . ' minutes', strtotime('now'));

                if ($startDate < $limit)
                {
                    $hours = floor($book_advance_timeout / 60);
                    $minutes = ($book_advance_timeout % 60);

                    $view = [
                        'message_title' => lang('appointment_locked'),
                        'message_text' => strtr(lang('appointment_locked_message'), [
                            '{$limit}' => sprintf('%02d:%02d', $hours, $minutes)
                        ]),
                        'message_icon' => base_url('assets/img/error.png')
                    ];
                    $this->load->view('appointments/message', $view);
                    return;
                }

                $appointment = $results[0];
                $resource = $this->resources_model->get_full_resource_by_service_group_and_id($appointment['service_group_id'], $appointment['resource_id']);

                /*
                $customer = $this->customers_model->get_row($appointment['id_users_customer']);

                $customer_token = md5(uniqid(mt_rand(), TRUE));

                // Save the token for 10 minutes.
                $this->cache->save('customer-token-' . $customer_token, $customer['id'], 600);
                */
            }
            else
            {
                // The visitor is going to book a new appointment so there is no need for the manage functionality to
                // be initialized.
                $manage_mode = FALSE;
                $customer_token = FALSE;
                $appointment = [];
                $resource = [];
                $visitors = [];
                $inmate =[];
            }

            // Load the book appointment view.
            $variables = [
                'available_services' => $available_services,
                'available_inmates' => $available_inmates,
                'company_name' => $company_name,
                'company_address' => $company_address,
                'company_phone' => $company_phone,
                'manage_mode' => $manage_mode,
                'customer_token' => $customer_token,
                'date_format' => $date_format,
                'time_format' => $time_format,
                'first_weekday' => $first_weekday,
                'require_phone_number' => $require_phone_number,
                'appointment_data' => $appointment,
                'resource_data' => $resource,
                'visitor_data' => $visitors,
                'display_cookie_notice' => $display_cookie_notice,
                'cookie_notice_content' => $cookie_notice_content,
                'display_terms_and_conditions' => $display_terms_and_conditions,
                'terms_and_conditions_content' => $terms_and_conditions_content,
                'display_privacy_policy' => $display_privacy_policy,
                'privacy_policy_content' => $privacy_policy_content,
                'timezones' => $timezones,
                'visitors_allowed' => $visitors_allowed
            ];
        }
        catch (Exception $exception)
        {
            $variables['exceptions'][] = $exception;
        }

        $this->load->view('appointments/book', $variables);
    }

    /**
     * Cancel an existing appointment.
     *
     * This method removes an appointment from the company's schedule. In order for the appointment to be deleted, the
     * hash string must be provided. The customer can only cancel the appointment if the edit time period is not over
     * yet.
     *
     * @param string $appointment_hash This appointment hash identifier.
     */
    public function cancel($appointment_hash)
    {
        try
        {
            // Check whether the appointment hash exists in the database.
            $appointments = $this->appointments_model->get_batch(['hash' => $appointment_hash]);

            if (empty($appointments))
            {
                throw new Exception('No record matches the provided hash.');
            }

            $appointment = $appointments[0];
            $resource = $this->resources_model->get_full_resource_by_service_group_and_id($appointment['service_group_id'],$appointment['resource_id']);
            $service = $this->services_model->get_row($appointment['id_services']);
            $visitors = $this->visitors_model->get_appointment_visitors($appointment['id']);

            $settings = [
                'company_name' => $this->settings_model->get_setting('company_name'),
                'company_email' => $this->settings_model->get_setting('company_email'),
                'company_link' => $this->settings_model->get_setting('company_link'),
                'date_format' => $this->settings_model->get_setting('date_format'),
                'time_format' => $this->settings_model->get_setting('time_format')
            ];

            // Remove the appointment record from the data.
            if ( ! $this->appointments_model->delete($appointment['id']))
            {
                throw new Exception('Appointment could not be deleted from the database.');
            }

            //$this->synchronization->sync_appointment_deleted($appointment, $provider);
            $this->notifications->notify_appointment_deleted($appointment, $service, $resource, $visitors, $settings);
        }
        catch (Exception $exception)
        {
            // Display the error message to the customer.
            $exceptions[] = $exception;
        }

        $view = [
            'message_title' => lang('appointment_cancelled_title'),
            'message_text' => lang('appointment_cancelled'),
            'message_icon' => base_url('assets/img/success.png')
        ];

        if (isset($exceptions))
        {
            $view['exceptions'] = $exceptions;
        }

        $this->load->view('appointments/message', $view);
    }

    /**
     * GET an specific appointment book and redirect to the success screen.
     *
     * @param string $appointment_hash The appointment hash identifier.
     *
     * @throws Exception
     */
    public function book_success($appointment_hash)
    {
        $appointments = $this->appointments_model->get_batch(['hash' => $appointment_hash]);

        if (empty($appointments))
        {
            redirect('appointments'); // The appointment does not exist.
            return;
        }

        $appointment = $appointments[0];
        unset($appointment['notes']);

        // Don't save the visitors as customers / users any more
        //$customer = $this->customers_model->get_row($appointment['id_users_customer']);
        $visitors = $this->visitors_model->get_appointment_visitors($appointment['id']);

        $resourceObj = $this->resources_model->get_full_resource_by_service_group_and_id($appointment['service_group_id'],$appointment['resource_id']);
        $resource = json_decode(json_encode($resourceObj), true);

        $service = $this->services_model->get_row($appointment['id_services']);

        $company_name = $this->settings_model->get_setting('company_name');

        // Get any pending exceptions.
        $exceptions = $this->session->flashdata('book_success');

        $view = [
            'appointment_data' => $appointment,
            'resource_data' => [
                'id' => $resource['resource_id'],
                'name' => $resource['resource_name'],
                'description' => $resource['resource_description'],
                'timezone' => $resource['timezone'],
            ],
            'visitor_data' => [
                'id' => $visitors[0]['id'],
                'first_name' => $visitors[0]['first_name'],
                'last_name' => $visitors[0]['last_name'],
                'email' => $visitors[0]['email']
            ],
            'service_data' => $service,
            'company_name' => $company_name,
        ];

        if ($exceptions)
        {
            $view['exceptions'] = $exceptions;
        }

        $this->load->view('appointments/book_success', $view);
    }

    /**
     * This method will check if the visitor is on the inmates approved list
     * This method answers to an AJAX request.
     *
     * Outputs true if on list, false if not
     */
    public function ajax_check_visitor_authorization()
    {
        try
        {
            $inmate_id = $this->input->post('inmate_id');
            $first_name = $this->input->post('first_name');
            $last_name = $this->input->post('last_name');

            $match = false;

            // Check global setting to make sure authorization is enabled
            $visitor_authorization_flag = $this->settings_model->get_setting('visitor_authorization_flag');
            if ($visitor_authorization_flag == "1") {
                // Pull the list of visitors given the inmate_id
                $visitors = $this->inmate_visitor_model->get_inmate_visitors($inmate_id);
                foreach ($visitors as $visitor) {
                    if ( (strtolower($visitor["visitor_first_name"]) == strtolower($first_name)) &&
                        (strtolower($visitor["visitor_last_name"]) == strtolower($last_name)) ) {
                        $match = true;
                        break;
                    }
                }
            } else {
                $match = true;
            }

            $response = [
                'check_visitor_authorization' => $match
            ];

        }
        catch (Exception $exception)
        {
            $response = [
                'check_visitor_authorization' => $match,
                'error' => $exception->getMessage(),
                'trace' => config('debug') ? $exception->getTrace() : []
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function fetch_appointments_by_date_for_inmate($inmate_id,$appt_date)
    {
        try {
            // pull and return existing appointment visitors for the given date and inmate
            $appointments = $this->appointments_model->get_appointments_by_date_inmate($inmate_id, $appt_date);
            return $appointments;
        }
        catch (Exception $exception)
        {
            return NULL;
        }
    }

    /**
     * Search for the visitor in the DB and return that info if found
     * This method answers to an AJAX request.
     */
    public function ajax_fetch_visitor_information()
    {
        try
        {
            $inmate_id = $this->input->post('inmate_id');
            $appt_date = $this->input->post('appt_date');
            $first_name = $this->input->post('first_name');
            $last_name = $this->input->post('last_name');
            $birthdate = $this->input->post('birthdate');

            // See if this visitor exists in the DB
            $visitor = $this->visitors_model->get_visitor($first_name,$last_name,$birthdate);

            // pull and return existing appointment visitors for the given date and inmate
            $appointment_visitors = $this->visitors_model->get_appointment_visitors_by_date_inmate($inmate_id,$appt_date);

            $response = [
                'visitor' => $visitor,
                'appointment_visitors' => $appointment_visitors
            ];

        }
        catch (Exception $exception)
        {
            $response = [
                'visitor' => array(),
                'appointment_visitors' => array(),
                'error' => $exception->getMessage(),
                'trace' => config('debug') ? $exception->getTrace() : []
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    
    /**
     * This method will check for any visitor restrictions 
     * This method answers to an AJAX request.
     *
     * Outputs true if no restrticions, false if a restriction
     */
    public function ajax_check_visitor_appointment_restrictions()
    {
        try
        {
            $post_data = $this->input->post('post_data');
            $newAppointment = $post_data['appointment'];
            $visitor = $post_data['visitor1'];
            $inmate_id = $newAppointment["id_inmate"];
            $inmate_name = $newAppointment["inmate_name"];
            $newStartDate = new DateTime($newAppointment["start_datetime"]);

            // Set a default response if there is no match
            $response = [
                'check_visitor_appointment_restrictions' => true,
                'days' => -2
            ];

            // Check for existing visitor and get ID
            // If visitor exists, check for other appointments with inmate
            // For now, disallow any appointment more than a week out from the current day
            // TODO: Future restrictions can be handled here as well
            $visitor_id = $this->visitors_model->exists($visitor);
            if ($visitor_id != -1) {
                // First check if visitor is restricted / flagged
                $checkVisitor = $this->visitors_model->get_row($visitor_id);
                if (($checkVisitor["flag"]) && ($checkVisitor["flag"] == "1")) {
                    $response = [
                        'check_visitor_appointment_restrictions' => false,
                        'restricted' => true
                    ];
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($response));

                    return;
                }

                $today = new DateTime();
                // Num of days between the dates ...
                $days_diff = $newStartDate->diff($today)->format("%a");
                if ($days_diff > 7) {
                    $response = [
                        'check_visitor_appointment_restrictions' => false,
                        'days' => $days_diff
                    ];
                } else {
                    $response = [
                        'check_visitor_appointment_restrictions' => true,
                        'days' => $days_diff
                    ];
                }
                /*  KPB - comment out for now, but leave in case appointment based restrictions are needed
                $appointments = $this->visitors_model->get_appointments_visitor($visitor_id);
                foreach ($appointments as $appointment) {
                    if ($appointment["id_inmate"] == $inmate_id) {
                        // Sorted in most recent to oldest, so first match is most recent
                        $startDate = new DateTime($appointment["start_datetime"]);
                        // if existing appointment is in the past, ignore this check
                        $currentDate = new DateTime();
                        if ($currentDate->format('Y-m-d') <= $startDate->format('Y-m-d')) {
                            // Num of days between the dates ...
                            $days_diff = $newStartDate->diff($startDate)->format("%a");
                            if ($days_diff > 7) {
                                $response = [
                                    'check_visitor_appointment_restrictions' => false,
                                    'days' => $days_diff
                                ];
                            } else {
                                $response = [
                                    'check_visitor_appointment_restrictions' => true,
                                    'days' => $days_diff
                                ];
                            }
                        } else {
                            $response = [
                                'check_visitor_appointment_restrictions' => true,
                                'days' => 0
                            ];
                        }
                    }
                }
                */
            } else {
                $response = [
                    'check_visitor_appointment_restrictions' => true,
                    'days' => -1
                ];
            }
        }
        catch (Exception $exception)
        {
            $response = [
                'check_visitor_appointment_restrictions' => true,
                'error' => $exception->getMessage(),
                'trace' => config('debug') ? $exception->getTrace() : []
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


    /**
     * Register the appointment to the database.
     *
     * Outputs a JSON string with the appointment ID.
     */
    public function ajax_register_appointment()
    {
        try {
            $post_data = $this->input->post('post_data');
            $captcha = $this->input->post('captcha');
            $manage_mode = filter_var($post_data['manage_mode'], FILTER_VALIDATE_BOOLEAN);
            $appointment = $post_data['appointment'];
            $visitor = $post_data['visitor1'];
            $visitors = array($visitor);
            $visitor2 = isset($post_data['visitor2']) ? $post_data['visitor2'] : NULL;
            if ($visitor2 != NULL) $visitors[] = $visitor2;
            $visitor3 = isset($post_data['visitor3']) ? $post_data['visitor3'] : NULL;
            if ($visitor3 != NULL) $visitors[] = $visitor3;

            // Check for existing appointment with this inmate
            // If exists, use that info and tack on new visitors
            // Otherwise, find first available resource for this inmate's provider block
            $appointment = $this->check_datetime_availability();

            if (!isset($appointment) || (empty(@$appointment['service_group_id'])) || (empty(@$appointment['resource_id'])))
            {
                throw new Exception(lang('requested_hour_is_unavailable'));
            }

            $resource = $this->resources_model->get_full_resource_by_service_group_and_id($appointment['service_group_id'],$appointment['resource_id']);
            $service = $this->services_model->get_row($appointment['id_services']);

            $require_captcha = $this->settings_model->get_setting('require_captcha');
            $captcha_phrase = $this->session->userdata('captcha_phrase');

            // Validate the CAPTCHA string.
            if ($require_captcha === '1' && $captcha_phrase !== $captcha) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'captcha_verification' => FALSE
                    ]));

                return;
            }

            // Either an existing appointment was found or we need to set some additional params
            if (!isset($appointment['id'])) {

                if (empty($appointment['location']) && !empty($service['location'])) {
                    $appointment['location'] = $service['location'];
                }

                // Save customer language (the language which is used to render the booking page).
                //$customer['language'] = config('language');
                //$customer_id = $this->customers_model->add($customer);

                $appointment['is_unavailable'] = (int)$appointment['is_unavailable']; // needs to be type casted
                $appointment['id'] = $this->appointments_model->add($appointment);
                $appointment['hash'] = $this->appointments_model->get_value('hash', $appointment['id']);
            }

            // Add the visitor(s)
            $v1id = $this->visitors_model->add($visitor);
            $v2id = -1;
            $v2id = isset($visitor2['first_name']) && ($visitor2['first_name'] !== "") ? $this->visitors_model->add($visitor2) : -1;
            $v3id = isset($visitor3['first_name']) && ($visitor3['first_name'] !== "")  ? $this->visitors_model->add($visitor3) : -1;

            // Load the appointment visitor records
            $appointment_visitor = [
                'appointment_id' => $appointment['id'],
                'visitor_id' => $v1id,
                'visitor_order' => 1
            ];
            $v1avid = $this->visitors_model->insert_appointment_visitor($appointment_visitor);
            $v2avid = -1;
            if ($v2id !== -1)
            {
                $appointment_visitor['visitor_id'] = $v2id;
                $appointment_visitor['visitor_order'] = 2;
                $v2avid = $this->visitors_model->insert_appointment_visitor($appointment_visitor);
            }
            $v3avid = -1;
            if ($v3id !== -1)
            {
                $appointment_visitor['visitor_id'] = $v3id;
                $appointment_visitor['visitor_order'] = 3;
                $v3avid = $this->visitors_model->insert_appointment_visitor($appointment_visitor);
            }

            $settings = [
                'company_name' => $this->settings_model->get_setting('company_name'),
                'company_link' => $this->settings_model->get_setting('company_link'),
                'company_email' => $this->settings_model->get_setting('company_email'),
                'date_format' => $this->settings_model->get_setting('date_format'),
                'time_format' => $this->settings_model->get_setting('time_format')
            ];

            $this->notifications->notify_appointment_saved($appointment, $service, $resource, $visitors, $settings, $manage_mode);

            $response = [
                'appointment_id' => $appointment['id'],
                'appointment_hash' => $appointment['hash']
            ];
        }
        catch (Exception $exception)
        {
            $this->output->set_status_header(500);

            $response = [
                'message' => $exception->getMessage(),
                'trace' => config('debug') ? $exception->getTrace() : []
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /* *****************************************************************
     *  New Appointment scheduling
     *    This should be based around inmate
     * 
     *  So, all of the associated functions will be refactored to use the inmate
     *    and will disallow double-booking
     * *****************************************************************
     */
    protected function check_datetime_availability()
    {
        $post_data = $this->input->post('post_data');
        $appointment = $post_data['appointment'];
        $date = date('Y-m-d', strtotime($appointment['start_datetime']));

        // Get service_group / resource information
        $service_id = $appointment['id_services'];
        $inmate_id = $appointment['id_inmate'];
        if ($service_id != VISITATION_SERVICE_ID) {
            // Get the valid resources for the service type
            $resources = $this->search_resources_by_service($service_id);
        } else {
            // Get the valid resources (and associated data) for this inmate
            $resources = $this->search_resources_by_inmate($inmate_id);
        }

        // Remove this for now
        // Get any existing appointment with the inmate
//        $existing_appts = $this->fetch_appointments_by_date_for_inmate($inmate_id,$date);
//        if (! empty($existing_appts)) {
//            return $existing_appt;
//        } else {
            // Check for existing appointments on this date at this time
            // Grab the first resource that is not already spoken for
            // If there are no resources left at this time, time is no longer available
            $resources_used = $this->search_resources_in_use($appointment['start_datetime']);
            $res_assigned = false;
            foreach ($resources as $searchResource) {
                if (!in_array($searchResource['resource_id'], $resources_used, true)) {
                    $appointment['service_group_id'] = $searchResource['service_group_id'];
                    $appointment['resource_id'] = $searchResource['resource_id'];
                    $res_assigned = true;
                    break;
                }
            }

            if (!$res_assigned) {
                return NULL;
            } else {
                return $appointment;
            }
//        }
    }

    public function ajax_get_unavailable_dates()
    {
        try
        {
            $service_id = $this->input->get('service_id');
            $appointment_id = $this->input->get_post('appointment_id');
            $manage_mode = $this->input->get_post('manage_mode');
            $selected_date_string = $this->input->get('selected_date');
            $selected_date = new DateTime($selected_date_string);

            $number_of_days_in_month = (int)$selected_date->format('t');
	        $inmate_id = $this->input->get_post('selectedInmateId');
            $unavailable_dates = [];
            $appointment_ids = [];

            $default_timezone = $this->settings_model->get_setting('default_timezone');
            $inmate_restricted_age = $this->settings_model->get_setting('inmate_restricted_age');
            $inmate_visitors_per_day = $this->settings_model->get_setting('inmate_visitors_per_day');
            $inmate_visits_per_week = $this->settings_model->get_setting('inmate_visits_per_week');
            $visitors_allowed = $this->settings_model->get_setting('visitors_allowed');

            //  Attorney Visits - $service_id = ATTORNEY_SERVICE_ID
            //  Other services != VISITATION_SERVICE_ID
            //  Handle these differently - all dates and times available except when inmate has existing appointment
            if ($service_id != VISITATION_SERVICE_ID) {
                // Get the valid resources for the service type
                $resources = $this->search_resources_by_service($service_id);
            } else {
                // Get the valid resources (and associated data) for this inmate
                $resources = $this->search_resources_by_inmate($inmate_id);
            }

            if ($inmate_id) {
                // First check for inmate_flag - if exists, this inmate cannot take visitors
                //  return and display a message
                $inmate = $this->inmates_model->get_row($inmate_id);
                if (($inmate["inmate_flag"]) && ($inmate["inmate_flag"] === "1")) {
                    $response[] = "restricted";
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($response));
                    return;
                }
                // Now check inmate's age - if 17 (or younger) - also restrict
                $dob = DateTime::createFromFormat('mdY', $inmate["DOB"]);
                // NOTE: At some point (around 2024-02-12) the DOB and Booking Date fields in the
                //  incoming data file changed format from mdY to m/d/Y 12:00:00 AM
                //   (ex. "9/28/1982 12:00:00 A" or "11/28/1982 12:00:00 " - the field width is capped at 20 chars)
                //  Try to handle both formats here to gracefully transition
                if ($dob == false) {  // The format is wrong / changed
                    $dob = DateTime::createFromFormat('n/j/Y H:i:s+', $inmate["DOB"]);
                }

                $age = $dob->diff(new DateTime('now', new DateTimeZone($default_timezone)))->y;
                if ($age <= $inmate_restricted_age) {
                    $response[] = "age_restricted";
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($response));
                    return;
                }
                
                // Get the appointment data of any existing visits with this inmate
                $appointments = $this->appointments_model->get_by_inmate_and_month($inmate_id,$selected_date);
                foreach ($appointments as $appt) {
                    $appointment_ids[] = $appt["id"];
                }
                
                // Do not exclude any appointment IDs
                //$exclude_appointment_ids = $manage_mode ? $appointment_ids : NULL;
                $exclude_appointment_ids = [];
            } else {
                // Skip the call if there is no inmate chosen
                $resource_ids = [];
            }
    
            // Get the service record.
            $service = $this->services_model->get_row($service_id);
            $today_date = new DateTime(date('Y-m-d 00:00:00'), new DateTimeZone($default_timezone));

            for ($i = 1; $i <= $number_of_days_in_month; $i++)
            {
                $loop_date = new DateTime($selected_date->format('Y-m') . '-' . $i, new DateTimeZone($default_timezone));

                if ($loop_date < $today_date)
                {
                    // Past dates become immediately unavailable.
                    $unavailable_dates[] = $loop_date->format('Y-m-d');
                    continue;
                } else if (($service_id == VISITATION_SERVICE_ID)
                        && ($loop_date->format('Y-m-d') == $today_date->format('Y-m-d'))) {
                    // No same day booking allowed for inmate visitation
                    // TODO: add in service check - other services may be able to book same day
                    $unavailable_dates[] = $loop_date->format('Y-m-d');
                    continue;
                }

                // Finding at least one slot of availability.
                foreach ($resources as $resource)
                {
                    $available_hours = $this->availability->get_available_hours(
                        $loop_date->format('Y-m-d'),
                        $service,
                        $resource,
                        $exclude_appointment_ids
                    );

                    if ( ! empty($available_hours))
                    {
                        break;
                    }
                }

                // No availability amongst all the resources.
                if (empty($available_hours)) {
                    $unavailable_dates[] = $loop_date->format('Y-m-d');
                } else {
                    // For inmate_visitation, this date may become unavailable for two reasons:
                    //  1. Inmate already has met inmate_visits_per_week quota
                    //  2. If inmate_visitors_per_day > 0,
                    //     Check if the inmate already has visitors_allowed appointment-visitor slots filled up for the day
                    //     If so, no go (for non-attorney visits)
                    if ($service_id == VISITATION_SERVICE_ID) {
                        if ($inmate_visitors_per_day > 0) {
                            $visitorSlotsForDate = 0;
                            foreach ($appointments as $appt) {
                                $startDate = new DateTime($appt["start_datetime"]);
                                if ($startDate->format('Y-m-d') == $loop_date->format('Y-m-d')) {
                                    $visitorSlotsForDate++;
                                }
                            }
                            if ($visitorSlotsForDate >= $inmate_visitors_per_day) {
                                $unavailable_dates[] = $loop_date->format('Y-m-d');
                            }
                        } else {
                            // Check on how many appointments in the current week (Mon - Sun)
                            $visitorSlotsForWeek = 0;
                            foreach ($appointments as $appt) {
                                $startDate = new DateTime($appt["start_datetime"]);
                                if ($startDate->format('W') == $loop_date->format('W')) {
                                    $visitorSlotsForWeek++;
                                }
                            }
                            if ($visitorSlotsForWeek >= $inmate_visits_per_week) {
                                $unavailable_dates[] = $loop_date->format('Y-m-d');
                            }
                        }
                    }
                }
            }

            $response = $unavailable_dates;
        }
        catch (Exception $exception)
        {
            $this->output->set_status_header(500);

            $response = [
                'message' => $exception->getMessage(),
                'trace' => config('debug') ? $exception->getTrace() : []
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function ajax_get_available_hours()
    {
        try
        {
            $service_id = $this->input->post('service_id');
            $selected_date = $this->input->post('selected_date');
            $inmate_id = $this->input->post('inmate_id');

            // Do not continue if there was no inmate selected
            if (empty($inmate_id))
            {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([]));

                return;
            }

            $inmate = $this->inmates_model->get_row($inmate_id);

            // If manage mode is TRUE then the following we should not consider the selected appointment when
            // calculating the available time periods of the provider.
            $exclude_appointment_id = $this->input->post('manage_mode') === 'true' ? $this->input->post('appointment_id') : NULL;

            // Find the resources available to the inmate or service
            //  Attorney Visits - $service_id = ATTORNEY_SERVICE_ID
            //  Other services != VISITATION_SERVICE_ID
            //  Handle these differently - all dates and times available except when inmate has existing appointment
            if ($service_id != VISITATION_SERVICE_ID) {
                // Get the valid resources for the service type
                $resources = $this->search_resources_by_service($service_id);
            } else {
                // Get the valid resources (and associated data) for this inmate
                $resources = $this->search_resources_by_inmate($inmate_id);
            }

            if (empty ($resources))
            {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([]));

                return;
            }

            $service = $this->services_model->get_row($service_id);

            // Finding at least one slot of availability.
            foreach ($resources as $resource)
            {
                $response = $this->availability->get_available_hours(
                    $selected_date,
                    $service,
                    $resource,
                    $exclude_appointment_id
                );

                if ( ! empty($response))
                {
                    break;
                }
            }

            //    We now should have *all* available hour slots
            //    Remove slots based on:
            //    1:  Check for an existing appointment for this inmate at this time
            //    2:  Any special handling (such as inmate classification conflicts with existing appointments from other
            //        inmates
            //    3:  Make sure all resources are not engaged for each available_hour period
            $response = $this->availability->screen_existing_appointment_times($inmate,$selected_date,$response);
            $response = $this->availability->special_hours_handling($inmate,$selected_date,$response);
            $response = $this->availability->check_resource_availability($resources,$selected_date,$response);
        }
        catch (Exception $exception)
        {
            $this->output->set_status_header(500);

            $response = [
                'message' => $exception->getMessage(),
                'trace' => config('debug') ? $exception->getTrace() : []
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function ajax_inmate_visitor_count()
    {
        try
        {
            $inmate_id = $this->input->post('inmate_id');
            $sel_date = $this->input->post('selected_date');
            $selected_date = new DateTime($sel_date);

            $appointments = $this->inmates_model->get_inmate_appointments($inmate_id);
            $visitorSlotsForDate = 0;
            foreach ($appointments as $appt) {
                $startDate = new DateTime($appt["start_datetime"]);
                if ($startDate->format('Y-m-d') == $selected_date->format('Y-m-d')) {
                    $visitorSlotsForDate++;
                } else if ($startDate->format('Y-m-d') > $selected_date->format('Y-m-d')) {
                    break;
                }
            }
            $response = [
                'visitor_slots_used' => $visitorSlotsForDate
            ];
        }
        catch (Exception $exception)
        {
            $this->output->set_status_header(500);

            $response = [
                'visitor_slots_used' => -1,
                'message' => $exception->getMessage(),
                'trace' => config('debug') ? $exception->getTrace() : []
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /* *****************************************************************
     *  New Appointment scheduling
     * *****************************************************************
     */

    /**
     * Search for any resource that can handle the requested service.
     *
     * This method will return the database IDs of the resources attached to that service through service groups
     *
     * @param int $service_id The requested service ID.
     *
     * @return array Returns the IDs of the resources that can provide the requested service.
     */
    protected function search_resources_by_service($service_id)
    {
        $resources_list = $this->resources_model->get_resources_by_service_id($service_id);
        return $resources_list;
    }

    protected function search_resources_by_inmate($inmate_id)
    {
        $available_resources = $this->inmates_model->search_resources_by_inmate($inmate_id);

        return $available_resources;
    }

    protected function search_resources_in_use($appointment_start_time)
    {
        $available_resources = $this->appointments_model->get_by_start_datetime($appointment_start_time);
        $resource_list = [];

        foreach ($available_resources as $resource)
        {
            $resource_list[] = $resource['resource_id'];
        }

        return $resource_list;
    }

    public function ajax_upload_document() {
    
    	$target_dir = $_SERVER['DOCUMENT_ROOT']."/storage/uploads/user_doc/";
    
        // create file name
        $temp = explode(".", $_FILES["user_document"]["name"]);
        $newfilename = time() . '.' . end($temp);
        $target_file = $target_dir.$newfilename;
        if (move_uploaded_file($_FILES["user_document"]["tmp_name"],$target_file)) {
            $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(["message" => "file uploaded successfully", 'fileName' => $newfilename, 'error' => false]));
        } else {
            $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(["message" => "file upload failed",  'error' => true]));
        }
    }

}
