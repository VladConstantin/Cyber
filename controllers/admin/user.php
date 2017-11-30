<?php

namespace Admin;

class User extends AdminController {

	public function index($f3) {
		$users = $this->Model->Users->fetchAll();
		$f3->set('users',$users);
	}

	public function edit($f3) {	
		// Add validation to the user input to avoid SQL injection and XXS
		$id = $this->validateinp($f3->get('PARAMS.3'));
		// Add validation to the user input to make sure it is not empty
		if(empty($id)) {
			return $f3->reroute('/');
		}
		$u = $this->Model->Users->fetch($id);
		// Add validation and notify the user if it is not a valid comment id 
		if(empty($u)) {
			\StatusMessage::add('Sorry, user does not exist','danger');
			return $f3->reroute('/');
		}
		if($this->request->is('post')) {
			/* Added validation for user input */
			$u->copyfrom($u->validateinp('POST'));
			$u->setPassword($this->request->data['password']);
			$u->save();
			\StatusMessage::add('User updated successfully','success');
			return $f3->reroute('/admin/user');
		}			
		$_POST = $u->cast();
		$f3->set('u',$u);
	}

	public function delete($f3) {		
		
		/*$id = $this->validateinp($f3->get('PARAMS.3'));
		$u = $this->Model->Users->fetch($id);
		
		if($id == $this->Auth->user('id')) {
			\StatusMessage::add('You cannot remove yourself','danger');
			return $f3->reroute('/admin/user');
		}*/
		
		//this is a CSRF check
		if($this->CSRF->check()){
			// Add validation to the user input to avoid SQL injection and XXS
			$id = $this->validateinp($f3->get('PARAMS.3'));
			// Add validation to the user input to make sure it is not empty
			if(empty($id)) {
				return $f3->reroute('/');
			}
			$u = $this->Model->Users->fetch($id);
			// Add validation and notify the user if it is not a valid user id 
			if(empty($u)) {
				\StatusMessage::add('Sorry, user does not exist','danger');
				return $f3->reroute('/');
			}
			if($id == $this->Auth->user('id')) {
				\StatusMessage::add('You cannot remove yourself','danger');
				return $f3->reroute('/admin/user');
			}
		}

		//Remove all posts and comments
		$posts = $this->Model->Posts->fetchAll(array('user_id' => $id));
		foreach($posts as $post) {
			$post_categories = $this->Model->Post_Categories->fetchAll(array('post_id' => $post->id));
			foreach($post_categories as $cat) {
				$cat->erase();
			}
			$post->erase();
		}
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $id));
		foreach($comments as $comment) {
			$comment->erase();
		}
		$u->erase();

		\StatusMessage::add('User has been removed','success');
		return $f3->reroute('/admin/user');
	}


}

?>
