<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaForm_RuleFunction extends TrinacriaForm_Rule {
    private $target;
    private $func;
    private $msg;
    
    public function __construct($target, $function, $options = array()) {
        if(is_array($function)) {
            $f = explode('::', $function[0]);
        } else {
            $f = explode('::', $function);
        }
        
        $nb = count($f) ;

        if(!is_object($target) || $nb > 2 || (1 == $nb && !function_exists($f[0])) || (2 == $nb && !method_exists($f[0], $f[1]))) {
            throw new Exception('Can\'t build Rule "Function" : target is not an object or function doesn\'t exist.');
        } else {
            if(empty($msg)) $msg = 'Erreur de validation';
            
            $this->target = $target;
            $this->func = $function;
            $this->msg = !isset($options['msg']) ? 'Erreur de validation' : $options['msg'];
        }
    }

    public function execute() {
        if(is_array($this->func)) {
            // get parameters
            //debug('$this->func');
            //debug($this->func,3);
            $params = array_slice($this->func, 1);
            //debug('params slice');
            //debug($params,3);
            // set value to the first parameters
            array_unshift($params, $this->target->getValue());
            //debug('params array_unshift');
            //debug($params,3);

            //debug('Rule Function with parameters');
            //debug('function : '.$this->func[0]);
            //debug('params : ');
            //debug($this->target->getValue(),3);
            return call_user_func_array($this->func[0], $params);
        } else {
            //debug('Rule Function without parameters');
            //debug('function : '.$this->func);
            //debug('params : ');
            //debug($this->target->getValue(),3);
            return call_user_func($this->func, $this->target->getValue());
        }
    }

    public function getName() {
        return 'function';
    }

    public function getErrorMsg() {
        return $this->msg;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
