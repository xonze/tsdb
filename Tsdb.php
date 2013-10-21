<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * tsdb 
 * 
 * Please set Metric first
 * Do not support tags this version
 *
 * @package 
 * @version 0.0.3
 * @copyright 2013 The UCLOUD Group
 * @author roychen <roychad.cy7@gmail.cn> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class tsdb {
    // Attributs
    private $_aggregation;
    private $_aggregationField;
    private $_metric;
    private $_tags;
    private $_url;

    /**
     * __construct 
     * 
     * @param string $metric 
     * @param array $tags 
     * @param string $aggregation 
     * @access public
     * @return void
     */
    public function __construct($metric = '', $tags = array(), $aggregation = 'sum') {
        // Set attributes 
        // Will not change
        $this->_aggregationField = array(
            'sum',
            'min',
            'max',
            'avg',
            'dev' 
        );

        $this->_url = 'http://172.17.130.4:4242/q?';
        
        // Init attributes
        $this->setMetric($metric);
        $this->setTags($tags);
        $this->setAggregation($aggregation);
    
        return;
    }

    /**
     * get 
     * 
     * @param int $startTime 
     * @param int $endTime 
     * @access public
     * @return array
     */
    public function get($startTime, $endTime = false) {
        // Check $startTime and $endTime
        if ($this->_isTimestamp($startTime) === false
         || $this->_isTimestamp($endTime) === false) {
            return array();    
        }

        // Check metric and aggregation
        if ($this->_isSetMetric() === false
         || $this->_isSetAggregation() === false) {
            return array();
        }

        // Get url
        $url    = $this->_spliceUrl($startTime, $endTime);

        $curl   = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
        $html = curl_exec($curl);
        curl_close($curl);
        
        return $this->_dealCurlInfo($html, $startTime, $endTime);
    }
    
    /*--------------------------------------------Begin Set------------------------------------------*/

    /**
     * setMetric 
     * 
     * @param string $metric 
     * @access public
     * @return boolean
     */
    public function setMetric($metric) {
        // Check if $metric is string
        if (is_string($metric)) {
            $this->_metric = $metric;
            return true;
        } else {
            return false;
        }
    }

    /**
     * setTags 
     * 
     * @param array $tags 
     * @access public
     * @return boolean
     */
    public function setTags($tags) {
        // Check if $tags is not array
        if (is_array($tags) === false) {
            return false;
        }

        $_tagsTmp   = $this->_tags;

        // Init $this->_tags
        $this->_tags    = array();
        foreach ($tags as $tag) {
            if (!isset($tag['name']) 
             || is_string($tag['name']) === false  
             || !isset($tag['value'])) {
                // If tag format error
                // revent $this->_tags
                $this->_tags = $_tagsTmp;
                return false;
            }
            
            $this->_tags[$tag['name']] = $tag['value'];
        }

        return true;
    }

    /**
     * setAggregation 
     * 
     * @param string $aggregation 
     * @access public
     * @return boolean
     */
    public function setAggregation($aggregation) {
        // Check if $aggregation is string and in $this->_aggregationField
        if (is_string($aggregation) && in_array($aggregation, $this->_aggregationField)) {
            $this->_aggregation = $aggregation;
            return true;
        } else {
            return false;
        }
    }
    
    /*---------------------------------------------End Set-------------------------------------------*/
    
    /*--------------------------------------------Begin Get------------------------------------------*/
    
    /**
     * getMetric 
     * 
     * @access public
     * @return string
     */
    public function getMetric() {
        return $this->_metric;
    } 

    /**
     * getTags 
     * 
     * @access public
     * @return array
     */
    public function getTags() {
        return $this->_tags;
    }

    /**
     * getAggregation 
     * 
     * @access public
     * @return string
     */
    public function getAggregation() {
        return $this->_aggregation;
    }
    
    /*---------------------------------------------End Get-------------------------------------------*/
    

    /**
     * _spliceUrl 
     * 
     * @param string $startTime 
     * @param string $endTime 
     * @access private
     * @return string
     */
    private function _spliceUrl($startTime, $endTime = false) {
        // Start and End
        $url = $this->_url.'start='.$startTime;
        if ($endTime !== false) {
            $url .= '&end='.$endTime;
        }

        // Set aggregation and metric
        $url .= '&m='.$this->_aggregation.':'.$this->_metric.'{';

        // Set tags
        foreach ($this->_tags as $key => $tag) {
            $url .= $key.'='.$tag.',';
        } 
        
        $url = substr($url, 0, strlen($url)-1);
        // Add ascii
        $url .= '}&ascii';
        return $url;
    }

    /**
     * _dealCurlInfo 
     * 
     * @param string $html 
     * @access private
     * @return array
     */
    private function _dealCurlInfo($html, $startTime, $endTime = false) {
        // Check input
        if (is_string($html) === false
         || is_numeric($startTime) === false
         || ($endTime !== false && is_numeric($endTime) === false)) {
            return array();
        }
        
        // Explode $html by "\n"
        $tmpArray    = explode("\n", $html);
        
        unset($tmpArray[count($tmpArray) - 1]);

        $res = array();
        foreach ($tmpArray as $tmp) {
            $infoArray = explode(' ', $tmp);

            // Error row
            if (isset($infoArray[1]) === false
             || isset($infoArray[2]) === false) {
                continue; 
            }

            // Data out of range
            if ($infoArray[1] < $startTime
             || ($endTime !== false && $infoArray[1] > $endTime)) {
                continue; 
            }
            
            // Push data
            $info   = array(
                'timestamp' => $infoArray[1],
                'value'     => $infoArray[2],
            );
            
            array_push($res, $info);
        }

        return $res;
    }


    /*-----------------------------------------Check Function----------------------------------------*/
    
    /**
     * _isSetMetric 
     * 
     * @access private
     * @return boolean 
     */
    private function _isSetMetric() {
        return strlen($this->_metric) > 0;
    }

    /**
     * _isSetAggregation 
     * 
     * @access private
     * @return boolean 
     */
    private function _isSetAggregation() {
        return strlen($this->_aggregation) > 0;
    }

    /**
     * isTimestamp 
     *
     * Check the $timestamp is valid or not
     *
     * @param string $timestamp 
     * @access private
     * @return boolean
     */
    private function _isTimestamp($timestamp) {
        return preg_match("/^([1-9]\d{0,})$/", $timestamp);
    }
}
// END Tsdb Class

/* End of file Tsdb.php */
/* Location: ./system/libraries/Tsdb.php */
