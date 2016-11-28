<?php
class Ia_View_Helper_IsValidDateTime extends Zend_View_Helper_Abstract
{

    public function isValidDateTime($date_obj)
    {
        $res_date_obj = false;
        if(is_object($date_obj)){
            $date_array = get_object_vars($date_obj);
            if(!empty($date_array['date']) && (strtotime($date_array['date'])>0)){
                $res_date_obj = true;
            }
        }
        return $res_date_obj;
    }   

} 