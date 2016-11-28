<?php
namespace Ia\Observer;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
interface ObserverInterface
{

	public function execute();    
    
    public function setPercentComplete($percent_complete);
    
    public function setGearmanJob(\GearmanJob $job);

}