<?php
class HM_Form_Multi extends HM_Form
{

    /**
     * @var Zend_Session_Namespace
     */
    private $_session = null;
    private $_currentForm = null;    
    private $_redirector = null;
    
    protected $_namespace = 'multiform';

    public function init()
    {
        $this->_redirector = new HM_Controller_Action_Helper_ConditionalRedirector();
        $this->getSessionNamespace();
        if (!$form = $this->getCurrentSubForm()) {
            $form = $this->getNextSubForm();
        }

        $this->_currentForm = $form;
    }

    public function getSessionNamespace()
    {
        if (null == $this->_session)
        {
            $this->_session = new Zend_Session_Namespace($this->_namespace);
        }
        return $this->_session;
    }

    public function setRedirector($redirector)
    {
        $this->_redirector = $redirector;
    }

    /**
     * @return HM_Controller_Action_Helper_ConditionalRedirector
     */
    public function getRedirector()
    {
        return $this->_redirector;
    }

    public function getStoredForms()
    {
        $stored = array();
        foreach ($this->_session as $key => $values) {
            $stored[] = $key;
        }

        return $stored;
    }

    public function getPotentialForms()
    {
        return array_keys($this->getSubForms());
    }

    public function getCurrentSubForm()
    {
        $subForm = $this->getParam('subForm', false);
        if (!$subForm) {
            if (Zend_Session::namespaceIsset($this->_namespace)) {
                Zend_Session::namespaceUnset($this->_namespace);
                $this->getRedirector()->gotoUrlAndExit($this->getView()->url());
            }

            return false;
        }

        foreach ($this->getPotentialForms() as $name) {
            if ($name == $subForm) {
                $form = $this->getSubForm($name);
                $delete = false;
                foreach($this->_session as $sessionName => $values) {
                    if ($sessionName == $name) {
                        $form->setDefaults($values);
                        $delete = true;
                    }
                    if ($delete) {
                        unset($this->_session->$sessionName);
                    }
                }
                return $form;
            }
        }

        return false;
    }

    public function getNextSubForm()
    {
        $storedForms    = $this->getStoredForms();
        $potentialForms = $this->getPotentialForms();
        foreach ($potentialForms as $name) {
            if (!in_array($name, $storedForms)) {
                $form = $this->getSubForm($name);
                return $form;
            }
        }
        return false;
    }

    public function __toString()
    {
        try {
            $return = $this->_currentForm->render();
            return $return;
        } catch (Exception $e) {
            $message = "Exception caught by form: " . $e->getMessage()
                     . "\nStack Trace:\n" . $e->getTraceAsString();
            trigger_error($message, E_USER_WARNING);
            return '';
        }
    }

    public function isValid($data)
    {   
        $name = $this->_currentForm->getName();
        $subForm = $this->getSubForm($name);

        if ($subForm->isValid($data))
        {
            $this->_session->$name = $subForm->getValues();
            //$this->getRedirector()->gotoSimple();
            $this->_currentForm = $this->getNextSubForm();

            if ($this->_currentForm) {
                $this->getRedirector()->gotoUrlAndExit($this->getView()->url(array('subForm' => $this->_currentForm->getName())));
            }

        } else {
            return false;
        }

        if ($this->_currentForm) return false;

        $data = array();
        foreach ($this->_session as $name => $info) {
            if (is_array($info) && count($info)) {
                foreach($info as $key => $value) {
                    $data[$key] = $value;
                }
            }
        }
        $valid = parent::isValid($data);
        if ($valid) {
            Zend_Session::namespaceUnset($this->_namespace);            
        }
        
        return $valid;
    }

    public function getValues()
    {
        $result = array();
        foreach(parent::getValues() as $key => $values) {
            if (is_array($values) && count($values)) {
                foreach($values as $k => $v) {
                    $result[$k] = $v;
                }
            }
        }

        return $result;
    }

    public function getValue($name) {
        $values = $this->getValues();
        return $values[$name];
    }

    public function getClassifierValues()
    {
        $values = array();
        $subforms = $this->getSubForms();
        if(count($subforms)){
            foreach($subforms as $subform){
                $value = $subform->getClassifierValues();
                if (is_array($value) && count($value)) {
                    $values = array_merge($values, $value);
                }
            }
        }
        return $values;
    }

}