<?php
class HM_Course_CourseModel extends HM_Model_Abstract
{
    protected $_primaryName = 'CID';
    
    const STATUS_DEVELOPED=0;
    const STATUS_ACTIVE=1;
    const STATUS_ARCHIVED=2;
    const STATUS_STUDYONLY = 7;

    const SUBSTATUS_DEV=0;
    const SUBSTATUS_REC=1;
    const SUBSTATUS_VER=2;
    const SUBSTATUS_PUB=3;

    const FORMAT_FREE    = 999;
    const FORMAT_UNKNOWN = 0;
    const FORMAT_SCORM   = 2;
    const FORMAT_AICC    = 3;
    const FORMAT_EAU3    = 4;
    const FORMAT_ZIP     = 5;

    const FORMAT_FREE_STR = 'Произвольный';
    const FORMAT_SCORM_STR = 'Scorm';
    const FORMAT_AICC_STR  = 'AICC';
    const FORMAT_EAU3_STR  = 'eAuthor';
    const FORMAT_ZIP_STR   = 'ZIP';

    const EMULATE_IE_NONE = 0;
    const EMULATE_IE_7 = 7;
    const EMULATE_IE_8 = 8;
    const EMULATE_IE_9 = 9;
    const EMULATE_IE_10 = 10;

    const EMULATE_IE_7_DIR = 'emulate-ie7';
    const EMULATE_IE_8_DIR = 'emulate-ie8';
    const EMULATE_IE_9_DIR = 'emulate-ie9';
    const EMULATE_IE_10_DIR = 'emulate-ie10';
    
    const PROGRESS_COMPLETED = 'ok';
    const PROGRESS_INCOMPLETE = 'no';

    static public function factory($data, $default = 'HM_Course_CourseModel')
    {

        if (isset($data['type']))
        {
            if ($data['type'] == 1)
            {
                return parent::factory($data, 'HM_Resource_ResourceModel');
            }
        }

        return parent::factory($data, $default);
    }
    
    public function getClassName()
    {
        return _('Учебный модуль');
    }

    public static function getFormats()
    {
        return array(
            self::FORMAT_FREE => _(self::FORMAT_FREE_STR),
            self::FORMAT_EAU3 => _('Публикация eAuthor 3'),
            self::FORMAT_SCORM => _('Пакет SCORM'),
            self::FORMAT_AICC => _('Пакет AICC')
        );
    }

    public static function getImportFormats()
    {
        return array(
            self::FORMAT_EAU3 => _('Публикация eAuthor 3'),
            self::FORMAT_SCORM => _('Пакет SCORM'),
            self::FORMAT_AICC => _('Пакет AICC')
        );
    }

    public static function getInteractiveFormats()
    {
        return array(
            self::FORMAT_EAU3,
            self::FORMAT_SCORM,
            self::FORMAT_AICC
        );
    }

    public static function getFormat($format)
    {
        $formats = self::getFormats();
        if (isset($formats[$format])) {
            return $formats[$format];
        }
        return _('Неизвестный');
    }

    /**
     * Статическая замена для getStatuses
     * 
     * @return array
     */
    public static function s_getStatuses()
    {
        return array(
            self::STATUS_STUDYONLY => _('Ограниченное использование'),
            self::STATUS_DEVELOPED => _('Не опубликован (в разработке)'),
            self::STATUS_ACTIVE => _('Опубликован'),
            self::STATUS_ARCHIVED => _('Архивный'));
    }

    public function getStatuses()
    {
        return self::s_getStatuses();
    }

    public function getStatus($status = null)
    {

        $statuses = $this->getStatuses();
        if (null == $status) {
            $status = $this->Status;
        }

        return $statuses[$status];

    }

    public function getAvailStatuses()
    {

        return array(
            self::STATUS_DEVELOPED => 'developed',
            self::STATUS_ACTIVE => 'active',
            self::STATUS_ARCHIVED => 'archived',
            self::STATUS_STUDYONLY => 'studyonly');

    }

    public function getSubStatuses()
    {

        return array(
            self::SUBSTATUS_DEV => _('Готов к разработке'),
            self::SUBSTATUS_REC => _('Готов к рецензированию'),
            self::SUBSTATUS_VER => _('Готов к верстке'),
            self::SUBSTATUS_PUB => _('Готов к публикации'));

    }

    /**
     * @return the $statusName
     */
    public function getStatusName()
    {

        return $this->getStatuses();
    }

    /**
     * @return the $statusAvail
     */
    public function getStatusAvail()
    {

        return $this->getAvailStatuses();
    }

    /**
     * @return the $subStatusAvail
     */
    public function getSubStatusAvail()
    {

        return $this->getSubStatuses();
    }

    /**
     * Возвращаем объект селект, в зависимости от статуса курсов
     * @param int $status
     * @return Zend_Db_Select
     */
    public function getListSelect($statuses)
    {
        if (!is_array($statuses)) {
            $statuses = array($statuses);
        }
        $course=Zend_Registry::get('serviceContainer')->getService('Course');

        $selectFields = $this->getListFields($statuses);
        $selectFields = array_merge($selectFields, array('classifiers' => new Zend_Db_Expr("GROUP_CONCAT(cf.name)")));
        $select = $course->getSelect()->from(array('c' => 'Courses'), $selectFields)->where('c.Status IN (?)', $statuses);
        $select->joinLeft(array('p' => 'providers'), 'c.provider = p.id', array());
        $select->joinLeft(
            array('cl' => 'classifiers_links'),
            'cl.item_id = c.CID AND cl.type = ' . HM_Classifier_Link_LinkModel::TYPE_COURSE,
            array()
        );
        $select->joinLeft(
            array('cf' => 'classifiers'),
            'cl.classifier_id = cf.classifier_id',
            array()
        );      
        
        
        
        if (in_array(self::STATUS_ACTIVE, $statuses) || in_array(self::STATUS_STUDYONLY, $statuses)) {
            $select->joinLeft(
                array('sc' => 'subjects_courses'),
                'c.CID = sc.course_id',
                array(
                    'courses' => new Zend_Db_Expr('GROUP_CONCAT(sc.subject_id)')
                )
            );
            // Возможно существование мертвых связок модуль - курс в
            // таблице subjects_courses из-за некорректного удаления
            $select->joinLeft(array('s' => 'subjects'), 'sc.subject_id = s.subid', array());
//             $select->where('s.subid IS NOT NULL');
        }
        
        $select->where('c.chain IS NULL OR c.chain = ?', 0);
        $select = $this->getListRulesAccept($select);
        unset($selectFields['classifiers']);
        $select->group($selectFields);
        $select->order('c.Title ASC');
      //exit($select->__toString());
        return $select;

    }

    /**
     * Функция позволяющая применить к выборке текущую роль
     * пользователя и выбрать только те значения,
     * которые он может видеть
     *
     * @param Zend_Db_Select $select
     * @return Zend_Db_Select
     */
    protected function getListRulesAccept($select)
    {

        // Пока просто возвращаем обратно
        return $select;

    }

    /**
     *
     * Возвращает возможные действия в зависимости от статуса курсов
     * @param int $status
     * @return multitype:
     */
    public function getListActions($status)
    {

        $array = array();

        return $array;
    }

    /**
     * Вычитает из первого элемента второй и
     * возвращает массив из 2ух массивов. Первый - в первую колонку,
     * Второй во вторую
     * @param unknown_type $collection
     * @param unknown_type $array
     */
    public function getDiff($collection, $array)
    {

        $arr1 = array();
        $arr2 = array();
        if (! is_array($array))
        {
            $array = array();
        }

        if ( $collection ) {
            foreach ( $collection as $key => $value )
            {
                if (array_search($key, $array) !== false)
                {
                    unset($collection[$key]);
                    $arr2[$key] = $value;
                } else
                {
                    unset($collection[$key]);
                    $arr1[$key] = $value;
                }

            }
        }
        return array(
            $arr1,
            $arr2);

    }

       /**
     * Определяем какие поля нужны для вывода при определенном статусе.
     * @param int $status  Номер статуса
     * @return Ambigous <multitype:string >
     */
    protected function getListFields($statuses)
    {
        if (!is_array($statuses)) {
            $statuses = array($statuses); 
        }
        
        $result = array();
        
        $array = array(
            self::STATUS_ACTIVE => array(
                'CID' => 'c.CID',
                'Title' => 'c.Title',
                'Status' => 'c.Status',
            	'provider' => 'p.title',
                'provider_id' => 'p.id',
                'courseFormat' => 'c.format',
                'longtime' => 'c.longtime',
                'lastUpdateDate' => 'c.lastUpdateDate',
                'tags' => 'CID'
            ),
            self::STATUS_STUDYONLY => array(
                'CID' => 'c.CID',
                'Title' => 'c.Title',
                'Status' => 'c.Status',
                'provider' => 'p.title',
                'provider_id' => 'p.id',
                'courseFormat' => 'c.format',
                'longtime' => 'c.longtime',
                'lastUpdateDate' => 'c.lastUpdateDate',
                'tags' => 'CID'
            ),
            self::STATUS_DEVELOPED => array(
                'CID' => 'c.CID',
                'Title' => 'c.Title',
                'Status' => 'c.Status',
                'courseFormat' => 'c.format',
                'provider' => 'p.title',
                'provider_id' => 'p.id',
                'planDate' => 'c.planDate',/*
                'developStatus',*/
                'lastUpdateDate' => 'c.lastUpdateDate',
            ),
            self::STATUS_ARCHIVED => array(
                'CID' => 'c.CID',
                'Title' => 'c.Title',
                'Status' => 'c.Status',
                'courseFormat' => 'c.format',
                'archiveDate' => 'c.archiveDate'
            )
        );
        
        foreach ($statuses as $status) {
            $result = array_merge($result, $array[(int) $status]);
        }
        
        return $result;
    }

     // Функция для преобразования статуса
    function statusFunction($field, $array)
    {

        if (isset($array[$field]))
        {
            return $array[$field];
        } else
        {
            return 'Неопределенный статус';
        }

    }

    /**
     * Локализуем дату
     * @param string $field Дата в формате yyyy-mm-dd
     * @return string
     */
    function dateFunction($field)
    {
        if ($field) {
        $dateObject = new Zend_Date($field, 'yyyy-MM-dd');
        return $dateObject->toString(HM_Locale_Format::getDateFormat());
        }

    }

    public function getPlanDate()
    {
        return $this->date($this->planDate);
    }

    public function getName(){
        return $this->Title;
    }

    public function getDescription(){
        return $this->Description;
    }

    static public function getEmulateModes()
    {
        return array(
            self::EMULATE_IE_NONE => _('Нет'),
            self::EMULATE_IE_7 => _('Internet Explorer 7'),
            self::EMULATE_IE_8 => _('Internet Explorer 8'),
            self::EMULATE_IE_9 => _('Internet Explorer 9'),
            self::EMULATE_IE_10 => _('Internet Explorer 10')
        );
    }

    static public function getEmulatePaths()
    {
        return array(
            self::EMULATE_IE_7 => self::EMULATE_IE_7_DIR,
            self::EMULATE_IE_8 => self::EMULATE_IE_8_DIR,
            self::EMULATE_IE_9 => self::EMULATE_IE_9_DIR,
            self::EMULATE_IE_10 => self::EMULATE_IE_10_DIR
        );
    }

    public function getEmulatePathPiece($emulateMode = 0)
    {
        $paths = self::getEmulatePaths();
        if (isset($paths[$emulateMode])) {
            return $paths[$emulateMode];
        }

        return false;
    }

    public function getPath()
    {
        return $this->getEmulatePath($this->emulate);
    }

    public function getEmulatePath($emulateMode)
    {
        $paths = self::getEmulatePaths();
        if (!$emulateMode || !isset($paths[$emulateMode])) {
            return $_SERVER['DOCUMENT_ROOT'].'/COURSES/course'.$this->CID;
        }

        return $_SERVER['DOCUMENT_ROOT'].'/COURSES/'.$paths[$emulateMode].'/course'.$this->CID;

    }


    public function getIconClass()
    {
        return 'course';
    }
    
    public function getCardUrl()
    {
        if(!$this->CID) return false;
        return array(
            'module' => 'course',
            'controller' => 'list',
            'action' => 'card',
            'course_id' => $this->CID,
        );
    }

    public function getViewUrl()
    {
        if(!$this->CID) return false;
        return array(
            'module' => 'course',
            'controller' => 'index',
            'action' => 'index',
            'course_id' => $this->CID,
        );
    }

    public function getCreateUpdateDate()
    {
        return sprintf(_('Создан: %s'), $this->dateTime($this->createdate));
    }

    public function getAuthor(){
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
            $select->where('t1.MID = ?',$this->author);
            $user=$select->query()->fetchAll();
            if ($user)
                $createby = $user[0]['fio'].' ('.$user[0]['department'].')';
        }
        return $createby;
    }
}