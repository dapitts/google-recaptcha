<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Google_recaptcha 
{
	const API_URL = 'https://www.google.com/recaptcha/api/siteverify';

	private $ch;
	private $secret_key;

	function __construct()
	{
		$CI =& get_instance();		

		$this->secret_key   = $CI->config->item('recaptcha_secret_key', 'tank_auth');
	}

	public function verify($recaptcha_response, $ip_address)
	{
		$header_fields = array(
			'Content-Type: application/x-www-form-urlencoded',
			'Accept: application/json'
		);

		$post_fields = array(
			'secret'    => $this->secret_key,
			'response'  => $recaptcha_response,
			'remoteip'  => $ip_address
		);

		$response = $this->call_api('POST', self::API_URL, $header_fields, http_build_query($post_fields));

		if ($response['result'] !== FALSE)
		{
			if ($response['http_code'] === 200)
			{
				return array(
					'success'   => TRUE,
					'response'  => $response['result']
				);
			}
			else
			{
				return array(
					'success'   => FALSE,
					'response'  => $response['result']
				);
			}
		}
		else
		{
			return array(
				'success'   => FALSE,
				'response'  => array(
					'status'    => 'cURL returned false',
					'message'   => 'errno = '.$response['errno'].', error = '.$response['error']
				)
			);
		}
	}

	private function call_api($method, $url, $header_fields, $post_fields = NULL)
	{
		$this->ch = curl_init();

		switch ($method)
		{
			case 'POST':
				curl_setopt($this->ch, CURLOPT_POST, true);

				if (isset($post_fields))
				{
					curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_fields);
				}

				break;
			case 'PUT':
				curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');

				if (isset($post_fields))
				{
					curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_fields);
				}

				break;
			case 'DELETE':
				curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
		}

		if (is_array($header_fields))
		{
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header_fields);
		}

		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
		//curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 5);
		//curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);

		if (($response['result'] = curl_exec($this->ch)) !== FALSE)
		{
			// Make sure the size of the response is non-zero prior to json_decode()
			if (curl_getinfo($this->ch, CURLINFO_SIZE_DOWNLOAD_T))
			{
				$response['result'] = json_decode($response['result'], TRUE);
			}

			$response['http_code'] = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		}
		else
		{
			$response['errno'] 	= curl_errno($this->ch);
			$response['error'] 	= curl_error($this->ch);
		}

		curl_close($this->ch);

		return $response;
	}
}