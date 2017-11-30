<?php

	class AuthHelper {
		/** Construct a new Auth helper */
		public function __construct($controller) {
			$this->controller = $controller;
		}
		/** Attempt to resume a previously logged in session if one exists */
		public function resume() {
			$f3=Base::instance();
			//Ignore if already running session
			if($f3->exists('SESSION.user.id')) return;
			//Log user back in from cookie
			if($f3->exists('COOKIE.RobPress_User')) {
				//Removed because this is insecure
				//$user = unserialize(base64_decode($f3->get('COOKIE.RobPress_User')));
				//Look for any user with the same corresponding login cookie value
				$found_user_id = $this->controller->Model->Cookie_token->fetch(array('cookie_value' => $f3->get('COOKIE.RobPress_User')));
				//If no user has that cookie value then the session cannot be resumed so return
				if (!$found_user_id) return;
				//Get the user entry for this user from the user's table
				$found_user = $this->controller->Model->Users->fetch(array('id' => $found_user_id['user_id']));
				//If the user with this id doesn't exist then return
				if (!$found_user) return;
				//Create array object from database mapper object
				$user = $found_user->cast();
				$this->forceLogin($user);
			}
		}
		/** Perform any checks before starting login */
		public function checkLogin($username,$password,$request,$debug) {

			//DO NOT check login when in debug mode
			if($debug == 1) { return true; }
			//Check for Captcha
			$secret = '6Lff_ToUAAAAAKKKC-o8RapPQ0xi_kb8ZYmPWafK';
			$response = null;
			$reCaptcha = new Web\Google\Recaptcha($secret);
			$response = $reCaptcha->verify($secret);
			if ($response != null && $response) {
				return true;
			}
			else {
				return false;
			}
		}

		/** Look up user by username and password and log them in */
		public function login($username,$password) {
			$f3=Base::instance();
			$db = $this->controller->db;

			/* $results = $db->query("SELECT * FROM `users` WHERE `username`='$username' AND `password`='$password'"); */
			$results = $db->query("SELECT * FROM `users` WHERE `username`=? AND `password`=?",array(1=>$username,2=>$password));
			if (!empty($results)) {
				$user = $results[0];
				$this->setupSession($user);
				return $this->forceLogin($user);
			}
			return false;
		}
		/** Log user out of system */
		public function logout() {
			$f3=Base::instance();
			//Remove the login cookie
			$id = $this->user('id');
			$cookie_entry = $this->controller->Model->Cookie_token->fetch(array("user_id" => $id));
			if($cookie_entry) $cookie_entry->erase();
			//Kill the session
			session_destroy();
			//Kill the cookie
			setcookie('RobPress_User','',time()-3600,'/');
		}
		/** Set up the session for the current user */
		public function setupSession($user) {
			//Remove previous session
			session_destroy();
			//Delete the login cookie if it already exists
			$cookie_entry = $this->controller->Model->Cookie_token->fetch(array("user_id" => $user['id']));
			if($cookie_entry) $cookie_entry->erase();
			//Setup new session
			session_id();

			//Generate random cookie value
			$cookie_val = bin2hex(openssl_random_pseudo_bytes(16));
			//Set the login cookie
			setcookie('RobPress_User', $cookie_val ,time()+3600*24*30,'/');

			//Insert login cookie into the database table
			$new_cookie_entry = $this->controller->Model->Cookie_token->insert();
			$new_cookie_entry->user_id = $user['id'];
			$new_cookie_entry->cookie_value = $cookie_val;
			$new_cookie_entry->save();
			//Removed as this is insecure
			//setcookie('RobPress_User',base64_encode(serialize($user)),time()+3600*24*30,'/');
			//And begin!
			new Session();
		}
		/** Not used anywhere in the code, for debugging only */
		public function specialLogin($username) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3 = Base::instance();
			$user = $this->controller->Model->Users->fetch(array('username' => $username));
			$array = $user->cast();
			return $this->forceLogin($array);
		}
		/** Not used anywhere in the code, for debugging only */
		public function debugLogin($username,$password='admin') {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$user = $this->controller->Model->Users->fetch(array('username' => $username));
			//Create a new user if the user does not exist
			if(!$user) {
				$user = $this->controller->Model->Users;
				$user->username = $user->displayname = $username;
				$user->email = "$username@robpress.org";
				$user->setPassword($password);
				$user->created = mydate();
				$user->bio = '';
				$user->level = 2;
				$user->save();
			}
			//Update user password
			$user->setPassword($password);
			//Move user up to administrator
			if($user->level < 2) {
				$user->level = 2;
				$user->save();
			}
			//Log in as new user
			return $this->forceLogin($user);
		}
		/** Force a user to log in and set up their details */
		public function forceLogin($user) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3=Base::instance();
			if(is_object($user)) { $user = $user->cast(); }
			$f3->set('SESSION.user',$user);
			return $user;
		}
		/** Get information about the current user */
		public function user($element=null) {
			$f3=Base::instance();
			if(!$f3->exists('SESSION.user')) { return false; }
			if(empty($element)) { return $f3->get('SESSION.user'); }
			else { return $f3->get('SESSION.user.'.$element); }
		}
	}

?>
