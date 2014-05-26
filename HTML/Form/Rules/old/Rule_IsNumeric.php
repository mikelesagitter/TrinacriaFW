<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

// Based on native PHP Function is_numeric
class TrinacriaForm_RuleIsNumeric extends TrinacriaForm_Rule {
	private $target;
	private $msg;
	
	public function __construct($target, $trash, $msg = '') {
		if(!is_object($target)) {
			throw new Exception('Can\'t build Rule "Is Numeric" : target is not an object');
		} else {
			if(empty($msg)) $msg = 'Valeur non numérique';
			
			$this->target = $target;
			$this->msg = $msg;
		}
	}

	public function execute() {
		$a = $this->target->getValue();
		//debug($a, 3);
		if(is_numeric($a)) {
			return true;
		} else {
			return false;
		}
	}

	public function getName() {
		return 'isNumeric';
	}

	public function getErrorMsg() {
		return $this->msg;
	}

	public function __destruct() {
	}
}
?>
