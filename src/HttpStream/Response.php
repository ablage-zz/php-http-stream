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
 * Response class to stream over http
 *
 * ATTENTION: Does not yet support multipart/x-byteranges
 */
class Response {

    /**
     * Path of file
     *
     * @var string
     */
    protected $_file = NULL;


    /**
     * Headers
     *
     * @var Headers
     */
    protected $_headers = NULL;

    /**
     * Response code for client
     *
     * @var int
     */
    protected $_responseCode = 500;


    /**
     * Offset of content within file
     *
     * @var int
     */
    protected $_offset = 0;

    /**
     * Length of content
     *
     * @var int
     */
    protected $_length = 0;


    /**
     * Initializes http-stream response
     *
     * @param string $file
     * @param array [$options]
     */
    public function __construct($file, $options = array()) {
        $this->_file = $file;

        $this->_headers = new Headers();

        $this->_process($options);
    }


    /**
     * Gets path of file
     *
     * @return string
     */
    public function getFile() {
        return $this->_file;
    }


    /**
     * Gets offset of content within file
     *
     * @return int
     */
    public function getOffset() {
        return $this->_offset;
    }

    /**
     * Gets length of content
     *
     * @return int
     */
    public function getLength() {
        return $this->_length;
    }


    /**
     * Gets headers
     *
     * @return Headers
     */
    public function getHeaders() {
        return $this->_headers;
    }

    /**
     * Gets determined response-code
     *
     * @return int
     */
    public function getResponseCode() {
        return $this->_responseCode;
    }


    /**
     * Processes request data
     *
     * @param array $options
     * @throws \Exception
     */
    public function _process($options) {

        // Get file information
        $filePath = $this->getFile();
        $fileLength = filesize($filePath);

        $ignoreErrors = FALSE;
        if (isset($options['ignoreErrors'])) {
            $ignoreErrors = $options['ignoreErrors'];
        }

            $fileMime = 'application/octet-stream';
        if (isset($options['mimeType'])) {
            $fileMime = $options['mimeType'];
        }
        if (($fileMime === NULL) && (function_exists('mime_content_type'))) {
            $fileMime = mime_content_type($filePath);
        }

        // Set-up default values
        $offsetStart = 0;
        $offsetEnd = $fileLength - 1;

        $this->_responseCode = 200;

        // Has header range-request?
        if (isset($options['HTTP_RANGE'])) {

            // Get range-data
            $range = $options['HTTP_RANGE'];

            // Break-up range data
            list($rangeType, $range) = explode('=', $range);

            // Is range-type bytes (only supported)
            if ($rangeType == 'bytes') {

                // Get range values
                list($rangeStart, $rangeEnd) = explode('-', $range);

                // Error checking
                if (($rangeStart == '') && ($rangeEnd == '')) {
                    if (!$ignoreErrors) {
                        throw new \Exception('Range cannot be missing for both attributes');
                    }
                } else if (($rangeStart < 0) || ($rangeEnd < 0)) {
                    if (!$ignoreErrors) {
                        throw new \Exception('Range numbers cannot be negative');
                    }

                } else {

                    if (($rangeStart == '') && ($rangeEnd != '')) {
                        // Only end given
                        $offsetStart = $fileLength - $rangeEnd;

                    } else if (($rangeStart != '') && ($rangeEnd == '')) {
                        // Only start given
                        $offsetStart = $rangeStart;

                    } else {
                        // Start and end given
                        $offsetStart = $rangeStart;
                        $offsetEnd = $rangeEnd;
                    }

                    // Correction
                    if ($offsetStart < 0) $offsetStart = 0;
                    if ($offsetEnd > $fileLength - 1) $offsetEnd = $fileLength - 1;

                    // Make sure again that the numbers are correct
                    if ($offsetStart > $offsetEnd) {
                        if (!$ignoreErrors) {
                            throw new \Exception('Range numbers are not valid');
                        }

                        $offsetStart = 0;
                        $offsetEnd = $fileLength - 1;
                    }
                }

                // Set response code
                $this->_responseCode = 206;

            } else {
                // Not supported range-type
                if (!$ignoreErrors) {
                    throw new \Exception('Do not support range-type '.$rangeType.' for requesting file '.$filePath);
                }
            }
        }

        $contentLength = ($offsetEnd - $offsetStart + 1);

        $headers = $this->getHeaders();

        // Tell browser that it can request also parts of the file
        $headers['Accept-Ranges'] = 'bytes';

        // Set headers
        $headers['Content-Range'] = "bytes $offsetStart-$offsetEnd/$fileLength";
        $headers['Content-Length'] = $contentLength;
        $headers['Content-Type'] = $fileMime;

        $this->_offset = $offsetStart;
        $this->_length = $contentLength;
    }


    /**
     * Gets range-content of file
     *
     * @return string
     */
    public function getContent() {

        $offsetStart = $this->getOffset();
        $contentLength = $this->getLength();

        if ($contentLength > 0) {

            // Read file and dump to output
            $fp = fopen($this->getFile(), 'r');

            if ($offsetStart > 0) fseek($fp, $offsetStart);
            $content = fread($fp, $contentLength);

            fclose($fp);

        } else {
            $content = '';
        }

        return $content;
    }


    /**
     * Flushes processed data to client
     */
    public function flush() {

        // Set response code
        $responseCode = $this->getResponseCode();
        header('X-Http-Stream-Response-Code: '.$responseCode, true, $responseCode);

        // Send header
        $this->getHeaders()->flush();

        // Send data
        echo $this->getContent();
    }
}
