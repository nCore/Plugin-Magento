<?php
class Synerise_Export_Model_Adminhtml_System_Config_Backend_Export_Cron extends Mage_Core_Model_Config_Data {

    const CRON_MODEL_PATH = 'synerise_export/generate/cron_schedule';

    protected function _afterSave() 
    {
        $enabled    = $this->getData('groups/generate/fields/enabled/value');
        $time       = $this->getData('groups/generate/fields/time/value');
        $frequncy   = $this->getData('groups/generate/fields/frequency/value');

        $frequencyDaily     = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
        $frequencyWeekly    = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
        $frequencyMonthly   = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;        
        
        $cronExprString = '';
        if ($enabled) {
            $cronDayOfWeek = date('N');
            $cronExprArray = array(
                intval($time[1]),                                   # Minute
                intval($time[0]),                                   # Hour
                ($frequncy == $frequencyMonthly) ? '1' : '*',       # Day of the Month
                '*',                                                # Month of the Year
                ($frequncy == $frequencyWeekly) ? '1' : '*',        # Day of the Week
            );
            $cronExprString = join(' ', $cronExprArray);
        }    
        
        try {
            Mage::getModel('core/config_data')->load(self::CRON_MODEL_PATH, 'path')->setValue($cronExprString)->setPath(self::CRON_MODEL_PATH)->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
        }
    }

}