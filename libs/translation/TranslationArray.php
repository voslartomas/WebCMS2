<?php 

namespace WebCMS;

class TranslationArray extends \ArrayObject{
	private $translation;
	protected $data = array(); 
	
	public function __construct($translation){
		$this->translation = $translation;
	}
	
	public function getData(){
		return $this->data;
	}
	
    public function offsetGet($name) { 
        
    	if(empty($this->data[$name])){
    		$this->translation->addTranslation($name, $name);
			echo $this->data[$name] = $name;
    	}

    	return $this->data[$name]; 
    } 
    
    public function offsetSet($name, $value) { 
        $this->data[$name] = $value; 
    } 
    
    public function offsetExists($name) { 
        return isset($this->data[$name]); 
    } 
    
    public function offsetUnset($name) { 
        unset($this->data[$name]); 
    } 
	
}

?>