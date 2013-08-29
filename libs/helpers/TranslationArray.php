<?php 

namespace WebCMS;

class TranslationArray extends \ArrayObject{
	
	protected $data = array(); 
	
	public function getData(){
		return $this->data;
	}
	
    public function offsetGet($name) { 
        
    	if(empty($this->data[$name])){
    		Translation::addTranslation($name, $name);
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