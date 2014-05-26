<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaForm_RuleRequired extends TrinacriaForm_Rule {
    private $target;
    private $msg;
    private $star;
    
    public function __construct($target, $trash, $options = array()) {
        if(!is_object($target)) {
            throw new Exception('Can\'t build Rule "Required" : target is not an object');
        } else {
            $this->target = $target;
            $this->msg = !isset($options['msg']) ? 'Champ obligatoire' : $options['msg'];
            $this->star = !isset($options['star']) ? ' *' : $options['star'];

            if($this->target->getParent() != null) {
                if($this->target->getParent()->getOption('requiredStar') == 'field') {
                    $this->target->addToLabel($this->star);
                }
            }
        }
    }

    public function execute() {
        $a = $this->target->getValue(TrinacriaForm::RULE_EXECUTION);
        if($a === null || $a == '') {
            return false;
        } else {
            return true;
        }
    }

    public function getName() {
        return 'required';
    }

    public function getErrorMsg() {
        return $this->msg;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
