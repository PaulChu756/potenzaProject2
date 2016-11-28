<?php

class Ia_View_Helper_Money extends Zend_View_Helper_Abstract
{
    
    public function money($num=0,$correctToZero=false,$html=true,$currencySymbol='$')
    {
        if(!$correctToZero && !is_numeric($num)){
            return 'NaN:'.$num;
        } elseif ($correctToZero && !is_numeric($num)){
            $num = 0;
        }
        if($html){
            $classes = array('money');
            if($num>0) $classes[] = 'positive';
            if($num==0) $classes[] = 'zero';
            if($num<0) $classes[] = 'negative';
            return '<span class="'.implode(' ',$classes).'">'.$currencySymbol.number_format($num,2,'.',',').'</span>';
        } else {
            return $currencySymbol.number_format($num,2,'.',',');
        }
    }

}