<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

// Based on native PHP Function is_float/is_real
class TrinacriaForm_RuleRegex_IsFloat extends TrinacriaForm_Rule {
	private $target;
	private $msg;
	private $direction;
	
	public function __construct($target, $direction = null, $msg = '') {
		if(!is_object($target)) {
			throw new Exception('Can\'t build Rule "Is Numeric" : target is not an object');
		} else {
			if(empty($msg)) $msg = 'Valeur non decimale';
			
			$this->target = $target;
			$this->msg = $msg;
			$this->direction = $direction;
		}
	}

	public function execute() {
		$a = $this->target->getValue();
		$r = false;
		
		if(is_float($a)) {
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
			//([0-9]*[\.][0-9]+)|([0-9]+[\.][0-9]*)
			switch($this->direction) {
				case '>':
					$r = preg_match('#^(0|[1-9]{1}[0-9]{0,})((,|\.)[0-9]{1,}){0,1}$#', $a);
				break;
					
				case '>=':
					$r = preg_match('#^(0|((0|[1-9]{1}[0-9]{0,})((,|\.)[0-9]{1,}){0,1}))$#', $a);
				break;
				
				case '<':
					$r = preg_match('#^-(0|[1-9]{1}[0-9]{0,})((,|\.)[0-9]{1,}){0,1}$#', $a);
				break;
				
				case '<=':
					$r = preg_match('#^(0|((0|-[1-9]{1}[0-9]{0,})((,|\.)[0-9]{1,}){0,1}))$#', $a);
				break;
				
				default:
					$r = preg_match('#^(-)?(0|([1-9]{1}[0-9]{0,}))((,|\.)[0-9]{1,}){0,1}$#', $a);
				break;
			}
		}

		return $r;
	}

	public function getName() {
		return 'regex_IsFloat';
	}

	public function getErrorMsg() {
		return $this->msg;
	}

	public function __destruct() {
	}
}
?>
