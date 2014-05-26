<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');

class TrinacriaForm_Container {
    static private $id = 0;

    private $name;
    private $elements;
    private $nbElements;
    private $options;
    private $aIds;// tableaux contenant les id HTML des elements
    private $rules;
    private $rulesTypes;
    private $errors;
    private $errorMethod;
    private $eParent;
    private $infos;

    private $errorsText;
    private $infosText;
    private $successText;

    public function __construct($name, $options = null) {
//        debug(__METHOD__.' ; START');
        $this->name = $name;
        $this->elements = array();
        $this->nbElements = 0;
        $this->aIds = array();
        $this->rules = array();
        $this->rulesTypes = array();
        $this->errors = array();
        $this->eParent = null;
        $this->infos = '';

        $this->errorMethod = null;
        if(!empty($options) && is_array($options)) {
            if(!empty($options['error'])) {

                $this->errorMethod = $options['error'];

                unset($options['error']);

                if($this->errorMethod) {
                    switch($this->errorMethod) {
                        case TrinacriaForm::ERROR_METHOD_FIELD:
                        case TrinacriaForm::ERROR_METHOD_CONTAINER:
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

            $this->options = $options;
        } else {
            $this->options = array();
        }

        if(!isset($this->options['requiredStar'])) {
            $this->options['requiredStar'] = 'field';
        }

        $this->errorsText = array();
        $this->infosText = array();
        $this->successText = array();

//        debug(__METHOD__.' ; $this->errorMethod :');
//        debug($this->errorMethod,3);
//        debug(__METHOD__.' ; END');
    }

    public function fireEvent($event, $args) {
        //debug('Container->fireEvent() ; START');

        switch($event) {
            case 'addedToParent':
                //debug('Container->fireEvent() ; Manage event "addedToParent" START');
                $this->eParent = $args['parent'];

                if(empty($this->errorMethod)) {
                    //debug('Container->fireEvent() ; Container->errorMethod is not set');
                    //debug('Container->fireEvent() ; -> set to '.($args['showError']));
                    $this->errorMethod = $args['showError'];
                }

                //debug('Container->fireEvent() ; Manage event "addedToParent" END');
            break;

            default:
                //debug('Container->fireEvent() ; UNMANAGED EVENT');
            break;
        }

        //debug('Container->fireEvent() ; END');
    }

    public function getOption($option) {
        if(isset($this->options[$option])) {
            return $this->options[$option];
        }

        return null;
    }

    public function getMethod() {
        return $this->eParent->getMethod();
    }

    public function getParent() {
        return $this->eParent;
    }

    // Add an element to the form
    public function add($element) {
//        debug(__METHOD__.' ; START');
//        debug(__METHOD__.' ; $this->errorMethod');
//        debug($this->errorMethod);
        //debug(__METHOD__.' ; '.get_class($element).' -- '.$element->getName().' -- '.$element->getId());
        if(is_object($element)) {
            if(false == $this->idIsAvailable($element)) {
                throw new Exception('Element name ('.$element->getId().') already exist');
            } else {
                $this->elements[] = $element;
                $this->aIds[] = $element->getId();
                $this->nbElements++;

                $element->fireEvent('addedToParent', array(
                    'parent' => $this,
                    'showError' => $this->errorMethod
                ));
            }
        } else {
            throw new Exception('Can\'t add element : it must be an object');
        }

        //debug('Container->add() ; END');

        return $element;
    }

    // Verifie si un id HTML est disponnible
    // en parcourant les elements enfants
    public function idIsAvailable($iElement) {
        return (!in_array($iElement->getId(), $this->aIds));
    }

    //public function addRule($rule, $parameters = null, $msg = null) {
    public function addRule($rule, $parameters = null, $options = null) {
        if(!TrinacriaForm_Rules::isRule($rule)) {
            throw new Exception('Rule "'.$rule.'" does\'t exist');
        } else {
            $ruleClass = 'TrinacriaForm_Rule'.ucfirst(strtolower($rule));
            //$this->rules[] = new $ruleClass($this, $parameters, $msg);
            $this->rules[] = new $ruleClass($this, $parameters, $options);
            $this->rulesTypes[] = $rule;
        }

        return $this;
    }

    public function addErrorText($text) {
        if(!empty($text)) {
            $this->errorsText[] = $text;
        }

        return $this;
    }

    public function showError($value) {
        $this->errorMethod = $value;
    }

    public function setParent($parent) {
        $this->eParent = $parent;

        return $this;
    }

    public function setInfos($a) {
        $this->infos = $a;
        return $this;
    }

    public function getNodeType() {
        return get_class($this);
    }

    public function getId() {
        return $this->name;
    }

    public function getName() {
        return $this->name;
    }

    public function getDatas($prefix, $toExclude = array()) {
        $d = array();

        if(array_key_exists('depend', $this->rules)) {
            // on verifie que la dependance est validee
            // Si la dependance est validee, on recupere les champ
            // sinon √ßa ne sert √† rien ...
            if(!$this->rules['depend']->execute()) {
                return $d;
            }
        }

        $s = strlen($prefix);
        $exclude = array('TrinacriaForm_ElementInput_Submit');

        if(!empty($toExclude) && is_array($toExclude)) {
            //debug('exclude');
            foreach($toExclude as $item) {
                if(is_object($item)) {
                    //debug('is_object');
                    $itemClass = get_class($item);
                } else {
                    //debug('!is_object');
                    $itemClass = $item;
                }

                if(is_string($itemClass) && class_exists($itemClass &&
                        !in_array($itemClass, $exclude))) {
                    //debug('add to array');
                    $exclude[] = $itemClass;
                }
            }
        }

        foreach($this->elements as $e) {
            //debug('Container->getDatas() ; $e->getNodeType() : '.$e->getNodeType());
            if(!in_array($e->getNodeType(), $exclude)) {
                switch($e->getNodeType()) {
                    case 'TrinacriaForm_Container':
                        //debug('Container->getDatas() ; TrinacriaForm_Container->getDatas(prefix,exclude)');
                        $value = $e->getDatas($prefix, $exclude);
                        //debug('Container->getDatas() ; merge');
                        //debug('$value : ');
                        //debug($value,3);
                        if($value !== null) {
                            //$d = array_merge($d, $value);
                            $d += $value;
                        }
                        break;

                    case 'TrinacriaForm_ElementRadio':
                        //debug('Container->getDatas() ; TrinacriaForm_ElementRadio->getDatas(prefix)');
                        $value = $e->getDatas($prefix);
                        //debug('Container->getDatas() ; merge');
                        //debug('$value : ');
                        //debug($value,3);
                        if($value !== null) {
                            //$d = array_merge($d, $value);
                            $d += $value;
                        }
                        break;

                    default:
                        //debug('Container->getDatas() ; default');
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

        //~ foreach($this->elements as $e) {
            //~ if(!in_array($e->getNodeType(), $exclude)) {
                //~ if($e->getNodeType() == 'TrinacriaForm_Container') {
                    //~ $value = $e->getDatas($prefix, $exclude);
                    //~ $d = array_merge($d, $value);
                //~ } else {
                    //~ $k = substr($e->getName(), $s);
                    //~ $d[$k] = $e->getValue();
                //~ }
            //~ }
        //~ }

        return $d;
    }

    public function check() {
        //debug(__METHOD__.' : START');
        //debug(__METHOD__.' : Validating $container '.$this->name);

        $nbRules = count($this->rules);
        if($nbRules > 0) {
            $score = 0;
            $rulesTmp = array();
            $rulesTypesTmp = array();

            // s'il existe une regle "depend"
            //if(array_key_exists('depend', $this->rules)) {
            if(in_array('depend', $this->rulesTypes)) {
                //debug('Container->check() ; $container '.$this->name.' - "Depend rule" exist');
                // on verifie que la ou les dépendances sont validées
                // Pour chaque dépendance, si elle est valide, on retire la règle temporairement
                $i = 0;
                $max = count($this->rulesTypes);
                $stop = false;

                while($stop == false && $i < $max) {
                    if('depend' == $this->rulesTypes[$i]) {
                        if($this->rules[$i]->execute()) {
                            //debug('Container->check() ; $container '.$this->name.' must to be valided');
                            $rulesTmp[] = $this->rules[$i];
                            $rulesTypesTmp[] = $this->rulesTypes[$i];

                            $score++;

                            unset($this->rules[$i], $this->rulesTypes[$i]);
                        } else {
                            // Inutile de poursuivre. Une dépendance au moins n'est pas validée
                            $stop = true;
                        }
                    }
                    $i++;
                }

                // Validation sautée
                if($stop) {
                    //debug(__METHOD__.' : $container '.$this->name.' validation skipped - Rule(s) "Depend" not passed');
                    return true;
                }
            }

            //debug(__METHOD__.' : Validating other rules');
            // Validation des autres règles
            foreach($this->rules as $rule) {
                // Chaque règle retourne true ou false
                $result = $rule->execute();
                //debug('$rule : '.$rule->getName());
                //debug('$result : ');
                //debug($result, 3);
                if(!$result) {
                    $this->errors[] = $rule;
                }
                $score += (int)$result;
            }

            if(!empty($rulesTmp)) {
                array_merge($this->rules, $rulesTmp);
            }

            if(!empty($rulesTypesTmp)) {
                array_merge($this->rulesTypes, $rulesTypesTmp);
            }

            if($nbRules != $score) {
                //debug('$container '.$this->name.' is NON valid. Skip children validation');
                return false;
            }

            //debug(__METHOD__.' : $container '.$this->name.' is valid');
        } else {
            //debug(__METHOD__.' : $container '.$this->name.' is valid because no rules');
        }

        // Si container validée (regles, ou absence de regle)
        //debug(__METHOD__.' : Run Container->execute();');
        $score = 0;
        foreach($this->elements as $element) {
            // Chaque règle retourne true ou false
            $score += (int)$element->check();
            //debug('$score : '.$score);
        }

        //debug(__METHOD__.' : $this->nbElements : '.$this->nbElements);

        if($this->nbElements == $score) {
            //debug(__METHOD__.' : $element '.$this->name.' est valide. Ses enfants aussi');
            //debug(__METHOD__.' : END');
            return true;
        } else {
            //debug(__METHOD__.' : $element '.$this->name.' n\'est pas valide √† cause de ses enfants');
            //debug(__METHOD__.' : END');
            return false;
        }
    }

    public function updateValue() {
        // do nothing
        //debug('Container->updateValue() ; START/END ; DO NOTHING');
    }

    public function addToLabel($str) {
        if(!empty($this->options['text'])) {
            $this->options['text'] .= ' '.$str;
        }

        return $this;
    }

    public function open() {
        $a = '';
        $b = '';

        if(!empty($this->options['text'])) {
            $a .= $this->options['text'];
        }

        if(!empty($this->errorMethod)) {
            if(!empty($this->errors)) {
                foreach($this->errors as $rule) {
                    $b .= '<div class="alert alert-error">'.$rule->getErrorMsg().'</div>'."\n";
                }
            }

            if(!empty($this->errorsText)) {
                foreach($this->errorsText as $text) {
                    $b .= '<div class="alert alert-error">'.$text.'</div>'."\n";
                }
            }

            $b .= "\n";
        }

        return $b.$a;
    }

    public function close() {
        $a = '';

        if(!empty($this->infos)) {
            $a = $this->infos;
        }

        return $a;
    }

    public function getMsg($type = 'errors') {
        $a = '';

        switch($type) {
        case 'errors':
            if(!empty($this->errorsText)) {
                foreach($this->errorsText as $text) {
                    $a .= '<div class="alert alert-error">'.$text.'</div>'."\n";
                }
            }

            break;

        case 'notices':
            if(!empty($this->infosText)) {
                foreach($this->infosText as $text) {
                    $a .= '<div class="alert alert-info">'.$text.'</div>'."\n";
                }
            }

            break;

        case 'success':
            if(!empty($this->successText)) {
                foreach($this->successText as $text) {
                    $a .= '<div class="alert alert-success">'.$text.'</div>'."\n";
                }
            }

            break;
        }

        return $a;
    }

    public function __toString() {
        return '';
    }

    public function __destruct() {
    }
}
?>
