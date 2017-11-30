<?php

	namespace Admin;

	class Category extends AdminController {

		public function index($f3) {
			$categories = $this->Model->Categories->fetchAll();
			$counts = array();
			foreach($categories as $category) {
				$counts[$category->id] = $this->Model->Post_Categories->fetchCount(array('category_id' => $category->id));
			}
			$f3->set('categories',$categories);
			$f3->set('counts',$counts);
		}

		public function add($f3) {
			if($this->request->is('post')) {
				$category = $this->Model->Categories;
				$category->title = $this->request->data['title'];
				$category->save();

				\StatusMessage::add('Category added successfully','success');
				return $f3->reroute('/admin/category');
			}
		}

		public function delete($f3) {
			// Add validation to the user input to avoid SQL injection and XXS
			$categoryid = $this->validateinp($f3->get('PARAMS.3'));
			// Add validation to the user input to make sure it is not empty
			if(empty($categoryid)) {
				return $f3->reroute('/');
			}
			$category = $this->Model->Categories->fetchById($categoryid);
			// Add validation and notify the user if it is not a valid category id 
			if(empty($category)) {
				\StatusMessage::add('Sorry, the category does not exist','danger');
				return $f3->reroute('/');
			}
			$category->erase();

			//Delete links		
			$links = $this->Model->Post_Categories->fetchAll(array('category_id' => $categoryid));
			foreach($links as $link) { $link->erase(); } 
	
			\StatusMessage::add('Category deleted successfully','success');
			return $f3->reroute('/admin/category');
		}

		public function edit($f3) {
			// Add validation to the user input to aavoid SQL injection and XXS
			$categoryid = $this->validateinp($f3->get('PARAMS.3'));
			// Add validation to the user input to make sure it is not empty
			if(empty($categoryid)) {
				return $f3->reroute('/');
			}
			$category = $this->Model->Categories->fetchById($categoryid);
			// Add validation and notify the user if it is not a valid post id 
			if(empty($category)) {
				\StatusMessage::add('Sorry, the category does not exist','danger');
				return $f3->reroute('/');
			}
			if($this->request->is('post')) {
				$category->title = $this->request->data['title'];
				$category->save();
				\StatusMessage::add('Category updated successfully','success');
				return $f3->reroute('/admin/category');
			}
			$f3->set('category',$category);
		}


	}

?>
