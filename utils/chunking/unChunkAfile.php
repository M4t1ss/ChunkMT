<?php
// This file generates full sentences from a chunked input file
// It requires two parameters - chunked input file name & output file name
// It is run with the following command:
// php unChunkAfile.php <input chunked> <output>
// For example:
// php unChunkAfile.php chunked.txt output.txt

if(!isset($argv[1]) || $argv[1]=="" || !isset($argv[2]) || $argv[2]==""){
	echo "Please provide input/output text file names!\n";
	die;
}

$inputFileName 	= $argv[1];
$outputFileName = $argv[2];

$inputFile = fopen($inputFileName, "r") or die("Can't create input file!");
$outputFile = fopen($outputFileName, "a") or die("Can't create input file!");

while (($input = fgets($inputFile)) !== false) {
	if($input == "\n"){
		fwrite($outputFile, "\n");
	}else{
		$outputString = trim($input)." ";
		fwrite($outputFile, $outputString);
	}
}

fclose($inputFile);
fclose($outputFile);

