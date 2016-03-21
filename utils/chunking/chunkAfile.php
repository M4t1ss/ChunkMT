<?php
// This is the part of the Syntactic Multi-System Hybrid Translator workflow that generates chunks from a parsed text file
// It requires two parameters - parsed input file name & output file name
// It is run with the following command:
// php chunkAfile.php <input parsed> <output chunked>
// For example:
// php chunkAfile.php parsed.txt chunked.txt

if(!isset($argv[1]) || $argv[1]=="" || !isset($argv[2]) || $argv[2]==""){
	echo "Please provide input/output text file names!\n";
	die;
}

$inputFile 	= $argv[1];
$outputFile = $argv[2];

include '../../chunk.php';

chunkAfile($inputFile, $outputFile);