<?php
class Classifier_ListController extends HM_Controller_Action_Crud
{

	protected $_currentLang = 'rus';

    private $_classifierType = null;

    public function init() {
        $this->_setForm(new HM_Form_Classifier());
        parent::init();
        $this->_setSubjectNavigation();
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
    }

    private function _setSubjectNavigation()
    {
        $type = (int) $this->_getParam('type', 0);
        $classifierType = $this->_classifierType = $this->getOne($this->getService('ClassifierType')->find($type));

        if ($classifierType) {
            $pages = array();
            $pages['label'] = $classifierType->name;
            $pages['module'] = 'classifier';
            $pages['controller'] = 'list';
            $pages['action'] = 'index';
            $pages['params'] = array('type' => $classifierType->type_id, 'parent' => 0);
            $pages['active'] = 1;

            $pages = array('uri' => '', 'pages' => array($pages));

            $this->view->setSubjectNavigation($pages);
        }
    }

    protected function _redirectToIndex()
    {
        $type = (int) $this->_getParam('type', 0);
        $parent = (int) $this->_getParam('parent', 0);
        $this->_redirector->gotoSimple('index', 'list', 'classifier', array('type' => $type, 'parent' => $parent), null, true);

    }

    public function indexAction()
    {
        $type = (int) $this->_getParam('type', 0);
        $parent = (int) $this->_getParam('parent', 0);

        $level = 0;

        $select = $this->getService('Classifier')->getSelect();
        $select->from('classifiers', array('classifier_id', 'name', 'lft', 'rgt', 'name_translation'));

	
		// echo '<pre>';
		// exit( var_dump($select));
		
		
		
        $select->where('type = ?', $type);

        if ($parent) {
            $classifier = $this->getOne($this->getService('Classifier')->find($parent));
            if ($classifier) {
                $level = $classifier->level + 1;

                $select->where('lft >= ?', $classifier->lft);
                $select->where('rgt <= ?', $classifier->rgt);
            }
        }

        $select->where('level = ?', $level);

        $grid = $this->getGrid(
            $select,
            array(
                'classifier_id' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'callback' => array('function' => array($this, 'updateName'), 'params' => array('{{name}}', '{{lft}}', '{{rgt}}', '{{name_translation}}'))
                ),
				'name_translation' => array('hidden' => true),
                'lft' => array('hidden' => true),
                'rgt' => array('hidden' => true)
            ),
            array(
                'name' => null
            )
        );

        $grid->addAction(array(
            'module' => 'classifier',
            'controller' => 'list',
            'action' => 'edit'
        ),
            array('classifier_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'classifier',
            'controller' => 'list',
            'action' => 'delete'
        ),
            array('classifier_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(array(
            'module' => 'classifier',
            'controller' => 'list',
            'action' => 'delete-by'
        ),
            _('Удалить'),
            _('Вы уверены?')
        );

        $this->view->grid = $grid->deploy();

        if (!$this->isAjaxRequest()) {
            $tree = $this->getService('Classifier')->getTreeContent(null, 0, $type, false, $parent);
        }

        $tree = array(
            0 => array(
                'title' => _('Направления обучения'),//$this->_classifierType->name,//
                'count' => 0,
                'key' => 0,
                'isLazy' => true,
                'isFolder' => true,
                'expand' => true
            ),
            1 => $tree
        );

        $this->view->tree = $tree;
        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->gridmod = $this->_getParam('gridmod', false);
        $this->view->parent = $parent;
        //$this->view->classifierType = $this->getOne($this->getService('ClassifierType')->find($type));
        $this->view->type = $type;
    }

    public function updateName($name, $lft, $rgt, $name_translation='')
    {
        $class = 'icon-folder';
        if ($lft+1 == $rgt) {
            $class = 'icon-item';
        }
		if ($this->_currentLang == 'eng' && $name_translation != '')
        return sprintf('<span class="%s"></span>', $class).$name_translation;
	else
		return sprintf('<span class="%s"></span>', $class).$name;
    }

    protected function _getMessages() {

        return array(
            self::ACTION_INSERT => _('Рубрика успешно создана'),
            self::ACTION_UPDATE => _('Рубрика успешно обновлёна'),
            self::ACTION_DELETE => _('Рубрика успешно удалёна'),
            self::ACTION_DELETE_BY => _('Рубрики успешно удалены')
        );
    }

    public function setDefaults(Zend_Form $form) {
        $classifierId = (int) $this->_getParam('classifier_id', 0);
        $classifier = $this->getOne($this->getService('Classifier')->find($classifierId));
        if ($classifier) {
            $form->setDefaults($classifier->getValues());
        }
    }

    public function update(Zend_Form $form) {
        $classifier = $this->getService('Classifier')->update(
            array(
                'classifier_id' => $form->getValue('classifier_id'),
                'name' => $form->getValue('name'),
				'name_translation' => $form->getValue('name_translation')
            )
        );


        $icon = $form->getElement('icon');
        if($icon && $icon->isUploaded()){

            $imageClassifier = $this->getService('ClassifierImage')->getOne(
                $this->getService('ClassifierImage')->fetchAll(
                    array(
                        'type = ?' => HM_Classifier_Image_ImageModel::TYPE_CLASSIFIER,
                        'item_id = ?' => $classifier->classifier_id,
                    )
                )
            );

            if(!$imageClassifier){
                $imageClassifier = $this->getService('ClassifierImage')->insert(
                    array(
                        'type' => HM_Classifier_Image_ImageModel::TYPE_CLASSIFIER,
                        'item_id' => $classifier->classifier_id,
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





    }

    public function create(Zend_Form $form)
    {
        $parent = (int) $this->_getParam('parent', 0);
        $type = (int) $this->_getParam('type', 0);
        $classifier = $this->getService('Classifier')->insert(
            array(
                'name' => $form->getValue('name'),
				'name_translation' => $form->getValue('name_translation'),
                'type' => $type
            ),
            $parent
        );


        $icon = $form->getElement('icon');
        if($icon && $icon->isUploaded()){

            $imageClassifier = $this->getService('ClassifierImage')->insert(
                array(
                    'type' => HM_Classifier_Image_ImageModel::TYPE_CLASSIFIER,
                    'item_id' => $classifier->classifier_id,
                )
            );


            $path = $this->getService('ClassifierImage')->getPath(Zend_Registry::get('config')->path->upload->classifier, $imageClassifier->classifier_image_id);

            $icon->addFilter('Rename', $path . $imageClassifier->classifier_image_id . '.jpg', 'icon', array( 'overwrite' => true));
            $icon->receive();
            $img = PhpThumb_Factory::create($path . $imageClassifier->classifier_image_id . '.jpg');
            $img->resize(HM_Classifier_Image_ImageModel::WIDTH, HM_Classifier_Image_ImageModel::HEIGHT);
            $img->save($path . $imageClassifier->classifier_image_id . '.jpg');
        }





    }

    public function delete($id)
    {
        return $this->getService('Classifier')->deleteNode($id, true);
    }
}