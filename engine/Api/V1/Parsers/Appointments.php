<?php

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2020, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

namespace EA\Engine\Api\V1\Parsers;

/**
 * Appointments Parser
 *
 * This class will handle the encoding and decoding from the API requests.
 *
 * @deprecated
 */
class Appointments implements ParsersInterface {
    /**
     * Encode Response Array
     *
     * @param array &$response The response to be encoded.
     */
    public function encode(array &$response)
    {
        $encoded_response = [
            'id' => array_key_exists('id', $response) ? (int)$response['id'] : NULL,
            'book' => $response['book_datetime'],
            'start' => $response['start_datetime'],
            'end' => $response['end_datetime'],
            'hash' => $response['hash'],
            'location' => $response['location'],
            'notes' => $response['notes'],
            'visitor_2_name' => $response['visitor_2_name'],
            'visitor_3_name' => $response['visitor_3_name'],
            'visitor_4_name' => $response['visitor_4_name'],
            'visitor_1_dl_number' => $response['visitor_1_dl_number'] !== NULL ? (int)$response['visitor_1_dl_number'] : NULL,
            'visitor_2_dl_number' => $response['visitor_2_dl_number'] !== NULL ? (int)$response['visitor_2_dl_number'] : NULL,
            'visitor_3_dl_number' => $response['visitor_3_dl_number'] !== NULL ? (int)$response['visitor_3_dl_number'] : NULL,
            'visitor_4_dl_number' => $response['visitor_4_dl_number'] !== NULL ? (int)$response['visitor_4_dl_number'] : NULL,
            'visitor_1_dl_state' => $response['visitor_1_dl_state'],
            'visitor_2_dl_state' => $response['visitor_2_dl_state'],
            'visitor_3_dl_state' => $response['visitor_3_dl_state'],
            'visitor_4_dl_state' => $response['visitor_4_dl_state'],
            'visitor_1_dl' => $response['visitor_1_dl'],
            'visitor_2_dl' => $response['visitor_2_dl'],
            'visitor_3_dl' => $response['visitor_3_dl'],
            'visitor_4_dl' => $response['visitor_4_dl'],
            'inmate_name' => $response['inmate_name'],
            'inmateId' => $response['id_inmate'] !== NULL ? (int)$response['id_inmate'] : NULL,
            'customerId' => $response['id_users_customer'] !== NULL ? (int)$response['id_users_customer'] : NULL,
            'providerId' => $response['id_users_provider'] !== NULL ? (int)$response['id_users_provider'] : NULL,
            'serviceId' => $response['id_services'] !== NULL ? (int)$response['id_services'] : NULL,
            'googleCalendarId' => $response['id_google_calendar'] !== NULL ? (int)$response['id_google_calendar'] : NULL,
            
        ];

        if (isset($response['provider']))
        {
            $provider_parser = new Providers();
            $provider_parser->encode($response['provider']);
            $encoded_response['provider'] = $response['provider'];
        }
        
        if (isset($response['inamte']))
        {
            $inmate_parser = new Inmates();
            $inmate_parser->encode($response['inmate']);
            $encoded_response['inmate'] = $response['inmate'];
        }

        if (isset($response['customer']))
        {
            $customer_parser = new Customers();
            $customer_parser->encode($response['customer']);
            $encoded_response['customer'] = $response['customer'];
        }

        if (isset($response['service']))
        {
            $service_parser = new Services();
            $service_parser->encode($response['service']);
            $encoded_response['service'] = $response['service'];
        }

        $response = $encoded_response;
    }

    /**
     * Decode Request
     *
     * @param array &$request The request to be decoded.
     * @param array $base Optional (null), if provided it will be used as a base array.
     */
    public function decode(array &$request, array $base = NULL)
    {
        $decoded_request = $base ?: [];

        if (array_key_exists('id', $request))
        {
            $decoded_request['id'] = $request['id'];
        }

        if (array_key_exists('book', $request))
        {
            $decoded_request['book_datetime'] = $request['book'];
        }

       /* if (array_key_exists('new_test_field', $request))
        {
            $decoded_request['new_test_field'] = $request['new_test_field'];
        }*/

        if (array_key_exists('visitor_2_name', $request))
        {
            $decoded_request['visitor_2_name'] = $request['visitor_2_name'];
        }
        
        if (array_key_exists('visitor_3_name', $request))
        {
            $decoded_request['visitor_3_name'] = $request['visitor_3_name'];
        }
        
        if (array_key_exists('visitor_4_name', $request))
        {
            $decoded_request['visitor_4_name'] = $request['visitor_4_name'];
        }
        
        if (array_key_exists('visitor_1_dl_number', $request))
        {
            $decoded_request['visitor_1_dl_number'] = $request['visitor_1_dl_number'];
        }
        
        if (array_key_exists('visitor_2_dl_number', $request))
        {
            $decoded_request['visitor_2_dl_number'] = $request['visitor_2_dl_number'];
        }
        
        if (array_key_exists('visitor_3_dl_number', $request))
        {
            $decoded_request['visitor_3_dl_number'] = $request['visitor_3_dl_number'];
        }
        
        if (array_key_exists('visitor_4_dl_number', $request))
        {
            $decoded_request['visitor_4_dl_number'] = $request['visitor_4_dl_number'];
        }
        
        if (array_key_exists('visitor_1_dl_state', $request))
        {
            $decoded_request['visitor_1_dl_state'] = $request['visitor_1_dl_state'];
        }
        
        if (array_key_exists('visitor_2_dl_state', $request))
        {
            $decoded_request['visitor_2_dl_state'] = $request['visitor_2_dl_state'];
        }
        
        if (array_key_exists('visitor_3_dl_state', $request))
        {
            $decoded_request['visitor_3_dl_state'] = $request['visitor_3_dl_state'];
        }
        
        if (array_key_exists('visitor_4_dl_state', $request))
        {
            $decoded_request['visitor_4_dl_state'] = $request['visitor_4_dl_state'];
        }
        
        if (array_key_exists('visitor_1_dl', $request))
        {
            $decoded_request['visitor_1_dl'] = $request['visitor_1_dl'];
        }
        
        if (array_key_exists('visitor_2_dl', $request))
        {
            $decoded_request['visitor_2_dl'] = $request['visitor_2_dl'];
        }
        
        if (array_key_exists('visitor_3_dl', $request))
        {
            $decoded_request['visitor_3_dl'] = $request['visitor_3_dl'];
        }
        
        if (array_key_exists('visitor_4_dl', $request))
        {
            $decoded_request['visitor_4_dl'] = $request['visitor_4_dl'];
        }
        
        if (array_key_exists('inmateId', $request))
        {
            $decoded_request['id_inmate'] = $request['inmateId'];
        }

        if (array_key_exists('inmate_name', $request))
        {
            $decoded_request['inmate_name'] = $request['inmate_name'];
        }

        if (array_key_exists('start', $request))
        {
            $decoded_request['start_datetime'] = $request['start'];
        }

        if (array_key_exists('end', $request))
        {
            $decoded_request['end_datetime'] = $request['end'];
        }

        if (array_key_exists('end', $request))
        {
            $decoded_request['visitor_2_name'] = $request['end'];
        }
        if (array_key_exists('hash', $request))
        {
            $decoded_request['hash'] = $request['hash'];
        }

        if (array_key_exists('location', $request))
        {
            $decoded_request['location'] = $request['location'];
        }

        if (array_key_exists('notes', $request))
        {
            $decoded_request['notes'] = $request['notes'];
        }

        if (array_key_exists('customerId', $request))
        {
            $decoded_request['id_users_customer'] = $request['customerId'];
        }

        if (array_key_exists('providerId', $request))
        {
            $decoded_request['id_users_provider'] = $request['providerId'];
        }

        if (array_key_exists('serviceId', $request))
        {
            $decoded_request['id_services'] = $request['serviceId'];
        }

        if (array_key_exists('googleCalendarId', $request))
        {
            $decoded_request['id_google_calendar'] = $request['googleCalendarId'];
        }

        $decoded_request['is_unavailable'] = FALSE;

        $request = $decoded_request;
    }
}
