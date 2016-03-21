<?php

function chunkAfile($inputFile, $outputFile){
	// read the input file by line
	while (($input = fgets($inputFile)) !== false) {
		$input = str_replace("\n", "", $input);
		
		// remove "( " from the start of the line and " )" from the end
		$input = substr($input, 2);
		$input = substr($input, 0, -2);

		// change the brackets for easier work...
		$input = str_replace("(","{",$input);
		$input = str_replace(")","}",$input);
		
		// get only the top-level phrases
		preg_match_all('/{((?:[^{}]++|(?R))*+)}/', $input, $matches);
		preg_match_all('/{((?:[^{}]++|(?R))*+)}/', $matches[1][0], $matches2);
		
		// get the chunks
		$cleanChunks = getCleanChunks($matches2[1]);
		
		foreach($cleanChunks as $cleanCunk){
			fwrite($outputFile, $cleanCunk."\n");
		}
		fwrite($outputFile, "\n");
	}
	fclose($inputFile);
	fclose($outputFile);
}



// returns an array of clean chunks
function getCleanChunks($chunks){
	$cleanChunks = array();

	foreach ($chunks as $chunk) {
		if(strlen($chunk) > 0){
			preg_match_all("/^[^\{]+/", $chunk, $constituent);
			// if the chunk starts with a phrase identificator followed by an opening bracket...
			if(ctype_upper(trim($constituent[0][0]))){
				// remove everything up to the opening bracket
				$cleanChunk = preg_replace("/^[^\{]+/", "", $chunk);
				// remove the opening bracket and the phrase identificator (up to six capital letters and/or $ sign)
				$cleanChunk = preg_replace("/\{[A-Z$]{1,6} /", "", $cleanChunk);
				// clean up some symbols...
				$cleanChunk = str_replace("{: :}", ":", $cleanChunk);
				$cleanChunk = str_replace("{: ;}", ";", $cleanChunk);
				$cleanChunk = str_replace("{, ,}", ",", $cleanChunk);
				$cleanChunk = str_replace("{'' '}", "'", $cleanChunk);
				$cleanChunk = str_replace("{`` `}", "`", $cleanChunk);
				$cleanChunk = str_replace("{: '}", "'", $cleanChunk);
				$cleanChunk = str_replace("{: -}", "-", $cleanChunk);
				$cleanChunk = str_replace("{: ...}", "...", $cleanChunk);
				// remove the remaining brackets
				$cleanChunk = str_replace("}", "", $cleanChunk);
				$cleanChunk = str_replace("{", "", $cleanChunk);
				// deal with redundant spaces
				$cleanChunk = str_replace("  ", " ", $cleanChunk);
				$cleanChunk = str_replace("  ", " ", $cleanChunk);
				$cleanChunk = str_replace("  ", " ", $cleanChunk);
			}else{
			// if the chunk does not start with a phrase identificator followed by an opening bracket...
				// remove the phrase identificator up to five symbols followed by a space
				$cleanChunk = preg_replace("/.{1,5}\ /", "", $chunk);
			}
		}

		$cleanChunks[] = $cleanChunk;
	}

	return $cleanChunks;
}