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
     * This method adds a visitor to the database.
     * 
     * At the moment, we can only key on visitor email - if there is a match, re-use and update that visitor record
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

        // If there is an id, just update the record
        if (isset($visitor['id'])) {
            $this->update($visitor);
        } else {
            // Check if a visitor already exists (by email).
            $visitorExistsId = $this->exists($visitor);
            if ($visitorExistsId !== -1) {
                $visitor['id'] = $visitorExistsId;
                $this->updateSave($visitor);  // Update, but save a record of any changes from past values
            } else {
                $visitor['id'] = $this->insert($visitor);
            }
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
                //throw new Exception('Provided visitor id does not '
                //    . 'exist in the database.');
                return FALSE;
            }
        }

        // Validate required fields
        if ( ! isset(
                $visitor['first_name'],
                $visitor['last_name'],
                $visitor['birthdate']
            ) )
        {
            //throw new Exception('Not all required fields are provided: ' . print_r($visitor, TRUE));
            return FALSE;
        }

        // Validate email address
        /*  IGNORE - only visitor 1 is required to give email
        if ( ! filter_var($visitor['email'], FILTER_VALIDATE_EMAIL))
        {
            //throw new Exception('Invalid email address provided: ' . $visitor['email']);
            return FALSE;
        }
        */

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
     * @return int Returns the id of the existing record, or -1 if none found
     *
     * @throws Exception If visitor email property is missing.
     */
    public function exists($visitor)
    {
        if (empty($visitor['email']))
        {
            //throw new Exception('Visitor\'s email is not provided.');
            //  just return -1
            return -1;
        }

        // This method shouldn't depend on another method of this class.
        $result = $this->db
            ->select('*')
            ->from('visitors')
            ->where('email', $visitor['email'])
            ->get();

        if ($result->num_rows() > 0) {
            return $result->row()->id;
        } else {
            return -1;
        }
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
     * Update an existing visitor record in the database, but save previous info
     *
     * If this is a returning visitor with the same email, they may have entered different info for name, phone, ID, etc
     *  update the record to their latest info, but save the rest
     *
     * @param array $visitor Associative array with the visitor's data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the updated record ID.
     *
     * @throws Exception If visitor record could not be updated.
     */
    public function updateSave($visitor)
    {
        /*   FUTURE EXPANSION
        $this->db->where('id', $visitor['id']);

        if ( ! $this->db->update('visitors', $visitor))
        {
            throw new Exception('Could not update visitor to the database.');
        }

        return (int)$visitor['id'];
        */
        return $this->update($visitor);
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
     * Get all visitors
     */
    public function get_all()
    {
        // Get visitor's id
        $result = $this->db
            ->select('*')
            ->from('visitors')
            ->order_by('visitors.last_name','ASC')
            ->get();

        return $result->result_array();
    }

    /**
     * Get a specific row from the visitors table.
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

    /**
     * Get all, or specific records from visitors table.
     *
     * Example:
     *
     * $this->visitors_model->get_batch([$id => $record_id]);
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

        return $this->db->get_where('visitors', ['last_name !=' => NULL], $limit, $offset)->result_array();
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

    public function get_appointment_visitor($appointment_id, $visitor_id)
    {
        if ((! isset($appointment_id)) || ( ! is_numeric($appointment_id)))
        {
            throw new Exception('Invalid argument type $appointment_id: ' . $appointment_id);
        }

        if ((! isset($visitor_id)) || ( ! is_numeric($visitor_id)))
        {
            throw new Exception('Invalid argument type $visitor_id: ' . $visitor_id);
        }

        $whereArray = array('appointment_id' => $appointment_id, 'visitor_id' => $visitor_id);
        $result = $this->db
            ->select('*')
            ->from('appointment_visitor')
            ->where($whereArray)
            ->get();

        if ($result->num_rows() == 0)
        {
            // Record does not exist
            throw new Exception('No record found for $appointment_id / $visitor_id: ' . $appointment__id . " / " . $visitor_id);
        }
        else
        {
            return $result->row_array();
        }
    }

    public function get_appointment_visitors($appointment_id)
    {
        if ( ! is_numeric($appointment_id) )
        {
            throw new Exception('Invalid argument type $appointment_id: ' . $appointment_id);
        }

        $result = $this->db
            ->select('visitors.*, appointment_visitor.*')
            ->from('appointment_visitor')
            ->join('visitors', 'visitors.id = appointment_visitor.visitor_id')
            ->where('appointment_visitor.appointment_id', $appointment_id)
            ->order_by('appointment_visitor.visitor_order','ASC')
            ->get();

        if ($result->num_rows() == 0)
        {
            // Record does not exist - ignore
        }
        else
        {
            return $result->result_array();
        }
    }

    public function get_appointments_visitor($visitor_id)
    {
        if ( ! is_numeric($visitor_id) )
        {
            throw new Exception('Invalid argument type $visitor_id: ' . $visitor_id);
        }

        $result = $this->db
            ->select('appointments.*, appointment_visitor.*, services.name as service_name')
            ->from('appointment_visitor')
            ->join('appointments', 'appointments.id = appointment_visitor.appointment_id')
            ->join('services', 'services.id = appointments.id_services')
            ->where('appointment_visitor.visitor_id', $visitor_id)
            ->order_by('appointments.start_datetime','DESC')
            ->get();

        if ($result->num_rows() == 0)
        {
            // Record does not exist - ignore
        }
        else
        {
            return $result->result_array();
        }
    }
}
