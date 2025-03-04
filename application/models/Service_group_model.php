<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Service_group_model Class
 *
 * Contains the database operations for service_groups and associated tables and scheduling operations
 *
 * @package Models
 */
class Service_group_model extends EA_Model {
    /**
     * CTOR
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('data_validation');
        $this->load->helper('general');
    }

    /*
     * Cell Range
     */
    function get_all_service_groups()
    {
        $query = $this->db->get('service_group');
        return $query->result_array();
    }

    function get_service_group_by_id($id)
    {
        $this->db->where('service_group_id', $id);
        $query = $this->db->get('service_group');
        return $query->first_row();
    }

    // Checks for existing id - if already in table, does update
    // If new, does insert
    function update_service_group($record)
    {
        if (isset($record['service_group_id'])) {
            $this->db->where('service_group_id', $record['service_group_id']);
            $query = $this->db->get('service_group')->row();
        }

        if (@$query) {
            $this->db->where('service_group_id', $record['service_group_id']);
            $update = $this->db->update('service_group', $record);
        } else {
            $insert = $this->db->insert('service_group', $record);
        }

        return true;
    }

    function delete_service_group_by_id($id = '') {
        $this->db->where('service_group_id', $id);
        $delete = $this->db->delete('service_group');
    }

    public function get_service_group_batch($where = NULL, $limit = NULL, $offset = NULL, $order_by = NULL)
    {
        if ($where !== NULL)
        {
            $this->db->where($where);
        }

        if ($order_by !== NULL)
        {
            $this->db->order_by($order_by);
        }
        $this->db->select('sg.service_group_id,sg.group_name,sg.group_description,s.id as service_id,s.name as service_name');
        $this->db->from('service_group sg');
        $this->db->join('services s', 's.id = sg.service_id', 'inner');
        $query = $this->db->get($limit, $offset);
        $batch = $query->result_array();

        // Return service_group records in an array.
        return $batch;
    }

    function get_all_service_group_schedules()
    {
        $query = $this->db->get('service_group_schedule');
        return $query->result_array();
    }

    function get_service_group_schedule_by_id($id)
    {
        $this->db->where('service_group_schedule_id', $id);
        $query = $this->db->get('service_group_schedule');
        return $query->first_row();
    }

    function get_service_group_schedule_by_service_group_id($service_group_id)
    {
        $this->db->where('service_group_id', $service_group_id);
        $query = $this->db->get('service_group_schedule');
        return $query->first_row();
    }

    // Checks for existing id - if already in table, does update
    // If new, does insert
    function update_service_group_schedules($record)
    {
        if ((isset($record['service_group_schedule_id']))
           && ($record['service_group_schedule_id'] != "")) {
            $this->db->where('service_group_schedule_id', $record['service_group_schedule_id']);
            $query = $this->db->get('service_group_schedule')->row();
        }

        if (@$query) {
            $this->db->where('service_group_schedule_id', $record['service_group_schedule_id']);
            $update = $this->db->update('service_group_schedule', $record);
        } else {
            unset($record['service_group_schedule_id']);
            $insert = $this->db->insert('service_group_schedule', $record);
        }

        return true;
    }

    function delete_service_group_schedules_by_id($id = '') {
        $this->db->where('service_group_schedule_id', $id);
        $delete = $this->db->delete('service_group_schedule');
    }

    function get_service_group_resources_by_service_group_id($service_group_id)
    {
        $this->db->where('service_group_id', $service_group_id);
        $query = $this->db->get('service_group_resource');
        return $query->result_array();
    }

    // Checks for existing id - if already in table, does update
    // If new, does insert
    function update_service_group_resources($service_group_id, $resources)
    {
        if (isset($service_group_id)) {
            $this->db->where('service_group_id', $service_group_id);
            $existing = $this->db->get('service_group_resource')->result_array();
        }

        $record['service_group_id'] = $service_group_id;
        if (@$existing) {
            // There are existing records, manage appropriately
            $exResId = [];
            foreach($existing as $rec) {
                $exResId[] = $rec['resource_id'];
            }

            // Add any that are missing
            foreach($resources as $resId) {
                if (!in_array($resId, $exResId)) {
                    $record['resource_id'] = $resId;
                    $insert = $this->db->insert('service_group_resource', $record);
                }
            }

            // Remove any existing that are not in new list
            foreach($exResId as $exId) {
                if (!in_array($exId, $resources)) {
                    $this->delete_service_group_resources_by_resource_id($service_group_id, $exId);
                }
            }
        } else {
            // Insert them all
            foreach($resources as $resId) {
                $record['resource_id'] = $resId;
                $insert = $this->db->insert('service_group_resource', $record);
            }
        }

        return true;
    }

    function delete_service_group_resources_by_resource_id($service_group_id, $resource_id = '') {
        $this->db->where('service_group_id', $service_group_id);
        $this->db->where('resource_id', $resource_id);
        $delete = $this->db->delete('service_group_resource');
    }

    public function save_working_plan_exception($date, $working_plan_exception, $provider_id)
    {
        // Validate the working plan exception data.
        $start = date('H:i', strtotime($working_plan_exception['start']));
        $end = date('H:i', strtotime($working_plan_exception['end']));

        if ($start > $end)
        {
            throw new Exception('Working plan exception "start" must be prior to "end".');
        }

        // Make sure the provider record exists.
        $conditions = [
            'id' => $provider_id,
            'id_roles' => $this->db->get_where('roles', ['slug' => DB_SLUG_PROVIDER])->row()->id
        ];

        if ($this->db->get_where('users', $conditions)->num_rows() === 0)
        {
            throw new Exception('Provider record was not found in database: ' . $provider_id);
        }

        // Add record to database.
        $working_plan_exceptions = json_decode($this->get_setting('working_plan_exceptions', $provider_id), TRUE);

        if ( ! isset($working_plan_exception['breaks']))
        {
            $working_plan_exception['breaks'] = [];
        }

        $working_plan_exceptions[$date] = $working_plan_exception;

        return $this->set_setting(
            'working_plan_exceptions',
            json_encode($working_plan_exceptions),
            $provider_id
        );
    }

    public function delete_working_plan_exception($date, $provider_id)
    {
        $provider = $this->get_row($provider_id);

        $working_plan_exceptions = json_decode($provider['settings']['working_plan_exceptions'], TRUE);

        if ( ! isset($working_plan_exceptions[$date]))
        {
            return TRUE; // The selected date does not exist in provider's settings.
        }

        unset($working_plan_exceptions[$date]);

        return $this->set_setting(
            'working_plan_exceptions',
            json_encode(empty($working_plan_exceptions) ? new stdClass() : $working_plan_exceptions),
            $provider_id
        );
    }
}
