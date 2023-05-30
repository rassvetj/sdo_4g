<?php

if(!$testimport){
	$_POST['test_delete'] = 1;
	$_POST['materials_delete'] = 1;
}
else{
	$_POST['cid'] = 1;
}

if(isset($_POST['remote_']) && $_POST['remote_']){
	include_once("../1.php");
}

include_once("../xml2/xml.class.php4");
include_once("../metadata.lib.php");
include_once("organization.lib.php");
include_once("ziplib.php");
require_once('../lib/scorm/scorm.lib.php');
require_once('../lib/aicc/aicc.class.php');
require_once('../lib/classes/ProgressBar.class.php');
require_once('../lib/classes/xml2array.class.php');
require_once('../lib/classes/CourseContent.class.php');
require_once('../lib/library_cms/index.class.php');
require_once('../lib/classes/Task.class.php');
require_once('../lib/classes/Question.class.php');
require_once('../lib/classes/CourseImport.class.php');
require_once('../lib/classes/CCourseAdaptor.class.php');

include_once($_SERVER['DOCUMENT_ROOT']."/test.inc.php");
include_once($GLOBALS['wwf']."/template_test/1-v.php");
include_once($GLOBALS['wwf']."/template_test/2-v.php");
include_once($GLOBALS['wwf']."/template_test/3-v.php");
include_once($GLOBALS['wwf']."/template_test/4-v.php");
include_once($GLOBALS['wwf']."/template_test/5-v.php");
include_once($GLOBALS['wwf']."/template_test/6-v.php");
include_once($GLOBALS['wwf']."/template_test/7-v.php");

$cid = $GLOBALS['cid'] = (int) $_POST['cid'];
$auk = (boolean) getField('Courses','type','CID',$cid);
define('SKIP_SUBTREE', 'skip_subtree');
define("SKIP_THIS_BRANCH", 1);
$tmpdir = $GLOBALS['wwf'].'/temp';
if(isset($import_type)) {
        if ($cid > 0) {
            if (!is_dir($GLOBALS['wwf'].'/COURSES/course'.(int) $cid)) {
                mkdir($GLOBALS['wwf'].'/COURSES/course'.(int) $cid);
            }
        }

        // autodetect SkillSoft
        Zend_Registry::set('providerDetected', false);
        Zend_Registry::set('providerOptions', false);

        if(in_array($import_type, array(IMPORT_TYPE_SCORM, IMPORT_TYPE_EAU2))) {
                $GLOBALS['progress'] = new CProgressBar($_POST['progressId']);
                $GLOBALS['progress']->setAction(_('Инициализация'));
                if($send==1) {
                        $strMsg = "";
                        $strSalt = getSalt();

                        // Используется ли IMS Manifest (SCORM, AICC)?
                        $isIMSManifest = $GLOBALS['isIMSManifest'] = (in_array($_POST['import_type'], array(IMPORT_TYPE_SCORM, IMPORT_TYPE_AICC))) ? $_POST['import_type'] : 0;

                        // Разархивирование package
                        $uploaded = true;
                        $zipfile_name = $_FILES['zipfile']['name'];
                        if (!move_uploaded_file($zipfile, $tmpdir."/".$zipfile_name)) {
                            $uploaded = false;
                            if (($_FILES['zipfile']['error'] != UPLOAD_ERR_OK) || !file_exists($tmpdir.'/'.$zipfile_name)) {
                                $strMsg .= _("Нет файла данных")." $zipfile_name <BR>";
                            } else {
                                $uploaded = true;
                            }
                        }
                        if ($uploaded) {
                            //chmod($tmpdir."/".$zipfile_name,0660);
                            $GLOBALS['progress']->setAction(_('Распаковка архива курса'));
                            $GLOBALS['progress']->saveProgress(0);
                            $packageDir = extractModZip($cid, $tmpdir."/".$zipfile_name, $strSalt);
                            $GLOBALS['progress']->saveProgress(100);
                        }

                        // ===========================================================================
                        /**
                        * Парсинг xml
                        */
                        $GLOBALS['progress']->setAction(_('Обработка файлов курса'));
                        $GLOBALS['progress']->saveProgress(0);
                        if ($isIMSManifest)
                            $xmlfile_name = $packageDir.'/'.IMS_MANIFEST_FILENAME;
                        else {
                            $xmlfile_name = $packageDir.'/course.xml';
                            /*@move_uploaded_file($xmlfile, $tmpdir."/".$xmlfile_name);
                            $xmlfile_name = $tmpdir."/".$xmlfile_name;*/
                        }

                        if (!file_exists($xmlfile_name))
                                $strMsg .= _("Нет файла структуры")." $xmlfile_name <BR>";
                        else {

                                if ($isIMSManifest)
                                    $strContent = ims_transliterateXMLHrefs($xmlfile_name);
                                else
                                    $strContent = transform_xml($xmlfile_name);
                                $fs = fopen($xmlfile_name, "r");
                                $strContent = fread($fs, filesize($xmlfile_name));
                                fclose($fs);

                                if (!$isIMSManifest) {

                                    // Собственный формат курсов

                                    $strPatt01 = "(DB_ID=\")";
                                    $strPatt02 = "(InnerLink=\")";
                                    $strPatt03 = "(src=\")\./Files";
                                    $strPatt04 = "(src=\")\./files";
                                    $strPatt05 = "(background=\")\./Files";
                                    $strPatt06 = "(background=\")\./files";
                                    $strPath = $strSalt ? "./media/{$strSalt}" : "./media";
                                    $strContent = eregi_replace($strPatt01, "\\1{$strSalt}", $strContent);
                                    $strContent = eregi_replace($strPatt02, "\\1{$strSalt}", $strContent);
                                    $strContent = eregi_replace($strPatt03, "\\1{$strPath}", $strContent);
                                    $strContent = eregi_replace($strPatt04, "\\1{$strPath}", $strContent);
                                    $strContent = eregi_replace($strPatt05, "\\1{$strPath}", $strContent);
                                    $strContent = eregi_replace($strPatt06, "\\1{$strPath}", $strContent);

                                    $strContent = optimize_course($cid, $strContent);

									if (@!append($strContent, $_SERVER['DOCUMENT_ROOT'].COURSES_DIR_PREFIX."/COURSES/course{$cid}/course.xml", true)) $strMsg .= _("Ошибка копирования")."<br>";

                                } else {

                                    // IMS manifest

                                    $strPatt01 = "(identifier=\")";
                                    $strPatt02 = "(identifierref=\")";

                                    $strContent = eregi_replace($strPatt01, "\\1{$strSalt}", $strContent);
                                    $strContent = eregi_replace($strPatt02, "\\1{$strSalt}", $strContent);

                                    }
                                $done = importCourse($cid, $strContent);
                                if(!$done) {

                                        $strMsg .= _("Ошибки в вопросах")." ({$intNum}). <a href='{$GLOBALS['sitepath']}zlog/xml/import/log.xml'>"._("Лог-файл")."</a><br>";

                                } else {
                                    //$sql = "UPDATE Courses SET locked = '1' WHERE CID = '".(int) $cid."'";
                                    //sql($sql);

								    $tree_obj = new CCourseContentTree();
								    $tree = $tree_obj->getChildren($cid);
                                    if (is_array($tree['children']) && count($tree['children'])) {
                                        $tree_first_child = reset($tree['children']);
                                    }

                                    if( count($tree['children']) > 1 || $tree_first_child !== false && count($tree_first_child['children']) >= 1) {

//                                    if(count($tree['children']) > 1 /*&& $import_type == IMPORT_TYPE_SCORM*/) {
                                        sql("UPDATE Courses SET has_tree = 0 WHERE CID = " . intval($cid));
                                    }else{
                                        sql("UPDATE Courses SET has_tree = 1 WHERE CID = " . intval($cid));
                                    }


                                    if (!count($tree['children'])) {
                                        sql("INSERT INTO organizations (title, cid, prev_ref, level) VALUES ('<пустой элемент>','$cid','-1', '0')");
                                        $tree = $tree_obj->getChildren($cid);
                                    }
                                    $GLOBALS['adodb']->UpdateClob('Courses', 'tree', serialize($tree), "CID='{$cid}'");

                                }

                        }
                        $GLOBALS['progress']->saveProgress(100);
                        // ===========================================================================

                        if (file_exists($tmpdir."/".$zipfile_name)) @unlink($tmpdir."/".$zipfile_name);
                        //if (file_exists($xmlfile_name)) @unlink($xmlfile_name);

                        if (!strlen($strMsg))
                        $strMsg = _("Данные успешно загружены")."<br>";
                        $page = "courses_base.php4";
                        if (!$auk) $page = 'course/index/index/course_id/'.$cid;
                        if (isset($GLOBALS['subjectId'])) {
                            $page = 'subject/index/courses/subject_id/'.$GLOBALS['subjectId'];
                        }

                        if (Zend_Registry::get('providerDetected')) {
                            sql("UPDATE Courses SET provider = '" . Zend_Registry::get('providerDetected')  . "', provider_options = '" . Zend_Registry::get('providerOptions') . "' WHERE CID = " . intval($cid));
                        }

                        if(!$_POST['remote_']){
	                        //$GLOBALS['controller']->setMessage($strMsg, JS_GO_URL, $GLOBALS['sitepath'].$page);
	                        //$GLOBALS['controller']->terminate();
                            $page = $GLOBALS['sitepath'].$page;
                            return true;
                        }

                }
                if(!$_POST['remote_']){
	                $GLOBALS['progress']->saveProgress(-1);
	                $GLOBALS['progress']->unlink();
                }

        }
        /**
        * Импорт AICC курса
        */

        if (($import_type == IMPORT_TYPE_AICC) && isset($_FILES['zipfile'])) {
            $GLOBALS['progress'] = new CProgressBar($_POST['progressId']);
            $GLOBALS['progress']->setAction(_('Инициализация'));
            $aicc = new CAicc();
            $GLOBALS['progress']->saveProgress(100);
            $aicc->parse_package($_FILES['zipfile'],$cid, $_POST['ch_info']);

            $errors = $aicc->get_errors();
            if (is_array($errors))
                $strMsg = join('. ',$errors);
            else
            	$strMsg = _('Данные успешно загружены');

            $tree_obj = new CCourseContentTree();
            $tree = $tree_obj->getChildren($cid);

            if (is_array($tree['children']) && count($tree['children'])) {
                $tree_first_child = reset($tree['children']);
            }

            if( count($tree['children']) > 1 || $tree_first_child !== false && count($tree_first_child['children']) >= 1) {
                sql("UPDATE Courses SET has_tree = 0 WHERE CID = " . intval($cid));
            }else{
                sql("UPDATE Courses SET has_tree = 1 WHERE CID = " . intval($cid));
            }
            if (!count($tree['children'])) {
                sql("INSERT INTO organizations (title, cid, prev_ref, level) VALUES ('<пустой элемент>','$cid','-1', '0')");
                $tree = $tree_obj->getChildren($cid);
            }

            $GLOBALS['adodb']->UpdateClob('Courses', 'tree', serialize($tree), "CID='{$cid}'");
            $page = "courses_base.php4";
            if (!$auk) $page = 'course/index/index/course_id/'.$cid;
            if (isset($GLOBALS['subjectId'])) {
                $page = 'subject/index/courses/subject_id/'.$GLOBALS['subjectId'];
            }
            if($testimport) $page = 'test/abstract/index/subject_id/'.$subjectId;;


            if(!$_POST['remote_']){
            	//$GLOBALS['controller']->setMessage($strMsg, JS_GO_URL, $GLOBALS['sitepath'].$page);
	            //$GLOBALS['controller']->terminate();
                $page = $GLOBALS['sitepath'].$page;
                return true;

	            $GLOBALS['progress']->saveProgress(-1);
	            $GLOBALS['progress']->unlink();
            }
        }

        /**
        * Импорт курса eAuthor 3
        */
        if (($import_type == IMPORT_TYPE_EAU3) && isset($_FILES['zipfile'])) {
            //sql("UPDATE Courses SET has_tree = 1 WHERE CID = " . intval($cid));
            $GLOBALS['progress'] = new CProgressBar($_POST['progressId']);
            $GLOBALS['progress']->setAction(_('Инициализация'));

            $GLOBALS['db_ids'] = array();
            $strMsg = ''; $strSalt = getSalt();

            $GLOBALS['progress']->saveProgress(100);
            $GLOBALS['progress']->setAction(_('Распаковка архива курса'));
            $GLOBALS['progress']->saveProgress(0);

            $packageFileName = $tmpdir.'/'.$_FILES['zipfile']['name'];
//            if (!move_uploaded_file($_FILES['zipfile']['tmp_name'],$tmpdir.'/'.$_FILES['zipfile']['name']))
            $uploaded = true;
            if (!move_uploaded_file($_FILES['zipfile']['tmp_name'], $packageFileName)) {
                $uploaded = false;
                if (($_FILES['zipfile']['error'] != UPLOAD_ERR_OK) || !file_exists($packageFileName)) {
                    $strMsg .= _("Нет файла данных")." {$_FILES['zipfile']['name']}.<BR>";
                } else {
                    $uploaded = true;
                }
            }
            if ($uploaded) {
                $packageDir = extractModZip($cid, $packageFileName, $strSalt);
            }

            $GLOBALS['progress']->saveProgress(100);
            $GLOBALS['progress']->setAction(_('Обработка файлов курса'));
            $GLOBALS['progress']->saveProgress(0);

            $xml_filename = $tmpdir.'/course.xml';
            if (!file_exists($xml_filename)) $strMsg .= _("Нет файла структуры")." {$xml_filename}.";
            else {
            	/***********
            		Поддержка импорта курсов в формате eau3.2.
            		Формат 3.2 определяется автоматически по атрибуту в organizations.
            		Поддержка только тех вопросов, которые существуют в сервере 3.3.

            	************/
            	if (check_eau_32($xml_filename)) {
            		if (file_exists($packageFileName)) @unlink($packageFileName);
            		$ret = importCourse32($cid, $strSalt, $testimport, $subjectId);
                    $page = $ret[1];
                    $strMsg = $ret[0];
                    return true;
            		exit();
            	}
        		transform_xml($xml_filename);
            }

            if ($fs = @fopen($xml_filename,'r')) {
                $strContent = file_get_contents($xml_filename);

                $strPattern = "/(src=\")(file:)/i";
                $strContent = preg_replace($strPattern, "\\1", $strContent);

                $strPattern = "/(src=\")\.\/(files)/i";
                $strReplace = $strSalt ? "./media/{$strSalt}" : "./media";
                $strContent = preg_replace($strPattern, "\\1{$strReplace}", $strContent);

                $strContent = optimize_course($cid, $strContent);

                if(!importCourse($cid, $strContent))
                    $strMsg .= _("Ошибки в вопросах")." ({$intNum}). <a href='/zlog/xml/import/log.xml'>Лог-файл</a>.<br>";

                if (@!append($strContent, $_SERVER['DOCUMENT_ROOT'].COURSES_DIR_PREFIX."/COURSES/course{$cid}/course.xml", true))
                            $strMsg .= _("Ошибка копирования данных")."<br>";

                fclose($fs);
            }

            if (file_exists($packageFileName)) @unlink($packageFileName);
            if (file_exists($tmpdir.'/course.xml')) @unlink($tmpdir.'/course.xml');

            //$sql = "UPDATE Courses SET locked = '1' WHERE CID = '".(int) $cid."'";
            //sql($sql);

            $tree_obj = new CCourseContentTree();
            $tree = $tree_obj->getChildren($cid);
            if (is_array($tree['children']) && count($tree['children'])) {
                $tree_first_child = reset($tree['children']);
            }

            if( count($tree['children']) > 1 || $tree_first_child !== false && count($tree_first_child['children']) >= 1) {

//            if (count($tree['children']) > 1){
                sql("UPDATE Courses SET has_tree = 0 WHERE CID = " . intval($cid));
            }else{
                sql("UPDATE Courses SET has_tree = 1 WHERE CID = " . intval($cid));
            }


            if (!count($tree['children'])) {
                sql("INSERT INTO organizations (title, cid, prev_ref, level) VALUES ('<пустой элемент>','$cid','-1', '0')");
                $tree = $tree_obj->getChildren($cid);
            }
            $GLOBALS['adodb']->UpdateClob('Courses', 'tree', serialize($tree), "CID='{$cid}'");


            $GLOBALS['progress']->saveProgress(100);

            if (!strlen($strMsg)){
				$strMsg = _("Данные успешно загружены");
            }

            if(!$_POST['remote_']){
                $page = 'course/index/index/course_id/'.$cid;
                if (isset($GLOBALS['subjectId'])) {
                    $page = 'subject/index/courses/subject_id/'.$GLOBALS['subjectId'];
                }

	            //$GLOBALS['controller']->setMessage($strMsg, JS_GO_URL, $GLOBALS['sitepath'].$page);
	            //$GLOBALS['controller']->terminate();
                $page = $GLOBALS['sitepath'].$page;
                return true;

	            $GLOBALS['progress']->saveProgress(-1);
	            $GLOBALS['progress']->unlink();
            }
        }

        /**
        * Импорт zip-архива
        */
        if (($import_type == IMPORT_TYPE_ZIP) && isset($_FILES['zipfile'])) {

        	$destFolder = $wwf .COURSES_DIR_PREFIX. "/COURSES/course{$_GET['cid']}/mods/";
        	//выбор имени папки для пакета
        	$modDirName = to_translit(substr($_FILES['zipfile']['name'], 0, strpos($_FILES['zipfile']['name'], '.')));
        	$dummy = -1;
            do{
                if (in_array($modDirName, scandir($destFolder))) {
                    $modDirName .= ++$dummy;
                }else {
                    $dummy = -1;
                }
            }
        	while($dummy != -1);
        	$destFolder .= $modDirName.'/';
	        require_once("../lib/PCL/pclzip.lib.php");
	        if (is_uploaded_file($_FILES['zipfile']['tmp_name'])){
		        //$archive = new PclZip($_FILES['zipfile']['tmp_name']);
                $archive = new ZipArchive();
                if (!$archive->open($_FILES['zipfile']['tmp_name']) || !$archive->extractTo($destFolder)) {
		        //if (!is_array($list = $archive->extract(PCLZIP_OPT_PATH, $destFolder))) {
		            $strMsg .= _("Невозможно разархивировать файлы");
		        }
		        else {
                    $list = array();
                    for($i = 0; $i<$archive->numFiles; $i++) {
                        $entry = $archive->statIndex($i);
                        if (is_file($destFolder.$entry['name'])) {
                            $entry['folder'] = false;
                            $list[] = $entry;
                        }
                    }
					//======================================================
					// рекурсивно проходим по каталогу и находим русские названия файлов делаем транслит, во всех файлах производим замену в ссылках
					$russian_name_file = array();
					//рекурсивно обходим файлы проверяя их имена
					search_file_name_not_lat("../COURSES/course{$_GET['cid']}/mods/$modDirName/" . $entry['stored_filename']);
					$sizearr = sizeof($russian_name_file);
					if ($sizearr>0){
						// Проходим по массиву с русскими именами файлов формируя два массива для замены в файлах
						foreach($russian_name_file as $key=>$value){
							@chmod($dir.$filename, 0777);
							if (rename( $value['path'], to_translit($value['path']))){
								$orig_work[] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", $value['word']);
								$translite_work[] = to_translit($value['word']);
							}
							@chmod($dir.$filename, 0755);
						}
						// Проходим по файлам и производим замену
						recursion_search_ruslink("../COURSES/course{$_GET['cid']}/mods/$modDirName/".$entry['stored_filename'], $orig_work, $translite_work);
					}
					//======================================================
		            $strMsg .= _("Данные успешно загружены");
		            if ($_POST['ch_info']){
						require_once('../lib/library_cms/library.class.php');
						require_once('../lib/library_cms/book.class.php');
			            $library = new CLibrary();
			            foreach ($list as $key=>$entry) {
			            	if ($entry['folder']) continue;
							$library->addItem(array(
							    'title' => _('Материал').'#'.($key+1),
							    'cid' => $_GET['cid'],
							    'filename' => "/../COURSES/course{$_GET['cid']}/mods/$modDirName/" . $entry['name']
							    ), array());
			            }
		            }
		        }
	        } else {
		            $strMsg .= _("Невозможно разархивировать файлы");
	        }

	        if(!$_POST['remote_']){
                $page = 'course/index/index/course_id/'.$cid;
                if (isset($GLOBALS['subjectId'])) {
                    $page = 'subject/index/courses/subject_id/'.$GLOBALS['subjectId'];
                }
	            //$GLOBALS['controller']->setMessage($strMsg, JS_GO_URL, $GLOBALS['sitepath'].$page);
	            //$GLOBALS['controller']->terminate();
                $page = $GLOBALS['sitepath'].$page;
                return true;

	            $GLOBALS['progress']->saveProgress(-1);
	            $GLOBALS['progress']->unlink();
	        }
        }

    if (!isset($GLOBALS['progress'])) {
        $GLOBALS['progress'] = new CProgressBar($_POST['progressId']);
        $GLOBALS['progress']->saveProgress(-1);
        $GLOBALS['progress']->unlink();
    }

    if (Zend_Registry::get('providerDetected')) {
        sql("UPDATE Courses SET provider = '" . Zend_Registry::get('providerDetected')  . "', provider_options = '" . Zend_Registry::get('providerOptions') . "' WHERE CID = " . intval($cid));
    }

}

/*
 *	function search_file_name_not_lat - поиск файлов с русскими именами
 *	@var $dir string - directory for search
 *	@return array() - массив с русскими названиями
 */
function search_file_name_not_lat($dir)
{
global $russian_name_file;
	$handle = opendir($dir);
	while($filename = readdir($handle)){
		if ($filename=='.' or $filename=='..')	continue;
		if (is_dir($dir.$filename)){
			search_file_name_not_lat($dir.$filename.'/');
		}
		if (is_file($dir.$filename)){
			preg_match('|[а-я]|i', $filename, $matches);
			if (sizeof($matches)>0){
				//нашли русские буквы в названии. добавляем их в массив
				$russian_name_file[] = array('word'=>$filename, 'path'=>$dir.$filename);
			}
		}
	}
}


/*
 *	function recursion_search_ruslink - поиск ссылок на русские имена файлов
 *	@var $dir string - directory for search
 *	@var $orig_work array() 	- оригинальные слова
 *	@var $translite_work array() 	- транслит слов
 *
 */
function recursion_search_ruslink($dir, $orig_work, $translite_work)
{
	$handle = opendir($dir);
	while($filename = readdir($handle)){
		if ($filename=='.' or $filename=='..')	continue;
		if (is_dir($dir.$filename)){
			recursion_search_ruslink($dir.$filename.'/',$orig_work, $translite_work);
		}
		if (is_file($dir.$filename) and in_array(strtolower(strrchr($filename, '.')),array('.xml','.html','.xhtml','.xsl','.htm'))){
			// нашли файл ищем в нем ссылки и заменяем на транслит
			@chmod($dir.$filename, 0777);
			$fh = fopen($dir.$filename, "r+");
			flock($fh, LOCK_EX);
				$text=fread($fh,filesize($dir.$filename));
				//замена
				$text = str_replace($orig_work, $translite_work, $text, $count);
				if ($count>0){
					fseek($fh, 0);
					ftruncate($fh, 0);
					fseek($fh, 0);
					fwrite($fh,$text);
				}
			flock($fh, LOCK_UN);
			fclose($fh);
			@chmod($dir.$filename, 0755);
		}
	}
}

function importCourse32($cid, $salt = '', $testonly = false, $subjectId = null, $location = 0) {

    $courseImport = CCourseImportFactory::factory(IMPORT_TYPE_EAU3_2);
    if ($courseImport) {
        $GLOBALS['progress'] = new CProgressBar($_POST['progressId']);
        $GLOBALS['progress']->setAction(_('Инициализация'));
        $GLOBALS['progress']->saveProgress(100);
        $GLOBALS['progress']->setAction(_('Распаковка архива курса'));
        $GLOBALS['progress']->saveProgress(0);
        $courseImport->init(
            array(
                'zip'          => false,
                'courseId'     => $cid,
                'renameCourse' => $_POST['ch_info'],
                'salt' 		   => $salt,

            )
        );
        $GLOBALS['progress']->saveProgress(100);
        $GLOBALS['progress']->setAction(_('Обработка файлов курса'));
        $GLOBALS['progress']->saveProgress(0);
        $courseImport->import($testonly, $subjectId, $location);

        $GLOBALS['progress']->saveProgress(100);
        $GLOBALS['progress']->saveProgress(-1);
        $GLOBALS['progress']->unlink();

        sql("UPDATE Courses SET tree = '' WHERE CID = '{$cid}'");

        if ($courseImport->getErrors()) {
            $GLOBALS['controller']->setMessage(join('<br>', $courseImport->getErrors()), JS_GO_URL, $GLOBALS['sitepath'].'course/index/index/course_id/'.$cid);
            $GLOBALS['controller']->terminate();
            exit();
        }
        $msg = _("Данные успешно загружены.");
        $uri = $GLOBALS['sitepath'].'course/index/index/course_id/'.$cid;
        if($testonly) $msg = _("Данные успешно загружены.");
        if($testonly) $uri = $GLOBALS['sitepath'].'test/abstract';
        if($subjectId) $uri .= '/index/subject_id/'.$subjectId;
        //$GLOBALS['controller']->setMessage($msg, JS_GO_URL, $uri);
        //$GLOBALS['controller']->terminate();
        $strMsg = $msg;
        $page = $uri;
        return array($msg, $uri);
        exit();
    }
}


function importCourse($cid, $strXML)
{
        global $tree;
        global $isIMSManifest;

        if (!$xml = domxml_open_mem($strXML)) {
                echo "Error while parsing the document\n";
                exit;
        }

        if ($isIMSManifest) {

            $GLOBALS['scorm']->version = scorm_getVersion($xml);
            $GLOBALS['scorm']->resources = scorm_getResourceElements($xml);

        }

        $intLevel = 0;
        $intPrevId = -1;
        $tree[$intLevel]['id'] = $cid;
        if ($node = $xml->document_element()) {
                iterateNode($node);
        }
        checkCourse($cid);
        //optimize_course($cid);

        return true;
}

function optimize_course($cid, $update = "")
{
	$course_dir = $_SERVER['DOCUMENT_ROOT'].COURSES_DIR_PREFIX."/COURSES/course$cid/";
	$t1 = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $t2 = "<text><![CDATA[";
    $t3 = "]]></text>";

	if($update != "")
	{
		$xml = domxml_open_mem($update);
	}
	else
	{
		if(is_file($course_dir."course.xml"))
			$filename = $course_dir."course.xml";
    	else
    		return false;

    	$xml = domxml_open_file($filename);
	}

	$node_array = $xml->get_elements_by_tagname("studiedproblem");
      @mkdir($course_dir."cdata", 0755);
      chmod($course_dir."cdata", 0755);
	for ($i = 0; $i<count($node_array); $i++)
	{
	    $node = $node_array[$i];
	    $text_array = $node->child_nodes();
    	for($j = 0; $j<count($text_array); $j++)
    	{
    		$text = $text_array[$j];
    		$content = $text->get_content();
    		$id = $text->attributes();
    		$tmp = null;
    		if(is_array($id) && count($id)) {
    			foreach ($id as $id_value) {
    				if ($id_value->name == "DB_ID") {
    					$tmp = $id_value->value;
    				}
    			}
    		}
    		if($tmp && strlen($content))
    		{
    			$tmpfilename = $course_dir."cdata/$tmp.xml";
				$handle = fopen($tmpfilename, "w");
    			fwrite($handle, $t1.$t2.$content.$t3);
				fclose($handle);
				$txt = "";
				$tmptext = $text->first_child();
    			//$tmptext->set_content($txt);
    			$text->remove_child($tmptext);
    			$text->set_attribute("src", "cdata/$tmp.xml");
    		}
    	}
	}

	if($update != "")
		return $xml->dump_mem();

	$xml->dump_file($filename, false, true);
    return true;
}

function iterateNode($node, $level=0)
{
        global $tree;
        if (SKIP_THIS_BRANCH === saveNode($node,$level)) return;
        if ($node->has_child_nodes()) {
                $boolLevelNext = false;
                foreach ($node->child_nodes() as $child) {
                        if (!is_a($child, "domelement") && !is_a($child, "php4DOMElement")) continue;
                        if (!$boolLevelNext) array_push($tree, array());
                        $boolLevelNext = true;
                        iterateNode($child,$level+1);
                }
                if ($boolLevelNext) array_pop($tree);
        }
}

function saveNode($node, $level = 0)
{

        if ($level < $GLOBALS['currentLevel']) {
            for($tmpLevel = $level; $tmpLevel <= $GLOBALS['currentLevel']; $tmpLevel++) {
                unset($GLOBALS['titles'][$tmpLevel]);
            }
        }
        $GLOBALS['currentLevel'] = $level;

        global $adodb;
        global $strSalt;
        global $arrAttributes;
        global $arrCodes;
        global $tree;
        global $cid;
        global $intOid;
        global $isIMSManifest;

        $arrAttributes = $node->attributes();
        $strContent = trim($node->get_content());
        $strContent = (!hasChildrenElements($node)) ? iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$strContent) : "";

        //$strPath = $GLOBALS['sitepath']."COURSES/course{$cid}/media/{$strSalt}/";
        $strPath = /*$GLOBALS['sitepath'].*/"COURSES/course{$cid}/";
        $strPatt = "<(img[^>]*src[[:space:]]*=[[:space:]]*[\"'])([^\"^']*[\\/])([^\"^']*)([\"'][^>]*)>";
        $strContent = eregi_replace($strPatt, "<\\1{$strPath}\\2\\3\\4>", $strContent);
        $strContent = str_replace("InnerLink=\"", "InnerLink=\"{$strSalt}", $strContent);
        $intLevel = getLevelCurrent($tree);
        if (!$isIMSManifest && is_array($GLOBALS['db_ids'])) $GLOBALS['db_ids'][] = getAttribute('DB_ID');
        $strSwitch = (defined("LOCAL_IMPORT_IMS_COMPATIBLE") && LOCAL_IMPORT_IMS_COMPATIBLE && ($node->tagname == "item")) ? getAttribute("type") : $node->tagname;
        if ($isIMSManifest && ($node->tagname == 'item')) $strSwitch = $node->tagname;
        $strRoot = (defined("LOCAL_IMPORT_IMS_COMPATIBLE") && LOCAL_IMPORT_IMS_COMPATIBLE) ? "organization" : "course";

        if ($isIMSManifest || ($strSwitch != 'text')) {
            $strContent = $adodb->qstr($strContent);
        }

        switch ($strSwitch) {
                case $strRoot:
                        saveCourse($cid,$node);
//                        $tree[$intLevel]['oid'] = saveStructElement("NULL",$node);
                break;
                case "abstract":
                        if (isset($_POST['ch_info'])) saveAbstract($strContent);
                break;
                case "studiedproblem":
                        $id = saveStructElement(saveLesson($node),$node);
                        if ($id == SKIP_SUBTREE) return SKIP_THIS_BRANCH;
                        $tree[$intLevel]['oid'] = $id;
				break;
                case "test":
                        $tree[$intLevel]['id'] = saveTest();
                        $id = saveStructElement($tree[$intLevel]['id'], $node, true);
                        if ($id == SKIP_SUBTREE) return SKIP_THIS_BRANCH;
                        $tree[$intLevel]['oid'] = $id;
                break;
                case "question":
                        $tree[$intLevel]['id'] = saveQuestion();
                        if ($tree[$intLevel]['id']) $arrCodes[] = $tree[$intLevel]['id'];
                break;
                case "text":
                        switch ($tree[getLevelPrev()]['tag']) {
                                case "question":
                                        saveQuestionText($strContent);
                                break;
                        }
                break;
                case "answers":
                        saveAnswers();
                break;
                case "answer":
                        saveAnswer($strContent);
                break;
                case "unit": case "topic": case "theme": case 'studiedproblems': case 'lesson':
                        $id = saveStructElement("NULL",$node);
                        if ($id == SKIP_SUBTREE) return SKIP_THIS_BRANCH;
                        $tree[$intLevel]['oid'] = $id;
                break;
                case "item": // IMS Manifest Items
                        if ($isIMSManifest) {
                            $id = saveStructElement(saveLesson($node),$node);
                            if ($id == SKIP_SUBTREE) return SKIP_THIS_BRANCH;
                            $tree[$intLevel]['oid'] = $id;
                        }
                break;
                default:
                break;
        }
        $tree[$intLevel]['tag'] = $strSwitch;
}

function saveCourse($cid,$node)
{
	global $adodb;
    global $isIMSManifest;

        $boolSucces = true;

        if ($isIMSManifest)
            $strTitle = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, scorm_getTitle($node));
        else
            $strTitle = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("title"));

        if ($_POST['ch_info']) {
                $sql = sprintf("update Courses set Title=%s where CID=%s", $adodb->qstr($strTitle), $cid);
                if (!($a = sql($sql))) $boolSucces = false;
        }

        CCourseAdaptor::clearForImport($cid);

        //удаляем тесты и вопросы если нужно
       // if ($_POST['test_delete']) {
        CCourseAdaptor::deleteTests($cid);
              //  }

        //удаляем материалы если нужно
        //if ($_POST['materials_delete']) {
        CCourseAdaptor::deleteMaterials($cid);
                //}
        CCourseAdaptor::deleteStatistic($cid);
        return $boolSucces;
}

function saveLesson($node)
{
        global $adodb;
        global $cid;
        global $tree;
        global $isIMSManifest;

        $boolSucces = true;
        $IMSboolSuccess = false;

        if ($isIMSManifest) {

            $itemInfo = scorm_get_item_info($node);
            $strItemInfo = serialize($itemInfo);
            $strModTitle = $itemInfo['title'];
//            $strModTitle = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, scorm_getTitle($node));
            $intNum = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("identifier"));

            $resources = scorm_getResourcesById(getAttribute('identifierref'));
            $parameters = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute('parameters'));

            if (isset($resources) && is_array($resources) && count($resources)>0) {

                /**
                * Получение пути к текущему модулю из иерархии
                */
                $strModPath = '';

                for($tmpLevel = 0; $tmpLevel < $GLOBALS['currentLevel']; $tmpLevel++) {
                    if (isset($GLOBALS['titles'][$tmpLevel]) && !empty($GLOBALS['titles'][$tmpLevel])) {
                        $strModPath .= $GLOBALS['titles'][$tmpLevel].'. ';
                    }
                }

/*
                $tmpTree = array();
                for($tmpLevel=1;($tmpLevel<(getLevelCurrent()));$tmpLevel++) {


                    $tmpTree[$tmpLevel] = $tree[$tmpLevel];

                    if (isset($tree[$tmpLevel]['title']) && !empty($tree[$tmpLevel]['title']))
                    $strModPath .= $tree[$tmpLevel]['title'].'. ';

                }

                $tree = $tmpTree;
*/

                $IMSboolSuccess = true;

                $sql=sprintf("insert into mod_list( Title, Num, Pub, CID )
                values(%s,'%s',%s,%s)",$adodb->qstr($strModPath.$strModTitle), $intNum, 1, $cid);

                //if (!($a = sql($sql))) $boolSucces = false;
                //$intModId = sqllast();

                reset($resources);
                while(list(,$v) = each($resources)) {

                    if ($parameters) $v['href'] .= $parameters;

/*
                    $q = sprintf("INSERT INTO mod_content (title, ModId, mod_l, type, conttype, scorm_params)
                    VALUES (%s, %s, '%s', '%s', '%s',%s)",
                    $adodb->qstr(_("Материал:")." {$strModTitle}"),
                    $intModId,
                    "/COURSES/course{$cid}/{$v['href']}",
                    (@$GLOBALS['scorm']->version) ? $GLOBALS['scorm']->version : "html",
                    ($v['type'] != '?') ? $v['type'] : "text/html",
                    $adodb->qstr($strItemInfo)
                    );
*/                    //if (!($a = sql($q))) $boolSucces = false;

                    /**
                     * Занесения модуля в библиотеку
                     */

/*                    if (!$GLOBALS['auk']) {
                        if (strlen($v['href'])) {
                            $bid = 0;
                            $sql = "SELECT bid FROM library WHERE filename LIKE '%{$v['href']}'";
                            $res = sql($sql);

                            while($row = sqlget($res)) {
                                $intModId = $row['bid'];
                            }

                        }

                    } else {
*/
                    	$mid = ($_SESSION['s']['mid']) ? $_SESSION['s']['mid'] : 0;
                        $sql = "INSERT INTO library (cid, mid, title, filename, upload_date, is_active_version, content, scorm_params".(count($metaData) ? ','.join(',',array_keys($metaData)) : '').")
                                VALUES ($cid, {$mid}, ".$GLOBALS['adodb']->Quote($strModPath.$strModTitle).",".$GLOBALS['adodb']->Quote("/../COURSES/course{$cid}/{$v['href']}").",NOW(),1,'".((@$GLOBALS['scorm']->version) ? $GLOBALS['scorm']->version : "html")."',".$GLOBALS['adodb']->Quote($strItemInfo).(count($metaData) ? ','.join(',',$metaData) : '').")";
                        sql($sql);

                        $intModId = sqllast();

                    //}

                }

            }

        } else {

            $strModTitle = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("title"));
            $intNum = getAttribute("DB_ID");


            $sql = "INSERT INTO library (cid, mid, title, filename, upload_date, is_active_version, content, scorm_params".(count($metaData) ? ','.join(',',array_keys($metaData)) : '').")
                    VALUES ($cid, {$_SESSION['s']['mid']}, ".$GLOBALS['adodb']->Quote($strModPath.$strModTitle).",".$GLOBALS['adodb']->Quote("/../COURSES/course{$cid}/index.htm?id=" . urlencode($intNum)).",NOW(),1,'".((@$GLOBALS['scorm']->version) ? $GLOBALS['scorm']->version : "html")."',".$GLOBALS['adodb']->Quote($strItemInfo).(count($metaData) ? ','.join(',',$metaData) : '').")";
            if (!sql($sql)) $boolSucces = false;
            $intModId = sqllast();

/*
            $sql=sprintf("insert into mod_list( Title, Num, Pub, CID )
                          values(%s,'%s',%s,%s)",$adodb->qstr($strModTitle), "", 1, $cid);

            if (!($a = sql($sql))) $boolSucces = false;
            $intModId = sqllast();

            $q = sprintf("INSERT INTO mod_content (title, ModId, mod_l, type, conttype)
            VALUES (%s, %s, '%s', '%s', '%s')",
            $adodb->qstr(_("Материал:")." {$strModTitle}"),
            $intModId,
            "/COURSES/course{$cid}/index.htm?id=" . urlencode($intNum),
            "html",
            "text/html"
            );
            if (!($a = sql($q))) $boolSucces = false;
*/
        }

        //if ($isIMSManifest) $tree[getLevelCurrent()-2]['title'] = $strModTitle;
        //else $tree[getLevelCurrent()]['title'] = $strModTitle;

        $GLOBALS['titles'][$GLOBALS['currentLevel']] = $strModTitle;

        if ($isIMSManifest && !$IMSboolSuccess) $boolSucces = false;
        return ($boolSucces) ? $intModId : 0;
}

function saveTest()
{
        global $adodb;
        global $cid;
        global $arrAttributes;

        $boolSucces = true;
        $intTimeLimit = getAttribute("timelimit");
        $strTestTitle = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("title"));
        $strTitle = "";
        $strTitle .= getFromTree("any", "title");
        if (strlen($strTitle)) $strTitle .= " - ";
        $strTitle .= $strTestTitle;

	        $rq="INSERT INTO test
	          (cid, cidowner, title, datatype, data, random, lim, qty, sort, free, skip, rating, status, timelimit, last, created_by)
	          VALUES
	          ($cid, $cid, " . $adodb->qstr($strTitle) . ", 1, '', 1, 0, 1, 0, 0, 0, 0, 1, '".(int)$intTimeLimit."', ".time().", '".(int) $_SESSION['s']['mid']."')";
	        if (!($a = sql($rq))) $boolSucces = false;
	        $intTid = sqllast();

        return ($boolSucces) ? $intTid : 0;
}

function saveQuestion()
{
        global $cid;
        global $brtag;
        global $adodb;
       	global $test_id;

        $boolSucces = true;
        $intTid = getFromTree("test");

        $sql = sprintf(
			        "INSERT INTO list (kod, qtema, balmin, balmax, last, timelimit, qmoder) VALUES ('%s', '%s', '%s', '%s', '%s', %s, %s)",
			        ($strCode = newQuestion( $cid )),
			        iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("group")),
			        0,
			        ($tmp = getAttribute("weight")) ? $tmp : 1,
			        time(),
			        ($tmp = getAttribute("timelimit")) ? $tmp : 'NULL',
			        0
        );
        if (!($a = sql($sql, "errfn"))) $boolSucces = false;
        $query = "SELECT data FROM test WHERE tid = '{$intTid}'";
        $result = sql($query);
        $row = sqlget($result);
        if(trim($row['data']) == "") {
           $sql = "UPDATE test SET data = '{$strCode}' WHERE tid = '{$intTid}'";
        }
        else {
           $sql = "UPDATE test SET data = ".$adodb->Concat("'{$row['data']}'", "'{$brtag}'", "'{$strCode}'")." WHERE tid = '{$intTid}'";
        }
        //$sql = "UPDATE test SET data=CONCAT(data, IF((data=''),'','{$brtag}'), '{$strCode}') WHERE tid='{$intTid}'";
        if (!($a = sql($sql))) $boolSucces = false;
        return ($boolSucces) ? $strCode : 0;
}

function saveQuestionText($strContent)
{
        global $cid;
        global $adodb;
        $boolSucces = true;
        $intCode = getFromTree("question");
        if (!($adodb->UpdateClob('list','qdata',trim($adodb->Quote($strContent), "'"),"kod='{$intCode}'"))) $boolSucces = false;
        return $boolSucces;
}

function saveAnswers()
{
        global $cid;
        global $tree;

        $boolSucces = true;
        $arrTypes = array("single" => 1, "multiple" => 2, "fill" => 5, "compare" => 3, 'free' => 6, 'long-fill' => 6);
        $intCode = getFromTree("question");
        $intType = $arrTypes[getAttribute("type")];
        $tmp = getAttribute("type");
        $sql = "UPDATE list SET qtype='{$intType}' WHERE kod='{$intCode}'";
        if (!($a = sql($sql))) {
                $boolSucces = false;
        }
        $tree[getLevelCurrent()]['type'] = $intType;
        return $boolSucces;
}

function saveAnswer($strContent)
{
        global $cid;
        global $tree;
        global $brtag;
        global $adodb;

        $boolSucces = true;
        $intCount = ++$tree[getLevelCurrent()]['count'];
        $intCode = getFromTree("question");
        $intType = getFromTree("answers", 'type');

        $query = "SELECT qdata, adata, weight, balmax, balmin FROM list WHERE kod = '{$intCode}'";
        $result = sql($query);
        $row = sqlget($result);
        $row['qdata'] = $row['qdata'];
        $row['adata'] = $row['adata'];
        if (!empty($row['weight'])) $row['weight'] = unserialize($row['weight']);
//        $row['qdata'] = mysql_escape_string($row['qdata']);
//        $row['adata'] = mysql_escape_string($row['adata']);

        $weights = ''; $balmax = $row['balmax']; $balmin=$row['balmin'];
        if ($right = getAttribute('right')) {
            if (empty($row['weight'])) {
                $row['weight'][1] = $right;
                $balmax = $balmin = $right;
            }
            else {
                $row['weight'][] = $right;
                switch($intType) {
                    case 1:
                        $balmax = ($right>$row['balmax']) ? $right : $row['balmax'];
                        $balmin = ($right<$row['balmin']) ? $right : $row['balmin'];
                    break;
                    case 2:
                        $balmax = process_max($row['balmax'],$right);
                        $balmin = process_min($row['balmin'],$right);
                    break;
                }
            }
            $weights = ", weight = '".serialize($row['weight'])."', balmax='".addslashes($balmax)."', balmin='".addslashes($balmin)."'";
        }

        switch ($intType) {
                case 1:
                        $strTrue = (getAttribute("type") == "true") ? ", adata = '{$intCount}'" : "";
                        $sql = "UPDATE list SET qdata=".$adodb->Concat($adodb->qstr($row['qdata']),"'{$brtag}'","'{$intCount}'","'{$brtag}'", "{$strContent}")." {$strTrue} {$weights} WHERE kod='{$intCode}'";
                break;
                case 2:
                        if(trim($row['adata']) != "") {
                                $adata_update = $brtag;
                        }
                        else {
                                $adata_update = "";
                        }
                        $strTrue = (getAttribute("type") == "true") ? "1" : "0";
                        $sql = "UPDATE list SET qdata=".$adodb->Concat($adodb->qstr($row['qdata']),"'{$brtag}'","'{$intCount}'","'{$brtag}'", "{$strContent}").", adata = ".$adodb->Concat("'{$row['adata']}'", "'{$adata_update}'", "'{$strTrue}'")." {$weights} WHERE kod='{$intCode}'";
                break;
                case 3:
                        $strTrue = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("right"));
                        $sql = "UPDATE list SET qdata = ".$adodb->Concat($adodb->qstr($row['qdata']),"'{$brtag}'","'{$intCount}'","'{$brtag}'","{$strContent}","'{$brtag}'","'{$strTrue}'")." WHERE kod = '{$intCode}'";
                break;
                case 5:
                        if(strpos($row['qdata'], $brtag)) {
                           $qdata_update = "";
                           $dobrtag = $brtag;
                        }
                        else {
                           $dobrtag = '';
                           $qdata_update = $brtag.$brtag;
                        }

                        $strRight = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("right"));
                        $sql = "UPDATE list SET qdata=".$adodb->Concat($adodb->qstr($row['qdata']), "'{$dobrtag}'","'{$qdata_update}'",$GLOBALS['adodb']->Quote($strRight),"'{$brtag}'","{$strContent}")." WHERE kod='{$intCode}'";
//                        $sql = "UPDATE list SET qdata=".$adodb->Concat("'{$row['qdata']}'","'{$qdata_update}'","'{$brtag}'","'{$strTrue}'","'{$brtag}'","'{$strContent}'","'{$brtag}'")." WHERE kod='{$intCode}'";
                break;
                case 6:
                    // ?????? ? ????????? ???????
                    // do nothing
                break;
        }
        if (!empty($sql)) {
            if (!($a = sql($sql))) $boolSucces = false;
        }
        return $boolSucces;
}

function saveStructElement($intModId = 0, $node, $test = false)
{
        global $cid;
        global $adodb;
        global $tree;
        global $intOid;
        global $isIMSManifest;

        $boolSucces = true;

        if ($isIMSManifest)
            $strTitle = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, scorm_getTitle($node));
        else
            $strTitle = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("title"));
        $strTitle = trim($strTitle);
        if (!strlen($strTitle)) {
            $strTitle = _('Без названия');
        }
        //заполнение таблицы organizations
        $all_data = array("title"=>$strTitle);
        $meta = set_metadata($all_data,get_posted_names($all_data), "item" );
        $intLevelPrev = getLevelPrev();
        $intLevel = getLevelCurrent();

        if ($intOid === NULL) $_intOid = -1;
        else $_intOid = $intOid;
//        if ($isIMSManifest) $intLevel -= 2;
        if ($isIMSManifest) $intLevel -= 3; // теперь не пишем в organizations название курса
        else $intLevel -= 1;

        // <item isvisible=?
        $add2organizations = true;
        if ($isIMSManifest) {
            if (iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("isvisible"))=="false") $add2organizations = false;
        } else {
            if (iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("visibility"))=="hidden") $add2organizations = false;
        }

        if ($add2organizations) {

            if ($intModId == "NULL") $intModId = 0;

            $vol1 = $vol2 = 0;
            if ($test) {
                $vol1 = $intModId;
                $intModId = 0;
            }

            if (!strlen($strTitle)) $strTitle = _('Нет заголовка');

            $sql = sprintf("insert into organizations(title,level,cid,prev_ref,vol1,vol2,mod_ref,module)
                           values(%s, %s, %s, %s, %s, %s, %s, '%s')",
            $adodb->qstr($strTitle), $intLevel, $cid, ( $intLevelPrev >= 0) ? $_intOid : -1, (int) $vol1, (int) $vol2, 0, $intModId);

            if (!($a = sql($sql))) $boolSucces = false;

            $intOid = sqllast();
            save_item_metadata( $intOid, $meta);

        } else {
            return SKIP_SUBTREE;
        }

        $tree[$intLevel]['title'] = $strTitle;
        return ($boolSucces) ? $intOid : 0;
}

function updateTopElement()
{
        global $cid;
        global $adodb;

        $strTitle = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,getAttribute("title"));
        $sql = "UPDATE organizations SET title=" . $adodb->qstr($strTitle) . " WHERE cid='{$cid}' AND prev_ref='-1'";
        $r = sql($sql);
        $tree[$intLevel]['title'] = $strTitle;
        return true;
}

function saveAbstract($str)
{
        global $adodb;
        global $cid;

        $boolSucces = true;
        $strDescription = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$str);
        $sql = sprintf("update Courses set Description=%s where CID=%s", $str, $cid);
        if (!($a = sql($sql))) $boolSucces = false;
        return $boolSucces;
}

function hasChildrenElements($node)
{
        if (!$node->has_child_nodes()) return false;
        else {
                foreach ($node->child_nodes() as $child) {
                        if (is_a($child, "domelement") || is_a($child, "php4DOMElement")) return true;
                }
        }
        return false;
}

function getAttribute($arrKey)
{
        global $arrAttributes;
        if (is_array($arrAttributes)) {
                foreach ($arrAttributes as $attribute) {
                        if (!is_a($attribute, "domattribute") && !is_a($attribute,"php4DOMAttr")) continue;
                        if ($attribute->name == $arrKey) return $attribute->value;
                }
        }
        return false;
}

function getFromTree($strTag, $strReturn = 'id')
{
        global $tree;
        $rtree = $tree;
        krsort($rtree);
        foreach ($rtree as $key => $arr) {
                if ($strTag == "any") {
                        if (isset($arr[$strReturn])) return  $arr[$strReturn];
                } elseif ($arr['tag'] == $strTag) return $arr[$strReturn];
        }
        return false;
}

function getLevelCurrent()
{
        global $tree;
        if (is_array($tree) && count($tree)) {
            ksort($tree);
            return (int)array_pop(array_keys($tree));
        }
        return 0;
}

function getLevelPrev()
{
        global $tree;
        global $cid;

        if (is_array($tree) && count($tree) > 1) {
                $treeTmp = $tree;
                ksort($treeTmp);
                array_pop($treeTmp);
                return (int)array_pop(array_keys($treeTmp));
        }
        return -1;
}

function checkCourse($cid)
{
        global $brtag;
        global $arrCodes;

        if (!is_array($arrCodes)) return true;

        $strCodes = "'".implode("', '", $arrCodes)."'";
        $arrBad = array();
        //check list
        $q = "
                SELECT *
                FROM list
                WHERE
                  kod IN ({$strCodes})
                ";
        $r = sql($q);
        $arrBad = array();
        while ($a = sqlget($r)) {

                $var = array();
                $data = array();

                $data=explode($brtag,$a['qdata']);
                for ($i=1; $i<count($data); $i+=2) {
                        $var[$data[$i]]=$data[$i+1];
                }
                $data=explode($GLOBALS[brtag],$a['adata']);


                switch ($a['qtype']) {
                        case 1:
                        @$boolBad = ((!in_array($a['adata'], array_keys($var)) && empty($a['weight'])) || (count($var) == 0));
                        break;
                        case 2:
                        @$boolBad = ((count($var) != count($data)) || ( count($var) == 0));
                        break;
                        case 3:
                                @$boolBad = false;
                        break;
                        case 5:
                        @$boolBad = (strpos($a['qdata'], $brtag) === false);
                        break;
                        case 6:
                        @$boolBad = (empty($a['qdata']) === true);
                        break;
                        default:
                        $boolBad = true;
                }
                if ($boolBad) {
                        $arrBad[] = ($tmp = substr($a['qdata'], 0, strpos($a['qdata'], $brtag))) ? substr($tmp, 0, 10)."..." : "&nbsp;&nbsp;"._("Нет текста вопроса");
                        sql("DELETE FROM list WHERE kod='{$a['kod']}'");
                }
        }
        return $arrBad;
}

//-------------------------------------------------------------------------------------
// Работа с архивом
//-------------------------------------------------------------------------------------
function createModZip($cID, $zipFileName) // создание архива модулей заданого курса
{
        global $wwf;

        $dirs[]="mods";  //"../COURSES/course".$cID."/mods";
        $flags["overwrite"]=1;
        $zz = new tarfile("../COURSES/course".$cID."/",$flags);
        $zz->adddirectories($dirs);
        $err =$zz->filewrite($zipFileName);
        if($err)
        echo "Error: ". $err;
}

//-------------------------------------------------------------------------------------
function extractModZip($cID, $zipFileName, $strSalt) // распаковка архива модулей заданого курса
{
        global $tmpdir, $wwf;
        global $isIMSManifest;
//        global $strSalt;

        $workDir = getcwd();
        $packageDir = $wwf.COURSES_DIR_PREFIX."/COURSES/course".$cID;
        chdir($packageDir);
        //chdir($wwf."/COURSES/course".$cID);
        $path_parts = pathinfo($zipFileName);
        if (strtolower($path_parts["extension"])=="tar")
        {
                $flags["overwrite"]=1;
                $zz = new tarfile(".", $flags);
                $tararr = $zz->extractfile($zipFileName);
                if(sizeof($tararr)<1)
                {
                        exit("Error TAR archive");
                }
                foreach($tararr as $tarFile)
                {
                        foreach($tarFile as $key=>$value)
                        {
                                if($key=="filename") $fName=$value;
                                if($key=="data") $fData=$value;
                        }
                        $s= dirname($fName);
                        if(!file_exists($s))   mkdirs($s);
                        $fp = fopen($fName, "wb");
                        fwrite($fp,$fData);
                        fclose($fp);
                }
        }
        if (strtolower($path_parts["extension"])=="zip")
        {
                if (!$isIMSManifest && ($_REQUEST['import_type'] != IMPORT_TYPE_EAU3 || $_REQUEST['testimport'])) {
                    $strPath = $strSalt ? "./media/{$strSalt}" : "./media";
                                        if(!file_exists("media")) {
                        mkdir("media",0775);
                        chmod("media",0775);
                    }
                    if(!file_exists($strPath)) {
                        mkdir($strPath,0775);
                        chmod($strPath,0775);
                    }
                    if(!file_exists("{$strPath}/images")) {
                        mkdir("{$strPath}/images",0775);
                        chmod("{$strPath}/images",0775);
                    }
                    if(!file_exists("{$strPath}/doc")) {
                        mkdir("{$strPath}/doc",0775);
                        chmod("{$strPath}/doc",0775);
                    }
                    if(!file_exists("{$strPath}/dwf")) {
                        mkdir("{$strPath}/dwf",0775);
                        chmod("{$strPath}/dwf",0775);
                    }
                    if(!file_exists("{$strPath}/exe")) {
                        mkdir("{$strPath}/exe",0775);
                        chmod("{$strPath}/exe",0775);
                    }
                    if(!file_exists("{$strPath}/pdf")) {
                        mkdir("{$strPath}/pdf",0775);
                        chmod("{$strPath}/pdf",0775);
                    }
                    if(!file_exists("{$strPath}/wrl")) {
                        mkdir("{$strPath}/wrl",0775);
                        chmod("{$strPath}/wrl",0775);
                    }
                    if(!file_exists("{$strPath}/ppt")) {
                        mkdir("{$strPath}/ppt",0775);
                        chmod("{$strPath}/ppt",0775);
                    }
                    if(!file_exists("{$strPath}/sounds")) {
                        mkdir("{$strPath}/sounds",0775);
                        chmod("{$strPath}/sounds",0775);
                    }
                    if(!file_exists("{$strPath}/flash")) {
                        mkdir("{$strPath}/flash",0775);
                        chmod("{$strPath}/flash",0775);
                    }
                    if(!file_exists("{$strPath}/html")) {
                        mkdir("{$strPath}/html",0775);
                        chmod("{$strPath}/html",0775);
                    }
                    if(!file_exists("{$strPath}/HtmlStuff")) {
                        mkdir("{$strPath}/HtmlStuff",0775);
                        chmod("{$strPath}/HtmlStuff",0775);
                    }
                    if(!file_exists("{$strPath}/video")) {
                        mkdir("{$strPath}/video",0775);
                        chmod("{$strPath}/video",0775);
                    }

                    $patterns = array(
                                        "!^$strPath/images/!i",
                                        "!^$strPath/doc/!i",
                                        "!^$strPath/dwf/!i",
                                        "!^$strPath/exe/!i",
                                        "!^$strPath/pdf/!i",
                                        "!^$strPath/wrl/!i",
                                        "!^$strPath/ppt/!i",
                                        "!^$strPath/sounds/!i",
                                        "!^$strPath/flash/!i",
                                        "!^$strPath/html/!i",
                                        "!^$strPath/HtmlStuff/!i",
                                        "!^$strPath/video/!i");

                    $replacements = array(
                                        "{$strPath}/images/",
                                        "{$strPath}/doc/",
                                        "{$strPath}/dwf/",
                                        "{$strPath}/exe/",
                                        "{$strPath}/pdf/",
                                        "{$strPath}/wrl/",
                                        "{$strPath}/ppt/",
                                        "{$strPath}/sounds/",
                                        "{$strPath}/flash/",
                                        "{$strPath}/html/",
                                        "{$strPath}/HtmlStuff/",
                                        "{$strPath}/video/");

                }
                $zip = zip_open($zipFileName);
                if ($zip)
                {
                        while ($zip_entry = zip_read($zip))
                        {
                                if (zip_entry_open($zip, $zip_entry, "r"))
                                {
                                        $fSize = zip_entry_filesize($zip_entry);
                                        $eName = zip_entry_name($zip_entry);

                                        if (!$isIMSManifest && ($_REQUEST['import_type'] != IMPORT_TYPE_EAU3 || $_REQUEST['testimport']))
                                        $eName = preg_replace('/^(?:files)(.*)/i',$strPath.'\\1',$eName,1);
                                        //$eName = str_replace(array("Files","files"), $strPath, $eName, 1);
                                        if (in_array($eName[0],array("/","\\"))) $eName = substr($eName,1);
                                        $eName = str_replace("\\", "/", $eName);
                                        $eName_tmp = iconv('cp866',$GLOBALS['controller']->lang_controller->lang_current->encoding,$eName);
                                        if (!empty($eName_tmp)) $eName = $eName_tmp;

                                        if (!$isIMSManifest && ($_REQUEST['import_type'] != IMPORT_TYPE_EAU3 || $_REQUEST['testimport']))
                                        $eName = preg_replace($patterns,$replacements,$eName,1);

                                        if (!$isIMSManifest && defined("IS_TRANSLITERATE_SRC_VALUE") && IS_TRANSLITERATE_SRC_VALUE && ($_REQUEST['import_type'] != IMPORT_TYPE_EAU3 || $_REQUEST['testimport']))
                                            $eName = to_translit($eName);

                                        $pathinfo = pathinfo($eName);
                                        if (!file_exists($pathinfo['dirname'])) mkdirs($pathinfo['dirname']);

                                        if($fSize==0)
                                        {
                                                $s= dirname($eName);
                                                if(!file_exists($eName)) {
@                                                        mkdirs($eName);
                                                }
                                        }
                                        else
                                        {
                                                if ($providerDetected = Zend_Registry::get('serviceContainer')->getService('Provider')->autodetectProvider($pathinfo['basename'])) {
                                                    Zend_Registry::set('providerDetected', $providerDetected);
                                                    Zend_Registry::set('providerOptions', $eName);
                                                }

                                                 $courseXML = false;
                                                 if (in_array($_REQUEST['import_type'],array(IMPORT_TYPE_SCORM, IMPORT_TYPE_EAU3)) && !$isIMSManifest) {
                                                    if (CObject::toLower($eName) == 'course.xml') {
                                                        $eName = $tmpdir.'/'.$eName;
                                                    }
                                                 } else {
                                                    if (CObject::toLower($eName) == 'course.xml') {
                                                        $eName =  $wwf.COURSES_DIR_PREFIX."/COURSES/course".$cID.'/'.$eName;
                                                        $courseXML = true;
                                                    }
                                                 }

@                                                $buf=zip_entry_read($zip_entry, $fSize);
@                                                //$fp = fopen (to_translit($eName), "wb+");
@                                                $fp = fopen ($eName, "wb+");
@                                                fwrite($fp,$buf);
@                                                fclose($fp);

                                                 if (!in_array($_REQUEST['oper'],array(4)) && $courseXML) {
                                                     transform_xml($eName);
                                                 }
                                        }

                                        zip_entry_close($zip_entry);
                                }
                        }
                        zip_close($zip);
                }
        }
        chdir($workDir);

        return $packageDir;
}

//-------------------------------------------------------------------------------------
function excludeDomainName( $url )
{
        // исключает доменное имя из адреса
        $u = parse_url( $url );
        return( $u[path].$u[query].$u[fragment]);
}



function getLastStruct($cid)
{
        $items=get_organization( $cid );
        $org=sort_organization( $items, 1, $cid );
        if (is_array($org)) $arrTmp = array_shift($org);
        return (isset($arrTmp['oid'])) ? $arrTmp['oid'] : 0;
}

function unique_db_id($node) {
    if ($node->has_child_nodes()) {
        foreach($node->child_nodes() as $n) {
            if ($n->node_name() != '#text') {
                $GLOBALS['arrAttributes'] = $n->attributes();
                if (($n->node_name() == 'item')
                    && in_array(getAttribute('DB_ID'),$GLOBALS['db_ids'])) {
                    $n->unlink_node();
                    continue;
                }
                unique_db_id($n);
            }
        }
    }
}

function append($strAppend, $strDestFile, $boolOverwrite = false)
{
        $strRoot = (defined("LOCAL_IMPORT_IMS_COMPATIBLE") && LOCAL_IMPORT_IMS_COMPATIBLE) ? "organization" : "course";
        if (!file_exists($strDestFile) || $boolOverwrite) {
                $fd = fopen($strDestFile, "w");
                fwrite($fd, $strAppend);
                fclose($fd);
                return true;
        } elseif ((!@$fd = fopen($strDestFile, "r+b"))) {
                return false;
        } else {
                $strDestContent = fread($fd, filesize($strDestFile));
                // Обеспечение уникальности DB_ID, для eAuthor 3. Если данный DB_ID уже есть то старый удаляется
                if (is_array($GLOBALS['db_ids']) && count($GLOBALS['db_ids'])
                    && !$GLOBALS['isIMSManifest'] && !empty($strDestContent)) {
                    if ($dom = domxml_open_mem($strDestContent)) {
                        $root = $dom->document_element();
                        echo "<pre>";
                        pr($strDestContent);
                        echo "</pre>";
                        unique_db_id($root);
                        $strDestContent = $dom->dump_mem(true);
                        fclose($fd);
                        if (!@$fd = fopen($strDestFile,'w')) return false;
                        fwrite($fd,$strDestContent);
                        fclose($fd);
                        if (!@$fd = fopen($strDestFile,'r+b')) return false;
                        $strDestContent = fread($fd, filesize($strDestFile));
                    }

                }

                if ($strAppend = substr($strAppend, (strpos($strAppend, ">", strpos($strAppend, "<{$strRoot}"))+1))) {
                        fseek($fd, strpos($strDestContent, "</{$strRoot}>"));
                        fwrite($fd, $strAppend);
                        fclose($fd);
                        return true;
                }
                return false;
        }
}

function check_eau_32($file_name){
    global $arrAttributes;
	$domxml_object = domxml_open_file($file_name);
	$organization = $domxml_object->get_elements_by_tagname("organization");
	if (isset($organization[0])) {
	    $arrAttributes = $organization[0]->attributes();
		$id = getAttribute('base-template-id');
		return ($id == '{0D3592AC-B60C-47D0-B0CB-0851C2AC61B4}');
	}
	return false;
}

function transform_xml($domxml_object) {
        if( !defined("IS_TRANSLITERATE_SRC_VALUE") ) {
                define("IS_TRANSLITERATE_SRC_VALUE", false);
        }

        if(IS_TRANSLITERATE_SRC_VALUE) {

                $domxml_object = domxml_open_file($file_name);
                $elements_array = $domxml_object->get_elements_by_tagname("object");
                foreach($elements_array as $key => $element) {
                        $element->set_attribute("src", iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8",to_translit(iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$element->get_attribute("src")))));
                }
                $elements_array = $domxml_object->get_elements_by_tagname("img");
                foreach($elements_array as $key => $element) {
                        $element->set_attribute("src", iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8",to_translit(iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$element->get_attribute("src")))));
                }
                $domxml_object->dump_file($file_name);

        }

}

function getSalt() {
//        return "";
        $r1 = sql("SELECT oid FROM organizations ORDER BY oid DESC");
        $r2 = sql("SELECT tid FROM test ORDER BY tid DESC");
        srand(make_seed());
        $strRand = (string)rand();
        if (($a1 = sqlget($r1)) || ($a2 = sqlget($r2))) {
                return $a1['oid'] . $a2['tid'] . $strRand;
        } else return "1";
}

function make_seed() {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $sec + ((float) $usec * 100000);
}
die();
?>