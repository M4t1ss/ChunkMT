Combining machine translated sentence chunks from multiple MT systems
===================================

This is a hybrid solution for acquiring the best translation of an input sentence by combining translated chunks from multiple online MT engines 

Included software
---------

* THE BERKELEY PARSER - https://github.com/slavpetrov/berkeleyparser
	
	* You can find some grammar files here
	
* Query from KenLM - https://github.com/kpu/kenlm

Requirements
---------

* PHP with curl

* Java (for the Berkeley Parser)

* Berkeley Parser compatible grammar

* KenLM compatible language model (preferrably binarized)

* Access to at least two APIs

  * Google Translate - https://cloud.google.com/translate/
  * Bing Translator - http://www.bing.com/dev/en-us/translator
  * LetsMT - https://www.letsmt.eu

* Tokenized input sentences

Supported APIs
-----------

* Google Translate
* Bing Translator
* LetsMT

Usage
-----------

Upload the files to your server. Set execute permissions (chmod 755) for **exp.sh** and **query**
The ChunkMT requires three parameters - the language model, input sentences, grammar file. It is run with the following command:

```
php ChunkMT.php <language model> <input sentences> <grammar>
```

For example:

```
php ChunkMT.php languageModel.binary inputSentences.txt eng_sm6.gr
```

The output generates four three files:

* output.google.txt
* output.bing.txt
* output.letsmt.txt
* output.hybrid.txt

Utils
-----------

The utils directory contains separate parts of the ChunkMT system that can be run as standalone

* utils/chunking/ contains files for individual chunking and unchunking
	* to parse an input file with the Berkeley Parser (a parsed file is required as input for the chunker) run:
	
	```
	java -Xmx1024m -jar BerkeleyParser-1.7.jar -gr grammar.gr < input.txt
	```
	
* utils/chunks_to_translated_chunks/ contains files for individual translating of chunked files
* utils/translated_chunks_to_hybrid/ contains files for running the hybrid system with chunked translated files


