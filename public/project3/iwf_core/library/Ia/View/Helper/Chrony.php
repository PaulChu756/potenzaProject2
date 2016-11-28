<?php
/*
 * Requires http://wbotelhos.com/chrony
 */
class Ia_View_Helper_Chrony extends Zend_View_Helper_Abstract
{
    
    public function chrony($timestr,$callback=false)
    {
    	$id = md5(rand(10000,99999));

		$d = floor(($timestr/86400));
		$remainder = $timestr % 86400;
		$h = floor(($remainder/3600)) + (24*$d);
		$remainder = $remainder % 3600;
		$m = floor(($remainder/60));
		$s = $remainder % 60;
		
	    $timeFormatted = ($h.':'.$m.':'.$s);

        $this->view->headScript()->captureStart();
        if($callback):
        ?>
			$('#<?=$id?>').chrony({text:'<?=$timeFormatted?>',finish:function(){<?=$callback;?>}});
		<?php
		else:
		?>
			$('#<?=$id?>').chrony({text:'<?=$timeFormatted?>'});
		<?php 
		endif;
        $this->view->headScript()->captureEnd();

	    return '<span class="chrony" id="'.$id.'"></span>';

    }

}