<?php

namespace WebCMS\Translation;

class TranslationArray extends \ArrayObject
{
    private $translation;
    protected $data = array();

    /**
     * @param Translation $translation
     */
    public function __construct($translation)
    {
        $this->translation = $translation;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function offsetGet($name)
    {
        if (!array_key_exists($name, $this->data)) {
            $this->translation->addTranslation($name, $name);
            $this->data[$name] = $name;
        }

        return $this->data[$name];
    }

    public function offsetSet($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function offsetExists($name)
    {
        return isset($this->data[$name]);
    }

    public function offsetUnset($name)
    {
        unset($this->data[$name]);
    }
}
