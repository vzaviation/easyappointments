<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 *
 * Messages added by Tarmac Technologies
 * 
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
 * Messages Model
 *
 * @package Models
 */
class Messages_model extends EA_Model {
    /**
     * Messages_Model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('data_validation');
    }
    
    /**
     * Insert a new messages record to the database.
     *
     * @param array $messages Associative array with the message data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the id of the new record.
     *
     * @throws Exception If messages record could not be inserted.
     *
    public function insert($messages)
    {
        if ( ! $this->db->insert('messages', $messages))
        {
            throw new Exception('Could not insert message into the database.');
        }
        return (int)$this->db->insert_id();
    }
     */

    /**
     * Update an existing messages record in the database.
     *
     * The messages data argument should already include the record ID in order to process the update operation.
     *
     * @param array $messages Associative array with the message data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the updated record ID.
     *
     * @throws Exception If messages record could not be updated.
     *
    public function update($messages)
    {
        $this->db->where('id', $messages['id']);

        if ( ! $this->db->update('messages', $messages))
        {
            throw new Exception('Could not update message to the database.');
        }

        return (int)$messages['id'];
    }
     */

    /**
     * Delete an existing messages record from the database.
     *
     * @param int $message_id The record id to be deleted.
     *
     * @return bool Returns the delete operation result.
     *
     * @throws Exception If $messages_id argument is invalid.
     *
    public function delete($message_id)
    {
        if ( ! is_numeric($message_id))
        {
            throw new Exception('Invalid argument type $messages_id: ' . $message_id);
        }

        $num_rows = $this->db->get_where('messages', ['id' => $message_id])->num_rows();
        if ($num_rows == 0)
        {
            return FALSE;
        }

        return $this->db->delete('messages', ['id' => $message_id]);
    }
    */

    /**
     * Get all messages
     */
    public function get_all()
    {
        $result = $this->db
            ->select('*')
            ->from('inmate_data_messages')
            ->order_by('update_datetime','DESC')
            ->get();

        return $result->result_array();
    }

    /**
     * Get all messages from last X days only
     */
    public function get_all_last_days($days)
    {
        $result = $this->db
            ->select('*')
            ->from('inmate_data_messages')
            ->where('update_datetime >= (NOW() - INTERVAL ' . $days . ' DAY)')
            ->order_by('update_datetime','DESC')
            ->get();

        return $result->result_array();
    }

    /**
     * Given a message ID, retrieve the inmate SO and see if there are upcoming appointments
     */
    public function check_upcoming_appointment_by_message_id($messageId)
    {
        $result = $this->db
            ->select('ea.id,ea.start_datetime')
            ->from('ea_appointments ea')
            ->join('ea_inmates ei', 'ei.ID = ea.id_inmate','inner')
            ->join('ea_inmate_data_messages eidm','eidm.inmate_so_num = ei.SO','left')
            ->where("ea.start_datetime > CONVERT_TZ(NOW(),'SYSTEM','America/Chicago')")
            ->where('eidm.id = ' . $messageId)
            ->get();

        return $result->result_array();
    }
}
