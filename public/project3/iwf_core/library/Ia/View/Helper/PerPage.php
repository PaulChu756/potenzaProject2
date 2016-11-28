<?php

class Ia_View_Helper_PerPage extends Zend_View_Helper_Abstract
{
    
    public function perPage($currentPerPage=null)
    {			
        $perPages = \Ia\Config::get('scaffolding/per_page_options');
        if(!is_array($perPages)){
            $perPages = array(10,25,50,100);
        }
        $xhtml = '<form class="form-inline" method="post" action="">';
		$xhtml .= '<div class="form-group"><label for="perPage">Per Page</label>
        <select class="form-control" name="perPage" id="perPage" onchange="this.form.submit()">';
            foreach($perPages as $perPage){
                $xhtml .= '<option value="'.$perPage.'"';
                if($perPage==$currentPerPage){
                    $xhtml .= ' selected="selected"';
                }                
                $xhtml .= '>'.$perPage.'</option>';
            }
        $xhtml .= '</select>';
        $xhtml .= '</div></form>';
		return $xhtml;	
	
	}

}
