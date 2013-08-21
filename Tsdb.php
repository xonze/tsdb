<?php

/**
 * tsdb 
 *
 * @package 
 * @version 0.0.1
 * @copyright 2013 The UCLOUD Group
 * @author roychen <roychad.cy7@gmail.cn> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class tsdb {
    private $_aggregation;
    private $_aggregationField  = array(
        'sum',
        'min',
        'max',
        'avg',
        'dev' 
    );
    private $_metric;
    private $_domain;

    public function __construct($domain) {
        $this->_aggregation = 'sum';
        $this->setDomain($domain);
    }
    
    /**
     * get 
     * 
     * @param mixed $startTime 
     * @param mixed $endTime 
     * @access public
     * @return void
     */
    public function get($startTime, $endTime = false) {
        $url    = $this->_spliceUrl($startTime, $endTime);

        $curl   = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
        $html = curl_exec($curl);
        curl_close($curl);

        $tmpArray    = explode("\n", $html);
     
        unset($tmpArray[count($tmpArray) - 1]);

        $res = array();
        foreach ($tmpArray as $tmp) {
            $infoArray = explode(' ', $tmp);
            $info   = array(
                'timestamp' => $infoArray[1],
                'value'     => $infoArray[2],
                'tags'      => array()
            );
            
            for ($i = 0; $i <= 2; $i++) {
                unset($infoArray[$i]);
            }
            
            foreach ($infoArray as $i) {
                $tmp    = explode('=', $i);
                array_push($info['tags'], array(
                    'key'   => $tmp[0],
                    'value' => $tmp[1]
                ));
            }
     
            array_push($res, $info);
        }

        return $res;
    }

    public function setMetric($metric) {
        $this->_metric = $metric;
        return $this->_metric;
    }
    
    public function setTag($tags = array()) {
        $this->_tags = $tags;
        return $this->_tags;
    }

    public function setAggregation($aggregation) {
        $this->_aggregation = in_array($aggregation, $this->_aggregationField) ? $aggregation : 'sum'; 
        return $this->_aggregation;
    }

    public function setDomain($url) {
        $this->_domain  = preg_match('/http/i', $url) ? $url : 'http://'.$url;
        $this->_domain .= substr($this->_domain, -1, 1) === '/' ? 'q?': '/q?';
    }

    /**
     * _isSetMetric 
     * 
     * @access private
     * @return boolean 
     */
    private function _isSetMetric() {
        return !($this->_metric === false || $this->_metric === null);
    }

    /**
     * _isSetAggregation 
     * 
     * @access private
     * @return boolean 
     */
    private function _isSetAggregation() {
        return !($this->_aggregation === false || $this->_aggregation === null);
    }
    
    private function _spliceUrl($startTime, $endTime = false) {
        // Start and End
        $url = $this->_domain.'start='.$startTime;
        if ($endTime !== false) {
            $url .= '&end='.$endTime;
        }

        // Metric and Tag
        $url .= '&m='.$this->_aggregation.':'.$this->_metric.'{}&ascii';
        return $url;
    }
}

// END Tsdb Class
/* End of file Tsdb.php */
