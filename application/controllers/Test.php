<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Test controller
 *
 * ---------------------------------------------------------------------------- */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

#require './vendors/phpmailer/phpmailer/src/PHPMailer.php';
#require './vendors/phpmailer/phpmailer/src/Exception.php';
#require './vendors/phpmailer/phpmailer/src/SMTP.php';

/**
 * Test Controller
 *
 * @package Controllers
 */
class Test extends EA_Controller {
    /**
     * Class Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Test send email
     */
    public function test_send_email()
    {
        $to_email = "karlpbuchmann@gmail.com";
        //$to_email = "admin@visitationlink.com";
        $from_email = "noreply@visitationlink.com";


        try
        {
            $this->config->load('email');

            $mailer = $this->create_mailer();
            $mailer->SetFrom($from_email, 'Visitation Link');
            $mailer->AddAddress($to_email, 'Vistitation Link User');
            $mailer->Subject = "Test of Visitation Link Email Send through SES";
            $mailer->Body = "<html><head></head><body><h1>Hello World!</h1></body></html>";
    
            if ( ! $mailer->Send())
            {
                throw new RuntimeException('Email could not been sent. Mailer Error (Line ' . __LINE__ . '): '
                    . $mailer->ErrorInfo);
            }

            $response = [
                'heading' => "This is actually not an error!",
                'message' => "<h1>Mail Sent</h1>"
            ];

            $this->load->view('errors/html/error_general.php', $response);
        }
        catch (Exception $exception)
        {
            $this->output->set_status_header(500);

            $response = [
                'heading' => "This IS actually an error!",
                'message' => $exception->getMessage()
            ];
            $this->load->view('errors/html/error_general.php', $response);
        }
    }

    /**
     * Create PHP Mailer Instance
     *
     * @return PHPMailer
     */
    protected function create_mailer()
    {
        $mailer = new PHPMailer();
        
        $mailconfig['useragent'] = 'VisitationLink';
        $mailconfig['protocol'] = 'smtp'; // 'mail' or 'smtp'
        $mailconfig['mailtype'] = 'html'; // 'html' or 'text'
        $mailconfig['smtp_host'] = 'email-smtp.us-east-1.amazonaws.com';
        $mailconfig['smtp_user'] = 'AKIAYK5KWZXUKKSHT5F2';
        $mailconfig['smtp_pass'] = 'BPpFTkCb9hcTc8LAn4ylnEXcbrsVZbO3xuQPFtNWI5vx';
        $mailconfig['smtp_crypto'] = 'tls'; // 'ssl' or 'tls'
        $mailconfig['smtp_port'] = 587;  // for authenticated TLS

        if ($mailconfig['protocol'] === 'smtp')
        {
            $mailer->isSMTP();
            $mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $mailer->Host = $mailconfig['smtp_host'];
            $mailer->SMTPAuth = TRUE;
            $mailer->Username = $mailconfig['smtp_user'];
            $mailer->Password = $mailconfig['smtp_pass'];
            $mailer->SMTPSecure = $mailconfig['smtp_crypto'];
            $mailer->Port = $mailconfig['smtp_port'];
        }

        $mailer->IsHTML($mailconfig['mailtype'] === 'html');

        return $mailer;
    }
}
