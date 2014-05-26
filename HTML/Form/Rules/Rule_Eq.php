<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaForm_RuleEq extends TrinacriaForm_Rule {
    private $target;
    private $compare;
    private $msg;
    
    public function __construct($target, $compareTo, $options = array()) {
        if(!is_object($target)) {
            throw new Exception('Can\'t build Rule "Eq" : target is not an object');
        } else {
            if(empty($msg)) $msg = 'Erreur de validation';
            
            $this->target = $target;
            $this->compare = $compareTo;
            $this->msg = !isset($options['msg']) ? 'Erreur de validation' : $options['msg'];
        }
    }

    public function execute() {
        if(is_object($this->compare)) {
            return ($this->target->getValue() === $this->compare->getValue());
        } else {
            return ($this->target->getValue() === $this->compare);
        }
    }

    public function getName() {
        return 'eq';
    }

    public function getErrorMsg() {
        return $this->msg;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
