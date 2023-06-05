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
     * Get all inmates
     */
    public function get_all()
    {
        $result = $this->db
            ->select('*')
            ->from('inmates')
            ->order_by('inmates.inmate_name','ASC')
            ->get();

        return $result->result_array();
    }

    public function update($inmate)
    {
        $this->db->where('ID', $inmate['ID']);

        if ( ! $this->db->update('inmates', $inmate))
        {
            throw new Exception('Could not update inmate to the database.');
        }

        return (int)$inmate['ID'];
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
            ->select('ea_inmates.id, ea_inmates.inmate_name, ea_inmates.inmate_classification_level')
            ->from('ea_inmates')
            ->where('ea_inmates.booking_status= "1"');
            


        $inmates = $this->db->get()->result_array();

        // Return provider records.
        return $inmates;
    }

    public function get_providers_by_inmates($id, $service_id) {
        $this->db
            ->select('ea_inmates.id, ea_inmates.inmate_name, ea_inmates.inmate_classification_level')
            ->from('ea_inmates')
            ->where('ea_inmates.id='.$id);



        $inmates = $this->db->get()->result_array();

        $this->db
            ->select('ea_u.id')
            ->from('ea_users ea_u')
            ->join('ea_services_providers ea_sp', 'ea_u.id = ea_sp.id_users', 'left')
            ->where('ea_u.inmate_classification_level='.$inmates[0]['inmate_classification_level'])
            ->where('ea_u.id_roles=2')
            ->where('ea_sp.id_services='.$service_id);

        $providers = $this->db->get()->result_array();

        // Return provider records.
        return $providers;
    }

    public function get_inmate_appointments($inmate_id) {

        $this->db
            ->select('a.id,a.start_datetime')
            ->from('ea_appointments a')
            ->where('a.id_inmate='.$inmate_id);

        $appts = $this->db->get()->result_array();

        return $appts;
    }

    /**
     * Get all, or specific records from inmates table.
     *
     * Example:
     *
     * $this->inmates_model->get_batch([$id => $record_id]);
     *
     * @param mixed|null $where
     * @param int|null $limit
     * @param int|null $offset
     * @param mixed|null $order_by
     *
     * @return array Returns the rows from the database.
     */
    public function get_batch($where = NULL, $limit = NULL, $offset = NULL, $order_by = NULL)
    {
        if ($where !== NULL)
        {
            $this->db->where($where);
        }

        if ($order_by !== NULL)
        {
            $this->db->order_by($order_by);
        }

        return $this->db->get_where('inmates', ['inmate_name !=' => NULL], $limit, $offset)->result_array();
    }

    /**
     * Get a specific row from the inmates table.
     *
     * @param int $inmate_id The record's id to be returned.
     *
     * @return array Returns an associative array with the selected record's data. Each key has the same name as the
     * database field names.
     *
     * @throws Exception If $inmate_id argument is invalid.
     */
    public function get_row($inmate_id)
    {
        if ( ! is_numeric($inmate_id))
        {
            throw new Exception('Invalid argument provided as $inmate_id : ' . $inmate_id);
        }
        return $this->db->get_where('inmates', ['ID' => $inmate_id])->row_array();
    }
}
