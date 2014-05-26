<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'HTML/TrinacriaForm/Elements/Element.php';
require_once 'HTML/TrinacriaForm/Elements/ElementLabel.php';

class TrinacriaForm_ElementOptGroup {
    // str
    private $label;
    // array
    private $options;

    public function __construct($label, $options) {
        $this->label = $label;
        $this->options = $options;
    }

    public function toString($selectedValue = '') {
        $a = '<optgroup label="'.$this->label.'">';

        foreach($this->options as $k => $v) {
            $a .= '<option value="'.$k.'"';
            if($selectedValue == $k) {
                $a .= ' selected="selected"';
            }
            $a .= '>'.$v.'</option>'."\n";
        }

        return $a.'</optgroup>';
    }

    public function getValues() {
        return $this->options;
    }

    public function __toString() {
        return $this->toString();
    }
}

class TrinacriaForm_ElementSelect extends TrinacriaForm_Element {
    private $name;
    // $values puted in code
    // used for display
    private $values;
    private $rowValues;
    private $options;
    private $label;
    private $selected;
    private $infos;

    // Todo
    // - optgroup
    public function __construct($name, $values, $label = null, $id = '',
        $selected = null, $options = null) {
        //debug(__METHOD__.' : START');

        if(!is_array($values)) {
            throw new Exception(
                'Can\'t build node Select without values'
            );
        }

        parent::__construct($id);

        $this->name = $name;
        $this->values = $values;
        $this->rowValues = array();

        foreach($values as $k => $v) {
            if(is_object($v) && is_a($v, 'TrinacriaForm_ElementOptGroup')) {
                $this->rowValues = array_merge(
                    $this->rowValues, $v->getValues()
                );
            } else {
                $this->rowValues[$k] = $v;
            }
        }

        $this->options = null;
        // Delete label in options if is already set in $label
        if(!empty($label)) {
            if(is_array($options) && isset($options['label'])) {
                unset($options['label']);
            }

            if(is_object($label)) {
                if(get_class($label) == 'TrinacriaForm_ElementLabel') {
                    $this->label = $label;
                    $this->label->setTarget($this);
                } else {
                    throw new Exception('$label have wrong Type');
                }
            } else {
                $this->label = new TrinacriaForm_ElementLabel(
                    $label, $this, array('class' => 'select')
                );
            }
        }

        if(!empty($options) && is_array($options)) {
            if(array_key_exists('readonly', $options)) {
                $options['disabled'] = ($options['readonly'] == 'readonly');
                unset($options['readonly']);
            }

            $this->options = $options;
        }

        $this->selected = $selected;
        $this->infos = '';

        //debug(__METHOD__.' : END');
    }

    public function unsetLabel() {
        $this->label = null;
        return $this;
    }

    public function addToLabel($str) {
        if(isset($this->label)) {
            $this->label->concatValue($str);
        }

        return $this;
    }

    public function updateValue() {
        //debug(__METHOD__.' : START');
        if(!empty($this->eParent)) {
            $var = TrinacriaRequest::getVar(
                $this->name,
                $this->eParent->getMethod(),
                TrinacriaRequest::ALLOW_EMPTY
            );

            if(!is_bool($var)) {
                if(array_key_exists($var, $this->rowValues)) {
                    $this->selected = $var;
                }
            }
        }
        //debug(__METHOD__.' : END');
    }

    //
    // Getters / Setters
    //

    public function setLabel($label = '') {
        if(empty($label)) {
            $this->label = new TrinacriaForm_ElementLabel($this);
        } else {
            $this->label = $label;
        }
        return $this;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getValue() {
        $var = TrinacriaRequest::getVar(
            $this->name, $this->eParent->getMethod()
        );

        if(array_key_exists($var, $this->rowValues)) {
            return $var;
        } else {
            return $this->selected;
        }
    }

    public function getName() {
        return $this->name;
    }

    public function setInfos($a) {
        $this->infos = $a;
        return $this;
    }

    // Display
    public function __toString() {
        //debug(__METHOD__.' ; START');
        //debug(__METHOD__.' ; $this->errorMethod :');
        //debug($this->errorMethod);

        if(in_array('required', $this->rulesTypes)) {
            if(!empty($this->options['class'])) {
                $this->options['class'] .= ' required';
            } else {
                $this->options['class'] = 'required';
            }
        }

        $a = '';
        $b = '';
        $c = '';

        if(!empty($this->label)) {
            $b = $this->label->toString()."\n";
        } else {
            $b = '';
        }

        if($this->errorMethod == TrinacriaForm::ERROR_METHOD_FIELD) {
            $c = '';
            if(!empty($this->errors)) {
                foreach($this->errors as $rule) {
                    $c .= '<span class="alert alert-error">'
                        .$rule->getErrorMsg().'</span>'."\n";
                }
            }

            if(!empty($this->errorsText)) {
                foreach($this->errorsText as $text) {
                    $c .= '<span class="alert alert-error">'
                        .$text.'</span>'."\n";
                }
            }
        }

        if(array_key_exists('wrapper', $this->options)) {
            $wrapperStart = '';
            $wrapperEnd = '';

            if($this->options['wrapper'] !== null) {
                $wrapperStart = '<'.$this->options['wrapper']['html'];
                $wrapperEnd = '</'.$this->options['wrapper']['html'].'>';

                if(!empty($this->options['wrapper']['options'])) {
                    foreach($this->options['wrapper']['options']
                        as $attribute => $value) {
                        $wrapperStart .= ' '.$attribute.'="'.$value.'"';
                    }
                }

                $wrapperStart .= '>';
            }

            unset($this->options['wrapper']);
        } else {
            $wrapperStart = '<span class="wrapper" id="'.$this->id.'-wrapper">';
            $wrapperEnd = '</span>';
        }

        $a = $c.$b.$wrapperStart
                .'<select id="'.$this->id.'" name="'.$this->name.'"';

        if(!empty($this->options)) {
            foreach($this->options as $attribute => $value) {
                switch($attribute) {
                    case 'disabled':
                        if($value === true || $value === 'disabled') {
                            $a .= ' disabled="disabled"';
                        } else {
                            //debug('Option disabled : incorrect value given');
                        }
                        break;

                    case 'dir':
                        if($value === 'ltr' || $value === 'rtl') {
                            $a .= ' dir="'.$value.'"';
                        } else {
                            //debug('Option dir : incorrect value given');
                        }
                        break;

                        break;
                    default:
                        $a .= ' '.$attribute.'="'.$value.'"';

                        break;
                }
            }
        }

        $a .= '>'."\n";
        foreach($this->values as $k => $v) {
            if(is_object($v) && is_a($v, 'TrinacriaForm_ElementOptGroup')) {
                $a .= $v->toString($this->selected);
            } else {
                $a .= '<option value="'.$k.'"';
                if($this->selected == $k) {
                    $a .= ' selected="selected"';
                }
                $a .= '>'.$v.'</option>'."\n";
            }
        }
        $a .= '</select>'.$wrapperEnd;

        return $a;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
