<?php
// This is the part of the Syntactic Multi-System Hybrid Translator workflow that generates a hybrid translation from chunks of Google & LetsMT APIs provided via text files
// It requires four parameters - the language model, input chunks from google, bing, letsmt & output file name
// It is run with the following command:
// php from_txt-google_letsmt.php <language model> <google chunks> <letsmt chunks> <output>
// For example:
// php from_txt-google_letsmt.php languageModel.binary google.txt letsmt.txt hybrid.txt

if(!isset($argv[1]) || $argv[1]=="" || !isset($argv[2]) || $argv[2]=="" || !isset($argv[3]) || $argv[3]=="" || !isset($argv[4]) || $argv[4]==""){
	echo "Please provide the language model and input/output text file names!\n";
	die;
}

$languageModel 		= $argv[1];
$googleChunkFile 	= $argv[2];
$letsmtChunkFile 	= $argv[3];
$outputFile 		= $argv[4];

$ing = fopen($googleChunkFile, "r") or die("Can't create output file!"); 				//Google output
$inl = fopen($letsmtChunkFile, "r") or die("Can't create output file!"); 				//LetsMT output
$outh = fopen($outputFile, "a") or die("Can't create output file!"); 					//Hybrid output
$outCount = fopen($outputFile.".count.txt", "a") or die("Can't create output file!"); 	//Hybrid count

$totalChunks 	= 0;
$equalChunks 	= 0;
$letsmtChunks 	= 0;
$googleChunks 	= 0;

//process input file by line
if ($inl && $ing) {
    while (($sentenceOne = fgets($inl)) !== false && ($sentenceTwo = fgets($ing)) !== false ) {
		
		unset($sentences);
		unset($perplexities);
		
		if(strlen(trim($sentenceOne)) == 0 && strlen(trim($sentenceTwo)) == 0){
			$outputString = "\n";
		}else{
			//Use the language model ONLY if the translations differ
			if(strcmp($sentenceOne, $sentenceTwo) != 0){
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceOne);
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceTwo);
				
				//Get the perplexities of the translations
				$perplexities[] = shell_exec('../../queryKenLM.sh '.$languageModel.' "'.$sentenceOne.'"');
				$perplexities[] = shell_exec('../../queryKenLM.sh '.$languageModel.' "'.$sentenceTwo.'"');
				
				$outputString = $sentences[array_keys($perplexities, min($perplexities))[0]];
			}else{
				$outputString = $sentenceOne;
			}
			
			$outputString = trim($outputString)." ";		
			
			//Count chunks
			$totalChunks++;
			$letsmtSentence = str_replace(array("\r", "\n"), '', $sentenceOne);
			$googleSentence = str_replace(array("\r", "\n"), '', $sentenceTwo);
			$letsmtSentence = trim($letsmtSentence)." ";	
			$googleSentence = trim($googleSentence)." ";	
			if(strcmp($sentenceOne, $sentenceTwo) == 0){
				$equalChunks++;
			}elseif ($outputString == $letsmtSentence){
				$letsmtChunks++;
			}elseif ($outputString == $googleSentence){
				$googleChunks++;
			}
		}
		fwrite($outh, $outputString);
	}
	//Write chunk counts
	fwrite($outCount, "Total chunk count: ".$totalChunks."\n");
	fwrite($outCount, "Equal chunk count: ".$equalChunks."\n");
	fwrite($outCount, "LetsMT chunk count: ".$letsmtChunks."\n");
	fwrite($outCount, "Google chunk count: ".$googleChunks."\n");
	
	fclose($inl);
	fclose($ing);
	fclose($outh);
	fclose($outCount);
}
