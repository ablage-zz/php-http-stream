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
 * Headers list class
 */
class Headers implements \ArrayAccess, \IteratorAggregate, \Serializable {

    /**
     * List of headers
     *
     * @var array
     */
    protected $_headers = array();


    /**
     * Gets the index of a header
     *
     * @param string|Header $name
     * @return int
     */
    public function indexOf($name) {
        foreach($this->_headers as $index => $header) {
            if ($header->isEqual($name)) {
                return $index;
            }
        }
        return -1;
    }


    /**
     * Gets the offset of a header
     *
     * @param int|string|Header $offset
     * @return int
     */
    public function _getOffset($offset) {
        if (is_int($offset)) {
            return $offset;
        } else {
            return $this->indexOf($offset);
        }
    }


    // From ArrayAccess
    /**
     * Checks existence of offset header
     *
     * @param int|string|Header $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->_headers[$this->_getOffset($offset)]);
    }

    /**
     * Gets header at offset
     *
     * @param int|string|Header $offset
     * @return Header
     */
    public function offsetGet($offset) {
        $index = $this->_getOffset($offset);
        if ($index !== -1) {
            return $this->_headers[$index];
        } else {
            return NULL;
        }
    }

    /**
     * Sets header at offset
     *
     * @param int|string|Header $offset
     * @param Header|scalar $value
     */
    public function offsetSet($offset, $value) {
        $index = $this->_getOffset($offset);

        if (!($value instanceof Header)) {
            if ($offset instanceof Header) {
                $offset = $offset->getName();
            }
            $vaue = new Header($offset, $value);
        }

        if ($index === -1) {
            $this->_headers[] = $value;
        } else {
            $this->_headers[$index] = $value;
        }
    }

    /**
     * Removes header at offset
     *
     * @param int|string|Header $offset
     */
    public function offsetUnset($offset) {
        $index = $this->_getOffset($offset);
        if ($index !== -1) {
            unset($this->_headers[$index]);
        }
    }


    // From IteratorAggregate
    /**
     * Returns an header iterator
     *
     * @return ArrayIterator|Traversable
     */
    public function getIterator() {
        return new ArrayIterator($this->_headers);
    }


    // Serializable
    /**
     * Serializes headers
     *
     * @return string
     */
    public function serialize() {
        return serialize($this->_headers);
    }

    /**
     * Unserialzes headers
     *
     * @param string $serialized
     */
    public function unserialize($serialized) {
        $this->_headers = unserialize($serialized);
    }


    /**
     * Flushes headers to client
     */
    public function flush() {
        foreach($this->_headers as $header) {
            $header->flush();
        }
    }
}