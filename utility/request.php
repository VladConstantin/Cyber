<?php

class Request {

	public function __construct() {
		$this->type = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get';
		foreach ($_POST as $name=>$value) {
			if is_string($name) {htmlspecialchars($name);}
		}
		$this->data=$_POST;
	}

	public function is($type) {
		if (strtolower($this->type) == strtolower($type)) { return true; }
		return false;
	}

}

?>
