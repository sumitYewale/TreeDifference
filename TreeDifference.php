<?php

$errors = array(
	0 => "Library only works with version 5.3+",
	1 => "Invalid data passed to compare!!"
);

class TreeDifference{    

	protected static $error;
	protected static $removed_items = [];

	public function __construct(){
		set_error_handler(array($this,"error"));
		self::$error = $GLOBALS;
		try{
			if(version_compare(PHP_VERSION , "5.3.*") == -1){
				trigger_error(self::$error['errors'][0]);
			}
		}
		catch(Exception $e){
			trigger_error($e->getMessage());
		}   
	}

	private static function getType($path1 , $path2 ){
		if((is_array($path1) && is_array($path2)) || 
			(is_array($path1) && is_object($path2))|| (is_object($path1) && is_array($path2))){
			return true;
	}
	else{
		return false;
	}
	}

	    // ****************************** 
	    //  Set perticular keys to remove 
	    // ******************************
	public static function set_remove($removed_items = array()){
		self::$removed_items = $removed_items;
	}

		// ****************************** 
	    //  Get perticular keys to remove 
	    // ******************************
	private static function get_remove(){
		return self::$removed_items;
	}

	    // ****************************** 
	    //  Get difference between to arrays 
	    // ******************************
	public static function get_diff($structured_arr1 , $structured_arr2 , $status = false){
		try{
			if(!self::getType($structured_arr1 , $structured_arr2) || (empty($structured_arr1) && empty($structured_arr2))){
				trigger_error(self::$error['errors'][1]);
			}       
			else{
				$structured_arr1 = self::convert_to_array($structured_arr1);
				$structured_arr2 = self::convert_to_array($structured_arr2);
				// $arrDiff = array();
				
	            $new = self::new_values_in_array($structured_arr1 , $structured_arr2);
				$edited = self::edit_values_in_array($structured_arr1 , $structured_arr2, $status);
				
	            
	            $removed = self::removed_values_in_array($structured_arr1 , $structured_arr2);

	            if(!empty(self::get_remove())){
	                foreach(self::$removed_items as $d_key => $value){
	                    unset($new[$value]  , $removed[$value] , $edited[$value]);
	                }
	            }

	            if(!empty($new) && $new != null && $new != []){
	                $arrDiff['new'] = $new;
	            }
				if(!empty($edited) && $edited != null && $edited != []){
					$arrDiff['edited'] =  $edited;
				}
	            if(!empty($removed) && $removed != null && $removed != []){
	                $arrDiff['removed'] = $removed;
	            }

				return $arrDiff;
			}
		}
		catch(Exception $e){
			trigger_error($e->getMessage());
		}
	}

	public static function new_values_in_array($new , $old){
		if(!isset($arrDiff)){
			$arrDiff= array(); 
		}
		if(gettype($new) == "object" || gettype($new) == "array"){
			foreach($new as $key => $val) { 
				if(isset($old[$key])){ 
					if(is_array($val)){
						self::new_values_in_array($val, $old[$key]);
						// $arrDiff = array_merge($arrDiff , self::new_values_in_array($val, $old[$key]));
					}
				}
				else{
					if($val != [] || $val != null){
						$arrDiff[$key] =  (object)[
							'oldvalue' => '',
							'newvalue' => json_encode(array_values((array)$val))
						]; 
					}
				}
			} 
		}
		return $arrDiff; 
	}

	public static function edit_values_in_array($new , $old , $status = false){
		if(!isset($arrDiff)){
			$arrDiff= array(); 
		}
		
		if(gettype($new) == "array" || gettype($new) == "object"){
			foreach($new as $key => $val) { 
				if(isset($old[$key])){ 
					if(is_array($val) && is_array($old[$key])){
						if(count($val) == count($old[$key])){
							if((count($val) != count($val , COUNT_RECURSIVE))){
								$arrDiff = array_merge($arrDiff,  self::edit_values_in_array($val , $old[$key]));
							}		
							else{
								// If any new element is added 
								$differ = array_map('unserialize',array_diff(array_map('serialize',$val) , array_map('serialize',$old[$key])));
								if(!empty($differ) && $val != [] && !empty($val) && $val != null){
									$arrDiff[$key] = (object)[
										'oldvalue' => json_encode(array_values((array)$old[$key])),
										'newvalue' => json_encode(array_values((array)$val)),
									];

                                    if($status) $arrDiff[$key]->difference = json_encode(array_values((array)$differ));
								}
							}
						}
						else{
							// If any element is removed but others are there
							if(count($val) > count($old[$key])){
								$differ = array_map('unserialize',array_diff(array_map('serialize',$val), array_map('serialize',$old[$key])));
								if($val != [] && !empty($val) && $val != null){
									$arrDiff[$key] = (object)[
										'oldvalue' => json_encode(array_values((array)$old[$key])),
										'newvalue' => json_encode(array_values((array)$val)),
									];
                                    if($status) $arrDiff[$key]->difference = json_encode(array_values((array)$differ));
								}
							}
							else{
								$differ = [];
								if($val != [] && !empty($val) && $val != null){
									$arrDiff[$key] = (object)[
										'oldvalue' => json_encode(array_values((array)$old[$key])),
										'newvalue' => json_encode(array_values((array)$val)),
									];
                                    if($status) $arrDiff[$key]->difference = json_encode(array_values((array)$differ));
                                    
								}
							}
						}
					}
					else{
						// If both are string
						$differ = array_map('unserialize',array_diff(array_map('serialize',(array)$val) , array_map('serialize' , (array)$old[$key]) ));
						if(!empty($differ) && $val != [] && !empty($val) && $val != null && $val != $old[$key] ){
							$arrDiff[$key] = (object)[
								'oldvalue' => json_encode(array_values((array)$old[$key])),
								'newvalue' => json_encode(array_values((array)$val)),
							];
                            if($status) $arrDiff[$key]->difference = json_encode(array_values((array)$differ));
						}
					}
				}
			} 
		}
		return $arrDiff; 
	}

	public static function removed_values_in_array($new , $old){
		if(!isset($arrDiff)){
			$arrDiff= array(); 
		}

		if(gettype($old) == "object" || gettype($old) == "array"){
			foreach($old as $key => $val) { 
				if(isset($new[$key])){ 
					if(is_array($val)){
						self::removed_values_in_array($new[$key] , $val);
						// $arrDiff = array_merge($arrDiff , self::removed_values_in_array($new[$key] , $val));
					}
				}
				else{
					if($val != null && $val != []){
						$arrDiff[$key] =  (object)[
							'oldvalue' => json_encode(array_values((array)$val)),
							'newvalue' => ''
						]; 
					}
				}
			} 
		}

		return $arrDiff;
	}

	private function error($errno, $errstr, $errfile, $errline){
		echo "<b>Custom error:</b> [$errno] $errstr<br>";
		echo " Error on line $errline in $errfile<br>";
		exit;
	}

	public static function convert_to_array($dataSet = array()){
		return json_decode(json_encode($dataSet) , true);
	}
}
?>