<?php 
	session_start();
	error_reporting(0);
	ini_set('display_errors','0');
	if(!isset($_POST) && !isset($_SESSION)){
		header('location:../facebook/');
	}
	
	if(isset($_POST['accessToken'])){
		$user_id = $_POST['user_id'];
		$user_name = $_POST['user_name'];
		$_SESSION['user_id'] = $_POST['user_id'];
		$_SESSION['user_name'] = $_POST['user_name'];
		$_SESSION['accessToken'] = $_POST['accessToken'];
		$accessToken = $_POST['accessToken'];
	}else{
		$user_id = $_SESSION['user_id'];
		$user_name = $_SESSION['user_name'];
		$accessToken = $_SESSION['accessToken'];
	}
	
	if($accessToken == ''){
		header('location:/facebook/');
	}
	
	$json_link = "https://graph.facebook.com/v2.10/{$user_id}?fields=id,name,albums&access_token={$accessToken}";
		
	$json = file_get_contents($json_link);	 
	$obj = json_decode($json);
	
	/* echo '<pre>';
	print_r($obj);
	exit; */
	if($obj != ''){
		$username = $obj->name;
		$albums = $obj->albums->data;
	}
?>

<html>
	<head>
		<title><?php echo $user_name."' albums";?></title>
		<?php require "lib/source.php";?>
		<?php require "lib/script.js";?>
		<meta name="viewport" content="width=device-width" />
	</head>
	<body>
		<div id="loading" style="display:none;">
			<img id="loading-image" src="lib/ajax-loader.gif" alt="Loading..." />
		</div>
		
		<?php if($accessToken != '' && $albums != ''){ ?>
			<div class="container user-album-container">
				<!-- <div class="google-signin-btn">
					<div class="g-signin2" data-onsuccess="onSignIn"></div>
				</div> -->
				
				<div id="user_info">
					<?php if($_SESSION['uploaded'] == 'uploaded'): ?>
					
						<?php $driveLink = "https://drive.google.com/open"; ?>
						<?php $sharableLink = $_SESSION['parfolderId']; ?>
					
						<div class="alert alert-success" style="padding:9px 0 9px 12px;">
							<i class="fa fa-envelope" aria-hidden="true"></i>
							<b>success!</b> uploaded to drive successfully.
							<span class="attch-header">
								<i class="fa fa-folder-open" aria-hidden="true"></i>
								<a href="<?php echo $driveLink.'?id='.$sharableLink ?>" target="_blank">sharable Link</a>
							</span>
						</div>
					<?php elseif($_SESSION['uploaded'] == 'album_not_found'): ?>
					
						<div class="alert alert-info" style="padding:9px 0 9px 12px;">
							<i class="fa fa-envelope" aria-hidden="true"></i>
							<b>Album not found!</b> Please download the album.
						</div>
					<?php endif;?>
					
					<?php if($_SESSION['uploaded'] != ''){
						unset($_SESSION['uploaded']);
					} ?>
					<?php if($_SESSION['parfolderId'] != ''){
						unset($_SESSION['parfolderId']);
					} ?>
				</div>
				
				<div class="fb-username">
					<span class="fb-user"><?php echo 'Hello '.$username; ?></span>
				</div>
				<form method="post">
					<div class="wrapper slider">
						<div class="albums">
							<?php foreach($albums as $data){ ?>
								<div>
									<a href='javascript:void(0)' style='text-decoration: none;' onclick='getAlbumPhotoes(<?php echo $data->id ?>);'>
										<span class='fb-album-name'><?php echo $data->name ?></span>
									</a>
									<img class='img-responsive-album img-thumbnail' src='https://graph.facebook.com/v2.3/<?php echo $data->id ?>/picture?access_token=<?php echo $accessToken?>' alt='' onclick='getAlbumPhotoes(<?php echo $data->id ?>);'>
									<input type="checkbox" class="download-checkbox" onchange="getSelectedValues(<?php echo $data->id ?>);" style="margin: -3px 0 0 0;">
									<span id="download<?php echo $data->id ?>">
										<a href="javascript:void(0)" class="dw-hover" onclick="downloadAlbum(<?php echo $user_id.','.$data->id ?>,'<?php echo $data->name ?>')" style="text-decoration:none;">
											<i class="fa fa-cloud-download cloud-icons" aria-hidden="true"></i>
										</a>
									</span>
									<input type="checkbox" class="upload-checkbox" onchange="getUploadSelectedValues('<?php echo $data->name ?>');">
									<span id="upload<?php echo $data->id ?>" class="moveToDrive">
										<a href="javascript:void(0)" class="up-hover" onclick="location.href='uploadToDrive.php?album_name=<?php echo $data->name; ?>&range=single'" style="text-decoration:none;">
											<i class="fa fa-cloud-upload cloud-icons cloud-upload" aria-hidden="true" style="margin:-3px 2px 0 0;"></i>
										</a>
									</span>
									<!-- moveToDrive('<?php //echo $data->name ?>') -->
								</div>
							<?php } ?>
						</div>
					</div>
				</form>
				<input type="hidden" id="dSelect" value="">
				<input type="hidden" id="uSelect" value="">
				<div class="data-transfer">
					<div class="col-sm-6 data-trnf-div">
						<div class="">
							<button id="download_all" onclick="downloadSelected()" class="data-trnf-btn btn btn-primary">Download Selected Albums</button>
							<span id="dSelected"></span>
						</div>
						<div class="">
							<button id="download_seletced_all" onclick="downloadAllAlbums()" class="data-trnf-btn btn btn-primary">Download All Albums</button>
							<span id="dSelectedAll"></span>
						</div>
					</div>
					<div class="col-sm-6 data-trnf-div">
						<div class="">
							<button id="upload_seletced_all" onclick="uploadSelectedAll()" class="data-trnf-btn btn btn-primary">Upload Selected Albums</button>
							<span id="dSelectedAll"></span>
						</div>
						<div class="">
							<button id="upload_all" onclick="uploadAllAlbums()" class="data-trnf-btn btn btn-primary">Upload All Albums</button>
							<span id="dSelectedAll"></span>
						</div>
					</div>
					<!--
					<p class="infotouser">
						Note :- Please Click on <b class="user-info-gsignin">Sign in</b> button on <b class="user-info-gsignin">top-right</b> to Move Albums to your <b class="user-info-gsignin">Google Drive</b>...
					</p> -->
				</div>
				
				<!-- load photoes of selected album -->
				<data id="albumFeeds"></div>
			</div>
			
		<?php } else { ?>
			<script>
				alert('Access token expired..');
				location.href='../facebook/';
			</script>
		<?php } ?>
		
	</body>
</html>