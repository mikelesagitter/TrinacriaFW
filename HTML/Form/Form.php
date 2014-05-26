<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

require 'HTML/TrinacriaForm/Elements/Element.php';
require 'HTML/TrinacriaForm/Elements/ElementInput.php';
require 'HTML/TrinacriaForm/Elements/ElementInput_Text.php';
require 'HTML/TrinacriaForm/Elements/ElementInput_Checkbox.php';
require 'HTML/TrinacriaForm/Elements/ElementInput_File.php';
require 'HTML/TrinacriaForm/Elements/ElementInput_Password.php';
require 'HTML/TrinacriaForm/Elements/ElementInput_Submit.php';
require 'HTML/TrinacriaForm/Elements/ElementInput_Image.php';
require 'HTML/TrinacriaForm/Elements/ElementRadio.php';
require 'HTML/TrinacriaForm/Elements/ElementRadio_Option.php';
require 'HTML/TrinacriaForm/Elements/ElementRadio_OptionCombo.php';
require 'HTML/TrinacriaForm/Elements/ElementSelect.php';
require 'HTML/TrinacriaForm/Elements/ElementTextarea.php';
require 'HTML/TrinacriaForm/Elements/Container.php';

class TrinacriaForm {
    const RULE_EXECUTION = 1;

    const ERROR_METHOD_FIELD = 2;
    const ERROR_METHOD_CONTAINER = 3;

    const MSG_TYPE_ERRORS = 10;
    const MSG_TYPE_NOTICES = 20;
    const MSG_TYPE_SUCCESS = 30;
    const MSG_TYPE_INFO = 40;

    static private $id = 0;

    private $name;
    private $method;// post, get
    private $action; // url
    private $options;
    private $elements;// all form's direct children
    private $nbElements;// # of elements
    private $aIds;// all form's direct children id
    private $token;
    private $tokenName;
    private $errorMethod;
    private $errorsText;
    private $infosText;
    private $successText;

    static public function createLabel($datas = array()) {
        return TrinacriaForm_ElementLabel::createLabel($datas);
    }

    // Constructeur
    public function __construct($method = '', $action = '', $options = '') {
        //debug('Form::__construct() ; START');
        if(!empty($method) && TrinacriaRequest::METHOD_GET != $method &&
            TrinacriaRequest::METHOD_POST != $method) {
            throw new Exception('Wrong method. Method available :'
                .' TrinacriaRequest::METHOD_GET, TrinacriaRequest::METHOD_POST');
        }

        if(empty($action)) {
            throw new Exception('Missing action');
        }

        if(empty($method)) {
            $this->method = TrinacriaRequest::METHOD_POST;
        } else {
            $this->method = $method;
        }

        $this->action = $action;
        $this->elements = array();
        $this->aIds = array();
        $this->errorMethod = null;


        if(!empty($options) && is_array($options)) {
            if(!empty($options['error'])) {

                $this->errorMethod = $options['error'];

                unset($options['error']);

                if($this->errorMethod) {
                    switch($this->errorMethod) {
                        case self::ERROR_METHOD_FIELD:
                        case self::ERROR_METHOD_CONTAINER:
                            // DO NOTHING FOR NOW ...
                            // Just "test" the value
                        break;

                        default:
                            $this->errorMethod = null;
                        break;
                    }
                } else {
                    $this->errorMethod = null;
                }
            }

            if(isset($options['id'])) {
                if($options['id'] !== '') {
                    $this->name = $options['id'];
                }

                unset($options['id']);
            }

            $this->options = $options;
        } else {
            $this->options = array();
        }

        if(empty($this->name)) {
            $this->name = 'form-'.(++self::$id);
        }

        if(!isset($this->options['requiredStar'])) {
            $this->options['requiredStar'] = 'field';
        }

        $this->token = str_replace('.', '', uniqid('', true));
        $this->tokenName = 'token-'.$this->name;

        $this->errorsText = array();
        $this->infosText = array();
        $this->successText = array();


        //debug('Form::__construct() ; $this->errorMethod : '.$this->errorMethod);
        //debug('Form::__construct() ; END');
    }

    // Add an element to the form
    public function add($element) {
        //debug('Form->add() ; START');
        //debug('Form->add() : '.get_class($element).' -- '.$element->getName().' -- '.$element->getId());
        if(is_object($element)) {
            if(false == $this->idIsAvailable($element)) {
                throw new Exception('Element name ('.$element->getId().') already exist');
            } else {
                $this->elements[] = $element;
                $this->aIds[] = $element->getId();
                $this->nbElements++;

                //debug('Form->add() : fireEvent(\'addedToParent\')');
                //debug('Form->add() : $this->errorMethod : '.$this->errorMethod);
                $element->fireEvent('addedToParent', array(
                    'parent' => $this,
                    'showError' => $this->errorMethod
                ));

                //~ $element->setParent($this);
            }
        } else {
            throw new Exception('Can\'t add element : it must be an object');
        }

        //debug('Form->add() ; END');
        return $element;
    }

    // Verifie si un id HTML est disponnible
    // en parcourant les elements enfants
    public function idIsAvailable($iElement) {
        return (!in_array($iElement->getId(), $this->aIds));
    }

    // Ajoute un message d'erreur
    public function addErrorText($text) {
        if(!empty($text)) {
            $this->errorsText[] = $text;
        }

        return $this;
    }

    // Ajoute un message d'informations
    public function addInfosText($text) {
        if(!empty($text)) {
            $this->infosText[] = $text;
        }

        return $this;
    }

    // Ajoute un message de "réussite"
    public function addSuccessText($text) {
        if(!empty($text)) {
            $this->successText[] = $text;
        }

        return $this;
    }

    // Unset options "label"
    static public function cleanLabel(&$search, $element) {
        if(array_key_exists('label', $search)) {
            $match = $search['label'];

            if(is_object($match) && 'TrinacriaForm_ElementLabel' == get_class($match)) {
                $element->setLabel($match);
            } else if(is_array($match) && !empty($match['value'])) {
                $element->setLabel(
                    new TrinacriaForm_ElementLabel($match['value'], $element, ((isset($match['options'])) ? $match['options'] : null))
                );
            }

            unset($search['label']);
        }
    }

    // Execute function
    public function execute() {
        //debug(__METHOD__.' : START');

        $score = 0;
        foreach($this->elements as $element) {
            // Each validating return true or false
            $score += (int)$element->check();
            //debug(__METHOD__.' ; $element : '.$element->getId());
            //debug(__METHOD__.' ; $score : '.$score);
        }

        //debug(__METHOD__.' ; $this->nbElements : '.$this->nbElements);

        // If all elements are valid
        // Form can be send
        if($this->nbElements == $score) {
            //debug(__METHOD__.' : END (valid)');
            return true;
        } else {
            //debug(__METHOD__.' :  END (nonvalid)');
            return false;
        }
    }

    public function getDatas($prefix = null, $toExclude = null) {
        //debug('Form->getDatas() : START');
        $d = array();
        if($prefix !== null) {
            $s = strlen($prefix);
        }

        $exclude = array('TrinacriaForm_ElementInput_Submit');

        if(!empty($toExclude) && is_array($toExclude)) {
            foreach($toExclude as $item) {
                if(is_object($item)) {
                    $itemClass = get_class($item);
                } else {
                    $itemClass = $item;
                }

                if(is_string($itemClass) && class_exists($itemClass) && !in_array($itemClass, $exclude)) {
                    $exclude[] = $itemClass;
                }
            }
        }

        foreach($this->elements as $e) {
            //debug('Form->getDatas() ; $e->getNodeType() : '.$e->getNodeType());
            if(!in_array($e->getNodeType(), $exclude)) {
                switch($e->getNodeType()) {
                    case 'TrinacriaForm_Container':
                        //debug('Form->getDatas() ; TrinacriaForm_Container->getDatas(prefix,exclude)');
                        $value = $e->getDatas($prefix, $exclude);
                        //debug('Form->getDatas() ; merge');
                        if($value !== null) {
                            //$d = array_merge($d, $value);
                            $d += $value;
                        }
                        break;

                    case 'TrinacriaForm_ElementRadio':
                        //debug('Form->getDatas() ; TrinacriaForm_ElementRadio->getDatas(prefix)');
                        $value = $e->getDatas($prefix);
                        //debug('Form->getDatas() ; merge');
                        if($value !== null) {
                            //$d = array_merge($d, $value);
                            $d += $value;
                        }
                        break;

                    default:
                        //debug('Form->getDatas() ; default');
                        if($prefix !== null) {
                            $k = substr($e->getName(), $s);
                        } else {
                            $k = $e->getName();
                        }
                        $d[$k] = $e->getValue();
                        break;
                }
            }
        }

        //debug('Form->getDatas() : END');
        return $d;
    }

    // Display
    public function open() {
        $a = '<form method="'.TrinacriaRequest::$methodsAsStr[$this->method]
            .'" action="'.htmlentities($this->action).'" id="'.$this->name.'"';
        if(!empty($this->options)) {
            foreach($this->options as $attribute => $value) {
                if($attribute != 'requiredStar') {
                    $a .= ' '.$attribute.'="'.$value.'"';
                }
            }
        }

        return $a.'>';
    }

    public function getMsg($type = self::MSG_TYPE_ERRORS) {
        $a = '';

        switch($type) {
        case self::MSG_TYPE_ERRORS:
            if(!empty($this->errorsText)) {
                foreach($this->errorsText as $text) {
                    $a .= '<div class="alert alert-error">'.$text.'</div>'."\n";
                }
            }

            break;

        case self::MSG_TYPE_NOTICES:
            if(!empty($this->infosText)) {
                foreach($this->infosText as $text) {
                    $a .= '<div class="alert alert-info">'.$text.'</div>'."\n";
                }
            }

            break;

        case self::MSG_TYPE_SUCCESS:
            if(!empty($this->successText)) {
                foreach($this->successText as $text) {
                    $a .= '<div class="alert alert-success">'.$text.'</div>'."\n";
                }
            }

            break;
        }

        return $a;
    }

    public function close() {
        return '<div><input type="hidden" name="'.$this->tokenName
        .'" value="'.$this->token.'" /></div></form>';
    }

    // Get token
    public function getToken() {
        return $this->token;
    }

    public function getTokenName() {
        return $this->tokenName;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getOption($option) {
        if(isset($this->options[$option])) {
            return $this->options[$option];
        } else {
            return null;
        }
    }

    public function __destruct() {
    }
}
?>
