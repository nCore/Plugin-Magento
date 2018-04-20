<?php
namespace Synerise\Producers;

class Product extends ProducerAbstract
{

    public function addProduct($params = array()) {
        $data['params'] = $params;
        $data['type'] = 'product.add';
        $this->enqueue($data);
    }

    public function removeProduct($params = array()) {
        $data['params'] = $params;
        $data['type'] = 'product.remove';
        $this->enqueue($data);
    }


}