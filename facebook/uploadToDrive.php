<?php

session_start();
$user_id = $_SESSION['user_id'];
if(isset($_REQUEST['album_name'])){
	$album_name = $_REQUEST['album_name'];
	$_SESSION['tmp_album_name'] = $album_name;
}else{ 
	$album_name = ''; 
}
if(isset($_REQUEST['range'])){
	$range = $_REQUEST['range'];
	$_SESSION['tmp_range'] = $range;
}else{
	$range = '';
}
$user_name = $_SESSION['user_name'];
$uname = explode(' ',$user_name);
$user_name = $uname[0];

try{
	// url of file name
	$url_array = explode('?', 'http://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	$url = $url_array[0];
	//$url = 'http://spatel.club/facebook/user_albums.php';
	// load google liabraries
	require_once 'lib/Google/Google_Client.php';
	require_once 'lib/Google/contrib/Google_DriveService.php';
	
	// client initialization
	$client = new Google_Client();
	$client->setClientId('777486536644-bu7mr19lgl4r8qd33f3ropr2v4m6vbl8.apps.googleusercontent.com');
	$client->setClientSecret('wZUAYXay5xtG35BkSt6CZ31A');
	$client->setRedirectUri($url);
	$client->setScopes(array('https://www.googleapis.com/auth/drive'));
	
	// store generated token to session
	if (isset($_GET['code'])) {
		$_SESSION['GoogleTokenizer'] = $client->authenticate($_GET['code']);
		header('location:'.$url);exit;
	} elseif (!isset($_SESSION['GoogleTokenizer'])) {
		$client->authenticate();
	}

	$absPath = "user_space/{$user_id}/";
	$files = array();
	$dir = dir($absPath);
	while($_file = $dir->read()){
		if($_file != '.' && $_file != '..'){
			$files[] = $_file;
		}
	}
	$dir->close();

	/* if(!empty($_POST))
	{ */
		$client->setAccessToken($_SESSION['GoogleTokenizer']);
		$service = new Google_DriveService($client);
		
		// check if upload type is single or selected
		// create album list array if selected album upload
		if($_SESSION['tmp_range'] == 'single' or $_SESSION['tmp_range'] == 'selected'){
			$albumList = explode('|',$_SESSION['tmp_album_name']);
		}else if($_SESSION['tmp_range'] == 'allAlbums'){
			// if all albums should upload
			$userParDir = "user_space/{$user_id}/";
			
			//create zip file with all-albums.zip name
			$zip = new ZipArchive;
			$zip->open("{$userParDir}all-albums.zip");
			$zip->extractTo($userParDir);
			$zip->close();
			
			// create album list array
			$albumList = array();
			$dir = dir($userParDir.$user_id);
			while($_file = $dir->read()){
				if($_file != '.' && $_file != '..'){
					$albumList[] = $_file;
				}
			}
		}
		
		// check if master directory exists or not
		// if exists - store id of directory
		$par_flag_option = true;
		$fileList = $service->files->listFiles();
		foreach ($fileList['items'] as $item) {
			if($item['title'] == "facebook_".$user_name."_albums"){
				$par_flag_option = false;
				$parfolderId = $item['id'];
				break;
			}
		}
		
		// else create new master directory
		if($par_flag_option == true){
			$folder = new Google_DriveFile();
			$folder_mime = "application/vnd.google-apps.folder";
			$folder_name = "facebook_".$user_name."_albums";
			$folder->setTitle($folder_name);
			$folder->setMimeType($folder_mime);
			$masterDirectory = $service->files->insert($folder);
			$parfolderId  = $masterDirectory['id'];
		}
		
		foreach($albumList as $album){
			$absPath = "user_space/{$user_id}/{$album}";
			// check if directory or zip available
			// if directory not found
			if(!file_exists($absPath)){
				if(!file_exists("{$absPath}.zip")){
					// store validation string to show bootstrap alert
					$_SESSION['uploaded'] = 'album_not_found';
					// return to referer page
					header("location:/facebook/user_albums.php");
					exit;
				}else{
					// then extract zip with same album name
					$zip = new ZipArchive;
					$zip->open("{$absPath}.zip");
					$zip->extractTo($absPath);
					$zip->close();
				}
			}
			// list all files to upload
			$files= array();
			$dir = dir($absPath);
			while ($_file = $dir->read()) {
				if ($_file != '.' && $_file != '..') {
					$files[] = $_file;
				}
			}
			$dir->close();

			//$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$file = new Google_DriveFile();
			
			$flag_option = true;
			$fileList = $service->files->listFiles();
			$folderId = '';
			// check if directory already uploaded
			foreach ($fileList['items'] as $item) {
				if ($item['title'] == $album){
					$flag_option = false;
					$folderId = $item['id'];
					break;
				}
			}
			// if not then create new directory with album name to master directory
			if($flag_option == true){
				$folder = new Google_DriveFile();
				$folder_mime = "application/vnd.google-apps.folder";
				$folder->setTitle($album);
				$folder->setMimeType($folder_mime);
				if($parfolderId != null){
					$parent = new Google_ParentReference();
					$parent->setId($parfolderId);
					$folder->setParents(array($parent));
				}
				$masterDirectory = $service->files->insert($folder);

				$parentId  = $masterDirectory['id'];
				if ($parentId != null) {
					$parent = new Google_ParentReference();
					$parent->setId($parentId);
					$file->setParents(array($parent));
				}
			}
			// upload file to parent directory
			foreach ($files as $file_name) {
				$file_path = "user_space/{$_SESSION['user_id']}/{$album}/{$file_name}";
				//$mime_type = finfo_file($finfo, $file_path);
				$mime_type = 'image/jpeg';
				
				$file->setTitle($file_name);
				$file->setDescription('This is a '.$mime_type.' document');
				$file->setMimeType($mime_type);
				$service->files->insert(
					$file,
					array(
						'data' => file_get_contents($file_path),
						'mimeType' => $mime_type
					)
				);
			}
			//finfo_close($finfo);
			//header('location:http://spatel.club/facebook/user_albums.php');exit;
		}
		// store string to session to inform the user with success
		$_SESSION['uploaded'] = 'uploaded';
		// used on referer page for sharable link
		$_SESSION['parfolderId'] = $parfolderId;
		$_SESSION['tmp_album_name'] = '';
		$_SESSION['tmp_range'] = '';
		header("location:/facebook/user_albums.php");exit;
	// }
}catch(Exception $e){
	echo $e->getMessage();
}