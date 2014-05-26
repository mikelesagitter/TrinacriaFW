<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'HTML/TrinacriaForm/Elements/ElementInput.php';
require_once 'HTML/TrinacriaForm/Elements/ElementLabel.php';
// TODO : check options
class TrinacriaForm_ElementInput_Checkbox extends TrinacriaForm_ElementInput {
    public function __construct($name, $label = null, $id = '', $options = null) {
        // Delete label in options if is already set in $label
        if(!empty($label)) {
            if(is_array($options) && isset($options['label'])) {
                unset($options['label']);
            }
        }

        if(!empty($options['class'])) {
            $options['class'] .= ' checkbox';
        } else {
            $options['class'] = 'checkbox';
        }

        parent::__construct('checkbox', $name, $id, '', $options);

        if(!empty($label)) {
            $this->label = new TrinacriaForm_ElementLabel($label, $this, '');
        }
    }

    public function updateValue() {
        //debug('ElementInput_Checkbox->updateValue() ; START');
        if(!empty($this->eParent)) {
            $var = TrinacriaRequest::getVar($this->name, $this->eParent->getMethod(),
                TrinacriaRequest::ALLOW_EMPTY
            );

            //debug($var);

            if(!empty($var)) {
                $this->options['checked'] = 'checked';
                $this->value = true;
            } else {
                unset($this->options['checked']);
                $this->value = false;
            }
            /*else {
                if(is_array($this->options) && array_key_exists('checked', $this->options)) {
                    unset($this->options['checked']);
                }
            }
            */
        }
        //debug('ElementInput_Checkbox->updateValue() ; END');
    }

    //
    // Getters / Setters
    //

    public function getValue() {
        return $this->value;
        /*
        $var = TrinacriaRequest::getVar($this->name, $this->eParent->getMethod(), true);
        if(!is_bool($var)) {
            return true;
        } else {
            return false;
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
