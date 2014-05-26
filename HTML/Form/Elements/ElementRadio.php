<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'HTML/TrinacriaForm/Elements/Element.php';
require_once 'HTML/TrinacriaForm/Elements/ElementLabel.php';

class TrinacriaForm_ElementRadio extends TrinacriaForm_Element {
    private $name;
    private $label;
    private $elements;
    private $nbElements;
    private $selected;
    private $options;
    private $separator;
    private $infos;

    public function __construct($name, $elements, $label = '',
        $separator = '', $options = null) {
        //debug('ElementRadio::__construct() ; START');

        if(empty($elements) || !is_array($elements)) {
            throw new Exception(
                'Can\'t build node Radios without elements'
            );
        } else {
            parent::__construct();

            $this->name = $name;
            $this->label = $label;
            $this->separator = $separator;
            $this->infos = '';
            $this->elements = array();

            if(!empty($options) && is_array($options)) {
                if(isset($options['label'])) {
                    unset($options['label']);
                }
            }

            $this->options = $options;

            //debug('ElementRadio::__construct() ; $this->errorMethod = '.$this->errorMethod);

            // TODO: Gérer le $selected
            $this->selected = 0;
            $this->nbElements = 0;

            // Elements générés automatiquement
            if(array_key_exists('options', $elements)) {
                //~ debug('ElementRadio::__construct() ; isset options');
                $selected = !empty($elements['selected']) ?
                    (--$elements['selected']) : null;

                foreach($elements['options'] as $key => $element) {
                    if(is_array($element)) {
                        $elOptions = !empty($element[3]) ?
                            $element[3] : null;

                        if(isset($options['disabled'])) {
                            $elOptions['disabled'] = $options['disabled'];
                        }

                        if(isset($options['readonly'])) {
                            $elOptions['readonly'] = $options['readonly'];
                        }

                        // Generation de l'option
                        $this->elements[$this->nbElements] =
                            new TrinacriaForm_ElementRadio_Option(
                                $name,
                                $element[0],
                                $element[1],
                                $element[2],
                                $elOptions
                            );
                    } else {
                        $this->elements[$this->nbElements] = $element;
                    }

                    $this->elements[$this->nbElements]->fireEvent(
                        'addedToParent', array(
                            'parent' => $this,
                            'showError' => $this->errorMethod
                        )
                    );

                    if($key === $selected) {
                        $this->selected = $this->nbElements;
                        $this->elements[$this->selected]->setSelected();
                    }

                    $this->nbElements++;
                }
            } else {
                //~ debug('ElementRadio::__construct() ; !isset options');
                // Elements générés manuellement
                foreach($elements as $key => $element) {
                    $elOptions = !empty($element[3]) ?
                        $element[3] : null;

                    if(isset($options['disabled'])) {
                        $elOptions['disabled'] = $options['disabled'];
                    }

                    if(isset($options['readonly'])) {
                        $elOptions['readonly'] = $options['readonly'];
                    }

                    $this->elements[$this->nbElements] =
                        new TrinacriaForm_ElementRadio_Option(
                            $name,
                            $element[0],
                            $element[1],
                            $element[2],
                            $elOptions
                        );

                    $this->elements[$this->nbElements]->fireEvent(
                        'addedToParent', array(
                            'parent' => $this,
                            'showError' => $this->errorMethod
                        )
                    );

                    if($key === 'selected') {
                        $this->selected = $this->nbElements;
                        $this->elements[$this->selected]->setSelected();
                    }

                    $this->nbElements++;
                }
            }
        }

        //debug('ElementRadio::__construct() ; END');
    }

    public function __destruct() {
        parent::__destruct();
    }

    public function updateValue() {
        //debug('ElementRadio->updateValue() ; START');
        $var = TrinacriaRequest::getVar(
            $this->name,
            $this->eParent->getMethod(),
            TrinacriaRequest::ALLOW_EMPTY
        );

        //debug('ElementRadio->updateValue() ; $var');
        //debug($var,3);

        $e = null;

        if(!empty($var)) {
            $done = false;
            $i = 0;
            while($i < $this->nbElements && false === $done) {
                $e = $this->elements[$i];
                //debug('ElementRadio->updateValue() ; get_class($e) : '.get_class($e));

                switch(get_class($e)) {
                    case 'TrinacriaForm_ElementRadio_Option':
                        if($var == $e->getValue()) {
                            $found = true;
                            if($this->selected >= 0) {
                                $this->elements[$this->selected]->setUnselected();
                            }
                            $this->selected = $i;
                            $e->setSelected();
                        }
                        break;

                    case 'TrinacriaForm_ElementRadio_OptionCombo':
                        if($var == $e->getRadioValue()) {
                            $found = true;
                            if($this->selected >= 0) {
                                $this->elements[$this->selected]->setUnselected();
                            }
                            $this->selected = $i;
                            $e->setSelected();
                            $e->updateComboValue();
                        }
                        break;

                    default:
                        throw new Exception('ElementRadio->updateValue() ; UNMANAGED ELEMENT_RADIO CHILD');
                        break;
                }
                $i++;
            }
        }

        //debug('ElementRadio->updateValue() ; END');
    }

    // $label is not an object for this element
    public function addToLabel($str) {
        $this->label .= ' '.$str;

        return $this;
    }

    public function check() {
        //debug('ElementRadio->check() ; START');
        $r = parent::check();
        //debug('ElementRadio->check() ; parent::check() result : ');
        //debug($r,3);

        if($r) {
            foreach($this->elements as $e) {
                switch(get_class($e)) {
                    case 'TrinacriaForm_ElementRadio_OptionCombo':
                        //debug('ElementRadio->check() ; Launching ElementRadio_OptionCombo->check();');
                        $r = ($r && $e->check());
                    break;

                    default:
                    break;
                }
            }
        }

        //debug('ElementRadio->check() ; END');

        return $r;
    }

    public function fireEvent($event, $args) {
        //debug('ElementRadio->fireEvent() ; START');
        parent::fireEvent($event, $args);

        foreach($this->elements as $e) {
            $e->fireEvent('updateShowErrorStatus', $args);
        }

        //debug('ElementRadio->fireEvent() ; END');
    }

    //
    // Getters / Setters
    //

    public function getMethod() {
        return $this->eParent->getMethod();
    }

    public function getDatas($prefix) {
        //debug('ElementRadio->getDatas($prefix) ; START');
        //debug('ElementRadio->getDatas($prefix) ; $this->selected');
        //debug($this->selected,3);

        if($prefix !== null) {
            $k = substr($this->name, strlen($prefix));
        } else {
            $k = $this->name;
        }

        $r = null;

        switch(get_class($this->elements[$this->selected])) {
            case 'TrinacriaForm_ElementRadio_Option':
                $r = array($k => $this->elements[$this->selected]->getValue());

                break;

            case 'TrinacriaForm_ElementRadio_OptionCombo':
                $r = array($k => $this->elements[$this->selected]->getDatas());
                break;

            default:
                throw new Exception('ElementRadio->updateValue() ; UNMANAGED ELEMENT_RADIO CHILD');
                break;
        }

        //$e =

        //~ //debug('ElementRadio->getDatas($prefix) ; FOREACH START');
        //~ foreach($this->elements as $e) {
            //~ //debug('ElementRadio->getDatas($prefix) ; FOREACH');
            //~ //debug('ElementRadio->getDatas($prefix) ; e : '.$e->getId().' -- '.get_class($e).' -- '.$e->getName());
            //~
            //~ $d[$e->getName()] = $e->getValue();
        //~ }
        //~ //debug('ElementRadio->getDatas($prefix) ; FOREACH END');

        //debug('ElementRadio->getDatas($prefix) ; END');

        return $r;
    }

    public function getValue() {
        $var = TrinacriaRequest::getVar(
            $this->name, $this->eParent->getMethod()
        );

        $defaultValue = $this->elements[$this->selected]->getValue();

        if(!empty($var)) {
            // before return a value
            // check if the POST/GET value is possible
            // by checking elements
            $found = false;
            $tmp = null;
            $i = 0;

            while($i < $this->nbElements && false == $found) {
                $found = ($var == $this->elements[$i]->getValue());
                $i++;
            }

            if($found) {
                return $var;
            } else {
                return $defaultValue;
            }
        } else {
            return $defaultValue;
        }
    }

    public function setInfos($a) {
        $this->infos = $a;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function getLabel() {
        $a = '<span class="label">'.$this->label.'</span>'."\n";
        $b = '';

        if($this->errorMethod == TrinacriaForm::ERROR_METHOD_FIELD) {
            if(!empty($this->errors)) {
                foreach($this->errors as $rule) {
                    $b .= '<span class="alert alert-error">'.$rule->getErrorMsg().'</span>'."\n";
                }
            }

            if(!empty($this->errorsText)) {
                foreach($this->errorsText as $text) {
                    $b .= '<span class="alert alert-error">'.$text.'</span>'."\n";
                }
            }
        }

        $a = $b.$a;

        return $a;
    }

    public function getElements() {
        return $this->elements;
    }

    public function getShowErrorStatus() {
        return $this->errorMethod;
    }

    public function __toString() {
        $a = self::getLabel();

        $i = 1;

        foreach($this->elements as $element) {
            $a .= $element->toString();
            if($i < $this->nbElements) $a .= $this->separator;
            $i++;
        }

        return $a;
    }
}
?>
