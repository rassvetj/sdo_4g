<?php
class Test_ListController extends HM_Controller_Action_Crud
{

    protected $service      = 'Subject';
    protected $idParamName  = 'subject_id';
    protected $idFieldName  = 'subid';
    protected $id           = 0;
    
    public $amount = array();
    
    public function init()
    {
        parent::init();
        $subjectId = (int) $this->_getParam('subject_id',0);
        $courseId  = (int) $this->_getParam('course_id',0);

        if ( $subjectId && !$courseId ) {

            if (!$this->isAjaxRequest()) {

                $this->id = (int) $this->_getParam($this->idParamName, 0);
                $subject  = $this->getOne($this->getService($this->service)->find($this->id));

                $this->view->setExtended(
                    array(
                        'subjectName'        => $this->service,
                        'subjectId'          => $this->id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject'            => $subject
                    )
                );

                $this->_subject = $subject;
            }
        }
    }
    
    public function indexAction()
    {
        
    	$courseId=$this->_request->getParam('course_id', 0);
    
        $select = $this->getService('Test')->getSelect();
        $select->from(array(
                    't' => 'test'), 
        			array(
                        'tid'    => 't.tid',
                        'name'   => 't.title',
        			    'amount' => 't.tid'
        			)
               )
       	       ->where('t.cid = ?', $courseId)
               ->group(array('t.tid', 't.title'));
               
       $grid = $this->getGrid($select, 
                              array(
                                 'tid'    => array('hidden' => true),
                                 'name'   => array('title'  => _('Название')),
                                 'amount' => array('title'  => _('Количество вопросов'))
                              ), 
                   			  array('name'   => null,
                                    'amount' => null)
        
        );
         
        
        $grid->updateColumn('name', 
                            array('callback' => array('function' => array($this,'updateTitle'),
                                                      'params'   => array('{{name}}', 
                                                                        $courseId, 
                                                                        '{{tid}}'
                                                                  )
                                                )
                            )
        );
        
        $grid->updateColumn('amount', 
                            array('callback' => array('function' => array($this,'updateAmount'),
                                                      'params'   => array('{{amount}}', $select)
                                                )
                            )
        );
        
        
        $grid->addAction(array('module'     => 'test', 
                               'controller' => 'list', 
                               'action'     => 'edit'
                         ),
                         array('tid'),
                         $this->view->icon('edit')
        );
        
        $grid->addAction(array('module'     => 'test', 
                               'controller' => 'list', 
                               'action'     => 'delete'
                         ),
                         array('tid'),
                         $this->view->icon('delete')
        );
        
        $grid->addMassAction(array('module'     => 'test', 
                                   'controller' => 'list', 
                                   'action'     => 'delete-by'
                             ),
                             _('Удалить'),
                             _('Вы уверены?')
        );
        
        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
     
    }
    
    
    
    public function newAction(){
        $courseId = $_GET['cid'] = (int) $this->_getParam('course_id', 0);
        $this->_setParam('cid', $courseId);

        if($this->_getParam('c') == 'edit_post2'){
            $this->_setParam('c', 'edit_post2');
        }
        else{
            $this->_setParam('c', 'add');
        }

        $GLOBALS[brtag]='~~';

        $s = Zend_Registry::get('session_namespace_unmanaged')->s;

        $params = $this->_getAllParams();
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                $$key = $value;
            }
        }

        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));

        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');

        $currentDir = getcwd();

        ob_start();
        chdir(APPLICATION_PATH.'/../public/unmanaged/');

        $res = include(APPLICATION_PATH.'/../public/unmanaged/test_test.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));

        chdir($currentDir);
         
        if($res=='Ok' && $this->_getParam('c') == 'edit_post2'){

            $this->_flashMessenger->addMessage(_('Тест успешно добавлен!'));
            $this->_redirector->gotoSimple('index', 'list', 'test', array('course_id' =>array('course_id' => $courseId )));

        }elseif($this->_getParam('c') == 'edit_post2'){
            $this->_flashMessenger->addMessage(_('Возникла ошибка. Тест не был добавлен!'));
            $this->_redirector->gotoSimple('new', 'list', 'test', array('course_id' =>array('course_id' => $courseId )));

        }


        $this->view->content = $content;
         
    }
    
    

    public function editAction(){
        $courseId = $_GET['cid'] = (int) $this->_getParam('course_id', 0);
        $testId = $_GET['tid'] = (int) $this->_getParam('tid', 0);
        $this->_setParam('cid', $courseId);
        $this->_setParam('tid', $testId);

        if($this->_getParam('c') == 'edit_post2'){
            $this->_setParam('c', 'edit_post2');
        }
        else{
            $this->_setParam('c', 'edit2');
        }

        $GLOBALS[brtag]='~~';

        $s = Zend_Registry::get('session_namespace_unmanaged')->s;

        $params = $this->_getAllParams();
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                $$key = $value;
            }
        }

        $paths = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/", APPLICATION_PATH . "/../public/unmanaged/lib/classes")));
        $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
        $currentDir = getcwd();
        ob_start();
        chdir(APPLICATION_PATH.'/../public/unmanaged/');
        $res = include(APPLICATION_PATH.'/../public/unmanaged/test_test.php');
        $content = ob_get_contents();
        ob_end_clean();
        set_include_path(implode(PATH_SEPARATOR, array($paths)));

        chdir($currentDir);
         
        if($res=='Ok' && $this->_getParam('c') == 'edit_post2'){

            $this->_flashMessenger->addMessage(_('Тест успешно изменен!'));
            $this->_redirector->gotoSimple('index', 'list', 'test', array('course_id' =>array('course_id' => $courseId )));

        }elseif($this->_getParam('c') == 'edit_post2'){
            $this->_flashMessenger->addMessage(_('Возникла ошибка. Изменение теста прервано!!'));
            $this->_redirector->gotoSimple('edit', 'list', 'test', array('course_id' =>array('course_id' => $courseId )));

        }

        $this->view->content = $content;
    }
    
    /**
     * Редактирование теста для свободных курсов
     */
    public function editFcAction()
    {
        $testId    = (int) $this->_getParam('test_id',0);
        $subjectId = (int) $this->_getParam('subject_id',0);
        $request   = $this->getRequest();
        
        $schedules = $this->getService('Lesson')->fetchAll(array('CID=?'=>$subjectId));
        $arScedule = (count($schedules))? array_keys($schedules->getList('SHEID', 'title')) : array();
        
        $test = $this->getService('Test')
                     ->getOne(
                               $this->getService('Test')
                                    ->fetchAll(array(
                                                      'test_id=?' => $testId,
                                                      'lesson_id IN (?)' => $arScedule 
                                    )));
        if ( !$test ) {
            $this->_flashMessenger->addMessage(_('Тест не найден'));
            $this->_redirector
                 ->gotoSimple('index','abstract','test',array('subject_id'=>$subjectId));
        }

        $this->_request->setParam('lesson_id', $test->lesson_id);

        $form = new HM_Form_Test();
                        
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $test->title          = $form->getValue('title');
                $test->mode           = $form->getValue('mode');
                $test->lim            = $form->getValue('lim');
                $test->qty            = $form->getValue('qty');
                $test->startlimit     = $form->getValue('startlimit');
                $test->limitclean     = $form->getValue('limitclean');
                $test->timelimit      = $form->getValue('timelimit');
                $test->random         = $form->getValue('random');
                $test->threshold      = $form->getValue('threshold');
                //$test->questres       = $form->getValue('questres');
                //$test->showurl        = $form->getValue('showurl');
                $test->endres         = $form->getValue('endres');
                $test->skip           = $form->getValue('skip');
                $test->allow_view_log = $form->getValue('allow_view_log');
                //$test->comments       = $form->getValue('comments');

                $test = $this->getService('Test')->update($test->getValues());
                
                if ($test) 
                {
                    // сменили заголовок теста - меням заголовок связанного лессона
                    $lesson = $this->getService('Lesson')
                                   ->getOne($this->getService('Lesson')
                                                 ->find($test->lesson_id));
                    if ( $lesson ) {
                        $lesson->title = $form->getValue('title');
                        $this->getService('Lesson')
                             ->update($lesson->getValues());
                    }
                    // сменили заголовок теста - меням заголовок абстрактного теста
                    $testAbstract = $this->getService('TestAbstract')
                                         ->getOne($this->getService('TestAbstract')
                                                       ->find($test->test_id));

                    if ( $testAbstract ) {
                        $testAbstract->title = $form->getValue('title');
                        $this->getService('TestAbstract')
                             ->update($testAbstract->getValues());
                    }
                    
                    $this->getService('TestTheme')
                         ->deleteBy(
                                     $this->getService('TestTheme')
                                          ->quoteInto(
                                                        array('tid = ?' , ' AND cid = ?'),
                                                        array($test->tid, $test->cid)
                                                      )
                                    );
    
                    if ( $form->getValue('questions') == HM_Test_TestModel::QUESTIONS_BY_THEMES_SPECIFIED ) {
                        $this->getService('TestTheme')
                             ->insert(
                                        array(
                                				'tid'       => $test->tid,
                                				'cid'       => $test->cid,
                                				'questions' => serialize($form->getValue('questions_by_theme'))
                                             )
                                     );
                    }
                }
                
                $this->_flashMessenger->addMessage(_('Свойства теста сохранены'));
                $this->_redirector
                     ->gotoSimple('index','abstract','test',array('subject_id'=>$subjectId));
            }
        } else {
            $form->setDefaults($request->getParams());
        }
        
        $form->setDefaults($test->getValues());
        
        $theme = $this->getOne(
                                $this->getService('TestTheme')
                                     ->fetchAll(
                                                 $this->getService('TestTheme')
                                                      ->quoteInto(
                                                                    array('tid = ?', ' AND cid = ?'),
                                                                    array($test->tid, $test->cid)
                                                                  )
                                                )
                               );

        if ($theme && count($theme->getQuestionsByThemes())) {
            $form->setDefault('questions', 1);
        }
        $this->view->title = $test->title;
        $this->view->form  = $form;
    }

    public function deleteAction(){
        $courseId = (int) $this->_getParam('course_id', 0);
        $testId =  (int) $this->_getParam('tid', 0);
        $this->getService('Test')->delete($testId);
        $this->_flashMessenger->addMessage(_('Тест успешно удален!'));
        $this->_redirector->gotoSimple('index', 'list', 'test', array('course_id' =>array('course_id' => $courseId )));
    }

    public function deleteByAction(){
        $courseId = (int) $this->_getParam('course_id', 0);
        $testIds = $this->_request->getParam('postMassIds_grid');
        $testIds = explode(',', $testIds);
        if (! empty($testIds))
        {
            foreach ( $testIds as $value )
            {
                $this->getService('Test')->delete($testId);
            }
        }

        $this->_flashMessenger->addMessage(_('Тесты успешно удалены!'));
        $this->_redirector->gotoSimple('index', 'list', 'test', array('course_id' =>array('course_id' => $courseId )));
    }
    
    
    public function updateTitle($field, $courseId, $testId){
        
        
        return '<a href="' . $this->view->url(array('module' => 'question', 'controller' => 'list', 'action' => 'test', 'test_id' => $testId ) ).'" >' . $this->view->escape($field) . '</a>';
    
    }
    
    public function updateAmount($field, $select){
        $GLOBALS['brtag']='~~';
        if(!isset($this->amount[$field])){
            $select1 = clone $select;
            $select1->reset();
            $select1->from('test', array('tid', 'data'))
                    ->limit($select->getPart('limitcount'), 
                            $select->getPart('limitoffset')
                    );
                    
            $smtp = $select1->query();
            $all  = $smtp->fetchAll(); 
            foreach($all as $val){
                  $this->amount[current($val)] = next($val);
            }    
              

        }
        
        $field = $this->amount[$field];
        
        
        if($field == ''){
            return 0;
        }else{
            $num = substr_count($field,$GLOBALS['brtag']) + 1;
            return $num;
        }
        
    }
    
    
    
    
       
}

