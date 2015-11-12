<?php

namespace Kerisy\Http;

use Countable;
use IteratorAggregate;
use Kerisy\Core\Object;
use Kerisy\Support\BagTrait;


class HeaderBag extends Object implements IteratorAggregate, Countable
{
    use BagTrait;

    public function __construct(array $data = [], $config = [])
    {
        $this->replace($data);

        parent::__construct($config);
    }

    protected function transformKey($key)
    {
        return strtr(strtolower($key), '_', '-');
    }

    protected function transformValue($value)
    {
        return (array)$value;
    }

    public function with($key, $values)
    {
        $values = array_merge($this->get($key, []), $this->transformValue($values));

        $this->set($key, $values);
    }

    public function first($key, $default = null)
    {
        $values = $this->get($key);

        return !empty($values) ? array_shift($values) : $default;
    }
}
