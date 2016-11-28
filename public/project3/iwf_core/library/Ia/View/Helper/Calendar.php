<?php

class Ia_View_Helper_Calendar extends Zend_View_Helper_Abstract
{
    public function calendar($month=null,$year=null,$callback,$counterCallback){
        $today = new DateTime();
        $month = ($month==null) ? date("m") : $month;
        $year = ($year==null) ? date("Y") : $year;
        $numdaysinmonth = cal_days_in_month( CAL_GREGORIAN, $month, $year );
        $jd = cal_to_jd( CAL_GREGORIAN,$month,date( 1 ),$year);
        $startday = jddayofweek( $jd , 0 );
        $monthname = jdmonthname( $jd, 1 );
        ob_start();
        ?>
            <h2 class="month-name"><?= $monthname ?> <?=$year?></h2>
            <table class="calendar table table-bordered">
            <thead>
                <th>Sun</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thur</th>
                <th>Fri</th>
                <th>Sat</th>
            </thead>
            <tbody>
            <tr>
        <?php
            $emptycells = 0;
            for( $counter = 0; $counter <  $startday; $counter ++ ) {
                echo "\t\t<td>-</td>\n";
                $emptycells ++;
            }
            $rowcounter = $emptycells;
            $numinrow = 7;
            for( $counter = 1; $counter <= $numdaysinmonth; $counter ++ ) {
                $rowcounter ++;
                $todayClass = ($today->format('Ymj')==$year.$month.$counter) ? ' today' : '';
                echo "\t\t<td><span class=\"counter$todayClass\">";
                if(is_callable($counterCallback))
                    echo call_user_func($counterCallback,DateTime::createFromFormat('Ymj',$year.$month.$counter),$this->view);
                else
                    echo $counter;
                echo "</span>";
                echo call_user_func($callback,DateTime::createFromFormat('Ymj',$year.$month.$counter),$this->view);
                echo "</td>\n";
                if( $rowcounter % $numinrow == 0 ) {
                    echo "\t</tr>\n";
                    if( $counter < $numdaysinmonth ) {
                        echo "\t<tr>\n";
                    }
                    $rowcounter = 0;
                }
            }
            $numcellsleft = $numinrow - $rowcounter;
            if( $numcellsleft != $numinrow ) {
                for( $counter = 0; $counter < $numcellsleft; $counter ++ ) {
                    echo "\t\t<td>-</td>\n";
                    $emptycells ++;
                }
            }
        ?>
            </tr>
            </tbody>
        </table>
        <?php
        $xhtml = ob_get_contents();
        ob_end_clean();
        return $xhtml;
    }
}        