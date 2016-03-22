<?php
if(!isset($argv[1]) || $argv[1]==""){
	echo "Please provide the language model!\n";
}

$languageModel 	= $argv[1];
$ing = fopen("/home/matiss/EXP_JAN_2016/data/general/translated/chunks/general-en.tok.chunks.google.txt", "r") or die("Can't create output file!"); 					//Google output
$inb = fopen("/home/matiss/EXP_JAN_2016/data/general/translated/chunks/general-en.tok.chunks.bing.txt", "r") or die("Can't create output file!"); 					//Bing output
$outh = fopen("/home/matiss/EXP_JAN_2016/data/general/translated/full/DGT12gram/general-en.hybrid.tok.bg.txt", "a") or die("Can't create output file!"); 				//Hybrid output
$outCount = fopen("/home/matiss/EXP_JAN_2016/data/general/translated/full/DGT12gram/general-en.hybrid.tok.bg.count.txt", "a") or die("Can't create output file!"); 	//Hybrid count

$totalChunks 	= 0;
$equalChunks 	= 0;
$googleChunks 	= 0;
$bingChunks 	= 0;

//process input file by line
if ($ing && $inb) {
    while (($sentenceOne = fgets($ing)) !== false && ($sentenceTwo = fgets($inb)) !== false ) {
		
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
			$googleSentence = str_replace(array("\r", "\n"), '', $sentenceOne);
			$bingSentence = str_replace(array("\r", "\n"), '', $sentenceTwo);
			$googleSentence = trim($googleSentence)." ";	
			$bingSentence = trim($bingSentence)." ";	
			if(strcmp($sentenceOne, $sentenceTwo) == 0){
				$equalChunks++;
			}elseif ($outputString == $googleSentence){
				$googleChunks++;
			}elseif ($outputString == $bingSentence){
				$bingChunks++;
			}
		}
		fwrite($outh, $outputString);
	}
	//Write chunk counts
	fwrite($outCount, "Total chunk count: ".$totalChunks."\n");
	fwrite($outCount, "Equal chunk count: ".$equalChunks."\n");
	fwrite($outCount, "Google chunk count: ".$googleChunks."\n");
	fwrite($outCount, "Bing chunk count: ".$bingChunks."\n");
	
	fclose($ing);
	fclose($inb);
	fclose($outh);
	fclose($outCount);
}
