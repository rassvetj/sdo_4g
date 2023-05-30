<?php
class Classifier_ListTypesController extends HM_Controller_Action_Crud
{
	public function init()
	{
		$this->_setForm(new HM_Form_ClassifiersTypes());
		parent::init();
	}

	public function indexAction()
	{
		$select = $this->getService('ClassifierType')->getSelect();
		$select->from(
		array('ct' => 'classifiers_types'),
		array(
	                'type_id' => 'ct.type_id',
	                'name' => 'ct.name',
	                'link_types' => 'ct.link_types'
		));

		$grid = $this->getGrid(
			$select,
			array(
	            'type_id' => array('hidden' => true),
	            'name' => array(
	                'title' => _('Название'),
	                'decorator' => '<a href="'.$this->view->url(array('module' => 'classifier', 'controller' => 'list', 'action' => 'index', 'type' => '{{type_id}}'), null, false, false).'">{{name}}</a>'
	                ),
	            'link_types' => array('title' => _('Область применения'))
        	),
			array(
					'type_id' => null,
					'name' => null,
					'link_types' => array('values' => HM_Classifier_Link_LinkModel::getTypes())
			)
        );

        $grid->updateColumn('link_types',
                array(
                	'callback' =>
	                array(
	                    'function' => array(HM_Classifier_Link_LinkModel, 'IdsToNames'),
	                    'params' => array('{{link_types}}')
	                ))
        );

        $grid->addAction(
                array('module' => 'classifier', 'controller' => 'list-types', 'action' => 'edit'),
                array('type_id'),
                $this->view->icon('edit')
        );

        $grid->addAction(
                array('module' => 'classifier', 'controller' => 'list-types', 'action' => 'delete'),
                array('type_id'),
                $this->view->icon('delete')
        );

        $grid->addMassAction(array(
            'module' => 'classifier',
            'controller' => 'list-types',
            'action' => 'delete-by'
        ),
            _('Удалить'),
            _('Вы уверены?')
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

	}

	protected function _getMessages()
	{
        return array(
            self::ACTION_INSERT => _('Классификатор успешно создан'),
            self::ACTION_UPDATE => _('Классификатор успешно обновлён'),
            self::ACTION_DELETE => _('Классификатор успешно удалён'),
            self::ACTION_DELETE_BY => _('Классификаторы успешно удалены')
        );
	}


	public function setDefaults(Zend_Form $form)
	{
		$typeId = (int) $this->_request->getParam('type_id', 0);

		$cltype = $this->getService('ClassifierType')->getOne($this->getService('ClassifierType')->find($typeId));
		if ($cltype)
		{
			$values = $cltype->getValues();
			$values['link_types'] = $cltype->getTypes();
			$form->populate($values);
			//$form->setDefaults($subject->getValues());
		}


        $elem = $form->getElement('icon');
        $elem->setOptions(array('classifierType' => $cltype));


	}

    public function create(Zend_Form $form)
    {
        $cltype = $this->getService('ClassifierType')->insert(
					array(
			                'name' => $form->getValue('name')
					)
		);

        if ($cltype) {
            $cltype->setTypes($form->getValue('link_types'));
            $cltype = $this->getService('ClassifierType')->update(
                $cltype->getValues()
            );
        }

        $icon = $form->getElement('icon');
        if($icon->isUploaded()){

            $imageClassifier = $this->getService('ClassifierImage')->insert(
                array(
                    'type' => HM_Classifier_Image_ImageModel::TYPE_CATEGORY,
                    'item_id' => $cltype->type_id,
                )
            );


            $path = $this->getService('ClassifierImage')->getPath(Zend_Registry::get('config')->path->upload->classifier, $imageClassifier->classifier_image_id);

            $icon->addFilter('Rename', $path . $imageClassifier->classifier_image_id . '.jpg', 'icon', array( 'overwrite' => true));
            $icon->receive();
            $img = PhpThumb_Factory::create($path . $imageClassifier->classifier_image_id . '.jpg');
            $img->resize(HM_Classifier_Image_ImageModel::WIDTH, HM_Classifier_Image_ImageModel::HEIGHT);
            $img->save($path . $imageClassifier->classifier_image_id . '.jpg');
        }



        $this->getService('DeanResponsibility')->checkForUnlimitedClassifiers($cltype->type_id);
    }

	public function update(Zend_Form $form)
	{

	    /*
	    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
        $this->_redirectToIndex();
	    */

		$cltype = $this->getService('ClassifierType')->update(
					array(
			                'type_id' => $form->getValue('type_id'),
			                'name' => $form->getValue('name')
					)
		);

        if ($cltype) {
            $cltype->setTypes($form->getValue('link_types'));
            $cltype = $this->getService('ClassifierType')->update(
                $cltype->getValues()
            );
        }
        /*
         * Удалить все ссылки на рубрики, которые не относятся к области применения текущего классификатора.
         */
        $link_types = $form->getValue('link_types');
        if(is_string($link_types))
            $link_types = array();
        else{
            if(in_array(HM_Classifier_Link_LinkModel::TYPE_RESOURCE, $link_types))
                $link_types = array_merge ($link_types, HM_Classifier_Link_LinkModel::getResourceTypes());
            if(in_array(HM_Classifier_Link_LinkModel::TYPE_UNIT, $link_types))
                $link_types = array_merge ($link_types, HM_Classifier_Link_LinkModel::getUnitTypes());
        }
        $classifiers = $this->getService('Classifier')->fetchAll('type = '. intval($cltype->type_id));
        if(count($classifiers)){
            foreach($classifiers as $classifier){
                $this->getService('ClassifierLink')
                    ->deleteBy(array('not type IN(?)' => $link_types, 'classifier_id = ?' => $classifier->classifier_id));
            }
        }
        
        $icon = $form->getElement('icon');
        if($icon->isUploaded()){

            $imageClassifier = $this->getService('ClassifierImage')->getOne(
                $this->getService('ClassifierImage')->fetchAll(
                    array(
                        'type = ?' => HM_Classifier_Image_ImageModel::TYPE_CATEGORY,
                        'item_id = ?' => $cltype->type_id,
                    )
                )
            );

            if(!$imageClassifier){

                $imageClassifier = $this->getService('ClassifierImage')->insert(
                    array(
                        'type' => HM_Classifier_Image_ImageModel::TYPE_CATEGORY,
                        'item_id' => $cltype->type_id,
                    )
                );



            }

            $path = $this->getService('ClassifierImage')->getPath(Zend_Registry::get('config')->path->upload->classifier, $imageClassifier->classifier_image_id);
            unlink($path . $imageClassifier->classifier_image_id . '.jpg');
            $icon->addFilter('Rename', $path . $imageClassifier->classifier_image_id . '.jpg', 'icon', array( 'overwrite' => true));
            $icon->receive();
            $img = PhpThumb_Factory::create($path . $imageClassifier->classifier_image_id . '.jpg');
            $img->resize(HM_Classifier_Image_ImageModel::WIDTH, HM_Classifier_Image_ImageModel::HEIGHT);
            $img->save($path . $imageClassifier->classifier_image_id . '.jpg');
        }


        $this->getService('DeanResponsibility')->checkForUnlimitedClassifiers($cltype->type_id);
	}

    public function delete($id)
    {
        $ret = $this->getService('ClassifierType')->delete($id);
        $this->getService('DeanResponsibility')->checkForUnlimitedClassifiers($id);
        return $ret;
    }


}

