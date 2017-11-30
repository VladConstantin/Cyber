<?php

class Request {

	public function __construct() {
		$this->type = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get';
		/* 	$this->data = $_POST; */
		$verified = array();
		foreach ($_POST as $name=>$value) {
			if (is_string($value)) {
				if ($name != 'content') {
					$value = stripslashes($value);
					$value = str_replace('<','',$value);
					$value = str_replace('>','',$value);
					$value = str_replace('/','',$value);
					$value = str_replace("'",'',$value);
					$value = htmlspecialchars($value);
					$verified[$name] = $value;
				} else {
					$verified[$name] = $value;
				}
			} else {
				$verified[$name] = $value;
			}
		}
		$this->data = $verified;
	}

	public function is($type) {
		if (strtolower($this->type) == strtolower($type)) { return true; }
		return false;
	}

}

?>
