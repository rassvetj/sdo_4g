<?php
class HM_Activity_ActivityService extends HM_Service_Abstract
{

    protected $_isIndexable = false;

    /**
     * @var HM_Search_Indexer_Activity
     */
    private $_indexer = null;

    /**
     * @var HM_Activity_Cabinet_CabinetModel
     */
    protected $_cabinet = null;

    public function __construct($mapperClass = null, $modelClass = null)
    {
        parent::__construct($mapperClass, $modelClass);
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this->initializeActivityCabinet(get_class($this), $request->getParam('subject', ''), $request->getParam('subject_id', 0), $request->getParam('lesson_id', 0));
    }

    public function initializeActivityCabinet($activityName, $activitySubjectName, $activitySubjectId, $activityLessonId = 0)
    {
        if ($activityLessonId) {
            $activitySubjectName = 'subject';
        }

        $this->_cabinet = new HM_Activity_Cabinet_CabinetModel(
            array(
                'activity_name' => $activityName,
                'subject_name' => $activitySubjectName,
                'subject_id' => $activitySubjectId,
                'lesson_id' => $activityLessonId
            )
        );
    }

    public function isIndexable()
    {
        return $this->_isIndexable;
    }

    public function getCabinet(){
        return $this->_cabinet;
    }

    public function indexActivityItem(HM_Activity_Search_Document $doc)
    {
        if ($this->isIndexable()) {
            if (null == $this->_indexer) {
                $this->_indexer = new HM_Search_Indexer_Activity();
            }

            $doc->addField(Zend_Search_Lucene_Field::Keyword('document_activity_name', strtolower($this->_cabinet->getActivityName())));
            $doc->addField(Zend_Search_Lucene_Field::Keyword('document_activity_subject_name', strtolower($this->_cabinet->getActivitySubjectName())));
            $doc->addField(Zend_Search_Lucene_Field::Keyword('document_activity_subject_id', strtolower($this->_cabinet->getActivitySubjectId())));

            return $this->_indexer->insert($doc);
        }
        return true;
    }

    public function isActivityUser($userId, $userRole)
    {

        if ($this->getService('Acl')->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            $userRole = HM_Role_RoleModelAbstract::ROLE_ENDUSER;
        }

        $select = $this->getActivityUsersSelect();
        $select->where('t1.MID = ?', $userId);
        $select->where('r.role LIKE ?', '%'.$userRole.'%');
        $stmt = $select->query();
        $stmt->fetchAll();
        return $stmt->rowCount();
    }

    /**
     * @param  string $activitySubjectName
     * @param  int $activitySubjectId
     * @return HM_Collection
     */
    public function getActivityUsers($onlyModerator = false, $onlyCurrentUser = false)
    {
        $collection = new HM_Collection(array(), 'HM_User_UserModel');
        $subSelect = $this->getActivityUsersSelect($onlyModerator);
        $select = $this->getService('User')->getSelect();
        $select->from(array('p' => 'People'), array('p.*'))
                ->joinInner(array('s' => $subSelect), 'p.MID = s.MID', array());
        
        if ($onlyCurrentUser) {
            $select->where($this->quoteInto('p.MID = ?', $this->getService('User')->getCurrentUserId()));
        }
              
        $stmt = $select->query();
        $result = $stmt->fetchAll();
        if (count($result)) {
            foreach($result as $data) {

// цикл по _всем_ юзерам с случае глобального кабинета => проблема с быстродействием
// решил на уровне БД в getActivityUsersSelect()
               
//                 $isModerator = false;
//                 if (isset($data['role'])) {
//                     $roles = explode(',', $data['role']);
//                     if (count($roles)) {
//                         foreach($roles as $role) {
//                             $isModerator = $this->isUserActivityModerator($data['MID'], $role);
//                             if ($isModerator) break;
//                         }
//                     }
//                 }
//                 unset($data['role']);
//                 $data['isPotentialModerator'] = $isModerator;
//                 /**
//                  * Чтобы пользователь мог видеть свои папки
//                  * при постоянном фильтре (только модераторы)
//                  */
//                 if(($isModerator == false && $onlyModerator == true) && ($this->getService('User')->getCurrentUserId() != $data['MID'])) continue;

                $collection[count($collection)] = new HM_User_UserModel($data);
            }
        }

        return $collection;

    }

    /**
     * @param  $activitySubjectName
     * @param  $activitySubjectId
     * @return Zend_Db_Table_Select
     */
    public function getActivityUsersSelect($onlyModerator = false)
    {
        $currentUserId = $this->getService('User')->getCurrentUserId();
        $select = $this->getService('User')->getSelect();
        if ($this->_cabinet->getActivityLessonId() > 0) {

            $select1 = clone $select;
            $select2 = clone $select;

            $subSelect = clone $select;

            $select1
                ->from(
                    array('t1' => 'People'),
                    array(
                        't1.MID',
                        'fio'    => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                        't1.Phone',
                        't1.Fax',
                        't1.Gender',
                        't1.EMail'))
                ->joinInner(
                    array('si' => 'scheduleID'),
                    't1.MID = si.MID',
                    array()
                )->where('si.SHEID = ?', $this->_cabinet->getActivityLessonId());

            $select2
                ->from(
                    'People',
                    array(
                        'People.MID',
                        'fio'    => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(People.LastName, ' ') , People.FirstName), ' '), People.Patronymic)"),
                        'People.Phone',
                        'People.Fax',
                        'People.Gender',
                        'People.EMail'))
                ->joinInner(
                    'Teachers',
                    'Teachers.MID = People.MID',
                    array()
                )->where('Teachers.CID = ?', $this->_cabinet->getActivitySubjectId());

            $subSelect->union(array($select1, $select2), Zend_Db_Select::SQL_UNION);

            $select->from(array('t1' => $subSelect),
                array(
                    't1.MID',
                    't1.fio',
                    't1.Phone',
                    't1.Fax',
                    't1.Gender',
                    't1.EMail')
                )->joinLeft(
                    array( 'r' =>'roles'),
                    'r.mid = t1.MID',
                    array(
                      'role' => 'r.role'
                    )
                );

            return $select;
        }

        $activitySubjectName = $this->_cabinet->getActivitySubjectName();

        switch(strtolower($activitySubjectName)) {
            case 'course':
            case 'resource':
            case 'subject':

                $fields = array(
                    't1.MID',
                        'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                    );

                $isModerator = $this->isUserActivityPotentialModerator($currentUserId);

                if ($isModerator || !$this->getService('Option')->getOption('disable_contacts')) {
                     $fields[] = 't1.Phone';
                     $fields[] = 't1.EMail';
                }

                $select->from(array('t1' => 'People'), $fields)
                    ->join(array('r' =>'activities'),
                        'r.mid = t1.MID',
                            array('role' => new Zend_Db_Expr('GROUP_CONCAT(r.role)')
                        ));

                $activitySubjectId = $this->_cabinet->getActivitySubjectId();
                if ($activitySubjectId > 0) {
                    $whereSubject = $this->quoteInto(array(
                            'r.subject_name = ? AND ',
                            '(r.subject_id = ? OR r.subject_id = 0)',
                        ), array(
                            $activitySubjectName,
                            $activitySubjectId,
                        ));
                    $select->where($whereSubject);
                } else {
                    $onlyModerator = true;
                }

                $select->group(array(
                        't1.MID',
                        't1.LastName',
                        't1.FirstName',
                        't1.Patronymic',
                        't1.Phone',
                        't1.Fax',
                        't1.Gender',
                        't1.EMail'
                ));
                break;

            default:
                $isPotentialModerator = $this->quoteInto(array(
                    'CASE WHEN (r.role LIKE ? OR ',
                    'r.role LIKE ? OR ',
                    'r.role LIKE ?) THEN 1 ELSE 0 END',
                ), array(
                    '%' . HM_Role_RoleModelAbstract::ROLE_MANAGER . '%',
                    '%' . HM_Role_RoleModelAbstract::ROLE_DEAN . '%',
                    '%' . HM_Role_RoleModelAbstract::ROLE_ADMIN . '%',
                    '%' . HM_Role_RoleModelAbstract::ROLE_SUPERVISOR . '%',
                    '%' . HM_Role_RoleModelAbstract::ROLE_TUTOR . '%',
                ));
                
                $select->from(array('t1' => 'People'),
                        array(
                            't1.MID',
                            'fio'    => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"),
                            't1.Phone',
                            't1.Fax',
                            't1.Gender',
                            't1.EMail',
                            'isPotentialModerator' => new Zend_Db_Expr($isPotentialModerator)
                        ))
                    ->joinLeft(array( 'r' =>'roles'),
                        'r.mid = t1.MID',
                        array(
                          'role' => 'r.role'
                        )
                    );
        }
                    
                if ($onlyModerator) {
                    $select->where($this->quoteInto(array(
                    'r.role LIKE ? OR ',
                    'r.role LIKE ? OR ',
                    'r.role LIKE ? OR ',
                    't1.MID = ?',
                ), array(
                    '%' . HM_Role_RoleModelAbstract::ROLE_MANAGER . '%',
                    '%' . HM_Role_RoleModelAbstract::ROLE_DEAN . '%',
                    '%' . HM_Role_RoleModelAbstract::ROLE_ADMIN . '%',
                    $currentUserId // не совсем понимаю зачем это, но сейчас currentUser тоже выводится вместе с модераторами
                )));                    
            }

        return $select;
    }

    public function isUserActivityPotentialModerator($userId)
    {
        if ($this->_cabinet->getActivityLessonId() > 0) {
            return $this->getService('Lesson')->isTeacher($this->_cabinet->getActivityLessonId(), $userId);
        }

        switch(strtolower($this->_cabinet->getActivitySubjectName())) {
            case 'subject':
                return $this->getService('Subject')->isTeacher($this->_cabinet->getActivitySubjectId(), $userId);
                break;
            case 'course':
                return $this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_DEAN);
                break;
            case 'resource':
                return $this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_MANAGER);
                break;
            default:
                return ($this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_ADMIN)
                        || $this->getService('User')->isRoleExists($userId, HM_Role_RoleModelAbstract::ROLE_DEAN));
        }
    }

    public function isUserActivityModerator($userId, $userRole)
    {   
		if ($this->_cabinet->getActivityLessonId() > 0) {
            return (
					(
						(HM_Role_RoleModelAbstract::ROLE_TEACHER == $userRole) // преподаватель в занятии или 
						&& $this->getService('Lesson')->isTeacher($this->_cabinet->getActivityLessonId(), $userId)
					) ||
					(
						(HM_Role_RoleModelAbstract::ROLE_TUTOR == $userRole) //--тьютор на курсе (не в занятии)
						&& $this->getService('Subject')->isTutor($this->_cabinet->getActivitySubjectId(), $userId)
					)
					);
        }

        switch(strtolower($this->_cabinet->getActivitySubjectName())) {            
			case 'subject':
                return (
					(
						(HM_Role_RoleModelAbstract::ROLE_TEACHER == $userRole)
                        && $this->getService('Subject')->isTeacher($this->_cabinet->getActivitySubjectId(), $userId)
                        /*
                         * хоть мы и даём преподавателю права модератора в рамках своего курса,
                         * но нельзя ему давать возможность редактировать любые папки, кроме своей
                         */
                        && $this->_cabinet->getActivityName() != 'HM_Storage_StorageFileSystemService'
					) 
					||
					(
						(HM_Role_RoleModelAbstract::ROLE_TUTOR == $userRole) //--для тьютора такие же права на форум, как и для преподавателя.
                        && $this->getService('Subject')->isTutor($this->_cabinet->getActivitySubjectId(), $userId)
                        /*
                         * хоть мы и даём преподавателю права модератора в рамках своего курса,
                         * но нельзя ему давать возможность редактировать любые папки, кроме своей
                         */
                        && $this->_cabinet->getActivityName() != 'HM_Storage_StorageFileSystemService'
					)	
                );
                break;
            case 'course':
                return in_array($userRole, array(HM_Role_RoleModelAbstract::ROLE_MANAGER, HM_Role_RoleModelAbstract::ROLE_DEVELOPER));
                break;
            case 'resource':
                return (HM_Role_RoleModelAbstract::ROLE_MANAGER == $userRole);
                break;
            default:
                return in_array($userRole, array(
                    HM_Role_RoleModelAbstract::ROLE_ADMIN,
                    HM_Role_RoleModelAbstract::ROLE_DEAN,
                    HM_Role_RoleModelAbstract::ROLE_MANAGER
                ));
                break;
        }
        return false;
    }

    public function isCurrentUserActivityModerator()
    {        
		return $this->isUserActivityModerator($this->getService('User')->getCurrentUserId(), $this->getService('User')->getCurrentUserRole());
    }

    /**
     * Добавить комментарий
     *
     * @return HM_Comment_CommentModel
     */
    public function insertActivityComment(HM_Comment_CommentModel $comment)
    {
        return $this->getService('Comment')->insert(
            array(
                'activity_name' => $this->_cabinet->getActivityName(),
                'subject_name' => $this->_cabinet->getActivitySubjectName(),
                'subject_id' => $this->_cabinet->getActivitySubjectId(),
                'user_id' => $comment->user_id,
                'item_id' => $comment->item_id,
                'message' => $comment->message,
                'created' => $this->getDateTime()
            )
        );
    }

    /**
     * Удалить комментарий $commentId
     * @param  $activityName
     * @param  $activitySubjectName
     * @param  $activitySubjectId
     * @param  $commentId
     * @return
     */
    public function deleteActivityComment($commentId)
    {
        return $this->getService('Comment')->deleteBy(
            $this->quoteInto(
                array('activity_name = ?', ' AND subject_name = ?', ' AND subject_id = ?', ' AND id = ?'),
                array($this->_cabinet->getActivityName(), $this->_cabinet->getActivitySubjectName(), $this->_cabinet->getActivitySubjectId(), $commentId)
            )
        );
    }

    /**
     * Получить список комментариев текущего виртуального кабинета
     * @param  $itemId
     * @param  $userId
     * @param  $count
     * @param  $offset
     * @return HM_Collection
     */
    public function fetchAllActivityComments($itemId = null, $userId = null, $count = null, $offset = null)
    {
        $subjectName = $this->_cabinet->getActivitySubjectName();
        return $this->getService('Comment')->fetchAll(
            $this->quoteInto(
                array('activity_name = ?', ' AND subject_id = ?'),
                array($this->_cabinet->getActivityName(), $this->_cabinet->getActivitySubjectId())
            )
            .(!empty($subjectName) ? $this->quoteInto(' AND subject_name = ?', $subjectName) : ' AND (subject_name IS NULL OR subject_name = \'\')')
            .(null !== $itemId ? $this->quoteInto(' AND item_id = ?', $itemId) : '')
            .(null !== $userId ? $this->quoteInto(' AND user_id = ?', $userId) : ''),
            'created DESC',
            $count,
            $offset
        );
    }

    /**
     * @param HM_Subscription_Channel_ChannelModel $channel
     * @return  HM_Subscription_Channel_ChannelModel
     */
    public function registerActivityChannel(HM_Subscription_Channel_ChannelModel $channel)
    {
        if (!$channel->activity_name) $channel->activity_name = $this->_cabinet->getActivityName();
        if (!$channel->subject_name)  $channel->subject_name  = $this->_cabinet->getActivitySubjectName();
        if (!$channel->subject_id)    $channel->subject_id    = $this->_cabinet->getActivitySubjectId();
        if (!$channel->lesson_id)     $channel->lesson_id     = $this->_cabinet->getActivityLessonId();

        return $this->getService('Subscription')->insertChannel($channel->getValues());
    }

    /**
     * @param HM_Subscription_Channel_ChannelModel $channel
     * @return bool
     */
    public function isActivityChannel(HM_Subscription_Channel_ChannelModel $channel)
    {
        if ($channel->activity_name) {
            $query['activity_name = ?'] = $channel->activity_name;
        } else {
            return false;
        }

        if ($channel->subject_name) {
            $query['subject_name = ?'] = $channel->subject_name;
        } else {
            $query['subject_name IS NULL'];
        }

        $query['subject_id = ?'] = (int) $channel->subject_id;
        $query['lesson_id = ?']  = (int) $channel->lesson_id;

        $result = $this->getService('SubscriptionChannel')->getOne($this->getService('SubscriptionChannel')->fetchAll($query));
        return ($result)? true : false;
    }

    public function unregisterActivityChannel(HM_Subscription_Channel_ChannelModel $channel)
    {
        return $this->getService('Subscription')->deleteChannel($channel->id);
    }

    public function subscribeUser($userId, $channelId)
    {
        return $this->getService('Subscription')->insert(array('user_id' => $userId, 'channel_id' => $channelId));
    }

    public function unsubscribeUser($userId, $channelId)
    {
        return $this->getService('Subscription')->deleteBy($this->quoteInto(array('user_id = ?', ' AND channel_id = ?'), array($userId, $channelId)));
    }

    /**
     * @param  $channelId
     * @param HM_Subscription_Entry_EntryModel $entry
     * @return HM_Subscription_Entry_EntryModel
     */
    public function insertActivityEntry($channelId, HM_Subscription_Entry_EntryModel $entry)
    {
        return $this->getService('Subscription')->insertEntry($entry->getValues());
    }

    public function updateActivityEntry(HM_Subscription_Entry_EntryModel $entry)
    {
        return $this->getService('Subscription')->updateEntry($entry->getValues());
    }

    public function deleteActivityEntry($entryId)
    {
        return $this->getService('Subscription')->deleteEntry($entryId);
    }

    /**
     * @param  $userId
     * @param  $activityName
     * @param  $activitySubjectName
     * @param  $activitySubjectId
     * @return HM_Collection
     */
    public function getUserActivityChannels($userId)
    {
        return $this->getService('SubscriptionChannel')->fetchAll(
            $this->quoteInto(
                array('user_id = ?', ' AND activity_name = ?', ' AND subject_name = ?', ' AND subject_id = ?'),
                array($userId, $this->_cabinet->getActivityName(), $this->_cabinet->getActivitySubjectName(), $this->_cabinet->getActivitySubjectId())
            )
        );
    }

    public function getActivityFileBrowserUrl()
    {
        return '';
    }

    /**
     * @param  $fileId
     * @return HM_Activity_File_FileModel
     */
    public function findActivityFile($fileId)
    {

    }

    public function getSubjectTitle($subjectName, $subjectId)
    {
        switch(strtolower($subjectName)) {
            case 'subject':
                return  $this->getOne($this->getService('Subject')->find($subjectId))->name;
            case 'course':
                return $this->getOne($this->getService('Course')->find($subjectId))->Title;
            case 'resource':
                return $this->getOne($this->getService('Resource')->find($subjectId))->title;
            default:
                return '';
        }
    }

    /**
     * Создание канала для занятия
     * @param $lesson
     * @return bool|HM_Subscription_Channel_ChannelModel
     */
    public function createLessonSubscriptionChannel(HM_Lesson_LessonModel $lesson)
    {
        $activityService = HM_Activity_ActivityModel::getActivityService($lesson->typeID);
        if (strlen($activityService)) {
            $service = $this->getService($activityService);
        }

        if ($service instanceof HM_Service_Schedulable_Interface) {
            $channel = new HM_Subscription_Channel_ChannelModel(array(
                'activity_name' => $service->_cabinet->getActivityName(),
                'subject_name'  => $service->_cabinet->getActivitySubjectName(),
                'subject_id'    => $service->_cabinet->getActivitySubjectId(),
                'lesson_id'     => $lesson->SHEID,
                'title'         => $lesson->title
            ));

            if (!$this->isActivityChannel($channel)) {
                return $this->registerActivityChannel($channel);
            }
        }

        return false;
    }
}
