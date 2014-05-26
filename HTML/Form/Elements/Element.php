<?php
defined('DIRECT_INCLUDE') or die('RESTRICTED ACCESS');
// TODO : check options
require 'HTML/TrinacriaForm/Rules/Rules.php';

abstract class TrinacriaForm_Element {
    static private $idCounter = 0;

    protected $id;
    protected $eParent;
    protected $rules;
    protected $rulesTypes;
    protected $errors;
    protected $errorsText;
    protected $errorMethod;

    // Common methods
    public function __construct($id = null) {
        if(empty($id)) {
            $this->id = ++self::$idCounter;
        } else {
            $this->id = $id;
        }

        $this->eParent = null;
        $this->rules = array();
        $this->rulesTypes = array();
        $this->errors = array();
        $this->errorsText = array();
        $this->errorMethod = null;
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

    public function check() {
        //debug(__METHOD__.' : START');
        //debug(__METHOD__.' ; $element '.$this->getId().' - '.get_class($this));

        //debug(__METHOD__.' : START updateValue()');
        $this->updateValue();
        //debug(__METHOD__.' : END updateValue()');

        $nbRules = count($this->rules);
        if(0 == $nbRules) {
            //debug(__METHOD__.' : $element '.$this->getId().' is valid because no rules');
            //debug(__METHOD__.' : END');
            return true;
        } else {
            $score = 0;
            $rulesTmp = array();
            $rulesTypesTmp = array();

            // s'il existe une regle "depend"
            //if(array_key_exists('depend', $this->rules)) {
            if(in_array('depend', $this->rulesTypes)) {
                //debug('Element->check() ; $element '.$this->getId().' - rule depend exist');
                // on verifie que la ou les dépendances sont validées
                // Pour chaque dépendance, si elle est valide, on retire la règle temporairement
                $i = 0;
                $max = count($this->rulesTypes);
                $stop = false;

                while($stop == false && $i < $max) {
                    if('depend' == $this->rulesTypes[$i]) {
                        if($this->rules[$i]->execute()) {
                            //debug('Element->check() ; $element '.$this->getId().' must to be valided');
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
                    //debug(__METHOD__.' : $element '.$this->getId().' validation skipped - Rule(s) "Depend" not passed');
                    return true;
                }
            }

            //debug('Element->check() ; Validating other rules');
            // Validation des autres règles
            foreach($this->rules as $rule) {
                // Chaque règle retourne true ou false
                $result = $rule->execute();
                //debug('$rule : '.$rule->getName());
                //debug('$result : '.$result);
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
                //debug(__METHOD__.' ; $element '.$this->getId().' is NOT valid.');
                return false;
            } else {
                //debug(__METHOD__.' ; $element '.$this->getId().' is valid');
                return true;
            }
        }

        //debug('Element->check() ; END');
    }

    public function showError($value) {
        //debug('Element->showError() ; START');
        //debug('Element->showError() ; $element '.$this->getId().' - '.get_class($this));
        //debug('Element->showError() ; new value : '.($value ? 'true' : 'false'));
        $this->errorMethod = $value;
        //debug('Element->showError() ; END');
    }

    public function fireEvent($event, $args) {
        //debug(__METHOD__.' : START');

        switch($event) {
            case 'addedToParent':
                //debug(__METHOD__.' : addedToParent');
                $this->eParent = $args['parent'];
                $this->errorMethod = $args['showError'];

                // /!\ TAG_UPDATE_VALUE
                $this->updateValue();
            break;

            case 'updateShowErrorStatus':
                //debug(__METHOD__.' : updateShowErrorStatus');
                $this->errorMethod = $args['showError'];
            break;

            default:
                //debug('Element->fireEvent() ; UNMANAGED EVENT');
            break;
        }

        //debug(__METHOD__.' : END');
    }

    //
    // Getters / Setters
    //

    public function getId() {
        return $this->id;
    }

    public function getNodeType() {
        return get_class($this);
    }

    public function getRules() {
        return $this->rulesTypes;
    }

    public function getParent() {
        return $this->eParent;
    }

    public function setParent($parent) {
        $this->eParent = $parent;
        return $this;
    }

    // Method to redefine
    abstract public function getValue();
    abstract public function updateValue();

    public function toString() {
        return $this->__toString();
    }

    public function __destruct() {
    }
}
?>
