<?php
// echo translateWithLetsMT("the chairman shall not vote .");

function translateWithLetsMT($textToTranslate){
	//Translation stuff
	// $sourceLanguage		= "en";
	// $targetLanguage		= "lv";
	// $textToTranslate		= "this arrangement applies to bovine meat . ";
	$textToTranslate = urlencode($textToTranslate);

	//API stuff
	global $LetsMTusername, $LetsMTpassword, $LetsMTSystemID;
	 
	$context = stream_context_create(array(
		'http' => array(
			'header'  => "Authorization: Basic " . base64_encode("$LetsMTusername:$LetsMTpassword")
		)
	));
	$LetsMTURL = "https://www.letsmt.eu/ws/service.svc/json/Translate?systemID=$LetsMTSystemID&text=$textToTranslate";
	$json = file_get_contents($LetsMTURL, false, $context);
	$response = json_decode($json);

	return $response;
}