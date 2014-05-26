<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaRequest {
    const ALLOW_EMPTY = true;
    const DISALLOW_EMPTY = false;

    const METHOD_GET = 10;
    const METHOD_POST = 20;
    const METHOD_FILES = 30;

    static public $methodsAsStr =  array(
        self::METHOD_GET => 'get',
        self::METHOD_POST => 'post',
        self::METHOD_FILES => 'files'
    );
    
    static public function getVar($name, $method = self::METHOD_GET,
        $allowEmpty = false) {
        $method = strtoupper($method);

        if(empty($name)) {
            debug(__METHOD__.' $name parameter is empty');
            return null;
        }

        if(empty($method)) {
            debug(__METHOD__.' $method parameter is empty');
            return null;
        }

        $methods = array(
            self::METHOD_GET => 'TrinacriaRequest::METHOD_GET',
            self::METHOD_POST => 'TrinacriaRequest::METHOD_POST',
            self::METHOD_FILES => 'TrinacriaRequest::METHOD_FILES'
        );
        
        if(!array_key_exists($method, $methods)) {
            debug(
                __METHOD__.' $method parameter value has wrong value ;'
                .' must be one of followings : '.implode(', '.$methods)
            );
            return null;
        }

        switch($method) {
        case self::METHOD_GET:
            $from = &$_GET;
            break;
        case self::METHOD_POST:
            $from = &$_POST;
            break;
        case self::METHOD_FILES:
            $from = &$_FILES;
            break;
        default:
            $from = null;
            break;
        }

        if( !empty($from)
            && (($allowEmpty && array_key_exists($name, $from))
            || !empty($from[$name]) )) {
            return $from[$name];
        } else {
            return null;
        }
    }
}
?>
