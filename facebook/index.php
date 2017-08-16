<html>
	<head>
		<!-- load source files -->
		<?php include 'lib/source.php';?>
		<title>Homepage</title>
	</head>
	<body>
		<!-- alternate facebook button
			<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>
			<div id="fb-root"></div>
		-->
		<div class="container home-page-container">
			<div class="table-responsive">
				<table class="table home-page-table">
					<tr>
						<td class="home-page-tbl-header">Log in with Facebook</td>
					</tr>
					<tr>
						<td>
							<ul class="table-ul-home">
								<li class="fb-int-li">
									<i class="fa fa-arrow-right fa-index" aria-hidden="true"></i>&nbsp;Facebook integration
								</li>
								<li class="google-int-li">
									<i class="fa fa-arrow-right fa-index" aria-hidden="true"></i>&nbsp;Google Drive integration
								</li>
							</ul>
							<ul class="table-ul-home" style="padding-bottom: 4px;">
								<li>
									<i class="fa fa-minus-circle fa-index" aria-hidden="true"></i>&nbsp;The App is still in development mode. so it may not properly work unless verified &nbsp;
									<i class="fa fa-user fa-index" aria-hidden="true"></i>&nbsp;Testing Account. Thank you.
								</li>
							</ul>
							<form method="post" action="user_albums.php">
								<!-- login with facebook button -->
								<fb:login-button scope="public_profile,email" onlogin="checkLoginState();">
									Log in with Facebook
								</fb:login-button> <br>
								<!-- hidden variables to store and used by redirecting page -->
								<input type="hidden" id="accessToken" name="accessToken" value="">
								<input type="hidden" id="user_id" name="user_id" value="">
								<input type="hidden" id="user_name" name="user_name" value="">
								<input type="submit" id="submitForm" class="btn btn-primary" name="submit" value="submit">
							</form>
						</td>
					</tr>
					<tr> <!-- display message to user -->
						<td>
							<span id="status" class="user-status"></span>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</body>
</html>

<!-- facebook js sdk -->
<script>
// check user login status
function statusChangeCallback(response) {
	console.log(response);
	if(response.status === 'connected'){
		// if connected get accessToken from response
		// and store in hidden variables
		var accessToken = response.authResponse.accessToken;
		jQuery('#accessToken').val(accessToken);
		jQuery('#submitForm').removeAttr('disabled');
		console.log(accessToken);
		// generate appropriate message to user
		validateAPI();
	} else {
		// disable submit button until user logged in
		document.getElementById('status').innerHTML = 'Please log ' + 'into this app.';
		jQuery('#submitForm').attr('disabled','disabled');
	}
}
// check login status when hit on facebook button
function checkLoginState() {
	FB.getLoginStatus(function(response) {
	statusChangeCallback(response);
	});
}
//initialize and integrate with Facebook
window.fbAsyncInit = function() {
	FB.init({
		appId      : '203203223544899',
		cookie     : true,
		xfbml      : true,
		version    : 'v2.10'
	});
	FB.getLoginStatus(function(response) {
		statusChangeCallback(response);
	});
};
// load the facebook js sdk
(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/en_US/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

function validateAPI() {
	FB.api('/me', function(response) {
		// store user id and user name to hidden variables
		console.log('Successful login for: ' + response.name);
		document.getElementById('status').innerHTML = 'Thanks for logging in, ' + response.name + '!';
		console.log(response);
		jQuery('#user_id').val(response.id);
		jQuery('#user_name').val(response.name);
	});
}
</script>
