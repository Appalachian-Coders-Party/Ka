<?php
	class APIUsers extends KaModel
	{
		public function __construct()
		{
			parent::__construct(__CLASS__);
		}

		public function login($username,$password)
		{
			$result=$this->query('SELECT * FROM apiusers WHERE username=:username AND password=:password', array('username'=>$username, 'password'=>$password));
			if (count($result))
			{
				return true;
			} else {
				return false;
			}
		}
	}
?>
