<?php
class Contact extends CI_Controller {
	
	/* This defines a constructor for the current controller */
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
		// cache this page
		//$this->output->cache(60);
    }
	function index()
	{
		// load the RHEM view
		$this->load->view('contact');
	}
	
	/**
	 * Sends an email to the application
	 *
	 * @return void
	 */
	function send()
	{
		$privatekey = "6Lc8m1EUAAAAAADz2plDcnwwcHfCTQazNFdMGG8r";
		//$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $this->input->post('recaptcha_challenge_field'), $this->input->post('recaptcha_response_field'));

		//get verify response data
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$privatekey.'&response='.$_POST['g-recaptcha-response']);
		$responseData = json_decode($verifyResponse);
		if (!$responseData->success)
		{
			// What happens when the CAPTCHA was entered incorrectly
			$data['errormessage'] = $this->errors->print_message("The reCAPTCHA wasn't entered correctly. Go back and try it again.", "red_box");
		} else {
			// Your code here to handle a successful verification
			//die('Success!'); 
			$mail = new phpmailer();
			$mail->IsSMTP();
			$mail->Host     = "smtp-relay.gmail.com";
			$mail->From     = "gerardo@arstucson.net"; //$mail->AddReplyTo($_POST['email'], $_POST['firstname'] . " " . $_POST['lastname']);
			/* New code */
			$mail->SMTPSecure = "ssl";
			$mail->Port = "465";
			/* End new code */

			$mail->FromName = $_POST['firstname'] . " " . $_POST['lastname'] . "<" . $_POST['email'] . ">";
			$mail->Subject  =  "RHEM Web Tool: Contact Form Submission";
			
			$mail->Body     =  $_POST['comments'] . " from " . $_POST['email'];
			
			$requesttype  =  $_POST['requesttype'];
			
			if($requesttype == "website") 
				$mail->AddAddress('gerardo.armendariz@ars.usda.gov',"Gerardo Armendariz");
			else
				$mail->AddAddress('mariano.hernandez@ars.usda.gov',"Mariano Hernandez");
				
			if(!$mail->Send())
			   $data['errormessage'] = $this->errors->print_message("There was an error sending your comment.", "yellow_box");
			else
				$data['errormessage'] = $this->errors->print_message("Thanks for your feedback.  We'll get back to you soon.", "yellow_box");
		}
		
		$this->load->view('contact',$data);
	}
}
?>