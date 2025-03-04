<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Cells_Model Class
 *
 * Contains the database operations for cells / cell_lookup
 *
 * @package Models
 */
class Cells_model extends EA_Model {
    /**
     * CTOR
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('data_validation');
        $this->load->helper('general');
    }

    function get_all_cell_lookups()
    {
        $this->db->order_by('cell_range ASC, cell ASC');
        $query = $this->db->get('cell_lookup');
        return $query->result_array();
    }

    function get_cell_lookup_by_id($id)
    {
        $this->db->where('cell_lookup_id', $id);
        $query = $this->db->get('cell_lookup');
        return $query->first_row();
    }

    // Checks for existing id - if already in table, does update
    // If new, does insert
    function update_cell_lookup($record)
    {
        if (isset($record['cell_lookup_id'])) {
            $this->db->where('cell_lookup_id', $record['cell_lookup_id']);
            $query = $this->db->get('cell_lookup')->row();
        }

        if (@$query) {
            $this->db->where('cell_lookup_id', $record['cell_lookup_id']);
            $update = $this->db->update('cell_lookup', $record);
        } else {
            $insert = $this->db->insert('cell_lookup', $record);
        }

        return true;
    }

    function delete_cell_lookup_by_id($id = '') {
        $this->db->where('cell_lookup_id', $id);
        $delete = $this->db->delete('cell_lookup');
    }

}
