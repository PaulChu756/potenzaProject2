<?php

class Ia_View_Helper_FilterByDate extends Zend_View_Helper_Abstract
{
    
    public function filterByDate($params)
    {			        
        $column = $params[0];
        $currentDate = (strlen($params[1])==0) ? null : ($params[1]);
        
        $xhtml = '<form method="post" action="">';
		$xhtml .= '<label for="Date">Date</label><input onchange="this.form.submit()" type="text" class="datepicker" name="filters['.$column.']" id="Date" value="'.$currentDate.'">';
        $xhtml .= '</form>';
		return $xhtml;	
	}

}