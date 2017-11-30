<?php
class Blog extends Controller {
	
	public function index($f3) {	
		if ($f3->exists('PARAMS.3')) {
			// Validate the query parameter from the URL
			$categoryid = $this->validateinp($f3->get('PARAMS.3'));
			$category = $this->Model->Categories->fetch($categoryid);
			// Add validation and notify the user if it is not a valid category id
			if(empty($category)) {
				StatusMessage::add('Sorry, the category you requested does not exist','danger');
				return $f3->reroute('/');
			}
			$postlist = array_values($this->Model->Post_Categories->fetchList(array('id','post_id'),array('category_id' => $categoryid)));
			$posts = $this->Model->Posts->fetchAll(array('id' => $postlist, 'published' => 'IS NOT NULL'),array('order' => 'published DESC'));
			$f3->set('category',$category);
		} else {
			$posts = $this->Model->Posts->fetchPublished();
		}

		$blogs = $this->Model->map($posts,'user_id','Users');
		$blogs = $this->Model->map($posts,array('post_id','Post_Categories','category_id'),'Categories',false,$blogs);
		$f3->set('blogs',$blogs);
	}

	public function view($f3) {
		// Validate the query parameter from the URL
		$id = $this->validateinp($f3->get('PARAMS.3'));
		// Add validation to the user input to make sure it is not empty
		if(empty($id)) {
			return $f3->reroute('/');
		}
		$post = $this->Model->Posts->fetch($id);
		// Add validation and notify the user if it is not a valid post id
		if(empty($post)) {
			StatusMessage::add('Sorry, the post you requested does not exist','danger');
			return $f3->reroute('/');
		}
		
		$blog = $this->Model->map($post,'user_id','Users');
		$blog = $this->Model->map($post,array('post_id','Post_Categories','category_id'),'Categories',false,$blog);

		$comments = $this->Model->Comments->fetchAll(array('blog_id' => $id));
		$allcomments = $this->Model->map($comments,'user_id','Users');

		$f3->set('comments',$allcomments);
		$f3->set('blog',$blog);		
	}

	public function reset($f3) {
		//Check access of user
		$access = $this->Auth->user('level');        

		//No access if not logged in
		if(empty($access)) { //FIX THIS
			\StatusMessage::add('Access Denied','danger');
			return $f3->reroute('/');
		}
		
		//Check if user has level 2 - preventive mesure for unwanted guests
		if($access < 2){
			\StatusMessage::add('Access Denied','danger');
			return $f3->reroute('/');
		}
		$allposts = $this->Model->Posts->fetchAll();
		$allcategories = $this->Model->Categories->fetchAll();
		$allcomments = $this->Model->Comments->fetchAll();
		$allmaps = $this->Model->Post_Categories->fetchAll();
		foreach($allposts as $post) $post->erase();
		foreach($allcategories as $cat) $cat->erase();
		foreach($allcomments as $com) $com->erase();
		foreach($allmaps as $map) $map->erase();
		StatusMessage::add('Blog has been reset');
		return $f3->reroute('/');
	}

	public function comment($f3) {
		// Validate the query parameter from the URL 
		$id = $this->validateinp($f3->get('PARAMS.3'));
		// Add validation to the user input to make sure it is not empty
		if(empty($id)) {
			return $f3->reroute('/');
		}
		$post = $this->Model->Posts->fetch($id);
		// Add validation and notify the user if it is not a valid post id 
		if(empty($post)) {
			StatusMessage::add('Sorry, the post you requested does not exist','danger');
			return $f3->reroute('/');
		}
		if($this->request->is('post')) {
			$comment = $this->Model->Comments;
			// Added validation for user input
			$comment->copyfrom($comment->validateinp('POST'));
			$comment->blog_id = $id;
			$comment->created = mydate();

			//Moderation of comments
			if (!empty($this->Settings['moderate']) && $this->Auth->user('level') < 2) {
				$comment->moderated = 0;
			} else {
				$comment->moderated = 1;
			}

			//Default subject
			if(empty($this->request->data['subject'])) {
				$comment->subject = 'RE: ' . $post->title;
			}

			$comment->save();

			//Redirect
			if($comment->moderated == 0) {
				StatusMessage::add('Your comment has been submitted for moderation and will appear once it has been approved','success');
			} else {
				StatusMessage::add('Your comment has been posted','success');
			}
			return $f3->reroute('/blog/view/' . $id);
		}
	}

	public function moderate($f3) {
		//Check access of user
		$access = $this->Auth->user('level');        

		//No access if not logged in
		if(empty($access)) { //FIX THIS
			\StatusMessage::add('Access Denied','danger');
			return $f3->reroute('/');
		}
		
		//Check if user has level 2 - preventive mesure for unwanted guests
		if($access < 2){
			\StatusMessage::add('Access Denied','danger');
			return $f3->reroute('/');
		}
		// Validate the query parameter from the URL
		list($id,$option) = explode("/",$this->validateinp2($f3->get('PARAMS.3')));
		$comments = $this->Model->Comments;
		// Add validation to the user input to make sure it is not empty
		if(empty($id)) {
			return $f3->reroute('/');
		}
		// Add validation to the user input to make sure it is not empty
		if(empty($option)) {
			return $f3->reroute('/');
		}
		$comment = $comments->fetch($id);
		// Add validation and notify the user if it is not a valid comment id 
		if(empty($comment)) {
			StatusMessage::add('Sorry, the comment you requested does not exist','danger');
			return $f3->reroute('/blog/comment/');
		}

		$post_id = $comment->blog_id;
		//Approve
		if ($option == 1) {
			$comment->moderated = 1;
			$comment->save();
		} else {
		//Deny
			$comment->erase();
		}
		StatusMessage::add('The comment has been moderated');
		$f3->reroute('/blog/view/' . $comment->blog_id);
	}


	public function search($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);
			if(empty($search)) {
				return $f3->reroute('/blog/search/');
			}
			$f3->set('search',$search); //FIX THiS

			//Get search results
			$search = str_replace("*","%",$search); //Allow * as wildcard
			$ids = $this->db->connection->exec("SELECT id FROM `posts` WHERE `title` LIKE \"%$search%\" OR `content` LIKE '%$search%'");
			$ids = Hash::extract($ids,'{n}.id');
			if(empty($ids)) {
				StatusMessage::add('No search results found for ' . $search); 
				return $f3->reroute('/blog/search');
			}

			//Load associated data
			$posts = $this->Model->Posts->fetchAll(array('id' => $ids));
			$blogs = $this->Model->map($posts,'user_id','Users');
			$blogs = $this->Model->map($posts,array('post_id','Post_Categories','category_id'),'Categories',false,$blogs);

			$f3->set('blogs',$blogs);
			$this->action = 'results';	
		}
	}
}
?>
