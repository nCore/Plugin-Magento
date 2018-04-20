<?php
namespace Synerise\Response\Collection;

abstract class AbstractCollection extends \Synerise\Response\AbstractResponse implements \IteratorAggregate
{
    protected $_paginator;
    protected $_data = array();

    public function __construct(array $response)
    {
        $this->_paginator = isset($response['data']['paginator']) ? $response['data']['paginator'] : null;
        parent::__construct($response);
    }

    public function getPaginator()
    {
        return $this->_paginator;
    }

    public function getIterator() {
        return new \ArrayIterator($this->_data);
    }
}