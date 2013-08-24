<?php
//
//    The MIT License (MIT)
//
//    Copyright (c) 2013 Marcel Erz
//
//    Permission is hereby granted, free of charge, to any person obtaining a copy of
//    this software and associated documentation files (the "Software"), to deal in
//    the Software without restriction, including without limitation the rights to
//    use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
//    the Software, and to permit persons to whom the Software is furnished to do so,
//    subject to the following conditions:
//
//    The above copyright notice and this permission notice shall be included in all
//    copies or substantial portions of the Software.
//
//    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
//    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
//    FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
//    COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
//    IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
//    CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//

namespace HttpStream;

/**
 * Header data class
 */
class Header implements \Serializable {

    /**
     * Name of header
     *
     * @var string
     */
    protected $_name = NULL;

    /**
     * Value of header
     *
     * @var scalar
     */
    protected $_value = NULL;


    /**
     * Initializes header
     *
     * @param string $name
     * @param scalar $value
     */
    public function __construct($name, $value) {
        $this->setName($name);
        $this->setValue($value);
    }


    /**
     * Compares with another header
     *
     * @param Header|string $other
     * @return bool
     */
    public function isEqual($other) {
        if ($other instanceof Header) {
            return ($other->getName() === $this->getName());
        } else {
            return ($other == $this->getName());
        }
    }


    /**
     * Gets name of header
     *
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * Sets name of header
     *
     * @param string $name
     * @throws \InvalidArgumentException
     */
    public function setName($name) {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('A header name must be a string.');
        }
        $this->_name = $name;
    }


    /**
     * Gets value of header
     *
     * @return scalar
     */
    public function getValue() {
        return $this->_value;
    }

    /**
     * Sets value of header
     *
     * @param scalar $value
     * @throws \InvalidArgumentException
     */
    public function setValue($value) {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException('A header value must be a scalar.');
        }
        $this->_value = $value;
    }


    // From Serializable
    /**
     * Serializes header
     *
     * @return string
     */
    public function serialize() {
        return serialize(array($this->_name, $this->_value));
    }

    /**
     * Unserializes header
     *
     * @param string $serialized
     */
    public function unserialize($serialized) {
        list($this->_name, $this->_value) = unserialize($serialized);
    }


    /**
     * Flushes header data to client
     */
    public function flush() {
        header($this->_name.': '.$this->_value);
    }
}