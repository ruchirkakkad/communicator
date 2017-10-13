<?php

namespace Picahoo\Communicator;

class Configuration
{
	protected $api_url;
	protected $password;
	protected $email;
	protected $mail_from_name;
	protected $token;
	protected $token_generated=false;
	protected $token_response=[
		'status' => 'fail',
		'code' => 400,
		'message' => 'configuration not set'
	];
	protected $config_status=false;


	public function __construct() {
		$this->setDefaultConfiguration();
		$this->token_response = $this->generateToken();
	}

	public function getCredentials()
	{
		return [
			'email'    => $this->email,
			'password' => $this->password
		];
	}

	private function setDefaultConfiguration()
	{
		if(empty(config('communicator')) || empty(config('communicator.credential')) || empty(config('communicator.mail'))){
			$this->config_status = false;
		}else{
			$this->version = config('communicator.version');
			$this->api_url = config('communicator.api_url');
			$this->email = config('communicator.credential.email');
			$this->password = config('communicator.credential.password');
			$this->mail_from_name = config('communicator.mail.mail_from_name');
			$this->config_status = true;
		}
	}

	public function generateToken()
	{
		if(!$this->config_status){
			return [
				'code'    => 400,
				'message' => 'Your config file not set properly, please set config file.',
				'status'  => 'fail',
			    'body' => null
			];
		}

		if(!empty($this->token)){
			return [
				'code'    => 200,
				'message' => 'Token Already exists',
				'status'  => 'ok',
				'token'   => $this->token
			];
		}
		$data = $this->request("POST", $this->getCredentials(), [],$this->getApiUrl('/user/authenticate'));
		if($data['statusCode'] != 200){
			$this->token_generated = false;
			return [
				'code'    => $data['statusCode'],
				'message' => $data['body'],
				'status'  => 'fail',
				'token'   => null
			];
		}
		$res = json_decode($data['body'], true);
		if (isset($res['token'])) {
			$this->token = $res['token'];
			$this->token_generated = true;
			return [
				'code'    => 200,
				'message' => 'Token generated',
				'status'  => 'ok',
				'token'   => $res['token']
			];
		}

	}

	public function getApiUrl($url)
	{
		return $this->api_url . $url;
	}

	public function getToken()
	{
		return $this->token;
	}
	public function transformContacts($contactLists)
	{
		$newList = [];
		if (isset($contactLists['contacts'])) {
			foreach ($contactLists['contacts'] as $contact) {
				$newList[] = [
					"id"         => $contact['id'],
					"user_id"    => $contact['user_id'],
					"first_name" => $contact['first_name'],
					"last_name"  => $contact['last_name'],
					"email"      => $contact['email'],
					"phone"      => $contact['phone'],
					"active"     => $contact['active'],
					"created_at" => $contact['created_at'],
					"updated_at" => $contact['updated_at'],
					"deleted_at" => $contact['deleted_at']
				];
			}
		}
		return $newList;
	}

	public function request($type = "GET", $data = [], $headers = [], $url)
	{
		$curl = curl_init();
		$HEADERS = ['Authorization' => 'Bearer ' . $this->token];
		$HEADERS = array_merge($HEADERS, $headers);
		if ($type == 'GET' || $type == 'get') {
			curl_setopt_array($curl, array (
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL            => $url . '?token=' . $this->token,
			));
		}
		else {
			curl_setopt_array($curl, array (
				CURLOPT_URL            => $url . '?token=' . $this->token,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => "",
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => $type,
				CURLOPT_POSTFIELDS     => http_build_query($data),
				CURLOPT_HTTPHEADER     => $HEADERS,
			));
		}
		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$error_no =curl_errno($curl);
		$error_message = curl_error($curl);
		curl_close($curl);

		if ($curl === null) {
			return [
				'statusCode' => $httpcode,
				'body' => $response,
			    'error_no' => $error_no,
			    'error_message' => $error_message
			];
        } else {
			return [
				'statusCode' => $httpcode,
				'body' => $response,
				'error_no' => $error_no,
			    'error_message' => $error_message
			];
        }
	}

}