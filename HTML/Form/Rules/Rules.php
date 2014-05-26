<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require 'HTML/TrinacriaForm/Rules/Rule.php';
require 'HTML/TrinacriaForm/Rules/Rule_Date.php';
require 'HTML/TrinacriaForm/Rules/Rule_Depend.php';
require 'HTML/TrinacriaForm/Rules/Rule_Eq.php';
require 'HTML/TrinacriaForm/Rules/Rule_Function.php';
//require 'HTML/TrinacriaForm/Rules/old/Rule_IsNumeric.php';
//require 'HTML/TrinacriaForm/Rules/old/Rule_IsInteger.php';
//require 'HTML/TrinacriaForm/Rules/old/Rule_IsFloat.php';
require 'HTML/TrinacriaForm/Rules/Rule_Regex.php';
//require 'HTML/TrinacriaForm/Rules/old/Rule_Regex_IsInteger.php';
//require 'HTML/TrinacriaForm/Rules/old/Rule_Regex_IsFloat.php';
require 'HTML/TrinacriaForm/Rules/Rule_Required.php';

class TrinacriaForm_Rules {
    static private $rules = array(
        'depend',
        'required',
        'function',
        'date',
        'empty',
        'eq',
        //'isNumeric',
        //'isInteger',
        //'isFloat',
        'nonempty',
        'regex',
        //'regex_IsInteger',
        //'regex_IsFloat'
    );

    static public function isRule($rule) {
        if(in_array($rule, self::$rules)) {
            return true;
        } else {
            return false;
        }
    }
}
?>

