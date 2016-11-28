<?php

class Ia_View_Helper_ActiveInactive extends Zend_View_Helper_Abstract
{
    
    public function activeInactive($params)
    {			
        $column = $params[0];
        $currentActiveInactive = (strlen($params[1])==0) ? null : intval($params[1]);
        $activeInactives = array(null=>'All',1=>'Active',0=>'Inactive');
        $xhtml = '<form method="post" action="">';
		$xhtml .= '<label for="activeInactive">Active/Inactive</label>
        <select class="form-control" name="filters['.$column.']" id="activeInactive" onchange="this.form.submit()">';
            foreach($activeInactives as $activeInactive=>$label){
                
                $xhtml .= '<option value="'.$activeInactive.'"';
                if($activeInactive===$currentActiveInactive || $label==$currentActiveInactive){
                    $xhtml .= ' selected="selected"';
                }                
                $xhtml .= '>'.$label.'</option>';
            }
        $xhtml .= '</select>';
        $xhtml .= '</form>';
		return $xhtml;	
	
	}

}