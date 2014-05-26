<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaForm_RuleRegex extends TrinacriaForm_Rule {
    private $target;
    private $pattern;
    private $msg;

    public function __construct($target, $pattern = '', $options = array()) {
        if(!is_object($target) || empty($pattern)) {
            throw new Exception('Can\'t build Rule : target is not an object');
        } else {
            if(empty($msg)) $msg = 'Erreur de validation';

            $this->target = $target;
            $this->pattern = $pattern;
            $this->msg = !isset($options['msg']) ? 'Erreur de validation' : $options['msg'];
        }
    }

    public function execute() {
        return preg_match($this->pattern, $this->target->getValue());
    }

    public function getName() {
        return 'regex';
    }

    public function getErrorMsg() {
        return $this->msg;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
