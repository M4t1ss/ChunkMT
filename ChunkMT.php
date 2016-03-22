<?php
// This is the full Syntactic Multi-System Hybrid Translator workflow all in one file
// It requires three parameters - the language model, input sentences, grammar file
// It is run with the following command:
// php ChunkMT.php <language model> <input sentences> <grammar>
// For example:
// php ChunkMT.php languageModel.binary inputSentences.txt eng_sm6.gr

//Configuration
include 'config.php';

if(!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3]) || $argv[1]=="" || $argv[2]=="" || $argv[3]==""){
	echo "Please provide file names the language model, grammar and input sentences!\n";
	die;
}

//Input parameters
$languageModel 	= $argv[1];
$inputFile		= $argv[2];
$grammar	 	= $argv[3];
//Count output chunks
$totalChunks 	= 0;
$googleChunks 	= 0;
$bingChunks 	= 0;
$letsmtChunks 	= 0;
$yandexChunks 	= 0;

//Parse the input sentences
shell_exec('java -Xmx1024m -jar BerkeleyParser.jar -gr '.$grammar.' < '.$inputFile.' > '.$inputFile.'.parsed');

//Chunk the parsed sentences
include 'chunk.php';
chunkAfile($inputFile.'.parsed', $inputFile.'.chunked');


$inCh = fopen($inputFile.".chunked", "r") or die("Can't open input file!"); 	//Chunked input sentences
$outg = fopen("output.google.txt", "a") or die("Can't create output file!"); 	//Google output sentences
$outb = fopen("output.bing.txt", "a") or die("Can't create output file!"); 		//Bing output sentences
$outl = fopen("output.letsmt.txt", "a") or die("Can't create output file!"); 	//LetsMT output sentences
$outy = fopen("output.yandex.txt", "a") or die("Can't create output file!"); 	//Yandex output sentences
$outh = fopen("output.hybrid.txt", "a") or die("Can't create output file!"); 	//Hybrid output sentences

include 'API/googleTranslate.php';
include 'API/bingTranslator.php';
include 'API/LetsMT.php';
include 'API/yandexTranslator.php';

//Process input file by line
if ($inCh) {
    while (($sourceSentence = fgets($inCh)) !== false) {
		
		if($sourceSentence == "\n"){
			
			fwrite($outg, "\n");
			fwrite($outb, "\n");
			fwrite($outl, "\n");
			fwrite($outy, "\n");
			fwrite($outh, "\n");
			
		}else{
			
			$sourceSentence = str_replace(array("\r", "\n"), '', $sourceSentence);		
			
			//Translate with the APIs
			$sentenceOne = translateWithGoogle($sourceLanguage, $targetLanguage, $sourceSentence);
			$sentenceTwo = translateWithBing($sourceLanguage, $targetLanguage, $sourceSentence);
			$sentenceThree = translateWithLetsMT($sourceSentence);
			$sentenceFour = translateWithYandex($sourceLanguage, $targetLanguage, $sourceSentence);
			
			$sentenceOne = trim($sentenceOne)." ";
			$sentenceTwo = trim($sentenceTwo)." ";
			$sentenceThree = trim($sentenceThree)." ";
			$sentenceFour = trim($sentenceFour)." ";
		
			fwrite($outg, $sentenceOne);
			fwrite($outb, $sentenceTwo);
			fwrite($outl, $sentenceThree);
			fwrite($outy, $sentenceFour);
			
			$sentences 		= array();
			$perplexities 	= array();
		
			$sentences[] = str_replace(array("\r", "\n"), '', $sentenceOne);
			$sentences[] = str_replace(array("\r", "\n"), '', $sentenceTwo);
			$sentences[] = str_replace(array("\r", "\n"), '', $sentenceThree);
			$sentences[] = str_replace(array("\r", "\n"), '', $sentenceFour);
			
			//Get the perplexities of the translations
			$perplexities[] = shell_exec('./exp.sh '.$languageModel.' "'.$sentenceOne.'"');
			$perplexities[] = shell_exec('./exp.sh '.$languageModel.' "'.$sentenceTwo.'"');
			$perplexities[] = shell_exec('./exp.sh '.$languageModel.' "'.$sentenceThree.'"');
			$perplexities[] = shell_exec('./exp.sh '.$languageModel.' "'.$sentenceFour.'"');
			
			//Write the chunk with the smallest perplexity to the hybrid output
			$outputString = $sentences[array_keys($perplexities, min($perplexities))[0]];
			$outputString = trim($outputString)." ";
			fwrite($outh, $outputString);
			
			//Count chunks
			$totalChunks++;
			$googleSentence = str_replace(array("\r", "\n"), '', $sentenceOne);
			$bingSentence = str_replace(array("\r", "\n"), '', $sentenceTwo);
			$lesmtSentence = str_replace(array("\r", "\n"), '', $sentenceThree);
			$yandexSentence = str_replace(array("\r", "\n"), '', $sentenceFour);
			$googleSentence = trim($googleSentence)." ";	
			$bingSentence = trim($bingSentence)." ";	
			$lesmtSentence = trim($lesmtSentence)." ";	
			$yandexSentence = trim($yandexSentence)." ";	
			
			if (strcmp($outputString, $lesmtSentence) == 0){
				$letsmtChunks++;
			}elseif(strcmp($outputString, $bingSentence) == 0){
				$bingChunks++;
			}elseif(strcmp($outputString, $googleSentence) == 0){
				$googleChunks++;
			}elseif(strcmp($outputString, $yandexSentence) == 0){
				$yandexChunks++;
			}
		}
	}
    fclose($inCh);
	fclose($outg);
	fclose($outb);
	fclose($outl);
	fclose($outy);
	fclose($outh);
	
	if($writeStats){
		//Write chunk counts
		$outCount = fopen("stats.txt", "a") or die("Can't create output file!"); 	//Hybrid count
		fwrite($outCount, "Total chunk count: ".$totalChunks."\n");
		fwrite($outCount, "Google chunk count: ".$googleChunks."\n");
		fwrite($outCount, "Bing chunk count: ".$bingChunks."\n");
		fwrite($outCount, "LetsMT chunk count: ".$letsmtChunks."\n");
		fwrite($outCount, "Yandex chunk count: ".$yandexChunks."\n");
		fclose($outCount);
	}
}
