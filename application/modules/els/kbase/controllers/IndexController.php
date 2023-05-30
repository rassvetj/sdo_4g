<?php
class Kbase_IndexController extends HM_Controller_Action
{
    public function indexAction()
    {
        $config = Zend_Registry::get('config');
        $this->view->headLink()->appendStylesheet($config->url->base.'css/content-modules/kbase.css');
        
        // получаем метки БЗ
        $tags = $this->getService('Tag')->getTagsRating(array_keys(HM_Tag_Ref_RefModel::getBZTypes()));

        // получаем информацию о классификаторах с областью применения "Объекты БЗ"
        $types = $this->getService('Classifier')->getTypes(HM_Classifier_Link_LinkModel::TYPE_RESOURCE);
        $arClassif = array(); 
        foreach ( $types as $tk => $tv) {
            # пока не вывожу, т.к. не должно изначально.
			$path 	= false;
			$image 	= $this->getService('ClassifierImage')->fetchAll(array('type = ?' => HM_Classifier_Image_ImageModel::TYPE_CATEGORY, 'item_id = ?' => (int)$tk));
			$image 	= $this->getService('ClassifierImage')->getOne($image);
			$path 	= Zend_Registry::get('serviceContainer')->getService('ClassifierImage')->getImageSrc($image->classifier_image_id);			
			
			//$tree[$tk] = $this->getService('Classifier')->getTreeContent(HM_Classifier_Link_LinkModel::TYPE_RESOURCE, 0, $tk);
            $arClassif[$tk] =	array(	'title' => $tv,
										'items' => $this->getService('Classifier')->getChildren(0, true, 'node.type = '.(int) $tk),
										'image' => $path,											   
								);
        }
        
        // статистика Всего ИР
        $statIRCount = count ($this->getService('Course')->fetchAll()) + 
                       //count ($this->getService('Poll')->fetchAll()) +
                       count ($this->getService('Resource')->fetchAll(array(
                           'parent_id = ?' => 0,
                           'status = ?' => 1,
                       ))) 
                       //count ($this->getService('Task')->fetchAll()) +
                       //count ($this->getService('TestAbstract')->fetchAll()) +
                       //count ($this->getService('Exercises')->fetchAll())
        ;

        // статистика Всего пользователей               
        $statUCount =  $this->getService('User')->countAll('blocked != 1');
        
        // статистика Новых ИР за последний месяц
        $testDate = $this->getService('Course')->getDateTime(mktime(0,0,0,(intval(date('n'))-1),intval(date('j')),intval(date('Y'))));
        $statMIRCount = count ($this->getService('Course')->fetchAll($this->getService('Course')->quoteInto('createdate >= ?',$testDate))) +
                        //count ($this->getService('Poll')->fetchAll($this->getService('Poll')->quoteInto('created >= ?',$testDate))) +
                        count ($this->getService('Resource')->fetchAll($this->getService('Resource')->quoteInto('created >= ?',$testDate)))
                        //count ($this->getService('Task')->fetchAll($this->getService('Task')->quoteInto('created >= ?',$testDate))) +
                        //count ($this->getService('TestAbstract')->fetchAll($this->getService('TestAbstract')->quoteInto('created >= ?',$testDate))) +
                        //count ($this->getService('Exercises')->fetchAll($this->getService('Exercises')->quoteInto('created >= ?',$testDate)))
        ;
        
        // последние добавленые ИР
        

        $arLastAdding = array();
        $result = $this->getService('Kbase')->fetchAll('status = '.HM_Resource_ResourceModel::STATUS_PUBLISHED,'cdate DESC',3);

        if ( count($result) ) {
            foreach ($result as $addItem) {
                $ref = HM_Tag_Ref_RefModel::factory(array('item_type' => $addItem->type,
                    'item_id' =>$addItem->id));

                $arLastAdding[] = array('title' => $addItem->title,
                    'url'   => $ref->getService()->getItemViewAction($addItem->id),
                    'type'  => HM_Tag_Ref_RefModel::getTypeTitle($addItem->type));
            }
        }

        
        $this->view->statUCount = $statUCount;
        $this->view->statIRCount = $statIRCount;
        $this->view->statMIRCount = $statMIRCount;
        $this->view->lastAdd = $arLastAdding;
        $this->view->tags = $tags;
        $this->view->classifiers = $arClassif;
    }
    
    // tagsearch moved to SearchController
        
    public function advancedSearchAction()
    {
        $this->view->setHeader(_('Расширенный поиск'));
        $form = new HM_Form_SearchAdvanced(); 
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();
            }
        }
        $this->view->form = $form;
    }
}