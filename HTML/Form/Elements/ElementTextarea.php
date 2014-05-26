<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'HTML/TrinacriaForm/Elements/Element.php';

class TrinacriaForm_ElementTextarea extends TrinacriaForm_Element {
    private $name;
    private $value;
    private $options;
    private $label;
    private $infos = '';

    public function __construct($name, $label = null, $id = '', $value = '', $options = null, $parent = null) {
        parent::__construct($id);

        $this->name = $name;
        $this->value = $value;

        $this->options = null;
        // Delete label in options if is already set in $label
        if(!empty($label)) {
            if(is_array($options) && isset($options['label'])) {
                unset($options['label']);
            }
            $this->label = new TrinacriaForm_ElementLabel($label, $this, '');
        }

        if(!empty($options) && is_array($options)) {
            $this->options = $options;
        }

        if(empty($this->options['rows'])) {
            $this->options['rows'] = 5;
        }

        if(empty($this->options['cols'])) {
            $this->options['cols'] = 50;
        }

        $this->infos = '';
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
        //debug('ElementTextarea updateValue()');
        //if(!empty($this->eParent)) {
            $var = TrinacriaRequest::getVar($this->name, $this->eParent->getMethod(),
                TrinacriaRequest::ALLOW_EMPTY
            );

            if(!empty($var)) {
                $this->value = $var;
            }
        //}
    }

    //
    // Getters / Setters
    //

    public function getOptions() {
        return $this->options;
    }

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

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
        /*
        $var = TrinacriaRequest::getVar($this->name, $this->eParent->getMethod());
        if(!is_bool($var)) {
            debug(__METHOD__);
            debug('$var :');
            debug($var);
            return $var;
        } else {
            debug(__METHOD__);
            debug('$this->value :');
            debug($this->value);
            return $this->value;
        }
        */
    }

    // Display

    public function __toString() {
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
        }

        if($this->errorMethod == TrinacriaForm::ERROR_METHOD_FIELD) {
            $c = '';
            if(!empty($this->errors)) {
                foreach($this->errors as $rule) {
                    $c .= '<span class="alert alert-error">'.$rule->getErrorMsg().'</span>'."\n";
                }
            }

            if(!empty($this->errorsText)) {
                foreach($this->errorsText as $text) {
                    $c .= '<span class="alert alert-error">'.$text.'</span>'."\n";
                }
            }
        }

        $a = $c.$b.'<span class="wrapper" id="'.$this->id.'-wrapper">'
                .'<textarea id="'.$this->id.'" name="'.$this->name.'"';

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

                    case 'readonly':
                        if($value === true || $value === 'readonly') {
                            $a .= ' readonly="readonly"';
                        } else {
                            //debug('Option readonly : incorrect value given');
                        }
                        break;

                    case 'dir':
                        if($value === 'ltr' || $value === 'rtl') {
                            $a .= ' dir="'.$value.'"';
                        } else {
                            //debug('Option dir : incorrect value given');
                        }
                        break;
                    default:
                        $a .= ' '.$attribute.'="'.$value.'"';

                        break;
                }
            }
        }

        $a .= '>'.htmlspecialchars($this->value).'</textarea></span>';

        if(!empty($this->infos)) {
            $a .= '<span class="notice">'.$this->infos.'</span>';
        }

        return $a;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
