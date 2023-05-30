<?php
/**
 * Created by JetBrains PhpStorm.
 * Date: 12.04.12
 * Time: 11:27
 * To change this template use File | Settings | File Templates.
 */

abstract class HM_Form_Modifier{

    /* @var $_form HM_Form */
    protected $_form = null;

    public function init()
    {
        $actions = $this->_getActions();

        foreach($actions as $action){
            $actionName = $action['name'];
            $this->applyAction($actionName, $action);
        }


    }
    public function setForm($form)
    {
        $this->_form = $form;
    }

    public function getForm()
    {
        return $this->_form;
    }

    /**
     * @abstract
     * @return array
     */
    abstract protected function _getActions();


    /**
     * Applying action to element
     * Possible actionTypes = array('remove', 'setValue', 'changeType', 'setParam')
     *
     * @param $elementName
     * @param array $action
     */
    protected function applyAction($elementName, Array $action)
    {
        /* @var $element Zend_Form_Element */
        switch($action['type']){
            case "remove":
                $element = $this->_form->getElement($elementName);
                if($element){
                    $this->_form->removeElement($elementName);
                }
                break;
            case "setValue":
                $element = $this->_form->getElement($elementName);
                if($element){
                    $value = $action['value'];
                    $element->setValue($value);
                }
                break;
            case "setOption":
                $element = $this->_form->getElement($elementName);
                if($element){
                    $name = $action['paramName'];
                    $value = $action['paramValue'];
                    $element->setOptions(array($name => $value));
                }
                break;
            case "setOptions":
                $element = $this->_form->getElement($elementName);
                if($element){
                    $value = $action['paramValue'];
                    $element->setOptions($value);
                }
                break;

            case "changeType":

                $element = $this->_form->getElement($elementName);

                if($element){
                    $type =  $action['element_type'];


                    $value = $element->getValue();
                    $filters = $element->getFilters();
                    $validators = $element->getValidators();
                    $required = $element->isRequired();

                    $this->_form->removeElement($elementName);
                    $this->_form->addElement($type,
                        $elementName,
                        array(
                            'Required'   => $required,
                            //'Validators' => $validators,
                            //'Filters'    => $element->getFilters(),
                            'Value'      =>  $value
                        )
                    );
                    $element = $this->_form->getElement($elementName);
                    foreach($filters as $filter){
                        $element->addFilter($filter);
                    }
                    foreach($validators as $validator){
                        $element->addValidator($validator);
                    }
                }

                break;

            default:
                throw new HM_Exception('There is no valid actionType.');
        }
    }


    /**
     * Return array with current accessible elements (some elements could be removed after applying actions
     * and we shouldn't try to update this fields ).
     * @return array
     */
    public function getElements()
    {
        $elements = $this->_form->getElements();

        $res = array();
        foreach($elements as $element){
            $res[] = $element->getName();
        }
        return $res;
    }

    /**
     * Proxy for all form method call
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws HM_Exception
     */
    public function __call($name, $arguments)
    {

        if(method_exists($this->_form, $name)){
            return call_user_func_array(array($this->_form, $name), $arguments);
        }
        throw new HM_Exception('Call to undefined method ' . $name);
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }




}