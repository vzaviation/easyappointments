<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 *
 * Visitors added by Tarmac Technologies
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
 * Visitors Model
 *
 * @package Models
 */
class Visitors_model extends EA_Model {
    /**
     * Visitors_Model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('data_validation');
    }

    /**
     * Add a visitor record to the database.
     *
     * This method adds a visitor to the database. If the visitor doesn't exists it is going to be inserted, otherwise
     * the record is going to be updated.
     *
     * @param array $visitor Associative array with the visitor's data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the visitor id.
     * @throws Exception
     */
    public function add($visitor)
    {
        // Validate the visitor data before doing anything.
        $this->validate($visitor);

        // Check if a visitor already exists (by email).
        if ($this->exists($visitor) && ! isset($visitor['id']))
        {
            // Find the visitor id from the database.
            $visitor['id'] = $this->find_record_id($visitor);
        }

        // Insert or update the visitor record.
        if ( ! isset($visitor['id']))
        {
            $visitor['id'] = $this->insert($visitor);
        }
        else
        {
            $this->update($visitor);
        }

        return $visitor['id'];
    }

    /**
     * Validate visitor data before the insert or update operation is executed.
     *
     * @param array $visitor Contains the visitor data.
     *
     * @return bool Returns the validation result.
     *
     * @throws Exception If visitor validation fails.
     */
    public function validate($visitor)
    {
        // If a visitor id is provided, check whether the record exist in the database.
        if (isset($visitor['id']))
        {
            $num_rows = $this->db->get_where('visitors', ['id' => $visitor['id']])->num_rows();

            if ($num_rows === 0)
            {
                throw new Exception('Provided visitor id does not '
                    . 'exist in the database.');
            }
        }

        // Validate required fields
        if ( ! isset(
                $visitor['first_name'],
                $visitor['last_name'],
                $visitor['birthdate']
            ) )
        {
            throw new Exception('Not all required fields are provided: ' . print_r($visitor, TRUE));
        }

        // Validate email address
        if ( ! filter_var($visitor['email'], FILTER_VALIDATE_EMAIL))
        {
            throw new Exception('Invalid email address provided: ' . $visitor['email']);
        }

        // When inserting a record the email address must be unique.
        // TODO: 2023-03-24 - KPB - For now, remove this restriction and save all records
        $visitor_id = isset($visitor['id']) ? $visitor['id'] : '';

        $num_rows = $this->db
            ->select('*')
            ->from('visitors')
            ->where('email', $visitor['email'])
            ->where('id !=', $visitor_id)
            ->get()
            ->num_rows();

        if ($num_rows > 0)
        {
//            throw new Exception('Given email address belongs to another visitor record. '
//                . 'Please use a different email.');
        }

        return TRUE;
    }

    /**
     * Check if a particular visitor record already exists.
     *
     * This method checks whether the given visitor already exists in the database. It doesn't search with the id, but
     * with the following fields: "email"
     *
     * @param array $visitor Associative array with the visitor's data. Each key has the same name with the database
     * fields.
     *
     * @return bool Returns whether the record exists or not.
     *
     * @throws Exception If visitor email property is missing.
     */
    public function exists($visitor)
    {
        if (empty($visitor['email']))
        {
            throw new Exception('Visitor\'s email is not provided.');
        }

        // This method shouldn't depend on another method of this class.
        $num_rows = $this->db
            ->select('*')
            ->from('visitors')
            ->where('email', $visitor['email'])
            ->get()->num_rows();

        return $num_rows > 0;
    }

    /**
     * Find the database id of a visitor record.
     *
     * The visitor data should include the following fields in order to get the unique id from the database: "email"
     *
     * IMPORTANT: The record must already exists in the database, otherwise an exception is raised.
     *
     * @param array $visitor Array with the visitor data. The keys of the array should have the same names as the
     * database fields.
     *
     * @return int Returns the ID.
     *
     * @throws Exception If visitor record does not exist.
     */
    public function find_record_id($visitor)
    {
        if (empty($visitor['email']))
        {
            throw new Exception('Visitor\'s email was not provided: '
                . print_r($visitor, TRUE));
        }

        // Get visitor's id
        $result = $this->db
            ->select('id')
            ->from('visitors')
            ->where('email', $visitor['email'])
            ->get();

        if ($result->num_rows() == 0)
        {
            throw new Exception('Could not find visitor record id.');
        }

        return $result->row()->id;
    }

    /**
     * Insert a new visitor record to the database.
     *
     * @param array $visitor Associative array with the visitor's data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the id of the new record.
     *
     * @throws Exception If visitor record could not be inserted.
     */
    public function insert($visitor)
    {
        if ( ! $this->db->insert('visitors', $visitor))
        {
            throw new Exception('Could not insert visitor into the database.');
        }
        return (int)$this->db->insert_id();
    }

    /**
     * Update an existing visitor record in the database.
     *
     * The visitor data argument should already include the record ID in order to process the update operation.
     *
     * @param array $visitor Associative array with the visitor's data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the updated record ID.
     *
     * @throws Exception If visitor record could not be updated.
     */
    public function update($visitor)
    {
        $this->db->where('id', $visitor['id']);

        if ( ! $this->db->update('visitors', $visitor))
        {
            throw new Exception('Could not update visitor to the database.');
        }

        return (int)$visitor['id'];
    }

    /**
     * Delete an existing visitor record from the database.
     *
     * @param int $visitor_id The record id to be deleted.
     *
     * @return bool Returns the delete operation result.
     *
     * @throws Exception If $visitor_id argument is invalid.
     */
    public function delete($visitor_id)
    {
        if ( ! is_numeric($visitor_id))
        {
            throw new Exception('Invalid argument type $visitor_id: ' . $visitor_id);
        }

        $num_rows = $this->db->get_where('visitors', ['id' => $visitor_id])->num_rows();
        if ($num_rows == 0)
        {
            return FALSE;
        }

        return $this->db->delete('visitors', ['id' => $visitor_id]);
    }

    /**
     * Get a specific row from the appointments table.
     *
     * @param int $visitor_id The record's id to be returned.
     *
     * @return array Returns an associative array with the selected record's data. Each key has the same name as the
     * database field names.
     *
     * @throws Exception If $visitor_id argumnet is invalid.
     */
    public function get_row($visitor_id)
    {
        if ( ! is_numeric($visitor_id))
        {
            throw new Exception('Invalid argument provided as $visitor_id : ' . $visitor_id);
        }
        return $this->db->get_where('visitors', ['id' => $visitor_id])->row_array();
    }

    /**
     * Get a specific field value from the database.
     *
     * @param string $field_name The field name of the value to be returned.
     * @param int $visitor_id The selected record's id.
     *
     * @return string Returns the records value from the database.
     *
     * @throws Exception If $visitor_id argument is invalid.
     * @throws Exception If $field_name argument is invalid.
     * @throws Exception If requested visitor record does not exist in the database.
     * @throws Exception If requested field name does not exist in the database.
     */
    public function get_value($field_name, $visitor_id)
    {
        if ( ! is_numeric($visitor_id))
        {
            throw new Exception('Invalid argument provided as $visitor_id: '
                . $visitor_id);
        }

        if ( ! is_string($field_name))
        {
            throw new Exception('$field_name argument is not a string: '
                . $field_name);
        }

        if ($this->db->get_where('visitors', ['id' => $visitor_id])->num_rows() == 0)
        {
            throw new Exception('The record with the $visitor_id argument '
                . 'does not exist in the database: ' . $visitor_id);
        }

        $row_data = $this->db->get_where('visitors', ['id' => $visitor_id])->row_array();

        if ( ! array_key_exists($field_name, $row_data))
        {
            throw new Exception('The given $field_name argument does not exist in the database: '
                . $field_name);
        }

        $visitor = $this->db->get_where('visitors', ['id' => $visitor_id])->row_array();

        return $visitor[$field_name];
    }

    /** ********************************************************
     * appointment_visitor table actions - insert, update, fetch
     */
    public function insert_appointment_visitor($appointment_visitor)
    {
        if ((! isset($appointment_visitor['appointment_id'])) || ( ! is_numeric($appointment_visitor['appointment_id'])))
        {
            throw new Exception('Invalid argument type $appointment_id: ' . $appointment_visitor['appointment_id']);
        }

        if ((! isset($appointment_visitor['visitor_id'])) || ( ! is_numeric($appointment_visitor['visitor_id'])))
        {
            throw new Exception('Invalid argument type $visitor_id: ' . $appointment_visitor['visitor_id']);
        }

        if ((! isset($appointment_visitor['visitor_order'])) || ( ! is_numeric($appointment_visitor['visitor_order'])))
        {
            $appointment_visitor['visitor_order'] = 1;
        }

        $num_rows = $this->db->get_where('appointment_visitor',
            [
                'appointment_id' => $appointment_visitor['appointment_id'],
                'visitor_id' => $appointment_visitor['visitor_id']
            ]
            )->num_rows();
        if ($num_rows > 0) {
            // Record already exists
            return -1;
        } else {
            if ( ! $this->db->insert('appointment_visitor', $appointment_visitor))
            {
                throw new Exception('Could not insert appointment_visitor into the database.');
            }
    
            return (int)$this->db->insert_id();
    
        }
    }

    public function update_appointment_visitor($appointment_visitor)
    {
        if ((! isset($appointment_visitor['appointment_id'])) || ( ! is_numeric($appointment_visitor['appointment_id'])))
        {
            throw new Exception('Invalid argument type $appointment_id: ' . $appointment_visitor['appointment_id']);
        }

        if ((! isset($appointment_visitor['visitor_id'])) || ( ! is_numeric($appointment_visitor['visitor_id'])))
        {
            throw new Exception('Invalid argument type $visitor_id: ' . $appointment_visitor['visitor_id']);
        }

        // Fetch the id, if it exists
        $result = $this->db
            ->select('id')
            ->from('appointment_visitor')
            ->where('appointment_id', $appointment_visitor['appointment_id'])
            ->where('visitor_id', $appointment_visitor['visitor_id'])
            ->get();

        if ($result->num_rows() == 0)
        {
            // Record does not exist - ignore
        }
        else
        {
            $this->db->where('id', $result->row()->id);

            if ( ! $this->db->update('appointment_visitor', $appointment_visitor))
            {
                throw new Exception('Could not update appointment_visitor in the database.');
            }
        }
    }

    public function get_appointment_visitors($appointment_id)
    {
        if ( ! is_numeric($appointment_id) )
        {
            throw new Exception('Invalid argument type $appointment_id: ' . $appointment_id);
        }

        // Fetch the id, if it exists
        $result = $this->db
            ->select('*')
            ->from('appointment_visitor')
            ->where('appointment_id', $appointment_visitor['appointment_id'])
            ->get();

        if ($result->num_rows() == 0)
        {
            // Record does not exist - ignore
        }
        else
        {
            return $result->row_array();
        }
    }
}
