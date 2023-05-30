<?php

class HM_Chat_ChatChannelsService extends HM_Activity_ActivityService implements HM_Service_Schedulable_Interface
{
    protected $_isIndexable = false;

    // public function insert($data)
    // {
        // $data['created'] = $this->getDateTime();
        // $item = parent::insert($data);
        // if ($item) {
            // $doc = new HM_Activity_Search_Document(array(
                // 'activityName' => 'Blog',
                // 'activitySubjectName' => $item->SUBJECT_NAME,
                // 'activitySubjectId' => $item->SUBJECT_ID,
                // 'id' => $item->ID,
                // 'title' => $item->TITLE,
                // 'preview' => $item->TITLE
            // ));

            // $this->indexActivityItem($doc);
        // }
        // return $item;
    // }
    
    public function getChannelsSelect($subjectId, $subjectName = null)
    {
        $select = $this->getSelect()
            ->from(array('ch' => 'chat_channels'),
                array(
                    'id',
                    'is_general',
                    'name',
                    'users_count' => 'name',
                    'access_time' => 'start_date',
                    'start_date',
                    'end_date', 
                    'start_time', 
                    'end_time',
                    'last_update' => 'start_date'
                ));
        // $select->where('is_general = ?', 0);
        $select->where('subject_id = ?', $subjectId);
        if ($subjectName) {
            $select->where('subject_name = ?', $subjectName);
        } else {
            $select->where('subject_name IS NULL', null);
        }
        $select->where('lesson_id IS NULL', null);
        return $select;
    }
    
    public function getChannelsUsersCount($subjectId, $subjectName = null)
    {
        $where = array();
        $where['subject_id = ?'] = $subjectId;
        if ($subjectName) {
            $where['subject_name = ?'] = $subjectName;
        } else {
            $where['subject_name IS NULL'] = null;
        }
        $where['lesson_id IS NULL'] = null;
        $channels = $this->fetchAllManyToMany('Users', 'ChatRefUsers', $where);
        $res = array();
        foreach($channels as $channel) {
            if($channel->is_general) {
                $res[$channel->id] = count($this->getActivityUsers());
            } else {
                $res[$channel->id] = count($channel->users);
            }
        }
        return $res;
    }
    
    /** 
     * Return channels select by $subjectId and $subjectName
     */
    public function getChannelsCondition($subjectId, $subjectName = null, $split = false)
    {
        $where = array();
        $where['subject_id = ?'] = $subjectId;
        if ($subjectName) {
            $where['subject_name = ?'] = $subjectName;
        } else {
            $where['subject_name IS NULL'] = null;
        }
        $where['lesson_id IS NULL'] = null;
        $time = (int)date('Hi');
        $date = date('Y-m-d');
        $where["((start_date IS NULL AND end_date IS NULL AND (start_time IS NULL OR start_time = 0) AND (end_time IS NULL OR end_time = 0))
                OR
                ((start_time IS NULL OR start_time = 0) AND (end_time IS NULL OR end_time = 0) AND '$date' >= start_date AND '$date' <= end_date)
                OR
                (start_time IS NOT NULL AND end_time IS NOT NULL AND '$date' = start_date AND $time >= start_time AND $time <= end_time)
            )"] = null;
        if(!$split) {
            return $where;
        }
        $parts = array();
        foreach($where as $k=>$v) {
            $parts []= $this->quoteInto($k, $v);
        }
        return '('.implode(') AND (', $parts).')';
    }
    
    public function getArchive($subjectId, $subjectName = null)
    {
        $where = array();
        $where['subject_id = ?'] = $subjectId;
        if ($subjectName) {
            $where['subject_name = ?'] = $subjectName;
        } else {
            $where['subject_name IS NULL'] = null;
        }
        $where['lesson_id IS NULL'] = null;
        $time = (int)date('Hi');
        $date = date('Y-m-d');
        $cond = "(start_time IS NULL AND end_time IS NULL AND ('$date' > end_date)) OR ";//'$date' < start_date AND OR '$date' > end_date
        $cond .= "(start_time IS NOT NULL AND end_time IS NOT NULL AND start_date = '$date' AND ($time > end_time))";//$time < start_time OR $time > end_time
        $where[$cond] = null;
        $where['show_history = ?'] = 1;
        // print_r($where);exit;
        return $this->fetchAll($where);
    }
    
    public function getGeneralChannel($subjectId, $subjectName = null, $lessonId = 0)
    {
        $where = array();
        $where['subject_id = ?'] = $subjectId;
        if ($subjectName) {
            $where['subject_name = ?'] = $subjectName;
        } else {
            $where['subject_name IS NULL'] = null;
        }
        if($lessonId) {
            $where['lesson_id = ?'] = $lessonId;
        }
        $where['is_general = ?'] = 1;
        $channel = $this->fetchAllManyToMany('Users', 'ChatRefUsers', $where)->current();
        if(!$channel->id) {
            $data = array(
                'subject_name' => $subjectName,
                'subject_id' => $subjectId, 
                'is_general' => 1
            );
            if($lessonId) {
               $data['lesson_id'] = $lessonId;
               $lesson = $this->getService('Lesson')->find($lessonId)->current();
               $data['name'] = $lesson->title;
            } else {
               $data['name'] = _('Общий канал');
    }
            $channel = $this->insert($data);
        }
        $channel->users = $this->getActivityUsers();
        return $channel;
    }
    
    public function getById($channelId)
    {
        $channel = $this->findManyToMany('Users', 'ChatRefUsers', $channelId)->current();
        if($channel && $channel->id && $channel->is_general) {
            $channel->users = $this->getActivityUsers();
    }
        return $channel;
    }
    
    public function isCurrentUserInChannel($channel)
    {
        $userIds = $this->getChannelUserIds($channel);
        return in_array($this->getService('User')->getCurrentUserId(), $userIds);
    }
    
    public function getChannelUserIds($channel)
    {
        $userIds = array();
        if($channel->is_general) {
            $users = $this->getActivityUsers();
            foreach($users as $user) {
                $userIds []= $user->MID;
            }
        } else if($channel->users && count($channel->users) > 0) {
            foreach($channel->users as $user) {
                $userIds []= $user->MID;
            }
        }

        return $userIds;
    }
    
    public function delete($id)
    {
        $id = (int) $id;
        $this->getService('ChatHistory')->deleteBy(array('channel_id = ?' => $id));
        $this->getService('ChatRefUsers')->deleteBy(array('channel_id = ?' => $id));
        parent::delete($id);
    }
    
    public function onCreateLessonForm(Zend_Form $form, $activitySubjectName, $activitySubjectId, $title = null)
    {
        $frontController = Zend_Controller_Front::getInstance();
        $request = $frontController->getRequest();
        $lessonId = (int)$request->getParam('lesson_id', 0);
        $subForm = $request->getParam('subForm', 0);
        $session = new Zend_Session_Namespace('chat_LessonForm');
        if($lessonId) {
            // $where = array();
            // $where['subject_id = ?'] = $activitySubjectId;
            // $where['subject_name = ?'] = $activitySubjectName;
            // $where['lesson_id = ?'] = $lessonId;
            // $lessonChat = $this->getOne($this->find($where));
            // if($lessonChat && $lessonChat->id) {
                // $session->module = $lessonChat->id;
            // }
        } elseif($subForm == 'step2' && !$session->module) {
            $lessonChatData = array();
            $lessonChatData['name'] = $title;
            $lessonChatData['subject_name'] = $activitySubjectName;
            $lessonChatData['subject_id'] = $activitySubjectId;

            $lessonChat = $this->insert($lessonChatData);
            $session->module = $lessonChat->id;
        }
        $form->clearElements();
    }
    
    public function onLessonUpdate($lesson, $form)
    {
        $session = new Zend_Session_Namespace('chat_LessonForm');
        if(isset($session->module)) {
            $lessonChat = $this->getOne($this->find($session->module));
        if($lessonChat && $lessonChat->id && !$lessonChat->lesson_id) {
            $this->update(array(
                'lesson_id' => $lesson->SHEID,
                'id' => $lessonChat->id
            ));
            $switch = (int)$form->getValue('switch');
            $students = $form->getValue('students');
            if($switch == 0) {
                $students = $this->getService('Subject')->getAssignedUsers($lessonChat->subject_id)->getList('MID', 'MID');
            }
            if(is_array($students) && count($students)) {
                foreach($students as $userId) {
                    $this->getService('ChatRefUsers')->insert(array(
                        'channel_id' => $lessonChat->id,
                        'user_id' => $userId
                    ));
                }
            }
        }
            $params = $lesson->getParams();
            $params['module_id'] = $session->module;
            $lesson->setParams($params);
            unset($session->module);
    }
    }

    public function getLessonModelClass()
    {
        return "HM_Lesson_Chat_ChatModel";
    }
    
    public function usersOnline($channel)
    {
        $users = array();
        $userIds = array();
        if($channel->users && count($channel->users) > 0) {
            $usersOnline = $this->getService('User')->getUsersOnline();
            foreach($channel->users as $user) {
                if(in_array($user->MID, $usersOnline)) {
                    $users []= $user;
                }
            }
        }
        return $users;
    }
      /**
     * метод обновления общего канала чата
     * @param integer $localId MID пользователя
     * @author GlazyrinAE <glazyrin.andre@mail.ru>
     */
    public function updateTotalChannel($localId)
    {
        $updateChannel = $this->fetchAll(array('is_general = ?' => 1));
        $channelsGeneral = $updateChannel->getList('id');
        if (count($channelsGeneral)>0)
        {
            foreach ($updateChannel as $channelsGen)
            {
                $researchChannel = $this->getService('ChatRefUsers')->fetchRow(array('channel_id = ?' => $channelsGen->id,'user_id = ?' => $localId));
                if (!$researchChannel->user_id)
                {
                    $this->getService('ChatRefUsers')->insert(array(
                        'channel_id' => $channelsGen->id,
                        'user_id' => $localId
                    ));
                }
            }
        }
    }
}