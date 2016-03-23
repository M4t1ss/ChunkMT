<?php
// This is the part of the Syntactic Multi-System Hybrid Translator workflow that generates a hybrid translation from chunks of Google & Bing APIs provided via text files
// It requires four parameters - the language model, input chunks from google, bing, letsmt & output file name
// It is run with the following command:
// php hybrid_from_txt-bing_google.php <language model> <google chunks> <bing chunks> <output>
// For example:
// php hybrid_from_txt-bing_google.php languageModel.binary google.txt bing.txt hybrid.txt

if(!isset($argv[1]) || $argv[1]=="" || !isset($argv[2]) || $argv[2]=="" || !isset($argv[3]) || $argv[3]=="" || !isset($argv[4]) || $argv[4]==""){
	echo "Please provide the language model and input/output text file names!\n";
	die;
}

$languageModel 		= $argv[1];
$googleChunkFile 	= $argv[2];
$bingChunkFile 		= $argv[3];
$outputFile 		= $argv[4];

$ing = fopen($googleChunkFile, "r") or die("Can't create output file!"); 				//Google output
$inb = fopen($bingChunkFile, "r") or die("Can't create output file!"); 					//Bing output
$outh = fopen($outputFile, "a") or die("Can't create output file!"); 					//Hybrid output
$outCount = fopen($outputFile.".count.txt", "a") or die("Can't create output file!"); 	//Hybrid count

$totalChunks 	= 0;
$equalChunks 	= 0;
$googleChunks 	= 0;
$bingChunks 	= 0;

//process input file by line
if ($ing && $inb) {
    while (($sentenceOne = fgets($ing)) !== false && ($sentenceTwo = fgets($inb)) !== false ) {
		
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
				$perplexities[] = shell_exec('../../exp.sh '.$languageModel.' "'.$sentenceOne.'"');
				$perplexities[] = shell_exec('../../exp.sh '.$languageModel.' "'.$sentenceTwo.'"');

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
