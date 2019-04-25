<?php
/**
 * Logs in and out user. Also, in charge of password recovery.
 *
 * @access	public
 * @return	void
 */
class Login extends CI_Controller {
	
	/* This defines a constructor for the current controller */
	function __construct()
    {
		parent::__construct();
		
		$this->load->library('session');
		// load custom errors class
		$this->load->library('Errors');
		// load the mailer
		$this->load->helper('phpmailer'); 
		// cache this page
		//$this->output->cache(60);
		$this->load->helper('security');
    }
	function index()
	{
		// load the RHEM view
		$this->load->view('login');
	}
	
	/**
	 * Log in the user based on the provided username and password and redirect the user to the front page.
	 *
	 * @access	public
	 * @return	void
	 */
	function in()
	{
		$this->session->sess_create();
		
		if(isset($_POST['username']) && isset($_POST['password']))
		{
			$user['username'] = $_POST['username'];
			$user['password'] = $_POST['password'];
			
			$userresult = $this->Rhemmodel->find_user($user);
			$count = sizeof($userresult);
			if($count > 0)
			{	
				$usersession = array(
                   'is_logged_in'  => true,
				   'user_id'     => $userresult[0]->user_id,
                   'user_first_name'     => $userresult[0]->name,
                   'user_last_name' => $userresult[0]->last_name
              	);
				$this->session->set_userdata($usersession);
				
				redirect('tool');
			}
			else
			{
				$data['errormessage'] = $this->errors->print_message('Username or password is incorrect.', 'red_box');
				
				$this->load->view('login', $data);
			}
		}
		else
		{
			$data["status"] = "Problem encountered";
			$data["message"] = "Please make sure that all fields have been entered.";
		}
	}

	/**
	 * Log out the user based on the provided username and password. Destroy the session data 
	 * and redirect the user to the front page.
	 *
	 * @access	public
	 * @return	void
	 */
	function out()
	{
		$this->session->sess_destroy();
		
		redirect('.');
	}
	
	/**
	 * This is a miscellaneous class used to crate a new randomm 6 character/digit password.
	 *
	 * @access	public
	 * @return	void
	 */
	function create_random_password() 
	{
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
		srand((double)microtime()*1000000);
		$i = 0;
		$pass = ''; 
		
		while ($i <= 6) {
			$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		
		return $pass;
	}
	
	/**
	 * Allows user to recover lost password. Sends an email to the user with new password. 
	 *
	 * @access	public
	 * @return	void
	 */
	function recover()
	{
		// make sure that the email variable has been posted
		if(isset($_POST['email']))
		{	
			// find out if the user's email exists in the database
			$userresult = $this->Rhemmodel->find_user_by_email($_POST['email']);
			$count = sizeof($userresult);
			if($count > 0)
			{
				$mail = new phpmailer();
				$mail->IsSMTP();
				$mail->Host     = "smtp-relay.gmail.com";
				$mail->From     = "gerardo@arstucson.net"; //$mail->AddReplyTo($_POST['email'], $_POST['firstname'] . " " . $_POST['lastname']);
				/* New code */
				$mail->SMTPSecure = "ssl";
				$mail->Port = "465";
				/* End new code */
				$mail->FromName = "RHEM Web Tool Administrator";
				$mail->Subject  =  "RHEM Web Tool: Your New Password";
				
				$newpassword = $this->create_random_password();
				
				$this->Rhemmodel->update_user_password($_POST['email'], $newpassword);
				
				$userName = $userresult[0]->username;
				$mail->Body     =  "The password for username " . $userName . " has been reset. Your new password is: " . $newpassword;
				$mail->AddAddress($_POST['email'],"");
				if(!$mail->Send())
				{
				   $data['errormessage'] = $this->errors->print_error("ERROR: Message was not sent. Please contact administrator.");
				}
				else
				{
					$data['errormessage'] = $this->errors->print_message('Your new password was successfully sent to your email.', 'orange_box');
				}
				$this->load->view('recover',$data);
			}
			// if the user's email was not found in the database
			else
			{
				$data['errormessage'] = $this->errors->print_message('The provided email was not found. Please try again.', 'red_box');
				$this->load->view('recover',$data);	
			}
			
		}
		// if the user's email was not posted properly
		else
		{
			$this->load->view('recover');
		}
	}
}
?>