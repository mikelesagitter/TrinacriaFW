<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaHTML {
    private $elements;
    
    private function __construct() {
        $this->elements = array();
    }

    static public function getInstance() {
        if('' === session_id()) {
            throw new Exception('Session is not started. Cant\'t build '
                .__CLASS__);
        } else {
            if(empty($_SESSION[__CLASS__])) {
                $_SESSION[__CLASS__] = new TrinacriaHTML();
            }
        }

        return $_SESSION[__CLASS__];
    }

    public function set($k,$v) {
        $this->elements[$k] = $v;
    }

    public function get($k, $defaultValue = null) {
        if(array_key_exists($k,$this->elements)) {
            return $this->elements[$k];
        } else {
            return $defaultValue;
        }
    }

    static function htmlDisplayMsg($msg, $type) {
        if(!empty($msg)) {
            echo '<div class="'.$type.'">';
            if(is_array($msg)) {
                echo '<ul>';
                foreach($msg as $txt) {
                    echo '<li>'.$txt.'</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>'.$msg.'</p>';
            }
            echo '</div>';
        }
    }
}
?>
