<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

// Based on native PHP Function is_int/is_integer
class TrinacriaForm_RuleRegex_IsInteger extends TrinacriaForm_Rule {
	private $target;
	private $msg;
	private $direction;
	
	public function __construct($target, $direction = null, $msg = '') {
		if(!is_object($target)) {
			throw new Exception('Can\'t build Rule "Is Numeric" : target is not an object');
		} else {
			if(empty($msg)) $msg = 'Valeur non entière';
			
			$this->target = $target;
			$this->msg = $msg;
			$this->direction = $direction;
		}
	}

	public function execute() {
		$a = $this->target->getValue();
		$r = false;
		
		if(is_int($a)) {
			switch($this->direction) {
				case '>=':
					$r = ($a >= 0);
				break;
				
				case '>':
					$r = ($a > 0);
				break;
				
				case '<=':
					$r = ($a <= 0);
				break;
				
				case '<':
					$r = ($a < 0);
				break;
				
				default:
					$r = true;
				break;
			}
		} else {
			switch($this->direction) {
				case '>=':
					$r = preg_match('#^([1-9][0-9]*|0)$#', $a);
				break;
				
				case '>':
					$r = preg_match('#^[1-9][0-9]*$#', $a);
				break;
				
				case '<=':
					$r = preg_match('#^(-[1-9][0-9]*|0)$#', $a);
				break;
				
				case '<':
					$r = preg_match('#^-[1-9][0-9]*$#', $a);
				break;
				
				default:
					$r = preg_match('#^(-?[1-9][0-9]*|0)$#', $a);
				break;
			}
		}

		return $r;
	}

	public function getName() {
		return 'regex_IsInteger';
	}

	public function getErrorMsg() {
		return $this->msg;
	}

	public function __destruct() {
	}
}
?>
