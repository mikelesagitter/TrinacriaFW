<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaForm_RuleDepend extends TrinacriaForm_Rule {
    private $on;
    private $what;
    private $targetValue;
    private $msg;
    
    public function __construct($trash, $params = array(), $options = array()) {
        //debug('RuleDepend::__construct() ; START');
        if(    empty($params)
            || count($params) != 3
            || empty($params['on'])
            || !is_object($params['on'])
            || empty($params['what'])
            || !isset($params['value'])
            || !preg_match('#(statut|value|function:)#i', $params['what'])
            ) {
            throw new Exception('Can\'t build Rule "Depend" : params wrong. "On" must be on object. "what" => "statut" | "value". "value" must be filled');
        }

        if(preg_match('#^function:#', $params['what'])) {
            $f = explode(':',$params['what']);
            if(!function_exists($f[1])) {
                throw new Exception('Can\'t build Rule "Depend" : params wrong. "what" is not a function');
            } else {
                $this->what = $f[1];
            }
        } else {
            $this->what = $params['what'];
        }

        $this->on = $params['on'];
        $this->targetValue = $params['value'];
        $this->msg = !isset($options['msg']) ? 'Erreur de validation' : $options['msg'];

        //debug('RuleDepend::__construct() ; $this->on : ');
        //debug($this->on, 3);
        //debug('RuleDepend::__construct() ; $this->targetValue : ');
        //debug($this->targetValue, 3);

        //debug('RuleDepend::__construct() ; END');
    }
    
    public function execute() {
        //debug('RuleDepend->execute() ; START');
        
        switch($this->what) {
            case 'statut':
                //debug('RuleDepend->execute() ; Rule depend on statut');
                $value = $this->on->check();
                break;
            case 'value':
                //debug('RuleDepend->execute() ; Rule depend on value');
                $value = $this->on->getValue();
                break;
            default:
                //debug('RuleDepend->execute() ; Rule depend on function result');
                $value = call_user_func($this->what, $this->on->getValue());
        }

        //debug('RuleDepend->execute() ; value :');
        //debug($value, 3);

        //debug('RuleDepend->execute() ; targetValue :');
        //debug($this->targetValue, 3);

        if($value == $this->targetValue) {
            //debug('RuleDepend->execute() ; VALID');
            return true;
        } else {
            //debug('RuleDepend->execute() ; !VALID');
            return false;
        }

        //debug('RuleDepend->execute() ; END');
    }

    public function getName() {
        return 'depend';
    }

    public function getErrorMsg() {
        return $this->msg;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
