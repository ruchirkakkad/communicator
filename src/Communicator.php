<?php

namespace Picahoo\Communicator;

class Communicator extends Configuration
{
	public function __construct()
	{
		parent::__construct();
		if(!$this->config_status || !$this->token_generated){
			return $this->token_response;
		}
	}

	public function refreshToken()
	{
		return $this->generateToken();
	}

	public function getContactList()
	{
		if($check = $this->checkConfigAndToken()){
			return $check;
		}

		$data = $this->request('get', [], ['Authorization' => 'Bearer ' . $this->token], $this->getApiUrl('/user/contact/all'));
		if ($data['statusCode'] != 200) {
			return [
				'code'     => $data['statusCode'],
				'status'   => 'fail',
				'message'  => $data['body'],
				'contacts' => null
			];
		}
		$res = json_decode($data['body'], true);
		return [
			'code'     => $data['statusCode'],
			'status'   => 'ok',
			'message'  => 'all good',
			'contacts' => $this->transformContacts(collect($res)->toArray())
		];
	}

	private function addContact($requestData)
	{
		if($check = $this->checkConfigAndToken()){
			return $check;
		}

		$data = $this->request('POST', [
			"first_name" => $requestData['first_name'],
			"last_name"  => $requestData['last_name'],
			"email"      => $requestData['email'],
			"phone"      => $requestData['phone']
		], ['Authorization' => 'Bearer ' . $this->token], $this->getApiUrl('/user/contact/force-store'));

		if($data['statusCode'] != 200){
			return [
				'code'     => $data['statusCode'],
				'status'   => 'fail',
				'message'  => $data['body']
			];
		}

		$jsonObj = json_decode($data['body'], true);
		if(!(isset($jsonObj['contact']) && $jsonObj['contact'])){
			return [
				'code'     => 400,
				'status'   => 'fail',
				'message'  => 'contact create fail',
			    'contact' => null
			];
		}
		return [
			'code'    => 200,
			'status'  => 'ok',
			'message' => 'contact created',
			'contact' => [
				"id"         => $jsonObj['contact']['id'],
				"user_id"    => $jsonObj['contact']['user_id'],
				"first_name" => $jsonObj['contact']['first_name'],
				"last_name"  => $jsonObj['contact']['last_name'],
				"email"      => $jsonObj['contact']['email'],
				"phone"      => $jsonObj['contact']['phone'],
				"active"     => $jsonObj['contact']['active']
			]
		];
	}

	public function findContactByEmail($email)
	{
		$response = $this->getContactList();
		if($response['code'] != 200){
			return $response;
		}

		$conatct = collect($response['contacts'])->where('email', $email)->first();
		if (!empty($conatct)){
			return [
				'code'    => 200,
				'status'  => 'ok',
				'message' => 'contact found',
				'contact' => $conatct
			];
		}
	}

	private function checkContactByEmail($email)
	{
		if($check = $this->checkConfigAndToken()){
			return $check;
		}
		$contactResponse = $this->findContactByEmail($email);

		if($contactResponse['code'] == 200){
			return $contactResponse;
		}

		$response = $this->addContact([
			"first_name" => null,
			"last_name"  => null,
			"email"      => $email,
			"phone"      => null
		]);

		return $response;
	}

	public function sendEmail($to, $message, $subject,$attachments=[])
	{
		$attachmentTotalSize = 25;
		$sendableAttachments = [];

		if(count($attachments) > 0){
			$actualFileSize = 0;
			foreach ($attachments as $attachmentPath){
				$sendableAttachments[]=[
					'name' => pathinfo($attachmentPath, PATHINFO_BASENAME),
					'type' => finfo_file(finfo_open(FILEINFO_MIME_TYPE), $attachmentPath),
					'content' => base64_encode(file_get_contents($attachmentPath)),
				];

				$actualFileSize+= (floatval(filesize($attachmentPath)) / pow(1024, 2));
			}

			if(!($actualFileSize <= $attachmentTotalSize)){
				return ['message' => 'attachment file size limit exceed, you upload under 25mb','status' => 'fail','code'	=> 404];
			}
		}

		if($check = $this->checkConfigAndToken()){
			return $check;
		}
		$res = $this->checkContactByEmail($to);
		if($res['code'] != 200){
			return $res;
		}

		if (empty($res) || !isset($res['contact']) && !isset($res['contact']['id'])) {
			return ['message' => 'contact not exists in system','status' => 'fail','code' => 404];
		}

		return $this->sendEmailByContactId($res['contact']['id'], $message, $subject,$sendableAttachments);
	}

	/*
	 * @ contactId integer
	 * @ message string
	 * @sendableAttachments array ['name','type','content']
	 *
	 * */
	public function sendEmailByContactId($contactId, $message, $subject,$sendableAttachments=[])
	{
		$data = $this->request('POST', [
			"message"    => $message,
			"contact_id" => $contactId,
			"subject"    => $subject,
		    "attachments" => $sendableAttachments
		], ['Authorization' => 'Bearer ' . $this->token], $this->getApiUrl('/mandrill/send'));

		if($data['statusCode'] != 200){

			if($data['statusCode'] ==100 && isset($data['error_message'])){
				$data['body'] = $data['error_message'];
			}

			return [
				'message' => $data['body'],
				'status'  => 'fail',
				'code'    => $data['statusCode']
			];
		}

		$res = json_decode($data['body'], true);
		return [
			'message' => $res['message'],
			'status'  => 'ok',
			'code'    => 200
		];
	}

	public function checkConfigAndToken()
	{
		if(!$this->config_status){
			return [
				'code'    => 400,
				'message' => 'Your config file not set properly, please set config file.',
				'status'  => 'fail',
			    'body' => null
			];
		}

		if(!$this->token_generated){
			return [
				'code'    => 400,
				'message' => 'Token not provided',
				'status'  => 'fail',
			    'body' => null
			];
		}

		return false;
	}

}