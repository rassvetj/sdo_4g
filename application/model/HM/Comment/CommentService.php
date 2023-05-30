<?php
class HM_Comment_CommentService extends HM_Service_Abstract implements Es_Entity_Trigger
{

    const EVENT_GROUP_NAME_PREFIX = 'BLOG_COMMENT_ADD';

    public function insert($data, $unsetNull = true) {
        $item = parent::insert($data, $unsetNull);
        return $item;
    }

    public function createEvent(\HM_Model_Abstract $model) {
        $event = $this->getService('ESFactory')->newEvent($model, array(
           'message', 'created', 'user_id'
        ), $this);
        return $event;
    }


    public function getRelatedUserList($id) {
        $db =  Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select();
        $result = array();

        $commentSelect = clone $select;
        $commentSelect->from(array('c' => 'comments'), array('subject_id' => 'c.subject_id'))
            ->where('c.id = ?', $id, 'INTERGER');
        $stmt = $commentSelect->query();
        $stmt->execute();
        $subjectRow = $stmt->fetchAll();
        $subjectId = $subjectRow[0]['subject_id'];

        if ($subjectId === null || intval($subjectId) == 0) {
            $select->from(array('c1' => 'comments'), array())
                ->join(array('c2' => 'comments'), 'c1.item_id = c2.item_id', array('CUID' => 'c2.user_id'))
                ->join(array('b' => 'blog'), 'b.id=c2.item_id', array('PUID' => 'b.created_by'))
                ->where('c1.id = ?', $id, 'INTEGER')
                ->group('CUID', 'PUID');
            $stmt = $select->query();
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $index => $item) {
                if ($index == 0) {
                    $result[] = intval($item['PUID']);
                }
                $result[] = intval($item['CUID']);
            }
            $result = array_unique($result);
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
                $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_COMMENT_ADD);
            } else {
                $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_COMMENT_INTERNAL_ADD);
            }
            $fullItem = $service->getService('Comment')->fetchAllDependence(array('TagList','User'), 'id='.intval($item->getPrimaryKey()))->current();
            $event->setParam('user_name', $fullItem->User->current()->getName());
            $tags = array();
            if ($fullItem->refTags !== null && $fullItem->refTags->count() > 0) {
                $tagIds = array();
                foreach ($fullItem->refTags as $tagRef) {
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
            $blogPostInstance = $service->getService('Blog')->find((int)$item->item_id)->current();
            $eventGroup = $service->getService('ESFactory')->eventGroup(
                HM_Comment_CommentService::EVENT_GROUP_NAME_PREFIX, intval($item->item_id) 
            );
            $eventGroup->setData(json_encode(array(
                'post_title' => $blogPostInstance->title
            )));


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
