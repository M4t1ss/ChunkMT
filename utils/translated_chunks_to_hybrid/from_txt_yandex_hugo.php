<?php
if(!isset($argv[1]) || $argv[1]==""){
	echo "Please provide the language model!\n";
}

$languageModel 	= $argv[1];
$inh = fopen("/home/matiss/EXP_JAN_2016/data/general/translated/chunks/general-en.tok.chunks.hugo.txt", "r") or die("Can't create output file!"); 		//Hugo output
$iny = fopen("/home/matiss/EXP_JAN_2016/data/general/translated/chunks/general-en.tok.chunks.yandex.txt", "r") or die("Can't create output file!"); 	//Yandex output
$outh = fopen("/home/matiss/EXP_JAN_2016/data/general/translated/full/DGT12gram/general-en.hybrid.tok.hy.txt", "a") or die("Can't create output file!"); 	//Hybrid output
$outCount = fopen("/home/matiss/EXP_JAN_2016/data/general/translated/full/DGT12gram/general-en.hybrid.tok.hy.count.txt", "a") or die("Can't create output file!"); 	//Hybrid count

$totalChunks 	= 0;
$equalChunks 	= 0;
$hugoChunks 	= 0;
$yandexChunks 	= 0;

//process input file by line
if ($inh && $iny) {
    while (($sentenceOne = fgets($inh)) !== false && ($sentenceTwo = fgets($iny)) !== false ) {
		
		unset($sentences);
		unset($perplexities);
		
		if($sentenceOne == "\n" && $sentenceTwo == "\n"){
			$outputString = "\n";
		}else{
			//Use the language model ONLY if the translations differ
			if(strcmp($sentenceOne, $sentenceTwo) != 0){
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceOne);
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceTwo);
				
				//Get the perplexities of the translations
				$perplexities[] = shell_exec('/home/matiss/EXP_JAN_2016/HybridTXTex/exp.sh '.$languageModel.' "'.$sentenceOne.'"');
				$perplexities[] = shell_exec('/home/matiss/EXP_JAN_2016/HybridTXTex/exp.sh '.$languageModel.' "'.$sentenceTwo.'"');
				
				$outputString = $sentences[array_keys($perplexities, min($perplexities))[0]];
			}else{
				$outputString = $sentenceOne;
			}
			
			$outputString = trim($outputString)." ";		
			
			//Count chunks
			$totalChunks++;
			$hugoSentence = str_replace(array("\r", "\n"), '', $sentenceOne);
			$yandexSentence = str_replace(array("\r", "\n"), '', $sentenceTwo);
			$hugoSentence = trim($hugoSentence)." ";	
			$yandexSentence = trim($yandexSentence)." ";	
			if(strcmp($sentenceOne, $sentenceTwo) == 0){
				$equalChunks++;
			}elseif ($outputString == $hugoSentence){
				$hugoChunks++;
			}elseif ($outputString == $yandexSentence){
				$yandexChunks++;
			}
		}
		fwrite($outh, $outputString);
	}
	//Write chunk counts
	fwrite($outCount, "Total chunk count: ".$totalChunks."\n");
	fwrite($outCount, "Equal chunk count: ".$equalChunks."\n");
	fwrite($outCount, "Hugo chunk count: ".$hugoChunks."\n");
	fwrite($outCount, "Yandex chunk count: ".$yandexChunks."\n");
	
	fclose($inh);
	fclose($iny);
	fclose($outh);
	fclose($outCount);
}
