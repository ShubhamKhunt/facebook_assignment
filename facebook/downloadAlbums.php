<?php

session_start();
$user_id = $_SESSION['user_id'];
$accessToken = $_SESSION['accessToken'];
$album_id = $_REQUEST['album_id'];
$url = "https://graph.facebook.com/v2.3/{$album_id}?fields=name,photos&access_token={$accessToken}";

$json = file_get_contents($url);
$obj = json_decode($json);

if($accessToken != '' && $obj != ''){
	$album_name = $obj->name;
	$albums = $obj->photos->data;
}

// create new directory of user if not created
$dir = 'user_space';
if(!file_exists($dir)){
	mkdir($dir);
}

$directory = 'user_space/'.$user_id.'/';
if(!file_exists($directory)){
	mkdir($directory);
}

// create zip file with album name going tobe download
$zip = new ZipArchive();
$zip_name = $directory.$album_name.".zip";
$zip->open($zip_name,  ZipArchive::CREATE);

try{
	foreach($albums as $pics){
		$url = "https://graph.facebook.com/v2.10/{$pics->id}/picture?access_token={$accessToken}";
		$img_file = $pics->id.'.jpg';
		$data = file_get_contents($url);
		// file pointer to save image in user-s directory
		$fp = fopen($directory.$img_file,"w");
		fwrite($fp,$data);
		fclose($fp);
		// add created image to zip file
		$zip->addFromString(basename($directory.$img_file),  file_get_contents($directory.$img_file));
		// remove created image file
		unlink($directory.$img_file);
	}
	$zip->close();
	echo 'true';
}catch(Exception $e){
	echo $e->getMessage();
}

?>