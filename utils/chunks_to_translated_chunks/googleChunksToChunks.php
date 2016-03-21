<?php
// This is the part of the Syntactic Multi-System Hybrid Translator workflow that translates a chunked input to a chunked translated output with Google
// It requires two parameters - chunked input file name & output file name
// It is run with the following command:
// php googleChunksToChunks.php <input chunked> <output chunked>
// For example:
// php googleChunksToChunks.php chunked.txt chunked.translated.txt

if(!isset($argv[1]) || $argv[1]=="" || !isset($argv[2]) || $argv[2]==""){
	echo "Please provide input/output text file names!\n";
	die;
}

$inputFileName 	= $argv[1];
$outputFileName = $argv[2];

//Configuration
include '../../config.php';
include '../../API/googleTranslate.php';

$inputFile = fopen($inputFileName, "r") or die("Can't create input file!");
$outputFile = fopen($outputFileName, "a") or die("Can't create output file!");

//process input file by line

while (($input = fgets($inputFile)) !== false) {
	if($input == "\n"){
		fwrite($outputFile, "\n");
	}else{
		$input = str_replace(array("\r", "\n"), '', $input);		
		$translatedSentence = translateWithGoogle($sourceLanguage, $targetLanguage, $input);
		$outputString = trim($translatedSentence)." ";
		fwrite($outputFile, $outputString."\n");
	}
}

