<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_UserActivityByCourseBlock extends HM_View_Infoblock_ScreenForm
{
    const MAX_ITEMS = 10;

    protected $id = 'userActivityByCourse';

    public function userActivityByCourseBlock($title = null, $attribs = null, $options = null)
    {

            $userId = $this->getService('User')->getCurrentUserId();
            
            $subject = $options['subject'];
   
            $serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject');
            $sec = Array();

                $selectLess = $serviceSubject->getSelect();
                $selectLess->from(array('l' => 'lessons'),
                    array(
                        'CID' => 'l.cid',
                        'SHEID' => 'l.sheid',
                        'title' => 'l.title',
                        )
                    )->where('l.cid = ? ',$subject->subid);    
                    
                        if ($rowsetLess = $selectLess->query()->fetchAll()) {  
                            foreach ($rowsetLess as $rowLess) {
                            
                            /*Подсчет времени по каждому модулю*/
                            
                                $select = $serviceSubject->getSelect();
                                $select->from(array('s' => 'scorm_tracklog'),
                                array(
                                    'mid' => 's.mid',
                                    'lesson_id' => 's.lesson_id',
                                    'timer' => 'SUM(UNIX_TIMESTAMP(s.stop) - UNIX_TIMESTAMP(s.start))',
                                    )
                                )->where('mid ='. $userId .' AND lesson_id ='. $rowLess['SHEID'])
                                 ->group(array('mid', 'lesson_id'));
                                
                                    if ($rowset = $select->query()->fetchAll()) {
                                        foreach ($rowset as $row) {                                        
                                        /* Формирование массива для вывода данных*/                                      
                                            $sec[$subject->subid]=$sec[$subject->subid]+$row['timer'];
                                        }
                                    }
                            }                    
                        }
      
                $this->view->sec = $sec;
            
        

         /* Log User */
         $logs = array();
        
            $selectTry = $serviceSubject->getSelect();
            $selectTry->from(array('u' => 'loguser'),
            array(
                'CID' => 'u.cid',
                'MID' => 'u.mid',
                )
             )->where('CID = '.$subject->subid.' AND MID = '.$userId); // Количество попыток пользователя в пределах курса
                if ($rowLog = $selectTry->query()->fetchAll()) {
                   
                       $logs[$subject->subid]=count($rowLog);
                    
                    }
                $this->view->logs = $logs;    
        
         /*Forums Messages*/
         $my_mess = array();
        
            $selectList = $serviceSubject->getSelect();
            $selectList->from(array('m' => 'forums_list'),
            array(
                 'subject_id' => 'm.subject_id',
                 'forum_id' => 'm.forum_id',
                 )
            )->where('subject_id = '.$subject->subid);  // Получение списка форумов в пределах курса.
                 if ($rowList = $selectList->query()->fetchAll()) { 
                         foreach ($rowList as $rList) {            
                             $selectMess = $serviceSubject->getSelect();
                             $selectMess->from(array('mes' => 'forums_messages'),
                             array(
                                 'user_id' => 'mes.user_id',
                                 'forum_id' => 'mes.forum_id',
                                 )
                             )->where('user_id = '.$userId. ' AND forum_id = '.$rList['forum_id']); 
                            
                          /*Подсчет количества сообщений в пределах курса*/
                         
                            if ($rowMess = $selectMess->query()->fetchAll()) {     
                                 
                                    $my_mess[$subject->subid]=$my_mess[$subject->subid]+count($rowMess);
                                
                            }
                        }
                }
         
         $this->view->my_mess = $my_mess;    

        $content = $this->view->render('userActivityByCourseBlock.tpl');
        return parent::screenForm($title, $content, $attribs);
    }

   
}