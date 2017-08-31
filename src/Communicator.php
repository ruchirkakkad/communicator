<?php

namespace Picahoo\Communicator;

//use GuzzleHttp\Client;
use Picahoo\Communicator\Interfaces\CommunicatorInterface;

class Communicator implements CommunicatorInterface{

	protected static $version = 'v1';
	protected $api_url= 'http://picahooapi.test4you.in/api/v1';

	private $password;
	private $email;
	private $token;

	public function __construct() {
		$this->setDefaultConfiguration();
		$this->generateToken();
	}

	private function setDefaultConfiguration(){
		$this->email = config('communicator.email');
		$this->password = config('communicator.password');
	}

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
		throw new \Exception('Token Generate Fail');
		return '';
	}

	public function getApiUrl($url)
	{
		return $this->api_url.$url;
	}

	public function generateToken()
	{
		$data = $this->request("POST",$this->getCredentials(),[],$this->getApiUrl('/user/authenticate'));
		$res = json_decode($data,true);
		if(isset($res['token'])){
			$this->token = $res['token'];
			return ['code' => 200,'message' => 'Token generated','status' => 'ok','token' =>$res['token']];
		}else{
			return ['code' => 400,'message' => 'Token generate fail, enter valid credentials in config file!','status' => 'fail'];
		}


//		$client = new Client();
//		try{
//			$response = $client->request('post', $this->getApiUrl('/user/authenticate'),[
//				'form_params' => [
//					'email' => $this->email,
//					'password' => $this->password
//				]
//			]);
//			$body =(string)$response->getBody();
//			$jsonObj =json_decode($body);
//
//			if($data = collect($jsonObj)->toArray()){
//				if(isset($data['token'])){
//					session()->put('picahoo_communicator_token',$data['token']);
//					$this->token = $data['token'];
//					return ['code' => 200,'message' => 'Token generated','status' => 'ok','token' =>$data['token']];
//				}
//			}
//		}catch (\Exception $e){
//		}
//		return ['code' => 400,'message' => 'Token generate fail, enter valid credentials in config file!','status' => 'fail'];
	}

	public function getToken()
	{
		return $this->token;
	}

	public function getContactList()
	{
		$this->generateToken();
		$data = $this->request('get',[],['Authorization' => 'Bearer ' . $this->token],$this->getApiUrl('/user/contact/all'));
		$res = json_decode($data,true);
		return $this->transformContacts(collect($res)->toArray());
//
//		if(isset($res['token'])){
//			$this->token = $res['token'];
//			return ['code' => 200,'message' => 'Token generated','status' => 'ok','token' =>$data['token']];
//		}else{
//			return ['code' => 400,'message' => 'Token generate fail, enter valid credentials in config file!','status' => 'fail'];
//		}
//		try{
//			$client = new Client();
//			$response = $client->request('get', $this->getApiUrl('/user/contact/all'),[
//				'headers' => [
//			        'Authorization' => 'Bearer '.$this->token
//			    ]
//			]);
//			$body =(string)$response->getBody();
//			$jsonObj =json_decode($body);
//			return $this->transformContacts(collect($jsonObj)->toArray());
//		}catch (\Exception $e){
//			return collect([]);
//		}
	}

	public function addContact($requestData)
	{
		$this->generateToken();

		$data = $this->request('POST', [
			"first_name" => $requestData['first_name'],
			"last_name"  => $requestData['last_name'],
			"email"      => $requestData['email'],
			"phone"      => $requestData['phone']
		], ['Authorization' => 'Bearer ' . $this->token], $this->getApiUrl('/user/contact/force-store'));
		$jsonObj =json_decode($data,true);

		if(isset($jsonObj['contact']) && $jsonObj['contact']){
			return [
				"id"         => $jsonObj['contact']['id'],
				"user_id"    => $jsonObj['contact']['user_id'],
				"first_name" => $jsonObj['contact']['first_name'],
				"last_name"  => $jsonObj['contact']['last_name'],
				"email"      => $jsonObj['contact']['email'],
				"phone"      => $jsonObj['contact']['phone'],
				"active"     => $jsonObj['contact']['active']
			];
		}else{
			return [];
		}

//		try{
//			$client = new Client();
//			$response = $client->request('post', $this->getApiUrl('/user/contact/force-store'),[
//				'headers' => [
//			        'Authorization' => 'Bearer '.$this->token
//			    ],
//			    'form_params' => [
//				    "first_name"=>$requestData['first_name'],
//                    "last_name"=>$requestData['last_name'],
//                    "email"=>$requestData['email'],
//                    "phone"=>$requestData['phone']
//			    ]
//			]);
//			$body =(string)$response->getBody();
//			$jsonObj =collect(json_decode($body))->toArray();
//			if(isset($jsonObj['contact'])){
//				return [
//					"id"         => $jsonObj['contact']->id,
//					"user_id"    => $jsonObj['contact']->user_id,
//					"first_name" => $jsonObj['contact']->first_name,
//					"last_name"  => $jsonObj['contact']->last_name,
//					"email"      => $jsonObj['contact']->email,
//					"phone"      => $jsonObj['contact']->phone,
//					"active"     => $jsonObj['contact']->active
//				];
//			}
//		}catch (\Exception $e){
//			return [];
//		}
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

	public function sendEmail($to, $message, $subject)
	{
		$contact = $this->checkContactByEmail($to);
		if(empty($contact) || !isset($contact['id'])){
			return ['message' => 'contact not exists in system','status' => 'fail','code' => 404];
		}
		$res = $this->sendEmailByContactId($contact['id'],$message, $subject);
		if($res['code'] == 200){
			return true;
		}else{
			return false;
		}
//		return $this->sendEmailByContactId($contact['id'],$message, $subject);
	}

	public function sendEmailByContactId($contactId,$message, $subject)
	{
		$this->generateToken();
		$data = $this->request('POST',[
							 "message"    => $message,
							 "contact_id" => $contactId,
							 "subject"    => $subject
						],['Authorization' => 'Bearer ' . $this->token],$this->getApiUrl('/mandrill/send'));

		$res = json_decode($data,true);
		if($res['message']){
			return ['message' => $res['message'],'status' => 'ok','code' => 200];
		}
		return ['message' => 'Bad Request','status' => 'fail','code' => 400];
//		try{
//			$client = new Client();
//			$response = $client->request('post', $this->getApiUrl('/mandrill/send'),[
//				'headers' => [
//			        'Authorization' => 'Bearer '.$this->token
//			    ],
//				'form_params' => [
//					 "message"    => $message,
//					 "contact_id" => $contactId,
//					 "subject"    => $subject
//				]
//			]);
//
//			if($response->getStatusCode() == 200){
//				$body =(string)$response->getBody();
//				$res = \GuzzleHttp\json_decode($body);
//				return ['message' => $res->message,'status' => 'ok','code' => 200];
//			}
//		}catch (\Exception $e){
//			return ['message' => $e->getMessage(),'status' => 'fail','code' => $e->getCode()];
//		}
//		return ['message' => 'Bad Request','status' => 'fail','code' => 400];
	}

	public function transformContacts($contactLists)
	{
		$newList = [];
		if(isset($contactLists['contacts'])){
			foreach ($contactLists['contacts'] as $contact){
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

	private function request($type = "GET", $data = [], $headers = [], $url)
	{
		$curl = curl_init();
		$HEADERS = ['Authorization' => 'Bearer ' . $this->token];
		$HEADERS = array_merge($HEADERS, $headers);
		if($type == 'GET' || $type =='get'){
			// Set some options - we are passing in a useragent too here
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_URL => $url.'?token='.$this->token,
			));
		}else{
			curl_setopt_array($curl, array (
				CURLOPT_URL            => $url.'?token='.$this->token,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => "",
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 25,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => $type,
				CURLOPT_POSTFIELDS     => http_build_query($data),
				CURLOPT_HTTPHEADER     => $HEADERS,
			));
		}
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			dd($err);
		}
		else {
			return $response;
		}
	}



}