<?php

namespace Admin;

class Page extends AdminController {

	public function index($f3) {
		$pages = $this->Model->Pages->fetchAll();
		$f3->set('pages',$pages);
	}

	public function add($f3) {
		
		/* if($this->request->is('post')) {
			$pagename = strtolower(str_replace(" ","_",$this->request->data['title']));
			$this->Model->Pages->create($pagename);
		
			\StatusMessage::add('Page created successfully','success');
			return $f3->reroute('/admin/page/edit/' . $pagename);
		} */
		
		if($this->request->is('post')) {
			$pagename = strtolower(str_replace(" ","_",$this->request->data['title']));
			if(!empty($pagename)){
				$this->Model->Pages->create($pagename);
				\StatusMessage::add('Page created succesfully','success');
				return $f3->reroute('/admin/page/edit/' . $pagename);
			}else {
				\StatusMessage::add('You forgot to add a title','danger');
				return $f3->reroute('/admin/page');
			}
		}
	}

	public function edit($f3) {
		// Add validation to the user input to avoid SQL injection and XXS
		$pagename = $this->validateinp($f3->get('PARAMS.3'));
		// Add validation to the user input to make sure it is not empty
		if(empty($pagename)) {
			return $f3->reroute('/');
		}
		if ($this->request->is('post')) {
			$pages = $this->Model->Pages;
			$pages->title = $pagename;
			$pages->content = $this->request->data['content'];
			$pages->save();

			\StatusMessage::add('Page updated successfully','success');
			return $f3->reroute('/admin/page');
		}
	
		$pagetitle = ucfirst(str_replace("_"," ",str_ireplace(".html","",$pagename)));	
		$page = $this->Model->Pages->fetch($pagename);
		$f3->set('pagetitle',$pagetitle);
		$f3->set('page',$page);
	}

	public function delete($f3) {
		// Add validation to the user input to avoid SQL injection and XXS
		$pagename = $this->validateinp($f3->get('PARAMS.3'));
		// Add validation to the user input to make sure it is not empty
		if(empty($pagename)) {
			return $f3->reroute('/');
		}
		$this->Model->Pages->delete($pagename);	
		\StatusMessage::add('Page deleted successfully','success');
		return $f3->reroute('/admin/page');	
	}

}

?>
