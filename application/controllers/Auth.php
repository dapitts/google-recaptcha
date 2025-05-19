<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
	private $use_recaptcha;
	private $site_key;
	
	function __construct()
	{
		parent::__construct();
		
		# check for client
		if (!$this->tank_auth->is_logged_in()) 
		{
			if ($this->config->item('use_subdomain_security'))
			{
				$this->tank_auth->check_for_subdomain();
	
			}
		}

		$this->use_recaptcha    = $this->config->item('use_recaptcha', 'tank_auth');
		$this->site_key         = $this->config->item('recaptcha_site_key', 'tank_auth');
	}

	function forgot_password()
	{
		# function is ready for auth-login		
		if ($this->tank_auth->is_logged_in()) 
		{									
			// logged in -> redirect to dashboard
			$this->tank_auth->auth_redirects($this->session->userdata('active_client_subdomain'), '/console/dashboard');
		} 
		else 
		{
			$data['use_recaptcha']  = $this->use_recaptcha;
			$data['site_key']       = $this->site_key;
			
			if ($this->use_recaptcha)
			{
				if ($this->input->method(TRUE) === 'POST')
				{
					$this->load->library('google_recaptcha');
	
					$recaptcha_response = $this->input->post('g-recaptcha-response');
					$ip_address         = $this->input->ip_address();
					$verify_success     = TRUE;
	
					if (!empty($recaptcha_response))
					{
						$response = $this->google_recaptcha->verify($recaptcha_response, $ip_address);
	
						if ($response['success'])
						{
							$verify_success = $response['response']['success'];  // true|false
						}
						else
						{
							$verify_success = FALSE;
						}
					}
	
					if (empty($recaptcha_response) || !$verify_success)
					{
						$this->session->set_userdata('my_flash_message_type', 'error');
						$this->session->set_userdata('my_flash_message', 'Please complete the reCAPTCHA and then resubmit your password reset.');
	
						$this->load->view('assets/header');
						$this->load->view('auth/forgot_password_form_new', $data);
						$this->load->view('assets/footer');
	
						return;
					}
				}
			}
	
			$this->form_validation->set_rules('login', 'Username', 'trim|required');
	
			$data['errors'] = array();
			$email_sent 	= FALSE;
			
			if ($this->form_validation->run()) 
			{								
				# validation ok
				if (!is_null($data = $this->tank_auth->forgot_password($this->form_validation->set_value('login')))) 
				{
					$data['site_name'] = $this->config->item('website_name', 'tank_auth');
	
					# Send email with password activation link
					$this->_send_email('forgot_password', $data['email'], $data);
	
					$this->session->set_userdata('my_flash_message_type', 'success');
					$this->session->set_userdata('my_flash_message', 'An email with instructions has been sent to your email address.');
				} 
				else 
				{
					# FAILED - show success message and log event:
					$this->session->set_userdata('my_flash_message_type', 'success');
					$this->session->set_userdata('my_flash_message', 'An email with instructions has been sent to your email address.');
	
					# Write To Log
					$log_message = "[Password Reset Attempt] an invalid login was used to try and reset password.";
					$this->utility->write_log_entry('notice', $log_message);					
				}
				$email_sent = TRUE;
			}			
			else
			{
				# Set and Show Flash Alert Errors		
				$flashalertdata = validation_errors();
				$this->session->set_userdata('my_flash_message_type', 'error');
				$this->session->set_userdata('my_flash_message', $flashalertdata);
			}
			
			# Page Views
			if ($email_sent)
			{
				$this->load->view('assets/header');
				$this->load->view('auth/general_auth');
				$this->load->view('assets/footer');
			}
			else
			{
				$this->load->view('assets/header');
				$this->load->view('auth/forgot_password_form_new', $data);
				$this->load->view('assets/footer');
			}			
		}
	}
}