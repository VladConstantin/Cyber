<?php
class User extends Controller {

	public function view($f3) {
		$userid = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($userid);

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
				$user->copyfrom('POST');
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
			if (
				($this->Auth->checkLogin($username,$password,$this->request,$debug) && ($this->Auth->login($username,$password))) ||
				($debug && $this->Auth->debugLogin($username))) {

					$this->afterLogin($f3);

			} else {
				StatusMessage::add('Invalid username or password','danger');
			}
		}
	}

	/* Handle after logging in */
	private function afterLogin($f3) {
				StatusMessage::add('Logged in succesfully','success');

				//Redirect to where they came from
				if(isset($_GET['from'])) {
					$f3->reroute($_GET['from']);
				} else {
					$f3->reroute('/');
				}
	}

	public function logout($f3) {
		$this->Auth->logout();
		StatusMessage::add('Logged out succesfully','success');
		$f3->reroute('/');
	}


	public function profile($f3) {
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

				$uploadAllowed = 1;

				//checks the mime type
				$fileType = $_FILES['avatar']['type'];
				$mimes = array('jpg|jpeg' => 'image/jpeg' , 'png' => 'image/png');
				if(!in_array($fileType, $mimes)){
					$uploadAllowed=0;
					\StatusMessage::add('Only files of type jpg, jpeg or png are allowed.','danger');
				}

				//checks the file extension
				if($uploadAllowed==1){
					$filePath = $_FILES['avatar']['name'];
					$fileExt = pathinfo($filePath , PATHINFO_EXTENSION);
					if($fileExt != "jpg" && $fileExt != "jpeg" && $fileExt != "png"){
					 	$uploadAllowed = 0;
					 	\StatusMessage::add(".$fileExt files are not allowed. Only files with the extension .jpg, .jpeg or .png are allowed.",'danger');
					}
				}

				//checks for double file extension
				if($uploadAllowed==1){
					$fileName = pathinfo($_FILES['avatar']['name'], PATHINFO_BASENAME);
					$fileArray = explode(".", $fileName);
					if(count($fileArray) !== 2){
						$uploadAllowed = 0;
						\StatusMessage::add("No periods are allowed in the file name.",'danger');
					}
				}

				if($uploadAllowed==1){
					$url = File::Upload($_FILES['avatar']);
					$u->avatar = $url;
					\StatusMessage::add('Profile updated successfully','success');
				}

			} else if(isset($reset)) {
				$u->avatar = '';
				\StatusMessage::add('Profile updated successfully','success');
			}

			$u->save();
			return $f3->reroute('/user/profile');
		}

		$_POST = $u->cast();
		$f3->set('u',$u);
	}

	public function promote($f3) {
		$id = $this->Auth->user('id');
		$u = $this->Model->Users->fetch($id);
		$u->level = 2;
		$u->save();
		return $f3->reroute('/');
	}

}
?>
