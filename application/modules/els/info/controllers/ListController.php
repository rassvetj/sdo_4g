<?php
class Info_ListController extends HM_Controller_Action_Crud
{

    /**
     * Инит формы
     * @see HM_Controller_Action::init()
     */
    public function init() {
        $this->_setForm(new HM_Form_Info());
        parent::init();
    }

    /**
     * Грид с инфо-новостями
     */
    public function indexAction ()
    {
        $service = $this->getService('Info');
        $grid = $service->configureGrid($this->getGrid());
        $this->view->grid = $grid->deploy();
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }
    
    /**
     * Меняет видимость инфо-новостей на противоположную
     */
    public function visreversAction()
    {
        $arID = $this->_getParam('nID',$this->_getParam('postMassIds_grid',array()));
        
        //дальше работаем с массивом
        if ( !is_array($arID) ) {
            $arID = explode(',', $arID);
        }
        $arID = array_unique($arID);
        
        if ( !$arID ) {
            
            $this->_flashMessenger->addMessage(_('Не выбраны элементы'));
            $this->_redirector->gotoSimple('index','list','info');
            
        } else {
            
            $service = $this->getService('Info');
            
            $arInfo = $service->fetchAll($service->quoteInto('nID IN(?)',$arID));
            
            if ( count ($arInfo)) {
                foreach($arInfo as $info) {
                    $info->invertVisible();
                    $service->update($info->getValues());
                }
                $this->_flashMessenger->addMessage(_('Видимость успешно изменена'));
                $this->_redirector->gotoSimple('index','list','info');
            } else {
                $this->_flashMessenger->addMessage(_('При изменении видимости произошла ошибка'));
                $this->_redirector->gotoSimple('index','list','info');
            }
        }
    }
    
    /* (non-PHPdoc)
     * @see HM_Controller_Action_Crud::setDefaults()
     */
    public function setDefaults(Zend_Form $form) 
    {
        $nID = (int) $this->_getParam('nID', 0);
        $info = $this->getOne($this->getService('Info')->find($nID));
        if ( $info ) {
            $data = $info->getValues();
            $data['resource_id'] = $this->getService('Resource')->setDefaultRelatedResources($data['resource_id']);
            $form->setDefaults($data);
        }
    }
    
     /* (non-PHPdoc)
      * @see HM_Controller_Action_Crud::update()
      */
     public function update(Zend_Form $form) 
     {
        $resourceIds = $form->getValue('resource_id');
        $this->getService('Info')->update(array(
            'nID' => $form->getValue('nID'),
            'show' => $form->getValue('show',0),
            'Title' => $form->getValue('Title',''),
			'Title_translation' => $form->getValue('Title_translation',''),
            'message' => $form->getValue('message',''),
            'translation' => $form->getValue('translation',''),
            'resource_id' => count($resourceIds) ? array_shift($resourceIds) : 0,
        ));
     }
     
    /* (non-PHPdoc)
     * @see HM_Controller_Action_Crud::delete()
     */
    public function delete($id)
    {
        return $this->getService('Info')->delete($id);
    }
    
    /* (non-PHPdoc)
     * @see HM_Controller_Action_Crud::create()
     */
    public function create(Zend_Form $form)
    {
        $resourceIds = $form->getValue('resource_id');
        $this->getService('Info')->insert(array(
            'show' => $form->getValue('show',0),
            'Title' => $form->getValue('Title',''),
			'Title_translation' => $form->getValue('Title_translation',''),
            'message' => $form->getValue('message',''),
            'translation' => $form->getValue('translation',''),
            'resource_id' => count($resourceIds) ? array_shift($resourceIds) : 0,
        ));
    }
}
