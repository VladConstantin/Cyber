<?php

class Request {

	public function __construct() {
		$this->type = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get';
		/** check and validate each input from the user before pass them into the database */
		$verified = array();
		foreach ($_POST as $name=>$value) {
			if (is_string($value)) {
				$value = stripslashes($value);
				$value = str_replace('<','',$value);
				$value = str_replace('>','',$value);
				$value = str_replace('/','',$value);
				$value = str_replace("'",'',$value);
				$value = htmlspecialchars($value);
				$verified[$name] = $value;
				}
		}
		$this->data=$verified;
	}

	public function is($type) {
		if (strtolower($this->type) == strtolower($type)) { return true; }
		return false;
	}

}

?>
