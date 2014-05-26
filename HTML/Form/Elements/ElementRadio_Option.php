<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once  'HTML/TrinacriaForm/Elements/Element.php';

class TrinacriaForm_ElementRadio_Option extends TrinacriaForm_Element {
    private $name;
    private $value;
    private $label;
    private $selected;
    private $options;

    public function __construct($name, $value, $label = null,
        $id = null, $options = null) {

        parent::__construct($id);

        $this->name = $name;
        $this->value = $value;

        if(!is_object($label)) {
            $label = new TrinacriaForm_ElementLabel(
                $label,
                $this,
                array('class' => 'radio')
            );
        }

        $this->label = $label;
        $this->selected = false;

        $this->options = $options;
    }

    public function unsetLabel() {
        $this->label = null;
        return $this;
    }

    // do nothing : see parent
    public function updateValue() {
        //debug('ElementRadio_Option->updateValue() ; START / END ; DO NOTHING');
    }

    //~ // do nothing : used on variant TrinacriaForm_ElementRadio_OptionCombo
    //~ public function updateOptions() {
        //~ //debug('ElementRadio_Option->updateOptions() ; START / END ; DO NOTHING');
    //~ }

    public function fireEvent($event, $args) {
        //debug('ElementRadio_Option->fireEvent() ; START');
        parent::fireEvent($event, $args);
        //debug('ElementRadio_Option->fireEvent() ; END');
    }

    //
    // Getters / Setters
    //

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function setSelected() {
        $this->selected = true;
        return $this;
    }

    public function setUnselected() {
        $this->selected = false;
        return $this;
    }

    public function toString() {
        return $this->__toString();
    }

    public function __toString() {
        $a = '<span class="radio-option">';

        if(!empty($this->label)) {
            $a .= $this->label->toString()."\n";
        }

        $b = '<input type="radio" class="radio"'
            .' name="'.$this->name.'" value="'.$this->value.'"';

        if(!empty($this->id)) {
            if(ctype_digit($this->id) || is_int($this->id))
                $b .= ' id="'.$this->getNodeType().'-'.$this->id.'"';
            else
                $b .= ' id="'.$this->id.'"';
        }
        if(!empty($this->selected)) {
            $b .= ' checked="checked"';
        }

        if(!empty($this->options)) {
            foreach($this->options as $attribute => $value) {
                switch($attribute) {
                    case 'disabled':
                        if($value === true || $value === 'disabled') {
                            $b .= ' disabled="disabled"';
                        } else {
                            //debug('Option disabled : incorrect value given');
                        }
                        break;

                    case 'readonly':
                        if($value === true || $value === 'readonly') {
                            $b .= ' readonly="readonly"';
                        } else {
                            //debug('Option readonly : incorrect value given');
                        }
                        break;

                        break;
                    default:
                        $b .= ' '.$attribute.'="'.$value.'"';

                        break;
                }
            }
        }

        $b .= ' />';

        if(!empty($this->options['reverse'])) {
            return $b.$a.'</span>';
        } else {
            return $a.$b.'</span>';
        }
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
