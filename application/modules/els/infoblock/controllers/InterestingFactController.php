<?php

class Infoblock_InterestingFactController extends HM_Controller_Action_Crud
{
    
    public function init()
    {
        $this->_setForm(new HM_Form_Fact());
        parent::init();
        
    }
    
    protected function _redirectToIndex()
    {

        $this->_redirector->gotoSimple('index', 'interesting-fact', 'infoblock', array());
        parent::_redirectToIndex();
    }
    

	public function indexAction()
	{
	    $select = $this->getService('InfoblockFact')->getSelect();
	    $select->from('interesting_facts', array('interesting_facts_id', 'title', 'text', 'status'));
	    
	    $grid = $this->getGrid(
            $select,
            array(
                'interesting_facts_id' => array('hidden' => true),
                'title'  => array('title' => _('Название') /*'decorator' => $this->view->cardLink($this->view->url(array('action' => 'card', 'resource_id' => '')).'{{resource_id}}', _('Карточка ресурса')).'<a href="'.$this->view->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'index', 'resource_id' => '{{resource_id}}'), null, false, false).'">{{title}}</a>'*/),
                'text'   => array('title' => _('Содержание')),
                'status' => array('title' => _('Статус')),
            ),
            array(
                'title' => null,
                'status' => array('values' => array(HM_Infoblock_Fact_FactModel::STATUS_UNPUBLISHED => _('Не опубликован'), HM_Infoblock_Fact_FactModel::STATUS_PUBLISHED => _('Опубликован')))
            )
        );
	    
        $grid->addAction(
            array('module' => 'infoblock', 'controller' => 'interesting-fact', 'action' => 'edit'),
            array('interesting_facts_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(
            array('module' => 'infoblock', 'controller' => 'interesting-fact', 'action' => 'delete'),
            array('interesting_facts_id'),
            $this->view->icon('delete')
        );
        
        
        
        $grid->addMassAction(
                array('module' => 'infoblock', 'controller' => 'interesting-fact', 'action' => 'public'), 
                _('Опубликовать')
        );
        
        $grid->addMassAction(
                array('module' => 'infoblock', 'controller' => 'interesting-fact', 'action' => 'unpublic'), 
                _('Убрать публикацию')
        );
	    
        $grid->addMassAction(
                array('module' => 'infoblock', 'controller' => 'interesting-fact', 'action' => 'delete-by'), 
                _('Удалить')
        );
        
        $grid->updateColumn('status',
            array('callback' => 
                array('function' => 
                    array($this,'updateStatus'),
                    'params'   => array('{{status}}')
                )
            )
        );
        
        
        $grid->updateColumn('text',
            array('callback' => 
                array('function' => 
                    array($this,'updateText'),
                    'params'   => array('{{text}}')
                )
            )
        );
        
        
        
        $this->view->grid = $grid->deploy();
	}
	
	public function create($form)
	{
        $resource = $this->getService('InfoblockFact')->insert(
            array(
                'title'       => $form->getValue('title'),
                'text' => $form->getValue('text'),
                'status' => $form->getValue('status')
            )
        );
	    
	}
	
	
    public function delete($id)
    {
        $this->getService('InfoblockFact')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $factId = (int) $this->_getParam('interesting_facts_id', 0);

        $resource = $this->getService('InfoblockFact')->getOne($this->getService('InfoblockFact')->find($factId));
        if ($resource) {
            $form->setDefaults(
                $resource->getValues()
            );
        }
    }
	
    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Факт успешно создан'),
            self::ACTION_UPDATE => _('Факт успешно обновлён'),
            self::ACTION_DELETE => _('Факт успешно удалён'),
            self::ACTION_DELETE_BY => _('Факты успешно удалены')
        );
    }
    
    
    
    public function publicAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('InfoblockFact')->update(
                        array(
                        	'interesting_facts_id' => $id,
                            'status' => HM_Infoblock_Fact_FactModel::STATUS_PUBLISHED
                        )
                    );
                    
                }
                $this->_flashMessenger->addMessage(_('Факты успешно опубликованы'));
            }
        }
        $this->_redirectToIndex();
    }
    
    public function unpublicAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('InfoblockFact')->update(
                        array(
                        	'interesting_facts_id' => $id,
                            'status' => HM_Infoblock_Fact_FactModel::STATUS_UNPUBLISHED
                        )
                    );
                    
                }
                $this->_flashMessenger->addMessage(_('Публикация факта успешно отменена'));
            }
        }
        $this->_redirectToIndex();
    }
    
    
    
    
 public function update(Zend_Form $form)
    {
        $this->getService('InfoblockFact')->update(
            array(
                'interesting_facts_id' => $form->getValue('interesting_facts_id'),
                'title' => $form->getValue('title'),
                'text' => $form->getValue('text'),
                'status' => $form->getValue('status')
            )
        );
   }
   
    public function updateStatus($status){
        
        $array = array(HM_Infoblock_Fact_FactModel::STATUS_UNPUBLISHED => _('Не опубликован'), HM_Infoblock_Fact_FactModel::STATUS_PUBLISHED => _('Опубликован'));
        
        return $array[$status];
    }
	
    public function updateText($text){
        
        $text = strip_tags($text);
        
        $text = wordwrap($text, 300, '<br/>');  

        $text = explode('<br/>', $text);
        
        return $text[0];
    }
	
	
	
	
}