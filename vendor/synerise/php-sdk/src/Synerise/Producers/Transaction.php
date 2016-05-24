<?php
namespace Synerise\Producers;

class Transaction extends ProducerAbstract
{

    public function addProduct($params = array()) {
        $data['params'] = $params;
        $data['object'] = 'product.add';
        $this->enqueue($data);
    }

    public function removeProduct($params = array()) {
        $data['params'] = $params;
        $data['object'] = 'product.remove';
        $this->enqueue($data);
    }

    public function addFavoriteProduct($params = array()) {
        $data['params'] = $params;
        $data['object'] = 'product.addToFavorite';
        $this->enqueue($data);
    }


    public function charge($params = array()) {
        $data['params'] = $params;
        $data['object'] = 'transaction.charge';
        $this->enqueue($data);
    }

    public function cancel($params = array()) {
        $data['params'] = $params;
        $data['object'] = 'transaction.cancel';
        $this->enqueue($data);
    }

}