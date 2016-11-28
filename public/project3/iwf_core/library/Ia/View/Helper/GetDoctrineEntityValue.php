<?php

class Ia_View_Helper_GetDoctrineEntityValue extends Zend_View_Helper_Abstract
{
    
    public function getDoctrineEntityValue($entity,$key,$relations,$filters=array(),$delimeter=', '){   //filters aka formatters
    	try{
            if(strpos($key,'.')!==false){
                $parts = explode('.',$key); // [0] c [1] company_name
                $relation = $relations[$parts[0]];
                if(strpos($relation,'.')!==false){
                    $relationParts = explode('.',$relation);
                    foreach($relationParts as $part){
                        if(is_object($entity))
                            $entity = $entity->{$part};
                    }
                } else {
                    $entity = $entity->{$relation};
                }
                if(!is_object($entity)){
                    $value = null;
                } elseif($entity instanceof Doctrine\ORM\PersistentCollection) {
                    $valueArray = array();
                    foreach($entity as $subent)
                    {
                        $valueArray[] = $subent->{$parts[1]};
                    }
                    foreach($valueArray as $key2=>$value2){
                        $valueArray[$key2] = $this->_handleDateTime($value2);
                    }
                    $value = implode($delimeter,$valueArray);
                } else {
                    $value = $entity->{$parts[1]};
                }
            } else {
                $value = $entity->{$key};
            }
            $value = $this->_handleDateTime($value);
            return $value;
    	} catch(\Exception $e) {
    	   return '-';
    	}
    }

    protected function _handleDateTime($value)
    {
        if($value instanceOf DateTime)
            if(isset($filters[$key]) && $filters[$key] instanceof Zend_Filter_Interface)
                $value = $filters[$key]->filter($value);
            else
                if($value->format('H:i:s')=='00:00:00')
                    $value = $value->format('m/d/Y');
                else
                    $value = $value->format('m/d/Y H:i:s');
        return $value;        

    }

}
