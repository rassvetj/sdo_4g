<?php

/**
 * Сервис форума
 * Является полным API форума.
 * Реализует бизнес-логику форума.
 * Ничего не знает о способе хранения данных, использует для доступа к данным сервисы-модели.
 *
 * Все вызовы касательно форума осуществляются только через него.
 * Работать на прямую с сервисами-моделями настоятельно не рекомендуется.
 */
class HM_Forum_ForumService extends HM_Activity_ActivityService implements 
            HM_Forum_Library_Constants,
            HM_Service_Schedulable_Interface,
            Es_Entity_Trigger
{

    const EVENT_GROUP_NAME_PREFIX = 'FORUM_MESSAGE_ADD';

    /**
     * Конфигурация по умолчанию
     *
     * @var array
     */
    protected $defaultConfig = array(
        // Настройки форумов
        'forums'    => array(
            // Модераторы
            'moderators' =>array(
                HM_Role_RoleModelAbstract::ROLE_ADMIN,
                HM_Role_RoleModelAbstract::ROLE_MANAGER,
                HM_Role_RoleModelAbstract::ROLE_DEAN
            ),
            // Параметры для новых форумов
            'new' => array(
               'flags'  => array(
                    'active'      => true,  // форум активен и доступен для пользователей
                    'subsections' => false, // структура форума имеет подразделы
                    'subsecttree' => false, // структура форума допускает более одного уровня вложенности подразделов
                    'closed'      => false, // форум закрыт и доступен только для чтения
                    'private'     => false  // доступ на форум только по списку допусков пользователей
                )
            )
        ),

        // Настройки разделов
        'sections' => array(
            // Структура вывода разделов/тем
            'structure' => array(
                'order_last_msg' => true // Сортировка по последнему сообщению в теме
            ),
            // Параметры для новых разделов
            'new' => array(
                'flags'  => array(
                    'active'  => true,  // раздел активен и доступен для пользователей
                    'theme'   => false, // раздел является темой, не может содержать подразделы, может содержать только сообщения
                    'closed'  => false, // раздел закрыт и доступен только для чтения
                    'private' => false  // доступ в раздел только по списку допусков пользователей
                )
            )
        ),

        // Настройки сообщений
        'messages' => array(
            // Параметры для новых сообщений
            'new' => array(
                'flags' => array(
                    'active'  => true, // Сообщение показывается
                    'deleted' => false // Сообщение удалено
                )
            ),
            // Структура запроса и вывода сообщений
            // Для отображения в теме
            'structure' => array(
                'as_tree'             => true,  // Выводить сообщения в виде дерева
                'order_by_time'       => false,  // Сортировать сообщения по времени добавления
                'order_reverse'       => false,  // Обратный порядок сортировки (новые наверх)
                'only_new'            => false,  // Только новые сообщения (Запрос тяжёлый для БД !)
                'preview'             => false,  // Текст сообщений подгружается только если не превышает параметры максимального размера. Сообщения не помечаются как просмотренные
                'new_max_period'      => 604800, // Период времени в течении которого сообщение может считаться новым (в секундах)
            ),
            // Для отображения в предпросмотре (объединяются со "structure" при установленном параметре "preview")
            'structure_preview' => array(
                'order_by_time'       => true,
                'order_reverse'       => true,
            )
        ),

        // Создание форума по умолчанию
        'forum_init' => array(
            // Параметры форума
            'forum' => array(
                'title'    => 'Форум портала',
                'flags'    => array(
                    'subsections' => true,
                ),
            ),
            // Параметры разделов
            'sections' => array(
                array(
                    'title' => 'Раздел 1',
                    'text'  => 'Раздел 1'
                ),
                array(
                    'title' => 'Раздел 2',
                    'text'  => 'Раздел 2'
                )
            )
        ),

        // Создание форума для курса
        'subject_init' => array(
            // Название форума
            'forum_name' => 'Форум занятия "%s"',
            // Параметры форума
            'forum' => array(
                'flags' => array(
                    'subsections' => false
                )
            ),
        ),

        // Создание темы форума для занятия
        'lesson_init' => array(
            // Название темы
            'theme_name' => 'Тема занятия "%s"',
            // Параметры темы
            'section' => array(
                'flags' => array(
                    'theme' => true
                )
            )
        ),

        // ID форума по умолчанию
        'forum_default_id' => 1,

        // Оценки
        // 0 - оценка не выставлена
        'ratings' => array(
            5 => '5 (отлично)',
            4 => '4 (хорошо)',
            3 => '3 (удовлетворит.)',
            2 => '2 (неудовлетворит.)',
            1 => '1 (очень плохо)',
            6 => 'зачтено',
            7 => 'незачтено'
        )
    );

    /**
     * Конфигурация движка форума
     *
     * @var Zend_Config
     */
    protected $config;

    /**
     * Конструктор. Может принимать параметры конфигурации.
     *
     * @param Zend_Config | array $config
     */
    public function __construct($config = null){
        parent::__construct();
        $this->setConfig($config);
    }

    /**
     * Задать конфигурацию форума
     *
     * @param Zend_Config | array $config Параметры конфигурации
     */
    public function setConfig($config = null){
        if($this->config === null) $this->config = new Zend_Config($this->defaultConfig, true);

        switch(true){
            case is_array($config):
                $config = new Zend_Config($config);

            case $config instanceof Zend_Config:
                break;

            case Zend_Registry::isRegistered('config'):
                $config = Zend_Registry::get('config')->forum;
                if($config) break;

            default: return;
        }

        $this->config->merge($config);
    }

    /**
     * Получить объект конфигурации форума
     *
     * @return Zend_Config
     */
    public function getConfig(){
        return $this->config;
    }

    /**
     * Создать форум
     *
     * @param array $data параметры форума:
     * 'userId'   => id пользователя-владельца форума
     * 'userName' => ФИО пользователя-владельца форума
     * 'title'    => название форума
     * 'flags'    => опции
     *
     * @return HM_Forum_Forum_ForumModel
     */
    public function createForum(array $data){
        $data = array_replace_recursive($this->config->forums->new->toArray(), $data);
        $data = $this->_userData($data);

        return $this->getService('ForumForum')->createForum($data);
    }

    /**
     * Получить информацию о форуме
     * Так же является быстрым вариантом проверить существования форума
     *
     * @param int $forumId id форума
     * @return HM_Forum_Forum_ForumModel | null
     * @throws HM_Exception
     */
    public function getForumInfo($forumId){
        $forum = $this->getService('ForumForum')->getForum((int) $forumId);
        if(!$forum) throw new HM_Exception(_(self::ERR_MSG_NOFORUM), self::ERR_CODE_NOFORUM);
        return $forum;
    }

    /**
     * Получить форум по id форума (со всеми разделами и сообщениями)
     *
     * @param  int $forumId id форума
     * @param  int $sectionId id раздела
     * @return HM_Forum_Forum_ForumModel
     * @throws HM_Exception
     */
    public function getForum($forumId, $sectionId = null){
        $forum = $this->_getForum((int) $forumId);
        if(!$sectionId) $sectionId = null;

        // Корень форума
        if($sectionId === null){
            // Разделы
            $sections = $this->_getSectionsByForumId($forum->forum_id);
            $sections = $this->_orderSort($sections, 'order', true, 'section_id');
            $forum->sections = $sections;

            // Подразделы
            if(!empty($sections)){
                $subsectionsUnsort = $this->_getSectionsBySectionId(array_keys($sections));
                $subsectionsGrp = array();
                foreach ($subsectionsUnsort as $subsection) {
                    if(!isset($subsectionsGrp[$subsection->parent_id])) $subsectionsGrp[$subsection->parent_id] = array();
                    $subsectionsGrp[$subsection->parent_id][$subsection->section_id] = $subsection;
                }

                // Сортировка подразделов по разделам с сортировкой по приоритету и времени последнего сообщения
                $orderBy = $this->config->sections->structure->order_last_msg ? 'last_msg' : null;
                foreach($subsectionsGrp as $id => $subsections){
                    $sections[$id]->subsections = $this->_orderSort($subsections, 'order', true, 'section_id', $orderBy, true);
                }
            }
        }
        // Запрошенный раздел
        else{
            // Раздел
            $section = $this->_getSection((int) $sectionId);

            // Родительский раздел
            if($section->parent_id > 0) $section->parent = $this->_getSection($section->parent_id);

            // Раздел является темой форума, может содержать сообщения
            if($section->flags->theme) $section->messages = $this->_getMessagesBySectionId($section->section_id);

            // Раздел может содержать подразделы, в т.ч. темы форума
            else{
                // Подразделы
                $subsections = $this->_getSectionsBySectionId($section->section_id);
                $data = $this->_orderSort($subsections, 'order', true, 'section_id', 'last_msg', true);
                $section->subsections = $data;
            }
            $forum->section = $section;
        }

        return $forum;
    }

    /**
     * Получить форум по модели курса (со всеми разделами и сообщениями)
     *
     * @param  HM_Subject_SubjectModel $subjectId курс
     * @param  int $sectionId id раздела
     * @param  HM_Lesson_LessonModel $lesson занятие
     * @return HM_Forum_Forum_ForumModel
     * @throws HM_Exception
     */
    public function getForumBySubject(HM_Subject_SubjectModel $subject, $sectionId = null, HM_Lesson_LessonModel $lesson = null){
        $forum = $this->_getForumBySubject($subject);
        if(!$sectionId) $sectionId = null;

        if (
            $lesson &&
            (
                ($lesson->typeID == HM_Activity_ActivityModel::ACTIVITY_FORUM) || // занятие с типом "форум"
                (
                    is_array($activities = unserialize($lesson->activities)) &&  // вкладка "Форум", подключенная к занятию ИР или УМ
                    in_array(HM_Activity_ActivityModel::ACTIVITY_FORUM, $activities)
                )
            )
        ){
            // Раздел
            $section = $this->_getSectionByLesson($lesson, $forum);
            $section->subject_id = $forum->subject_id;

            // Сообщения
            $section->messages = $this->_getMessagesBySectionId($section->section_id, $forum->subject_id);

            $forum->section = $section;
        }
        // Корень форума
        elseif($sectionId === null){
            // Темы
            $sections = $this->_getSectionsByForumId($forum->forum_id);
            foreach($sections as $section) $section->subject_id = $forum->subject_id;

            $sections = $this->_orderSort($sections, 'order', true, 'section_id', 'last_msg', true);
            // Единственный раздел форума как сервиса взаимодействия
            $data = array(
                'subsections' => $sections,
                'subject_id'  => $forum->subject_id
            );
            $forum->sections = array(
                HM_Model_Abstract::factory($data, 'HM_Forum_Section_SectionModel')
            );
        }
        // Запрошенный раздел
        else{
            // Раздел
            $section = $this->_getSection((int) $sectionId);
            $section->subject_id = $forum->subject_id;

            // Сообщения
            $section->messages = $this->_getMessagesBySectionId($section->section_id, $forum->subject_id);

            $forum->section = $section;
        }

        return $forum;
    }

    /**
     * @param  int $forumId
     * @return HM_Forum_Forum_ForumModel
     * @throws HM_Exception
     */
    protected function _getForum($forumId){
        $forum = $this->getService('ForumForum')->getForum($forumId);

        if(!$forum){
            // Если не найден форум по умолчанию
            if($forumId == $this->config->forum_default_id){

                // Создание форума
                $forum = $this->config->forum_init->forum->toArray();

                try{ $forum = $this->createForum($forum); }
                catch(HM_Exception $e){ throw $e; }
                catch(Exception $e){ throw new HM_Exception(_(self::ERR_MSG_DEFAULTFORUM), self::ERR_CODE_DEFAULTFORUM); }

                // Создание разделов форума
                $sections = array();
                foreach($this->config->forum_init->sections->toArray() as $section){
                    $this->createSection($section, $forum);
                    $sections[$section->section_id] = $section;
                }
                $forum->sections = $sections;
            }

            else throw new HM_Exception(_(self::ERR_MSG_NOFORUM), self::ERR_CODE_NOFORUM);
        }

        // Определение привелегии модератора
        $role = $this->getService('User')->getCurrentUserRole();
        $forum->moderator = in_array($role, $this->config->forums->moderators->toArray());

        // Текущая конфигурация форума
        $forum->config = clone $this->config;
        $forum->config->setReadOnly();

        return $forum;
    }

    /**
     * @param  HM_Subject_SubjectModel $subject
     * @return HM_Forum_Forum_ForumModel
     * @throws HM_Exception
     */
    protected function _getForumBySubject(HM_Subject_SubjectModel $subject){
        $forum = $this->getService('ForumForum')->getForumBySubjectId($subject->subid);

        // При отсутствии форума соотсветствующего курсу пытаемся его создать
        if(!$forum){
            $data = array(
                'title'      => substr(sprintf($this->config->subject_init->forum_name, $subject->name),0,254),
                'user_name'  => substr($subject->name,0,254),
                'subject_id' => $subject->subid,
                'flags'      => array('subsections' => false)
            );

            try{ $forum = $this->createForum($data); }
            catch(HM_Exception $e){ $this->criticalError($e->getMessage()); }
        }

        // Определение привелегии модератора        
		$role = $this->getService('User')->getCurrentUserRole();
        $forum->moderator = in_array($role, $this->config->forums->moderators->toArray())
                            || $this->isCurrentUserActivityModerator();

        // Текущая конфигурация форума
        $forum->config = clone $this->config;
        $forum->config->setReadOnly();

        return $forum;
    }

    /**
     * @param  int $sectionId
     * @return HM_Forum_Section_SectionModel
     * @throws HM_Exception
     */
    protected function _getSection($sectionId){
        $section = $this->getService('ForumSection')->getSection($sectionId);
        if(!$section) throw new HM_Exception(_(self::ERR_MSG_NOSECTION), self::ERR_CODE_NOSECTION);

        return $section;
    }

    /**
     * @param  int $forumId
     * @return HM_Forum_Section_SectionModel[]
     */
    protected function _getSectionsByForumId($forumId){
        return $this->getService('ForumSection')->getSectionsList($forumId, null);
    }

    /**
     * @param  int $sectionId
     * @return HM_Forum_Section_SectionModel[]
     */
    protected function _getSectionsBySectionId($sectionId){
        return $this->getService('ForumSection')->getSectionsList(null, $sectionId);
    }

    /**
     * @param  HM_Lesson_LessonModel $lesson
     * @param  HM_Forum_Forum_ForumModel $forum
     * @return HM_Forum_Section_SectionModel[]
     * @throws HM_Exception
     */
    protected function _getSectionByLesson(HM_Lesson_LessonModel $lesson, HM_Forum_Forum_ForumModel $forum){
        $section = $this->getService('ForumSection')->getSectionByLessonId($lesson->SHEID);

        // Если занятие имеет тип "форум" и соответствующей ему темы не существует пытаемся её создать
        if(!$section){
            $theme = $this->config->lesson_init->section->toArray();
            $theme['title'] = sprintf($this->config->lesson_init->theme_name, $lesson->title);
            $theme['lesson_id'] = $lesson->SHEID;
            // Необходимо установить владельца форума - преподавателя на данном занятии.
            // Если на занятие не установлен преподаватель,
            // то владельцем устанавливается преподаватель из списка преподавателей курса
            if(!$lesson->teacher){
                // Получить курс $lesson->CID
                $course = $this->getOne($this->getService('Subject')->find($lesson->CID));
                // Получить список преподавателей и вставить id первого преподавателя курса
                $teacher = $this->getOne($this->getService('Subject')->getTeachers($course->subid))->MID;
                $theme['user_id'] = $teacher;
            } else {
                $theme['user_id'] = $lesson->teacher;
            }
            $section = $this->createSection($theme, $forum);
        }

        // установка признака "скрытая тема"  взависимости от настроек занятия
        $params = $lesson->getParams();
        if ( intval($params['is_hidden']) != $section->is_hidden ) {
            $data              = $section->getValues();
            $data['is_hidden'] = intval($params['is_hidden']);
            $this->getService('ForumSection')->updateSection($data['section_id'], $data);
        }

        if(!$section) throw new HM_Exception(self::ERR_MSG_NOSECTION, self::ERR_CODE_NOSECTION);

        return $section;
    }

    /**
     * Получить список сообщений по ID раздела(темы)
     *
     * @param  int $sectionId
     * @param int $subjectId
     * @return HM_Forum_Message_MessageModel[]
     */
    protected function _getMessagesBySectionId($sectionId, $subjectId = null){
        // Конфигурация для текущего запроса
        $structure = clone $this->config->messages->structure;
        if($structure->preview) $structure->merge($this->config->messages->structure_preview);

        $user = $this->getService('User')->getCurrentUser();
		if(!$user->MID){ return array(); }
        $newPeriod = $structure->new_max_period;
        $timeNow = time();

        $messages = $this->getService('ForumMessage')->getMessagesList(null, $sectionId, $structure, $user->MID);
        if(empty($messages)) return array();

        // Сообщения плоским списком
        $messagesList = $messages;
        if($structure->as_tree){
            foreach($messages as $message) $messagesList = $messagesList + $message->getAnswers(true);
        }

        // В случае принадлежности к занятиям
        if($subjectId !== null && !$structure->preview){
            $students = $this->getService('Student')->getUsersIds($subjectId);
            $ratings = $this->config->ratings->toArray();

            foreach($messagesList as $message){
                $message->subject_id = $subjectId;
                $message->createdByStudent = isset($students[$message->user_id]);
                $message->rating_raw = $message->rating;
                $message->rating = $ratings[$message->rating];
            }
        }

        // Определение значения сущности $new
        $lastLogin = $user->getLastLoginTimestamp();
        foreach($messagesList as $message){
            $timeCreated = strtotime($message->created);
            if(!$timeCreated) $timeCreated = 0;

            if($lastLogin - $timeCreated < $newPeriod) $message->new = true;
            else $message->new = false;
        }

        // Только не прочитанные сообщения
        if($structure->only_new){
            foreach($messagesList as $message) $message->showed = false;
            $showedNew = array_keys($messagesList);
        }
        // Все запрошенные сообщения, определение значения сущности $showed
        else{
            $showedRaw = $this->getService('ForumShowed')->getShowedList($user->MID, array_keys($messagesList));

            $showed = array();
            foreach($showedRaw->asArray() as $messageShowed){
                $timeCreated = strtotime($messageShowed['created']);
                if(!$timeCreated) $timeCreated = 0;
                if($timeNow - $timeCreated < $newPeriod) $showed[$messageShowed['message_id']] = true;
            }
            foreach($messagesList as $message) $message->showed = isset($showed[$message->message_id]);
            $showedNew = array_keys(array_diff_key($messagesList, $showed));
        }

        // Добавление просмотренных
        if(!$structure->preview && !empty($showedNew)) $this->getService('ForumShowed')->addShowed($user->MID, $showedNew);

        return $messages;
    }

    /**
     * Удалить форум (со всеми связанными с ним темами и сообщениями)
     *
     * @param HM_Forum_Forum_ForumModel $forum
     */
    public function deleteForum($forum){
        $this->getService('ForumForum')->deleteForum($forum->forum_id);
        $this->getService('ForumSection')->deleteSectionsByForumId($forum->forum_id);
        $this->getService('ForumMessage')->deleteMessagesByForumId($forum->forum_id);
    }

    /**
     * Создать раздел форума / подраздел раздела
     *
     * @param  $data array параметры
     * @param  HM_Forum_Forum_ForumModel
     * @param  HM_Forum_Section_SectionModel | null
     * @return HM_Forum_Section_SectionModel
     * @throws HM_Exception
     */
    public function createSection(array $data, HM_Forum_Forum_ForumModel $forum, HM_Forum_Section_SectionModel $section = null){
        // Нельзя создать раздел в разделе если структура форума не имеет подразделы
        if($section && !$forum->flags->subsections){
            throw new HM_Exception(_(self::ERR_MSG_FORUMNOSECTIONS), self::ERR_CODE_FORUMNOSECTIONS);
        }

        $data = array_replace_recursive($this->config->sections->new->toArray(), $data);
        $data['forum_id'] = (int) $forum->forum_id;
        $data['parent_id'] = (int) $section->section_id;
        $data['text'] = (string) $data['text'];
        $data = $this->_userData($data);

        $section = $this->getService('ForumSection')->createSection($data);
        if(!empty($forum->subject_id)) $section->subject_id = (int) $forum->subject_id;
        return $section;
    }

    /**
     * Изменить приоритет вывода темы
     *
     * @param HM_Forum_Section_SectionModel $section section
     * @param int $order order
     * @return HM_Forum_Section_SectionModel
     */
    public function setOrderOfSection(HM_Forum_Section_SectionModel $section, $order = 0){
        return $this->getService('ForumSection')->updateSection($section->section_id, array('order' => (int) $order));
    }

    /**
     * Закрыть/Открыть тему
     *
     * @param HM_Forum_Section_SectionModel $section
     * @param bool $flag
     * @return HM_Forum_Section_SectionModel
     */
    public function setClosedFlagsOfSection(HM_Forum_Section_SectionModel $section, $flag = true){
        $section->flags->closed = (bool) $flag;
        $data = array('flags' => $section->flags->getEncoded());

        return $this->getService('ForumSection')->updateSection($section->section_id, $data);
    }

    /**
     * Удалить раздел со всеми его подразделами и сообщениями
     *
     * @param HM_Forum_Section_SectionModel $section
     */
    public function deleteSection($section){
        $sections = $this->getService('ForumSection')->deleteSection($section->section_id);
        $this->getService('ForumMessage')->deleteMessagesBySectionId($sections);
    }

    /**
     * Добавить сообщение в тему определённого форума
     *
     * @param  array $data данные сообщения
     * @param  HM_Forum_Forum_ForumModel
     * @param  HM_Forum_Section_SectionModel
     * @return HM_Forum_Message_MessageModel
     */
    public function addMessage(array $data, HM_Forum_Forum_ForumModel $forum, HM_Forum_Section_SectionModel $section = null){
        $data = array_replace_recursive($this->config->messages->new->toArray(), $data);
        $data['forum_id'] = (int) $forum->forum_id;
        $data['section_id'] = (int) $section->section_id;
        $data['text'] = (string) $data['text'];
        $data['title'] = (string) $data['title'];
        $data['text_preview'] = $this->_prepareTextPreview($data['text']);
        $data = $this->_userData($data);
		
		//--фиксируем просрочку при ответе тьютора до сохранения сообщения.		
		if($this->getService('Acl')->inheritsRole(	$this->getService('User')->getCurrentUserRole(),
															array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR)
		)){	
			$this->getService('WorkloadForum')->setForumViolation($this->getService('User')->getCurrentUserId(), $data['forum_id']);
		}
		
        $message = $this->getService('ForumMessage')->addMessage($data);
        if(!empty($forum->subject_id)) $message->subject_id = (int) $forum->subject_id;

        // +1 Количество сообщений в теме
        $this->getService('ForumSection')->incMessagesCounter($message->section_id);

        $this->getService('EventDispatcher')->notify(
            new sfEvent($this, __CLASS__.'::esPushTrigger', array('message' => $message)) 
        );

        return $message;
    }

    /**
     * Удалить сообщение
     * Фактически сообщение остаётся в базе, с установленным флагом "deleted"
     *
     * @param HM_Forum_Message_MessageModel $message
     * @return HM_Forum_Message_MessageModel
     */
    public function deleteMessage(HM_Forum_Message_MessageModel $message){
        $message->flags->deleted = true;
        $message->deleted_by = $this->getService('User')->getCurrentUserId();
        $message->delete_date = $this->getDateTime();
        $data = array('flags' => $message->flags->getEncoded());
        $data['deleted_by']  = $message->deleted_by;
        $data['delete_date'] = $message->delete_date;
        return $this->getService('ForumMessage')->updateMessage($message->message_id, $data);
    }

    /**
     * Выставить оценку сообщения
     *
     * @param HM_Forum_Message_MessageModel $message
     * @param int $rating
     * @return HM_Forum_Message_MessageModel
     */
    public function setMessageRating(HM_Forum_Message_MessageModel $message, $rating = 0){
        $ratings = $this->config->ratings->toArray();
        if(!isset($ratings[$rating])) return;

        return $this->getService('ForumMessage')->updateMessage($message->message_id, array('rating' => $rating));
    }

    /**
     * Получить сообщение с определённым ID
     *
     * @param  int $messageId id сообщения
     * @param  HM_Subject_SubjectModel курс, если сообщение имеет к таковому отношение
     * @return HM_Forum_Message_MessageModel | null
     */
    public function getMessage($messageId, HM_Subject_SubjectModel $subject = null){
        $userId = $this->getService('User')->getCurrentUserId();
        $showedService = $this->getService('ForumShowed');

        $message = $this->getService('ForumMessage')->getMessage((int) $messageId);
        if(!$message) return null;

        $message->showed = $showedService->getShowed($userId, $message->message_id);

        if(!$message->showed){
            $showedService->addShowed($userId, $message->message_id);
            $message->showed = true;
        }

        if($subject){
            $message->subject_id = $subject->subid;
            $message->createdByStudent = (bool) $this->getService('Subject')->isStudent($subject->subid, $message->user_id);
        }

        return $message;
    }

    /**
     * Пометить сообщение как прочитанное
     *
     * @param  HM_Forum_Message_MessageModel $message
     * @return HM_Forum_Message_MessageModel
     */
    public function markMessageShowed(HM_Forum_Message_MessageModel $message){
        if(!empty($message->showed)) return $message;

        $userId = $this->getService('User')->getCurrentUserId();
        $this->getService('ForumShowed')->addShowed($userId, $message->message_id);

        $message->showed = true;
        return $message;
    }

    /**
     * Получить список сообщений раздела
     *
     * @param  int $sectionId id раздела
     * @return HM_Forum_Message_MessageModel[] | null
     */
    public function getMessagesList($sectionId){
        return $this->_getMessagesBySectionId((int) $sectionId);
    }

    /**
     * Подготавливает текст для предпросмотра
     *
     * @param  string $text исходный текст
     * @param  int $length максимальная длинна текста
     * @return string
     */
    protected function _prepareTextPreview($text, $length = 64){
        if (!$text) return '';
        return substr(strip_tags($text), 0, $length);
    }

    /**
     * Сортировка объектов по заданной сущности
     *
     * @param  array $data data
     * @param  string $orderProp property name
     * @param  bool $reverse reverse sort
     * @param  string $propAsKey property as key
     * @param  string $groupsByTimeProp sort by time property
     * @param  bool $gbtReverse sort by time reverse
     * @return array
     */
    protected function _orderSort($data, $orderProp, $reverse = null, $propAsKey = null, $groupsByTimeProp = null, $gbtReverse = null){
        $groups = array();
        foreach($data as $section){
            if(!isset($groups[$section->$orderProp])) $groups[$section->$orderProp] = array();
            $groups[$section->$orderProp][] = $section;
        }

        $reverse ? krsort($groups) : ksort($groups);

        $data = array();
        foreach($groups as $group){
            if($groupsByTimeProp) $group = $this->_timeSort($group, $groupsByTimeProp, $gbtReverse);
            foreach($group as $item){
                if($propAsKey) $data[$item->$propAsKey] = $item;
                else $data[] = $item;
            }
        }

        return $data;
    }

    /**
     * Сортировка объектов по заданной сущности рассматриваемой как строчный timestamp (2006-05-23 12:25:50)
     *
     * @param  array $data data
     * @param  string $timeProperty property name
     * @param  bool $reverse reverse sort
     * @return array data
     */
    protected function _timeSort(array $data, $timeProperty, $reverse = null){
        $sortedKeys = array();
        $sortedValues = array();
        foreach($data as $key => $item){
            $time = strtotime($item->$timeProperty);
            if(isset($sorted[$time])) ++$time;
            $sortedKeys[$time] = $key;
            $sortedValues[$time] = $item;
        }

        $reverse ? krsort($sortedValues) : ksort($sortedValues);

        $data = array();
        foreach($sortedValues as $key => $item) $data[$sortedKeys[$key]] = $item;

        return $data;
    }

    /**
     * Дополняет не достающие данные:
     * id пользователя
     * ip-адрес пользователя
     * ФИО пользователя
     *
     * Проверяет существование пользователя, от имени которого производится действие
     *
     * @param  array $data
     * @return array
     * @throws HM_Exception
     */
    protected function _userData($data){
        // user & user id
        $userService = $this->getService('User');
        if(isset($data['user_id'])){
            $data['user_id'] = (int) $data['user_id'];
            $user = $userService->getOne($userService->find($data['user_id']));
            if(!$user) throw new HM_Exception(_(self::ERR_MSG_NOUSER), self::ERR_CODE_NOUSER);
        }
        else{
            $user = $userService->getCurrentUser();
            $data['user_id'] = $user->MID;
        }

        // user name
        if(!isset($data['user_name'])) $data['user_name'] = substr($user->getName(),0,254);

        // user ip address
        if(!isset($data['user_ip'])) $data['user_ip'] = Zend_Controller_Front::getInstance()->getRequest()->getClientIp();

        return $data;
    }

    public function getLessonModelClass(){
        return 'HM_Lesson_Forum_ForumModel';
    }

    public function onCreateLessonForm(Zend_Form $form, $activitySubjectName, $activitySubjectId, $title = null){

        $lessonID = Zend_Controller_Front::getInstance()->getRequest()->getParam('lesson_id',0);
        $isHidden = false;
        if ($lessonID) {
            $lesson   = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonID));
            if ($lesson) {
                $params = $lesson->getParams();
                $isHidden = (bool) $params['is_hidden'];
            }
        }

        $form->addElement(
            'checkbox',
            'is_hidden',
            array(
                'Label' => _('Включить режим скрытых ответов в теме форума'),
                'description' => _('Сообщения участников в режиме скрытых ответов видит только автор темы. Сообщения же автора видят все участники.'),
                'value' => $isHidden
            )
        );
        return $form;
    }

    public function onLessonUpdate($lesson, $form){
        // если необходимо создаем канал для подписки
        $this->createLessonSubscriptionChannel($lesson);
    }

    public function onSetDefaultsLessonForm(Zend_Form $form, HM_Lesson_LessonModel $lesson) {

    }
    
    public function createEvent(\HM_Model_Abstract $model) {
        /*@var $event Es_Entity_Event */
        $event = $this->getService('ESFactory')->newEvent($model, array(
            'title', 'user_id', 'user_name', 'text', 'created', 'forum_id'
        ));
        return $event;
    }

    public function getRelatedUserList($id) {
        $db =  Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select();
        $result = array();

        $messageSelect = clone $select;
        $messageSelect->from(array('fm' => 'forums_messages'), array())
            ->join(array('fl' => 'forums_list'), 'fl.forum_id = fm.forum_id', array('subid' => 'fl.subject_id'))
            ->where('fm.message_id = ?', $id, 'INTERGER');
        $stmt = $messageSelect->query();
        $stmt->execute();
        $subjectRow = $stmt->fetchAll();
        $subjectId = $subjectRow[0]['subid'];

        if ($subjectId === null || intval($subjectId) == 0) {
            $select->from(array('fm1' => 'forums_messages'), array())
                ->join(array('fm2' => 'forums_messages'), 'fm1.forum_id = fm2.forum_id AND fm1.section_id=fm2.section_id', array('MUID' => 'fm2.user_id'))
                ->join(array('fs' => 'forums_sections'), 'fm1.section_id=fs.section_id', array('SUID' => 'fs.user_id'))
                ->join(array('fs2' => 'forums_sections'), 'fs.parent_id = fs2.section_id', array('FUID' => 'fs2.user_id'))
                ->where('fm1.message_id = ?', $id, 'INTEGER')
                ->group(array('fm2.user_id', 'fs.user_id', 'fs2.user_id'));
            $stmt = $select->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $index => $item) {
                if ($index == 0) {
                    $result[] = intval($item['FUID']);
                    $result[] = intval($item['SUID']);
                }
                $result[] = intval($item['MUID']);
            }
            $result = array_unique($result);
        } else {
            $teachersSubselect = clone $select;
			$tutorsSubselect   = clone $select;
            $studentsSubselect = clone $select;
            $unionSelect = clone $select;
            $teachersSubselect->from(array('s' => 'subjects'), array())
                ->join(array('t' => 'Teachers'), 't.CID = s.subid AND s.subid='.intval($subjectId), array('UserId' => 't.MID'));
				
			$tutorsSubselect->from(array('s' => 'subjects'), array())
				->join(array('tu' => 'Tutors'), 'tu.CID = s.subid AND s.subid='.intval($subjectId), array('UserId' => 'tu.MID'));

            $studentsSubselect->from(array('s' => 'subjects'), array())
                ->join(array('st' => 'Students'), 'st.CID = s.subid AND s.subid='.intval($subjectId), array('UserId' => 'st.MID'));
            $mainSelect = $unionSelect->union(array($teachersSubselect, $studentsSubselect, $tutorsSubselect))
                ->group('UserId');
            $stmt  = $mainSelect->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $item) {
                $result[] = intval($item['UserId']);
            }
        }
        return $result;
    }

    public function triggerPushCallback() {
        return function($ev) {
            $service = $ev->getSubject();
            $params = $ev->getParameters();
            $message = $params['message'];
            /*@var $event Es_Entity_AbstractEvent */ 
            $event = $service->createEvent($message);
            if ($message->subject_id === null) {
                $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_FORUM_ADD_MESSAGE);
            } else {
                $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_FORUM_INTERNAL_ADD_MESSAGE);
                $event->setParam('course_id', $message->subject_id);
                
                $subject = $service->getService('Subject')->find((int)$message->subject_id)->current();
                $event->setParam('course_name', $subject->name);
                
            }
            $user = $service->getService('User')->find(intval($message->user_id))->current();
            $event->setParam('user_name', $user->getName());
            $event->setParam('user_id', $user->getPrimaryKey());
            
            $userAvatar = '/'.ltrim($user->getPhoto(), '/');
            $event->setParam('user_avatar', $userAvatar);
            
            $section = $service->getService('ForumSection')->getSection((int)$message->section_id);
            $event->setParam('section_id', $section->section_id);
            $event->setParam('theme', $section->title);
            $eventGroup = $service->getService('ESFactory')->eventGroup(
                HM_Forum_ForumService::EVENT_GROUP_NAME_PREFIX, intval($message->section_id)
            );
            $groupData = array(
                'theme' => $event->getParam('theme'),
                'section_id' => $event->getParam('section_id')
            );
            if ($event->getParam('course_id') !== null) {
                $groupData['course_id'] = $event->getParam('course_id');
                $groupData['course_name'] = $event->getParam('course_name');
            }
            $eventGroup->setData(
                json_encode($groupData)
            );
            $event->setGroup($eventGroup);
            $esService = $service->getService('EventServerDispatcher');
            $esService->trigger(
                Es_Service_Dispatcher::EVENT_PUSH,
                $service,
                array('event' => $event)
            );
        };
    }

}
