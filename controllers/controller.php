<?php

class Controller {

	protected $layout = 'default';

	public function __construct() {
		$f3=Base::instance();
		$this->f3 = $f3;

		// Connect to the database
		$this->db = new Database();
		$this->Model = new Model($this);

		//Load helpers
		$helpers = array('Auth');
		foreach($helpers as $helper) {
			$helperclass = $helper . "Helper";
			$this->$helper = new $helperclass($this);
		}
	}

	public function beforeRoute($f3) {
		$this->request = new Request();

		//Check user
		$this->Auth->resume();

		//Load settings
		$settings = Settings::getSettings();
		$settings['base'] = $f3->get('BASE');
		
		//Append debug mode to title
		if($settings['debug'] == 1) { $settings['name'] .= ' (Debug Mode)'; }

		$settings['path'] = $f3->get('PATH');
		$this->Settings = $settings;
		$f3->set('site',$settings);

		//Enable backwards compatability
		if($f3->get('PARAMS.*')) { $f3->set('PARAMS.3',$f3->get('PARAMS.*')); }

		//Extract request data
		extract($this->request->data);

		//This is unnecessary and a potential risk
		//Process before route code
		//if(isset($beforeCode)) {
		//	Settings::process($beforeCode);
		//}
	}

	public function afterRoute($f3) {	
		//Set page options
		$f3->set('title',isset($this->title) ? $this->title : get_class($this));

		//Prepare default menu	
		$f3->set('menu',$this->defaultMenu());

		//Setup user
		$f3->set('user',$this->Auth->user());

		//Create a CSRF token
		//$token = $this->Csrf->getToken();
		//$f3->set('token', $token);

		//Check for admin
		$admin = false;
		if(stripos($f3->get('PARAMS.0'),'admin') !== false) { $admin = true; }

		//Identify action
		$controller = get_class($this);
		if($f3->exists('PARAMS.action')) {
			$action = $f3->get('PARAMS.action');	
		} else {
			$action = 'index';
		}

		//Handle admin actions
		if ($admin) {
			$controller = str_ireplace("Admin\\","",$controller);
			$action = "admin_$action";
		}

		//Handle errors
		if ($controller == 'Error') {
			$action = $f3->get('ERROR.code');
		}

		//Handle custom view
		if(isset($this->action)) {
			$action = $this->action;
		}

		//Extract request data
		extract($this->request->data);

		//Generate content		
		$content = View::instance()->render("$controller/$action.htm");
		$f3->set('content',$content);

		//This is unnecessary and a potential risk
		//Process before route code
		//if(isset($afterCode)) {
		//	Settings::process($afterCode);
		//}

		//Render template
		echo View::instance()->render($this->layout . '.htm');
	}

	public function defaultMenu() {
		$menu = array(
			array('label' => 'Search', 'link' => 'blog/search'),
			array('label' => 'Contact', 'link' => 'contact'),
		);

		//Load pages
		$pages = $this->Model->Pages->fetchAll();
		foreach($pages as $pagetitle=>$page) {
			$pagename = str_ireplace(".html","",$page);
			$menu[] = array('label' => $pagetitle, 'link' => 'page/display/' . $pagename);
		}

		//Add admin menu items
		if ($this->Auth->user('level') > 1) {
			$menu[] = array('label' => 'Admin', 'link' => 'admin');
		}

		return $menu;
	}
	/** Validation function for user input */
	public function validateinp($text,$args=NULL) {
		if (!empty($text)) {
			if (is_string($text)) {
				$text = preg_replace("/[^a-zA-Z0-9_]/","",$text);
				$text = htmlspecialchars($text);
				}
			return $text;
		}
		else { return $text; }
	}
	/** Special validation function for comment */
	public function validateinp2($text,$args=NULL) {
		if (!empty($text)) {
			if (is_string($text)) {
				$text = str_replace('>','',$text);
				$text = str_replace('<','',$text);
				$text = str_replace('#','',$text);
				$text = htmlspecialchars($text);
				}
			return $text;
		}
		else { return $text; }
	}
				
				
}

?>
