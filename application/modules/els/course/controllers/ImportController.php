<?php
class Course_ImportController extends HM_Controller_Action_Course
{
    const MESSAGE_IMPORT_SUCCESS = 'Данные успешно загружены';
    const MESSAGE_IMPORT_TEST_SUCCESS = 'Данные успешно загружены';

	public function indexAction()
	{
		$courseId = (int) $this->_getParam('course_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);
		$form = new HM_Form_Import();
		$this->view->form = $form;

		$request = $this->getRequest();
		if ($request->isPost() && $valid = $form->isValid($request->getParams())) {
			if ($form->zipfile->isUploaded()) {
				if ($form->zipfile->receive() && $form->zipfile->isReceived()) {
					// Импортирование курса
					$this->form = '';
					$s = Zend_Registry::get('session_namespace_unmanaged')->s;
					$params = $form->getValues();
					if (is_array($params) && count($params)) {
						foreach($params as $key => $value) {
							$$key = $value;
						}
					}

                    if ($subjectId > 0) {
                        $course = $this->getService('Course')->insert(
                            array(
                                'Title' => _('Учебный модуль'),
                                'Status' => HM_Course_CourseModel::STATUS_ACTIVE,
                                'lastUpdateDate' => date('Y-m-d'),
                                'createDate' => date('Y-m-d'),
                                'chain' => $subjectId,
                                'new_window' => 0
                            )
                        );

                        if ($course) {
                            $course = $this->getService('Course')->update(
                                array('CID' => $course->CID, 'Title' => sprintf(_('Учебный модуль #%d'), $course->CID))
                            );

                            $this->getService('Subject')->linkCourse($subjectId, $course->CID);

                            $cid = $GLOBALS['cid'] = $_POST['cid'] = $course->CID;
                        }
                        $GLOBALS['subjectId'] = $subjectId;
                        $GLOBALS['isEAU3']    = (intval($form->getValue('import_type')) == HM_Course_CourseModel::FORMAT_EAU3);
                    }

                    $skillsoft = HM_Provider_ProviderModel::SKILLSOFT;
					$paths = get_include_path();
					set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/")));

					$GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');
                    $GLOBALS['isEAU3']    = (intval($form->getValue('import_type')) == HM_Course_CourseModel::FORMAT_EAU3);

					$currentDir = getcwd();
					ob_start();

					chdir(APPLICATION_PATH.'/../public/unmanaged/teachers/');
					include(APPLICATION_PATH.'/../public/unmanaged/teachers/organization_exp.php');
					$content = ob_get_contents();
					ob_end_clean();
					set_include_path(implode(PATH_SEPARATOR, array($paths)));

					chdir($currentDir);

                    try {
                        if ($cid > 0) {
                            $course = $this->getOne($this->getService('Course')->find($cid));
                            if ($course) {

                                $emulate = $course->emulate;
                                $course = $this->getService('Course')->update(array('CID' => $course->CID, 'emulate' => 0, 'format' => $form->getValue('import_type')));
                                if (file_exists($course->getPath()) && is_dir($course->getPath())) {
                                    $this->getService('Course')->emulate($course->CID, $emulate);
                                }
                            }
                        }
                        $type = HM_Notification_NotificationModel::TYPE_SUCCESS;
                        if ((false === strstr($strMsg, _(self::MESSAGE_IMPORT_SUCCESS))) && (false === strstr($strMsg, _(self::MESSAGE_IMPORT_TEST_SUCCESS)))) {
                            $type = HM_Notification_NotificationModel::TYPE_ERROR;
                        }
                        $this->_flashMessenger->addMessage(array('message' => str_ireplace('<br>', "\n", $strMsg), 'type' => $type));
                    } catch(HM_Exception $e) {
                        $this->_flashMessenger->addMessage(array('message' => $e->getMessage(), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                    }

					//$this->view->content = $content;
					
                    if($subjectId > 0){
                        $this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));
                        $this->getService('Course')->createLesson($this->_subject->subid, $course->CID);
                        $this->_redirector->gotoSimple('courses', 'index', 'subject', array('subject_id' => $subjectId));
                    }
                    $this->_redirector->gotoUrl($page);
                    

				}
			}
		} else {
			$form->setDefault('cid', $courseId);
            $form->setDefault('subject_id', $subjectId);
		}

		/*
		 $courseId = $_GET['CID'] = (int) $this->_getParam('course_id', 0);
		 $this->_setParam('CID', $courseId);

		 $s = Zend_Registry::get('session_namespace_unmanaged')->s;
		 $params = $this->_getAllParams();
		 if (is_array($params) && count($params)) {
		 foreach($params as $key => $value) {
		 $$key = $value;
		 }
		 }

		 $paths = get_include_path();
		 set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/")));

		 $GLOBALS['controller'] = $controller = clone Zend_Registry::get('unmanaged_controller');

		 $currentDir = getcwd();
		 ob_start();

		 chdir(APPLICATION_PATH.'/../public/unmanaged/teachers/');
		 include(APPLICATION_PATH.'/../public/unmanaged/teachers/course_import.php');
		 $content = ob_get_contents();
		 ob_end_clean();
		 set_include_path(implode(PATH_SEPARATOR, array($paths)));

		 chdir($currentDir);

		 $this->view->content = $content;
		 *
		 */
	}

    public function subjectAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));

        $this->view->setExtended(
            array(
                'subjectName' => 'Subject',
                'subjectId' => $subjectId,
                'subjectIdParamName' => 'subject_id',
                'subjectIdFieldName' => 'subid',
                'subject' => $subject
            )
        );

        $this->indexAction();
        $this->view->deleteContextMenu('course');
    }

	public function multipleAction(){
		$dirpath = APPLICATION_PATH . "/../import_courses";
		if(!$dirpath || !is_dir($dirpath) || !$dir=opendir($dirpath))
		throw new HM_Exception(_('Путь к директории не указан, либо неверен'));

		$types = array(2 => 'SCORM', 3 => 'AICC', 4 => eAutor);

		while(($file = readdir($dir)) !== false){
			if($file == '.' || $file == '..' || !is_file($dirpath.'/'.$file)) continue;
			$strMsg = $dirpath.' '.$file.': <br>';
			$packageDir = $dirpath.'/'.$file;

			$zip = new ZipArchive;
			if ($zip->open($packageDir) === TRUE) {
				$zip->extractTo($dirpath.'/folder'.$file);
				$zip->close();
			}
			// определяем тип
			// SCORM define('IMPORT_TYPE_SCORM', 2); IMS_MANIFEST_FILENAME='imsmanifest.xml'
			if(file_exists($dirpath.'/folder'.$file.'/imsmanifest.xml'))
			$import_type = 2;
			// eAUTOR (define('IMPORT_TYPE_EAU2', 1); define('IMPORT_TYPE_EAU3', 4); define('IMPORT_TYPE_EAU3_2', 'eauthor3_2');
			elseif (file_exists($dirpath.'/folder'.$file.'/course.xml'))
			$import_type = 4;
			// AICC define('IMPORT_TYPE_AICC', 3);
			else
			$import_type = 3;


			 // добавляем курс, узнаем cid
			 $filedata = explode('.', $file);
			 $data = array(
			 'Title' => $filedata[0],
			 'Description' => '',
			 'Status' => 0,
			 'provider' => 0,
			 'developStatus' => 0,
			 'lastUpdateDate' => date('Y-m-d'),
			 'createDate' => date('Y-m-d'),
			 'planDate' => null, // ???
			 'TypeDes' => 0,
			 'chain' => 0,
			 'did' => '',
			 'sequence' => 0,
			 'is_module_need_check' => 0,
			 'has_tree' => 1,
			 'longtime' => 120); // ???
			 $result = $this->getService('Course')->insert($data);
			 $cid = $result->CID;
			echo $file.' - '.$types[$import_type].' - cid '.$cid.'<br>';

			$data = array(
					'remote_' => 1,
					'course_id' => $cid,
					'send' => 1,
					'cid' => $cid,
					'import_type' => $import_type,
					'zipfile' => '@'.$packageDir,
					'ch_info' => 1,
					'submit' => _('Сохранить')
			);

		    $headers = headers_list();
	        foreach ($headers as $header) {
	            $header = explode(":", $header);
	            if(array_shift($header) == 'Set-Cookie') $cookie = trim(implode(":", $header));
	        }

	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
			curl_setopt($ch, CURLOPT_URL, $_SERVER['HTTP_HOST']."/teachers/organization_exp.php");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			$output = curl_exec($ch);
			curl_close($ch);
			echo $output;
		}
			echo '<br>'._('Учебные модули успешно импортированы!');
	}
}