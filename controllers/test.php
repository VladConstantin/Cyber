<!DOCTYPE html>
<html lang="en">
	<head>
		<title>HOW TO INTEGRATE GOOGLE "NO CAPTCHA RECAPTCHA" ON YOUR WEBSITE </title>
	</head>
	
	<body>
	<?
	require_once "recaptchalib.php";
	$secret = '6Lff_ToUAAAAAKKKC-o8RapPQ0xi_kb8ZYmPWafK';
	$response = null;
	$reCaptcha = new ReCaptcha($secret);
	if ($_POST["g-recaptcha-response"]) {
	$response = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"],$_POST["g-recaptcha-response"]);
	}
	if ($response != null && $response->success) {
		echo "Thank you for completing the captcha";
	} else {
	?>
		<form action="" method="post">

		<label for="name" >Name:</label>
		<input name="name" required><br />
		
		<label for="email">Email:</label>
		<input name="email" type="email" required><br />
		
		<div class="g-recaptcha" dark-theme="dark" data-sitekey="6Lff_ToUAAAAAMQXWSrSSxky5Wu4c6_cSV4BV1tL"></div>
		
		<input type="submit" value="Submit" />
		</form>
	<? } ?>

	<script src='https://www.google.com/recaptcha/api.js'></script>
	
	</body>
</html>

<?
/*
//ReCaptcha
			$secret = '6Lff_ToUAAAAAKKKC-o8RapPQ0xi_kb8ZYmPWafK';
			$response = null;
			$reCaptcha = new Web\Google\Recaptcha($secret);
			$response = $reCaptcha->verify($secret);
			if ($response != null && $response->success) {}
*/
?>
