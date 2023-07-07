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
        $this->load->model('providers_model');
        $this->load->model('visitors_model');
        $this->load->model('inmates_model');
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
            $available_providers = $this->providers_model->get_available_providers();
            $company_name = $this->settings_model->get_setting('company_name');
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
            $display_any_provider = $this->settings_model->get_setting('display_any_provider');
            $timezones = $this->timezones->to_array();
            $available_inmates = $this->inmates_model->get_available_inmates();
            

            // Remove the data that are not needed inside the $available_providers array.
            foreach ($available_providers as $index => $provider)
            {
                $stripped_data = [
                    'id' => $provider['id'],
                    'first_name' => $provider['first_name'],
                    'last_name' => $provider['last_name'],
                    'services' => $provider['services'],
                    'timezone' => $provider['timezone'],
                    'inmate_classification_level' => $provider['inmate_classification_level']
                ];
                $available_providers[$index] = $stripped_data;
            }

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
                $provider = $this->providers_model->get_row($appointment['id_users_provider']);
                $customer = $this->customers_model->get_row($appointment['id_users_customer']);

                $customer_token = md5(uniqid(mt_rand(), TRUE));

                // Save the token for 10 minutes.
                $this->cache->save('customer-token-' . $customer_token, $customer['id'], 600);
            }
            else
            {
                // The customer is going to book a new appointment so there is no need for the manage functionality to
                // be initialized.
                $manage_mode = FALSE;
                $customer_token = FALSE;
                $appointment = [];
                $provider = [];
                $customer = [];
                $inmate =[];
            }

            // Load the book appointment view.
            $variables = [
                'available_services' => $available_services,
                'available_providers' => $available_providers,
                'available_inmates' => $available_inmates,
                'company_name' => $company_name,
                'manage_mode' => $manage_mode,
                'customer_token' => $customer_token,
                'date_format' => $date_format,
                'time_format' => $time_format,
                'first_weekday' => $first_weekday,
                'require_phone_number' => $require_phone_number,
                'appointment_data' => $appointment,
                'provider_data' => $provider,
                'customer_data' => $customer,
                'display_cookie_notice' => $display_cookie_notice,
                'cookie_notice_content' => $cookie_notice_content,
                'display_terms_and_conditions' => $display_terms_and_conditions,
                'terms_and_conditions_content' => $terms_and_conditions_content,
                'display_privacy_policy' => $display_privacy_policy,
                'privacy_policy_content' => $privacy_policy_content,
                'timezones' => $timezones,
                'display_any_provider' => $display_any_provider,
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
            $provider = $this->providers_model->get_row($appointment['id_users_provider']);
            $customer = $this->customers_model->get_row($appointment['id_users_customer']);
            $service = $this->services_model->get_row($appointment['id_services']);

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

            $this->synchronization->sync_appointment_deleted($appointment, $provider);
            $this->notifications->notify_appointment_deleted($appointment, $service, $provider, $customer, $settings);
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

        $provider = $this->providers_model->get_row($appointment['id_users_provider']);

        $service = $this->services_model->get_row($appointment['id_services']);

        $company_name = $this->settings_model->get_setting('company_name');

        // Get any pending exceptions.
        $exceptions = $this->session->flashdata('book_success');

        $view = [
            'appointment_data' => $appointment,
            'provider_data' => [
                'id' => $provider['id'],
                'first_name' => $provider['first_name'],
                'last_name' => $provider['last_name'],
                'email' => $provider['email'],
                'timezone' => $provider['timezone'],
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
     * Get the available appointment hours for the given date.
     *
     * This method answers to an AJAX request. It calculates the available hours for the given service, provider and
     * date.
     *
     * Outputs a JSON string with the availabilities.
     */
    public function ajax_get_available_hours_orig()
    {
        try
        {
            $provider_id = $this->input->post('provider_id');
            $service_id = $this->input->post('service_id');
            $selected_date = $this->input->post('selected_date');

            // Do not continue if there was no provider selected (more likely there is no provider in the system).
            if (empty($provider_id))
            {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([]));

                return;
            }

            // If manage mode is TRUE then the following we should not consider the selected appointment when
            // calculating the available time periods of the provider.
            $exclude_appointment_id = $this->input->post('manage_mode') === 'true' ? $this->input->post('appointment_id') : NULL;

            // If the user has selected the "any-provider" option then we will need to search for an available provider
            // that will provide the requested service.
            if ($provider_id === ANY_PROVIDER)
            {
                $provider_id = $this->search_any_provider($selected_date, $service_id);

                if ($provider_id === NULL)
                {
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([]));

                    return;
                }
            }

            $service = $this->services_model->get_row($service_id);

            $provider = $this->providers_model->get_row($provider_id);

            $response = $this->availability->get_available_hours($selected_date, $service, $provider, $exclude_appointment_id);
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

    /**
     * Search for any provider that can handle the requested service.
     *
     * This method will return the database ID of the provider with the most available periods.
     *
     * @param string $date The date to be searched (Y-m-d).
     * @param int $service_id The requested service ID.
     *
     * @return int Returns the ID of the provider that can provide the service at the selected date.
     *
     * @throws Exception
     */
    protected function search_any_provider($date, $service_id)
    {
        $available_providers = $this->providers_model->get_available_providers();

        $service = $this->services_model->get_row($service_id);

        $provider_id = NULL;

        $max_hours_count = 0;

        foreach ($available_providers as $provider)
        {
            foreach ($provider['services'] as $provider_service_id)
            {
                if ($provider_service_id == $service_id)
                {
                    // Check if the provider is available for the requested date.
                    $available_hours = $this->availability->get_available_hours($date, $service, $provider);

                    if (count($available_hours) > $max_hours_count)
                    {
                        $provider_id = $provider['id'];
                        $max_hours_count = count($available_hours);
                    }
                }
            }
        }

        return $provider_id;
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

            // Pull the list of visitors given the inmate_id
            $visitors = $this->inmates_model->get_inmate_visitors($inmate_id);
            foreach ($visitors as $visitor) {
                if ( (strtolower($visitor["visitor_first_name"]) == strtolower($first_name)) &&
                     (strtolower($visitor["visitor_last_name"]) == strtolower($last_name)) ) {
                        $match = true;
                        break;
                }
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
        try
        {
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

            // Check appointment availability before registering it to the database.
            $appointment['id_users_provider'] = $this->check_datetime_availability();

            if ( ! $appointment['id_users_provider'])
            {
                throw new Exception(lang('requested_hour_is_unavailable'));
            }

            $provider = $this->providers_model->get_row($appointment['id_users_provider']);
            $service = $this->services_model->get_row($appointment['id_services']);

            $require_captcha = $this->settings_model->get_setting('require_captcha');
            $captcha_phrase = $this->session->userdata('captcha_phrase');

            // Validate the CAPTCHA string.
            if ($require_captcha === '1' && $captcha_phrase !== $captcha)
            {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'captcha_verification' => FALSE
                    ]));

                return;
            }

            // TODO: Add some checks for existing visitor
//            if ($this->visitors_model->exists($visitor1))
//            {
//                $visitor1['id'] = $this->visitors_model->find_record_id($visitor);
//            }

            if (empty($appointment['location']) && ! empty($service['location']))
            {
                $appointment['location'] = $service['location'];
            }

            // Save customer language (the language which is used to render the booking page).
            //$customer['language'] = config('language');
            //$customer_id = $this->customers_model->add($customer);

//            $appointment['id_users_customer'] = $customer_id;
            $appointment['is_unavailable'] = (int)$appointment['is_unavailable']; // needs to be type casted
            $appointment['id'] = $this->appointments_model->add($appointment);
            $appointment['hash'] = $this->appointments_model->get_value('hash', $appointment['id']);

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

            $this->synchronization->sync_appointment_saved($appointment, $service, $provider, $visitors, $settings, $manage_mode);
            $this->notifications->notify_appointment_saved($appointment, $service, $provider, $visitors, $settings, $manage_mode);

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
     *    This should be based around inmate in addition to provider, as any open provider can
     *    be used, and is selected at time of appointment at the jail
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

        if ((! isset($appointment['id_users_provider'])) ||
            ($appointment['id_users_provider'] == NULL) ||
            ($appointment['id_users_provider'] === ANY_PROVIDER))
        {

            $appointment['id_users_provider'] = $this->search_any_provider($date, $appointment['id_services']);

            return $appointment['id_users_provider'];
        }

        $service = $this->services_model->get_row($appointment['id_services']);

        $exclude_appointment_id = isset($appointment['id']) ? $appointment['id'] : NULL;

        $provider = $this->providers_model->get_row($appointment['id_users_provider']);

        $available_hours = $this->availability->get_available_hours($date, $service, $provider, $exclude_appointment_id);

        $is_still_available = FALSE;

        $appointment_hour = date('H:i', strtotime($appointment['start_datetime']));

        foreach ($available_hours as $available_hour)
        {
            if ($appointment_hour === $available_hour)
            {
                $is_still_available = TRUE;
                break;
            }
        }

        return $is_still_available ? $appointment['id_users_provider'] : NULL;
    }

    public function ajax_get_unavailable_dates()
    {
        try
        {
            $provider_id = $this->input->get('provider_id');
            $service_id = $this->input->get('service_id');
            $appointment_id = $this->input->get_post('appointment_id');
            $manage_mode = $this->input->get_post('manage_mode');
            $selected_date_string = $this->input->get('selected_date');
            $selected_date = new DateTime($selected_date_string);

            $number_of_days_in_month = (int)$selected_date->format('t');
	        $inmate_id = $this->input->get_post('selectedInmateId');
            $unavailable_dates = [];
            $appointment_ids = [];

            //  Attorney Visits - $service_id = ATTORNEY_SERVICE_ID
            //  Handle these differently - all dates and times available except when inmate has existing appointment
            if ($service_id == ATTORNEY_SERVICE_ID) {
                // Get the valid providers for the service type
                $provider_ids = $provider_id === ANY_PROVIDER
                ? $this->search_providers_by_service($service_id)
                : [$provider_id];
            } else {
                // Get the valid providers for this inmate
                $provider_ids = $provider_id === ANY_PROVIDER
                ? $this->search_providers_by_inmates($inmate_id, $service_id)
                : [$provider_id];            
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
                $age = $dob->diff(new DateTime('now'))->y;
                if ($age <= 17) {
                    $response[] = "restricted";
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($response));
                    return;
                }
                
                // Get the appointment data of any existing visits with this inmate
                $appointments = $this->inmates_model->get_inmate_appointments($inmate_id);
                foreach ($appointments as $appt) {
                    $appointment_ids[] = $appt["id"];
                }
                
                // Do not exclude any appointment IDs
                //$exclude_appointment_ids = $manage_mode ? $appointment_ids : NULL;
                $exclude_appointment_ids = [];
            } else {
                // Skip the call if there is no inmate chosen
                $provider_ids = [];
            }
    
            // Get the service record.
            $service = $this->services_model->get_row($service_id);

            for ($i = 1; $i <= $number_of_days_in_month; $i++)
            {
                $current_date = new DateTime($selected_date->format('Y-m') . '-' . $i);

                if ($current_date < new DateTime(date('Y-m-d 00:00:00')))
                {
                    // Past dates become immediately unavailable.
                    $unavailable_dates[] = $current_date->format('Y-m-d');
                    continue;
                } else if (($service_id != 2) && ($current_date->format('Y-m-d') == new DateTime(date('Y-m-d')))) {
                    // No same day booking allowed for inmate visitation
                    // TODO: add in service check - other services may be able to book same day
                    $unavailable_dates[] = $current_date->format('Y-m-d');
                    continue;
                }

                // Finding at least one slot of availability.
                foreach ($provider_ids as $current_provider_id)
                {
                    $provider = $this->providers_model->get_row($current_provider_id);

                    $available_hours = $this->availability->get_available_hours(
                        $current_date->format('Y-m-d'),
                        $service,
                        $provider,
                        $exclude_appointment_ids
                    );

                    if ( ! empty($available_hours))
                    {
                        break;
                    }
                }

                // No availability amongst all the provider.
                if (empty($available_hours)) {
                    $unavailable_dates[] = $current_date->format('Y-m-d');
                } else {
                    // Check if the inmate already has 3 appointment-visitor slots filled up for the day
                    // If so, no go (for non-attorney visits)
                    if ($service_id != 2) {
                        $visitorSlotsForDate = 0;
                        foreach ($appointments as $appt) {
                            $startDate = new DateTime($appt["start_datetime"]);
                            if ($startDate->format('Y-m-d') == $current_date->format('Y-m-d')) {
                                $visitorSlotsForDate++;
                            }
                        }
                        if ($visitorSlotsForDate >= 3) {
                            $unavailable_dates[] = $current_date->format('Y-m-d');
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
            $provider_id = $this->input->post('provider_id');
            $service_id = $this->input->post('service_id');
            $selected_date = $this->input->post('selected_date');

            // Do not continue if there was no provider selected (more likely there is no provider in the system).
            if (empty($provider_id))
            {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([]));

                return;
            }

            // If manage mode is TRUE then the following we should not consider the selected appointment when
            // calculating the available time periods of the provider.
            $exclude_appointment_id = $this->input->post('manage_mode') === 'true' ? $this->input->post('appointment_id') : NULL;

            // If the user has selected the "any-provider" option then we will need to search for an available provider
            // that will provide the requested service.
            if (($provider_id === ANY_PROVIDER) || ($provider_id == -1))
            {
                $provider_id = $this->search_any_provider($selected_date, $service_id);
                if ($provider_id === NULL)
                {
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([]));

                    return;
                }
            }

            $service = $this->services_model->get_row($service_id);

            $provider = $this->providers_model->get_row($provider_id);

            $response = $this->availability->get_available_hours($selected_date, $service, $provider, $exclude_appointment_id);
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
     * Check whether the provider is still available in the selected appointment date.
     *
     * It might be times where two or more customers select the same appointment date and time. This shouldn't be
     * allowed to happen, so one of the two customers will eventually get the preferred date and the other one will have
     * to choose for another date. Use this method just before the customer confirms the appointment details. If the
     * selected date was taken in the mean time, the customer must be prompted to select another time for his
     * appointment.
     *
     * @return int Returns the ID of the provider that is available for the appointment.
     *
     * @throws Exception
     */
    protected function check_datetime_availability_orig()
    {
        $post_data = $this->input->post('post_data');

        $appointment = $post_data['appointment'];

        $date = date('Y-m-d', strtotime($appointment['start_datetime']));

        if ($appointment['id_users_provider'] === ANY_PROVIDER)
        {

            $appointment['id_users_provider'] = $this->search_any_provider($date, $appointment['id_services']);

            return $appointment['id_users_provider'];
        }

        $service = $this->services_model->get_row($appointment['id_services']);

        $exclude_appointment_id = isset($appointment['id']) ? $appointment['id'] : NULL;

        $provider = $this->providers_model->get_row($appointment['id_users_provider']);

        $available_hours = $this->availability->get_available_hours($date, $service, $provider, $exclude_appointment_id);

        $is_still_available = FALSE;

        $appointment_hour = date('H:i', strtotime($appointment['start_datetime']));

        foreach ($available_hours as $available_hour)
        {
            if ($appointment_hour === $available_hour)
            {
                $is_still_available = TRUE;
                break;
            }
        }

        return $is_still_available ? $appointment['id_users_provider'] : NULL;
    }

    /**
     * Get Unavailable Dates
     *
     * Get an array with the available dates of a specific provider, service and month of the year. Provide the
     * "provider_id", "service_id" and "selected_date" as GET parameters to the request. The "selected_date" parameter
     * must have the Y-m-d format.
     *
     * Outputs a JSON string with the unavailable dates. that are unavailable.
     */
    public function ajax_get_unavailable_dates_orig()
    {
        try
        {
            $provider_id = $this->input->get('provider_id');
            $service_id = $this->input->get('service_id');
            $appointment_id = $this->input->get_post('appointment_id');
            $manage_mode = $this->input->get_post('manage_mode');
            $selected_date_string = $this->input->get('selected_date');
            $selected_date = new DateTime($selected_date_string);

            $number_of_days_in_month = (int)$selected_date->format('t');
	        $inmate_id = $this->input->get_post('selectedInmateId');
            $unavailable_dates = [];

    	    if ($inmate_id){
                $provider_ids = $provider_id === ANY_PROVIDER
                    ? $this->search_providers_by_inmates($inmate_id, $service_id)
                    : [$provider_id];
            } else {
                /* Skip the call if there is no inmate chosen - it just takes too long
               	$provider_ids = $provider_id === ANY_PROVIDER
                    ? $this->search_providers_by_service($service_id)
                    : [$provider_id];
                    */
                $provider_ids = [];
            }
            $exclude_appointment_id = $manage_mode ? $appointment_id : NULL;

            // Get the service record.
            $service = $this->services_model->get_row($service_id);

            for ($i = 1; $i <= $number_of_days_in_month; $i++)
            {
                $current_date = new DateTime($selected_date->format('Y-m') . '-' . $i);

                if ($current_date < new DateTime(date('Y-m-d 00:00:00')))
                {
                    // Past dates become immediately unavailable.
                    $unavailable_dates[] = $current_date->format('Y-m-d');
                    continue;
                }

                // Finding at least one slot of availability.
                foreach ($provider_ids as $current_provider_id)
                {
                    $provider = $this->providers_model->get_row($current_provider_id);

                    $available_hours = $this->availability->get_available_hours(
                        $current_date->format('Y-m-d'),
                        $service,
                        $provider,
                        $exclude_appointment_id
                    );

                    if ( ! empty($available_hours))
                    {
                        break;
                    }
                }

                // No availability amongst all the provider.
                if (empty($available_hours))
                {
                    $unavailable_dates[] = $current_date->format('Y-m-d');
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

    /**
     * Search for any provider that can handle the requested service.
     *
     * This method will return the database ID of the providers affected to the requested service.
     *
     * @param int $service_id The requested service ID.
     *
     * @return array Returns the ID of the provider that can provide the requested service.
     */
    protected function search_providers_by_service($service_id)
    {
        $available_providers = $this->providers_model->get_available_providers();
        $provider_list = [];

        foreach ($available_providers as $provider)
        {
            foreach ($provider['services'] as $provider_service_id)
            {
                if ($provider_service_id === $service_id)
                {
                    // Check if the provider is affected to the selected service.
                    $provider_list[] = $provider['id'];
                }
            }
        }

        return $provider_list;
    }

    protected function search_providers_by_inmates($inmate_id, $service_id)
    {
        $available_providers = $this->inmates_model->get_providers_by_inmates($inmate_id, $service_id);
        $provider_list = [];

        foreach ($available_providers as $provider)
        {
            $provider_list[] = $provider['id'];
        }

        return $provider_list;
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
