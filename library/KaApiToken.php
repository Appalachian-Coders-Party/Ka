<?php
	class KaApiToken extends KaModel
	{
		public function __construct()
		{
			parent::__construct(__CLASS__);
		}

		public function isAlive()
		{
			$fields=$this->getFields();
			$token_result=$this->query("
				SELECT * FROM kaapitoken WHERE token = :token AND ts_die > ".time()." LIMIT 1",
				$fields);
			if (count($token_result))
			{
				$this->load($token_result[0]['id']);
				return true;
			} else {
				return false;
			}
		}

		public function addTime()
		{
			$fields=$this->getFields();
			$this->load(array('ts_die'=>$fields['ts_die']+600));
			$this->save();
		}
	}
?>
