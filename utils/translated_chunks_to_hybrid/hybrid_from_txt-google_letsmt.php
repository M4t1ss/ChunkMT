<?php
// This is the part of the Syntactic Multi-System Hybrid Translator workflow that generates a hybrid translation from chunks of Google & LetsMT APIs provided via text files
// It requires four parameters - the language model, input chunks from google, bing, letsmt & output file name
// It is run with the following command:
// php hybrid_from_txt-google_letsmt.php <language model> <google chunks> <letsmt chunks> <output>
// For example:
// php hybrid_from_txt-google_letsmt.php languageModel.binary google.txt letsmt.txt hybrid.txt

if(!isset($argv[1]) || $argv[1]=="" || !isset($argv[2]) || $argv[2]=="" || !isset($argv[3]) || $argv[3]=="" || !isset($argv[4]) || $argv[4]==""){
	echo "Please provide the language model and input/output text file names!\n";
	die;
}

$languageModel 	= $argv[1];
$googleChunks 	= $argv[2];
$letsmtChunks 	= $argv[3];
$outputFile 	= $argv[4];

$ing = fopen($googleChunks, "r") or die("Can't create output file!"); 	//Google output
$inl = fopen($letsmtChunks, "r") or die("Can't create output file!"); 	//LetsMT output
$outh = fopen($outputFile, "a") or die("Can't create output file!"); 	//Hybrid output

//process input file by line
if ($ing && $inl) {
    while (($sentenceOne = fgets($ing)) !== false && ($sentenceTwo = fgets($inl)) !== false ) {
		
		unset($sentences);
		unset($perplexities);
		
		if($sentenceOne == "\n" && $sentenceTwo == "\n"){
			$outputString = "\n";
		}else{
			$sentences[] = str_replace(array("\r", "\n"), '', $sentenceOne);
			$sentences[] = str_replace(array("\r", "\n"), '', $sentenceTwo);
			
			//Get the perplexities of the translations
			$perplexities[] = shell_exec('/home/matiss/EXP_SEPT_2015/hybridFromTxt/exp.sh '.$languageModel.' "'.$sentenceOne.'"');
			$perplexities[] = shell_exec('/home/matiss/EXP_SEPT_2015/hybridFromTxt/exp.sh '.$languageModel.' "'.$sentenceTwo.'"');
			
			$outputString = $sentences[array_keys($perplexities, min($perplexities))[0]];
			$outputString = trim($outputString)." ";
		}
		fwrite($outh, $outputString);
	}
	fclose($ing);
	fclose($inl);
	fclose($outh);
}
