<?php
/**
 * This controller is is charge of user's registration process. 
 *
 * @access	public
 * @return	void
 */
class Register extends CI_Controller {
	
	function __construct()
    {
		parent::__construct();
		
		$this->load->library('session');
		// load custom errors class
		$this->load->library('Errors');
		// load recaptcha helper
		//$this->load->helper('recaptcha'); 
		// load the mailer
		$this->load->helper('phpmailer'); 
    }

	function index()
	{
		// load the RHEM view
		$this->load->view('register');
	}
	
	/**
	 * Confirm the user's registration. 
	 *
	 * @access	public
	 * @return	void
	 */
	function confirm()
	{
		$privatekey = "6Lc8m1EUAAAAAADz2plDcnwwcHfCTQazNFdMGG8r";
		//$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $this->input->post('recaptcha_challenge_field'), $this->input->post('recaptcha_response_field'));

		//get verify response data
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$privatekey.'&response='.$_POST['g-recaptcha-response']);
		$responseData = json_decode($verifyResponse);
		if (!$responseData->success)
		{
			// What happens when the CAPTCHA was entered incorrectly
			#$data["status"] = "Problem encountered";
			$data['message'] = $this->errors->print_message("The reCAPTCHA wasn't entered correctly. Go back and try it again.", "red_box");
		} 
		else {
			if(isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['username']) 
			   && isset($_POST['password']) && isset($_POST['email']) && isset($_POST['usage']))
			{
				// find out if the user's email exists in the database (user is already registered)
				$userresult = $this->Rhemmodel->find_user_by_email($_POST['email']);
				$count = sizeof($userresult);
				// user is already registered
				if($count > 0)
				{
					#$data["status"] = "Problem encountered";
					$data['message'] = $this->errors->print_message('The email specified is already registered.', 'red_box');
				}
				else
				{
					//$data["status"] = "Success";
					$data["message"] = "Registration was successful for " . $_POST['firstname'] . " " . $_POST['lastname'] . 
									   ". You will receive a confirmation email soon.<br/><br/>" .
									   "You can log in <a href='" . base_url() . "/login'>HERE</a>.";
					$data["fields"] = $_POST['username'] . "   " . $_POST['password'] . "  " . $_POST['email'] . "    ";
					
					$user['name'] = $_POST['firstname'];
					$user['last_name'] = $_POST['lastname'];
					$user['username'] = $_POST['username'];
					$user['password'] = $_POST['password'];
					$user['email'] = $_POST['email'];
					$user['usage_description'] = $_POST['usage'];
					
					$dbmessage = $this->Rhemmodel->insert_user($user);
					
					// use PHPMailer to send an email to the user comfirming a succesful registration
					$mail = new phpmailer();
					$mail->IsSMTP();
					$mail->Host     = "smtp-relay.gmail.com";
					$mail->From     = "gerardo@arstucson.net";
					/* New code */
					$mail->SMTPSecure = "ssl";
					$mail->Port = "465";
					/* End new code */
					$mail->FromName = "RHEM Web Tool Administrator";
					$mail->Subject  = "RHEM Web Tool: Your Registration Confirmation";
					$mail->Body     = "You have been registered successfully to use the RHEM Web Tool \n\nUsername: " . 
									  $_POST['username'] . " \nPassword: " . $_POST['password'] . " \n\n  You can log in at: " .
									  "http://apps.tucson.ars.ag.gov/rhem/login";
					$mail->AddAddress($_POST['email'],"");
					
					$mail->Send();
				}
			}
			else
			{
				$data['message'] = $this->errors->print_message('Please make sure that all fields have been entered.', 'red_box');
			}
		}
		
		$this->load->view('registerok', $data);
	}
}
?>