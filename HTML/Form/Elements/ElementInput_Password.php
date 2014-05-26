<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'HTML/TrinacriaForm/Elements/ElementInput.php';
require_once 'HTML/TrinacriaForm/Elements/ElementLabel.php';
// TODO : check options
class TrinacriaForm_ElementInput_Password extends TrinacriaForm_ElementInput {
    public function __construct($name, $label = null, $id = '', $value = '', $options = null) {
        // Delete label in options if is already set in $label
        if(!empty($label)) {
            if(is_array($options) && isset($options['label'])) {
                unset($options['label']);
            }
        }

        if(!empty($options['class'])) {
            $options['class'] .= ' password';
        } else {
            $options['class'] = 'password';
        }

        // Don't set default value
        parent::__construct('password', $name, $id, '', $options);

        if(!empty($label)) {
            $this->label = new TrinacriaForm_ElementLabel($label, $this, '');
        }
    }

    //
    // Getters / Setters
    //

    public function setInfos($a) {
        $this->infos = $a;
        return $this;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
