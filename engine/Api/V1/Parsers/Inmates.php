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
 * Providers Parser
 *
 * This class will handle the encoding and decoding from the API requests.
 *
 * @deprecated
 */ 
class Inmates implements ParsersInterface {
    /**
     * Encode Response Array
     *
     * @param array &$response The response to be encoded.
     */
    public function encode(array &$response)
    {
        $encoded_response = [
            'id' => array_key_exists('id', $response) ? (int)$response['id'] : NULL,
          //  'booking' => $response['Booking'],
        //    'so' => $response['SO'],
            'inmate_name' => $response['inmate_name']//,
         //   'dob' => $response['DOB'],
         //   'dl' => $response['DL'],
            //'booking_date' => $response['phone_number'],
           // 'release_date' => $response['address']
        ];

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

        if (array_key_exists('inmate_name', $request))
        {
            $decoded_request['inmate_name'] = $request['inmate_name'];
        }

      /*  if (array_key_exists('so', $request))
        {
            $decoded_request['so'] = $request['so'];
        }

        if (array_key_exists('dob', $request))
        {
            $decoded_request['dob'] = $request['dob'];
        }

        if (array_key_exists('dl', $request))
        {
            $decoded_request['dl'] = $request['dl'];
        }

        if (array_key_exists('booking_date', $request))
        {
            $decoded_request['booking_date'] = $request['booking_date'];
        }

        if (array_key_exists('release_date', $request))
        {
            $decoded_request['release_date'] = $request['release_date'];
        } */

        $request = $decoded_request;
    }
}
