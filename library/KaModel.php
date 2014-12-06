<?php
    class KaModel
    {
        private $table;
        protected $fields;
        private $db_connect;
		private $errors;

		public function beginTransaction()
		{
			$this->db_connect->beginTransaction();
		}

		public function commit()
		{
			$this->db_connect->commit();
		}

		public function rollBack()
		{
			$this->db_connect->rollBack();
		}

        public function __construct($class_name, $db=NULL)
        {
			$this->table=$class_name;
			$this->dbConnect($db);
			$this->fields=$this->getDBFields();
        }


		// Are are the fields in the model empty
		public function isEmpty()
		{
			$is_empty=1;
			foreach ($this->fields AS $key => $value)
			{
				if ($value==0 && $value===false) // empty doesn't check for 0 or false
				{
					$is_empty=0;
					break;
				} else if (!empty($value)) {
					$is_empty=0;
					break;
				}
			}

			return $is_empty;
		}

        public function get()
		{
			$sql="
				SELECT *
				FROM ".$this->getTable();
			       
			$query_string=$this->fieldsToQuery();
			$sql.=(!empty($query_string)?' WHERE '.$query_string:'');
			$query_result=$this->query($sql,$this->getFields());
				              
			return $query_result;
		}   

		public function fieldsToQuery()
		{
			if (is_array($this->fields))
			{
				$query_string='';
				foreach ($this->fields AS $key => $value)
				{
					if ($value===0 || $value===false)
					{
						$query_string .= " ".$key."=:".$key;  // This is for PDO so no values
						$query_string .= " AND";
					} else if (!empty($value)) { // If the field is empty, don't use it
						$query_string .= " ".$key."=:".$key;  // This is for PDO so no values
						$query_string .= " AND";
					}
				}
				$query_string = rtrim($query_string, " AND");
			}

			return $query_string;
		}

		static public function is_assoc($array) {
			if (is_array($array))
			{
				return (bool)count(array_filter(array_keys($array), 'is_string'));
			} else {
				return 0;
			}
		}

        public function load($data=null)
        {
			// Check if "id" was included in the array - if so then throw away the rest of the array
			if (isset($data["id"]))
			{
				$data=$data["id"];
			}

            // Check to see if it is an array
            if ($this->is_assoc($data))
            {
                foreach ($data AS $key=>$value)
                {
					//if (isset($this->fields[$key]))
					//{
						$this->fields[$key]=$value;
					//}
                }
				return 1;
            } else if (is_numeric($data) && ($data > 0)) {
				$data=intval($data);
                // It is an integer so load from the db using $data an key
				$sql="SELECT * FROM $this->table WHERE id = :id";
                $query=$this->db_connect->prepare($sql);
                $query->bindParam(':id', $data);
                $query->execute();
                $result=$query->fetch(PDO::FETCH_ASSOC);

				if (is_array($result))
				{
					foreach ($result AS $key=>$value)
					{
						if (isset($this->fields[$key]))
						{
							$this->fields[$key]=$value;
						}
					}
					return 1;
				} else {
					return 0;
				}
            } else if (is_null($data)) {
				return 1;
			}

			return 1;
        }

        protected function query($sql,$params=0)
        {
            // Add the ':' to the params key
            if (is_array($params))
            {
            	foreach($params AS $key=>$value)
				{
					if (!empty($value)) 
					{
						$params[':'.$key]=$value;
					}
					unset($params[$key]);
				}    
            }
			$query=$this->db_connect->prepare($sql);
			if (is_array($params)) 
			{
				$query->execute($params);
			} else {
				$query->execute();
			}

            return $query->fetchAll(PDO::FETCH_ASSOC);
        }

        public function save()
        {
            // Check to see if the id is set
            if (is_numeric($this->fields["id"]))
            {
				$id=intval($this->fields["id"]);
                // It is set, so just update the record
                $record_id=$id;
                $tempfields=$this->fields;
                unset($tempfields['id']);

                $sql="UPDATE $this->table SET ";
                $count=count($tempfields);
                $i=0;

                foreach ($tempfields AS $key=>$value)
                {
                    if (++$i===$count)
                    {
                        $sql.="$key=:$key";
                    } else {
                        $sql.="$key=:$key,";
                    }
                }
                $sql.=" WHERE id=:id";

				$tempfields['id']=$record_id;
                foreach ($tempfields AS $key=>$value)
                {
                    $tempfields[':'.$key]=$tempfields[$key];
                    unset($tempfields[$key]);
                }
                $query=$this->db_connect->prepare($sql);
                $query->execute($tempfields);
            } else {
                // there was not id, so insert a new record
                // First get the array keys
                $tempfields=$this->populatedFields();
                // take out the id field if it was set, because we don't need it for a new record
                unset($tempfields["id"]);

                $fieldlist=array_keys($tempfields);

                foreach ($tempfields AS $key=>$value)
                {
                    $tempfields[':'.$key]=$tempfields[$key];
                    unset($tempfields[$key]);
                }

                // Get the pdo string
                $pdo_string='';
                foreach ($fieldlist AS $key=>$value)
                {
                    if ($key==0)
                    {
                        $pdo_string=':'.$value;
                    } else {
                        $pdo_string.=', :'.$value;
                    }
                }

                $sql="INSERT INTO $this->table (".implode(', ',$fieldlist).") VALUES (".$pdo_string.")";
                $query=$this->db_connect->prepare($sql);
                $query->execute($tempfields);
				$this->fields["id"]=$this->db_connect->lastInsertId();

				return $this->fields["id"];
            }
        }

		public function populatedFields()
		{
			$rtn=array();
			foreach($this->fields AS $key => $value)
			{
				if ($value==0 || $value==false)
				{
					$rtn[$key]=$value;
				} else if (!empty($value)) {
					$rtn[$key]=$value;
				}
			}

			return $rtn;
		}

		public function getId()
		{
			return $this->fields["id"];
		}

        public function delete()
        {
            if (is_numeric($this->fields['id']))
            {
				$id=intval($this->fields['id']);
                $query=$this->db_connect->prepare("DELETE FROM $this->table WHERE id=:id");
                $query->execute(array(":id"=>$this->fields['id']));

				$this->fields=$this->getDBFields();
            } else {
				return 0;
			}
        }

		public function getErrors()
		{
			return $this->errors;
		}

        public function getFields()
        {
            return $this->fields;
        }

        public function getDBFields()
       {
            $query=$this->db_connect->prepare("DESCRIBE $this->table");
            $query->execute();
            $columns=$query->fetchAll(PDO::FETCH_COLUMN);

            $fields=array();
            foreach ($columns AS $key=>$value)
            {
                $fields[$value]='';
            }
			
			return $fields;

        }

        public function dbConnect($db)
        {
			// If this is a test use the test db as defined in config.php
			if ($GLOBALS['use_test_db'])
			{
				$db_name=TEST_DATABASE_NAME;
			} else {
				if (is_null($db))
				{
					$db_name=DATABASE_NAME;
				} else {
					$db_name=$db;
				}
			}
            $this->db_connect=new PDO("mysql:host=".DATABASE_HOST.";dbname=".$db_name,DATABASE_USERNAME,DATABASE_PASSWORD);
            $this->db_connect->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->db_connect->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        }

        public function getTable()
        {
            return $this->table;
        }

		// Validation Functions
		//------------------------------------------------------
		public function validation()
		{
			if (isset($this->rules) && is_array($this->rules) && count($this->rules)) // check to see if there are any rules
			{
				$no_error=1;
				foreach($this->rules AS $key => $value)
				{
					$rules=explode(',',$value); // Break it up because rules are separated by commas
					foreach ($rules AS $index => $rule) 
					{
						$value_array=explode('_',$rule);  // If it is a complex rule, break it up
						if (count($value_array) == 2)
						{
							$rule=$value_array[0];
							$value_criteria=$value_array[1];
						}

						if ($rule=='required')
						{
							if (!$this->required($key))
							{
								$no_error=0;
							}
						}
						if ($rule=='validEmail')
						{
							if (!$this->validEmail($key))
							{
								$no_error=0;
							}
						}
						if ($rule=='maxStringLength')
						{
							if (!$this->maxStringLength($key,$value_criteria))
							{
								$no_error=0;
							}
						}
						if ($rule=='mixStringLength')
						{
							if (!$this->mixStringLength($key,$value_criteria))
							{
								$no_error=0;
							}
						}
						if ($rule=='number')
						{
							if (!$this->number($key))
							{
								$no_error=0;
							}
						}
						if ($rule=='uniqueField')
						{
							if (!$this->uniqueField($key))
							{
								$no_error=0;
							}
						}
					}
				}
				return $no_error;
			} else { // There are no rules
				return 1;
			}
		}

		public function mixStringLength($key,$value_criteria)
		{
			if (sizeOf($this->fields[$key]) > $value_criteria) {
				$this->addError($key,"too short");
				return 0;
			} else {
				return 1;
			}
		}

		public function maxStringLength($key,$value_criteria)
		{
			if (sizeOf($this->fields[$key]) > $value_criteria) {
				$this->addError($key,"too long");
				return 0;
			} else {
				return 1;
			}
		}

		public function number($key)
		{
			if (!is_numeric($this->fields[$key]))
			{
				$this->addError($key,"not a number");
				return 0;
			} else {
				return 1;
			}
		}

		public function required($key)
		{
			if ($this->fields[$key]!==0 && $this->fields[$key] !== null && empty($this->fields[$key])) // check to see if the field is empty
			{
				$this->addError($key,"required");
				return 0;
			} else {
				return 1;
			}
		}

		public function validEmail($key)
		{
			if (!filter_var($this->fields[$key], FILTER_VALIDATE_EMAIL))
			{
				$this->addError($key,"invalid email");	
				return 0;
			} else {
				return 1;
			}
		}

		public function uniqueField($key)
		{
			$sql="
				SELECT *
				FROM ".$this->getTable()."
				WHERE ".$key."=:".$key;
			$data=array($key=>$this->fields[$key]);
			$match=$this->query($sql, $data);

			if (count($match))
			{
				$this->addError($key,"not unique");	
				return 0;
			} else {
				return 1;
			}
		}	

		public function addError($key,$error_message)
		{
			if (isset($this->errors[$key]) && is_array($this->errors[$key])) {
				array_push($this->errors[$key],$error_message);				
			} else {
				$this->errors[$key]=array($error_message);
			}
		}

		// End Validation Functions
		//--------------------------------------------------------
    }
?>
