<?php defined('BASEPATH') or exit('No direct script access allowed');

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

/**
 * Inmates_Model Class
 *
 * Contains the database operations for the service provider users of Easy!Appointments.
 *
 * @package Models
 */
class Inmates_model extends EA_Model {
    /**
     * Inmates_Model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('data_validation');
        $this->load->helper('general');
    }
  

    /**
     * Get the available inmates.
     *
     * This method returns the available inmates.
     *
     * @return array Returns an array with the inmates data.
     */
    public function get_available_inmates()
    {
        // Get provider records from database.
        $this->db
            ->select('ea_inmates.id, ea_inmates.inmate_name')
            ->from('ea_inmates');

        $inmates = $this->db->get()->result_array();

        // Return provider records.
        return $inmates;
    }
   
}
