<?php

function html2rgb($color)
{
    if ($color[0] == '#') {
        $color = substr($color, 1);
    }

    if (strlen($color) == 6) {
        list($r, $g, $b) = array(
            $color[0].$color[1],
            $color[2].$color[3],
            $color[4].$color[5]
        );
    } elseif (strlen($color) == 3) {
        list($r, $g, $b) = array(
            $color[0].$color[0],
            $color[1].$color[1],
            $color[2].$color[2]
        );
    } else {
        $r = $g = $b = '00';
    }

    return array(hexdec($r), hexdec($g), hexdec($b));
}

function lum($color)
{
    list($r, $g, $b) = html2rgb($color);

    return sqrt( 0.241 * pow($r, 2) + 0.691 * pow($g, 2) + 0.068 * pow($b, 2) );
}

class HM_Subject_SubjectService extends HM_Service_Abstract
{
    
    const EVENT_GROUP_NAME_PREFIX = 'COURSE_ACTIVITY';
	
	const CACHE_NAME = 'HM_Subject_SubjectService';
	
	const CACHE_LIFETIME				= 900; #900; # время жизни кэша. 15 минут
	
	protected $_userTutors  	= array(); # кэш тьюторов в сессии, доступных определенным студентам.
	protected $_debtTutors  	= array(); # кэш тьюторов в сессии, разделенные на группы продления
	protected $_cacheCreateted 	= NULL; # время первой записи в кэше.
	protected $_moduleData		= NULL; # кэш с данными по модульным дисциплинам
	protected $_cacheCreatetedLight = NULL; #  короткое время первой записи в кэше для данных в ведомости успеваемости.
	
	protected $_moduleSubjects		= NULL; # Накопительный кэш списка модульных сессий. key - код модуля + семестр. value - коллекция сессий.
	protected $_lessonCollections	= NULL; # Накопительный кэш списка занятий в сессии. key - id сессии. value - коллекция занятий.
	protected $_dotList 			= NULL; # Накопительный кэш списка сессий ДО/неДО key - subject_id, value - boolean, true if isDO
	
	protected $_availableStudents	= array(); # кэш студентов, доступных тьютору. key - subject_id, value - array of students_ids
	
	private $_user_balls			= array(); # хранит информацию по оценкам студента за сессию
	
	private function saveToCacheByName($name)
    {        
		return Zend_Registry::get('cache')->save(
            array(
                $name.'_lifetime'	=> time() + self::CACHE_LIFETIME,
                $name				=> $this->{$name},
            ),
            self::CACHE_NAME.'_'.$name
        );
    }
	
	private function clearCacheByName($name)
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME.'_'.$name);
    }
	
	private function restoreFromCacheByName($name)
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME.'_'.$name)) {
            $this->{$name}				= $actions[$name];
            $this->{$name.'_lifetime'}	= $actions[$name.'_lifetime'];
            return true;
        }
        return false;
    }
	
	# истекло время жизни кэша.
	private function isCacheExpired($name){
		if($this->{$name.'_lifetime'} <= time() ){ return true; }
		return false;
	}
	
	
	
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'debtTutors'	=> $this->_debtTutors,                 
                'userTutors'	=> $this->_userTutors,                 
                'moduleData'	=> $this->_moduleData,                 
                'cacheCreateted'	=> $this->_cacheCreateted,                 
                'cacheCreatetedLight'	=> $this->_cacheCreatetedLight,                 
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_debtTutors			= $actions['debtTutors'];            
            $this->_userTutors			= $actions['userTutors'];            
            $this->_moduleData			= $actions['moduleData'];            
            $this->_cacheCreateted		= $actions['cacheCreateted'];            
            $this->_cacheCreatetedLight	= $actions['cacheCreatetedLight'];            
            $this->_restoredFromCache 	= true;
            return true;
        }

        return false;
    }
    
    /**
     * кеш занятий пользователя по курсам
     * используется при подсчете статуса и процента прохождения
     * @var array
     */
    private $_userLessonsCache = array();

    private $_subjectsColorsCache = null;

    /**
     * Кеш соответствий ID оригинальных сущностей в курсе и их копий
     * @var array
     */
    private $_subjectCopyCache = array();
	
	protected $_serviceGraduated 	= NULL;
	protected $_serviceBrs 		 	= NULL;
	protected $_serviceSubjectMark	= NULL;
	

    public function insert($data)
    {
        $data = $this->_prepareData($data);
        $subject = null;
        if ($subject = parent::insert($data)) {

            // создаем дефолтную секцию для материалов в своб.доступе
            $this->getService('Section')->createDefaultSection($subject->subid);


        }
        return $subject;
    }

    public function update($data)
    {
        $data = $this->_prepareData($data);
        $subject = parent::update($data);

        if ($subject) {
            $this->getService('PollFeedback')->updateWhere(array('subject_name' => $subject->name), $this->quoteInto('subject_id = ?', $subject->subid));
            $timeEndedPlanned = false;
            $now = date('Y-m-d H:i:s');
            if ($subject->longtime) {
                $timeEndedPlanned = HM_Date::getRelativeDate(new Zend_Date($now), (int)$subject->longtime);
            } elseif ($subject->end) {
                $timeEndedPlanned = new Zend_Date($subject->end);
				
            } elseif ($subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) {			
                $timeEndedPlanned = new Zend_Date($subject->end_planned);
            }						
            if($timeEndedPlanned) {
                $this->getService('Student')->updateWhere(
                    array('time_ended_planned' => $timeEndedPlanned->get(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY) . ' 23:59:59'),
                    $this->quoteInto('CID = ?', $subject->subid)
                );
            }
        }

        return $subject;
    }

    /**
     * @param $subjectId
     * @param HM_Form_Element_Html5File|HM_Form_Element_ServerFile $photo Элемент формы
     * @param $destination Путь к папке с иконками
     * @param $size[] Максимальный размер иконки
     * @return bool
     * @todo Реализовать возможность выбирать размер иконок, решить как их сохранять (менять название файла/создавать папку)
     */
    public static function updateIcon($subjectId, $photo, $destination = null)
    {
        if (empty($destination)) {
            $destination = Zend_Registry::get('config')->path->upload->subject;
        }
        $w = $h = 90;
        $path = rtrim($destination, '/') . '/' . $subjectId . '.jpg';
        if ($photo instanceof HM_Form_Element_ServerFile) {
            $photoVal = $photo->getValue();
            //если инпут пустой - удаляем ткущее изображение
            if (empty($photoVal)) {
                unlink($path);
                return true;
            }
            $original = APPLICATION_PATH . '/../public' . $photoVal;
            //если новая картинка = старой, то ничего не меняем
            if (md5_file($original) == md5_file($path)) {
                return true;
            }
            $img = PhpThumb_Factory::create($original);
            $img->resize($w, $h);
            $img->save($path);
        } elseif ($photo->isUploaded()){
            $original = rtrim($photo->getDestination(), '/') . '/' . $photo->getValue();
            $img = PhpThumb_Factory::create($original);
	        $img->resize($w, $h);
	        $img->save($path);
            unlink($original);
        }
        return true;
    }

    private function _prepareData($data)
    {
        if (isset($data['period']) && ($data['period'] !== '')) {
            switch($data['period']) {
                case HM_Subject_SubjectModel::PERIOD_FREE:
                case HM_Subject_SubjectModel::PERIOD_FIXED:
                    $today = new HM_Date();
                    $data['begin'] = (string) $today->getDate();
                    //$today->add(1, HM_Date::MONTH);
                    $data['end'] = (string) $today->getDate();
//                 	$data['begin'] = $data['end'] = '';
                    break;
                case HM_Subject_SubjectModel::PERIOD_DATES:
// если дата начала и дата окончания совпадают - это значит курс идет один день!!!
//                    if (!empty($data['begin']) && ($data['begin'] == $data['end'])) {
//                        $data['begin'] = $data['end'] = '';
//                    }
//                    $today = new HM_Date();
//                    if (!$data['begin']) {
//                        $data['begin'] = (string) $today->getDate();
//                    }
//                    $today->add(1, HM_Date::MONTH);
//                    if (!$data['end']) {
//                        $data['end'] = (string) $today->getDate();
//                    }
                    if (!empty($data['end'])) {
                        $date = new Zend_Date(strtotime($data['end']));
                        $date->add(1, HM_Date::DAY);
                        $date->sub(1, HM_Date::SECOND);
                        $data['end'] = $date->toString('dd.MM.y H:m:s');
                    }
                    break;
                default:
                    unset($data['period']);
                    break;
            }
        } else {
            unset($data['period']);
        }
		
		if($data['base'] == HM_Subject_SubjectModel::BASETYPE_SESSION && (!isset($data['services']) || empty($data['services']))){			
			//$data['services'] = HM_Activity_ActivityModel::ACTIVITY_FORUM; //2--форум в сервисах
			$data['services'] = HM_Activity_ActivityModel::ACTIVITY_FORUM + HM_Activity_ActivityModel::ACTIVITY_NEWS; //2--форум в сервисах + 1 -- новости
		}	
		
		# только при создании новой записи
		if(empty($data['subid'])){		
			$data['date_created'] = new Zend_Db_Expr('NOW()');
		}
		
        return $data;
    }

    public function delete($courseId)
    {
        if ($subject = $this->find($courseId)->current()) {
            if ($subject->base_id && ($subject->base == HM_Subject_SubjectModel::BASETYPE_SESSION)) {
                if (count($this->getSessions($subject->base_id)) == 1) { // последний из могикан
                    $this->updateWhere(array('base' => HM_Subject_SubjectModel::BASETYPE_PRACTICE), array('subid = ?' => $subject->base_id));
                }
            }
        }

        // Удаляем связки из subjects_courses
        $this->getService('SubjectCourse')->deleteBy(
            $this->quoteInto('subject_id = ?', (int) $courseId));
        
        $this->getService('StudyGroupCourse')->deleteBy($this->quoteInto('course_id = ?', $courseId));
        $this->getService('Section')->deleteBy($this->quoteInto('subject_id = ?', $courseId));
        $this->getService('Student')->deleteBy($this->quoteInto('CID = ?', $courseId));
        $this->getService('Teacher')->deleteBy($this->quoteInto('CID = ?', $courseId));
        $this->getService('ProgrammEvent')->deleteBy(
            $this->quoteInto(array('type = ?', ' AND item_id = ?'), array(HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, $courseId))
        );
		$countGraduated = $this->getService('Graduated')->fetchAll(array('CID = ?' => $courseId));
		if (count($countGraduated)>0)
			$this->getService('Graduated')->deleteBy($this->quoteInto('CID = ?', $courseId));
        return parent::delete($courseId);
    }

    public function linkRooms($subjectId, $rooms)
    {
        $this->unlinkRooms($subjectId);
        if (is_array($rooms) && count($rooms)) {
            foreach($rooms as $roomId) {
                if ($roomId > 0) {
                    $this->linkRoom($subjectId, $roomId);
                }
            }
        }
        return true;
    }

    public function linkRoom($subjectId, $roomId)
    {
        $this->getService('SubjectRoom')->deleteBy(array('cid = ?' => $subjectId));
        return $this->getService('SubjectRoom')->insert(
            array(
                'cid' => $subjectId,
                'rid'  => $roomId
            )
        );
    }

    public function unlinkRooms($subjectId)
    {
        return $this->getService('SubjectRoom')->deleteBy(
            $this->quoteInto('cid = ?', $subjectId)
        );
    }

    public function linkClassifiers($subjectId, $classifiers)
    {
        $this->getService('Classifier')->unlinkItem($subjectId, HM_Classifier_Link_LinkModel::TYPE_SUBJECT);
        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                if ($classifierId > 0) {
                    $this->getService('Classifier')->linkItem($subjectId, HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $classifierId);
                }
            }
        }
        return true;
    }

    public function linkClassifier($subjectId, $classifierId)
    {
        return $this->getService('SubjectClassifier')->insert(
            array(
                'subject_id' => $subjectId,
                'classifier_id'  => $classifierId
            )
        );
    }

    public function unlinkClassifiers($subjectId)
    {
        return $this->getService('SubjectClassifier')->deleteBy(
            $this->quoteInto('subject_id = ?', $subjectId)
        );
    }

    public function unlinkCourse($subjectId, $courseId)
    {
        return $this->getService('SubjectCourse')->deleteBy(
            $this->quoteInto(array('subject_id = ?', ' AND course_id = ?'), array($subjectId, $courseId))
        );
    }

    public function linkCourse($subjectId, $courseId)
    {
        return $this->getService('SubjectCourse')->insert(
            array(
                'subject_id' => $subjectId,
                'course_id'  => $courseId
            )
        );
    }

    public function unlinkCourses($subjectId)
    {
        return $this->getService('SubjectCourse')->deleteBy(
            $this->quoteInto('subject_id = ?', $subjectId)
        );
    }

    public function getCourses($subjectId, $status = null)
    {
        if (null == $status) {
            return $this->getService('Course')->fetchAllDependenceJoinInner(
                'SubjectAssign',
                $this->quoteInto('SubjectAssign.subject_id = ?', $subjectId),
                'self.Title'
            );
        } else {
            return $this->getService('Course')->fetchAllDependenceJoinInner(
                'SubjectAssign',
                $this->quoteInto(array('SubjectAssign.subject_id = ?', ' AND self.Status = ?'), array($subjectId, $status)),
                'self.Title'
            );
        }
    }

    public function getFreeSubjects($count = 20, $user_id = null){

/*    	$subjects = $this->fetchAllDependenceJoinInner(
    					'Student',
    					$this->quoteInto(array(
    										'(reg_type = ?',
    										' OR reg_type = ?)',
    										' AND end > ?',
    										' AND registered IS NULL'
    									),
    									array(
    										HM_Subject_SubjectModel::REGTYPE_FREE,
    										HM_Subject_SubjectModel::REGTYPE_MODER,
    										$this->getDateTime()
    									))
    	);*/
	    $select = $this->getSelect();
	    if (!$count) $select->distinct();
		$select->from(
						array('s' => 'subjects'),
						array('subject_id' => 's.subid')
					);
		if($user_id){
			$select->joinLeft(
							array('st' => 'Students'),
							'st.CID = s.subid AND st.MID = '.$user_id,
							array('registeged' => 'st.registered')
						);
			$select->where('registered IS NULL');
		}
		$select->where($this->quoteInto(
            array('s.reg_type = ?', ' OR s.reg_type = ?'),
            array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_MODER)
		));
		$select->where($this->quoteInto(
            array('s.period <> ?', ' OR s.end > ?'),
            array(HM_Subject_SubjectModel::PERIOD_DATES, $this->getDateTime())
		));
		if($count) $select->limit($count);

		$tmp = $select->query()->fetchAll();
		$tmp = (is_array($tmp)) ? $tmp : array();

		$free_subjects = array();
		foreach ($tmp as $value) {
            $free_subjects[] = $value['subject_id'];
		}

		return $free_subjects;
    }

    public function isTeacher($subjectId, $userId)
    {
        return $this->getService('Teacher')->isUserExists($subjectId, $userId);
    }

    public function isTutor($subjectId, $userId)
    {
        return $this->getService('Tutor')->isUserExists($subjectId, $userId);
    }

    public function isStudent($subjectId, $userId)
    {
        return $this->getService('Student')->isUserExists($subjectId, $userId);
    }

    public function isGraduated($subjectId, $userId)
    {
        return $this->getService('Graduated')->isUserExists($subjectId, $userId);
    }

    /**
     * Возвращает модели юзеров, присвоенных определенному курсу
     * @param unknown_type $subject_id  Id Курса
     * @return multitype:
     */
    public function getAssignedUsers($subject_id)
    {
        $midWithHalfAccess = $this->getService('UserInfo')->getMidWithHalfAccess();
        
        if(empty($midWithHalfAccess)){
            $criteria = $this->quoteInto(
                array("Student.CID = ? ", " AND blocked != ?"),
                array(intval($subject_id), HM_User_UserModel::STATUS_BLOCKED)
            );
        } else {
            $criteria = $this->quoteInto(
                array("Student.CID = ? ", " AND blocked != ?", " AND Student.MID NOT IN (?)"),
                array(intval($subject_id), HM_User_UserModel::STATUS_BLOCKED, $midWithHalfAccess)
            );
        }
        return $this->getService('User')->fetchAllJoinInner('Student', $criteria);
    }

    public function getAssignedTeachers($subject_id){
        $collection = $this->getService('User')->fetchAllJoinInner('Teacher', 'Teacher.CID = '. (int) $subject_id);
        return $collection;
    }

    public function getAssignedTutors($subject_id){
        $collection = $this->getService('User')->fetchAllJoinInner('Tutor', 'Tutor.CID = '. (int) $subject_id);
        return $collection;
    }

    public function getAssignedGraduated($subject_id){
        $collection = $this->getService('User')->fetchAllJoinInner('Graduated', 'Graduated.CID = '. (int) $subject_id);
        return $collection;
    }
	
	public function getAssignedGraduatedActive($subject_id){
        $collection = $this->getService('User')->fetchAllJoinInner('Graduated', 'Graduated.CID = '. (int) $subject_id." AND blocked != '".HM_User_UserModel::STATUS_BLOCKED."'");
        return $collection;
    }

    public function assignUser($subjectId, $studentId)
    {
        $subject = $this->getOne($this->find($subjectId));
        if ($subject) {
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN) ||
                $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)
                ) {
                $this->assignStudent($subjectId, $studentId);
            } else {
                if ($subject->claimant_process_id == 0) {
                    $this->assignAcceptedClaimant($subjectId, $studentId);
                } else {
                $this->assignClaimant($subjectId, $studentId);
            }
        }
    }
    }

    public function assignDeanPolls($subjectId, $studentId)
    {
        $polls = $this->getService('LessonDeanPoll')->fetchAll(
            $this->quoteInto(
                array($this->quoteIdentifier('all').' = ?', ' AND CID = ?'),
                array('1', $subjectId)
            )
        );

        if (count($polls)) {
            foreach($polls as $poll) {
                $poll->getService()->assignStudents($poll->SHEID, array($studentId), false);
            }
        }
    }

    public function assignGraduated($subjectId, $studentId, $status = NULL)
    {
        $result = false;
        $student = $this->getOne(
            $this->getService('Student')->fetchAll(
                array(
                    'CID = ?' => $subjectId,
                    'MID = ?' => $studentId
                )
            )
        );
        if ($student) {

            # на текущий момент не нужно нам создавать сертификаты. Они не используются.
			# $certificate = $this->getService('Certificates')->addCertificate($studentId, $subjectId);
            $certificate_id = ($certificate)? $certificate->certificate_id : 0;
            if ($status === NULL) {
                $status = HM_Role_GraduatedModel::STATUS_SUCCESS;
            }
            $result = $this->getService('Graduated')->insert(
                array(
                     'MID'            => $studentId,
                     'CID'            => $subjectId,
                     'begin'          => $student->time_registered,
                     'status'         => (int) $status,
                     'certificate_id' => $certificate_id
                 )
            );
            
			/* #TT-56276: нет необходимости в уведомлении при переводе в прошедшее студентов. 
            $service = $this->getService('SubjectMark');
            $userSubjectMark = $service->getOne($service->fetchAll('mid = '.intval($studentId).' AND cid = '.intval($subjectId)));
            if ($userSubjectMark) {
                $this->getService('EventDispatcher')->notify(
                    new sfEvent(
                        $service,
                        get_class($service).'::esPushTrigger',
                        array('mark' => $userSubjectMark)
                    )
                );
            }
			*/

            $this->getService('Student')->deleteBy(array('CID = ?'=> $subjectId, 'MID = ?' => $studentId));
            
            // #16545
            $subject = $this->getOne($this->findDependence('Lesson', $subjectId));
            foreach ($subject->lessons as $lesson) {
                $this->getService('Subscription')->unsubscribeUserFromChannelByLessonId($studentId, $lesson->SHEID);
            }
            
            /** 
             * при автоматическом завершении курса,
             * у пользователя удаляется роль student и текущая роль в 
             * $GLOBALS['controller']->user->profile_current должна стать user,
             * но она становаится NULL из-за этого главное меню отображается 
             * как для незалогиненного пользователя.
             * ниже проверяется есть ли у пользователя еще курсы (если есть, то меню и так остается нормальным)
             * и является ли текущий пользователь тем, у кого завершен курс (текущий пользователь может быть и учителем, 
             * который вручную переводит студента в завершивших курс), затем принудительно выставляется роль "USER"
             * 
             * @todo разобраться почему удаляется profile_current из $GLOBALS['controller']->user
             */
            $UserHasSubjects = count($this->getService('Student')->getSubjects($studentId));
            $isStudent = ($studentId == $this->getService('User')->getCurrentUserId());
            if(!$UserHasSubjects && $isStudent){
                $this->getService('User')->switchRole(HM_Role_RoleModelAbstract::ROLE_USER);
            }
            //
            
            
            // назначение кураторских опросов "всем новым"
            $this->assignDeanPolls($subjectId, $studentId);
        }
        return $result;
    }

    public function unassignGraduated($subjectId, $studentId)
    {
        $subject = $this->getOne($this->findDependence(array('Graduated', 'Lesson'), $subjectId));
        if ($subject) {
            if ($subject->isGraduated($studentId)) {
                $this->getService('Graduated')->deleteBy(sprintf("MID = '%d' AND CID = '%d'", $studentId, $subjectId));
            }

            $lessons = $subject->getLessons();
            if (count($lessons)) {
                foreach($lessons as $lesson) {
                    $lesson->getService()->unassignStudent($lesson->SHEID, $studentId);
                    /*
                    if (!in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))) {
                        $this->getService('Lesson')->unassignStudent($lesson->SHEID, $studentId);
                    }
                    if (in_array($lesson->typeID, array_keys(HM_Event_EventModel::getDeanPollTypes()))) {
                        $this->getService('LessonDeanPoll')->unassignStudent($lesson->SHEID, $studentId);
                    }

                     */
                }
            }
        }
    }

    /**
     * Добавляет заявку со статусом HM_Role_ClaimantModel::STATUS_ACCEPTED
     * @param int $subjectId
     * @param int $studentId
     */
    public function assignAcceptedClaimant($subjectId, $studentId)
    {
        $subject = $this->getOne($this->findDependence('Claimant', $subjectId));
        if ($subject) {
            if (!$subject->isClaimant($studentId)) {
                $user = $this->getOne($this->getService('User')->find($studentId));
                $this->getService('Claimant')->insert(
                    array(
                        'MID' => $studentId,
                        'CID' => $subjectId,
                        'created' => $this->getDateTime(),
                        'begin' => $this->getDateTime(),
                        'end' => $subject->end,
                        'status' => HM_Role_ClaimantModel::STATUS_ACCEPTED,
                        'lastname' => $user->LastName,
                        'firstname' => $user->FirstName,
                        'patronymic' => $user->Patronymic
                    )
                );
            }
            $this->assignStudent($subjectId, $studentId);
        }
    }


    public function assignClaimant($subjectId, $studentId)
    {
        $subject = $this->getOne($this->findDependence('Claimant', $subjectId));
        if ($subject) {
            if (!$subject->isClaimant($studentId)) {
                $user = $this->getOne($this->getService('User')->find($studentId));
				$lastName      =   $user->LastName;
				$firstName     =   $user->FirstName;
				$patronymic    =   $user->Patronymic;
				$mid           =   $user->MID;
				$mid_external   =   $user->mid_external;		
				//Делаем запрос в БД(Table)`Claimant` и проверяем существует ли такой пользователь
				//если существует, то кладем в переменную dublicated MID пользователя на которого
				//похож, регистрирующийся пользователь - дубликат
				//$dublicated = $this->getService('Claimant')->checkDublicate($lastName, $firstName, $patronymic, $mid, $mid_external);  
                $this->getService('Claimant')->insert(
                    array(
                        'MID' => $studentId,
                        'CID' => $subjectId,
                        'created' => $this->getDateTime(),
                        'begin' => $this->getDateTime(),
                        'end' => $subject->end
                        //'lastname' => $user->LastName,
                        //'firstname' => $user->FirstName,
                        //'patronymic' => $user->Patronymic,
						//'dublicate' => $dublicated,
						//'mid_external'=> $user->mid_external,
                    )
                );
				$dublicated = $this->getService('Claimant')->updateClaimant();
                // Сообщение администрации
                $messenger = $this->getService('Messenger');
                $messenger->addMessageToChannel(HM_Messenger::SYSTEM_USER_ID,
                                              HM_Messenger::SYSTEM_USER_ID,
                                              HM_Messenger::TEMPLATE_ORDER,
                                              array(
                                                    'subject_id' => $subjectId,
                                                    'url_user' => Zend_Registry::get('view')->serverUrl(
                                                        Zend_Registry::get('view')->url(array(
                                                            'module' => 'user',
                                                            'controller' => 'edit',
                                                            'action' => 'card',
                                                            'user_id' => $studentId
                                                        ), null, true)
                                                    ),
                                                    'user_login'       => $user->Login,
                                                    'user_lastname'    => $user->LastName,
                                                    'user_firstname'   => $user->FirstName,
                                                    'user_patronymic'  => $user->Patronymic,
                                                    'user_mail'        => $user->EMail,
                                                    'user_phone'       => $user->Phone,
                                                    'subject_price'    => $subject->price,
                                                    'subject_currency' => $subject->price_currency
                                              )
                                             );
                /*$messenger->setOptions(
                    HM_Messenger::TEMPLATE_ORDER,
                    array(
                        'subject_id' => $subjectId,
                        'url_user' => Zend_Registry::get('view')->serverUrl(
                                        Zend_Registry::get('view')->url(array(
                                            'module' => 'user',
                                            'controller' => 'edit',
                                            'action' => 'card',
                                            'user_id' => $studentId
                                        ), null, true)
                                    ),
                        'user_login'       => $user->Login,
                        'user_lastname'    => $user->LastName,
                        'user_firstname'   => $user->FirstName,
                        'user_patronymic'  => $user->Patronymic,
                        'user_mail'        => $user->EMail,
                        'user_phone'       => $user->Phone,
                        'subject_price'    => $subject->price,
                        'subject_currency' => $subject->price_currency
                    )
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, HM_Messenger::SYSTEM_USER_ID);
                */
                // Сообщение пользователю
                $messenger->addMessageToChannel(HM_Messenger::SYSTEM_USER_ID,
                    $studentId,
                    HM_Messenger::TEMPLATE_ORDER_REGGED,
                    array(
                        'subject_id' => $subjectId
                    )
                );
                /*$messenger->setOptions(
                    HM_Messenger::TEMPLATE_ORDER_REGGED,
                    array(
                        'subject_id' => $subjectId
                    )
                );

                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $studentId);
                */

            }
        }
    }
	
	/**
	 * проверяет причины по которых студент не долже быть назначен на сессию. Тут же и формируем FlashMessenger сообщения.
	 * глупо, но необходимо, ибо слишком много оберток до выхода на контроллер
	 * Вы пихаете в сервисный слой то, что должно быть в контроллере! Не надо так!
	*/
	public function isNotNeedToAssign($user_id, $subject_id, $ignore_begin_learning = false){
		
		if(empty($this->subject)){ $this->subject = $this->getByid($subject_id); }
		
		$this->_flashMessenger	= Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		
		
		# нельзя назначать по причине ин.яза (подгруппы)
		if($this->isStopAssignByLanguage($user_id, $subject_id)){			
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Студент #'.$user_id.' не может быть назначен, т.к. не совпадает подгруппа (ин.яз)'))
			);			
			return true;
		}
		
		# нельзя назначить по причине несовпадения даты начала обучения. Справедливо для ГИА. 
		# Но если дата будет у обычной сессии, она также будет проверяться
		if(!$ignore_begin_learning){
			if($this->isStopAssignByDateBeginLearning($user_id, $subject_id)){
				$this->_flashMessenger->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						'message' => _('Студент #'.$user_id.' не может быть назначен, т.к. не совпадает дата начала обучения'))
				);			
				return true;
			}
		}
		
		return false;		
	}
	
	/**
	 * Если заданы даты начала обучения и они не совпадают, студента НЕ назначаем.
	 * Если дата меньше 2019-09-01 - считаем сессию старой. Дата начала обучения НЕ учитывается.
	 * TODO проработать вариант с датой 1900 и 1980 - считать их пустыми полями.	 
	*/
	public function isStopAssignByDateBeginLearning($user_id, $subject_id){
		
		if(empty($user_id) || empty($subject_id)){ return false; }
		
		if(empty($this->subject)){ $this->subject = $this->getByid($subject_id); }
		
		if(empty($this->subject->begin_learning)){ return false; }
		
		# Это старая сессия. Дата начала обучения не учитывается
		if( strtotime($this->subject->date_created) <= HM_Subject_SubjectModel::getOldSubjectToTimestamp() ){ return false; }
		
		# 1900 or 1980 
		$tm = strtotime($this->subject->begin_learning);
		if($tm <= 0){ return false; }
		
		if(!$this->userService){ $this->userService =  $this->getService('User'); };
		
		$user = $this->userService->getById($user_id);
		if(empty($user)){ return false; }
		
		# Дата начала из Студента
		if(strtotime($this->subject->begin) < HM_Subject_SubjectModel::getUserBeginLearningDateToTimestamp()){
			if($user->begin_learning == $this->subject->begin_learning){ return false; }
			return true;
		}
		
		# Дата начала из Группы
		$groups = $this->getService('StudyGroupCustom')->getUserGroups($user_id); # HM_Collection
		if(!count($groups)){ return false; }
		
		foreach($groups as $group){
			if(empty($group->begin_learning)){ continue; }
			if($group->begin_learning == $this->subject->begin_learning){ return false; }
		}
		return true;
	}
	
	
	/**
	 * Если запись по языкам/подгруппам есть, то можно назначить на сессию
	*/
	public function isStopAssignByLanguage($user_id, $subject_id){
		if(empty($user_id) || empty($subject_id)){ return false; }
		
		if(empty($this->subject)){ $this->subject = $this->getByid($subject_id); }
		
		if(empty($this->subject->language_code)){ return false; }
		
		if(!$this->userService){ $this->userService =  $this->getService('User'); };
			
		$user = $this->userService->getById($user_id);
		if(!$user || empty($user->mid_external)){ return false; }
		
		$disciplineCode = $this->subject->getDisciplineCode();
		
		$select = $this->getSelect();
		$select->from('Students_language', array('LID') );					
		$select->where($this->quoteInto(array('mid_external = ?', ' AND language_id = ?', ' AND discipline_code = ?'), array($user->mid_external, $this->subject->language_code, $disciplineCode)));
		$res = $select->query()->fetchObject();
		if(isset($res->LID)){ return false; }
		
		return true;
	}

	
	/**
	 * $this->getOne($this->findDependence вызывается для каждого студента раз за разом. Изменить на одиночный выхов и передачу в свойстве класса $this->subject
	*/
    public function assignStudent($subjectId, $studentId, $isNotSendMessage = false, $ignore_begin_learning = false)
    {
        if(empty($studentId)){ return false; }
		$subject 		= $this->getOne($this->findDependence(array('Student', 'Lesson', 'ClassifierLink'), $subjectId));
		$this->subject	= $subject;
		#pr(1);
		
		# Нужно ли назначить студента пр признаку "Дата начала обучения"
		# TODO слить с isNeedAssign под общий функционал
		if(  $this->isNotNeedToAssign($studentId, $subjectId, $ignore_begin_learning)){
			return false;
		}
		
		
		/*
		if(
			!empty($subject->language_code)
			&&
			!$this->isNeedAssign($studentId, $subject->language_code)){
				#pr(2);
				return false;
				
		}
		*/
		
		#pr(3);
        if ($subject) {
            if (!$subject->isStudent($studentId)) {

        		$now = date('Y-m-d H:i:s');
        		$timeEndedPlanned = false;
        		if ($subject->longtime) {
        			$timeEndedPlanned = HM_Date::getRelativeDate(new Zend_Date($now), (int)$subject->longtime);
        		} elseif ($subject->end) {
        			$timeEndedPlanned = new Zend_Date($subject->end);
        		} elseif ($subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) {
        		    $timeEndedPlanned = new Zend_Date($subject->end_planned);
        		}
        		$beginPlanned = new Zend_Date($subject->begin_planned);

                $this->getService('Student')->insert(
                    array(
                        'MID' => $studentId,
                        'CID' => $subjectId,
                        'Registered' => time(),
                        'time_registered' => (($subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) || ($subject->state == HM_Subject_SubjectModel::STATE_ACTUAL )) ? date('Y-m-d H:i:s') : $beginPlanned->get('Y-MM-dd'),
                    	'time_ended_planned' => $timeEndedPlanned ? $timeEndedPlanned->get('Y-MM-dd') . ' 23:59:59' : null,
                    )
                );

	            // если курс стартует вручную - занятия не назначать    public function assignTutor($subjectId, $tutorId)
//            $collection = $this->getService('Student')->fetchAll(
//                $this->quoteInto(array('MID = ?', ' AND CID = ?'), array($teacherId, $subjectId));

            /*
            $collection = $this->getService('Student')->fetchAll(
                $this->quoteInto(array('MID = ?', ' AND CID = ?'), array($teacherId, $subjectId))
            );

            if (!count($collection)) {
                $this->getService('Student')->insert( // #11928
                    array(
                        'MID' => $teacherId,
                        'CID' => $subjectId,
                        'Registered' => time(),
                        'time_registered' => $this->getService('Student')->getDateTime(),
            	        'time_ended_planned' => $this->getService('Student')->getDateTime(strtotime('+5 year'))
                    )
                );
            }

            if (!count($collection)) {
                $this->getService('Student')->insert(
                    array(
                        'MID' => $teacherId,
                        'CID' => $subjectId,
                        'Registered' => time(),
                        'time_registered' => $this->getService('Student')->getDateTime(),
                        'time_ended_planned' => $this->getService('Student')->getDateTime(strtotime('+5 year'))
                    )
                );
            }
            */
                if (($subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) || ($subject->state == HM_Subject_SubjectModel::STATE_ACTUAL )) {

                    // assign course lessons
                    $lessons = $subject->getLessons();
                    if (count($lessons)) {
                        foreach($lessons as $lesson) {
                            if (
                                $lesson->all
                                && $lesson->isfree != HM_Lesson_LessonModel::MODE_FREE_BLOCKED
                                && !in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))
                            ) {
                                $lesson->getService()->assignStudent($lesson->SHEID, $studentId);
                            }
                        }
                    }
                }

                // Отправка сообщения о назначении на учебный курс
                $roles = HM_Role_RoleModelAbstract::getBasicRoles();
				
				# явно указанный запрет на отправку уведомления
				if(!$isNotSendMessage){
				
					# Если сессия Текущая или будущая.
					# или студент не заблокированный
					if($this->isPresent($subject) && $this->getService('User')->getById($studentId)->blocked != HM_User_UserModel::STATUS_BLOCKED){					
						// если курс стартует вручную - заранее емайл не посылаем
						if (($subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL) || ($subject->state == HM_Subject_SubjectModel::STATE_ACTUAL )) {

							$messenger = $this->getService('Messenger');
							$messenger->setOptions(
								HM_Messenger::TEMPLATE_ASSIGN_SUBJECT,
								array(
									'subject_id' => $subjectId,
									'role' => $roles[HM_Role_RoleModelAbstract::ROLE_STUDENT]
								),
								'subject',
								$subjectId
							);
							//$messenger->setIcal($this->getIcal($subject, $studentId));

							$messenger->send(HM_Messenger::SYSTEM_USER_ID, $studentId);
						}
					}				
				}
				#
				if(!$this->_serviceGraduated)	{ $this->_serviceGraduated 	 = $this->getService('Graduated'); 		}
				if(!$this->_serviceBrs)			{ $this->_serviceBrs 		 = $this->getService('MarkBrsStrategy');}
				if(!$this->_serviceSubjectMark)	{ $this->_serviceSubjectMark = $this->getService('SubjectMark'); 	}
				$isGraduated = $this->_serviceGraduated->fetchAll(array('MID = ?' => $studentId, 'CID = ?' =>$subjectId))->getList('SID');
				if(!empty($isGraduated)){
					$this->_serviceGraduated->deleteBy(array('MID = ?' => $studentId, 'CID = ?' =>$subjectId)); # удаляем запись из прошедших
					$totalBall = $this->_serviceBrs->calcTotalValue($subjectId, $studentId, true); # пересчитываем итоговую оценку, она может быть взята из 1С и не совпадать с реальным баллом по урокам.
					$totalBall = ($totalBall > 100)?(100):($totalBall);
					
					$data = array(
						'cid' 		=> $subjectId,
						'mid' 		=> $studentId,
						'mark' 		=> $totalBall,
						'confirmed' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,
						'sync_1c' 	=> 0, # снимаем флаг синхронизации с 1С
					);
					$this->_serviceSubjectMark->updateWhere($data, array(
						'cid = ?' => $subjectId,
						'mid = ?' => $studentId
					));
				}
				#
				
            }
        }
    }
	
	/**
	 * повторное назначение студентов на сессию, если они уже закончили обучение.
	 * 
	*/
	public function reAssignGraduatedStudent($subjectId, $studentId){		
		if(!$subjectId || !$studentId) { return false; }
		$this->assignStudent($subjectId, $studentId);
		/*
		$this->getService('Graduated')->deleteBy(array('MID = ?' => $studentId, 'CID = ?' =>$subjectId)); # удаляем запись из прошедших
		$totalBall = $this->getService('MarkBrsStrategy')->calcTotalValue($subjectId, $studentId, true); # пересчитываем итоговую оценку, она может быть взята из 1С и не совпадать с реальным баллом по урокам.
		$totalBall = ($totalBall > 100)?(100):($totalBall);
		
		$data = array(
            'cid' 		=> $subjectId,
            'mid' 		=> $studentId,
            'mark' 		=> $totalBall,
            'confirmed' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,
			'sync_1c' 	=> 0, # снимаем флаг синхронизации с 1С
        );
		$this->getService('SubjectMark')->updateWhere($data, array(
			'cid = ?' => $subjectId,
			'mid = ?' => $studentId
		));
		*/
	}

    public function startSubjectForStudent($subjectId, $studentId)
    {
        $subject = $this->getOne($this->findDependence(array('Student', 'Lesson'), $subjectId));

        if ($subject) {
            if ($subject->isStudent($studentId)) {

        			$timeEndedPlanned = new Zend_Date($subject->end_planned);

                $this->getService('Student')->updateWhere(
                    array(
                        'time_registered' => date('Y-m-d H:i:s'),
                    	'time_ended_planned' => $timeEndedPlanned ? $timeEndedPlanned->get('Y-MM-dd') . ' 23:59:59' : null,
                    ), array(
                        'MID = ?' => $studentId,
                        'CID = ?' => $subjectId,
                    )
                );


	            // assign course lessons
	            $lessons = $subject->getLessons();
	            if (count($lessons)) {
	                foreach($lessons as $lesson) {
	                    if ($lesson->all && !in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))) {
	                        $lesson->getService()->assignStudent($lesson->SHEID, $studentId);
	                    }
	                }
	            }

                // Отправка сообщения о назначении на учебный курс
                $roles = HM_Role_RoleModelAbstract::getBasicRoles();

                $messenger = $this->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_ASSIGN_SUBJECT,
                    array(
                        'subject_id' => $subjectId,
                        'role' => $roles[HM_Role_RoleModelAbstract::ROLE_STUDENT]
                    ),
                    'subject',
                    $subjectId
                );

                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $studentId);
            }
        }
    }

    public function unassignStudent($subjectId, $studentId)
    {
        $subject = $this->getOne($this->findDependence(array('Student', 'Lesson'), $subjectId));
        if ($subject) {
            if ($subject->isStudent($studentId)) {
                $this->getService('Student')->deleteBy(sprintf("(MID = '%d' AND CID = '%d') OR (MID = '%d' AND CID = 0)", $studentId, $subjectId, $studentId));
                //$this->getService('Student')->deleteBy(sprintf("MID = '%d' AND CID = 0", $studentId, $subjectId));
            }

            $lessons = $subject->getLessons();
            if (count($lessons)) {
                foreach($lessons as $lesson) {
                    if (!in_array($lesson->typeID, array_keys(HM_Event_EventModel::getExcludedTypes()))) {
                        $this->getService('Lesson')->unassignStudent($lesson->SHEID, $studentId);
                    }
                }
            }
            
            $groups = $this->getService('Group')->fetchAll(array('cid = ?' => $subject->subid));
            if(count($groups)){
                foreach($groups as $group){
                    $this->getService('GroupAssign')->deleteBy(array('mid = ?' => $studentId, 'cid = ?' =>$subject->subid, 'gid = ?' => $group->gid));
                }
            }
        }
    }

    public function assignTeacher($subjectId, $teacherId) //--используется ли этот экшен?
{
    if (null !== $edoTeacherId) {
        // Назначение деканского преподавателя на очный курс
        $this->assignTeacherToDistanceSubject($subjectId, $edoTeacherId);
    }
    if(!$this->isTeacher($subjectId, $teacherId)) {
        $this->getService('Teacher')->insert(array(
            'MID' => $teacherId,
            'CID' => $subjectId,            			
			'teacher_id' => $edoTeacherId,
			'date_assign' => date('Y-m-d 23:59',time()),
        ));

        $collection = $this->getService('Student')->fetchAll(
            $this->quoteInto(array('MID = ?', ' AND CID = ?'), array($teacherId, $subjectId))
        );

        if (!count($collection)) {
            $this->getService('Student')->insert( // #11928
                array(
                    'MID' => $teacherId,
                    'CID' => $subjectId,
                    'Registered' => time(),
                    'time_registered' => $this->getService('Student')->getDateTime(),
                    'time_ended_planned' => $this->getService('Student')->getDateTime(strtotime('+5 year'))
                )
            );
        }
    }
}

    public function unassignTeacher($subjectId, $teacherId)
    {
        $teacher = $this->getOne(
            $this->getService('Teacher')->fetchAll(
                $this->quoteInto(
                    array('MID = ?', ' AND CID = ?'),
                    array($teacherId, $subjectId)
                )
            )
        );

        if (!$teacher) {
            return $this->getService('Teacher')->insert(
                array('MID' => $teacherId, 'CID' => $subjectId)
            );
        }
    }

	public function assignTutor($subjectId, $tutorId)
    {
        $subject = $this->getById($subjectId);
		if(!$subject){
			return false;
		}
		
		$assign  = $this->getService('Tutor')->getAssign($subjectId, $tutorId);
		
		if($assign){
			if($assign->date_debt != $subject->time_ended_debt || $assign->date_debt_2 != $subject->time_ended_debt_2){
				$assign->date_debt		= $subject->time_ended_debt   === NULL ? new Zend_Db_Expr('NULL') : $subject->time_ended_debt;
				$assign->date_debt_2	= $subject->time_ended_debt_2 === NULL ? new Zend_Db_Expr('NULL') : $subject->time_ended_debt_2;				
				$this->getService('Tutor')->update($assign->getValues());				
			}			
			return true;
		}
		
		$this->getService('Tutor')->insert(array(
            'MID' => $tutorId,
            'CID' => $subjectId,
			'date_assign' => date('Y-m-d 23:59',time()),
			'date_debt'   => $subject->time_ended_debt,
			'date_debt_2' => $subject->time_ended_debt_2,
		));
		return true;		
    }


    public function unassignTutor($subjectId, $tutorId)
    {
        return $this->getService('Tutor')->deleteBy(
            $this->quoteInto(
                array('MID = ?', ' AND CID = ?'),
                array($tutorId, $subjectId)
            )
        );
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('курс plural', '%s курс', $count), $count);
    }

    /**
	 *  Дата - время последнего обновления содержимого курса.
	 *  Что считаем обновлением содержимого:
	 *   - включение эл.курсов
	 *   - включение инф.ресурсов
	 *   - создание занятий в плане
	 *
	 *  Если дата в диапазоне [-бесконечность; 1год1месяц назад] - 0 баллов;
	 *  [1год1месяц назад - 1месяц назад] - XX балов пропорционально;
	 *  [1месяц назад - сейчас] - 100 баллов;
     */
    static public function calcFreshness($timestamp)
 	{
		if ($timestamp > ($ceil = time() - 2592000)) { // 1месяц назад
			return 100;
		} elseif ($timestamp > ($floor = time() - 31104000)) { // 1год1месяц назад
			return 0;
		} else {
			return 100* ($timestamp - $floor)/($ceil - $floor);
		}
 	}

 	/**
 	 * Возвращает массив типов регистрации с наименованиями
 	 * @return multitype:NULL
 	 */
 	public function getRegTypes()
 	{
 	    return HM_Subject_SubjectModel::getRegTypes();
 	}
	/**
 	 * Возвращает наименование типа регистрации
 	 * @return string
 	 */
 	public function getRegType($typeId)
 	{
 	    $arrTypes =  HM_Subject_SubjectModel::getRegTypes();
 	    if ( !array_key_exists($typeId, $arrTypes) ) {
 	        return '';
 	    }
 	    return $arrTypes[$typeId];
 	}

    public function copyClassifiers($fromSubjectId, $toSubjectId)
    {
        $classifiers = $this->getService('ClassifierLink')->fetchAll(
            $this->quoteInto(
                array('item_id = ?', ' AND type = ?'),
                array($fromSubjectId, HM_Classifier_Link_LinkModel::TYPE_SUBJECT)
            )
        );

        $this->getService('ClassifierLink')->deleteBy(
            $this->quoteInto(
                array('item_id = ?', ' AND type = ?'),
                array($toSubjectId, HM_Classifier_Link_LinkModel::TYPE_SUBJECT)
            )
        );

        if (count($classifiers)) {
            $this->linkClassifiers($toSubjectId, $classifiers->getList('classifier_id', 'classifier_id'));
        }
    }

    public function copyExercises($fromSubjectId, $toSubjectId)
    {
        $links = $this->getService('SubjectExercise')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        if (count($links)) {
            foreach($links as $link) {
                $link->subject_id = $toSubjectId;
                $this->getService('SubjectExercise')->insert(
                    $link->getValues()
                );
            }
        }
    }

    public function copyQuizzes($fromSubjectId, $toSubjectId)
    {

        $pollsLinks = array();

        $polls = $this->getService('Poll')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        $this->getService('Poll')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );
        $this->getService('SubjectPoll')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );        

        if (count($polls)) {
            foreach($polls as $poll) {
                $newPoll = $this->getService('Poll')->copy($poll, $toSubjectId);
                if ($newPoll) {
                    $pollsLinks[$poll->quiz_id] = $newPoll->quiz_id;
                    $this->_subjectCopyCache[HM_Event_EventModel::TYPE_POLL][$poll->quiz_id] = $newPoll->quiz_id;
                }
            }
        }

        $links = $this->getService('SubjectPoll')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        if (count($links)) {
            foreach($links as $link) {
                $link->subject_id = $toSubjectId;
                if (isset($pollsLinks[$link->quiz_id])) {
                    $link->quiz_id = $pollsLinks[$link->quiz_id];
                }

                $this->getService('SubjectPoll')->insert(
                    $link->getValues()
                );
            }
        }
    }

    public function copySections($fromSubjectId, $toSubjectId)
    {
        $sections = $this->getService('Section')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        $this->getService('Section')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );

        if (count($sections)) {
            foreach($sections as $section) {
                $newSection = $this->getService('Section')->copy($section, $toSubjectId);
                if ($newSection) {
                    $this->_subjectCopyCache['sections'][$section->section_id] = $newSection->section_id;
                }
            }
        }
    }

    public function copyResources($fromSubjectId, $toSubjectId)
    {
        $resourcesLinks = array();
        
        $this->getService('Resource')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );
        $this->getService('SubjectResource')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );

        $resources = $this->getService('Resource')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId),
        	'parent_id' //сначала получить корневые ресурсы, чтобы после копирования зависимого ресурса уже был известен новый id корня 
        );

        if (count($resources)) {
            foreach($resources as $resource) {
            	$newParentId = (int) 0;// В Oracle "false" может восприниматься как NULL, что критично для NOT NULL полей.
            	if ($resource->parent_id && isset($resourcesLinks[$resource->parent_id])) {
            		$newParentId = $resourcesLinks[$resource->parent_id];
            	}
                $newResource = $this->getService('Resource')->copy($resource, $toSubjectId, $newParentId);
                if ($newResource) {
                    $resourcesLinks[$resource->resource_id] = $newResource->resource_id;
                    $this->_subjectCopyCache[HM_Event_EventModel::TYPE_RESOURCE][$resource->resource_id] = $newResource->resource_id;
                }
            }
        }

        $links = $this->getService('SubjectResource')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        if (count($links)) {
            foreach($links as $link) {
                if (isset($resourcesLinks[$link->resource_id])) {
                    continue;
                }

                $link->subject_id = $toSubjectId;
                $this->getService('SubjectResource')->insert(
                    $link->getValues()
                );
            }
        }
    }

    public function copyTasks($fromSubjectId, $toSubjectId)
    {
        $tasksLinks = array();

        $tasks = $this->getService('Task')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        $this->getService('Task')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );
        $this->getService('SubjectTask')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );        

        if (count($tasks)) {
            foreach($tasks as $task) {
                $newTask = $this->getService('Task')->copy($task, $toSubjectId);
                if ($newTask) {
                    $tasksLinks[$task->task_id] = $newTask->task_id;
                    $this->_subjectCopyCache[HM_Event_EventModel::TYPE_TASK][$task->task_id] = $newTask->task_id;
                }
            }
        }

        $links = $this->getService('SubjectTask')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );          

        if (count($links)) {
            foreach($links as $link) {
                $link->subject_id = $toSubjectId;
                if (isset($tasksLinks[$link->task_id])) {
                    $link->task_id = $tasksLinks[$link->task_id];
                }

                $this->getService('SubjectTask')->insert(
                    $link->getValues()
                );
            }
        }
    }

    public function copyTests($fromSubjectId, $toSubjectId)
    {
        $testsLinks = array();

        $tests = $this->getService('TestAbstract')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        $this->getService('TestAbstract')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );
        $this->getService('SubjectTest')->deleteBy(
            $this->quoteInto('subject_id = ?', $toSubjectId)
        );        

        if (count($tests)) {
            foreach($tests as $test) {
                $newTest = $this->getService('TestAbstract')->copy($test, $toSubjectId);
                if ($newTest) {
                    $testsLinks[$test->test_id] = $newTest->test_id;
                    $this->_subjectCopyCache[HM_Event_EventModel::TYPE_TEST][$test->test_id] = $newTest->test_id;
                }
            }
        }

        $links = $this->getService('SubjectTest')->fetchAll(
            $this->quoteInto('subject_id = ?', $fromSubjectId)
        );

        if (count($links)) {
            foreach($links as $link) {
                $link->subject_id = $toSubjectId;
                if (isset($testsLinks[$link->test_id])) {
                    $link->test_id = $testsLinks[$link->test_id];
                }

                $this->getService('SubjectTest')->insert(
                    $link->getValues()
                );
            }
        }
    }


    public function copyLessons($fromSubjectId, $toSubjectId)
    {
        // копируем только то, что можно скопировать (отн.даты и без ограничений); здесь же псевдо-занятия для своб.доступа
        $lessons = $this->getService('Lesson')->fetchAll(
            $this->quoteInto(
                array('CID = ?', ' AND timetype IN (?)'),
                array($fromSubjectId, new Zend_Db_Expr(implode(',', array(
                    HM_Lesson_LessonModel::TIMETYPE_FREE,
                    HM_Lesson_LessonModel::TIMETYPE_RELATIVE,
                ))))
            )
        );
        $this->getService('Lesson')->deleteBy(
            $this->quoteInto('CID = ?',$toSubjectId)
        );
        
        if (count($lessons)) {
            $lessonsLink = array();
            foreach($lessons as $lesson) {

                $lessonID = $lesson->SHEID;
                unset($lesson->SHEID);
                //$lesson->teacher = 0; //#10586
                $lesson->CID = $toSubjectId;
                $lesson->section_id = isset($this->_subjectCopyCache['sections'][$lesson->section_id]) ? $this->_subjectCopyCache['sections'][$lesson->section_id] : null;

                // привязываем занятие к новым сущностям
                $params = $lesson->getParams();
                $type = ($lesson->typeID >= 0)? $lesson->typeID : $lesson->tool;
                if ( isset($params['module_id']) && isset($this->_subjectCopyCache[$type][$params['module_id']]) ) {
                    $params['module_id'] = $this->_subjectCopyCache[$type][$params['module_id']];
                    $lesson->setParams($params);
                }
				
				if (in_array($type, array(HM_Event_EventModel::TYPE_JOURNAL_LECTURE, HM_Event_EventModel::TYPE_JOURNAL_PRACTICE, HM_Event_EventModel::TYPE_JOURNAL_LAB))) {
					$lesson->max_ball = $this->getService('LessonJournal')->getDefaultMaxBall($toSubjectId);										 				
				}

                $newLesson = $this->getService('Lesson')->insert($lesson->getValues());
                $lessonsLink[$lessonID] = $newLesson->SHEID;
                //для опросов, тасков и тестов необходимо дублировать записи в таблице test
                if (in_array($type/*$newLesson->typeID*/,
                    array(
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER,
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT,
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER,
                        HM_Event_EventModel::TYPE_POLL,
                        HM_Event_EventModel::TYPE_TASK,
                        HM_Event_EventModel::TYPE_TEST))
                ) {

                    $test = $this->getOne($this->getService('Test')->fetchAll(
                        $this->getService('Test')->quoteInto(
                            array('lesson_id = ?'),
                            array($lessonID)
                        )
                    ));

                    if ($test) {
						//--Способ выборки вопросов в тесте источнике
						$theme = $this->getOne($this->getService('TestTheme')->fetchAll(
										$this->getService('TestTheme')->quoteInto(
												array('tid = ?', ' AND cid = ?'), array($test->tid, $test->cid)
										)
								));
						
						
                        $newType = ($newLesson->typeID >= 0)? $newLesson->typeID : $newLesson->tool;
                        if ($this->_subjectCopyCache[$newType][$test->test_id]) {
                            $test->test_id = $this->_subjectCopyCache[$newType][$test->test_id];
                        }
                        $test->lesson_id = $newLesson->SHEID;

                        //меням привязку по курсу(сессии)
                        $test->cid = $toSubjectId;
                        $test->cidowner = $toSubjectId;

                        $testVals = $test->getValues();
                        unset($testVals['tid']);
                        $newTest = $this->getService('Test')->insert($testVals);
                        if ($newTest) {
							
							if($theme){
								$themeNewTest = $this->getOne($this->getService('TestTheme')->fetchAll(
														$this->getService('TestTheme')->quoteInto(
																array('tid = ?', ' AND cid = ?'), array($newTest->tid, $newTest->cid)
														)
												));
								if(!$themeNewTest){
									//--Способ выборки вопросов из теста источника дублируем в новый тест, если еще нет настроек для него
									$this->getService('TestTheme')->insert(
											array(
												'tid' => $newTest->tid,
												'cid' => $newTest->cid,
												'questions' => $theme->questions
											)
									);
								}
							}
							
                            $params['module_id'] = $newTest->tid;
                            $newLesson->setParams($params);
                            $this->getService('Lesson')->update($newLesson->getValues());
                        }
                    }
                }
            }

            // обновление связей между новыми занятиями (например, условие выполнения)
            $newLessons = $this->getService('Lesson')->fetchAll(array('CID=?' => $toSubjectId));
            if ( count($newLessons) && count($lessonsLink) ) {
                foreach($newLessons as $newLesson) {
                    if ($newLesson->cond_sheid && isset($lessonsLink[$newLesson->cond_sheid]) ) {
                        $newLesson->cond_sheid = $lessonsLink[$newLesson->cond_sheid];
                        $this->getService('Lesson')->update($newLesson->getValues());
                    }
                }
            }
        }	
    }

    public function copyElements($oldId, $newId)
    {
        $this->copySections($oldId, $newId);

        $this->_subjectCopyCache[HM_Event_EventModel::TYPE_COURSE] = $this->getService('SubjectCourse')->copy($oldId, $newId);
        $this->copyClassifiers($oldId, $newId);
        //$this->copyExercises($subjectId, $newSubject->subid);
        $this->copyQuizzes($oldId, $newId);
        $this->copyResources($oldId, $newId, $sections);
        $this->copyTasks($oldId, $newId);
        $this->copyTests($oldId, $newId);
        $this->copyLessons($oldId, $newId);
        $this->createJournalLessons($newId); # только для неДОТ		
    }

    public function copy($subjectId)
    {
        if ($subjectId) {
            $subject = $this->getOne($this->find($subjectId));
            if ($subject) {
                $subject->name = sprintf(_('%s (Копия)'), $subject->name);
                // #16795
                $subject->base = HM_Subject_SubjectModel::BASETYPE_PRACTICE;
                $subject->external_id = '';
                unset($subject->subid);


                $values = $subject->getValues();

                if($values['end'] !=''){
                    list($date, $time) = explode(' ', $values['end']);
                    $values['end'] = $date;
                }

                $newSubject = $this->insert($values);

                if ($values['default_uri'] !=''){
                    $values['default_uri'] = str_replace("subject_id/{$subjectId}", "subject_id/{$newSubject->subid}", $values['default_uri']);
                    $values['subid'] = $newSubject->subid;
                    $this->update($values);
                }
                

                if ($newSubject) {
                    $this->copyElements($subjectId, $newSubject->subid);
                }

                $teachers = $this->getService('Teacher')->fetchAll(
                    $this->quoteInto('CID = ?', $subjectId)
                );

                if (count($teachers)) {
                    foreach($teachers as $teacher) {
                        $this->assignTeacher($newSubject->subid, $teacher->MID);
                    }
                }
/*                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
                    $this->assignTeacher($newSubject->subid, $this->getService('User')->getCurrentUserId());
                }*/

                return $newSubject;
            }
        }

        return false;
    }

    public function getSessions($subjectId)
    {
        $sessions = array();
        if ($subject = $this->find($subjectId)->current()) {
            if ($subject->base == HM_Subject_SubjectModel::BASETYPE_BASE) {
                $sessions = $this->fetchAll(array('base_id = ?' => $subjectId));
            }
        }
        return $sessions;
    }

    /**
     * Возвращает список занятий на оценку пользователя по курсу
     * результат кешируется
     * @param $subjectID
     * @param $userID
     * @return mixed
     */
    public function getUserVedomostLessons($subjectID,$userID)
    {
        if (!isset($this->_userLessonsCache[$subjectID][$userID])) {
            $this->_userLessonsCache[$subjectID][$userID] = $this->getService('LessonAssign')
                ->fetchAllDependenceJoinInner(
                'Lesson',
                $this->getService('Lesson')->quoteInto(array('Lesson.CID  = ?', ' AND Lesson.vedomost = ?', ' AND self.MID = ?'), array($subjectID, 1, $userID))
            );
        }
        return $this->_userLessonsCache[$subjectID][$userID];
    }

    /**
     * Возвращает среднюю оценку прохождения пользователем занятий курса
     * @param HM_Subject_SubjectModel | int $subject - модель курса или ИД
     * @param $userID - ИД пользователя
     * @return int
     */
    public function getUserMeanScore($subject,$userID)
    {
        $subjectID = ($subject instanceof HM_Model_Abstract)? $subject->subid : (int) $subject;
        $lessons = $this->getUserVedomostLessons($subjectID,$userID);
        $amount = count($lessons);
        $total = 0;
        foreach($lessons as $lesson){
            if($lesson->V_STATUS != -1){
                $total+= $lesson->V_STATUS;
            }
        }
        if ($amount) {
            $total = (ceil($total / $amount) <= 100) ? ceil($total / $amount) : 100;
        } else {
            $total = 0;
        }
        return $total;
    }

    /**
     * Возвращает процент прохождения пользователем курса
     * @param HM_Subject_SubjectModel | int $subject - модель курса или ИД
     * @param $userID - ИД пользователя
     * @return int
     */
    public function getUserProgress($subject,$userID)
    {
        $subjectID = ($subject instanceof HM_Model_Abstract)? $subject->subid : (int) $subject;
        $scoreLessonsTotal = $this->getService('Lesson')
            ->countAllDependenceJoinInner(
            'Assign',
            $this->getService('Lesson')
                 ->quoteInto(array('CID = ? AND vedomost = 1 ', ' AND MID = ?', ' AND isfree = ?'), array($subjectID, $userID, HM_Lesson_LessonModel::MODE_PLAN))
        );

        $scoreLessonsScored = $this->getService('Lesson')
            ->countAllDependenceJoinInner(
            'Assign',
            $this->getService('Lesson')
                 ->quoteInto(array('CID = ? AND vedomost = 1 ', ' AND MID = ? AND V_STATUS > -1', ' AND isfree = ?'), array($subjectID, $userID, HM_Lesson_LessonModel::MODE_PLAN))
        );

        return ($scoreLessonsTotal)? floor(($scoreLessonsScored / $scoreLessonsTotal) * 100) : 0;
    }

    /**
     * Функция возвращает TRUE в случае, если все занятия пользователя $userID по курсу $subject исмеют статус "выполнено"
     * @param HM_Subject_SubjectModel | int $subject - модель курса или ИД
     * @param $userID - ИД пользователя
     * @return bool
     */
    public function isAllLessonsDone($subject,$userID)
    {
        $subjectID = ($subject instanceof HM_Model_Abstract)? $subject->subid : (int) $subject;
        $lessons = $this->getUserVedomostLessons($subjectID,$userID);
        $finish = TRUE;
        foreach($lessons as $lesson){
            if ( $lesson->V_DONE != HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_DONE ) {
                $finish = FALSE;
            }
        }
        return $finish;
    }

    /**
     * Функция генерирует цвет в шестнадцатеричном представлении для нового курса
     * @return string
     */
    public function generateColor()
    {
        #if ($this->_subjectsColorsCache === null) {
        #    $this->_subjectsColorsCache = $this->fetchAll()->getList('subid','base_color'); # memory limit exceeded
        #}
        $this->loadSubjectColorCache();

        $colorsUsed    = array_unique($this->_subjectsColorsCache);
        $subjectsCount =  count($colorsUsed) + 1;         // для случая если курс создается
        $stepCount     = ceil(pow($subjectsCount,1/3));   // количество шагов дробления
        $skipColors    = array('000000','ffffff');        // какие цвета исключить

        if ($stepCount > 255) { $stepCount = 255;}        // на всякий случай

        for($currStep = 1; $currStep <= $stepCount; $currStep++) {
            $step = (int) 255/$currStep;
            for($color_r = 0 ;$color_r <= 255; $color_r += $step) {
                for($color_g = 0 ;$color_g <= 255; $color_g += $step) {
                    for($color_b = 0 ;$color_b <= 255; $color_b += $step) {
                        $color = sprintf("%02x%02x%02x",$color_r,$color_g,$color_b);
                        if (!in_array($color, $colorsUsed) && !in_array($color,$skipColors)) {
                            return $color;
                        }
                    }
                }
            }
        }
    }

    public function getSubjectColor($subid)
    {
        #if ($this->_subjectsColorsCache === null) {
        #    $this->_subjectsColorsCache = $this->fetchAll()->getList('subid','base_color'); # memory limit exceeded
        #}

        $this->loadSubjectColorCache();

        if ($subid && array_key_exists($subid,$this->_subjectsColorsCache)) {
            return $this->_subjectsColorsCache[$subid];
        }

        return '';
    }

    public function loadSubjectColorCache()
    {
        if($this->_subjectsColorsCache !== null){
            return false;
        }
        $select = $this->getSelect();            
        $select->from('subjects', array('subid', 'base_color'));
        $res = $select->query()->fetchAll();
        foreach($res as $item){
            $this->_subjectsColorsCache[$item['subid']] = $item['base_color'];
        }
        return true;
    }

    public function getCalendarSource($source, $defaultColor = '0000ff', $inText = false, $forUsers = null)
    {
        if (!$source instanceof HM_Collection) return '';

        $events        = array();
        $eventsSources = array();

        $forUsers = (array) $forUsers;
        
        foreach ( $source as $event ) {
            if (!$event || !$event->begin || !$event->end) continue;

            $start   = new HM_Date($event->begin);
            $end     = new HM_Date($event->end);
            $data = array(
                'id'    => $event->subid,
                'title' => $event->name,
                'start' => ($inText)? $start->toString("YYYY-MM-dd") : $start->getTimestamp(),
                'end'   => ($inText)? $end->toString("YYYY-MM-dd") : $end->getTimestamp(),
                'color' => "#{$event->base_color}",
                'textColor' => (lum($event->base_color) < 130) ? '#fff' : '#000'
            );

            if (count($event->teachers)) {
                $teacher = $event->teachers->current();

                if (count($forUsers) && !in_array($teacher->MID, $forUsers)) {
                    continue;
                }
                $teachers[] = $teacher->getName();

                array_unique($teachers);
                $data['title'] .= ' ' . _('Преподаватели') . ': ' . implode(', ', $teachers);
                unset($teachers);
            } elseif (count($forUsers)) {
                continue;
            }

            $events[] = $data;
        }

        return $events;
    }

	// ВНИМАНИЕ! создает сессию с ручным стартом
    public function createSession($baseId, $appentTitle = false)
    {
        if (!$appentTitle) $appentTitle = _('сессия');
        if ($base = $this->getOne($this->find($baseId))) {
            if ($base->base != HM_Subject_SubjectModel::BASETYPE_SESSION) {

                if ($base->base == HM_Subject_SubjectModel::BASETYPE_PRACTICE ) {
                    $changes = array(
                        'base'      => HM_Subject_SubjectModel::BASETYPE_BASE,
                        'period'    => HM_Subject_SubjectModel::PERIOD_FREE,
                        'claimant_process_id' => array_shift(HM_Subject_SubjectModel::getTrainingProcessIds()),
                    );
                    $this->getService('Subject')->updateWhere($changes, array('subid = ?' => $baseId));
                    $this->getService('Subject')->unlinkRooms($baseId);
                }

                $data = $base->getValues();
                $data['name'] = sprintf(_('%s (%s)'), $base->name, $appentTitle);
                $data['base'] = HM_Subject_SubjectModel::BASETYPE_SESSION;
                $data['begin_planned'] = date('Y-m-d');
                $data['end_planned'] = date('Y-m-d') . ' 23:59:59';
                $data['period'] = HM_Subject_SubjectModel::PERIOD_DATES;
                $data['period_restriction_type'] = HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL;
                $data['base_id'] = $baseId;
                unset($data['subid']);
                $session = $this->insert($data);

                try {
                    $this->getService('Subject')->copyElements($baseId, $session->subid);
                } catch (HM_Exception $e) {
                    // что-то не скопировалось..(
                }
                return $session;
            }
        }
        return false;
    }


    public function setDefaultUri($uri, $subjectId)
    {
        $this->updateWhere(array('default_uri' => urldecode($uri)), array('subid = ?' => $subjectId));
    }

    public function getDefaultUri($subjectId)
    {
        $subject = $this->find($subjectId)->current();
        if ($subject && !empty($subject->default_uri) && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {

                // dirty hack
                $uri = str_replace(array(
                    'lesson/list/index',
                ), array(
                    'lesson/list/my',
                ), $subject->default_uri);

                if($subjectId) //#17522
                    $uri = preg_replace("/(.*?)\/(subject_id)\/(\d+)(\/(.*?))*/", "\\1/\\2/{$subjectId}\\4", $uri);

                return $uri;

        } else {
            $view = Zend_Registry::get('view');
            return $view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subjectId));
        }
    }

    public function hasOnlyFreeLessons($subjectId)
    {
        $lessons = $this->getService('Lesson')->fetchAll(array(
            'CID = ?' => $subjectId,
            'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN
        ));
        return !count($lessons);
    }

    public function getIcal(HM_Subject_SubjectModel $subject, $studentId = 0)
    {
        // create and set icalendar object
        $calendar = new HM_Ical_Calendar();
        $calendar->addTimezone(HM_Ical_Timezone::fromTimezoneId(Zend_Registry::get('config')->timezone->default));
        $calendar->properties()->add(new HM_Ical_Property('METHOD', HM_Ical_Property_Value_Text::fromString('REQUEST')));

        $event = new HM_Ical_Event();
        $event->properties()->add(new HM_Ical_Property('UID', HM_Ical_Property_Value_Text::fromString(md5('subject_'.$subject->subid.time()))));
        $event->properties()->add(new HM_Ical_Property('SUMMARY', HM_Ical_Property_Value_Text::fromString($subject->name)));
        $event->properties()->add(new HM_Ical_Property('ORGANIZER', HM_Ical_Property_Value_Text::fromString('MAILTO:'.$this->getService('Option')->getOption('dekanEMail'))));

        //$event->properties()->add(new HM_Ical_Property('LOCATION', HM_Ical_Property_Value_Text::fromString('')));
        //$event->properties()->add(new HM_Ical_Property('SEQUENCE', HM_Ical_Property_Value_Text::fromString('0')));
        //$event->properties()->add(new HM_Ical_Property('TRANSP', HM_Ical_Property_Value_Text::fromString('OPAQUE')));
        //$event->properties()->add(new HM_Ical_Property('CLASS', HM_Ical_Property_Value_Text::fromString('PUBLIC')));

        if ($subject->begin) {
            $start = new HM_Date($subject->begin);
        } elseif ($subject->begin_planned) {
            $start = new HM_Date($subject->begin_planned);
        }

        if ($subject->end) {
            $end = new HM_Date($subject->end);
        } elseif ($subject->end_planned) {
            $end = new HM_Date($subject->end_planned);
        }

        if ($studentId) {
            $student = $this->getOne(
                $this->getService('Student')->fetchAll(
                    $this->quoteInto(
                        array('MID = ?', ' AND CID = ?'),
                        array($studentId, $subject->subid)
                    )
                )
            );

            if ($student) {
                if ($student->time_registered) {
                    $start = new HM_Date($student->time_registered);
                }

                if ($student->time_ended_planned) {
                    $end = new HM_Date($student->time_ended_planned);
                }
            }
        }

        $start->setHour(0)->setMinute(0)->setSecond(0);
        $end->setHour(23)->setMinute(59)->setSecond(59);

        $event->properties()->add(new HM_Ical_Property('DTSTART', HM_Ical_Property_Value_DateTime::fromString($start->toString('YYYYMMddTHHmmss'))));
        $event->properties()->add(new HM_Ical_Property('DTEND', HM_Ical_Property_Value_DateTime::fromString($end->toString('YYYYMMddTHHmmss'))));

        $now = new HM_Date();
        $event->properties()->add(new HM_Ical_Property('DTSTAMP', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMddTHHmmss'))));
        $event->properties()->add(new HM_Ical_Property('CREATED', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMddTHHmmss'))));
        $event->properties()->add(new HM_Ical_Property('LAST-MODIFIED', HM_Ical_Property_Value_DateTime::fromString($now->toString('YYYYMMddTHHmmss'))));
        $description = $subject->name;

        $collection = $this->getTeachers($subject->subid);

        $teachers = array();
        if (count($collection)) {
            foreach($collection as $item) {
                $teachers[$item->MID] = $item->getName();
            }
        }

        if (count($teachers)) {
            $description .= ', '.sprintf(_('Преподаватели: %s'), join(', ', $teachers));
        }

        $event->properties()->add(new HM_Ical_Property('DESCRIPTION', HM_Ical_Property_Value_Text::fromString($description)));

        $calendar->addEvent($event);
        return $calendar;
    }

    public function getTeachers($subjectId)
    {
        return $this->getService('User')->fetchAllJoinInner('Teacher', $this->quoteInto('Teacher.CID = ?', $subjectId));
    }

    public function getById($id)
    {
        #$cache      = Zend_Registry::get('cache');
		#$cache_name = self::CACHE_NAME . '__' . __FUNCTION__;
		#$lifetime   = 60; # сек. - время жизни
		
		#$items      = $cache->load($cache_name);
		
		#$item       = $items[$id];
		#$subject    = $item['items'];
		#$expired    = $item['expired'];
		
		#if((int)$expired < time()){
		#	$subject = false;
		#}
		
		#if(!$subject){				
			$subject               = $this->getOne($this->fetchAll($this->quoteInto('subid = ?', $id)));
		#	$items[$id]['items']   = $subject;
		#	$items[$id]['expired'] = time() + $lifetime;
						
		#	$cache->save($items, $cache_name);			
		#}
		return $subject;	
		
		
		
    }


    public function getMaxBallSum($subjectId) {
        if($subjectId){
            $select = $this->getSelect();
            //$select->from(array('l' => 'lessons'), array(
            $select->from(array('l' => 'schedule'), array( //--на тестовом не подцепился псевдоним таблицы. Поэтому пишем реальное имя.
                    'max_ball_sum' => 'SUM(l.max_ball)',
                )
            );				
			
            $select->where('CID = ?', $subjectId);
            $select->where('typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()));
            $select->where('isfree = ?', HM_Lesson_LessonModel::MODE_PLAN);
            $select->where('required = 1');
            $maxBallSum = $select->query()->fetchAll();
            $maxBallSum = $maxBallSum[0]['max_ball_sum'];
        }
        return $maxBallSum;
    }
	
	/**
	 * Проверяет на доступность к курсу (сессии) если она продлена
	 * return bool
	 * используется при инициализации курса в момент захода студента
	*/
	public function isActiveDebt($subjectId, $userId = false){

		$curTime = date('Y-m-d 00:00:00');
		
		if(!$userId){
			$userId = $this->getService('User')->getCurrentUserId();
		}
		$select = $this->getSelect();
		$select->from(array('st' => 'Students'), array(
				'time_ended_debtor' => 'st.time_ended_debtor',
			)
		);
		$select->where('CID = ?', $subjectId);
		$select->where('MID = ?', $userId);
		
		$select->where(
			$this->quoteInto(array('(    (time_ended_debtor IS NOT NULL AND (time_ended_debtor > ?', ' OR time_ended_debtor = ?))',
									' OR (time_ended_debtor_2 IS NOT NULL', ' AND (time_ended_debtor_2 > ?', ' OR time_ended_debtor_2 = ?))		)'),
							array($curTime, $curTime, $curTime, $curTime, $curTime))
		);
		
		#$select->where('time_ended_debtor IS NOT NULL');
		#$select->where('time_ended_debtor > ? OR time_ended_debtor = ?', $curTime, $curTime);
		$row = $select->query()->fetchAll();
		
		if(count($row) > 0){
			return true;			
		}		
		return false;				
	}
	
		
	public function sendTutorAssignMessage($personId, $courseId)
    {
        
		$this->_serviceSubject = $this->getService('Subject');
		$subject = $this->getOne($this->_serviceSubject->find($courseId));
		if(!$subject){
			$this->_cacheSubjectTitle[$courseId] = false;
			return;
		}
		
		$templateId = 25; //--Шаблон для тьюторов в БД
		$courseName = $subject->getName();
		$this->_template = $this->getOne($this->getService('Notice')->fetchAll($this->getService('Notice')->quoteInto('type = ?', $templateId)));
		
		if($this->_template->enabled == 1) {
			$messageTitle = str_replace('[COURSE]', $courseName, $this->_template->title);
			$messageText = str_replace('[COURSE]', $courseName, $this->_template->message);	
			
			$this->_person = $this->getOne($this->getService('User')->find($personId));
			
			$toName = $this->_person->LastName.' '.$this->_person->FirstName.' '.$this->_person->Patronymic;
			$toEmail = $this->_person->EMail;
			
			$this->sendEmail($toEmail, $toName, $messageTitle, $messageText);
			
		}	
    }
	
	public function sendEmail($toEmail, $toName, $messageTitle = '', $messageText) {		
		
		if(!$toEmail || !$toName || !$messageText){
			return false;
		}
		
		$validator = new Zend_Validate_EmailAddress();
		
        if (strlen($toEmail) && $validator->isValid($toEmail)) {
            $mail = new Zend_Mail(Zend_Registry::get('config')->charset);
            
			$mail->addTo($toEmail, $toName);
            
			$mail->setSubject($messageTitle);
			$mail->setType(Zend_Mime::MULTIPART_RELATED);
			$mail->setFromToDefaultFrom();
			$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);			
			try {
				$mail->send();
				            
				return true;
            } catch (Zend_Mail_Exception $e) {		
                return false;
            }
		}			
		return false;		
	}
	
	/**
	 * список групп, на которые назначены студенты, назначенные на сессию.
	*/
	public function getUsersGroupsById($subject_id, $isGraduated = false){
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
	  
		if(!$subject_id){ return false; }
		
		if($isGraduated){
			$students = $this->getService('Graduated')->fetchAll($this->quoteInto('CID = ?', $subject_id))->getList('MID');
		} else {
			$students = $this->getService('Student')->fetchAll($this->quoteInto('CID = ?', $subject_id))->getList('MID');
		}
		
		if(!count($students)) { return false; }
		
		$groupIDs = $this->getService('StudyGroupCustom')->fetchAll($this->quoteInto('user_id IN (?)', $students))->getList('group_id');
		if(!count($groupIDs)) { return false; }
		If($lng == 'eng' && $this->getService('StudyGroup')->fetchAll($this->quoteInto('group_id IN (?)', $groupIDs))->getList('group_id', 'name_translation')!='')
		{$groupList = $this->getService('StudyGroup')->fetchAll($this->quoteInto('group_id IN (?)', $groupIDs))->getList('group_id', 'name_translation');}
		else{
		$groupList = $this->getService('StudyGroup')->fetchAll($this->quoteInto('group_id IN (?)', $groupIDs))->getList('group_id', 'name');}
		if(!count($groupList)) { return false; }
		return $groupList;
	}
	
	
	/**
	 * Получаем кол-во студентов в уроке, которые требуют внимкания тьютора (последнее сообщение в диалоге от студента)
	 * Берем только те занятия, на которые назначен тьютор. Если не назначен ни на один, значит доступны все.
	*/
	public function getNewActionStudent($subject_id, $studentIDs = false){
		$lessons = $this->getService('Lesson')->fetchAll($this->quoteInto(array('CID = ?', ' AND typeID = ?'), array($subject_id, HM_Event_EventModel::TYPE_TASK)))->getList('SHEID');
		if(count($lessons)){
			$subSelect = $this->getSelect();
			
			#$assign_lessons = $this->getService('LessonAssignTutor')->getAssignSubject($this->getService('User')->getCurrentUserId(), $subject_id)->getList('LID');
			#if(!empty($assign_lessons)){				
			#	$subSelect->where($this->quoteInto('lesson_id IN (?)', $assign_lessons));				
			#}
						
			$subSelect->from(
				'interview',
				array(				
					'interview_hash' => 'interview_hash',							
					'max_interview_id' => 'MAX(interview_id)',							
				)
			);	
			$subSelect->group('interview_hash');
			$subSelect->where($this->quoteInto('lesson_id IN (?)', $lessons));
			$subSelect->where('interview_hash > 0');
			
			$select = $this->getSelect();
			$select->from(
				array('i' => 'interview'),
				array(				
					'interview_id' 	=> 'i.interview_id',												
					'lesson_id' 	=> 'i.lesson_id',												
				)
			);	
			$select->join(array('st' => 'Students'), 'st.MID = i.user_id', array()); //--исключаем возможность отбора сообщений удаленных с сессии студентов
			$select->join(array('sub' => $subSelect), 'sub.interview_hash = i.interview_hash AND sub.max_interview_id = i.interview_id', array());
			
            # исключаем заблокированных студентов
            $select->join(array('p' => 'People'), 'st.MID = p.MID', array()); 
            $select->where('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED);
			
			$select->where($this->quoteInto('i.type IN (?)', array(HM_Interview_InterviewModel::MESSAGE_TYPE_QUESTION, HM_Interview_InterviewModel::MESSAGE_TYPE_TEST)));
			$select->where('st.CID = ?', $subject_id);

            $midWithHalfAccess = $this->getService('UserInfo')->getMidWithHalfAccess();
            if(!empty($midWithHalfAccess)){
                $select->where($this->quoteInto('st.MID NOT IN (?)', $midWithHalfAccess));
            }
			
			if($studentIDs !== false){
				if(is_array($studentIDs) && count($studentIDs)){
					$select->where($this->quoteInto('st.MID IN (?)', $studentIDs));
				} else {
					$select->where('1=0');
				}
			}
			
			$res = $select->query()->fetchAll();
			
			$data = array();
			if(count($res)){
				foreach($res as $i){
					$data[$i['lesson_id']]++;
				}
			}			
			return $data;			
		}
		return array();		
	}
	
	/**
	 * Новое действие со стороны студента, требующее внимания преподавателя.
	 *
	*/
	public function isNewActionStudent($subject_id, $studentIDs = false){
		if(count($this->getNewActionStudent($subject_id, $studentIDs))){
			return true;
		}
		return false;	
	}
	
	/**
	 * проверяет завдвоение сессий по внешнему id сессий.
	 * return array of external ids
	*/
	public function getMultipleIDSubjects(){
		$select = $this->getSelect();
		$select->from('subjects', array('external_id') );	
		$select->group('external_id');
		$select->where('external_id IS NOT NULL');
		$select->where($this->quoteInto('external_id != ?', ''));
		$select->having($this->quoteInto('COUNT(external_id) > ?', 1));
		$res = $select->query()->fetchAll();
		$multipleIDs = array();
		if(count($res)){
			foreach($res as $i){
				$multipleIDs[$i['external_id']] = $i['external_id'];
			}
		}
		return $multipleIDs;
	}
	
	/**
	 * кол-во назначенных на сессию студентов. Без учета тех, кто завершил обучение
	*/
	public function getStudentCount($subject_id){	
		$collection = $this->getAssignedUsers($subject_id);
		return count($collection->getList('MID'));        
	}
	
	/**
	 * определяем, нужно ли назначить студента на сессию, которая является иностранным языком.
	 * @ return bool
	*/
	public function isNeedAssign($user_id, $language_code){		
		if(empty($user_id) || empty($language_code)){ return false; }
		
		if(!$this->userService){ $this->userService =  $this->getService('User'); };
			
		$user = $this->userService->getById($user_id);
		if(!$user || empty($user->mid_external)){ return false; }
		
		$select = $this->getSelect();
		$select->from('Students_language', array('LID') );					
		$select->where($this->quoteInto(array('mid_external = ?', ' AND language_id = ?'), array($user->mid_external, $language_code)));
		$res = $select->query()->fetchObject();
		if(isset($res->LID)){ return true; }
		return false;
	}
	
	
	/**
	 * получает студентов, которые доаступны указанному тьютору.
	 * назначения на группу и отдельных студентов складываются
	 * @return array or false. Массив, если назначен. false - если не назначен и доступны все студенты сессии
	*/
	# Тут возможно, косяк: попадают студенты, которые назначены на группу в StudyGroupUsers, но не назначены на указанную сессию. Проверить этот момент.
	public function getAvailableStudents($tutor_id, $subject_id){
		if(!$tutor_id || !$subject_id){ return false; }
		
		#$cache_field 			= '_availableStudents';
		#$cache_field_lifetime	= $cache_field.'_lifetime';
		#$key					= $subject_id.'~'.$tutor_id;
		
		#if(empty($this->{$cache_field})){ 
		#	$this->restoreFromCacheByName($cache_field);
		#}
		
		#if($this->{$cache_field_lifetime} <= time() ){ # очищаем кэш, старше CACHE_LIFETIME
		#	$this->clearCacheByName($cache_field);  	
		#	$this->{$cache_field_lifetime} = time() + self::CACHE_LIFETIME;
		#}
		
		#if(isset($this->{$cache_field}[$key])){
		#	return $this->{$cache_field}[$key];			
		#} 
		
		$groups 	= $this->getService('SubjectGroup')->getGroupIds($subject_id, $tutor_id);
		$students 	= $this->getService('SubjectUser')->getStudentIds($subject_id, $tutor_id);
		
		# доступны все студенты сессии
		if(!$groups && !$students){ 			
			#$this->{$cache_field}[$key] = false;
			#$this->saveToCacheByName($cache_field);					
			#return $this->{$cache_field}[$key]; 
			return false;
		}
		
		$groupStudents = array();		
		if(count($groups)){			
			$groupStudents = $this->getService('StudyGroupUsers')->fetchAll($this->quoteInto('group_id  IN (?)', $groups))->getList('user_id');			
		}		
		$studentIDs = array();
		if(count($students)){
			foreach($students as $student_id){ $studentIDs[$student_id] = $student_id; }
		}
		
		if(count($groupStudents)){
			foreach($groupStudents as $student_id){ $studentIDs[$student_id] = $student_id; }
		}
		
		#$this->{$cache_field}[$key] = array_filter($studentIDs);
		#$this->saveToCacheByName($cache_field);
		
		#return $this->{$cache_field}[$key];
        $midWithHalfAccess = $this->getService('UserInfo')->getMidWithHalfAccess();
        if(!empty($midWithHalfAccess)){
            foreach($studentIDs as $mid => $i){
                if(in_array($mid, $midWithHalfAccess)){
                    unset($studentIDs[$mid]);
                }
            }
        }

		return array_filter($studentIDs);		
	}
	
	
	/**
	 * Студенты, которые обучаются и доступны тьютору
	*/
	public function getAvailableStudentsAssign($tutor_id, $subject_id){
		if(empty($tutor_id) || empty($subject_id)){ return array(); }
		
		$assigns 			= $this->getAssignedUsers($subject_id)->getList('MID');
		if(empty($assigns)){ return array(); }
		
		# нужно отсеять недоступных текущему тьютору
		$availableStudents = $this->getAvailableStudents($tutor_id, $subject_id);
			
		# доступны все
		if($availableStudents === false){
			return $assigns;
		}
		
		return array_intersect_key($assigns, $availableStudents);		
	}
	
	/**
	 * оставляем только те группы, на которых назнаены студенты, доступные тьютору.
	 * возвращаются только те группы, которые есть и там и там.
	 * @return array
	*/
	public function filterGroupsByAssignStudents($subject_id, $tutor_id, $users_groups){
		if(!$subject_id || !$tutor_id){ return $users_groups; }
		if(empty($users_groups)) { return $users_groups; }
		
		$students = $this->getAvailableStudents($tutor_id, $subject_id);		
		if($students === false) { return $users_groups; }
		if(empty($students))	{ return array(); }			
		$groups = $this->getService('StudyGroup')->getGroupListOnUserIDs($students);
		
		if(!$groups){ return array(); }
		
		return array_intersect_key($users_groups,$groups );
	}
	
	
	/**
	 * Возвращает тьюторов, которым доступен указанный студент
	 * Этот метод только для студентов. Он неприемлем для тьюторов.
	**/
	public function filterAvailableTutors($tutors, $user_id, $subject_id){
		if(count($tutors) < 1){ return array(); }
		
		# проверяем кэш тьютора вида [id сессии] - [id студента] - [список тьюторов.key=>mid, value=>fio]
		#if(empty($this->_userTutors)){ $this->restoreFromCache(); }
		
		#if($this->_cacheCreateted <= time() ){ # очищаем кэш, старше 2 часов.
		#	$this->clearCache(); 
		#	$this->_cacheCreateted = time() + (60*60*2);
		#}
		
		# Временно отключено, на время тестов. 111111111111
		#if(isset($this->_userTutors[$subject_id][$user_id])){ return $this->_userTutors[$subject_id][$user_id]; }		
		
		# тьюторы, которым доступна данная сессия по признаку продления.
		$available_debtor_tutors = $this->getAvailableDebtorTutors($subject_id); 		
			
		$res = array();		
		foreach($tutors as $t){
			if(!isset($available_debtor_tutors[$t->MID])){ continue; }
				
			$availableStudents = $this->getAvailableStudents($t->MID, $subject_id);				
			if($availableStudents === false || in_array($user_id, $availableStudents)){
				$res[$t->MID] = $t->getName();
			}
		}
		#$this->_userTutors[$subject_id][$user_id] = $res;
		#$this->saveToCache();				
		return $res;			
	}
	/*
	public function getModularCode($subject_id){
		if(!$subject_id){ return false; }
		$subject = $this->getById($subject_id);
		if(empty($subject->module_code)){ return false; }
		return $subject->module_code;
	}
	*/
	public function getModuleData($subject_id, $user_IDs = false){
		if(!$subject_id){ return false; }
		
		#if(empty($this->_moduleData[$subject_id])){ $this->restoreFromCache(); }
		
		#if($this->_cacheCreatetedLight <= time() ){ # очищаем кэш, старше 1 минут
		#	$this->clearCache(); 
		#	$this->_cacheCreatetedLight = time() + (60*1);
		#}		
		#if(!empty($this->_moduleData[$subject_id])){  return $this->_moduleData[$subject_id]; }		
		
		$subject = $this->getById($subject_id);
		
		if(empty($subject->module_code)){ return false; }
		
		# по каждому расчитать интегральную оценку и итоговый текущий рейтинг
		$modules 	= $this->getModuleSubjects($subject->module_code, $subject->semester);

		if(empty($user_IDs)){
			$students 	= $this->getService('Student')->fetchAll($this->quoteInto(array('CID IN (?) ', ' AND MID > ?'), array($modules->getList('subid'), 0)));
		} else {
			$students 	= $this->getService('Student')->fetchAll($this->quoteInto(array('CID IN (?) ', ' AND MID IN (?)'), array($modules->getList('subid'), $user_IDs)));
		}
		
		if(!count($students)){ return false; }
		$data 				= array();
		$lessonCollections 	= array(); 		
		
		foreach($modules as $subject){
			$data['subjects'][$subject->subid] 		= $subject->name;			
			
			$data['additional'][$subject->subid] = array(
				'isDO' 					=> $subject->isDO,
				'maxBallMediumRating' 	=> $this->getService('Lesson')->getMaxBallMediumRating($subject->subid),
			);			
		}
		
		
		
		foreach($students as $s){			
			if(!isset($data['integrate'][$s->MID]['medium'])){
				$data['integrate'][$s->MID]['medium'] = $this->getIntegrateMediumRating($subject->module_code, $s->MID, $subject->semester);
			}
			
			if(!isset($lessonCollections[$s->CID])){
				#$lessonCollections[$s->CID] = $this->getService('Lesson')->getActiveLessonsOnSubjectIdCollection($s->CID);
				$lessonCollections[$s->CID] = $this->getActiveLessons($s->CID);
			}
			
			if(!isset($data['rating'][$s->CID]['medium'][$s->MID])){												
				$mediumRating 								= $this->getMediumRating($s->MID, $lessonCollections[$s->CID]);
				$data['rating'][$s->CID]['medium'][$s->MID] = $mediumRating;
				
				if(!isset($data['isPassAllModule'][$s->MID])) {
					$data['isPassAllModule'][$s->MID] = true;
				}
				
				# доходит до первого фэйла.
				if($data['isPassAllModule'][$s->MID] === true){		
					######
					$isPassModule 							= $this->isPassModule($s->CID, $s->MID, $mediumRating, $lessonCollections[$s->CID]);
					$data['isPassAllModule'][$s->MID] 		= $isPassModule;
					
					# Пока проверка по непрохождению доп. подуля идет до первого непройденного модуля. Если надо получить инфу по всем модулям, то вытащить из цикла и првоерят по всем.
					$data['additional'][$s->CID]['is_fail_module'][$s->MID] 	= !$isPassModule;
				}				
			}
		}
		
		#$this->_moduleData[$subject_id] = $data;		
		#$this->saveToCache();
		return $data;
	}
	
	/**
	 * итоговый балл за сессию для ДО
	*/
	public function getUserBallDot($subject_id, $student_id){
		if($this->getService('Lesson')->issetJournalPractic($subject_id)){
			$divide_balls	= $this->getUserBallDivide($subject_id, $student_id);			
			$total			= ($divide_balls['mark_current'] + $divide_balls['mark_landmark']);
			if($total > 100){ return 100; }
			return $total;			
		}
		
		$collection				= $this->getActiveLessons($subject_id);
		$lessons_ids 	 		= $collection->getList('SHEID');
		$assigns 		 		= $this->getService('LessonAssign')->getByLessons($student_id, $lessons_ids);
			
				
		foreach ($assigns as $i) {
			if ($i->V_STATUS == HM_Scale_Value_ValueModel::VALUE_NA) {
				continue;
			}
			$total += $i->V_STATUS;
		}
		return $total;
	}
	
	/**
	 * Справедливо для неДО.
	 * находим балл за сессию с учетом Итоговый текущий рейтинг (или Интегральный текущий рейтинг)  и Рубежный рейтинг
	*/
	public function getUserBalls($subject_id, $student_id){
		$user_balls = $this->getUserBallsSeparately($subject_id, $student_id);		
		return $user_balls['total'] + $user_balls['medium'];		
	}
	
	/**
	 * Возвращает баллы по Итоговый текущий рейтинг (или Интегральный текущий рейтинг)  и Рубежный рейтинг раздельно + доп признак главного модуля.
	*/
	public function getUserBallsSeparately($subject_id, $student_id){
		
		$data 					= array('total' => 0, 'medium' => 0);
		$user_balls 			= $this->getService('LessonJournalResult')->getRatingSeparated($subject_id, $student_id);
		
		
		$data['total'] 			= round($user_balls['total']);
		$data['medium'] 		= round($user_balls['medium']);
		$data['isMainModule'] 	= false;
		if(!$this->isMainModule($subject_id)){
			return $data;
		}
		$data['isMainModule'] = true;
		$subject 				= $this->getById($subject_id);							
		$integrateMediumRating	= $this->getIntegrateMediumRating($subject->module_code, $student_id, $subject->semester);
		$data['medium'] 		= round($integrateMediumRating);
		return $data;
	}
	
	/**
	 * получаем балл по всем занятиям сессии по академической активности (Итоговый текущий рейтинг ?)
	*/
	/*
	public function getAcademicBall($subject_id){
		if(!$subject_id){ return false; }
		$t = $this->getService('Lesson')->getUsersScore($subject_id);
		$dataRatingMedium = $t[3];
		$dataRatingTotal = $t[4];
		pr($dataRatingMedium);
		pr($dataRatingTotal);
		# получить все занятия сессии, что выводятся в плане занятий.
		#$collection = $this->getService('Lesson')->getActiveLessonsOnSubjectIdCollection($subject_id);
		#if(!$collection){ return false; }
		#foreach($collection as $lesson){
			#$this->getService('Lesson')-
		#}
		
		#pr($collection);
		return 0;
		# по каждому занятию определить рейтинг.
	}
	*/
	/**
	 * получаем балл по всем занятиям сессии по практическим занятиям (Рубежный рейтинг ?)
	*/
	/*
	public function getPracticBall($subject_id){
		# получить все занятия сессии, что выводятся в плане занятий.
		# по каждому занятию определить рейтинг.
	}
	*/
	
	/**
	 * Для модульной дисциплины определяет главный модуль.
	 * TODO уточнить условие определения главного модуля и доработать формулу.
	*/
	public function isMainModule($subject_id){
		if(!$subject_id){ return false; }
		$subject = $this->getById($subject_id);
		if(empty($subject->module_code)){ return false; }
		
		#$lessons = $this->getService('Lesson')->getActiveLessonsOnSubjectIdCollection($subject_id);
		$lessons = $this->getActiveLessons($subject_id);
		if(!$lessons){ return false; }
		foreach($lessons as $i){
			if((stristr($i->title, 'Итоговый тест') !== FALSE) || (stristr($i->title, 'Итоговый контроль') !== FALSE)){
				return true;
			}	
		}
		return false; 
	}
	

	public function getTutotList($subject_id){
		$tutorCollection = $this->getAssignedTutors($subject_id);
		if(empty($tutorCollection)) { return false; }
		
		$tutorRoles = $this->getService('Tutor')->fetchAll($this->quoteInto('CID = ?', $subject_id))->getList('MID', 'roles');				
		$tutorList	= array();
		foreach($tutorCollection as $i){
			$role 				= (isset($tutorRoles[$i->MID])) ? (' ('.HM_Lesson_Assign_Tutor_TutorModel::getRolesName($tutorRoles[$i->MID]).')') : ('');
			$tutorList[$i->MID] = $i->LastName.' '.$i->FirstName.' '.$i->Patronymic.$role;
		}
		return $tutorList;		
	}
	
	/**
	 * создание занятий с типом "журнал" для сессий неДОТ
	**/
	public function createJournalLessons($subject_id){
		$subject = $this->getById($subject_id);
		if(!empty($subject->isDO)){ return false; }
		$this->getService('LessonJournal')->createJournals($subject);		
	}
	
	
	/**
	 * 
	*/
	/*
	public function isDOT($subject_id){
		$subject = $this->getById($subject_id);		
		if(!empty($subject->isDO)){ return true; }
		return false;
	}
	*/
	
	/**
	 * доступна ли сессия для студента
	*/
	public function isAvailableSubject($subject_id, $user_id){
		if(!$subject_id || !$user_id){ return false; }
		
		$subject = $this->getService('Subject')->getById($subject_id);
		if(!$subject){ return false; }
		
		if($subject->period == HM_Subject_SubjectModel::PERIOD_FREE){ return true; } # Время обучения не ограничено
		
		$timestamp_current 	= strtotime(date('Y-m-d'));
		$timestamp_end 		= strtotime($subject->end);
		
		# вероятно, задан 1900 год, что эквивалентно - доступ без ограничений.
		if(empty($timestamp_end)) { return true; }

		if($timestamp_end > time() ) { return true; }
		
		$timestamp_debt_1 = strtotime($subject->time_ended_debt);
		$timestamp_debt_2 = strtotime($subject->time_ended_debt_2);
		
		# сессия не продлена.
		if(empty($timestamp_debt_1) && empty($timestamp_debt_2)){
			return ($timestamp_end < $timestamp_current) ? false : true; 
		}			
		
		$assign = $this->getOne($this->getService('Student')->fetchAll($this->quoteInto(array('MID = ?', ' AND CID = ?'), array($user_id, $subject_id))));
		
		# студент не назначен => и доступа нет
		if(!$assign->SID){ return false; } 
		
		$timestamp_user_debt_1 = strtotime($assign->time_ended_debtor); 
		$timestamp_user_debt_2 = strtotime($assign->time_ended_debtor_2);
		
		# сессия продлена, а студент нет.
		if(empty($timestamp_user_debt_1) && empty($timestamp_user_debt_2)) { return false; }
		
		# есть только первое продление
		if(empty($timestamp_user_debt_2)){
			return ($timestamp_user_debt_1 < $timestamp_current) ? false : true; 
		} 
		
		# есть второе продление
		return ($timestamp_user_debt_2 < $timestamp_current) ? false : true;		
	}
	
	/**

	 * @return bool
	 * Явлчяется ли сессия текущей или будущей.
	 * 
	*/
	public function isPresent($subject){
		if($subject->period == HM_Subject_SubjectModel::PERIOD_FREE){ return true; } # Время обучения не ограничено
		
		$timestamp_end = strtotime($subject->end);
		
		# вероятно, задан 1900 год, что эквивалентно - доступ без ограничений.
		if(empty($timestamp_end))	{ return true; }		
		if($timestamp_end >= time()) { return true; }
		return false;
	}
	/*	
	 * список иностарнных языков.
	 * В последующем разделение на додгруппы будутет не только по ин.язу, но и по другим дисциплинам.
	*/
	public function getSubGroupList(){
		$select = $this->getSelect();
		$select->from('Students_language_list', array('code', 'name') );
		$select->group(array('code', 'name'));
		$res	= $select->query()->fetchAll();
		$data	= array();
		if(!empty($res)){
			foreach($res as $i){
				$data[$i['code']] = trim($i['name']);
			}
		}
		asort($data);
		return $data;
	}
	


	public function getModuleSubjects($module_code, $semester = false)
	{
		$semester 	= (int)$semester;
		$key 		= $module_code . '~' . $semester;
		if(isset($this->_moduleSubjects[$key])){ return $this->_moduleSubjects[$key]; }
		if($semester){
			$conditions = $this->quoteInto(array('module_code = ?', ' AND semester = ?'), array($module_code, $semester));
		} else {
			$conditions = $this->quoteInto('module_code = ?', $module_code);
		}
		$this->_moduleSubjects[$key] = $this->fetchAll($conditions);		
		return $this->_moduleSubjects[$key];
	}
	
	public function getActiveLessons($subject_id){
		if(isset($this->_lessonCollections[$subject_id])){ return $this->_lessonCollections[$subject_id]; }
		$this->_lessonCollections[$subject_id] = $this->getService('Lesson')->getActiveLessonsOnSubjectIdCollection($subject_id);			
		return $this->_lessonCollections[$subject_id];
	}
	
	
	
	# - если модуль не пройден, то он считается как 0 баллов.
	public function getIntegrateMediumRating($module_code, $user_id, $semester = false){
		
		if(!$module_code || !$user_id){ return false; }
		
		$subjectCollection 	= $this->getModuleSubjects($module_code, $semester);
		$zetList 			= $subjectCollection->getList('subid', 'zet');
		
		if(empty($subjectCollection)){ return false; }
		
		/**/
		$subject_ids = $subjectCollection->getList('subid');
		$serviceJR   = $this->getService('LessonJournalResult');
		$mediumRating = -1; # неявка
		$totalZet	  = array_sum($zetList);
		
		#$lessonCollections = array();
		
		foreach($subject_ids as $subject_id){
			# если не пройден модуль, то $mediumRating = 0;
			
			$single_mediumRating	= $this->getMediumRating($user_id, $this->getActiveLessons($subject_id));
			$isPassModule 	  		= $this->isPassModule($subject_id, $user_id, $single_mediumRating, $this->getActiveLessons($subject_id));
			$rating['medium'] 		= 0;
			
			if($isPassModule){
				$rating = $serviceJR->getRatingSeparated($subject_id, $user_id);	
			}
			#######
			
			if($mediumRating == -1){ $mediumRating = 0; }
			$mediumRating += $rating['medium'] * intval($zetList[$subject_id]);	
		}		
		return round($mediumRating / $totalZet, 2);
		/**/
		/*
		$select = $this->getSelect();
		$select->from(array('l' => 'schedule'), array(
			'subject_id'	=> 'l.CID',			
			'lesson_id'		=> 'l.SHEID',
			'title'			=> 'l.title',
			'typeID'		=> 'l.typeID',
			'mark'			=> 'sh.V_STATUS',
		));						
		$select->join(array('sh' => 'scheduleID'), 'sh.SHEID = l.SHEID', array());
		
		$select->where($this->quoteInto('sh.MID = ?', $user_id));		
		$select->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()));
		$select->where('l.CID IN (?)', $subjectCollection->getList('subid'));
		$select->where('sh.V_STATUS >= ?', 0);
		
		$select->group(array('l.CID', 'l.SHEID', 'sh.V_STATUS', 'l.title', 'l.typeID'));
		
		$res = $select->query()->fetchAll();
		if(empty($res)){ return false; }
		$mediumRating = -1; # неявка
		$totalZet	  = array_sum($zetList);
		foreach($res as $i){			
			$lesson = new HM_Lesson_Custom_CustomModel(array('typeID' => $i['typeID'], 'title' => $i['title']));			
			if(!$this->getService('Lesson')->isMediumRating($lesson)){ continue; }
			if($mediumRating == -1){ $mediumRating = 0; }
			$mediumRating += ($i['mark'] * intval($zetList[$i['subject_id']]));			
		}
		return round($mediumRating / $totalZet, 2);
		*/
	}
	
	/**
	 * @return HM Subject Model collection
	*/
	public function getModuleIntegrateMark($module_code){
		
	}
	
	# Минимальное число рубежных контролей, которое нужно пройти для допуска к экзамену
	public function getMinimumLandmarkControlPass($subject_id){
		$subject = $this->getById($subject_id);	
		return ceil( 0.65 * intval($subject->zet) );
	}
	
	/**	
	 * Возвращает массив кодов.	 
	 * isTotalRating  - Итоговый текущий рейтинг,
	 * isMediumRating - Рубежный рейтинг
	 * Теперь минимальное кол-во пройденных рубежных контролей считается не по ЗЕТ, а по фактическому кол-ву в сессии.
	*/
	public function getFailPassCode($student_id, $subject_id){
		#$minimum_landmark_control_pass = $this->getMinimumLandmarkControlPass($subject_id);
		$minimum_landmark_control_pass = 0;
		$landmark_control_pass 		   = 0; # кол-во пройденных рубежных контролей
		
		#echo $minimum_landmark_control_pass;
		if(!$student_id || !$subject_id){ return false; }
		if($this->isDOT($subject_id))	{ return true;  }
		$serviceLesson	= $this->getService('Lesson');
		$serviceAssign	= $this->getService('LessonAssign');
        
		$subject = $this->getById($subject_id);
		if(empty($subject)){ return false; }
		
		
		#$collection 	= $serviceLesson->getActiveLessonsOnSubjectIdCollection($subject_id);
        $collection 	= $this->getActiveLessons($subject_id);
		 
        if(!$collection){ return false; }
		
		$reasons = array(); # Список кодов, из-за которых студент не допущен к экзамену. Для студента выводим первую причину. Для тьютора в ведомости берем все.
        
		#if($student_id == 58435  && $subject_id == 13831){ #&& $lesson->SHEID == 93267
				#	echo '.<div style="display:none">';
				#	#pr($collection);
				#	echo '</div>';
				#}
		$maxBallTotalRating = $serviceLesson->getMaxBallTotalRating($subject_id);
				
		$summ_balls = 0;
		foreach($collection as $item){
			
			
			if($item->required != 1){ continue; }
			
			
			$userResult	= $serviceAssign->fetchAll($this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array($item->SHEID, $student_id)))->current();	
			
			if($subject->isPractice()){
				
				# Если за итоговый контроль (Рубежный рейтинг) больше 20 баллов, то это старый вариант практик, для которых оценки определяется по формуле ДО
				if($maxBallTotalRating > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){
					if($userResult->V_STATUS < 0){ continue; }
					$summ_balls += $userResult->V_STATUS;
					continue;
				}
			}
			
			
			# рубежный контроль в т.ч и тест
			#if( (stristr($item->title, 'рубежный контрол') !== FALSE) || (stristr($item->title, 'рубежный тест') !== FALSE) ){ 
			if( (stristr($item->title, 'рубежный контрол') !== FALSE) || (stristr($item->title, 'тест ') !== FALSE) ){ 
				$minimum_landmark_control_pass++;
				if(
					$userResult->V_STATUS <= 0
					||
					!$serviceLesson->isPassMediumRating($item->max_ball, $userResult->V_STATUS)
				){ 					
					$reasons[HM_Subject_SubjectModel::FAIL_PASS_TOTAL_RAITING][$item->SHEID] = $item->SHEID; #HM_Subject_SubjectModel::FAIL_PASS_TOTAL_RAITING;
					
					
				} else {
					$landmark_control_pass++;
				}
				
			}			
            /*
			# Рубежный рейтинг ()
			if($serviceLesson->isMediumRating($item)){
				$userResult	= $serviceAssign->fetchAll($this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array($item->SHEID, $student_id)))->current();	
				
				# Не сдан один из рубежных контролей на положительную оценку 
				if(
					$userResult->V_STATUS <= 0
					||
					!$serviceLesson->isPassMediumRating($item->max_ball, $userResult->V_STATUS)
				){ 					
					$reasons[HM_Subject_SubjectModel::FAIL_PASS_TOTAL_RAITING] = HM_Subject_SubjectModel::FAIL_PASS_TOTAL_RAITING;					
				} 			
			
			} 
			*/
			
			# итоговое практ задание (ИПЗ)
			elseif($serviceLesson->isTotalPractic($item)){			
				#$userResult	= $serviceAssign->fetchAll($this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array($item->SHEID, $student_id)))->current();
				if($userResult->V_STATUS <= 0){					
					$reasons[HM_Subject_SubjectModel::FAIL_PASS_TOTAL_PRACTIC][$item->SHEID] = $item->SHEID; #HM_Subject_SubjectModel::FAIL_PASS_TOTAL_PRACTIC;	
				}				
			}						
		}	

		if($subject->isPractice()){
			# Если за итоговый контроль (Рубежный рейтинг) больше 20 баллов, то это старый вариант практик, для которых оценки определяется по формуле ДО
			if($maxBallTotalRating > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){
				return $reasons;
			}
		}	
		
		
		$max_rating_medium = $serviceLesson->getMaxBallMediumRating($subject_id, $collection);	
		$max_rating_medium = ($max_rating_medium > 80) ? 80 : $max_rating_medium;
		
		# сдал рубежных контролей менее 65% от общего кол-ва рубежных контролей
        $part = empty($minimum_landmark_control_pass) ? 1 : ($landmark_control_pass/$minimum_landmark_control_pass);
		
		#if($landmark_control_pass < $minimum_landmark_control_pass){
		if($part < 0.65){
			$reasons[HM_Subject_SubjectModel::FAIL_PASS_MIN_LANDMARK_COUNT] = true;

			
			
		} else {
			#unset($reasons[HM_Subject_SubjectModel::FAIL_PASS_TOTAL_RAITING]);
		}
		unset($reasons[HM_Subject_SubjectModel::FAIL_PASS_TOTAL_RAITING]);
		

		# если нет Итоговый текущий рейтинг, то не считаем.
		if($max_rating_medium > 0){			
			#$rating_medium 	   = $this->getMediumRating($student_id, $collection);	
			$rating = $this->getService('LessonJournalResult')->getRatingSeparated($subject_id, $student_id);
			$rating_medium = $rating['medium'];
			
			
			if(!$serviceLesson->isPassMediumRating($max_rating_medium, $rating_medium, false)){
				$reasons[HM_Subject_SubjectModel::FAIL_PASS_MIDDLE] = true; #HM_Subject_SubjectModel::FAIL_PASS_MIDDLE;
			}
		}
		ksort($reasons); # влияет на приоритет вывода сообщения для студента - первая причина из массива
		
		return $reasons;
		
			
		/*	
		foreach($collection as $item){		
				
            if($serviceLesson->isMediumRating($item)){
				
				
				$userResult	= $serviceAssign->fetchAll($this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array($item->SHEID, $student_id)))->current();				
				
				if($userResult->V_STATUS <= 0){ return HM_Subject_SubjectModel::FAIL_PASS_TOTAL_RAITING; }
				
				#if(!$serviceLesson->isPassTotalRating($item->max_ball, $userResult->V_STATUS, true)){
				if(!$serviceLesson->isPassMediumRating($item->max_ball, $userResult->V_STATUS, true)){
					return HM_Subject_SubjectModel::FAIL_PASS_TOTAL_RAITING;
				}
			
			} elseif($serviceLesson->isTotalPractic($item)){ # итоговое практ задание
				$userResult	= $serviceAssign->fetchAll($this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array($item->SHEID, $student_id)))->current();
				
				if($userResult->V_STATUS <= 0){ return HM_Subject_SubjectModel::FAIL_PASS_TOTAL_PRACTIC; }				
			} 
		}
		
		$rating_medium = $this->getMediumRating($student_id, $collection);		
		#$rating_total = $this->getTotalRating($student_id, $collection);
				
				
		if(!$serviceLesson->isPassMediumRating($serviceLesson->getMaxBallMediumRating($subject_id, $collection), $rating_medium, false)){
		#if(!$serviceLesson->isPassTotalRating($serviceLesson->getMaxBallTotalRating($subject_id), $rating_total, false)){
			return HM_Subject_SubjectModel::FAIL_PASS_MIDDLE;
		}
		*/
		
	}
	
	/**
	 * Набран ли минимум для допуска к экзамену
	 * @return string
	*/
	public function getFailPassMessage($student_id, $subject_id, $isTutor = false){
		$codes       = $this->getFailPassCode($student_id, $subject_id);
		if(empty($codes) || !is_array($codes)){ return false; }
        $reasons     = ($isTutor === true) ? HM_Subject_SubjectModel::getFailPassMessageListForTutor() : HM_Subject_SubjectModel::getFailPassMessageList();
		$data = array();		
		foreach($codes as $cod => $lessons){
			$data[$cod]['message'] = $reasons[$cod];
			if(is_array($lessons)){
				$data[$cod]['lessons'] = $lessons;
			}
		}		
        return $data;
	}
	
	/**
	 * Находим причины недопуска по всем модулям, кроме указанного в параметрах (кроме главного)
	*/
	public function getFailPassMessageModule($student_id, $subject_id, $isTutor = false){
		$subject 		= $this->getById($subject_id);
		$modules 		= $this->getModuleSubjects($subject->module_code, $subject->semester);
		$subject_IDs 	= $modules->getList('subid');
		$subject_Names 	= $modules->getList('subid', 'name');
		
		unset($subject_IDs[$subject_id]);
		if(empty($subject_IDs)){ return false; }
		
		$data = array();
		foreach($subject_IDs as $module_subject_id){
			$reasons = $this->getFailPassMessage($student_id, $module_subject_id, $isTutor);
			if(empty($reasons)){ continue; }
			$data[$module_subject_id]['reasons']	= $reasons;
			$data[$module_subject_id]['name']		= $subject_Names[$module_subject_id];			
		}
		return $data;
		
		#######
		#$subject->module_code
	}


	/**
	 * Получаем "Итоговый текущий рейтинг"
	 * Скорее всего это "Рубежный рейтинг"
	 * @param_1 int
	 * @param_2 HM Lesson Model collection
	*/
	public function getMediumRating($user_id, $lessons){	
		
		$lids = array();
		$serviceLesson 	= $this->getService('Lesson');		
		foreach($lessons as $lesson){
			if(!$serviceLesson->isMediumRating($lesson)){ continue; }
			$lids[$lesson->SHEID] = $lesson->SHEID;
		}
		if(empty($lids)){ return false; }

		$collection 	= $this->getService('LessonAssign')->fetchAll(            
            $this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array($lids, $user_id))         
        );
		if(!count($collection)){ return false; }
		$mediumRating = -1;
		foreach($collection as $i){
			if($i->V_STATUS < 0)	{ continue; }
			if($mediumRating == -1)	{ $mediumRating = 0; }
			
			$mediumRating += $i->V_STATUS;			
		}
		return $serviceLesson->correctMediumBall($mediumRating);
	}
	
	/**
	 * Получаем "Итоговый текущий рейтинг"
	 * @param_1 int
	 * @param_2 HM Lesson Model collection
	*/
	public function getTotalRating($user_id, $lessons){	
		
		$lids = array();
		$serviceLesson 	= $this->getService('Lesson');		
		foreach($lessons as $lesson){
			if(!$serviceLesson->isTotalRating($lesson)){ continue; }
			$lids[$lesson->SHEID] = $lesson->SHEID;
		}
		if(empty($lids)){ return false; }

		$collection 	= $this->getService('LessonAssign')->fetchAll(            
            $this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array($lids, $user_id))         
        );
		if(!count($collection)){ return false; }
		$totalRating = -1;
		foreach($collection as $i){
			if($i->V_STATUS < 0)	{ continue; }
			if($totalRating == -1)	{ $totalRating = 0; }
			
			$totalRating += $i->V_STATUS;			
		}
		return $totalRating;
	}
	
	
	/**
	 * Прошел ли порог сдачи модульной сессии по каждому из занятий "Рубежный рейтинг" и 
	 * Прошел ли порог сдачи модульной сессии по "Итоговый текущий рейтинг" 
	 * Прошел ли "рубежный контроль" или "рубежный тест" в 65%
	**/
	public function isPassModule($subject_id, $student_id, $medium_rating, $lessons){
		$serviceLesson = $this->getService('Lesson');
		$serviceAssign = $this->getService('LessonAssign');
		
		$subject = $this->getById($subject_id);
		
		$minimum_landmark_control_pass	= 0;
		$landmark_control_pass			= 0; # кол-во пройденных рубежных контролей
		
		# Наблал ли минимум в "Рубежном рейтинге"
		foreach($lessons as $lesson){
			if($serviceLesson->isTotalRating($lesson)){
				continue; # не нужно учитывать баллы за экзамен при проходлении/не прохождении модуля
				$userResult	= $serviceAssign->fetchAll($this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array($lesson->SHEID, $student_id)))->current();
				if($userResult->V_STATUS <= 0){ return false; }
				
				$isPassTotalRating = $serviceLesson->isPassTotalRating($lesson->max_ball, $userResult->V_STATUS, $this->isDOT($subject_id), $subject->isPractice());
				if(!$isPassTotalRating){ return false; }				
			} elseif($serviceLesson->isBoundaryControl($lesson->title)){
				$userResult	= $serviceAssign->fetchAll($this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array($lesson->SHEID, $student_id)))->current();
				$minimum_landmark_control_pass++;
				
				if($serviceLesson->isPassLesson($lesson, $userResult->V_STATUS)){
					$landmark_control_pass++;
				}
				/*
				# не пройден рубежный контроль
				if(!$serviceLesson->isPassLesson($lesson, $userResult->V_STATUS)){
					#if($student_id == 5829){
						#pr($lesson);
						#var_dump($userResult);
					#}					
					return false;
				}							
				*/
			}
		}
		
		# сдал рубежных контролей менее 65% от общего кол-ва рубежных контролей
        $part = empty($minimum_landmark_control_pass) ? 1 : ($landmark_control_pass/$minimum_landmark_control_pass);
		if($part < 0.65){
			return false;
		}
		
		$maxBall = $serviceLesson->getMaxBallMediumRating($subject_id, $lessons);
		$maxBall = $serviceLesson->correctMediumBall($maxBall);		
		return (round($medium_rating) >= ($maxBall * HM_Lesson_LessonModel::PASS_MODULE_PERCENT)) ? true : false;		
	}
	
	
	public function isDOT($subject_id){		
		if(isset($this->_dotList[$subject_id])){ return $this->_dotList[$subject_id]; }
		$this->_dotList[$subject_id] = empty($this->getById($subject_id)->isDO) ? false : true;		
		return $this->_dotList[$subject_id];
	}
	
	
	/*
	 * получаем список всех кафедр сессий
	*/
	public function getChairList(){
		if(empty($this->_chairs)){ $this->restoreFromCache(); }
		
		if($this->_cacheCreateted <= time() ){ # очищаем кэш, старше 1 часа (60*60).
			$this->clearCache(); 
			$this->_cacheCreateted = time() + (60*60);
		}
		
		if(!empty($this->_chairs)){ return $this->_chairs; }
		
		$select = $this->getSelect();          
        $select->from('subjects', array('chair'));							
        $select->where("chair != '' OR chair IS NOT NULL");
		$select->group('chair');		
        $chairs = $select->query()->fetchAll();
		$data 	= array();
		foreach($chairs as $i){
			if(empty($i['chair'])){ continue; }
			$data[$i['chair']] = $i['chair'];		
		}
		$this->_chairs = $data;
		$this->saveToCache();
		return $data;
	}

	
	/**
	 * получаем список всех факультеотв сессий
	*/
	public function getFacultyList(){
		if(empty($this->_faculty)){ $this->restoreFromCache(); }
		
		if($this->_cacheCreateted <= time() ){ # очищаем кэш, старше 1 часа (60*60).
			$this->clearCache(); 
			$this->_cacheCreateted = time() + (60*60);
		}
		
		if(!empty($this->_faculty)){ return $this->_faculty; }
		
		$select = $this->getSelect();          
        $select->from('subjects', array('faculty'));							
        $select->where("faculty != '' OR faculty IS NOT NULL");
		$select->group('faculty');		
        $chairs = $select->query()->fetchAll();
		$data 	= array();
		foreach($chairs as $i){
			if(empty($i['faculty'])){ continue; }
			$data[$i['faculty']] = $i['faculty'];		
		}
		$this->_faculty = $data;
		$this->saveToCache();
		return $data;
	}
	
	/**
	 * Проверяет, все ли назнаенные студенты имеют итоговый балл за сессию.
	*/
	public function isAllStudentsHasMark($subject_id){
		$select = $this->getSelect();
		
		
		$select->from(
			array('st' => 'Students'),
			array(								
				'not_marks'	=> new Zend_Db_Expr('COUNT(cm.mark)')
			)
		);	
		$select->joinLeft(array('cm' => 'courses_marks'), 'cm.mid = st.MID AND cm.cid = st.CID', array());					
		$select->where($this->quoteInto('st.CID = ?', $subject_id));
		$select->where('cm.mark IS NULL');
		#return $select->assemble();
		
		$res = $select->query()->fetchObject();
		if(empty($res->not_marks)){
			return true;
		}
		return false;
	}
	
	
	
	
	/**
	 * получаем список тьюторов, которым доступна сессия по продлению
	 * без учета назначений студентов и доступности отдельных занятий
	 
	 * Новая локика такая:
	 * Если нет тьюторов 2-го продления, она доступна тьюторам 1-продления
	 * Если нет тьютора 1-го продления, она доступна тьюторам 2-го продления
	 * Если нет тьюторов ни 1-го, ни 2-го продления, сессия доатспна основным тьюторам. 
	 # 10.04.2019  СУ00-3442 - исключаем из проверки заблокированные записи
	*/
	public function getAvailableDebtorTutors($subject_id){
		$subject 	=	$this->getById($subject_id);
		if(!$subject){ return array(); }
		
		# подучаем список тьюторов с разделением на группы продления. В т.ч из кэша, если есть
		if(empty($this->_debtTutors)){ $this->restoreFromCache(); }		
		
		if($this->_cacheCreateted <= time() ){ # очищаем кэш, старше 2 часов.
			$this->clearCache(); 
			$this->_cacheCreateted = time() + (60*60*2);			
		}
		
		# Временно отключено, на время тестов. 111111111111
		if(isset($this->_debtTutors[$subject_id])){			
			$tutors = $this->_debtTutors[$subject_id];
		} else {			
			$tAssigns 	= $this->getService('Tutor')->fetchAll(	$this->quoteInto('CID = ?', $subject_id)	);
			
			
			$ids 		 = $tAssigns->getList('MID');
			if(!empty($ids)){
				$activeUsers = $this->getService('User')->fetchAll(	$this->quoteInto(array('blocked != ? ', ' AND MID IN (?)'), array(HM_User_UserModel::STATUS_BLOCKED, $ids)	))->getList('MID');
			}
			
			
			$tutors 	= array(
				'base' 		=> array(),
				'first' 	=> array(),
				'second' 	=> array(),
			);
			
			foreach($tAssigns as $i){
				# исключаем заблокированных
				if(!isset($activeUsers[$i->MID])){ continue; }
				
				$tutors['base'][$i->MID] = $i->MID;
				
				if(!empty($i->date_debt)){
					$tutors['first'][$i->MID] = $i->MID;
				}
				
				if(!empty($i->date_debt_2)){
					$tutors['second'][$i->MID] = $i->MID;
				}
			}			
			$this->_debtTutors[$subject_id] = $tutors;
			$this->saveToCache();	
		}	

				
		
		# нет продлений. Доступны все тьюторы
		if(	empty($subject->time_ended_debt) && empty($subject->time_ended_debt_2)	){
			return $tutors['base'];			
		
		# Первое продление. Если нет тьюторов нужного продления, даем доступ тьюторам других продлений.
		} elseif(	empty($subject->time_ended_debt_2)	){
			if(!empty($tutors['first'])){
				return $tutors['first'];
			}
			
			if(!empty($tutors['second'])){
				return $tutors['second'];
			}
			
			return $tutors['base'];				
		}
		
		# Если дошли до этого момента - то это 100% второе продление
		if(!empty($tutors['second'])){
			return $tutors['second'];
		}
		
		if(!empty($tutors['first'])){
			return $tutors['first'];
		}
		
		return $tutors['base'];	
		
		/*
		$criteria 	= '';
		
		if(!empty($subject->time_ended_debt_2)){
			$criteria .= ' AND date_debt_2 IS NOT NULL ';			
		
		# Если есть второе продление, то первое уже не важно.
		} elseif(!empty($subject->time_ended_debt)){
			$criteria .= ' AND date_debt IS NOT NULL ';			
		}
		
		$turorAssigns	= $this->getService('Tutor')->fetchAll(		$this->quoteInto('CID = ? '.$criteria, $subject_id)		)->getList('MID');
		if(empty($turorAssigns)){ return array(); }
		return $turorAssigns;		
		*/
	}
	
	/**
	 *
	*/
	public function getByCode($external_id)
    {
        return $this->getOne($this->fetchAll($this->quoteInto('external_id = ?', $external_id)));
    }
	
	/**
	 * получаем итоговый текущий и рубежный рейтинг
	 * TODO сделать кэшь на список занятий и сессию
	 * @return array 
	 * без округления!!
	*/	
	public function getUserBallDivide($subject_id, $student_id){
		if(empty($subject_id) || empty($student_id)){ return false; }
		$_serviceLesson	= $this->getService('Lesson');		
		$collection 	= $this->getActiveLessons($subject_id);
		if(empty($collection)){ return false; }
		
		$lessons_ids = $collection->getList('SHEID');		
		if(empty($lessons_ids)){ return false; }
		
		$lessons = array();
		foreach($collection as $c){ $lessons[$c->SHEID] = $c; }
		
		$assigns = $this->getService('LessonAssign')->getByLessons($student_id, $lessons_ids);
		if(empty($assigns)){ return false; }
		
		$dataRatingTotal 	= 0;
		$taskRating 		= 0; # баллы за задания + журнал-практическая часть.
		$academRating 		= 0; # баллы за академическую активность только в журнале
		$dataRatingMedium 	= 0;
		$issetIPZ 			= false; # Есть ли ИПЗ
		$taskMax            = 0;
		
		foreach($assigns as $i){
			
			if($issetIPZ === false){
				# Если это ИПЗ
				if($_serviceLesson->isTotalPractic($lessons[$i->SHEID]->title)){
					$issetIPZ = true;
				}
			}
			
			$title 	= $lessons[$i->SHEID]->title;
			$typeID = $lessons[$i->SHEID]->typeID;

			if($typeID == HM_Event_EventModel::TYPE_TASK && (stristr($title, 'задание') !== FALSE)){ 
				$taskMax    += $lessons[$i->SHEID]->max_ball;
			}			
			
			if($i->V_STATUS <= 0) { continue; }
			
			
			if($typeID == HM_Event_EventModel::TYPE_JOURNAL_LECTURE || $typeID == HM_Event_EventModel::TYPE_JOURNAL_LAB){ 
				if ($lessons[$i->SHEID]->max_ball == HM_Lesson_Journal_JournalModel::MAX_BALL_AA_WITHOUT_PZ_AND_LAB) { $type_journal_AA_maxball = MAX_BALL_AA_WITHOUT_PZ_AND_LAB; }
				$academRating += $i->ball_academic;
			
			# журнал - практическое занятие				
			} elseif($typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){ 
				$taskRating 	+= $i->ball_practic;							
				$academRating 	+= $i->ball_academic; #  Итоговый текущий рейтинг		
			
			# Рубежный рейтинг											
			} elseif( (stristr($title, 'Итоговый тест') !== false) || (stristr($title, 'Итоговый контроль') !== false) ){ 
				$dataRatingTotal += $i->V_STATUS;
			
			# на поощрения не должно накладываться ограниение в максимальный балл.
			} elseif($typeID == HM_Event_EventModel::TYPE_TASK && (stristr($title, 'поощрени') !== FALSE)){ 
				$dataRatingMedium += $i->V_STATUS;
			
			} elseif($typeID == HM_Event_EventModel::TYPE_TASK && (stristr($title, 'задание') !== FALSE)){ 
				$taskRating += $i->V_STATUS;
			
			#  Итоговый текущий рейтинг
			} else { 
				$dataRatingMedium += $i->V_STATUS;
			}
		}
			
		$dataRatingMedium += $_serviceLesson->normalizeTask($taskRating, $issetIPZ, $taskMax);
		if ($type_journal_AA_maxball == HM_Lesson_Journal_JournalModel::MAX_BALL_ACADEMIC_ACTIVITY) {
			$dataRatingMedium += $_serviceLesson->normalizeAcadem($academRating); 				 # max 15 
		}
		else {
			$dataRatingMedium += $academRating < $type_journal_AA_maxball ? $academRating : $type_journal_AA_maxball;
		}
		$dataRatingMedium  = $_serviceLesson->normalizeTotalCurrentRating($dataRatingMedium);
		return array(
			'mark_current'	=> $dataRatingMedium,
			'mark_landmark' => $dataRatingTotal,
		);		
	}
	
	
	
	/**	 
	 * Возвращает баллы по Итоговый текущий рейтинг (или Интегральный текущий рейтинг)  и Рубежный рейтинг раздельно + доп признак главного модуля.
	 * аналогична методу getUserBallsSeparately, но подходит для вывода в ведомости успеваемости, т.к. есть информация по оценкам в занятиях
	 * @return array('lessons', total info, fail messages)	 
	 * !!!!! Переделать на сохранение оценок в БД и уже брать от туда. По крайне мере, для завершенных сессий не нужно расчитывать оценку	 
	 * UPD - убрать кэш, т.к. он общий для всех и работает криво и медленно. В контроллере для студента повесить на кэшь в сессии. Тут убрать.
	*/
	public function getUserBallsDetail($subject_id, $student_id){
		if(empty($subject_id) || empty($student_id)){ return false; }
		
		#$cache_name = '_user_balls';
		#$this->restoreFromCacheByName($cache_name);
		
		# раскомментировать при переносе на прод.
		#if($this->isCacheExpired($cache_name)){
		#	$this->clearCacheByName($cache_name);			
		#}
		
		#$user_balls = $this->{$cache_name};
		
		
		#if(isset($user_balls[$subject_id][$student_id])){
		#	return $user_balls[$subject_id][$student_id];
		#}
		
		#$user_balls[$subject_id][$student_id] = false;
		
		
		
		$_serviceLesson	= $this->getService('Lesson');
		$_serviceUser 	= $this->getService('User');		
		
		
		$divide_balls	 = $this->getUserBallDivide($subject_id, $student_id);		
		if(empty($divide_balls)){ return false; }
		
		#if(empty($divide_balls)){ 
		#	$this->{$cache_name} = $user_balls;
		#	$this->saveToCacheByName($cache_name);
		#	return false;
		#}
		
		$data = array();		
		$data['isDO']	 		= (bool)$this->isDOT($subject_id);
		$subject                = $this->getById($subject_id);
		
		# балл для ДО - это сумма баллов по занятиям. Если есть журнал, то немного иначе.
		if(
			$data['isDO']
			||
			$subject->isWithoutHours()
		){
			$data['total'] = $this->getUserBallDot($subject_id, $student_id);
		
			#$user_balls[$subject_id][$student_id] 	= $data;
			#$this->{$cache_name} 					= $user_balls;
			#$this->saveToCacheByName($cache_name);
			return $data;
		}
		
		$data['isMainModule']	= (bool)$this->isMainModule($subject_id);
		
		
		$collection				= $this->getActiveLessons($subject_id);
		$lessons_ids 	 		= $collection->getList('SHEID');
		$assigns 		 		= $this->getService('LessonAssign')->getByLessons($student_id, $lessons_ids);
		$data['lessons'] 		= $assigns->getList('SHEID', 'V_STATUS');
		
		
		$data['mark_current'] 	= round($divide_balls['mark_current']);
		$data['mark_landmark']	= round($divide_balls['mark_landmark']);
		$data['total'] 			= round($divide_balls['mark_current'] + $divide_balls['mark_landmark']); # сначала суммируем, потом округляем
		$data['isFail'] 		= true;
		$data['reasonFail'] 	= array();		
		$data['five_mark']		= $_serviceLesson->getFiveScaleMark($data['total']);
		
		
		
		if(empty($data['total'])){
			$data['isFail'] 	= false;	
		} else {
			$data['reasonFail'] = $this->getFailPassMessage($student_id, $subject_id);
		}
		
		if(empty($data['reasonFail'])){
			$data['isFail'] = false;
		}
		
		# если есть причина недопуска, то Рубежный рейтинг не учитываем
		if($data['isFail']){
			$data['total'] = round($divide_balls['mark_current']);
		}
		
		
		
		if( !$data['isMainModule'] ){ 
			#$user_balls[$subject_id][$student_id] 	= $data;
			#$this->{$cache_name} 					= $user_balls;
			#$this->saveToCacheByName($cache_name);
			return $data;
		}
		
		$subject = $this->getById($subject_id);
		
		if(empty($subject->module_code)){
			#$user_balls[$subject_id][$student_id] 	= $data;
			#$this->{$cache_name} 					= $user_balls;
			#$this->saveToCacheByName($cache_name);	
			return $data;
		}
		
		# до тех пор, пока не набран проходной балл по главному модулю, баллы из доп. модуля не учитываем.
		if(!$data['isFail']){
			$data['mark_current_integrate'] = $this->getIntegrateMediumRating($subject->module_code, $student_id, $subject->semester);			
			$data['total'] 					= round($data['mark_current_integrate'] + $divide_balls['mark_landmark']);  # сначала суммируем, потом округляем
			$data['five_mark']				= $_serviceLesson->getFiveScaleMark($data['total']);
			$data['reasonFail'] 			= $this->getFailPassMessage($student_id, $subject_id);
			$data['isFail'] 				= empty($data['reasonFail']) ? $data['isFail'] : true;
		}
		$data['isPassModule'] 				= true;
		
		$modules = $this->getModuleSubjects($subject->module_code, $subject->semester)->getList('subid');
		
		foreach($modules as $id){
			if($id == $subject_id){ continue; }
			
			$lessonCollections 						= $this->getActiveLessons($id);
			
			$divide_balls							= $this->getUserBallDivide($id, $student_id);			
			$data['module'][$id]['mark_current'] 	= $divide_balls['mark_current'];
			$data['module'][$id]['isFail']			= !$this->isPassModule($id, $student_id, $divide_balls['mark_current'], $lessonCollections);						
			$data['isPassModule'] 					= !$data['module'][$id]['isFail'] ? false : $data['isPassModule'];
			
			if($data['module'][$id]['isFail']){
				$list 			= HM_Subject_SubjectModel::getFailPassMessageList();
				$data['isFail'] = true;
				$data['reasonFail'][HM_Subject_SubjectModel::FAIL_PASS_MODULE_MIDDLE]['message'] = $list[HM_Subject_SubjectModel::FAIL_PASS_MODULE_MIDDLE];
			}
		}		
		#$user_balls[$subject_id][$student_id] 	= $data;
		#$this->{$cache_name} 					= $user_balls;
		#$this->saveToCacheByName($cache_name);		
		return $data;

	}
	
	
	public function isGia($subject)
	{
		$name = $subject->name;
		if(
			   stristr($name, 'ГИА') !== false
			|| stristr($name, 'Преддипломная') !== false
		){
			return true;			
		}
		return false;
	}
	
	
	
	
	
	/**
	 * оценка итогового рейтинга (оценка на экзамене)	 
	 * В \modules\els\user\controllers\ExportController.php аналогичная ф-ция
	*/
    public function getExamBall($mark){
		$mark = round($mark);
		if(empty($mark)){ return 0; }
		
		if($mark < 52){
			$ball_exam = 0;
			
		} elseif($mark == 52){
			$ball_exam = 1;
			
		} elseif($mark < 65){
			$ball_exam	= $mark - 52;
			
		} elseif(65 <= $mark && $mark < 75){
			$ball_exam = 13;
			
		} elseif(75 <= $mark && $mark < 85){
			$ball_exam = 17;
			
		} else {
			$ball_exam = 20;
		}
		return $ball_exam;
	}
	
	/**
	 * оценка текущего рейтинга
	 * В \modules\els\user\controllers\ExportController.php аналогичная ф-ция
	*/
	public function getCurrentBall($mark, $ball_exam){
		$mark = round($mark);
		if(empty($mark)) { return 0; }
		
		if($mark < 52){
			$ball_current = $mark;
			
		} elseif($mark == 52){
			$ball_current = $mark;
			
		} elseif($mark < 65){
			$ball_current = 52;
		
		} else {
			$ball_current = $mark - $ball_exam;
		}
		return $ball_current;
	}
	
	/**
	 * @return array
	 * @param - ограничение по дате сессии
	 * Получаем id всех сессий-очной формы обучения
	 * очка - это сессия, базовый курс которого в названии содержит фразу "очка"
	*/
	public function getFullTimeIds($date_from = false)
	{
		$select = $this->getSelect();
		$select->from(array('s' => 'subjects'), array('id'	=> 's.subid'));	
		$select->join(array('bs' => 'subjects'), 'bs.subid = s.base_id', array());					
		$select->where($this->quoteInto('s.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION));
		$select->where($this->quoteInto('bs.name LIKE ?', '%очка%'));
		
		if(!empty($date_from)){			
			$select->where($this->quoteInto('s.begin >= ?', $date_from));
		}
		
		$res = $select->query()->fetchAll();
		$ids = array();
		foreach($res as $i){
			$ids[$i['id']] = $i['id'];
		}
		return $ids;	
	}
	
	/**
	 * @return array
	 * @param - ограничение по дате сессии
	 * Получаем id всех сессий-практик
	 * практика - это сессия, базовый курс которого в названии содержит фразу "! Практика"
	*/
	public function getPracticeIds($date_from = false)
	{
		$select = $this->getSelect();
		$select->from(array('s' => 'subjects'), array('id'	=> 's.subid'));	
		$select->join(array('bs' => 'subjects'), 'bs.subid = s.base_id', array());					
		$select->where($this->quoteInto('s.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION));
		$select->where($this->quoteInto('bs.name LIKE ?', '%! Практика%'));
		
		if(!empty($date_from)){			
			$select->where($this->quoteInto('s.begin >= ?', $date_from));
		}
		
		$res = $select->query()->fetchAll();
		
		$ids = array();
		foreach($res as $i){
			$ids[$i['id']] = $i['id'];
		}
		return $ids;	
	}
	
	public function getPracticeMarkCurrent($ball)
	{
		return round ($ball * ( (HM_Subject_SubjectModel::MAX_PERCENT_PRACTICE_MARK_CURRENT) / 100 ) );
	}
	
	public function getPracticeMarksLandmark($ball)
	{
		return round ($ball * ( (HM_Subject_SubjectModel::MAX_PERCENT_PRACTICE_MARK_LANDMARK) / 100 ) );
	}
	
	public function getByProgramms($programms = array())
	{
		if(empty($programms)){ return false; }
		return $this->fetchAllDependenceJoinInner('ProgrammEvent',
                $this->quoteInto(array('ProgrammEvent.programm_id IN (?)', ' AND ProgrammEvent.type = ?'), array($programms, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT)),
                'self.name'
		);		
	}	
	
	public function getInadmissibilityReasons($subjectId, $studentId)
	{
		if(!$subjectId || !$studentId){ return false; }
		
		$subject   = $this->getService('Subject')->getById($subjectId);
		if(!$subject)        { return false; }
		
		$type = $subject->getTypeModel();
		
		return $type->getInadmissibilityReasons($subjectId, $studentId);
	}
	
	public function getScore($subject_id, $student_id, $isCalculate = false)
	{
		if($isCalculate){
			return $this->getService('SubjectMark')->calculateScore($subject_id, $student_id);
		}
		return $this->getService('SubjectMark')->getScore($subject_id, $student_id);
	}
	
	public function setScore($subject_id, $student_id)
	{
		return $this->getService('SubjectMark')->setScore($subject_id, $student_id);
	}
	
	public function updateLandmark($subjectId)
	{
		$subjectId     = (int)$subjectId;
		if(empty($subjectId)){ return false; }
		
		$mark_landmark = $this->getService('Lesson')->getMaxBallTotalRating($subjectId);		
		return $this->updateWhere(array('mark_landmark' => $mark_landmark), array('subid = ?' => $subjectId));
	}
	
}
