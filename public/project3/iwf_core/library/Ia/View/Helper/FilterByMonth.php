<?php

class Ia_View_Helper_FilterByMonth extends Zend_View_Helper_Abstract
{
    
    public function filterByMonth($params)
    {			        
        $column = $params[0];
        $defaults = (isset($params[1]) && is_array($params[1])) ? $params[1] : array();
        $xhtml = '<form method="post" action="">';
        $xhtml .= '<input type="hidden" name="filters['.$column.'][Strategy]" value="Ia_View_Helper_FilterByMonth" />';
		$xhtml .= '<label for="Month">Month</label><select onchange="this.form.submit()" name="filters['.$column.'][Month]" id="Month">';
        $months = $this->getMonths();
        foreach($months as $key=>$month){
            if((isset($defaults['Month'])) && $defaults['Month']==$key)
                $xhtml .= ' <option selected="selected" value="'.$key.'">';
            else
                $xhtml .= ' <option value="'.$key.'">';
            $xhtml .= $month.'</option>';
        }
        $xhtml .= '</select>';
        $xhtml .= '<label for="Year">Year</label><select onchange="this.form.submit()" name="filters['.$column.'][Year]" id="Year">';
        $months = $this->getYears();
        foreach($months as $key=>$month){
            if((isset($defaults['Year'])) && $defaults['Year']==$key)
                $xhtml .= ' <option selected="selected" value="'.$key.'">';
            else
                $xhtml .= ' <option value="'.$key.'">';
            $xhtml .= $month.'</option>';
        }
        $xhtml .= '</select>';        $xhtml .= '</form>';
		return $xhtml;	
	}
    
    public function getWhereArray($key,$value)
    {
        $where = array();
        $where[] = 'YEAR('.$key.')=\''.$value['Year'].'\'';
        $where[] = 'MONTH('.$key.')=\''.$value['Month'].'\'';
        return $where;
    }
    
    public function getMonths(){
        return array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        );    
    }
    
    public function getYears(){
        $years = array();
        $startYear = 2012;
        for($i=$startYear;$i<=($startYear+2);$i++){
            $years[$i] = $i;
        }
        return $years;
    }

}