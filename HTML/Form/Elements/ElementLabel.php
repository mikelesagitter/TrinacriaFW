<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once  'HTML/TrinacriaForm/Elements/Element.php';

class TrinacriaForm_ElementLabel extends TrinacriaForm_Element {
    private $value;
    private $target;
    private $options;

    static public function createLabel($datas = array()) {
        $value = null;
        $target = null;
        $options = null;

        if(array_key_exists('value', $datas)) $value = $datas['value'];
        if(array_key_exists('target', $datas)) $target = $datas['target'];
        if(array_key_exists('options', $datas)) $options = $datas['options'];

        return new TrinacriaForm_ElementLabel($value, $target, $options);
    }

    public function __construct($value, $target = null, $options = null) {
        parent::__construct();

        $this->value = $value;
        $this->target = $target;

        if(is_array($options) && isset($options['label'])) {
            unset($options['label']);
        }

        $this->options = $options;
    }

    public function hasTarget() {
        return !empty($target);
    }

    public function concatValue($str) {
        $this->value .= $str;
        return $this;
    }

    // do nothing
    public function updateValue() {
    }

    //
    // Getters / Setters
    //

    public function setTarget($target) {
        $this->target = $target;
    }

    public function getValueStr() {
        return $this->value;
    }

    // do nothing
    public function getValue() {
        return null;
    }

    public function __toString() {
        $a = '<label';
        if(is_object($this->target)) {
            if(in_array('required', $this->target->getRules())) {
                if(!empty($this->options['class'])) {
                    $this->options['class'] .= ' required';
                } else {
                    $this->options['class'] = 'required';
                }
            }

            $target = $this->target->getId();
            if(ctype_digit($target) || is_int($target)) {
                $target = $this->target->getNodeType().'-'.$target;
            }

            $a .= ' for="'.$target.'" id="label-'.$target.'"';
        }

        if(!empty($this->options)) {
            foreach($this->options as $attribute => $value) {
                $a .= ' '.$attribute.'="'.$value.'"';
            }
        }

        return ($a .= '>'.$this->value.'</label>');
    }

    public function __destruct() {
        parent::__destruct();

        if(is_object($this->target)) {
            $this->target->unsetLabel();
        }
    }
}
?>
