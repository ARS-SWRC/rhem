<?php
/**
 * Allows user to modify account settings. 
 *
 * @access	public
 * @return	void
 */
class Account extends CI_Controller {
	
	/* This defines a constructor for the current controller */
	function __construct()
    {
		parent::__construct();
		
		$this->load->library('session');
		// load custom errors class
		$this->load->library('Errors');
		// load recaptcha helper
		$this->load->helper('recaptcha'); 
		// load the mailer
		$this->load->helper('phpmailer'); 
    }
	
	function index()
	{
		$accountSettings = $this->Rhemmodel->get_user_account_settings($this->session->userdata('user_id'));
		$data['modify_parameter_file_flag'] = ($accountSettings->modify_parameter_file_flag == 1)?'checked':'';
		$data['detailed_output_flag'] = ($accountSettings->detailed_output_flag == 1)?'checked':'';
		
		// TODO: Think about removing this ability to choose which model to run later.  This was implemented to satisfy Jason William's request
		$data['model_version'] = $accountSettings->model_version;
		if($this->session->userdata('user_id') == 67 or $this->session->userdata('user_id') == 1)
			$data['model_version_21_enabled'] = 'enabled';
		else
			$data['model_version_21_enabled'] = 'disabled';

		
		$this->load->view('account',$data);
	}
	
	/*
	* Saves the changes to the user's account.  User is able to change password and report and parameter modification
	* options through this interface.
	*
	* @return void
	*/
	function save()
	{
		$newPass = $_POST['newPassword'];
		$modifyInputParametersFlag = isset($_POST['modifyInputParametersFlag']) ? 1 : 0;
		$detailedOutputFlag = isset($_POST['detailedOutputFlag']) ? 1 : 0;
		$model_version = $_POST['modelVersion'];
	
		$this->Rhemmodel->update_user_account($this->session->userdata('user_id'), $newPass, $modifyInputParametersFlag, $detailedOutputFlag, $model_version);
		$data['accountupdated'] = $this->errors->print_message("Your account has been updated. ", "alert-success");
		// show the currently selected data for the user
		$accountSettings = $this->Rhemmodel->get_user_account_settings($this->session->userdata('user_id'));
		$data['modify_parameter_file_flag'] = ($accountSettings->modify_parameter_file_flag == 1)?'checked':'';
		$data['detailed_output_flag'] = ($accountSettings->detailed_output_flag == 1)?'checked':'';
		
		// TODO: Think about removing this ability to choose which model to run later.  This was implemented to satisfy Jason William's request
		$data['model_version'] = $accountSettings->model_version;
		if($this->session->userdata('user_id') == 67 or $this->session->userdata('user_id') == 1)
			$data['model_version_21_enabled'] = 'enabled';
		else
			$data['model_version_21_enabled'] = 'disabled';


		$this->load->view('account',$data);
	}
}
?>