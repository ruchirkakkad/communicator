<?php

namespace Picahoo\Communicator;

use GuzzleHttp\Client;

class Communicator {

	protected static $version = 'v1';
	protected static $api_url= 'http://picahoo-api.local/v1';

	private $password;
	private $email;
	private $token;

	public function __construct() {
		$this->setDefaultConfiguration();
		$data = $this->generateToken();
		if($data['code'] != 200){
			return $data;
		}
	}

	/**
     * Set credential.
     *
     * @return void
     */
	private function setDefaultConfiguration(){
		$this->email = config('communicator.email');
		$this->password = config('communicator.password');
	}

	/**
     * Get credential.
     *
     * @return array
     */
	public function getCredentials()
	{
		return [
			'email' => $this->email,
		    'password' => $this->password
		];
	}

	public function refreshToken()
	{
		$token = $this->generateToken();
		if($token){
			return $token;
		}
		return '';
	}

	public function generateToken()
	{
		$client = new Client();
		try{
			$response = $client->request('post', 'picahoo-api.local/api/v1/user/authenticate',[
				'form_params' => [
					'email' => $this->email,
					'password' => $this->password
				]
			]);
			$body =(string)$response->getBody();
			$jsonObj =json_decode($body);

			if($data = collect($jsonObj)->toArray()){
				if(isset($data['token'])){
					session()->put('picahoo_communicator_token',$data['token']);
					$this->token = $data['token'];
					return ['code' => 200,'message' => 'Token generated','status' => 'ok','token' =>$data['token']];
				}
			}
		}catch (\Exception $e){
		}
		return ['code' => 400,'message' => 'Token generate fail, enter valid credentials in config file!','status' => 'fail'];
	}

	/**
     * Get Token.
     *
     * @return array
     */
	public function getToken()
	{
		return $this->token;
	}

	public function getContactList()
	{
		try{
			$client = new Client();
			$response = $client->request('get', 'picahoo-api.local/api/v1/user/contact/all',[
				'headers' => [
			        'Authorization' => 'Bearer '.$this->token
			    ]
			]);
			$body =(string)$response->getBody();
			$jsonObj =json_decode($body);
			return $this->transformContacts(collect($jsonObj)->toArray());
		}catch (\Exception $e){
			return collect([]);
		}
	}

	public function addContact($requestData)
	{
		try{
			$client = new Client();
			$response = $client->request('post', 'picahoo-api.local/api/v1/user/contact/force-store',[
				'headers' => [
			        'Authorization' => 'Bearer '.$this->token
			    ],
			    'form_params' => [
				    "first_name"=>$requestData['first_name'],
                    "last_name"=>$requestData['last_name'],
                    "email"=>$requestData['email'],
                    "phone"=>$requestData['phone']
			    ]
			]);
			$body =(string)$response->getBody();
			$jsonObj =collect(json_decode($body))->toArray();
			if(isset($jsonObj['contact'])){
				return [
					"id"         => $jsonObj['contact']->id,
					"user_id"    => $jsonObj['contact']->user_id,
					"first_name" => $jsonObj['contact']->first_name,
					"last_name"  => $jsonObj['contact']->last_name,
					"email"      => $jsonObj['contact']->email,
					"phone"      => $jsonObj['contact']->phone,
					"active"     => $jsonObj['contact']->active
				];
			}
		}catch (\Exception $e){
			return [];
		}
	}

	public function checkContactByEmail($email)
	{
		$contact_lists = $this->getContactList();
		$conatct = collect($contact_lists)->where('email',$email)->first();
		if(empty($conatct)){
			return $this->addContact([
				"first_name"=>null,
                "last_name"=>null,
                "email"=>$email,
                "phone"=>null
			]);
		}else{
			return collect($contact_lists)->where('email',$email)->first();
		}
	}

	/**
     * Get Token.
     *
     * @return array
     */
	public function sendEmail($to, $message, $subject)
	{
		$contact = $this->checkContactByEmail($to);
		if(empty($contact) || !isset($contact['id'])){
			return ['message' => 'contact not exists in system','status' => 'fail','code' => 404];
		}
		return $this->sendEmailByContactId($contact['id'],$message, $subject);
	}

	public function sendEmailByContactId($contactId,$message, $subject)
	{
		try{
			$client = new Client();
			$response = $client->request('post', 'picahoo-api.local/api/v1/mandrill/send',[
				'headers' => [
			        'Authorization' => 'Bearer '.$this->token
			    ],
				'form_params' => [
					 "message"    => $message,
					 "contact_id" => $contactId,
					 "subject"    => $subject
				]
			]);

			if($response->getStatusCode() == 200){
				$body =(string)$response->getBody();
				$res = \GuzzleHttp\json_decode($body);
				return ['message' => $res->message,'status' => 'ok','code' => 200];
			}
		}catch (\Exception $e){
			return ['message' => $e->getMessage(),'status' => 'fail','code' => $e->getCode()];
		}
		return ['message' => 'Bad Request','status' => 'fail','code' => 400];
	}

	public function transformContacts($contactLists)
	{
		$newList = [];
		if(isset($contactLists['contacts'])){
			foreach ($contactLists['contacts'] as $contact){
				$newList[] = [
					"id"         => $contact->id,
					"user_id"    => $contact->user_id,
					"first_name" => $contact->first_name,
					"last_name"  => $contact->last_name,
					"email"      => $contact->email,
					"phone"      => $contact->phone,
					"active"     => $contact->active,
					"created_at" => $contact->created_at,
					"updated_at" => $contact->updated_at,
					"deleted_at" => $contact->deleted_at
				];
			}
		}
		return $newList;
	}


}