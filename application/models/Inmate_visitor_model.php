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
 * Inmate_Visitor_model Class
 *
 * Manage approved inmate vistors and related data
 *
 * @package Models
 */
class Inmate_visitor_model extends EA_Model {
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('data_validation');
        $this->load->helper('general');
    }

    /**
     * inmate visitor functions
     */
    public function get_inmate_visitors($inmate_id) {

        $this->db
            ->select('iv.id,iv.visitor_first_name,iv.visitor_last_name,iv.visitor_number,iv.visitor_relationship_id,i.approved_visitor_list_effective_date as "effective_date",i.approved_visitor_list_obsolete_date as "obsolete_date"')
            ->from('ea_inmates i')
            ->join('ea_inmate_visitor iv','iv.inmate_id = i.ID')
            ->where('i.ID='.$inmate_id)
            ->order_by('iv.visitor_number ASC');

        $visitors = $this->db->get()->result_array();

        return $visitors;
    }
    
    public function add_inmate_visitor($visitor)
    {
        // Validate the visitor data before doing anything.
        if ($this->validate_inmate_visitor($visitor)) {
            // If there is an id, just update the record
            if (isset($visitor['id'])) {
                return $this->update_inmate_visitor($visitor);
            } else {
                return $this->insert_inmate_visitor($visitor);
            }
        }
    }

    public function validate_inmate_visitor($visitor)
    {
        if ( (!isset($visitor['inmate_id'])) ||
             (!isset($visitor['visitor_first_name'])) ||
             (!isset($visitor['visitor_last_name'])) ||
             (!isset($visitor['visitor_number'])) )
        {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function insert_inmate_visitor($visitor)
    {
        // First add the effective_date to the inmate record
        $inmateId = $visitor["inmate_id"];
        $effective_date = $visitor["effective_date"];
        $obsolete_date = $visitor["obsolete_date"];
        $this->db->set('approved_visitor_list_effective_date', $effective_date);
        $this->db->set('approved_visitor_list_obsolete_date', $obsolete_date);
        $this->db->where('ID', $inmateId);
        $this->db->update('ea_inmates');

        // Clear out extraneous values
        unset($visitor["effective_date"]);
        unset($visitor["obsolete_date"]);
        if ( ! $this->db->insert('ea_inmate_visitor', $visitor))
        {
            throw new Exception('Could not insert visitor into the database.');
        }
        return (int)$this->db->insert_id();
    }

    public function update_inmate_visitor($visitor)
    {
        // First add the effective_date to the inmate record
        $inmateId = $visitor["inmate_id"];
        $effective_date = $visitor["effective_date"];
        $obsolete_date = $visitor["obsolete_date"];
        $this->db->set('approved_visitor_list_effective_date', $effective_date);
        $this->db->set('approved_visitor_list_obsolete_date', $obsolete_date);
        $this->db->where('ID', $inmateId);
        $this->db->update('ea_inmates');

        // Clear out extraneous values
        unset($visitor["effective_date"]);
        unset($visitor["obsolete_date"]);
        $this->db->where('id', $visitor['id']);
        if ( ! $this->db->update('ea_inmate_visitor', $visitor))
        {
            throw new Exception('Could not update inmate visitor in the database.');
        }

        return (int)$visitor['id'];
    }

    public function get_all_relationships() {

        $this->db
            ->select('*')
            ->from('ea_inmate_visitor_relationship ivr')
            ->order_by('ivr.visitor_relationship ASC');

        $visitor_rel = $this->db->get()->result_array();

        return $visitor_rel;
    }
}
