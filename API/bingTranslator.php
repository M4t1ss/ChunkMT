<?php
	global $BingClientID, $BingClientSecret;
	include 'HttpTranslator.php';
	include 'AccessTokenAuthentication.php';
	$authUrl      = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
	$scopeUrl     = "http://api.microsofttranslator.com";
	$grantType    = "client_credentials";
	$authObj      = new AccessTokenAuthentication();
	$accessToken  = $authObj->getTokens($grantType, $scopeUrl, $BingClientID, $BingClientSecret, $authUrl);

function translateWithBing($sourceLanguage, $targetLanguage, $textToTranslate){
	$textToTranslate = urlencode($textToTranslate);

	try {
		global $accessToken;
		$authHeader		= "Authorization: Bearer ". $accessToken;
		$contentType	= 'text/plain';
		$category     	= 'general';
		$params			= "text=".$textToTranslate."&to=".$targetLanguage."&from=".$sourceLanguage;
		$translateUrl	= "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
		$translatorObj	= new HTTPTranslator();
		$curlResponse	= $translatorObj->curlRequest($translateUrl, $authHeader);

		$xmlObj = simplexml_load_string($curlResponse);
		foreach((array)$xmlObj[0] as $val){
			$translatedStr = $val;
		}
		
		if(strlen($translatedStr) == 0){			
			echo "\nI guess the token expired\n";
			echo "Let us try again...\n";
			global $grantType, $scopeUrl, $BingClientID, $BingClientSecret, $authUrl; 
			$authObj = new AccessTokenAuthentication();
			$accessToken = $authObj->getTokens($grantType, $scopeUrl, $BingClientID, $BingClientSecret, $authUrl);
			
			$authHeader = "Authorization: Bearer ". $accessToken;
			$contentType  = 'text/plain';
			$category     = 'general';
			$params = "text=".$textToTranslate."&to=".$targetLanguage."&from=".$sourceLanguage;
			$translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
			$translatorObj = new HTTPTranslator();
			$curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader);

			$xmlObj = simplexml_load_string($curlResponse);
			foreach((array)$xmlObj[0] as $val){
				$translatedStr = $val;
			}
		}		
		
		return $translatedStr;
	} catch (Exception $e) {
		echo "Exception: " . $e->getMessage() . PHP_EOL;
		return;
	}
}