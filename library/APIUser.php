<?php
	class APIUser extends KaModel
	{

		public function __construct()
		{
			parent::__construct(__CLASS__);
		}

		public function login($username=null,$password=null)
		{
			$result=$this->query('SELECT id, password FROM apiuser WHERE username=:username LIMIT 1', array('username'=>$username));
			if (count($result))
			{
				if (password_verify($password, $result[0]['password']))
				{
					// Set the api user
					$this->load($result[0]['id']);
					return true;
				} else {
					return false;
				}
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
