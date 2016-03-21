<?php

function translateWithGoogle($sourceLanguage, $targetLanguage, $textToTranslate){
	global $GoogleTranslateKey;
	$textToTranslate 	= urlencode($textToTranslate);
	$googleTranslateURL = "https://www.googleapis.com/language/translate/v2?key=$GoogleTranslateKey&source=$sourceLanguage&target=$targetLanguage&q=$textToTranslate";
	$json				= file_get_contents($googleTranslateURL);
	$response			= json_decode($json);

	return $response->data->translations[0]->translatedText;
}