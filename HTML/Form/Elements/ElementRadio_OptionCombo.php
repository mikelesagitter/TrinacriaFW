<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'HTML/TrinacriaForm/Elements/ElementRadio_Option.php';
require_once 'HTML/TrinacriaForm/Elements/ElementInput_Text.php';
require_once 'HTML/TrinacriaForm/Elements/ElementInput_Password.php';

class TrinacriaForm_ElementRadio_OptionCombo extends TrinacriaForm_Element {
    private $radio;
    // Can be only ElementInput_Text or ElementInput_Password
    private $other;

    private $isDependRuleCreated;

    public function __construct($radio, $other) {
        if(get_class($radio) != 'TrinacriaForm_ElementRadio_Option') {
            throw new Exception('$radio must be TrinacriaForm_ElementRadio_Option object type');
        }

        if(get_class($other) != 'TrinacriaForm_ElementInput_Password' &&
            get_class($other) != 'TrinacriaForm_ElementInput_Text') {
            throw new Exception('$other must be TrinacriaForm_ElementRadio_Option object type');
        }

        parent::__construct();

        $this->radio = $radio;
        $this->other = $other;

        $this->radio->setParent($this);
        $this->other->setParent($this);

        if($this->getParent() !== null) {
            //debug('ElementRadio_OptionCombo->construct() ; $this->getParent() !== null');
            $this->isDependRuleCreated = true;
            $this->other->addRule('depend', array('on' => $this->getParent(), 'what' => 'value', 'value' => $this->radio->getValue()), array('msg' => ''));
        } else {
            $this->isDependRuleCreated = false;
        }
    }

    public function check() {
        //debug('ElementRadio_OptionCombo->check() ; START');
        //debug('ElementRadio_OptionCombo->check() ; parent::check() : ');
        $r = parent::check();
        //debug($r,3);

        if($r) {
            //debug('ElementRadio_OptionCombo->check() ; Launch $this->other->check()');
            $r = ($r && $this->other->check());
        }

        //debug('ElementRadio_OptionCombo->check() ; END');

        return $r;
    }

    public function updateValue() {
        // do nothing
    }

    public function updateComboValue() {
        $this->other->updateValue();
    }

    public function fireEvent($event, $args) {
        //debug('ElementRadio_OptionCombo->fireEvent() ; START');
        parent::fireEvent($event, $args);

        $this->radio->fireEvent('updateShowErrorStatus', $args);
        $this->other->fireEvent('updateShowErrorStatus', $args);

        switch($event) {
            case 'addedToParent':
            //debug('ElementRadio_OptionCombo->fireEvent() ; addedToParent');
            if(!$this->isDependRuleCreated) {
                //debug('ElementRadio_OptionCombo->fireEvent() ; !$this->isDependRuleCreated');
                $this->isDependRuleCreated = true;
                $this->other->addRule('depend', array('on' => $this->getParent(), 'what' => 'value', 'value' => $this->radio->getValue()), array('msg' => ''));
            }
            break;

            default:
            break;
        }

        //debug('ElementRadio_OptionCombo->fireEvent() ; END');
    }

    //
    // Getters / Setters
    //


    public function getRadioValue() {
        return $this->radio->getValue();
    }

    public function getName() {
        return $this->eParent->getName();
    }

    public function getValue() {
        return $this->radio->getValue();
        //~ return array(
            //~ $this->radio->getValue() => $this->other->getValue()
        //~ );
    }

    public function getDatas() {
        //debug(__METHOD__);
        $a = array(
            'radio' => $this->radio->getValue(),
            'other' => $this->other->getValue()
        );

        //debug($a,3);

        return $a;
    }

    public function getMethod() {
        return $this->eParent->getMethod();
    }

    public function setSelected() {
        $this->radio->setSelected();
    }

    public function setUnselected() {
        $this->radio->setUnselected();
        return $this;
    }

    public function __toString() {
        return '<span class="radio-option-combo">'.$this->radio.$this->other.'</span>';
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
