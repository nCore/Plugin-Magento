<?php
class Synerise_Integration_Helper_Client extends Mage_Core_Helper_Abstract
{
    protected $syneriseData = null;

    public function getSyneriseData()
    {
        if(empty($this->syneriseData)) {

            /** @var Synerise\SyneriseClient $clientInstance */
            $clientInstance = Mage::helper('synerise_integration/api')->getInstance('Client');
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if($customer && $customer->getEmail()) {

                try {
                    $data = $clientInstance->getClientByParameter(array('email' => $customer->getEmail()));
                    if($data) {
                        $this->syneriseData = $data;
                    }
                } catch (Exception $e) {}
            }
        }

        return $this->syneriseData;
    }
}