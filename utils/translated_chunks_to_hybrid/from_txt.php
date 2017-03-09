<?php
// This is the part of the Syntactic Multi-System Hybrid Translator workflow that generates a hybrid translation from chunks of all three APIs provided via text files
// It requires five parameters - the language model, input chunks from google, bing, letsmt, yandex & output file name
// It is run with the following command:
// php from_txt.php <language model> <google chunks> <bing chunks> <letsmt chunks> <yandex chunks> <output>
// For example:
// php from_txt.php languageModel.binary google.txt bing.txt letsmt.txt yandex.txt hybrid.txt

if(!isset($argv[1]) || $argv[1]=="" || !isset($argv[2]) || $argv[2]=="" || !isset($argv[3]) || $argv[3]=="" || !isset($argv[4]) || $argv[4]=="" || !isset($argv[5]) || $argv[5]=="" || !isset($argv[6]) || $argv[6]==""){
	echo "Please provide the language model and input/output text file names!\n";
	die;
}

$languageModel 		= $argv[1];
$googleChunkFile 	= $argv[2];
$bingChunkFile 		= $argv[3];
$letsmtChunkFile 	= $argv[4];
$yandexChunkFile 	= $argv[5];
$outputFile 		= $argv[6];

$ing = fopen($googleChunkFile, "r") or die("Can't create output file!"); 				//Google output
$inb = fopen($bingChunkFile, "r") or die("Can't create output file!"); 					//Bing output
$inl = fopen($letsmtChunkFile, "r") or die("Can't create output file!"); 				//LetsMT output
$iny = fopen($yandexChunkFile, "r") or die("Can't create output file!"); 				//Yandex output
$outh = fopen($outputFile, "a") or die("Can't create output file!"); 					//Hybrid output
$outCount = fopen($outputFile.".count.txt", "a") or die("Can't create output file!"); 	//Hybrid count

$totalChunks 	= 0;
$equalChunks 	= 0;
$googleChunks 	= 0;
$bingChunks 	= 0;
$letsmtChunks 	= 0;
$yandexChunks 	= 0;

//process input file by line
if ($ing && $inb && $inl && $iny) {
    while (($sentenceOne = fgets($ing)) !== false && ($sentenceTwo = fgets($inb)) !== false && ($sentenceThree = fgets($inl)) !== false  && ($sentenceFour = fgets($iny)) !== false ) {
		
		unset($sentences);
		unset($perplexities);
		
		if(strlen(trim($sentenceOne)) == 0 && strlen(trim($sentenceTwo)) == 0 && strlen(trim($sentenceThree)) == 0 && strlen(trim($sentenceFour)) == 0){
			$outputString = "\n";
		}else{
			
			//if two of the translations are equal - that must be good enough
			if(strcmp($sentenceOne, $sentenceTwo) == 0 || strcmp($sentenceOne, $sentenceThree) == 0 || strcmp($sentenceOne, $sentenceFour) == 0){
				$outputString = $sentenceOne;
			}elseif(strcmp($sentenceTwo, $sentenceThree) == 0 || strcmp($sentenceTwo, $sentenceFour) == 0){
				$outputString = $sentenceTwo;
			}elseif(strcmp($sentenceThree, $sentenceFour) == 0){
				$outputString = $sentenceThree;
			//Use the language model ONLY if the translations differ
			}elseif(strcmp($sentenceOne, $sentenceTwo) != 0 || strcmp($sentenceOne, $sentenceThree) != 0 || strcmp($sentenceOne, $sentenceFour) != 0){
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceOne);
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceTwo);
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceThree);
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceFour);
				
				//Get the perplexities of the translations
				$perplexities[] = shell_exec('../../queryKenLM.sh '.$languageModel.' "'.$sentenceOne.'"');
				$perplexities[] = shell_exec('../../queryKenLM.sh '.$languageModel.' "'.$sentenceTwo.'"');
				$perplexities[] = shell_exec('../../queryKenLM.sh '.$languageModel.' "'.$sentenceThree.'"');
				$perplexities[] = shell_exec('../../queryKenLM.sh '.$languageModel.' "'.$sentenceFour.'"');
				
				$outputString = $sentences[array_keys($perplexities, min($perplexities))[0]];
			}
			$outputString = trim($outputString)." ";
			
			//Count chunks
			$totalChunks++;
			$googleSentence = str_replace(array("\r", "\n"), '', $sentenceOne);
			$bingSentence = str_replace(array("\r", "\n"), '', $sentenceTwo);
			$letsmtSentence = str_replace(array("\r", "\n"), '', $sentenceThree);
			$yandexSentence = str_replace(array("\r", "\n"), '', $sentenceFour);
			$googleSentence = trim($googleSentence)." ";	
			$bingSentence = trim($bingSentence)." ";	
			$letsmtSentence = trim($letsmtSentence)." ";	
			$yandexSentence = trim($yandexSentence)." ";	
			if(strcmp($sentenceOne, $sentenceTwo) == 0 && strcmp($sentenceOne, $sentenceThree) == 0 && strcmp($sentenceOne, $sentenceFour) == 0){
				$equalChunks++;
			}elseif ($outputString == $letsmtSentence){
				$letsmtChunks++;
			}elseif($outputString == $bingSentence){
				$bingChunks++;
			}elseif($outputString == $googleSentence){
				$googleChunks++;
			}elseif($outputString == $yandexSentence){
				$yandexChunks++;
			}
		}
		fwrite($outh, $outputString);
	}
	
	//Write chunk counts
	fwrite($outCount, "Total chunk count: ".$totalChunks."\n");
	fwrite($outCount, "Equal chunk count: ".$equalChunks."\n");
	fwrite($outCount, "Google chunk count: ".$googleChunks."\n");
	fwrite($outCount, "Bing chunk count: ".$bingChunks."\n");
	fwrite($outCount, "LetsMT chunk count: ".$letsmtChunks."\n");
	fwrite($outCount, "Yandex chunk count: ".$yandexChunks."\n");
	
	fclose($ing);
	fclose($inb);
	fclose($inl);
	fclose($iny);
	fclose($outh);
	fclose($outCount);
}
