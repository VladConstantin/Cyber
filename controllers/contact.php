<?php

class Contact extends Controller {

	public function index($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);
			//Check for Captcha
			$secret = '6Lff_ToUAAAAAKKKC-o8RapPQ0xi_kb8ZYmPWafK';
			$response = null;
			$reCaptcha = new Web\Google\Recaptcha($secret);
			$response = $reCaptcha->verify($secret);
			if ($response != null && $response) {
				$from = "From: $from";
	
			mail($to,$subject,$message,$from);

			StatusMessage::add('Thank you for contacting us');
			return $f3->reroute('/');
			} else {
			StatusMessage::add('Please complete the Captcha');
			return $f3->reroute('/contact/');
			}
		}	
	}

}

?>
