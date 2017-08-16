<?php

include 'lib/create_zip_file.php';
ini_set('max_execution_time',500);

session_start();
$user_id = $_SESSION['user_id'];
$accessToken = $_SESSION['accessToken'];
if(isset($_REQUEST['album_id'])){ $album_ids = $_REQUEST['album_id']; } else { $album_ids = ''; }
$allAlbums = $_REQUEST['allAlbums'];
if(isset($_REQUEST['type'])){
	$type = $_REQUEST['type'];
}else{
	$type = '';
}

# allAlbums = true -> download all the albums
# allAlbums = false -> download selected albums

# type = downloadScript -> when clicked on download all album button
# type = downloadForUploadScript -> when clicked on upload all album button
# else type = download selected albums


if($allAlbums == 'true'){
	$albumLabel = "user_space/{$user_id}/all-albums.zip";
	$json_link = "https://graph.facebook.com/v2.10/{$user_id}?fields=id,name,albums&access_token={$accessToken}";
	$json = file_get_contents($json_link);	 
	$obj = json_decode($json);
	if($obj != ''){
		$username = $obj->name;
		$albums = $obj->albums->data;
	}
	
	// store all album ids to be download
	foreach($albums as $data){
		$albumIds[] = $data->id;
	}
}else{
	// if selected albums tobe download 
	$albumLabel = "user_space/{$user_id}/selected-albums.zip";
	// create array from comma sepreated album id string
	$albumIds = explode(',',$album_ids);
}

try{
	// global dir list of user to prevent re-download
	$GlobDirs = scandir("user_space/{$user_id}/");
	$data = array();
	foreach($albumIds as $album_id){
		$url = "https://graph.facebook.com/v2.3/{$album_id}?fields=name,photos&access_token={$accessToken}";

		$json = file_get_contents($url);
		$obj = json_decode($json);
		
		if($accessToken != '' && $obj != ''){
			$flag_option = true;
			$album_name = $obj->name;
			$albumNames[] = $album_name;
			foreach($GlobDirs as $_globDirs){
				if($_globDirs == $obj->name){
					$flag_option = false;
					break; // breaks if album is already downloaded
				}
			}
			if($flag_option == false){
				continue; // skip current album processing if already downloaded
			}
			$albums = $obj->photos->data;
		}

		// creates new user directory is not available
		if(!file_exists('user_space')){
			mkdir('user_space');
		}

		$dir = 'user_space/'.$user_id.'/';
		if(!file_exists($dir)){
			mkdir($dir);
		}
		
		$directory = $dir.'/'.$album_name.'/';
		if(!file_exists($directory)){
			mkdir($directory);
		}

		try{
			// donwload albums
			foreach($albums as $pics){
				$url = "https://graph.facebook.com/v2.10/{$pics->id}/picture?access_token={$accessToken}";
				$arr = explode("/",$url);
				$img_file = $pics->id.'.jpg';
				$data = file_get_contents($url);
				$fp = fopen($directory.$img_file,"w");
				fwrite($fp,$data);
				fclose($fp);
			}
		}catch(Exception $e){
			$data['success'] = 'false';
		}
	}

	// create file array
	$files = array();
	if($allAlbums == 'true'){
		// if all albums should be download
		// store all the directory files
		$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("user_space/{$user_id}"));
		foreach ($rii as $file) {
			if ($file->isDir()){ 
				continue;
			}
			// store allowed files with their absolute path
			$fileType = pathinfo($file->getPathname());
			if($fileType['extension'] == 'jpg')
				$files[] = $file->getPathname(); 
		}
	}else if($allAlbums == 'false'){
		// if selected albums should be download
		// store selected album directory files
		$albumNames = array_unique($albumNames);
		foreach($albumNames as $_albumName){
			$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("user_space/{$user_id}/{$_albumName}"));
			foreach ($rii as $file){
				if ($file->isDir()){ 
					continue;
				}
				// store allowed files with their absolute path
				$fileType = pathinfo($file->getPathname());
				if($fileType['extension'] == 'jpg')
					$files[] = $file->getPathname(); 
			}
		}
	}

	// create zip file
	create_zip($files,$albumLabel);
	// function available in create_zip_file.php
}catch(Exception $e){
	echo $e->getMessage();
}

if($type == 'downloadScript'){
	echo 'downloadScript';
}else if($type == 'downloadForUploadScript'){
	echo 'downloadForUploadScript';
}else{
	$data = array();
	$data['success'] = 'true';
	$data['count'] = count($albumNames);
	echo json_encode($data);
}
exit;

?>