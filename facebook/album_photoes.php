<?php

session_start();

$album_id = $_REQUEST['album_id'];
$accessToken = $_SESSION['accessToken'];
$url = "https://graph.facebook.com/v2.3/{$album_id}?fields=photos&access_token={$accessToken}";

$json = file_get_contents($url);
$obj = json_decode($json);


if($accessToken != ''){
	if($obj != ''){
		$albums = $obj->photos->data;
	}
}

$data = array();
foreach($albums as $pics){ 
	$data[] = "https://graph.facebook.com/v2.10/{$pics->id}/picture?access_token={$accessToken}";
}

echo json_encode($data);
exit;

?>