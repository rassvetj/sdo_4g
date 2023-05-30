<?php

class HM_Wiki_WikiArticlesService extends HM_Activity_ActivityService implements HM_Service_Schedulable_Interface, Es_Entity_Trigger
{

    const EVENT_GROUP_NAME_PREFIX = 'WIKI_PAGE';

    public function insert($data)
    {
        $data['created'] = $this->getDateTime();
        $article = parent::insert($data);
        $this->getService('EventDispatcher')->notify(
            new sfEvent($this, __CLASS__.'::esPushTrigger', array('article' => $article))
        );
        return $article;
    }

    public function update($data, $unsetNull = null) {
        $item = parent::update($data);
        $this->getService('EventDispatcher')->notify(
            new sfEvent($this, __CLASS__.'::esPushTrigger', array('article' => $item))
        );
        return $item;
    }

    public function getWikigSelect($subjectId, $subjectName = null)
    {
        $select = $this->getSelect()
            ->from('wiki_articles', 
            array('DISTINCT(wiki_articles.id) as id', 'title', 'title as title_url', 'title as authors', 'wiki_articles.created', 'changed')
        );
        $select->where($this->getCondition($subjectId, $subjectName, true));
        return $select;
    }

    public function getByTitle($title, $subjectId, $subjectName = null)
    {
        $title = str_replace('.', '', $title);
        $where = $this->getCondition($subjectId, $subjectName);
        $where['title LIKE ?'] = trim($title);
        return $this->fetchAll($where, 'id DESC', 1)->current();
    }

    public function getByLesson($lessonId, $subjectId, $subjectName = null)
    {
        $where = array();
        $where['subject_id = ?'] = $subjectId;
        if ($subjectName) {
            $where['subject_name = ?'] = $subjectName;
        } else {
            $where['subject_name IS NULL'] = null;
        }
        $where['lesson_id = ?'] = $lessonId;
        $article = $this->fetchAll($where, 'id DESC', 1)->current();
        if($article && $article->id) {
            return $article;
        }
        $lesson = $this->getService('Lesson')->find($lessonId)->current();
        $article = $this->insert(array(
            'title' => $lesson->title,
            'subject_name' => $subjectName, 
            'subject_id' => $subjectId,
            'lesson_id' => $lessonId
        ));
        $body = $this->getService('WikiArchive')->insert(array(
            'author' => $this->getService('User')->getCurrentUserId(),
            'article_id' => $article->id,
            'body' =>'h1. '.ucfirst($lesson->title)
        ));
        $this->update(array(
            'changed' => $body->created,
            'id' => $article->id
        ));
        return $article;
    }

    public function getCondition($subjectId, $subjectName = null, $split = false)
    {
        $where = array();
        $where['subject_id = ?'] = $subjectId;
        if ($subjectName) {
            $where['subject_name = ?'] = $subjectName;
        } else {
            $where['subject_name IS NULL'] = null;
        }
        $where['lesson_id IS NULL'] = null;
        if(!$split) {
            return $where;
        }
        $parts = array();
        foreach($where as $k=>$v) {
            $parts []= $this->quoteInto($k, $v);
        }
        return '('.implode(') AND (', $parts).')';
    }

    public function onCreateLessonForm(Zend_Form $form, $activitySubjectName, $activitySubjectId, $title = null)
    {
        $frontController = Zend_Controller_Front::getInstance();
        $request = $frontController->getRequest();
        $lessonId = (int)$request->getParam('lesson_id', 0);
        $subForm = $request->getParam('subForm', 0);
        $session = new Zend_Session_Namespace('wiki_LessonForm');
        if($lessonId) {
            $where = array();
            $where['subject_id = ?'] = $activitySubjectId;
            $where['subject_name = ?'] = $activitySubjectName;
            $where['lesson_id = ?'] = $lessonId;
            $lessonWiki = $this->getOne($this->find($where));
            $session->module = $lessonWiki->id;
            $form->clearElements();
            return;
        } elseif($subForm == 'step2' && !isset($_POST['module'])) {
            $form->addElement('text', 'moduleTitle', array(
                'readonly' => true,
                'value' => $title
            ));
            $view = $frontController->getParam('bootstrap')->getResource('view');
            $form->addElement(new HM_Form_Element_WikiEditor('body', array(
                'connectorUrl' => $view->url(array(
                    'module' => 'storage',
                    'controller' => 'index',
                    'action' => 'elfinder',
                    'subject' => $activitySubjectName,
                    'subject_id' => $activitySubjectId
                )),
                'lang' => Zend_Registry::get('config')->wysiwyg->params->lang,
                'disableLinks' => true,
                'value' => 'h1. '.ucfirst($title)
            )));

            $lessonWikiData = array();
            $lessonWikiData['title'] = $title;
            $lessonWikiData['subject_name'] = $activitySubjectName;
            $lessonWikiData['subject_id'] = $activitySubjectId;

            $lessonWiki = $this->insert($lessonWikiData);
            $session->module = $lessonWiki->id;
        }
        if(isset($_POST['body'])) {
            $session->body = $_POST['body'];
        }
        if(isset($session->module)) {
            $form->addElement('hidden', 'module', array(
                'value' => $session->module
            ));
        }
    }

    public function onLessonUpdate($lesson, $form)
    {
        $session = new Zend_Session_Namespace('wiki_LessonForm');
        if(isset($session->module)) {
            $lessonWiki = $this->getOne($this->find($session->module));
            if($lessonWiki && $lessonWiki->id && !$lessonWiki->lesson_id) {
                $body = $this->getService('WikiArchive')->insert(array(
                    'author' => $this->getService('User')->getCurrentUserId(),
                    'article_id' => $lessonWiki->id,
                    'body' => $session->body
                ));
                $this->update(array(
                    'lesson_id' => $lesson->SHEID,
                    'id' => $lessonWiki->id,
                    'changed' => $body->created
                ));
            }
            $params = $lesson->getParams();
            $params['module_id'] = $session->module;
            $lesson->setParams($params);
            unset($session->module);
        }
    }

    public function getLessonModelClass()
    {
        return "HM_Lesson_Wiki_WikiModel";
    }

    public function delete($id)
    {
        $id = (int) $id;

        $this->getService('WikiArchive')->deleteBy(array('article_id = ?' => $id));

        parent::delete($id);
    }

    public function createEvent(\HM_Model_Abstract $model) {
        $event = $this->getService('ESFactory')->newEvent(
            $model,
            array('title','created','changed'),
            $this
        );
        return $event;
    }

    public function getRelatedUserList($id) {
        $wikiPage = $this->find($id)->current();
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select();
        $result = array();
        $subjectId = intval($wikiPage->subject_id);
        if ($subjectId == 0) {
            $deanSelect = clone $select;
            $deanSelect->from(array('d' => 'deans'),array('u' => 'd.MID'))
                ->group('d.MID');
            $stmt = $deanSelect->query();
            $rows = $stmt->fetchAll();
            foreach ($rows as $uRow) {
                $result[] = $uRow['u'];
            }
        } else {
            $teachersSubselect = clone $select;
            $studentsSubselect = clone $select;
            $unionSelect = clone $select;
            $teachersSubselect->from(array('s' => 'subjects'), array())
                ->join(array('t' => 'Teachers'), 't.CID = s.subid AND s.subid='.$subjectId, array('UserId' => 't.MID'));
            $studentsSubselect->from(array('s' => 'subjects'), array())
                ->join(array('st' => 'Students'), 'st.CID = s.subid AND s.subid='.$subjectId, array('UserId' => 'st.MID'));
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
            $params = $ev->getParameters();
            $article = $params['article'];
            $service = $ev->getSubject();

            /*@var $event Es_Entity_AbstractEvent */
            $event = $service->createEvent($article);
            $subject = null;
            $articleRelatedSubject = intval($article->subject_id);
            if ($articleRelatedSubject) {
                $subject = $service->getService('Subject')->find((int)$articleRelatedSubject)->current();
            }
            $data = array();
            $user = $service->getService('User')->getCurrentUser();
            if ($article->changed === null || intval($article->changed) == 0) {
                if ($articleRelatedSubject == 0) {
                    $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_WIKI_ADD_PAGE);
                    $eventGroup = $service->getService('ESFactory')->eventGroup(
                        HM_Wiki_WikiArticlesService::EVENT_GROUP_NAME_PREFIX, 0
                    );
                } else {
                    $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_WIKI_INTERNAL_ADD_PAGE);
                    $eventGroup = $service->getService('ESFactory')->eventGroup(
                        HM_Wiki_WikiArticlesService::EVENT_GROUP_NAME_PREFIX, $articleRelatedSubject
                    );
                    $data['course_name'] = $subject->name;
                    $data['course_id'] = $subject->getPrimaryKey();
                    $event->setParam('course_name', $subject->name);
                    $event->setParam('course_id', (int)$subject->getPrimaryKey());
                }
            } else {
                if ($articleRelatedSubject == 0) {
                    $eventGroup = $service->getService('ESFactory')->eventGroup(
                        HM_Wiki_WikiArticlesService::EVENT_GROUP_NAME_PREFIX, 0
                    );
                    $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_WIKI_MODIFY_PAGE);
                } else {
                    $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_WIKI_INTERNAL_MODIFY_PAGE);
                    $eventGroup = $service->getService('ESFactory')->eventGroup(
                        HM_Wiki_WikiArticlesService::EVENT_GROUP_NAME_PREFIX, $articleRelatedSubject
                    );
                    $data['course_name'] = $subject->name;
                    $data['course_id'] = $subject->getPrimaryKey();
                    $event->setParam('course_name', $subject->name);
                    $event->setParam('course_id', (int)$subject->getPrimaryKey());
                }
            }
            $event->setParam('author', $user->getName());
            $event->setParam('author_id', $user->getPrimaryKey());
            
            $authorAvatar = '/'.ltrim($user->getPhoto(), '/');
            $event->setParam('author_avatar', $authorAvatar);
            
            $eventGroup->setData(json_encode($data));
            $event->setGroup($eventGroup);
            $esService = $service->getService('EventServerDispatcher');
            $esService->getService('EventServerDispatcher')->trigger(
                Es_Service_Dispatcher::EVENT_PUSH,
                $service,
                array('event' => $event)
            );
        };
    }

}
