<?php
namespace Synerise\Producers;

class Transaction extends ProducerAbstract
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

    public function addFavoriteProduct($params = array()) {
        $data['params'] = $params;
        $data['type'] = 'product.addToFavorite';
        $this->enqueue($data);
    }


    public function charge($params = array()) {
        $data['params'] = $params;
        $data['type'] = 'transaction.charge';
        $this->enqueue($data);
    }

    public function cancel($params = array()) {
        $data['params'] = $params;
        $data['type'] = 'transaction.cancel';
        $this->enqueue($data);
    }

}