<?php

class HM_Blog_BlogService extends HM_Activity_ActivityService implements Es_Entity_Trigger
{
    const FILTER_TYPE_TAG = 'tag';
    const FILTER_TYPE_AUTHOR = 'author';
    const FILTER_TYPE_DATE = 'date';//date format is yyyy-mm
    
    const EVENT_GROUP_NAME_PREFIX = 'BLOG_MESSAGE_ADD';

    protected $_isIndexable = true;

    public function insert($data)
    {
        $data['created'] = $this->getDateTime();
        $item = parent::insert($data);
        if ($item) {
            // $doc = new HM_Activity_Search_Document(array(
                // 'activityName' => 'Blog',
                // 'activitySubjectName' => $item->SUBJECT_NAME,
                // 'activitySubjectId' => $item->SUBJECT_ID,
                // 'id' => $item->ID,
                // 'title' => $item->TITLE,
                // 'preview' => $item->TITLE
            // ));

            // $this->indexActivityItem($doc);
        }
        return $item;
    }

    public function insertActivityComment(HM_Comment_CommentModel $comment) {
        $item = parent::insertActivityComment($comment);
        // return $item;       
        return $item;
    }

    /**
     * Return blog select by $subjectId and $subjectName
     */
    public function getBlogSelect($subjectId, $subjectName = null)
    {
        $select = $this->getSelect()
            ->from(array('b' => 'blog'),
                array(
                    'id',
                    'blog_id' => 'id',
                    'title',
                    'created',
                    'tags' => 'id',
                    'created_by'//Grid не покажет поле, если его нет в выборке
                ));
            /*->joinLeft(array('p' => 'People'),
                    'p.MID = b.created_by',
                    array('author' => 'CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, \' \') , p.FirstName), \' \'), p.Patronymic)')
                );*/
        $select->where('b.subject_id = ?', $subjectId);
        if ($subjectName) {
            $select->where('b.subject_name = ?', $subjectName);
        } else {
            $select->where('b.subject_name IS NULL');
        }
        return $select;
    }

    public function getBlogCondition($subjectId, $subjectName = null, $filter = array(), $split = false)
    {
        $where = array();
        $where['subject_id = ?'] = $subjectId;
        if ($subjectName) {
            $where['subject_name = ?'] = $subjectName;
        } else {
            $where['subject_name IS NULL'] = null;
        }
        foreach($filter as $type => $value) {
            switch($type) {
                case self::FILTER_TYPE_TAG:
                                        
                    
                    /*$tagObj = $this->getOne($this->getService('Tag')->fetchAllDependence('TagRef',
                                                                                         array($this->quoteInto('BODY LIKE ?', 
                                                                                                                $value), 
                                                                                               $this->quoteInto('item_type = ?', 
                                                                                                                $objTagType->getType()))));*/
                    $select = $this->getSelect()->from(array('t' => 'tag'),array('tr.item_id'))
                                                ->joinInner(array('tr' => 'tag_ref'),'t.id = tr.tag_id', array())
                                                ->where($this->quoteInto('body LIKE ?', $value)) 
                                                ->where($this->quoteInto('item_type = ?', HM_Tag_Ref_RefModel::TYPE_BLOG));
                    $arResult = $select->query()->fetchAll();                                                                                                                                      
                    $ids = array();
                    foreach($arResult as $tagRef) {
                        $ids[] = $tagRef["item_id"];
                    }
                    if(count($ids) > 0) {
                        $where['id IN (?)'] = $ids;
                    }
                break;
                case self::FILTER_TYPE_AUTHOR:
                    $where['created_by = ?'] = $value;
                break;
                case self::FILTER_TYPE_DATE:
                    $start = $value.'-01';
                    $end = $value.'-'.date('t', strtotime($start));
                    $where['created >= ?'] = $start;
                    $where['created <= ?'] = $end;
                break;
                default:
                    throw new InvalidArgumentException('Unknown filter type '.$type);
            }
        }
        if(!$split) {
            return $where;
        }
        $parts = array();
        foreach($where as $k=>$v) {
            $parts []= $this->quoteInto($k, $v);
        }
        return '('.implode(') AND (', $parts).')';
    }

    public function addTag($blogId, $tagId, $rating)
    {
        $ref = $this->getService('TagRefBlog');
        $ref->insert(array(
            'tag_id' => $tagId,
            'blog_id' => $blogId
        ));
        $this->getService('Tag')->update(array(
            'rating' => $rating+1,
            'id' => $tagId
        ));
    }

    public function hasTag($blogId, $tagId)
    {
        $refs = $this->getService('TagRefBlog')->fetchAll(array(
            'tag_id = ?' => $tagId,
            'blog_id = ?' => $blogId
        ));
        return count($refs);
    }

    public function removeTag($blogId, $tag)
    {
        $this->getService('Tag')->update(array(
            'rating' => $tag->rating-1,
            'id' => $tag->id
        ));
        $this->getService('TagRefBlog')->deleteBy(array('blog_id = ?' => $blogId));
    }

    public function getAuthors($subjectId, $subjectName = null)
    {
        $select = $this->getSelect()
            ->from(array('b' => 'blog'),
                array('DISTINCT(created_by) as id'))
            ->where($this->getBlogCondition($subjectId, $subjectName, array(), true));
        $ids = array();
        $res = $select->query()->fetchAll();
        foreach($res as $item) {
            $ids []= $item['id'];
        }
        return $this->getService('User')->find($ids);
    }

    public function getArchiveDates($subjectId, $subjectName = null)
    {
        $blogs = $this->fetchAll($this->getBlogCondition($subjectId, $subjectName));
        $dates = array();
        $ldates = array();
        foreach($blogs as $blog) {
            $blog->created = strtotime($blog->created);
            $date = date('Y-m', $blog->created);
            if(!array_key_exists($date, $dates)) {
                $dates[$date] = 0;
                $ldates[$date] = new HM_Date($blog->created, Zend_Date::TIMESTAMP);
                $ldates[$date] = $ldates[$date]->getStandalone(HM_Date::MONTH_NAME).' '.$ldates[$date]->get(HM_Date::YEAR_8601);
            }
            $dates[$date]++;
        }
        foreach($ldates as $date => $name) {
            $ldates[$date] .= ' ('.$dates[$date].')';
        }
        return $ldates;
    }

    public function delete($id)
    {
        //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'), 
                                                               array($id,HM_Tag_Ref_RefModel::TYPE_BLOG)));
        parent::delete($id);
    }


    public function createEvent(\HM_Model_Abstract $model) {
        $event = $this->getService('ESFactory')->newEvent($model, array(
           'body', 'created', 'title'
        ), $this);
        return $event;
    }


    public function getRelatedUserList($id) {
        $db =  Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select();
        $result = array();

        $blogPostSelect = clone $select;
        $blogPostSelect->from(array('b' => 'blog'), array('subject_id' => 'b.subject_id'))
            ->where('b.id = ?', $id, 'INTERGER');
        $stmt = $blogPostSelect->query();
        $stmt->execute();
        $subjectRow = $stmt->fetchAll();
        $subjectId = $subjectRow[0]['subject_id'];

        if ($subjectId === null || intval($subjectId) == 0) {
            $select->from(array('d' => 'deans'), array('u' => 'd.MID'))
                ->group('d.MID');
            $stmt = $select->query();
            $res = $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $index => $item) {
                $result[] = intval($item['u']);
            }
        } else {
            $teachersSubselect = clone $select;
            $studentsSubselect = clone $select;
            $unionSelect = clone $select;
            $teachersSubselect->from(array('s' => 'subjects'), array())
                ->join(array('t' => 'Teachers'), 't.CID = s.subid AND s.subid='.intval($subjectId), array('UserId' => 't.MID'));
            $studentsSubselect->from(array('s' => 'subjects'), array())
                ->join(array('st' => 'Students'), 'st.CID = s.subid AND s.subid='.intval($subjectId), array('UserId' => 'st.MID'));
            $mainSelect = $unionSelect->union(array($teachersSubselect, $studentsSubselect))
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
            $parameters = $ev->getParameters();
            $item = $parameters['item'];
            $service = $ev->getSubject();
            /*@var $event Es_Entity_AbstractEvent */
            $event = $service->createEvent($item);

            if ($item->subject_id === null || intval($item->subject_id) == 0) {
                $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_BLOG_ADD_MESSAGE);
                $subject = null;
            } else {
                $subject = $service->getService('Subject')->find(intval($item->subject_id));
                $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_BLOG_INTERNAL_ADD_MESSAGE);
                $event->setParam('course_id', $subject->current()->getPrimaryKey());
            }
            $fullItem = $service->getService('Blog')->fetchAllDependence(array('TagRef','User'), 'id='.intval($item->getPrimaryKey()))->current();
            $event->setParam('user_name', $fullItem->User->current()->getName());
            $event->setParam('user_id', $fullItem->User->current()->getPrimaryKey());
            
            $userAvatar = '/'.ltrim($fullItem->User->current()->getPhoto(), '/');
            
            $event->setParam('user_avatar', $userAvatar);
            $tags = array();
            if ($fullItem->tagref !== null && $fullItem->tagref->count() > 0) {
                $tagIds = array();
                foreach ($fullItem->tagref as $tagRef) {
                    $tagIds[] = intval($tagRef->tag_id);
                }
                $tagRows = $service->getService('Tag')->fetchAll('id IN ('.implode(',', $tagIds).')');
                if ($tagRows->count() > 0) {
                    foreach ($tagRows as $tag) {
                        $tags[] = $tag->body;
                    }
                }
            }
            $event->setParam('tags', $tags);

            $eventGroup = $service->getService('ESFactory')->eventGroup(
                HM_Blog_BlogService::EVENT_GROUP_NAME_PREFIX, (int)$item->subject_id
            );
            $data = array();
            if ($subject !== null) {
                $data['course_name'] = $subject->current()->name;
                $data['course_id'] = $event->getParam('course_id');
                $event->setParam('course_name', $subject->current()->name);
            }
            $eventGroup->setData(json_encode($data));
            $event->setGroup($eventGroup);

            /* @var $service Es_Service_Dispatcher  */
            $esService = $service->getService('EventServerDispatcher');
            $esService->trigger(
                Es_Service_Dispatcher::EVENT_PUSH,
                $service,
                array('event' => $event)
            );
        };
    }

}
