<?php
namespace Synerise\Producers;

class Client extends ProducerAbstract
{
    private $_customIdentify;

    public function customIdentify($customIdentify) {
        $this->_customIdentify = $customIdentify;
    }

    public function update($data = array()) {
        $this->setData($data);
    }

    public function getCustomIdetify() {
        return $this->_customIdentify;    
    }

    public function setData($params = array()) {
        $data['params'] = $params;
        $data['object']= 'client.data'; 
        $this->enqueue($data);
    }

    public function logIn() {
        $data['object'] = 'client.logIn';
        $this->enqueue($data);
    }

    public function logOut() {
        $data['object'] = 'client.logOut';
        $this->enqueue($data);
    }

}