<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

// Based on native PHP Function is_float/is_real
class TrinacriaForm_RuleIsFloat extends TrinacriaForm_Rule {
	private $target;
	private $msg;
	
	public function __construct($target, $trash, $msg = '') {
		if(!is_object($target)) {
			throw new Exception('Can\'t build Rule "IsFloat" : target is not an object');
		} else {
			if(empty($msg)) $msg = 'Champ obligatoire';
			
			$this->target = $target;
			$this->msg = $msg;
		}
	}

	public function execute() {
		$a = $this->target->getValue();
		//debug($a, 3);
		if(is_float($a)) {
			return true;
		} else {
			return false;
		}
	}

	public function getName() {
		return 'isFloat';
	}

	public function getErrorMsg() {
		return $this->msg;
	}

	public function __destruct() {
	}
}
?>
