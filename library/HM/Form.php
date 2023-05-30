<?php

class HM_Form extends ZendX_JQuery_Form {

    private $_serviceContainer = null;
    private $_wysiwygElementName = 'textarea';
    private $_fileElementName = 'File';
    private $_isAjaxRequest = null;
    private $__isValid = true;
    private $_elementsWithPrefix = array();

    protected $_modifiers = array();

    protected $_classifierElements = false;

    public function __construct($options = null) {
        $this->addPrefixPath('HM_Form_Element', 'HM/Form/Element/', 'element');
        $this->addPrefixPath('HM_Form_Decorator', 'HM/Form/Decorator/', 'decorator');
        $this->addElementPrefixPath('HM_Validate', 'HM/Validate', 'validate');
        $this->addElementPrefixPath('HM_Validate_File', 'HM/Validate/File', 'validate');
        $this->addElementPrefixPath('HM_Filter', 'HM/Filter', 'filter');
        $this->_wysiwygElementName = Zend_Registry::get('config')->wysiwyg->editor;
        $this->_fileElementName = Zend_Registry::get('config')->form->file->uploader;
        parent::__construct($options);
    }

    public function init() {

        foreach($this->getElements() as $element) {
            $element->addFilter('StringTrim');
            if (!$element->loadDefaultDecoratorsIsDisabled()) {
                if (!in_array($element->getType(),
                    array(
                        /*'Zend_Form_Element_Captcha'*/
                    )
                )) {
                    if ($element->getType() == 'Zend_Form_Element_Hidden') {
                        $element->setDecorators($this->getHiddenElementDecorators($element->getName()));
                    }elseif(in_array($element->getType(), array(
                        'HM_Form_Element_Lists'
                    ))) {
                        $element->setDecorators($this->getCustomElementDecorators($element->getName()));
                    }elseif(in_array($element->getType(), array('Zend_Form_Element_File'))) {
                        $element->setDecorators($this->getFileElementDecorators($element->getName()));
                    }elseif(in_array($element->getType(),
                        array(
                            'Zend_Form_Element_Submit',
                            'Zend_Form_Element_Button',
                            'HM_Form_Element_SubmitCancel'
                        ))) {
                        $element->setDecorators($this->getButtonElementDecorators($element->getName()));
                    } elseif ($element instanceof Zend_Form_Element_Checkbox) {
                        $element->setDecorators($this->getCheckBoxDecorators($element->getName()));
                    } else {
                        if ($element instanceof ZendX_JQuery_Form_Element_UiWidget) {
                            $element->setDecorators($this->getElementDecorators($element->getName(), 'UiWidgetElement'));
                        } else {
                            $element->setDecorators($this->getElementDecorators($element->getName()));
                        }
                    }
                }
            }
        }

        $this->setDisplayGroupDecorators(
            array(
                'FormElements',
                array('HtmlTag', array('tag' => 'dl')),
                'Fieldset',
                //'DtDdWrapper'
            )
        );
        /*
        $this->setDisplayGroupDecorators(array(
             'FormElements',
             'Legend',
             array('HtmlTag', array('tag' => 'table', 'border' => 0, 'width' => '100%', 'class' => 'main')),
             array(array('br' => 'HtmlTag'), array('tag' => 'br', 'placement' => 'append'))
        ));
         *
         */

        $displayGroups = $this->getDisplayGroups();
        if (count($displayGroups)>1) {
            $this->getView()->headScript()->appendFile($this->getView()->serverUrl('/js/content-modules/fieldset.js'));
        }
        $this->getView()->headLink()->appendStylesheet( $this->getView()->serverUrl('/css/content-modules/forms.css') );

        $translator = new Zend_Translate('array', APPLICATION_PATH.'/system/errors.php');
        $this->setTranslator($translator);
    }

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();

        if (empty($decorators)) {

            $this->addDecorator('FormElements')
                 ->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'form'))
                 ->addDecorator('Form');
        }
    }

    public function getCheckBoxDecorators($alias, $first = 'ViewHelper')
    {
        return array (
            array($first),
            array('RedErrors'),
            //array('Description', array('tag' => 'p', 'class' => 'description')),
            array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
            array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
        );

    }

    public function getElementDecorators($alias, $first = 'ViewHelper') {
        return array ( // default decorator
                array($first),
                array('RedErrors'),
                //array('Description', array('tag' => 'p', 'class' => 'description')),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
                array('Label', array('tag' => 'dt'))

        );
        /*
        return array ( // default decorator
                array($first),
                array('RedErrors'),
                array('Description', array('tag' => 'p', 'class' => 'description')),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'  => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
        );
         *
         */
    }

    public function getHiddenElementDecorators($alias, $first = 'ViewHelper') {
        return array ( // default decorator
                array($first)
        );
    }

    public function getFileElementDecorators($alias, $first = 'ViewHelper') {
        return $this->getElementDecorators($alias, 'File');
    }

    public function getButtonElementDecorators($alias, $first = 'ViewHelper') {
        $decorators = array($first);

        if (null != $this->getElement('prevSubForm')) {
            $decorators[] = array(array('prev' => 'Button'), array('placement' => 'prepend', 'label' => _('Назад'), 'url' => $this->getView()->url(array('subForm' => $this->getElement('prevSubForm')->getValue()))));
        }

        if (null != $this->getElement('cancelUrl')) {
            $decorators[] = array(array('cancel' => 'Button'), array('placement' => 'append', 'label' => _('Отмена')/*, 'url' => $this->getElement('cancelUrl')->getValue()*/));
        }

        if (null != $this->getElement('previewUrl')) {
            $decorators[] = array(array('preview' => 'Button'), array(
                'placement' => 'append', 
                'label' => _('Предварительный просмотр'), 
                'onClick' => $this->getElement('previewUrl')->getAttrib('onClick'),
            ));
        }

        $decorators = array_merge($decorators, array(
            array(array('data' => 'HtmlTag'), array('tag' => 'dd'))
        /*
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'  => 'element', 'colspan' => 2, 'align' => 'right')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
            */
        ));
        return $decorators;
    }

    public function getCustomElementDecorators($alias, $first = 'ViewHelper') {
        $decorators = array(
            array(array('data' => 'HtmlTag'), array('tag' => 'dd'))
        /*
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'  => 'element', 'colspan' => 2, 'align' => 'right')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
            */
        );
        return $decorators;
    }

    public function getMessagesUtf8()
    {
        $messages = $this->getMessages();
        if (is_array($messages) && count($messages)) {
            foreach($messages as &$errors) {
                if (is_array($errors) && count($errors)) {
                    foreach($errors as &$error) {
                        $error = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $error);
                    }
                }
            }
        }
        return $messages;
    }

    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    public function isAjaxRequest()
    {
        if (null === $this->_isAjaxRequest) {
            if ($this->getRequest()->isXmlHttpRequest()
                || $this->getRequest()->getParam('ajax', false)
                || ($this->getRequest()->getParam('gridmod') == 'ajax')) {

                $this->_isAjaxRequest = true;
            } else {
                $this->_isAjaxRequest = false;
            }
        }
        return $this->_isAjaxRequest;
    }

    public function getValue($name, $cp1251 = null)
    {
        $value = parent::getValue($name);
        if ($this->isAjaxRequest() && !$cp1251) {
            if (is_string($value)) {
                $value = iconv("UTF-8", Zend_Registry::get('config')->charset, $value);
            }

            // todo: массив
        }
        return $value;
    }

    public function getValues($suppressArrayNotation = false)
    {
        $values = parent::getValues($suppressArrayNotation);
        unset($values['submit']);
        unset($values['cancelUrl']);

        if ($this->isAjaxRequest()) {
            foreach($values as $key => &$value) {
                if (is_string($value)) {
                    $value = iconv("UTF-8", Zend_Registry::get('config')->charset, $value);
                }
            }
        }

        return $values;
    }

    public function getParam($name, $default) {
        return $this->getRequest()->getParam($name, $default);
    }

    public function setServiceContainer($container)
    {
        $this->_serviceContainer = $container;
    }

    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        if (null == $this->_serviceContainer) {
            $this->_serviceContainer = Zend_Registry::get('serviceContainer');
        }
        return $this->_serviceContainer->getService($name);
    }

    public function getDefaultWysiwygElementName()
    {
        return $this->_wysiwygElementName;
    }

    public function getDefaultFileElementName()
    {
        return $this->_fileElementName;
    }

    public function render(Zend_View_Interface $view = null)
    {
        $result = parent::render($view);
        if (!$this->__isValid) {
            $result .= $this->getView()->Notifications(array(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Внимание! Не все поля заполнены корректно!'))), array('html' => true));
        }
        return $result;
    }

    public function isValid($data)
    {
        $result = parent::isValid($data);
        if (!$result)
        {
            $this->__isValid = false;
        }
        return $result;
    }

    public function addClassifierElements($linkType, $itemId = 0, $current = '', $classifiers_types = array())
    {
        $ret = array();
        $classifier_types = $this->getService('ClassifierType')->getClassifierTypes($this->checkLinkType($linkType), $classifiers_types);
        if (count($classifier_types)) {
            foreach($classifier_types as $type) {
                $name = 'classifier_'.$type->type_id;
                $this->addElement('UiMultiSelect', $name,
                    array(
                        'Label' => _($type->name),
                        'Required' => false,
                        'Filters' => array(
                            'Int'
                        ),
                        'jQueryParams' => array(
                            'sortable' => false,
                            'nodeComparator' => false,
                            'remoteUrl' => $this->getView()->url(
                                array(
                                    'module' => 'classifier',
                                    'controller' => 'ajax',
                                    'action' => 'list',
                                    'item_id' => $itemId,
                                    'item_type' => $linkType,
                                    'type' => $type->type_id,
                                    'current' => $current
                                ),
                                null,
                                true
                            )
                        ),
                        'multiOptions' => array(),
                        'class' => 'multiselect'
                    )
                );

                $ret[] = _($name);
                $this->_classifierElements[] = _($name);
            }
        }

        //$this->_classifierElements = $ret;
        return $ret;
    }

    protected function checkLinkType($linkType){

        $linkType = (int) $linkType;

        if (in_array($linkType, HM_Classifier_Link_LinkModel::getResourceTypes())) {
            $linkType = HM_Classifier_Link_LinkModel::TYPE_RESOURCE;
        }

        if (in_array($linkType, HM_Classifier_Link_LinkModel::getUnitTypes())) {
            //$linkType = HM_Classifier_Link_LinkModel::TYPE_UNIT;
        }

        return $linkType;

    }

    public function addClassifierDisplayGroup($classifierElements = null, $legend = null)
    {
        if (null === $legend) {
            $legend = _('Классификация');
        }

        if (null === $classifierElements) {
            $classifierElements = $this->_classifierElements;
        }

        if ($classifierElements) {
            $this->addDisplayGroup(
                $classifierElements,
                'classifiers',
                array('legend' => $legend)
            );
        }
    }

    public function getNonClassifierValues()
    {
        $values = array();
        foreach ($this->getValues() as $key => $value) {
            if (!in_array($key, $this->_classifierElements)) {
                $values[$key] = $value; 
            }
        }  
        return $values;      
    }
    public function getClassifierValues()
    {
        $values = array();
        if ($this->_classifierElements) {
            foreach($this->_classifierElements as $name) {
                $value = $this->getValue($name);
                if (is_array($value) && count($value)) {
                    $values = array_merge($values, $value);
                } elseif (!empty($value)) {
                    $values[] = $value; // бывают плоские классификаторы
                }
            }
        }
        return $values;
    }

    public function getClassifierTypes()
    {
        $values = array();
        if ($this->_classifierElements) {
            foreach($this->_classifierElements as $name) {
                $values[] = str_replace('classifier_', '', $name);
            }
        }
        array_unique($values);
        return $values;
    }

    public function addElement($element, $name = null, $options = null)
    {
        if (is_string($element)) {
            if (strtolower($element) == 'textarea') {
                if (!isset($options['cols']) && Zend_Registry::get('config')->form->textarea->cols) {
                    $options['cols'] = Zend_Registry::get('config')->form->textarea->cols;
                }

                if (!isset($options['rows']) && Zend_Registry::get('config')->form->textarea->rows) {
                    $options['rows'] = Zend_Registry::get('config')->form->textarea->rows;
                }
            }

            if (isset($options['Validators'])) {
                foreach ($options['Validators'] as $key => $validator) {
                    if ($validator[0] == 'StringLength' &&
                        is_array($validator[2]) &&
                        !array_key_exists('encoding',$validator[2]) &&
                        Zend_Registry::get('config')->charset) {
                        $options['Validators'][$key][2]['encoding'] = Zend_Registry::get('config')->charset;
                    }
                }
            }
        }
        parent::addElement($element, $name, $options);
    }

    /*
     * @var $modifier HM_Form_Modifier
     *
     */
    public function addModifier($modifier){
        $this->_modifiers[] = $modifier;
        $modifier->setForm($this);
        $modifier->init();

        return $this;
    }

    public function getModifiers()
    {
        return $this->_modifiers;
    }

    public function hasModifier($className)
    {
        foreach($this->getModifiers() as $modifier){
            if($modifier instanceof $className){
                return true;
            }
        }

        return False;
    }

    /*
     * elements  = array();
     * $prefixes = array('lang'=>'desc') or null;
     */
    public function addElementsPrefixLanguages($elements,$prefixes=null)
    {
        $addingElements = array();

        if($this->getService('Lang')->countLanguages() <= 1)
            return false;
        if(!count($elements))
            return false;
        if($prefixes === null){
            $prefixes = Zend_Registry::get('config')->form->more->languages;
        }


        if(count($prefixes) && count($elements)){
            foreach($prefixes as $prefix => $desc){
                foreach($elements as $elementName){
                    $element =  clone $this->getElement($elementName);
                    if($element){
                        $element->setLabel($this->getLabelWithDesc($element,$desc));
                        $element->setName($this->getNameWithPrefix($element,$prefix));
                        $this->addElement($element);
                        //$displayGroup->addElement($this->getElement($element->getName()));
                        $this->setElementWithPrefix($element->getName());
                    }
                }
            }
        }
    }

    public function setElementWithPrefix($name)
    {
        $this->_elementsWithPrefix[] = $name;
    }

    public function getElementsWithPrefix()
    {
        if(count($this->_elementsWithPrefix))
            return $this->_elementsWithPrefix;
        else
            return false;
    }

    public function unsetElementsWithPrefix()
    {
        $this->_elementsWithPrefix = array();
    }

    protected function getNameWithPrefix($element,$prefix){
        if(strlen($prefix)){
            return $element->getName()."_".$prefix;
        }
        return $element->getName();
    }

    protected function getLabelWithDesc($element,$desc){
        if(strlen($desc)){
            return $element->getLabel()." ("._($desc).") ";
        }
        return $element->getLabel();
    }

    public function addDisplayGroup($elements,$name,$options = null)
    {
        $elementsWithPrefix = $this->getElementsWithPrefix();
        if($elementsWithPrefix){
            $maxPosition =  $this->getMaxPositionElementsFromDisplayGroupByName($elements,$elementsWithPrefix)+1;
            $elements =  $this->getSliceElementByPosition($elements, $maxPosition);
            $elements = array_merge($elements['first'],$elementsWithPrefix,$elements['end']);
            $this->unsetElementsWithPrefix();
        }

        parent::addDisplayGroup($elements,$name,$options);
    }

    protected function getMaxPositionElementsFromDisplayGroupByName($elements,$elementsWithPrefix)
    {
        $positionElements = array();
        $prefixes = Zend_Registry::get('config')->form->more->languages;
        foreach($elementsWithPrefix as $elementWithPrefix){
            foreach($prefixes as $key => $desc){
                $elementWithPrefix = str_replace('_'.$key,'',$elementWithPrefix);
                if(array_search($elementWithPrefix,$elements)){
                    $positionElements[] = array_search($elementWithPrefix,$elements);
                }
            }
        }
        return max($positionElements);
    }

    protected function getSliceElementByPosition($elements = array(), $maxPosition = 0)
    {
        $slice = array();
        if(count($elements) && $maxPosition > 0){
            $slice['first'] = array_slice($elements,0,$maxPosition);
            $slice['end'] = array_slice($elements,(count($slice['first'])));
        }
        return $slice;
    }

}