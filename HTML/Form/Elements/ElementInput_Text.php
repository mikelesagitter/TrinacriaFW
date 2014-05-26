<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'HTML/TrinacriaForm/Elements/ElementInput.php';
require_once 'HTML/TrinacriaForm/Elements/ElementLabel.php';
// TODO : check options
class TrinacriaForm_ElementInput_Text extends TrinacriaForm_ElementInput {
    public function __construct($name, $label = null, $id = '', $value = '', $options = null) {
        // Delete label in options if is already set in $label
        if(!empty($label)) {
            if(is_array($options) && isset($options['label'])) {
                unset($options['label']);
            }
        }

        if(!empty($options['class'])) {
            $options['class'] .= ' text';
        } else {
            $options['class'] = 'text';
        }

        parent::__construct('text', $name, $id, $value, $options);

        if(!empty($label)) {
            $this->label = new TrinacriaForm_ElementLabel($label, $this, '');
        }
    }

    public function fireEvent($event, $args) {
        //debug('ElementInput_Text->fireEvent() ; START');
        parent::fireEvent($event, $args);
        //debug('ElementInput_Text->fireEvent() ; END');
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
