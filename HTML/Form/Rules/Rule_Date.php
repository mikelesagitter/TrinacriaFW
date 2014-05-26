<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaForm_RuleDate extends TrinacriaForm_Rule {
    private $elements;
    private $msg;
    private $canBeEmpty;
    
    public function __construct($trash, $elements = array(), $options = array()) {
        if(    !is_array($elements) || empty($elements)
            || count($elements) > 4 || count($elements) < 3
            || !array_key_exists('d', $elements)
            || !array_key_exists('m', $elements)
            || !array_key_exists('y', $elements)
        ) {
            throw new Exception('Can\'t build Rule "Date" : DateElements missing or are incorrect');
        } else {
            $this->msg = !isset($options['msg']) ? 'Erreur de validation' : $options['msg'];
            $this->elements = $elements;

            if(array_key_exists('empty', $this->elements)) {
                $this->canBeEmpty = $this->elements['empty'];
                unset($this->elements['empty']);
            }
        }
    }

    public function execute() {
        $m = $this->elements['m'];
        $d = $this->elements['d'];
        $y = $this->elements['y'];

        if(is_object($m)) $m = $m->getValue();
        if(is_object($d)) $d = $d->getValue();
        if(is_object($y)) $y = $y->getValue();

        //debug('$d');
        //debug($d,3);
        //debug('$m');
        //debug($m,3);
        //debug('$y');
        //debug($y,3);
        //debug('checkdate($m, $d, $y) : ');

        if($this->canBeEmpty && empty($m) && empty($d) && empty($y)) {
            return true;
        } else if(!empty($m) && !empty($d) && !empty($y)) {
            //debug(checkdate($m, $d, $y), 3);
            return checkdate($m, $d, $y);
        } else {
            return false;
        }
    }

    public function getName() {
        return 'date';
    }

    public function getErrorMsg() {
        return $this->msg;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
