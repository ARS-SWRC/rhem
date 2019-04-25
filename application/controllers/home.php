<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	/**
	 * This the entry page for the DRHEM Web Tool
	 *
	 * Maps to the following URL
	 * 		http://apps.tucson.ars.ag.gov/drhem/index.php/home
	 *	- or -  
	 * 		http://apps.tucson.ars.ag.gov/drhem/index.php
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://apps.tucson.ars.ag.gov
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/main/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	
	function __construct()
	{
		parent::__construct();	
		$this->load->library('session');
		//$this->output->cache(60);
	}
	
	public function index()
	{
		$this->load->view('home');
	}
	
	/**
	 * Routes to the static about page from the home controller.
	 *
	 * @access	public
	 * @return	void
	 */
	public function about()
	{
		$this->load->view('about');
	}

	/**
	 * Routes to the static links page from the home controller.
	 *
	 * @access	public
	 * @return	void
	 */
	public function docs()
	{
		$this->load->view('docs');
	}
	
	public function page404()
	{
		$this->load->view("page404.php");
	}
}

/* End of file home.php */
/* Location: ./application/controllers/home.php */