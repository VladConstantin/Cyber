<?php

class User extends Controller {

	public function view($f3) {
		// Add validation to the user input to avoid SQL injection and XXS
		$userid = $this->validateinp($f3->get('PARAMS.3'));
		// Add validation to the user input to make sure it is not empty
		if(empty($userid)) {
			return $f3->reroute('/');
		}
		$u = $this->Model->Users->fetch($userid);
		// Add validation and notify the user if it is not a valid user id */
		if(empty($u)) {
			StatusMessage::add('Sorry, user does not exist','danger');
			return $f3->reroute('/');
		}
		$articles = $this->Model->Posts->fetchAll(array('user_id' => $userid));
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $userid));

		$f3->set('u',$u);
		$f3->set('articles',$articles);
		$f3->set('comments',$comments);
	}

	public function add($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);
			$check = $this->Model->Users->fetch(array('username' => $username));
			if (!empty($check)) {
				StatusMessage::add('User already exists','danger');
			} else if($password != $password2) {
				StatusMessage::add('Passwords must match','danger');
			} else {
				$user = $this->Model->Users;
				/* Added validation for user input */
				$user->copyfrom($user->validateinp('POST'));
				$user->created = mydate();
				$user->bio = '';
				$user->level = 1;
				$user->setPassword($password);
				if(empty($displayname)) {
					$user->displayname = $user->username;
				}

				//Set the users password
				$user->setPassword($user->password);

				$user->save();
				StatusMessage::add('Registration complete','success');
				return $f3->reroute('/user/login');
			}
		}
	}

	public function login($f3) {
		/** YOU MAY NOT CHANGE THIS FUNCTION - Make any changes in Auth->checkLogin, Auth->login and afterLogin() */
		if ($this->request->is('post')) {
			//Check for debug mode
			$settings = $this->Model->Settings;
			$debug = $settings->getSetting('debug');
			//Either allow log in with checked and approved login, or debug mode login
			list($username,$password) = array($this->request->data['username'],$this->request->data['password']);
			if (($this->Auth->checkLogin($username,$password,$this->request,$debug) && ($this->Auth->login($username,$password))) ||
					$debug && $this->Auth->debugLogin($username)) {

					$this->afterLogin($f3);

			} else {
				StatusMessage::add('Invalid username or password','danger');
			}
		}
	}

	/* Handle after logging in */
	private function afterLogin($f3) {
		StatusMessage::add('Logged in succesfully','success');
		$f3->reroute('/');

		//I removed this because people might people might see what they weren't supposed to
		//Redirect to where they came from
		/*if(isset($_GET['from'])) {
			$f3->reroute($_GET['from']);
		}else{
			$f3->reroute('/');
		}*/

	}

	public function logout($f3) {
		$this->Auth->logout();
		StatusMessage::add('Logged out succesfully','success');
		$f3->reroute('/');

		//Check CSRF in order to prevent users from geting logged out
		/*if($this->Csrf->check()){
			$this->Auth->logout();
			StatusMessage::add('CSRF detected','danger');
		}else{
			StatusMessage::add('Logged out succesfully','success');
			return $f3->reroute('/');
		}	*/
	}

	public function profile($f3) {
		//Check access of user
		$access = $this->Auth->user('level');

		//No access if not logged in
		if(empty($access)) { //FIX THIS
			\StatusMessage::add('Access Denied','danger');
			return $f3->reroute('/');
		}
		$id = $this->Auth->user('id');
		extract($this->request->data);
		$u = $this->Model->Users->fetch($id);
		$oldpass = $u->password;
		if($this->request->is('post')) {
			/* Added validation for user input */
			$u->copyfrom($u->validateinp('POST'));
			if(empty($u->password)) { $u->password = $oldpass; }

			//Handle avatar upload
			if(isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])) {
						$url = File::Upload($_FILES['avatar']);
						if($url != false){
							$u->avatar = $url;
						}
			} else if(isset($reset)) {
						$u->avatar = '';
			}

			$u->save();
			\StatusMessage::add('Profile updated successfully','success');
			return $f3->reroute('/user/profile');
		}

		$_POST = $u->cast();
		$f3->set('u',$u);
	}
	/* This was removed because a user should only be promoted by and admin. seems like a risk.
	public function promote($f3) {
		$id = $this->Auth->user('id');
		$u = $this->Model->Users->fetch($id);
		$u->level = 2;
		$u->save();
		return $f3->reroute('/');
	}*/

}
?>
