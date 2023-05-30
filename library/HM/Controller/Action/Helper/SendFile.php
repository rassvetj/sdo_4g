<?php
 
/**
* Sends a file for download
*
* @category Noginn
* @copyright Copyright (c) 2009 Tom Graham (http://www.noginn.com)
* @license http://www.opensource.org/licenses/mit-license.php
*/
class HM_Controller_Action_Helper_SendFile extends Zend_Controller_Action_Helper_Abstract
{

    private $_df = 'D, d M Y H:i:s';

    /**
* Set cache headers
*
* @param array $options
*/
    public function setCacheHeaders($options)
    {
        $response = $this->getResponse();

        $cacheControl = array();
        if (isset($options['public']) && $options['public']) {
            $cacheControl[] = 'public';
        }
        if (isset($options['private']) && $options['private']) {
            $cacheControl[] = 'private';
        }
        if (isset($options['no-cache']) && $options['no-cache']) {
            $cacheControl[] = 'no-cache';
        }
        if (isset($options['no-store']) && $options['no-store']) {
            $cacheControl[] = 'no-store';
        }
        if (isset($options['must-revalidate']) && $options['must-revalidate']) {
            $cacheControl[] = 'must-revalidate';
        }
        if (isset($options['proxy-validation']) && $options['proxy-validation']) {
            $cacheControl[] = 'proxy-validation';
        }
        if (isset($options['max-age'])) {
            $cacheControl[] = 'max-age=' . (int) $options['max-age'];
            $response->setHeader('Expires', gmdate($this->_df, time() + $options['max-age']).' GMT', true);
        }
        if (isset($options['s-maxage'])) {
            $cacheControl[] = 's-maxage=' . (int) $options['s-maxage'];
        }

        $response->setHeader('Cache-Control', implode(', ', $cacheControl), true);
        if (isset($options['private']) && $options['private']) {
            $response->setHeader('Pragma', 'private', true);
        } else {
            $response->setHeader('Pragma', 'public', true);
        }
    }

    /**
* Validate the cache using the If-Modified-Since request header
*
* @param int $modified When the file was last modified as a unix timestamp
* @return bool
*/
    public function notModifiedSince($modified)
    {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $modified <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            // Send a 304 Not Modified header
            $response = $this->getResponse();
            $response->setHttpResponseCode(304);
            $response->sendHeaders();
            return true;
        }

        return false;
    }

    /**
* Send a file for download
*
* @param string $path Path to the file
* @param string $type The mime-type of the file
* @param array $options
* @return bool Whether the headers and file were sent
*/
    public function sendFile($path, $type, $options = array())
    {
        $response = $this->getResponse();
        $isRangeRequest = isset($_SERVER['HTTP_RANGE']);

        if (!is_readable($path) || !$response->canSendHeaders()) {
            return false;
        }

        // Set the cache-control
        if (isset($options['cache'])) {
            $this->setCacheHeaders($options['cache']);
        }
        else{
            $this->setCacheHeaders(array('public' => 1, 'max-age' => 1));
        }

        // Get the last modified time
        if (isset($options['modified'])) {
            $modified = (int) $options['modified'];
        } else {
            $modified = filemtime($path);
        }

        // Validate the cache
        if (!isset($options['cache']['no-store']) && $this->notModifiedSince($modified)) {
            return true;
        }

        // Set the file name
        if (isset($options['filename']) && !empty($options['filename'])) {
            $filename = $options['filename'];
        } else {
            $filename = basename($path);
        }

        // Set the content disposition
        if (isset($options['disposition']) && $options['disposition'] == 'inline') {
            $disposition = 'inline';
        } else {
            $disposition = 'attachment';
        }

        if ($isRangeRequest) {
            $response->setHttpResponseCode(206);
        } else {
            $response->setHttpResponseCode(200);
        }
        $response->setHeader('Content-Type', $type, true);
        $filename = (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']))? rawurlencode($filename) : $filename;
        $response->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"', true);

        // Do we want to use the X-Sendfile header or stream the file
        if (isset($options['xsendfile']) && $options['xsendfile']) {
            $response->setHeader('X-Sendfile',  implode("/", array_map("rawurlencode", explode("/", $path))));
            $response->sendHeaders();
            return true;
        }

        if (isset($options['buffer_size']) &&  is_numeric($options['buffer_size']) && $options['buffer_size'] > 0) {
             $buffer_size = (int)$options['buffer_size'];
        } else {
            $buffer_size = 4096;
        }

        // It can take looong time
        set_time_limit(0);

        $file_size = filesize($path);
        $response->setHeader('Last-Modified', gmdate($this->_df, $modified).' GMT', true);
        $response->setHeader('Content-Transfer-Encoding', 'binary', true);
        $response->setHeader('Accept-Ranges', 'bytes', true);
        $response->setHeader('Date', gmdate($this->_df, time()).' GMT', true);
        $ranges = array();
        if ($isRangeRequest) {
            $ranges = explode('=', $_SERVER['HTTP_RANGE'], 2);
            $ranges = explode(',', $ranges[1]);
            $temp_ranges = array();
            // care only about first range spec
            // TODO: multiple ranges
            //       see https://github.com/balupton/balphp/blob/master/lib/core/functions/_files.funcs.php
            //       http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
            //       we should send HTTP as multipart/byteranges
            foreach ($ranges as $range) {
                $range = explode('-', $ranges[0]);
                if ( !empty($range[0]) && is_numeric($range[0]) ) {
                    // The range has a start
                    $range_start = intval($range[0]);
                } else {
                    $range_start = 0;
                }
                if ( !empty($range[1]) && is_numeric($range[1]) ) {
                    // The range has an end
                    $range_end = intval($range[1]);
                } else {
                    $range_end = $file_size - 1;
                }
                if ($range_end > $file_size - 1) {
                    $range_end = $file_size - 1;
                }
                if ($range_start < 0) {
                    $range_start = 0;
                }
                $temp_ranges[] = array($range_start, $range_end);
            }
            $ranges = $temp_ranges;
        }
        if (empty($ranges)) {
            $ranges[] = array( 0, $file_size - 1);
        }
        $response->setHeader('Content-Length', (($ranges[0][1] - $ranges[0][0]) + 1), true);
        if ($isRangeRequest) {
            $response->setHeader('Content-Range', "bytes $range_start-$range_end/$file_size", true);
        }
        $response->sendHeaders();

        $file_descriptor = fopen($path, 'rb');

        foreach ($ranges as $range) {
            $range_start = $range[0];
            $range_end   = $range[1];
            $current = $range_start;
            fseek($file_descriptor, $range_start, 0);
            while (!feof($file_descriptor) && $curent <= $range_end && (connection_status() == 0)) {
                print fread($file_descriptor, min($buffer_size, ($range_end - $curent) + 1));
                flush();
                ob_flush();
                $curent += $buffer_size;
            }
            // TODO: right now only first byte range
            break;
        }

        return true;
    }

    /**
* Send file data as a download
*
* @param string $path Path to the file
* @param string $type The mime-type of the file
* @param string $filename The filename to send the file as, if null then use the base name of the path
* @param array $options
* @return bool Whether the headers and file were sent
*/
    public function sendData($data, $type, $filename, $options = array())
    {
        $response = $this->getResponse();

        if (!$response->canSendHeaders()) {
            return false;
        }

        // Set the cache-control
        if (isset($options['cache'])) {
            $this->setCacheHeaders($options['cache']);
        }
        
        if (isset($options['modified'])) {
            // Validate the cache
            if (!isset($options['cache']['no-store']) && $this->notModifiedSince($options['modified'])) {
                return true;
            }
            
            $response->setHeader('Last-Modified', gmdate('r', $options['modified']), true);
        }
 
        // Set the content disposition
        if (isset($options['disposition']) && $options['disposition'] == 'inline') {
            $disposition = 'inline';
        } else {
            $disposition = 'attachment';
        }
        
        $response->setHttpResponseCode(200);
        $response->setHeader('Content-Type', $type, true);
        $response->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"', true);
        $response->setHeader('Content-Length', strlen($data), true);
        $response->sendHeaders();
 
        echo $data;
        return true;
    }
 
    /**
* Proxy method for sendFile
*
* @param string $path Path to the file
* @param string $type The mime-type of the file
* @param array $options
* @return bool Whether the headers and file were sent
*/
    public function direct($path, $type, $options = array())
    {
        return $this->sendFile($path, $type, $options);
    }
}
 