<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'HTML/TrinacriaForm/Elements/ElementInput.php';
require_once 'HTML/TrinacriaForm/Elements/ElementLabel.php';
// TODO : check options
class TrinacriaForm_ElementInput_Submit extends TrinacriaForm_ElementInput {
    public function __construct($name, $id = '', $value = '', $options = array()) {
        // Delete label in options
        // input submit doesn't have label
        if(is_array($options) && isset($options['label'])) {
            unset($options['label']);
        }

        if(!empty($options['class'])) {
            $options['class'] .= ' submit';
        } else {
            $options['class'] = 'submit';
        }

        parent::__construct('submit', $name, $id, $value, $options);
    }

    public function fireEvent($event, $args) {
        //debug('ElementInput_Submit->fireEvent() ; START');
        parent::fireEvent($event, $args);
        //debug('ElementInput_Submit->fireEvent() ; END');
    }

    //
    // Getters / Setters
    //

    public function updateValue() {
        // Do nothing
        //debug('ElementInput_Submit->updateValue() ; START/END ; DO NOTHING');
    }

    // Always return true
    public function check() {
        return true;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
