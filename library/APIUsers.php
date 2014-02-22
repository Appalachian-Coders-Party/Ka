<?php
	class APIUsers extends KaModel
	{
		private $token;

		public function __construct()
		{
			parent::__construct(__CLASS__);
		}

		public function login($username=null,$password=null,$token=null)
		{
			$result=$this->query('SELECT * FROM apiuser WHERE username=:username AND password=:password', array('username'=>$username, 'password'=>$password));
			if (count($result))
			{
				$this->setToken($result[0]['id']);
				return true;
			} else {
				return false;
			}
		}

		public function getTokenJSON(){
			return json_encode(array('token'=>$this->token));
		}

		private function setToken($seed)
		{
			$birth_ts=time();
			$die_ts=$birth_ts+3600;
			$token_model=new KaApiToken;
			$this->token=md5($seed.time());
			$token_model->load(
				array(
					"token"=>$this->token,
					"ts_birth"=>$birth_ts,
					"ts_die"=>$die_ts
				)
			);
			$token_model->save();
		}
	}
?>
