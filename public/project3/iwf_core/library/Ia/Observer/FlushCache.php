<?php
namespace Ia\Observer;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class FlushCache extends ObserverAbstract implements ObserverInterface
{

	public function execute()
	{
		$cache = \Zend_Registry::get('cache');
        $cache->clean(\Zend_Cache::CLEANING_MODE_ALL);
        return 'Application cache has been flushed.';
	}

}