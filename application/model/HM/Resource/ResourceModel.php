<?php

class HM_Resource_ResourceModel extends HM_Model_Abstract
{
    protected $_primaryName = 'resource_id';
    
    const TYPE_EXTERNAL = 0;
    const TYPE_HTML = 1;
    const TYPE_URL = 2;
    const TYPE_FILESET = 3;
    const TYPE_WEBINAR = 4;
    const TYPE_ACTIVITY = 5;
    const TYPE_CARD = 99;


    //Статусы
    const STATUS_UNPUBLISHED = 0;
    const STATUS_PUBLISHED   = 1;
    const STATUS_ARCHIVED   = 2;
    const STATUS_STUDYONLY   = 7;

    //TYPE
    const LOCALE_TYPE_LOCAL  = 0;
    const LOCALE_TYPE_GLOBAL = 1;

    public function getClassName()
    {
        return _('Информационный ресурс');
    }

    static public function getTypes()
    {
        return array(
            self::TYPE_EXTERNAL => _('Файл'),
            self::TYPE_HTML => _('HTML-страница'),
            self::TYPE_FILESET => _('HTML-сайт'),
            self::TYPE_URL => _('Ссылка на внешний ресурс'),
            self::TYPE_WEBINAR => _('Запись вебинара'),
            self::TYPE_CARD => _('Только карточка'),
        );
    }

    static public function getEditableTypes()
    {
        $types = self::getTypes();
        unset($types[self::TYPE_WEBINAR]);
        return $types;
    }

    static public function getStatuses()
    {
        return array(
            self::STATUS_UNPUBLISHED    => _('Не опубликован'),
            self::STATUS_PUBLISHED      => _('Опубликован'),
            self::STATUS_STUDYONLY      => _('Ограниченное использование'),
            self::STATUS_ARCHIVED      => _('Архивный'),
        );
    }

    static public function getLocaleStatuses()
    {
        return array(
            self::LOCALE_TYPE_LOCAL  => _('Учебный курс'),
            self::LOCALE_TYPE_GLOBAL => _('База знаний')
        );
    }

    public function getLinkTitle(){

        if(!$this->resource_id) return false;
        return array(
            'module' => 'file',
            'controller' => 'get',
            'action' => 'resource',
            'resource_id' => $this->resource_id,
            'download' => 1,
            'name' => $this->title
        );
    }

    public function getName(){	
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		
        if($lng == 'eng' && isset($this->title_translation) && $this->title_translation != '')
			return $this->title_translation;
		else
			return $this->title;
    }

    public function getDescription(){
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);	   
		
        if($lng == 'eng' && isset($this->description_translation) && $this->description_translation != '')
			return $this->description_translation;
		else		
			return $this->description;
    }

    public function getType(){
        $types = $this->getTypes();
        return $types[$this->type];
    }

    public function getTypeByClassifier($classifierTypeId){
        if (count($this->classifierLinks)) {
            $classifierIds = $this->classifierLinks->getList('classifier_id');
            $classifiers = Zend_Registry::get('serviceContainer')->getService('Classifier')->fetchAll(array(
                'classifier_id IN (?)' => $classifierIds,
                'type = ?' => $classifierTypeId,
            ))->getList('name');
            return implode(', ', $classifiers);
        }
        return '';
    }

    // todo: сделать все типы viewable
    public function isViewable()
    {
        return
            in_array($this->type, array(HM_Resource_ResourceModel::TYPE_HTML, HM_Resource_ResourceModel::TYPE_URL, HM_Resource_ResourceModel::TYPE_FILESET)) ||
            in_array($this->filetype, array(
                                          HM_Files_FilesModel::FILETYPE_HTML,
                                          HM_Files_FilesModel::FILETYPE_FLASH,
                                          HM_Files_FilesModel::FILETYPE_IMAGE,
                                          HM_Files_FilesModel::FILETYPE_TEXT,
                                          HM_Files_FilesModel::FILETYPE_AUDIO,
                                          HM_Files_FilesModel::FILETYPE_PDF,
                                          HM_Files_FilesModel::FILETYPE_VIDEO
                                      )
              );
    }

    public function getIconClass()
    {
        return HM_Resource_ResourceService::getIconClass($this->type, $this->filetype, $this->filename, $this->activity_type);
    }

    public function getCardUrl()
    {
        if(!$this->resource_id) return false;
        return array(
            'module' => 'resource',
            'controller' => 'index',
            'action' => 'card',
            'resource_id' => $this->resource_id,
        );
    }

    public function getViewUrl()
    {
        if(!$this->resource_id) return false;
        return array(
            'module' => 'resource',
            'controller' => 'index',
            'action' => 'index',
            'resource_id' => $this->resource_id,
        );
    }

    public function getCreateUpdateDate()
    {
        $return = sprintf(_('Создан: %s'), $this->dateTime($this->created));
        if ($this->created != $this->updated) {
            $return .= ', ' . sprintf(_('обновлён: %s'), $this->dateTime($this->updated));
        }
        return $return;
    }

    public function getServiceName()
    {
        return 'Resource';
    }

    public function getFilesList($revisionId = 0)
    {
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
        $collection = $this->getService()->fetchAll(array(
            'parent_id = ?' => $this->resource_id,
            'parent_revision_id = ?' => $revisionId,
        ));
        if (count($collection)) {
            $ret = '';
            foreach($collection as $item) {
				if ($lng == 'eng')
                $ret .= sprintf('<a class="text" href="/file/get/resource/resource_id/%d">%s</a>', $item->resource_id, $item->translation_filename);
				else
                $ret .= sprintf('<a class="text" href="/file/get/resource/resource_id/%d">%s</a>', $item->resource_id, $item->filename);
            }
            return $ret;
        }

        return false;
    }

    public function getCreateBy()
    {
        $createby = '';
        if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
            Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),array(
            HM_Role_RoleModelAbstract::ROLE_DEVELOPER,
            HM_Role_RoleModelAbstract::ROLE_MANAGER
        ))
        ){
            $select=Zend_Registry::get('serviceContainer')->getService('User')->getSelect();
            $select->from(array('t1' => 'People'),array(
                'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                'department' => new Zend_Db_Expr("org2.name")
            ));
            $select->joinInner(array('org' => 'structure_of_organ'),'t1.MID = org.MID',array());
            $select->joinLeft(array('org2' => 'structure_of_organ'),'org.owner_soid = org2.soid',array());
            $select->where('t1.MID = ?',$this->created_by);
            $user=$select->query()->fetchAll();
            if ($user)
                $createby = $user[0]['fio'].' ('.$user[0]['department'].')';
        }
        return $createby;
    }

}