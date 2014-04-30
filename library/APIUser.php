<?php
	class APIUser extends KaModel
	{

		public function __construct()
		{
			parent::__construct(__CLASS__);
		}

		public function login($username=null,$password=null)
		{
			$result=$this->query('SELECT id FROM apiuser WHERE username=:username AND password=:password LIMIT 1', array('username'=>$username, 'password'=>$password));
			if (count($result))
			{
				// Set the api user
				$this->load($result[0]['id']);
				return true;
			} else {
				return false;
			}
		}

		public function getProgramName()
		{
			return $this->fields['program_name'];
		}
	}
?>
