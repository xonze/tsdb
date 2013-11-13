<?php

/**
 * Point 
 * 
 * @package 
 * @version $id$
 * @copyright 2013 
 * @author roychen <roychad.cy7@gmail.com> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class Point {
    // Attributs
    private $_value;
    private $_timestamp;

    /**
     * __construct 
     * 
     * @param mixed $value 
     * @param int $timestamp 
     * @access public
     * @return void
     */
    public function __construct($value, $timestamp) {
        $this->setValue($value);
        $this->setTimestamp($timestamp);
    }

    /**
     * getTimestamp 
     * 
     * @access public
     * @return int
     */
    public function getTimestamp() {
        return $this->_timestamp;
    }

    /**
     * getValue 
     * 
     * @access public
     * @return string
     */
    public function getValue() {
        return $this->_value;
    }

    /**
     * setTimestamp 
     * 
     * @param int $timestamp 
     * @access public
     * @return boolean
     */
    public function setTimestamp($timestamp) {
        if (preg_match("/^([1-9]\d{0,})$/", $timestamp) === true) {
            $this->_timestamp = $timestamp;
            return true;
        } else {
            return false;
        }
    }

    /**
     * setValue 
     * 
     * @param mixed $value 
     * @access public
     * @return boolean
     */
    public function setValue($value) {
        $this->_value = $value;
        return true;
    }
}
