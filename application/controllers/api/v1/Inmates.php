<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2020, Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

require_once __DIR__ . '/API_V1_Controller.php';

use EA\Engine\Api\V1\Request;
use EA\Engine\Api\V1\Response;
use EA\Engine\Types\NonEmptyText;

/**
 * Inmates Controller
 *
 * @package Controllers
 */
class Inmates extends API_V1_Controller {
    /**
     * Inmates Resource Parser
     *
     * @var \EA\Engine\Api\V1\Parsers\Inmates
     */
    protected $parser;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('inmates_model');
        $this->parser = new \EA\Engine\Api\V1\Parsers\Inmates;
    }

    /**
     * GET API Method
     *
     * @param int $id Optional (null), the record ID to be returned.
     */
    public function get($id = NULL)
    {
        try
        {

            $inmates = $this->inmates_model->get_available_inmates();

            if ($id !== NULL && count($inmates) === 0)
            {
                $this->throw_record_not_found();
            }

            $response = new Response($inmates);

            $response->encode($this->parser)
                ->search()
                ->sort()
                ->paginate()
                ->minimize()
                ->singleEntry($id)
                ->output();

        }
        catch (Exception $exception)
        {
            $this->handle_exception($exception);
        }
    }
    
    /**
     * POST API Method
     */
    public function post()
    {}
    public function put($id)
    {}
    public function delete($id) {
    }

 
}
