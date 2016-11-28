<?php

class Ia_View_Helper_SortColumn extends Zend_View_Helper_Abstract
{
    
    public function sortColumn($key,$label,$order=null)
    {
        $xhtml = null;
        $selected = false;
        $dir = 'ASC';
        if($order){
            $parts = explode(' ',$order);
            $orderCol = $parts[0];
            $orderDir = $parts[1];
            
            if($orderCol==$key){
                $selected = true;
                if($dir==$orderDir){
                    $dir = 'DESC';
                }
            }
        }
        $xhtml = '<a href="'.$this->view->url(array('order'=>$key.' '.$dir)).'">';
        if($selected){
            switch($dir){
                case 'ASC':
                    $xhtml .= '<i class="glyphicon glyphicon-arrow-down"></i>';
                    break;
                case 'DESC':
                    $xhtml .= '<i class="glyphicon glyphicon-arrow-up"></i>';
                    break;
            }
        }
        $xhtml .= $label.'</a>';
		return $xhtml;	
	}

}