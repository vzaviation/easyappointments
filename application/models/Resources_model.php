<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Resources_Model Class
 *
 * Contains the database operations for the service resources
 *
 * @package Models
 */
class Resources_model extends EA_Model {
    /**
     * CTOR
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('data_validation');
        $this->load->helper('general');
    }

    function get_all_resources()
    {
        $query = $this->db->get('resource');
        return $query->result_array();
    }

    function get_resource_by_id($id)
    {
        $this->db->where('resource_id', $id);
        $query = $this->db->get('resource');
        return $query->first_row();
    }

    // Checks for existing resource_id - if already in table, does update
    // If new, does insert
    function update_resource($record)
    {
        if (isset($record['resource_id'])) {
            $this->db->where('resource_id', $record['resource_id']);
            $query = $this->db->get('resource')->row();
        }

        if (@$query) {
            $this->db->where('resource_id', $record['resource_id']);
            $update = $this->db->update('resource', $record);
        } else {
            $insert = $this->db->insert('resource', $record);
        }

        return true;
    }

    function delete_resource_by_id($id = '') {
        $this->db->where('resource_id', $id);
        $delete = $this->db->delete('resource');
    }
}
