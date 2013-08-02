<?php
//TODO - Let validation interact with controllers/dbos directly, as to let the controller handle error responses
class Validate {
	static $errors;
	var $rules;

    static function run($validation_rules) {
        foreach ($validation_rules as $name => $rules) {
			self::is_valid($name,$rules);
        }
        // All rules should now have been processed
        return count(self::$errors) == 0;
    }

    static function is_valid($name,$rules) {
    	if ( ($name == "auth") and ($rules === TRUE) ) { //Check for authentication
    		if ( ! Session::user() ) {
    			self::error("auth","auth");
			}
		}
        // Check if compulsory field has not been filled
        if (isset($rules['required']) && ($rules['required'] === true) && (trim(Request::r($name)) == '')) {
            self::$errors[$name] = self::error($name, 'required');
        }
        if (Request::r($name)) {
            // Field not set, and it's not compulsory
        }
        $value = Request::r($name);
        // If a regular expression is specified, check using that
        if (isset($rules['regex']) && !preg_match($rules['regex'], $value)) {
            self::$errors[$name] = self::error($name,'regex');
        }
        if (isset($rules['max']) && strlen($value) > $rules["max"]) {
        	self::$errors[$name] = self::error($name,'max');
		}
        if (isset($rules['min']) && strlen($value) < $rules["min"]) {
        	self::$errors[$name] = self::error($name,'min');
		}
        // If there is a mustmatch rule, check using that
        if (isset($rules['mustmatch']) && $value != Request::r($rules['mustmatch'])) {
            self::$errors[$name] = self::error($name, 'mustmatch');
        }
        // If there is a calback rule, run that function
        if (isset($rules['callback'])) {
            $callback = $rules['callback'];
            if (substr($callback, 0, 5) == 'this:') {
                // It's actually a method on this class
                $method = substr($callback, 5);
                if (!self::$method($value)) {
                    self::$errors[$name] = self::error($name, 'callback');
                }
			} else {
                // It's just a normal function
                if (!$callback($value)) {
                    self::$errors[$name] = self::error($name, 'callback');
                }
            }
        }
		return count(self::$errors) == 0;
	}

    static function error($name,$code) {
    	$errorMsg = NULL;
    	$name = ucwords($name);
    	switch($code) {
    		case "required" :
    			$errorMsg = sprintf( _("%s required") ,$name);
    		break;

    		case "regex" :
    			$errorMsg = sprintf( _("%s is invalid") ,$name);
    		break;

    		case "mustmatch" :
    			$errorMsg = sprintf( _("%s does not match") ,$name);
    		break;

    		case "max" :
    			$errorMsg = sprintf( _("%s exceeds maximium length") ,$name);
    		break;

       		case "min" :
    			$errorMsg = sprintf( _("%s is too short") ,$name);
	   			$errorMsg = "{$name} is too short";
    		break;

    		case "auth" :
    			$errorMsg = _("Authentication required");
    		break;

    		case "callback" :
    			//$errorMsg = "{$name} is invalid";
    		break;
		}

		if ($errorMsg) {
			Request::json( false, array("error"=>$errorMsg,"errors"=>array($name=>$errorMsg)));
		}
	}


	static function is_state($input,$field_name='state') {
		$state_list = array(
			'ALABAMA'=>"AL", 'ALASKA'=>"AK", 'AMERICAN SAMOA'=>"AS", 'ARIZONA'=>"AZ", 'ARKANSAS'=>"AR", 'CALIFORNIA'=>"CA",
			'COLORADO'=>"CO", 'CONNECTICUT'=>"CT", 'DELAWARE'=>"DE", 'DISTRICT OF COLUMBIA'=>"DC", "FEDERATED STATES OF MICRONESIA"=>"FM",
			'FLORIDA'=>"FL", 'GEORGIA'=>"GA", 'GUAM' => "GU", 'HAWAII'=>"HI", 'IDAHO'=>"ID", 'ILLINOIS'=>"IL", 'INDIANA'=>"IN", 'IOWA'=>"IA",
			'KANSAS'=>"KS", 'KENTUCKY'=>"KY", 'LOUISIANA'=>"LA", 'MAINE'=>"ME", 'MARSHALL ISLANDS'=>"MH", 'MARYLAND'=>"MD", 'MASSACHUSETTS'=>"MA",
			'MICHIGAN'=>"MI", 'MINNESOTA'=>"MN", 'MISSISSIPPI'=>"MS", 'MISSOURI'=>"MO", 'MONTANA'=>"MT", 'NEBRASKA'=>"NE", 'NEVADA'=>"NV",
			'NEW HAMPSHIRE'=>"NH", 'NEW JERSEY'=>"NJ", 'NEW MEXICO'=>"NM", 'NEW YORK'=>"NY", 'NORTH CAROLINA'=>"NC", 'NORTH DAKOTA'=>"ND",
			"NORTHERN MARIANA ISLANDS"=>"MP", 'OHIO'=>"OH", 'OKLAHOMA'=>"OK", 'OREGON'=>"OR", "PALAU"=>"PW", 'PENNSYLVANIA'=>"PA", 'RHODE ISLAND'=>"RI",
			'SOUTH CAROLINA'=>"SC", 'SOUTH DAKOTA'=>"SD", 'TENNESSEE'=>"TN", 'TEXAS'=>"TX", 'UTAH'=>"UT", 'VERMONT'=>"VT", 'VIRGIN ISLANDS' => "VI",
			'VIRGINIA'=>"VA", 'WASHINGTON'=>"WA", 'WEST VIRGINIA'=>"WV", 'WISCONSIN'=>"WI", 'WYOMING'=>"WY"
		);
		$passed = in_array(strtoupper($input),$state_list);
		if ( ! $passed ) {
			Request::json( false , array("error"=>_("Invalid State"),"errors"=>array($field_name=>_("Invalid State"))));
		} else {
			return true;
		}
		return false;
	}

	//Use as a validator when registering
	static function email_free($email,$field_name='email') {
			$email = R::findOne('users',' email = ? ',array($email));
			if ($email) {
				Request::json( false , array("error"=>_("Email address is taken")));
				return false;
			}
			return true;
	}

}
?>
