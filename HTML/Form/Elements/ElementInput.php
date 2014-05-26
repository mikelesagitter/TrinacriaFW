<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require_once 'HTML/TrinacriaForm/Elements/Element.php';

class TrinacriaForm_ElementInput extends TrinacriaForm_Element {
    const ROW_CONTENT_BEFORE = 10;
    const ROW_CONTENT_AFTER = 20;

    static private $autorizedOptions = array('class', 'disabled', 'readonly',
        'checked', 'size', 'button', 'rowContent', 'value', 'labelPosition',
        'wrapper');

    protected $type;
    protected $name;
    protected $value;
    protected $options;
    protected $label;
    protected $infos = '';

    const regexNumber = '#^[1-9]{1}[0-9]{0,}$#';

    public function __construct($type, $name, $id = '', $value = '',
        $options = null, $parent = null) {
        //debug('ElementInput::__construct() ; START');

        parent::__construct($id);

        $this->type = $type;
        $this->name = $name;
        $this->value = $value;

        $this->options = null;

        if(!empty($options) && is_array($options)) {
            TrinacriaForm::cleanLabel($options, $this);

            foreach($options as $attribute => $value) {
                if(!in_array($attribute, self::$autorizedOptions)) {
                    //debug('ElementInput::__construct() ; skipped invalid option "'.$attribute.'"');
                    // delete invalid option
                    unset($options[$attribute]);
                }
            }

            if(!empty($options)) {
                $this->options = $options;
            }
        }

        $this->infos = '';
        //debug('ElementInput::__construct() ; END');
    }

    public function fireEvent($event, $args) {
        //debug('ElementInput->fireEvent() ; START');
        parent::fireEvent($event, $args);
        //debug('ElementInput->fireEvent() ; END');
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

            if(!empty($var)) {
                $this->value = $var;
            }
        }
        //debug(__METHOD__.' : END');
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
        /*
        if(!empty($this->eParent)) {
            $var = TrinacriaRequest::getVar(
                $this->name,
                $this->eParent->getMethod()
            );

            if(!is_bool($var)) {
                return $var;
            } else {
                // return default value
                return $this->value;
            }
        } else {
        */
            return $this->value;
        //}
    }

    public function setValue($value) {
        //~ debug(__METHOD__.' : $value = '.$value);
        $this->value = $value;
    }

    // Display

    public function __toString() {
        //debug('ElementInput::__toString() ; START');
        //debug('ElementInput::__toString() ; $this->errorMethod :');
        //debug($this->errorMethod,3);

        if(in_array('required', $this->rulesTypes)) {
            if(!empty($this->options['class'])) {
                $this->options['class'] .= ' required';
            } else {
                $this->options['class'] = 'required';
            }
        }

        if(empty($this->options['labelPosition'])) {
            $labelPosition = 'left';
        } else {
            $labelPosition = $this->options['labelPosition'];
            unset($this->options['labelPosition']);
        }

        $a = '';
        $b = '';
        $c = '';

        if(!empty($this->label) && $labelPosition == 'left') {
            $b = $this->label->toString()."\n";
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
                    $c .= '<span class="alert alert-error">
                        '.$text.'</span>'."\n";
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

        $asButton = false;

        if(!empty($this->options['button'])) {
            $asButton = true;
            unset($this->options['button']);

            $a = $c.$b.$wrapperStart.'<button id="'.$this->id.'"'
                .' type="'.$this->type.'" name="'.$this->name.'"';
        } else {
            $a = $c.$b.$wrapperStart
                .'<input id="'.$this->id.'" type="'.$this->type
                .'" name="'.$this->name.'"';

            if($this->type !== 'checkbox')
                $a .= ' value="'.htmlspecialchars($this->value).'"';
        }

        $rc = null;

        if(!empty($this->options['rowContent'])) {
            $rc = $this->options['rowContent'];
            unset($this->options['rowContent']);

            if(
                !is_array($rc)
                || count($rc) !== 2
                || !isset($rc['content'])
                || !isset($rc['position'])
                || ($rc['position'] !== self::ROW_CONTENT_BEFORE
                    && $rc['position'] !== self::ROW_CONTENT_AFTER)
            ) {
                $rc = null;
                //debug('Option rowContent : incorrect value given');
            }
        }

        if(!empty($this->options)) {
            // add class equivalent to attribute
            $c = '';
            foreach($this->options as $attribute => $value) {
                switch($attribute) {
                    case 'disabled':
                        if($value === true || $value === 'disabled') {
                            $a .= ' disabled="disabled"';
                            $c .= ' disabled';
                        }
                        //~ else {
                            //~ debug('Option disabled : incorrect value given');
                        //~ }
                        break;

                    case 'readonly':
                        if($value === true || $value === 'readonly') {
                            $a .= ' readonly="readonly"';
                            $c .= ' readonly';
                        }
                        //~ else {
                            //~ debug('Option readonly : incorrect value given');
                        //~ }
                        break;

                    case 'checked':
                        if($value === true || $value === 'checked') {
                            $a .= ' checked="checked"';
                            $c .= ' checked';
                        }
                        //~ else {
                            //~ debug('Option checked : incorrect value given');
                        //~ }
                        break;

                    case 'dir':
                        if($value == 'ltr' || $value === 'rtl') {
                            $a .= ' dir="'.$value.'"';
                        }
                        //~ else {
                            //~ debug('Option dir : incorrect value given');
                        //~ }
                        break;

                    case 'size':
                        if(preg_match(self::regexNumber, $value)) {
                            $a .= ' size="'.$value.'"';
                        }
                        //~ else {
                            //~ debug('Option size : incorrect value given');
                        //~ }

                        break;
                    default:
                        if($attribute == 'class') {
                            $value .= $c;
                        }

                        $a .= ' '.$attribute.'="'.$value.'"';

                        break;
                }
            }
        }

        if($asButton) {
            if($rc !== null) {
                $a .= '>';

                if($rc['position'] == self::ROW_CONTENT_AFTER) {
                    $a .= htmlspecialchars($this->value).$rc['content'];
                } else {
                    $a .= $rc['content'].htmlspecialchars($this->value);
                }

                $a .= '</button>';
            } else {
                $a .= '>'.htmlspecialchars($this->value).'</button>';
            }
        } else {
            $a .= ' />';
        }

        $a .= $wrapperEnd;

        if(!empty($this->label) && $labelPosition == 'right') {
            $a .= $this->label->toString()."\n";
        }

        if(!empty($this->infos)) {
            $a .= '<span class="notice">'.$this->infos.'</span>';
        }

        //debug('ElementInput::__toString() ; END');

        return $a;
    }

    public function __destruct() {
        parent::__destruct();
    }
}
?>
