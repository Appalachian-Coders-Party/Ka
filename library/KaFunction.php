<?php
	class KaFunction
	{
		/****************************************
		/ Returns 0 if there is no difference between two arrays
		/ Returns an array of differences if they are not the same
		/***************************************/
		public static function array_diff_assoc_rec($array1,$array2)
		{
			foreach($array1 as $key => $value) 
			{ 
				if(is_array($value)) 
				{ 
					if(!isset($array2[$key])) 
					{ 
						$difference[$key] = $value; 
					} 
					elseif(!is_array($array2[$key])) 
					{ 
						$difference[$key] = $value; 
					} 
					else 
					{ 
						$new_diff = KaFunction::array_diff_assoc_rec($value, $array2[$key]); 
						if($new_diff != FALSE) 
						{ 
							$difference[$key] = $new_diff; 
						} 
					} 
				} 
				elseif(!isset($array2[$key]) || $array2[$key] != $value) 
				{ 
					$difference[$key] = $value; 
				} 
			} 
			return !isset($difference) ? 0 : $difference; 
		}
	}
?>
