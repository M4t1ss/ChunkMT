<?php

function translateWithYandex($sourceLanguage, $targetLanguage, $textToTranslate){
	
	//API stuff
	global $YandexApiKey;
	
	$textToTranslate = urlencode($textToTranslate);
	$url = "https://translate.yandex.net/api/v1.5/tr.json/translate?key=$YandexApiKey&text=$textToTranslate&lang=$sourceLanguage-$targetLanguage";

	$result 	= file_get_contents($url);
	$decoded 	= json_decode($result);
	$translated = $decoded->text[0];
	
	return $translated;
}