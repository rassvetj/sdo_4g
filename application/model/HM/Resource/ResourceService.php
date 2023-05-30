<?php
class HM_Resource_ResourceService extends HM_Service_Abstract implements Es_Entity_Trigger
{

    const EVENT_GROUP_NAME_PREFIX = 'ADD_MATERIAL_TO_COURSE'; 

    public function insert($data)
    {
        $data['created'] = $data['updated'] = $this->getDateTime();
        $data['created_by'] = $this->getService('User')->getCurrentUserId();

        if (is_array($data['related_resources']) && count($data['related_resources'])) {
            $relatedResources = array_unique($data['related_resources']);
            $this->propagateRelatedResources($resourseId, $relatedResources);
            $data['related_resources'] = implode(',', $relatedResources);
        }

        return parent::insert($data);
    }

    public function update($data)
    {
        // обновляем связанные ресурсы только если они пришли с POSTом в виде массива
        if (is_array($data['related_resources'])) {
            $relatedResources = array_unique($data['related_resources']);
            if (false !== ($key = array_search($data['resource_id'], $relatedResources))) unset($relatedResources[$key]);
            $this->propagateRelatedResources($data['resource_id'], $relatedResources);
            $data['related_resources'] = implode(',', $relatedResources);
        }

        $data['updated'] = $this->getDateTime();
        return parent::update($data);
    }

    public function prepareMultipleFiles($resource, $fileResource, $populatedFiles = array())
    {
        // Формируем zip и указываем файлом parent'а
        $zip = new ZipArchive();
        $res = $zip->open(realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$resource->resource_id.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($res) {
            foreach ($populatedFiles as $populatedFile) {
                $zip->addFile($populatedFile->getPath(), iconv('UTF-8', 'cp866', $populatedFile->getDisplayName()));
            }
            if ($fileResource->isReceived()) {
                $fileNames = $fileResource->getFileName();
                if (!is_array($fileNames)) $fileNames = array($fileNames); 
                foreach($fileNames as $filename) {
	                $zip->addFile($filename, iconv('UTF-8', 'cp866', basename($filename)));
                }
            }
        }
        $zip->close();

        return realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$resource->resource_id.'.zip';
    }

    public function updateDependentResources($resource, $fileResource, $populatedFiles = array())
    {
        // Заносим каждый файл по отдельности
        $count = 0;
        $fileSizes = $fileNames = array();
        if ($fileResource->isReceived()) {
            $fileSizes = $fileResource->getFileSize();
            if (!is_array($fileSizes)) $fileSizes = array($fileSizes);
            $fileNames = $fileResource->getFileName();
            if (!is_array($fileNames)) $fileNames = array($fileNames);
        }

        $values = $resource->getValues();
        $values['parent_id'] = $values['resource_id'];
        unset($values['resource_id']);

        foreach($fileNames as $index => $filename) {
            $values['filename'] = basename($filename);
            $values['volume'] = $fileSizes[$index];
            $values['filetype'] = HM_Files_FilesModel::getFileType($values['filename']);

            $item = $this->getService('Resource')->insert($values);
            if ($item) {
                $filter = new Zend_Filter_File_Rename(
                    array(
                        'source' => $filename,
                        'target' => realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$item->resource_id,
                        'overwrite' => true
                    )
                );
                $filter->filter($filename);
            }
            $count++;
        }
        if (count($populatedFiles)) {
            foreach ($populatedFiles as $populatedFile) {
                $values['filename'] = $populatedFile->getDisplayName();
                $values['volume'] = 0; // @todo
                $values['filetype'] = HM_Files_FilesModel::getFileType($values['filename']);
                $item = $this->getService('Resource')->insert($values);   
                @copy($populatedFile->getPath(), realpath(Zend_Registry::get('config')->path->upload->resource) . '/' . $item->resource_id);
                $count++;             
            }
        }
        return $count;
    }

    public function propagateRelatedResources($resourseId, $relatedResources)
    {
        if (!count($relatedResources)) $relatedResources = array(0);
        $resources = $this->fetchAll(array('resource_id IN (?)' => $relatedResources));
        foreach ($resources as $resource) {
            $data = $resource->getData();
            $existingResources = !empty($resource->related_resources) ? explode(',', $resource->related_resources) : array();
            if (!in_array($resourseId, $existingResources)) {
                $existingResources[] = $resourseId;
            }
            $data['related_resources'] = implode(',', $existingResources);
            parent::update($data);
        }
        // @todo: оптимизировать. сейчас цикл почти по всем ресурсам:(
        if (count($resources = $this->fetchAll(array('resource_id NOT IN (?)' => $relatedResources)))) {
            foreach ($resources as $resource) {
                $data = $resource->getData();
                $existingResources = !empty($resource->related_resources) ? explode(',', $resource->related_resources) : array();
                $key = array_search($resourseId, $existingResources);
                if ($key !== false) {
                    unset($existingResources[$key]);
                }
                $data['related_resources'] = implode(',', $existingResources);
                parent::update($data);
            }
        }
    }

    public function delete($id)
    {
        // Удаляем детей
        $collection = $this->getService('Resource')->fetchAll(
            $this->quoteInto('parent_id = ?', $id)
        );

        if(count($collection)) {
            foreach($collection as $item) {
                $this->delete($item->resource_id);
            }
        }

        //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'),
            array($id,HM_Tag_Ref_RefModel::TYPE_RESOURCE)));

        // удаляем связи с этим ресурсом из всех других ресурсов
        $resource = $this->find($id)->current();
        if (!empty($resource->related_resources)) {
            $this->propagateRelatedResources($id, array());
        }

        unlink(Zend_Registry::get('config')->path->upload->resource . $id);
        return parent::delete($id);
    }

    public function isEditable($subjectIdFromResource, $subjectId, $status){

        $all = array(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER);
        $role = $this->getService('User')->getCurrentUserRole();
        if(in_array($role, $all)){
            return true;
        }
        if($subjectId == 0){
            return false;
        }

        if(
            $this->getService('Acl')->inheritsRole($role, HM_Role_RoleModelAbstract::ROLE_TEACHER)
            //$role == HM_Role_RoleModelAbstract::ROLE_TEACHER
            && $status == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL && $subjectIdFromResource == $subjectId){
                return true;
            }

        return false;
    }

    public function printContent($resourceModel)
    {
        switch ($resourceModel->type) {
        case HM_Resource_ResourceModel::TYPE_EXTERNAL:
            $filePath = Zend_Registry::get('config')->path->upload->resource . $resourceModel->resource_id;
            $resourceReader = new HM_Resource_Reader($filePath, $resourceModel->filename);
            $resourceReader->readFile();
            break;
        case HM_Resource_ResourceModel::TYPE_HTML:
            echo $resourceModel->content;
            break;
        case HM_Resource_ResourceModel::TYPE_URL:
            echo $resourceModel->url;
            break;
        }
    }


    public function createLesson($subjectId, $resourceId, $section = false, $order = false)
    {
        if (empty($section)) {
            $section = $this->getService('Section')->getDefaultSection($subjectId);
            if (empty($order)) {
                $currentOrder = $this->getService('Section')->getCurrentOrder($section);
                $order = ++$currentOrder;
            }
        }

        $lessons = $this->getService('Lesson')->fetchAll(
            $this->getService('Lesson')->quoteInto(
                array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                array(HM_Event_EventModel::TYPE_RESOURCE, '%module_id='.$resourceId.';%', $subjectId)
            )
        );
        if (!count($lessons)) {
            $resource = $this->getOne($this->getService('Resource')->find($resourceId));
            if ($resource) {
                $values = array(
                    'title' => $resource->title,
                    'descript' => $resource->description,
                    'begin' => date('Y-m-d 00:00:00'),
                    'end' => date('Y-m-d 23:59:00'),
                    'createID' => 1,
                    'createDate' => date('Y-m-d H:i:s'),
                    'typeID' => HM_Event_EventModel::TYPE_RESOURCE,
                    'vedomost' => 1,
                    'CID' => $subjectId,
                    'startday' => 0,
                    'stopday' => 0,
                    'timetype' => 2,
                    'isgroup' => 0,
                    'teacher' => 0,
                    'params' => 'module_id='.(int) $resource->resource_id.';',
                    'all' => 1,
                    'cond_sheid' => '',
                    'cond_mark' => '',
                    'cond_progress' => 0,
                    'cond_avgbal' => 0,
                    'cond_sumbal' => 0,
                    'cond_operation' => 0,
                    'isfree' => HM_Lesson_LessonModel::MODE_FREE,
                    'section_id' => $section->section_id,
                    'order' => $order,
                );
                $lesson = $this->getService('Lesson')->insert($values);
                $students = $lesson->getService()->getAvailableStudents($subjectId);
                if (is_array($students) && count($students)) {
                    $this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
                }
                $this->getService('EventDispatcher')->notify(
                    new sfEvent($this, __CLASS__.'::esPushTrigger', array('lesson' => $lesson))
                );
            }
        }
    }

/*    public function deleteLesson($subject, $resourceId)
    {
        $lessons = $this->getService('Lesson')->fetchAll(
            $this->getService('Lesson')->quoteInto(
                array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                array(HM_Event_EventModel::TYPE_RESOURCE, '%module_id=' . $resourceId . ';%', $subject->subid)
            )
        );
        if (count($lessons)) {
            foreach($lessons as $lesson) {
                $this->getService('Lesson')->delete($lesson->SHEID);
            }
        }
}*/

    public function clearLesson($subject, $resourceId)
    {
        if($subject == null){
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    array('typeID = ?', " AND params LIKE ?"),
                    array(HM_Event_EventModel::TYPE_RESOURCE, '%module_id=' . $resourceId . ';%')
                )
            );
        }else{
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                    array(HM_Event_EventModel::TYPE_RESOURCE, '%module_id=' . $resourceId . ';%', $subject->subid)
                )
            );
        }

        if (count($lessons)) {
            $subjectNew = null;
            foreach($lessons as $lesson) {
                $subjectNew = $this->getService('Subject')->getOne($this->getService('Subject')->find($lesson->CID));
                $this->getService('Lesson')->deleteBy(array('SHEID = ?' => $lesson->SHEID, 'isfree IN (?)' => new Zend_Db_Expr(implode(',', array(HM_Lesson_LessonModel::MODE_FREE, HM_Lesson_LessonModel::MODE_FREE_BLOCKED)))));
                $this->getService('Lesson')->updateWhere(array('params' => ''), array('SHEID = ?' => $lesson->SHEID, 'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN));
            }
        }

    }

    public function getDefaults()
    {
        $user = $this->getService('User')->getCurrentUser();
        return array(
            'type' => HM_Resource_ResourceModel::TYPE_HTML,
            'created' => $this->getDateTime(),
            'updated' => $this->getDateTime(),
            'created_by' => $user->MID,
            'status' => HM_Resource_ResourceModel::STATUS_PUBLISHED,
        );
    }

    public function copyContent($resource, $toResourceId)
    {
        if ($resource) {
            if ($resource->type == HM_Resource_ResourceModel::TYPE_FILESET) {
                $from = realpath(Zend_Registry::get('config')->path->upload->public_resource).'/'.$resource->resource_id.'/';
                $to = realpath(Zend_Registry::get('config')->path->upload->public_resource).'/'.$toResourceId.'/';

                try {
                    $this->getService('Course')->copyDir($from, $to);
                } catch (HM_Exception $e) {
                    // что-то не скопировалось
                }


            } else {
                $from = realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$resource->resource_id;
                $to = realpath(Zend_Registry::get('config')->path->upload->resource).'/'.$toResourceId;

                if (file_exists($from)) {
                    if (!is_readable($from)) {
                        throw new HM_Exception(sprintf(_('Нет прав на чтение %s'), $from));
                    }

                    if (!copy($from, $to)) {
                        throw new HM_Exception(sprintf(_('Невозможно скопировать файл %s в %s'), $from, $to));
                    }
                }
            }
        }
    }

    public function copy($resource, $toSubjectId = null, $newParentId = null)
    {
        if ($resource) {
            if (null !== $toSubjectId) {
                $resource->subject_id = $toSubjectId;
            }
            if (null !== $newParentId) {
                $resource->parent_id = $newParentId;
            }

            $newResource = $this->insert($resource->getValues(null, array('resource_id')));

            if ($newResource) {

                $this->copyContent($resource, $newResource->resource_id);

                $classifiers = $this->getService('ClassifierLink')->fetchAll(
                    $this->quoteInto(
                        array('item_id = ?', ' AND type = ?'),
                        array($resource->resource_id, HM_Classifier_Link_LinkModel::TYPE_RESOURCE)
                    )
                );

                if (count($classifiers)) {
                    foreach($classifiers as $classifier) {
                        $this->getService('Classifier')->linkItem($newResource->resource_id, HM_Classifier_Link_LinkModel::TYPE_RESOURCE, $classifier->classifier_id);
                    }
                }

                $this->getService('TagRef')->copy(HM_Tag_Ref_RefModel::TYPE_RESOURCE, $resource->resource_id, $newResource->resource_id);

                if ($newResource->subject_id > 0) {
                    $this->getService('SubjectResource')->insert(array('subject_id' => $newResource->subject_id, 'resource_id' => $newResource->resource_id));
                }
            }
            return $newResource;
        }
        return false;
    }

    /**
     * Set related resources
     *
     * @param $relatedResources array
     *
     * @return array
     */
    public function setDefaultRelatedResources($relatedResources)
    {
        $return = array();
        if (!empty($relatedResources)) {
            $relatedResources = explode(',', $relatedResources);
            array_walk($relatedResources, function(&$resource){$resource = (int) $resource;});
            if (count($resources = $this->getService('Resource')->fetchAll(array('resource_id IN (?)' => $relatedResources)))) {
                foreach ($resources as $resource) {
                    $return[$resource->resource_id] = sprintf('#%s: %s', $resource->resource_id, $resource->title);
                }
            }
        }
        return $return;
    }

    public function linkClassifiers($resourceId, $classifiers)
    {
        $classifiers = array_unique($classifiers);
        $this->getService('Classifier')->unlinkItem($resourceId, HM_Classifier_Link_LinkModel::TYPE_RESOURCE);
        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                if ($classifierId > 0) {
                    $this->getService('Classifier')->linkItem($resourceId, HM_Classifier_Link_LinkModel::TYPE_RESOURCE, $classifierId);
                }
            }
        }
        return true;
    }

    public function getResourceRevision($resourceId, $revisionId)
    {
        $resource = $this->getOne($this->find($resourceId));

        if (!$resource) {
            return false;
        }

        if ($revisionId && ($revision = $this->getService('ResourceRevision')->find($revisionId)->current())) {
            foreach (HM_Resource_Revision_RevisionService::getRevisionableAttributes() as $key) {
                $resource->$key = $revision->$key;
            }
        }
        return $resource;
    }

    static public function getIconClass($type, $filetype, $filename, $activityType)
    {
        switch ($type) {
        case HM_Resource_ResourceModel::TYPE_URL:
            return 'resource-' . HM_Resource_ResourceModel::TYPE_URL;
            break;
        case HM_Resource_ResourceModel::TYPE_FILESET:
        case HM_Resource_ResourceModel::TYPE_HTML:
            return 'resource-' . HM_Resource_ResourceModel::TYPE_HTML;
            break;
        case HM_Resource_ResourceModel::TYPE_EXTERNAL:
            if (empty($filetype)) {
                return 'resource-filetype-' . HM_Files_FilesModel::getFileType($filename);
            } else {
                return 'resource-filetype-' . $filetype;
            }
            break;
        case HM_Resource_ResourceModel::TYPE_ACTIVITY:
            return 'resource-activitytype-' . $activityType;
            break;
        default:
            return 'resource-' . $type;
            break;
        }
    }

    public function createEvent(\HM_Model_Abstract $model) {
        $event = $this->getService('ESFactory')->newEvent(
            $model, array('title', 'created'), $this
        );
        $user = $this->getService('User')->getCurrentUser();
        $event->setParam('author', $user->getName());
        $event->setParam('author_id', $user->getPrimaryKey());
        $userAvatar = '/'.ltrim($user->getPhoto(), '/');
        $event->setParam('user_avatar', $userAvatar);
        return $event;
    }

    public function getRelatedUserList($id) {
        $assigns = $this->getService('LessonAssign')->fetchAll($this->quoteInto('SHEID = ? AND MID > 0', $id));
        $result = array();
        if ($assigns->count() > 0) {
            foreach ($assigns as $student) {
                $result[] = intval($student->MID);
            }
        }
        return $result;
    }

    public function triggerPushCallback() {
        return function($ev) {
            $parameters = $ev->getParameters();
            $lesson = $parameters['lesson'];
            $service = $ev->getSubject();
            $event = $service->createEvent($lesson);
            $subject = $service->getService('Subject')->find(intval($lesson->CID))->current();
            $event->setParam('course_name', $subject->getName());
            $event->setParam('course_id', $subject->getPrimaryKey());
            
            $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_ADD_MATERIAL);

            $eventGroup = $service->getService('ESFactory')->eventGroup(
                HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, (int)$subject->getPrimaryKey()
            );
            $eventGroup->setData(json_encode(
                array(
                    'course_name' => $subject->getName(),
                    'course_id' => $subject->getPrimaryKey()
                )
            ));
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
