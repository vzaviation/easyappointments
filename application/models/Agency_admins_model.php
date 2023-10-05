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
 * Agency Admins Model
 *
 * Handles the db actions that have to do with Agency Admins.
 *
 * @package Models
 */
class Agency_admins_model extends EA_Model {
    /**
     * Agency Admins_Model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('general');
        $this->load->helper('data_validation');
    }

    /**
     * Add (insert or update) an aadmin user record into database.
     *
     * @param array $aadmin Contains the aadmin user data.
     *
     * @return int Returns the record id.
     *
     * @throws Exception When the aadmin data are invalid (see validate() method).
     */
    public function add($aadmin)
    {
        $this->validate($aadmin);

        if ($this->exists($aadmin) && ! isset($aadmin['id']))
        {
            $aadmin['id'] = $this->find_record_id($aadmin);
        }

        if ( ! isset($aadmin['id']))
        {
            $aadmin['id'] = $this->insert($aadmin);
        }
        else
        {
            $aadmin['id'] = $this->update($aadmin);
        }

        return (int)$aadmin['id'];
    }

    /**
     * Validate aadmin user data before add() operation is executed.
     *
     * @param array $aadmin Contains the aadmin user data.
     *
     * @return bool Returns the validation result.
     *
     * @throws Exception If aadmin validation fails.
     */
    public function validate($aadmin)
    {
        // If a record id is provided then check whether the record exists in the database.
        if (isset($aadmin['id']))
        {
            $num_rows = $this->db->get_where('users', ['id' => $aadmin['id']])->num_rows();

            if ($num_rows === 0)
            {
                throw new Exception('Given Agency Admin id does not exist in database: ' . $aadmin['id']);
            }
        }

        // Validate required fields integrity.
        if ( ! isset(
            $aadmin['last_name'],
            $aadmin['email'],
            $aadmin['phone_number']
        ))
        {
            throw new Exception('Not all required fields are provided: ' . print_r($aadmin, TRUE));
        }

        // Validate aadmin email address.
        if ( ! filter_var($aadmin['email'], FILTER_VALIDATE_EMAIL))
        {
            throw new Exception('Invalid email address provided: ' . $aadmin['email']);
        }

        // Check if username exists.
        if (isset($aadmin['settings']['username']))
        {
            $user_id = (isset($aadmin['id'])) ? $aadmin['id'] : '';
            if ( ! $this->validate_username($aadmin['settings']['username'], $user_id))
            {
                throw new Exception ('Username already exists. Please select a different '
                    . 'username for this record.');
            }
        }

        // Validate aadmin password.
        if (isset($aadmin['settings']['password']))
        {
            if (strlen($aadmin['settings']['password']) < MIN_PASSWORD_LENGTH)
            {
                throw new Exception('The user password must be at least '
                    . MIN_PASSWORD_LENGTH . ' characters long.');
            }
        }

        if ( ! isset($aadmin['id']) && ! isset($aadmin['settings']['password']))
        {
            throw new Exception('The user password cannot be empty for new users.');
        }

        // Validate calendar view mode.
        if (isset($aadmin['settings']['calendar_view']) && ($aadmin['settings']['calendar_view'] !== CALENDAR_VIEW_DEFAULT
                && $aadmin['settings']['calendar_view'] !== CALENDAR_VIEW_TABLE))
        {
            throw new Exception('The calendar view setting must be either "' . CALENDAR_VIEW_DEFAULT
                . '" or "' . CALENDAR_VIEW_TABLE . '", given: ' . $aadmin['settings']['calendar_view']);
        }

        // When inserting a record the email address must be unique.
        $aadmin_id = (isset($aadmin['id'])) ? $aadmin['id'] : '';

        $num_rows = $this->db
            ->select('*')
            ->from('users')
            ->join('roles', 'roles.id = users.id_roles', 'inner')
            ->where('roles.slug', DB_SLUG_AGENCY_ADMIN)
            ->where('users.email', $aadmin['email'])
            ->where('users.id !=', $aadmin_id)
            ->get()
            ->num_rows();

        if ($num_rows > 0)
        {
            throw new Exception('Given email address belongs to another Agency Admin record. '
                . 'Please use a different email.');
        }

        return TRUE;
    }

    /**
     * Validate Records Username
     *
     * @param string $username The provider records username.
     * @param int $user_id The user record id.
     *
     * @return bool Returns the validation result.
     */
    public function validate_username($username, $user_id)
    {
        if ( ! empty($user_id))
        {
            $this->db->where('id_users !=', $user_id);
        }

        $this->db->where('username', $username);

        return $this->db->get('user_settings')->num_rows() === 0;
    }

    /**
     * Check whether a particular aadmin record exists in the database.
     *
     * @param array $aadmin Contains the aadmin data. The 'email' value is required to be present at the moment.
     *
     * @return bool Returns whether the record exists or not.
     *
     * @throws Exception When the 'email' value is not present on the $aadmin argument.
     */
    public function exists($aadmin)
    {
        if ( ! isset($aadmin['email']))
        {
            throw new Exception('Agency Admin email is not provided: ' . print_r($aadmin, TRUE));
        }

        // This method shouldn't depend on another method of this class.
        $num_rows = $this->db
            ->select('*')
            ->from('users')
            ->join('roles', 'roles.id = users.id_roles', 'inner')
            ->where('users.email', $aadmin['email'])
            ->where('roles.slug', DB_SLUG_AGENCY_ADMIN)
            ->get()->num_rows();

        return $num_rows > 0;
    }

    /**
     * Find the database record id of a aadmin.
     *
     * @param array $aadmin Contains the aadmin data. The 'email' value is required in order to find the record id.
     *
     * @return int Returns the record id
     *
     * @throws Exception When the 'email' value is not present on the $aadmin array.
     */
    public function find_record_id($aadmin)
    {
        if ( ! isset($aadmin['email']))
        {
            throw new Exception('Agency Admin email was not provided: ' . print_r($aadmin, TRUE));
        }

        $result = $this->db
            ->select('users.id')
            ->from('users')
            ->join('roles', 'roles.id = users.id_roles', 'inner')
            ->where('users.email', $aadmin['email'])
            ->where('roles.slug', DB_SLUG_AGENCY_ADMIN)
            ->get();

        if ($result->num_rows() == 0)
        {
            throw new Exception('Could not find Agency Admin record id.');
        }

        return (int)$result->row()->id;
    }

    /**
     * Insert a new aadmin record into the database.
     *
     * @param array $aadmin Contains the aadmin data.
     *
     * @return int Returns the new record id.
     *
     * @throws Exception When the insert operation fails.
     */
    protected function insert($aadmin)
    {
        $settings = $aadmin['settings'];
        unset($aadmin['settings']);

        $aadmin['id_roles'] = $this->get_aadmin_role_id();

        if ( ! $this->db->insert('users', $aadmin))
        {
            throw new Exception('Could not insert Agency Admin into the database.');
        }

        $aadmin['id'] = (int)$this->db->insert_id();
        $settings['salt'] = generate_salt();
        $settings['password'] = hash_password($settings['salt'], $settings['password']);

        $this->save_settings($settings, $aadmin['id']);

        return $aadmin['id'];
    }

    /**
     * Get the aadmin users role id.
     *
     * @return int Returns the role record id.
     */
    public function get_aadmin_role_id()
    {
        return (int)$this->db->get_where('roles', ['slug' => DB_SLUG_AGENCY_ADMIN])->row()->id;
    }

    /**
     * Save the aadmin settings (used from insert or update operation).
     *
     * @param array $settings Contains the setting values.
     * @param int $aadmin_id Record id of the aadmin.
     *
     * @throws Exception If $aadmin_id argument is invalid.
     * @throws Exception If $settings argument is invalid.
     */
    protected function save_settings($settings, $aadmin_id)
    {
        if ( ! is_numeric($aadmin_id))
        {
            throw new Exception('Invalid $aadmin_id argument given:' . $aadmin_id);
        }

        if (count($settings) == 0 || ! is_array($settings))
        {
            throw new Exception('Invalid $settings argument given:' . print_r($settings, TRUE));
        }

        // Check if the setting record exists in db.
        $num_rows = $this->db->get_where('user_settings',
            ['id_users' => $aadmin_id])->num_rows();
        if ($num_rows == 0)
        {
            $this->db->insert('user_settings', ['id_users' => $aadmin_id]);
        }

        foreach ($settings as $name => $value)
        {
            $this->set_setting($name, $value, $aadmin_id);
        }
    }

    /**
     * Set a provider's setting value in the database.
     *
     * The provider and settings record must already exist.
     *
     * @param string $setting_name The setting's name.
     * @param string $value The setting's value.
     * @param int $aadmin_id The selected provider id.
     *
     * @return bool
     */
    public function set_setting($setting_name, $value, $aadmin_id)
    {
        $this->db->where(['id_users' => $aadmin_id]);
        return $this->db->update('user_settings', [$setting_name => $value]);
    }

    /**
     * Update an existing aadmin record in the database.
     *
     * @param array $aadmin Contains the aadmin record data.
     *
     * @return int Returns the record id.
     *
     * @throws Exception When the update operation fails.
     */
    protected function update($aadmin)
    {
        $settings = $aadmin['settings'];
        unset($aadmin['settings']);

        if (isset($settings['password']))
        {
            $salt = $this->db->get_where('user_settings', ['id_users' => $aadmin['id']])->row()->salt;
            $settings['password'] = hash_password($salt, $settings['password']);
        }

        $this->db->where('id', $aadmin['id']);
        if ( ! $this->db->update('users', $aadmin))
        {
            throw new Exception('Could not update aadmin record.');
        }

        $this->save_settings($settings, $aadmin['id']);

        return (int)$aadmin['id'];
    }

    /**
     * Delete an existing aadmin record from the database.
     *
     * @param int $aadmin_id The aadmin record id to be deleted.
     *
     * @return bool Returns the delete operation result.
     *
     * @throws Exception When the $aadmin_id is not a valid int value.
     */
    public function delete($aadmin_id)
    {
        if ( ! is_numeric($aadmin_id))
        {
            throw new Exception('Invalid argument type $aadmin_id: ' . $aadmin_id);
        }

        $num_rows = $this->db->get_where('users', ['id' => $aadmin_id])->num_rows();
        if ($num_rows == 0)
        {
            return FALSE; // Record does not exist in database.
        }

        return $this->db->delete('users', ['id' => $aadmin_id]);
    }

    /**
     * Get a specific aadmin record from the database.
     *
     * @param int $aadmin_id The id of the record to be returned.
     *
     * @return array Returns an array with the aadmin user data.
     *
     * @throws Exception When the $aadmin_id is not a valid int value.
     * @throws Exception When given record id does not exist in the database.
     */
    public function get_row($aadmin_id)
    {
        if ( ! is_numeric($aadmin_id))
        {
            throw new Exception('$aadmin_id argument is not a valid numeric value: ' . $aadmin_id);
        }

        // Check if record exists
        if ($this->db->get_where('users', ['id' => $aadmin_id])->num_rows() == 0)
        {
            throw new Exception('The given aadmin id does not match a record in the database.');
        }

        $aadmin = $this->db->get_where('users', ['id' => $aadmin_id])->row_array();

        $aadmin['settings'] = $this->db->get_where('user_settings',
            ['id_users' => $aadmin['id']])->row_array();
        unset($aadmin['settings']['id_users'], $aadmin['settings']['salt']);

        return $aadmin;
    }

    /**
     * Get a specific field value from the database.
     *
     * @param string $field_name The field name of the value to be returned.
     * @param int $aadmin_id Record id of the value to be returned.
     *
     * @return string Returns the selected record value from the database.
     *
     * @throws Exception When the $field_name argument is not a valid string.
     * @throws Exception When the $aadmin_id is not a valid int.
     * @throws Exception When the aadmin record does not exist in the database.
     * @throws Exception When the selected field value is not present on database.
     */
    public function get_value($field_name, $aadmin_id)
    {
        if ( ! is_string($field_name))
        {
            throw new Exception('$field_name argument is not a string: ' . $field_name);
        }

        if ( ! is_numeric($aadmin_id))
        {
            throw new Exception('$aadmin_id argument is not a valid numeric value: ' . $aadmin_id);
        }

        // Check whether the aadmin record exists.
        $result = $this->db->get_where('users', ['id' => $aadmin_id]);

        if ($result->num_rows() === 0)
        {
            throw new Exception('The record with the given id does not exist in the '
                . 'database: ' . $aadmin_id);
        }

        // Check if the required field name exist in database.
        $row_data = $result->row_array();

        if ( ! array_key_exists($field_name, $row_data))
        {
            throw new Exception('The given $field_name argument does not exist in the database: '
                . $field_name);
        }

        return $row_data[$field_name];
    }

    /**
     * Get all, or specific aadmin records from database.
     *
     * @param mixed|null $where (OPTIONAL) The WHERE clause of the query to be executed.
     * @param int|null $limit
     * @param int|null $offset
     * @param mixed|null $order_by
     *
     * @return array Returns an array with aadmin records.
     */
    public function get_batch($where = NULL, $limit = NULL, $offset = NULL, $order_by = NULL)
    {
        $role_id = $this->get_aadmin_role_id();

        if ($where !== NULL)
        {
            $this->db->where($where);
        }

        if ($order_by !== NULL)
        {
            $this->db->order_by($order_by);
        }

        $batch = $this->db->get_where('users', ['id_roles' => $role_id], $limit, $offset)->result_array();

        // Include every aadmin providers.
        foreach ($batch as &$aadmin)
        {
            $aadmin['settings'] = $this->db->get_where('user_settings',
                ['id_users' => $aadmin['id']])->row_array();
            unset($aadmin['settings']['id_users']);
        }

        return $batch;
    }

    /**
     * Get a providers setting from the database.
     *
     * @param string $setting_name The setting name that is going to be returned.
     * @param int $aadmin_id The selected provider id.
     *
     * @return string Returns the value of the selected user setting.
     */
    public function get_setting($setting_name, $aadmin_id)
    {
        $provider_settings = $this->db->get_where('user_settings',
            ['id_users' => $aadmin_id])->row_array();
        return $provider_settings[$setting_name];
    }
}
