<?php

class Ia_Form_Element_FileUploader extends Zend_Form_Element_File
{
    /**
     * @var string Default view helper
     */
    public $helper = 'formFileUploader';

    protected $_value = null;

    public function receive(){
        try{
            $value = array();
            $options = array(
                'print_response' => false
                );
            $upload_handler = new Ia_UploadHandler($options);
            $files = $upload_handler->get(false);
            foreach($files['files'] as $file){
                $upload_handler->move_uploaded_file($file,$this->getDestination());
                $value[] = $file->name;
            }
            $this->_value = $value;
            return $value;
        } catch(\Exception $e){
            return parent::receive();
        }
    }

    public function getValue()
    {
        if($this->_value !== null){
            return $this->_value;
        }
        return $this->receive();
    }
}