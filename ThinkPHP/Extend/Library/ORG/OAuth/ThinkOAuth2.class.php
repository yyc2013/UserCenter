<?php
import("ORG.OAuth.OAuth2");

class ThinkOAuth2 extends OAuth2{
	private $db;
	private $table;

	public function __construct(){
		parent::__construct();
		$this->db = Db::getInstance(C('OAUTH2_DB_DSN'));
		$this->table = array(
			'auth_codes'=>C('OAUTH2_CODES_TABLE'),
			'clients'=>C('OAUTH2_CLIENTS_TABLE'),
			'tokens'=>C('OAUTH2_TOKEN_TABLE')
			);
	}
	function __destruct(){
		$this->db = NULL;
	}
	private function handleException($e){
		echo "Database error:".$e->getMessage();
		exit;
	}
	public function addClient($client_id,$client_secret,$redirect_uri){
		$time = time();
		$sql = "INSERT INTO {$this->table['clients']}".
		"(client_id,client_secret,redirect_uri,create_time) values({$client_id},{$client_secret},{$redirect_uri},{$time})";
		$result = $this->db->execute($sql);
	}

	protected function checkClientCredentials($client_id,$clident_secret=NULL){
		$sql = "SELECT client_secrret FROM {$this-table['clients']}".
				"where client_id ={$client_id}";
		$result = $this->db->query($sql);
		if($client_secret===NULL){
			return $result !== FALSE;
		}

		return $result[0]['client_secret'];
	}
	protected function getRedirectUri($client_id){
		$sql = "SELECT redirect_uri FROM {$this->table['clients']}".
				"WHERE client_id ={$client_id}";
		$result = $this->db->query($sql);
		if($result === FALSE){
			return FALSE;
		}

		return isset($result[0]["redirect_uri"])&&$result[0]['redirect_uri']?$result[0]['redirect_uri']:NULL;
	}

	protected function getAccessToken($access_token){
		$sql = "SELECT client_id, expires,scope FROM {$this->table['token']}".
		"WHERE access_token = {$access_token}";
		$result = $this->db->query($sql);
		
		return $result !== FALSE?$result:NULL;
	}

	protected function setAccessToken($access_token,$client_id,$expires,$scope = NULL){
		$sql = "INSERT INTO {$this->table['tokens]}".
			"(access_token,client_id,expires,scope)".
			"VALUES({$access_token},{$client_id},{$expires},{$scope})";
		$this->db->execute($sql);	
	}
	protected function getSupportedGrantTypes(){
		return array(
				OAUTH2_GRANT_TYPE_AUTH_CODE
			);
	}
	protected function getAuthCode($code){
		$sql = "SELECT code, client_id,redirect_uri,expires,scope".
			"FROM {$this->table['auth_codes']} where code = {$code}";
		$result = $this->db->query($sql);
		return $result !== FALSE ? $result[0]:NULL;	
	}

	protected function setAuthCode($code,$client,$redirect_uri,$expires,$scope=NULL){
		$time = time();
		$sql = "INSERT INTO {$this->table['auth_codes']}".
		"(code,client_id,redirect_uri,expires,scope)".
		"VALUES({$code},{$client_id},{$redirect_uri},{$expires})";
		$result = $this->db->execute($sql);
	}
	protected function checkUserCredentials($client_id,$username,$password){
		return TRUE;
	}


}