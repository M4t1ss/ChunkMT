<?php
class Node
{
    public $category;    	// NP, VP, PP, ...
    public $word;    		// contains the word string
    public $parent;     	// the parent Node
	public $level;			// the integer level of the node
	public $visited;		// for chunking
	public $chunk;			// a chunk of all child words/chunks
	public $children;		// array of all child nodes

    public function __construct($category, $parent = null) {
        $this->category = $category;
        $this->parent = $parent;
		$this->visited = false;
    }

	public function addChild($childNode){
		$this->children[] = $childNode;
	}

	public function hasChildren(){
		return (count($this->children)>0?true:false);
	}

	public function setParent($parentNode){
		$this->parent = $parentNode;
	}

	public function getParent(){
		return $this->parent;
	}

	public function setWord($word){
		$this->word = $word;
	}

	//combines the current chunk with its siblings if they are just words and not chunks
	public function getSiblingWords($currentChunk){
		$combinedChunk = "";
		if(isset($this->parent) && is_array($this->parent->children)){
			foreach($this->parent->children as $sibling){
				if((isset($sibling->word) && $sibling->visited) || (!isset($sibling->word) && $sibling != $this)){
					return $currentChunk;
				}
				if($sibling != $this){
					$combinedChunk .= $sibling->word." ";
					$sibling->visited = true;
				}else{
					$combinedChunk .= $currentChunk." ";
				}
			}
		}else {
			$combinedChunk = $currentChunk;
		}
		if(isset($this->parent) && str_word_count($combinedChunk) < 7){
			$tempNode = $this->parent;
			$combinedChunk = $tempNode->getSiblingWords($combinedChunk);
		}
		return $combinedChunk;
	}

	public function allChildrenAreWords(){
		if($this->hasChildren()){
			foreach($this->children as $child){
				if(!isset($child->word))
					return false;
			}
		}else{
			return false;
		}
		return true;
	}

	public function allSiblingsAreWords(){
		if(!isset($this->parent))
			return false;
		foreach($this->parent->children as $sibling){
			if(!isset($sibling->word) && $sibling != $this)
				return false;
		}
		return true;
	}

	//apstaigāšana

	public function traverse($method, $sentece) {
		 switch($method) {
			 case 'inorder':
			 return $this->_inorder($this, $sentece);
			 break;
			 case 'revorder':
			 return $this->_revorder($this, $sentece);
			 break;

			 default:
			 break;
		 }
	}

	private function _inorder($node, &$sentece) {
		if(isset($node->word)){
			$sentece .= $node->word." ";
		}else{
			if($node->hasChildren()){
				foreach($node->children as $child){
					$this->_inorder($child, $sentece);
				}
			}
		}
		return $sentece;
	}

	private function _revorder($node, &$sentece) {
		if(isset($node->word)){
			$sentece .= $node->word." ";
		}else{
			foreach(array_reverse($node->children) as $child){
				$this->_revorder($child, $sentece);
			}
		}
		return $sentece;
	}

	public function getChunks($node, &$chunks){

		if(!$node->hasChildren() && !$node->visited){
			$chunks[] = array('chunk' => trim($node->word), 'level' => $node->level);
			$node->visited = true;
		}elseif($node->allChildrenAreWords()){
			$tempChunk = "";
			foreach($node->children as $child){
				$tempChunk .= $child->word." ";
				$child->visited = true;
			}
			if($node->allSiblingsAreWords()){
				$tempChunk = $node->getSiblingWords(trim($tempChunk));
			}
			$chunks[] = array('chunk' => trim($tempChunk), 'level' => $node->level);
		}else{
			if($node->hasChildren())
				foreach(array_reverse($node->children) as $child){
					$this->getChunks($child, $chunks);
				}
		}

		return $chunks;
	}
	
	
	public function setInnerChunks($node){
		if(isset($node->word)){
			$node->chunk = $node->word;
			return $node->word;
		}else{
			$tempInnerChunk = "";
			if($node->hasChildren()){
				foreach($node->children as $innerChild){
					$tempInnerChunk .= $innerChild->setInnerChunks($innerChild)." ";
				}
			}
			$tempInnerChunk = trim($tempInnerChunk);
			$node->chunk = $tempInnerChunk;
			return $tempInnerChunk;
		}
	}
	
	public function clearInnerChunks($node){
		if(isset($node->chunk)){
			unset($node->chunk);
		}
		if($node->hasChildren()){
			foreach($node->children as $nodeChild){
				$this->clearInnerChunks($nodeChild);
			}
		}
	}
	
	public function getChunksToSize($node, $maxSize, &$chunks){
		if(!isset($node->chunk)){
			$node->setInnerChunks($node);
		}
		if(str_word_count($node->chunk) > $maxSize && $node->hasChildren()){
			foreach(array_reverse($node->children) as $nodeChild){
				$nodeChild->getChunksToSize($nodeChild, $maxSize, $chunks);
			}
		}else{
			if(count($chunks) > 0){
				$lastChunk = array_pop($chunks);
				
				//combine one-word chunks and non alphanumerical chunks with to bigger chunks
				if(str_word_count($node->chunk) <= 1 || strlen(preg_replace( "/[^a-zA-Z0-9]/i", "", $lastChunk )) == 0 || is_numeric(preg_replace( "/[^a-zA-Z0-9]/i", "", $lastChunk )) || is_numeric($node->chunk)){
					// echo "oh, no!! '<b>" . $node->chunk . "</b>' is veeery small ): Can we add it to the next logical bigger chunk?<br/>";
					$chunks[] = $node->chunk." ".$lastChunk;
					// var_dump(array($node->chunk." ".$lastChunk, $node->level));
				}else{
					$chunks[] = $lastChunk;
					$chunks[] = $node->chunk;
					// var_dump(array(
						// str_word_count($node->chunk),
						// is_numeric($node->chunk),
						// strlen(preg_replace( "/[^a-zA-Z0-9]/i", "", $lastChunk )),
						// is_numeric(preg_replace( "/[^a-zA-Z0-9]/i", "", $lastChunk )),
						// $node->chunk, 
						// $lastChunk, 
						// $node->level
					// ));
				}
			}else{
				$chunks[] = $node->chunk;
			}
		}
		return $chunks;
	}
}
