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

    function get_full_resource_by_service_group_and_id($service_group_id, $resource_id)
    {
        $this->db->where('esg.service_group_id', $service_group_id);
        $this->db->where('er.resource_id', $resource_id);
        $this->db->select('esg.group_name,esg.service_id,es.name as "service_name",es.duration,er.resource_id,er.resource_name,er.resource_description,esg.timezone,esg.working_plan,esg.working_plan_exceptions');
        $this->db->from('ea_service_group esg');
        $this->db->join('ea_services es','es.id = esg.service_id','left');
        $this->db->join('ea_service_group_resource esgr','esgr.service_group_id = esg.service_group_id','left');
        $this->db->join('ea_resource er','er.resource_id = esgr.resource_id','left');
        $resource = $this->db->get()->first_row();
        return $resource;
    }

    function get_resources_by_service_id($service_id)
    {
        $this->db->where('esg.service_id', $service_id);
        $this->db->distinct();
        $this->db->select('esg.service_id,es.name as "service_name",es.duration,er.resource_id,er.resource_name,er.resource_description');
        $this->db->from('ea_service_group esg');
        $this->db->join('ea_services es','es.id = esg.service_id','left');
        $this->db->join('ea_service_group_resource esgr','esgr.service_group_id = esg.service_group_id','left');
        $this->db->join('ea_resource er','er.resource_id = esgr.resource_id','left');
        $resources = $this->db->get()->row_array();
        return $resources;
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
