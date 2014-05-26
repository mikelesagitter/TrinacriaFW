<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

abstract class TrinacriaForm_Rule {
    abstract public function execute();
    abstract public function getName();
    abstract public function getErrorMsg();

    public function __destruct() {
    }
}
?>
