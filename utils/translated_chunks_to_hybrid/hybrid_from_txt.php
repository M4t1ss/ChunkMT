<?php
// This is the part of the Syntactic Multi-System Hybrid Translator workflow that generates a hybrid translation from chunks of all three APIs provided via text files
// It requires five parameters - the language model, input chunks from google, bing, letsmt & output file name
// It is run with the following command:
// php hybrid_from_txt.php <language model> <google chunks> <bing chunks> <letsmt chunks> <output>
// For example:
// php hybrid_from_txt.php languageModel.binary google.txt bing.txt letsmt.txt hybrid.txt

if(!isset($argv[1]) || $argv[1]=="" || !isset($argv[2]) || $argv[2]=="" || !isset($argv[3]) || $argv[3]=="" || !isset($argv[4]) || $argv[4]=="" || !isset($argv[5]) || $argv[5]==""){
	echo "Please provide the language model and input/output text file names!\n";
	die;
}

$languageModel 	= $argv[1];
$googleChunks 	= $argv[2];
$bingChunks 	= $argv[3];
$letsmtChunks 	= $argv[4];
$outputFile 	= $argv[5];

$ing = fopen($googleChunks, "r") or die("Can't create output file!"); 	//Google output
$inb = fopen($bingChunks, "r") or die("Can't create output file!"); 	//Bing output
$inl = fopen($letsmtChunks, "r") or die("Can't create output file!"); 	//LetsMT output
$outh = fopen($outputFile, "a") or die("Can't create output file!"); 	//Hybrid output


//process input file by line
if ($ing && $inb && $inl) {
    while (($sentenceOne = fgets($ing)) !== false && ($sentenceTwo = fgets($inb)) !== false && ($sentenceThree = fgets($inl)) !== false ) {
		
		unset($sentences);
		unset($perplexities);
		
		if($sentenceOne == "\n" && $sentenceTwo == "\n" && $sentenceThree == "\n"){
			$outputString = "\n";
		}else{
			$sentences[] = str_replace(array("\r", "\n"), '', $sentenceOne);
			$sentences[] = str_replace(array("\r", "\n"), '', $sentenceTwo);
			$sentences[] = str_replace(array("\r", "\n"), '', $sentenceThree);
			
			//Get the perplexities of the translations
			$perplexities[] = shell_exec('../../exp.sh '.$languageModel.' "'.$sentenceOne.'"');
			$perplexities[] = shell_exec('../../exp.sh '.$languageModel.' "'.$sentenceTwo.'"');
			$perplexities[] = shell_exec('../../exp.sh '.$languageModel.' "'.$sentenceThree.'"');
			
			$outputString = $sentences[array_keys($perplexities, min($perplexities))[0]];
			$outputString = trim($outputString)." ";
		}
		fwrite($outh, $outputString);
	}
	fclose($ing);
	fclose($inb);
	fclose($inl);
	fclose($outh);
}
