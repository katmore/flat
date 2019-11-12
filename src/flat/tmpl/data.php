<?php

namespace flat\tmpl;

use ReflectionProperty;
use ReflectionClass;

class data implements \ArrayAccess
{

    private $data = [];

    private $mappableProperty = null;

    private function getMappableProperty(): array
    {
        if ($this->mappableProperty === null) {
            $this->mappableProperty = [];
            array_map(function (ReflectionProperty $p) {
                $this->mappableProperty[$p->getName()] = null;
            }, (new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED));
        }
        return $this->mappableProperty;
    }

    public function getAsArray(): array
    {
        $mappableProperty = $this->getMappableProperty();
        array_walk($mappableProperty, function (&$val, string $prop) {
            $val = $this->$prop;
        });
        return array_merge($this->data, $mappableProperty);
    }

    public function getAsObject(): \stdClass
    {
        return (object) $this->getAsArray();
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->data[] = $value;
            return;
        }
        if (key_exists((string) $offset, $this->getMappableProperty())) {
            $this->$offset = $value;
            return;
        }
        $this->data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return key_exists((string) $offset, $this->getMappableProperty()) || isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
        key_exists((string) $offset, $this->getMappableProperty()) && $this->$offset = null;
    }

    public function offsetGet($offset)
    {
        return key_exists((string) $offset, $this->getMappableProperty()) ? $this->$offset : (isset($this->data[$offset]) ? $this->data[$offset] : null);
    }

    public function __construct($data = null)
    {
        if (is_scalar($data)) {

            if (property_exists($this, 'value')) {
                $p = (new ReflectionClass($this))->getProperty('value');
                $p->setAccessible(true);
                $p->setValue($this, $data);
            }
            $this->data = [
                'data' => $data
            ];
        } else {
            if (is_array($data) || is_object($data)) {

                $mappableProperty = $this->getMappableProperty();
                foreach ($data as $k => $v) {

                    if (key_exists((string) $k, $mappableProperty)) {
                        $this->$k = $v;
                    } else {
                        $this->data[$k] = $v;
                    }
                }
                unset($k);
                unset($v);
            }
        }
    }
}