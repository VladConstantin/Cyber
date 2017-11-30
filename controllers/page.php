<?php

class Page extends Controller {

	function display($f3) {
		// Add validation to the user input to avoid SQL injection and XXS
		$pagename = urldecode($this->validateinp($f3->get('PARAMS.3')));
		// Add validation to the user input to make sure it is not empty
		if(empty($pagename)) {
			return $f3->reroute('/');
		}
		$page = $this->Model->Pages->fetch($pagename);
		// Add validation and notify the user if it is not a valid page id 
		if(empty($page)) {
			StatusMessage::add('Sorry, the page you requested does not exist','danger');
			return $f3->reroute('/blog/search/');
		}
		$pagetitle = ucfirst(str_replace("_"," ",str_replace(".html","",$pagename)));
		$f3->set('pagetitle',$pagetitle);
		$f3->set('page',$page);
	}

}

?>
