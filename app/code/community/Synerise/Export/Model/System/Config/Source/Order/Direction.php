<?php
class Synerise_Export_Model_System_Config_Source_Order_Direction
{
	public function toOptionArray()
	{
		$sorting = array();
		$sorting[] = array('value'=>'asc', 'label'=> ' ↑ ' . Mage::helper('synerise_export')->__('ascending'));				
		$sorting[] = array('value'=>'desc', 'label'=> ' ↓ ' . Mage::helper('synerise_export')->__('descending'));		
		return $sorting;
	}
}