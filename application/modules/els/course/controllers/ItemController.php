<?php
class Course_ItemController extends HM_Controller_Action
{

	const WRAPPER = '/wrapper';

    public function viewAction()
    {
        
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        //$this->getResponse()->setHeader('X-UA-Compatible', 'IE=EmulateIE7', true);
        //$this->getHelper('viewRenderer')->setNoRender();

        $itemId    = $this->_getParam('item_id', 0);
        $subjectId = $this->_getParam('subject_id', 0);
        $lessonId = $this->_getParam('lesson_id', 0);

        $itemService = $this->getService('CourseItem');
        
        $item        = $itemService->getOne($itemService->find($itemId)); 

        if ($item && $item->cid) {
            $course = $this->getOne($this->getService('Course')->find($item->cid));
            if ($course && $course->emulate) {
                $this->getResponse()->setHeader('X-UA-Compatible', 'IE=EmulateIE'.(int) $course->emulate, true);
            }
        }

        $currentRole = $this->getService('User')->getCurrentUserRole();
        $currentId   = $this->getService('User')->getCurrentUserId();
        
        if($item){
            switch($currentRole){
                case HM_Role_RoleModelAbstract::ROLE_TEACHER:
/*                    $result = $this->getService('Course')->isTeacher($item->cid, $currentId);
                    if($subjectId != 0) $result = $result & $this->getService('Subject')->isTeacher($item->cid, $currentId);            
                    if($result === false) throw new HM_Permission_Exception(_("Нет прав для доступа к элементу учебного модуля"));
*/                    break;
                case HM_Role_RoleModelAbstract::ROLE_TUTOR:
                    break;
                case HM_Role_RoleModelAbstract::ROLE_STUDENT:
/*                    $result = $this->getService('Course')->isStudent($item->cid, $currentId);
                    if($subjectId != 0) $result = $result & $this->getService('Subject')->isStudent($item->cid, $currentId);            
                    if($result === false) throw new HM_Permission_Exception(_("Нет прав для доступа к элементу учебного модуля"));
*/                    break;
                case HM_Role_RoleModelAbstract::ROLE_DEAN:
                    break;
                case HM_Role_RoleModelAbstract::ROLE_MANAGER:
                    break;
                case HM_Role_RoleModelAbstract::ROLE_DEVELOPER:
                    break;
                default:
                    if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
                        $result = $this->getService('Course')->isStudent($item->cid, $currentId);
                        if($subjectId != 0) $result = $result & $this->getService('Subject')->isStudent($item->cid, $currentId);
                        if($result === false) throw new HM_Permission_Exception(_("Нет прав для доступа к элементу учебного модуля"));
                    } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
                        $result = $this->getService('Course')->isTeacher($item->cid, $currentId);
                        if($subjectId != 0) $result = $result & $this->getService('Subject')->isTeacher($item->cid, $currentId);
                        if($result === false) throw new HM_Permission_Exception(_("Нет прав для доступа к элементу учебного модуля"));
                    } else {
                        throw new HM_Permission_Exception(_("Нет прав для доступа к элементу учебного модуля"));
                    }
                    break;
            }
            
            if(!($item instanceof HM_Course_Item_Empty_EmptyModel)){
                $data = array('mid'        => $currentId,
                              'cid'        => $item->cid,
                              'date'       => (string) new Zend_Date(),
                              'subject_id' => $subjectId,
                              'item'       => $itemId,
                              'lesson_id'  => $lessonId   
                );

                $this->getService('CourseItemHistory')->insert($data);
                $this->getService('CourseItemCurrent')->updateCurrent($currentId, $subjectId, $item->cid, $itemId, $lessonId);
            }
            
            $executeUrl = $item->getExecuteUrl();
            $this->view->debug = $executeUrl;
            $this->view->lessonId = $lessonId;
            $this->view->item = $item;
            $this->view->courseId = $item->cid;
            $this->view->executeUrl = false;
            if($executeUrl !== false){
                //$this->_redirector->gotoUrl($this->view->baseUrl($executeUrl));
                $this->view->executeUrl = $this->view->baseUrl($executeUrl);
            }

            // add aicc params
            $separator = '?';
            if (false !== strstr($this->view->executeUrl, $separator)) {
                $separator = '&';
                if (substr($this->view->executeUrl, -1) == '?') {
                    $separator = '';
                }
            }
/*
            $this->view->executeUrl .= $separator . 'aicc_url=' . urlencode($this->view->serverUrl($this->view->url(
                array(
                    'module' => 'course',
                    'controller' => 'api',
                    'action' => 'hacp',
                    'course_id' => $this->view->item->cid,
                    'item_id' => $this->view->item->oid,
                    'module_id' => $this->view->item->module,
                    'lesson_id' => $this->view->lessonId
                )
            )));
*/
            // todo: manage hacp code
            if (false !== strstr($item->getContentType(), HM_Library_Item_FileItemModel::CONTENT_AICC)) {
                //$this->view->executeUrl .= $separator . 'AICC_SID='.$item->module.'&AICC_URL=' . urlencode($this->view->serverUrl('/hacp_datamodel.php?subject_id='.$subjectId.'&lesson_id='.$lessonId));
                //$this->view->executeUrl .= $separator . 'AICC_SID='.$item->module.'&AICC_URL=' . urlencode($this->view->serverUrl($this->view->url(array('action' => 'hacp', 'controller' => 'api', 'module' => 'course', 'subject_id' => $subjectId, 'lesson_id' => $lessonId))));
                $this->view->executeUrl .= $separator . 'AICC_URL=' . urlencode($this->view->serverUrl(self::WRAPPER.$this->view->url(array('action' => 'hacp', 'controller' => 'api', 'module' => 'course', 'subject_id' => $subjectId, 'lesson_id' => $lessonId, 'time' => time())))) . '&AICC_SID='.$item->module;
            }

         
        } else {
            throw new HM_Exception_Data(_('Элемент учебного модуля не найден!'));
        }
        
        
    }


}


