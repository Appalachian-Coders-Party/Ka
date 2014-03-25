<?php
	class APIUsers extends KaModel
	{

		public function __construct()
		{
			parent::__construct(__CLASS__);
		}

		public function login($username=null,$password=null)
		{
			$result=$this->query('SELECT * FROM apiuser WHERE username=:username AND password=:password', array('username'=>$username, 'password'=>$password));
			if (count($result))
			{
				return true;
			} else {
				return false;
			}
		}
	}
?>
