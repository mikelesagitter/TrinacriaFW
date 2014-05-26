<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'HTML/TrinacriaForm/Elements/ElementInput.php';
require_once 'HTML/TrinacriaForm/Elements/ElementLabel.php';
// TODO : check options
class TrinacriaForm_ElementInput_File extends TrinacriaForm_ElementInput {
    private $hiddenValue = null;

    public function __construct($name, $label = null, $id = '', $value = '', $options = null) {
        // Delete label in options if is already set in $label
        if(!empty($label)) {
            if(is_array($options) && isset($options['label'])) {
                unset($options['label']);
            }
        }

        if(!empty($options['class'])) {
            $options['class'] .= ' file';
        } else {
            $options['class'] = 'file';
        }

        parent::__construct('file', $name, $id, $value, $options);

        if(!empty($label)) {
            $this->label = new TrinacriaForm_ElementLabel($label, $this, '');
        }

        $this->hiddenValue = null;
    }

    public function updateValue() {
        //debug('ElementInput_File->updateValue() ; START');
        $var = TrinacriaRequest::getVar($this->name, TrinacriaRequest::METHOD_FILES);
        if(!empty($var)) {
            $this->hiddenValue = $var;
        }
        //debug('ElementInput_File->updateValue() ; END');
    }

    //
    // Getters / Setters
    //

    public function getValue($when = null) {
        switch($when) {
            case TrinacriaForm::RULE_EXECUTION:
                return ($this->hiddenValue['error'] == 0);
            break;

            default:
                return $this->hiddenValue;
            break;
        }
        /*
        $var = TrinacriaRequest::getVar($this->name, 'file');
        if(!is_bool($var)) {
            return $var;
        } else {
            // return default value
            return $this->value;
        }
        */
    }

    public function setInfos($a) {
        $this->infos = $a;
        return $this;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
