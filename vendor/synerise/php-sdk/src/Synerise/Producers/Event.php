<?php
namespace Synerise\Producers;

class Event extends ProducerAbstract
{

    public function track($label, $params = []) {
        $data['label']= $label;
        $data['params']= $params; 
        $data['object']= 'event.track'; 
        $this->enqueue($data);
    }

}