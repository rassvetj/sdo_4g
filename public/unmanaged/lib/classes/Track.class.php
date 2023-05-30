<?php
define('ROOT', 'root');
define('LEVEL_COURSES', 1);
define('LEVEL_TRACK', 1);
define('LEVEL_COURSE', 2);
define('LEVEL_MODULE', 3);
define('COURSE_FREE', 'free');

class CCourseExportController {
    
    var $courses = array();
    var $course_current;
    var $filter;
    var $filtered;
    var $people = array();
    var $permissions = array();
    var $permissions_array = array();
    var $schedules = array();
    var $schedules_permissions = array();
    var $filelist = array();
    
    function initialize_response() {
        $this->_initialize_filter();
        $this->_check_empty_post();
        $this->_set_courses();
    }
    
    function initialize_request() {
        $this->_initialize_filter();
        $this->_check_empty();
        $this->_set_courses();
    }
    
    function _initialize_filter() {
        $filter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
        $filter->init();
        $this->filter = $filter;
    }
    
    function _check_empty(){        
        if (!is_array($this->courses) && !count($this->courses)){
            $GLOBALS['controller']->setMessage('Невозможно выполнить операцию.<br>Не создано ни одного курса.');
            $GLOBALS['controller']->terminate();
            exit();
        }
    }

    function _check_empty_post(){       
        $not_empty = false;
        if (is_array($_POST['hid_tracks']) && count($_POST['hid_tracks'])){
            foreach ($_POST['hid_tracks'] as $key => $id) {
                if (is_array($_POST["ch_track_{$id}"]) && count($_POST["ch_track_{$id}"])) {
                    $not_empty = true;
                    foreach($_POST["ch_track_{$id}"] as $cid) {
                       $this->filtered[] = $cid;
                    }
                } else {
                    unset($_POST['hid_tracks'][$key]);
                }
            }
        }

        if (is_array($_POST['ch_track_free']) && count($_POST['ch_track_free'])) {
            foreach ($_POST['ch_track_free'] as $key => $id) {
                if ($id>0) {
                    $not_empty = true;
                    $this->filtered[] = $id;
                }               
            }
        }
        
        if (!$not_empty) {
            $GLOBALS['controller']->setMessage('Невозможно выполнить операцию.<br>Не отмечен ни один курс.', JS_GO_BACK);
            $GLOBALS['controller']->terminate();
            exit();
        }
        $this->filtered = array_unique($this->filtered);
    }
    
    function _set_courses() {
        $query = "
        SELECT 
          Courses.CID,
          Courses.Title
        FROM 
          Courses
        WHERE 
          Courses.Status > 0
        ORDER BY Courses.Title
        ";
        $res = sql($query);
        while ($row = sqlget($res)){
            if (is_a($this->filter,'CCourseFilter')) {
                if (!$this->filter->is_filtered($row['CID'])) continue;             
            }
            if (is_array($this->filtered) && count($this->filtered)) {
                if (!in_array($row['CID'],$this->filtered)) continue;
            }
            $course = new CCourse($row);
            $this->courses[$row['CID']] = $course;
        }
    }

    function display() {
        $smarty = new Smarty_els();
        $smarty->assign('sitepath',$GLOBALS['sitepath']);
        $smarty->assign('okbutton',okbutton());
        $smarty->assign_by_ref('this',$this);
        $smarty->display('courses_export.tpl');        
    }
    
    function _set_people($cid) {
        if ($cid) {
            $query = "
                SELECT 
                  People.MID,
                  People.Login,
                  People.Password,
                  People.LastName,
                  People.FirstName,
                  People.Patronymic
                FROM
                  People
                  INNER JOIN Students ON (People.`MID` = Students.`MID`)
                WHERE
                  Students.CID = '{$cid}'
            ";
            $res = sql($query);
            while ($row = sqlget($res)) {
                $row['Password'] = randString(8);
                if (!isset($this->people[$row['MID']])) {
                     $this->people[$row['MID']] = implode(';', $row);
                }
            }
        }
    }    

    function _set_permissions_array() {
        if (is_array($this->people) && count($this->people)) {
            $query = "
                SELECT 
                  Students.CID, Students.MID
                FROM
                  Students
                  INNER JOIN Courses ON (Students.CID = Courses.CID)
                WHERE
                  Students.MID IN ('".join("','",array_keys($this->people))."') AND
                  Courses.Status > 1
            ";
            $res_student = sql($query);
            while($row_student = sqlget($res_student)){
                $this->permissions_array[$row_student['MID']][$row_student['CID']] = $row_student['CID'];
            }            
        }
    }    
    
    function _set_permissions() {
        if (is_array($this->people) && count($this->people)) {
            $query = "
                SELECT 
                  Students.CID, Students.MID
                FROM
                  Students
                  INNER JOIN Courses ON (Students.CID = Courses.CID)
                WHERE
                  Students.MID IN ('".join("','",array_keys($this->people))."') AND
                  Courses.Status > 1
            ";
            $res_student = sql($query);
            while($row_student = sqlget($res_student)){
                $this->permissions[$row_student['MID']][$row_student['CID']] = $row_student['CID'];
            }
            
            if (is_array($this->permissions) && count($this->permissions)) {
                while(list($mid,$cids)=each($this->permissions)) {
                    if (is_array($cids) && count($cids))
                    $this->permissions[$mid] = join(',',$this->permissions[$mid]);
                }
            }
        }
    }
    
    function execute() {
        if (is_array($this->courses) && count($this->courses)) {
                                    
            $folder_name_date = 'courses_'.date('Y-m-d_H-i');
            $folder_name = $_SERVER['DOCUMENT_ROOT'].'/temp/'.$folder_name_date;
            @mkdir($folder_name, 0777);
            @chmod($folder_name, 0777);            
            
            $this->filelist = CTrackExportController::_create_commons($folder_name);

            $folder_name_free = $folder_name . '/termfree';
            @mkdir($folder_name_free, 0777);
            @chmod($folder_name_free, 0777);            

            foreach(array_keys($this->courses) as $v) {
                $this->course_current = &$this->courses[$v];
                $this->course_current->_set_tests();
                $this->course_current->_set_structure($folder_name);
                $this->course_current->_set_metadata();
                //$this->course_current->_set_modules(); !!!
                $this->_set_people($this->course_current->id);

                $total = 1;
                $total += count($this->course_current->tests);
                $total += count($this->course_current->modules);
                if ($total>0) {
                    $increase = 100/$total;
                }
                $GLOBALS['progress']->saveProgress();
                $GLOBALS['progress']->setAction(_('Создание модулей и тестов курса').' #'.$this->course_current->id);
                $GLOBALS['progress']->setIncrease($increase);

                $course_id = $this->course_current->id;
                $folder_name_course = $folder_name_free . '/course' . $course_id;
                @mkdir($folder_name_course, 0777);
                @chmod($folder_name_course, 0777);
//                $folder_name_course_content = $folder_name_free . '/course' . $course_id . '/content';
//                @mkdir($folder_name_course_content, 0777);
//                @chmod($folder_name_course_content, 0777);                               

                $this->filelist = array_merge($this->filelist, copyDir($_SERVER['DOCUMENT_ROOT'] . '/COURSES/course' . $course_id, $folder_name_course . '/'));
                
//                $folder_name_course_tests = $folder_name_free . '/course' . $course_id . '/tests';
//                @mkdir($folder_name_course_tests, 0777);
//                @chmod($folder_name_course_tests, 0777);                               
                
//                copyDir($_SERVER['DOCUMENT_ROOT'] . '/template/interface/offline', $folder_name_course_tests . '/');
                
                $fim = new CFileOffline();
                $fim->template_content = 'index_module.tpl';
                $fim->template_dir = '/courses_export';
                $fim->document = new DocumentBlank();
                $fmm->document->root_url = $fmm->document->skin_url = '../common';
                $fmm->document->skin_url .= '/template';
                $fim->document->enable_course_navigation = ENABLE_EAUTHOR_COURSE_NAVIGATION;
                $fim->document->initialize();
                $fim->prepare_content(array('course_id' => $course_id));

                $f = fopen($folder_name_free . "/index_{$course_id}.html", 'w');
                fwrite($f, $fim->document->content);
                fclose($f);
                
                $this->filelist[] = $folder_name_free . "/index_{$course_id}.html";

                $fnm = new CFileOffline();
                $fnm->template_content = 'navigation_module.tpl';
                $fnm->document = new DocumentFrameOffline();
                $fnm->document->logo_url = '../common/images/logo/'.$this->logo;
                $fnm->document->initialize();
                $fnm->document->return_path = "../index.html";
                $fnm->document->enable_navigation = true;
                $fnm->prepare_content(array('course_id' => $course_id, 'tree' => $this->course_current->structureHtml, 'content_type' => CONTENT_EXPANDED), CONTENT_EXPANDED);
                    
                $f = fopen($folder_name_free . "/navigation_{$course_id}.html", 'w');
                fwrite($f, $fnm->document->fetch());
                fclose($f);
                
                $this->filelist[] = $folder_name_free . "/navigation_{$course_id}.html";
                    
                $fmm = new CFileOffline();
                $fmm->template_content = 'metadata_module.tpl';
                $fmm->document = new DocumentBlank();
                $fmm->document->root_url = $fmm->document->skin_url = '../common';
                $fmm->document->skin_url .= '/template';
                $fmm->document->initialize();
                $fmm->prepare_content(array('metadata' => $this->course_current->metadata, 'title' => $this->course_current->attributes['title']));
                    
                $f = fopen($folder_name_free . "/metadata_{$course_id}.html", 'w');
                fwrite($f, $fmm->document->fetch());
                fclose($f);                
                
                $this->filelist[] = $folder_name_free . "/metadata_{$course_id}.html";

                // Записываем файлы модулей
                /*
                foreach ($this->course_current->modules as $module_id => $mod) {  
                    if($module_id){
                        $fim = new CFileOffline();
                        $fim->template_content = 'index_module.tpl';
                        $fim->template_dir = '/courses_export';
                        $fim->document = new DocumentBlank();
                        $fim->document->initialize();
                        $fim->prepare_content(array('module_id' => $module_id, 'course_id' => $course_id, 'schedule_id' => $mod['schedule_id']));

                        $f = fopen($folder_name_course . "/index_{$module_id}.html", 'w');
                        fwrite($f, $fim->document->content);
                        fclose($f);
                            
                        $fnm = new CFileOffline();
                        $fnm->template_content = 'navigation_module.tpl';
                        $fnm->document = new DocumentFrameOffline();
                        $fnm->document->initialize();
                        $fnm->document->return_path = "../../index.html";
                        $fnm->prepare_content(array('item' => $mod, 'content_type' => CONTENT_COLLAPSED), CONTENT_COLLAPSED);
                        $fnm->prepare_content(array('item' => $mod, 'content_type' => CONTENT_EXPANDED), CONTENT_EXPANDED);
                            
                        $f = fopen($folder_name_course . "/navigation_{$module_id}.html", 'w');
                        fwrite($f, $fnm->document->fetch());
                        fclose($f);
                            
                        $fmm = new CFileOffline();
                        $fmm->template_content = 'metadata_module.tpl';
                        $fmm->document = new DocumentBlank();
                        $fmm->document->root_url = $fmm->document->skin_url = '../../common';
                        $fmm->document->initialize();
                            
                        $f = fopen($folder_name_course . "/metadata_{$module_id}.html", 'w');
                        fwrite($f, $fmm->document->fetch());
                        fclose($f);
                            
                        if (is_array($mod['tests']) && count($mod['tests'])) {
                            foreach ($mod['tests'] as $test) {
                                $test_xml = $folder_name_course_content . "/{$test->test_id}.xml";
                                
                                if (!file_exists($test_xml)) {                                  
                                    $xml = prepare_xml(explode($GLOBALS['brtag'], $test->data), $test);
                                    $f = fopen($test_xml, 'w');
                                    fwrite($f, $xml);                               
                                    fclose($f);
                                    
                                    $fim = new CFileOffline();
                                    $fim->template_content = 'index_test.tpl';
                                    $fim->template_dir = '/courses_export';
                                    $fim->document = new DocumentBlank();
                                    $fim->document->initialize();
                                    $fim->prepare_content(array('test_id' => $test->test_id, 'course_id' => $course_id, 'schedule_id' => $test->schedule_id));
                    
                                    $f = fopen($folder_name_course . "/test_{$test->test_id}.html", 'w');
                                    fwrite($f, $fim->document->content);
                                    fclose($f);     
                                    
                                    $fnm = new CFileOffline();
                                    $fnm->template_content = 'navigation_module.tpl';
                                    $fnm->document = new DocumentFrameOffline();
                                    $fnm->document->initialize();
                                    $fnm->document->return_path = "../../index.html";
                                    $fnm->prepare_content(array('item' => false, 'content_type' => CONTENT_COLLAPSED), CONTENT_COLLAPSED);
                                    $fnm->prepare_content(array('item' => false, 'content_type' => CONTENT_EXPANDED), CONTENT_EXPANDED);                                                                

                                    $f = fopen($folder_name_course . "/test_{$test->test_id}_navigation.html", 'w');
                                    fwrite($f, $fnm->document->fetch());
                                    fclose($f);
                                }
                            }
                        }                       
                    }
                    $GLOBALS['progress']->increase();
                }
                */

                // Записываем файлы тестов
                if (is_array($this->course_current->tests) && count($this->course_current->tests)) {
                    foreach($this->course_current->tests as $test) {
                        $test_xml = $folder_name  . "/tests/{$test->test_id}.xml";
                        if (!file_exists($test_xml)) {


                            $files = prepare_files($folder_name  . '/tests/files', explode($GLOBALS['brtag'], $test->data));
                            if (is_array($files) && count($files)) {
                                foreach($files as $file) {
                                    $this->filelist[] = $file;
                                }
                            }
                            
                            $xml = prepare_xml(explode($GLOBALS['brtag'], $test->data), $test);
                            // hardcode
                            $xml = str_replace('./COURSES/', '../termfree/', $xml);
                            $f = fopen($test_xml, 'w');
                            fwrite($f, $xml);                               
                            fclose($f);
                            
                            $this->filelist[] = $test_xml;

/*
                            $fim = new CFileOffline();
                            $fim->template_content = 'index_test.tpl';
                            $fim->template_dir = '/courses_export';
                            $fim->document = new DocumentBlank();
                            $fim->document->initialize();
                            $fim->prepare_content(array('test_id' => $test->test_id, 'course_id' => $course_id, 'schedule_id' => $test->schedule_id));
            
                            $f = fopen($folder_name_course . "/test_{$test->test_id}.html", 'w');
                            fwrite($f, $fim->document->content);
                            fclose($f);                                 
*/
/*
                            $fnm = new CFileOffline();
                            $fnm->template_content = 'navigation_module.tpl';
                            $fnm->document = new DocumentFrameOffline();
                            $fnm->document->initialize();
                            $fnm->document->return_path = "../../index.html";
                            $fnm->prepare_content(array('item' => false, 'content_type' => CONTENT_COLLAPSED), CONTENT_COLLAPSED);
                            $fnm->prepare_content(array('item' => false, 'content_type' => CONTENT_EXPANDED), CONTENT_EXPANDED);                                                                

                            $f = fopen($folder_name_course . "/test_{$test->test_id}_navigation.html", 'w');
                            fwrite($f, $fnm->document->fetch());
                            fclose($f);
*/
                        }
                        $GLOBALS['progress']->increase();
                    }
                }
                $GLOBALS['progress']->increase();                                
                
            }

            $this->_set_permissions_array();
            $this->_set_schedules();
            $this->_set_schedules_permissions();
            
            $total = 1;
            $total += count($this->schedules);
            if ($total>0) {
                $increase = 100/$total;
            }
            $GLOBALS['progress']->saveProgress();
            $GLOBALS['progress']->setAction(_('Создание расписания'));
            $GLOBALS['progress']->setIncrease($increase);
            
            if (is_array($this->schedules) && count($this->schedules)) {
                foreach($dates = array_keys($this->schedules) as $k=>$date) {
                    $fit = new CFileOffline();
                    $fit->template_dir = '/courses_export';
                    $fit->template_content = 'index_courses.tpl';
                    $fit->document = new DocumentPopupOffline();
                    $fit->document->logo_url = 'common/images/logo/'.$this->logo;                     
                    $fit->document->initialize(LEVEL_COURSES);
                    $fit->document->header = 'Расписание';
                                                            
                    $fit->prepare_content(array('prevDay' => date('d.m.Y', strtotime('-1 day', round($date/1000))), 'nextDay' => date('d.m.Y',strtotime('+1 week', round($date/1000))),'prev'=>$dates[$k-1],'next'=>$dates[$k+1],'schedules'=>$this->schedules[$date], 'schedules_permissions'=>$this->schedules_permissions));
                    
                    $f = fopen($folder_name . "/index_$date.html", 'w');
                    fwrite($f, $fit->document->fetch());
                    fclose($f);
                    
                    $this->filelist[] = $folder_name . "/index_$date.html";
                    
                    $GLOBALS['progress']->increase();
                }                
            }
            $GLOBALS['progress']->increase();

            $fit = new CFileOffline();
            $fit->template_dir = '/courses_export';
            $fit->template_content = 'index.tpl';
            $fit->document = new DocumentPopupOffline();
            $fit->document->initialize(LEVEL_COURSES);
            $fit->document->header = 'Расписание';
            $fit->prepare_content(array('schedules'=>array_keys($this->schedules)));
                
            $f = fopen($folder_name . "/index.html", 'w');
            fwrite($f, $fit->document->fetch());
            fclose($f);
            
            $this->filelist[] = $folder_name . "/index.html";
            
            $f = fopen($folder_name . "/people.csv", 'w');
            fwrite($f, implode("\n", $this->people));
            fclose($f);
            
            $this->filelist[] = $folder_name . "/people.csv";
            
            $this->messages[] = "{$folder_name_date}<br><a href='{$GLOBALS['protocol']}://{$_SERVER['HTTP_HOST']}/temp/{$folder_name_date}/people.csv' target='_blank'>открыть список слушателей</a>";
            
        }
        
        // doZip
        if ($_POST['doZip'] && isset($folder_name) && isset($folder_name_date)) {
            if (CZipOffline::create($folder_name_date, $folder_name, $this->filelist)) {
                $this->messages[] = "{$folder_name_date}.zip<br><a href=\"{$GLOBALS['sitepath']}temp/{$folder_name_date}.zip\">скачать архив оффлайн версий</a>";
            }
        }        
        
    }
    
    function _get_schedules_begin_end() {
        $sql = "SELECT MIN(schedule.begin) as begin, MAX(schedule.end) AS end
                FROM schedule WHERE schedule.CID IN ('".join("','",array_keys($this->courses))."')";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $ret['begin'] = $row['begin'];
            $ret['end'] = $row['end'];
        }
        return $ret;
    }
    
    function _get_bad_sheids() {
        $ret = array();
        if (is_array($this->courses) && count($this->courses)) {
            $sql = "
                SELECT DISTINCT 
                    schedule.SHEID
                FROM schedule
                INNER JOIN EventTools ON (EventTools.TypeID=schedule.typeID)
                WHERE EventTools.tools NOT LIKE 'module' AND EventTools.tools NOT LIKE 'tests'
                AND schedule.CID IN ('".join("','",array_keys($this->courses))."')
            ";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $ret[] = $row['SHEID'];
            }
        }
        return $ret;
    }

    function _set_schedules() {
        if ($begin_end = $this->_get_schedules_begin_end()) {
            $tweek = strtotime($begin_end['begin']);
            $end   = strtotime($begin_end['end']);
            
            while(date("w",$tweek)!=1) {
                $tweek-=11*60*60;
            }
            
            while($tweek<=$end) {
                $week_schedule = new WeekSchedule;
                $week_schedule->init_by_begin_week(date("Y-m-d", $tweek));
                $week_schedule->set_cids(array_keys($this->courses));                
                $week_schedule->set_bad_sheids($this->_get_bad_sheids());
                $this->schedules[(strtotime(date('Y-m-d 00:00:00',$tweek)) + date('Z')).'000'] = $week_schedule->get_as_array();
                $tweek += 604800;
            }
        }
    }
    
    function _set_schedules_permissions() {        
        
       $sql = "SELECT DISTINCT scheduleID.MID, scheduleID.SHEID, schedule.CID
               FROM scheduleID
               INNER JOIN schedule ON (schedule.SHEID = scheduleID.SHEID)
               WHERE schedule.CID IN ('".join("','",array_keys($this->courses))."') 
               AND scheduleID.MID > 0";

       $res = sql($sql);
       while($row = sqlget($res)) {           
           if (isset($this->permissions_array[$row['MID']]) && !isset($this->permissions_array[$row['MID']][$row['CID']])) continue;           
           $permissions[$row['MID']][$row['SHEID']] = $row['SHEID'];
       }
       if (is_array($permissions) && count($permissions)) {
           foreach($permissions as $mid => $sheids) {
               if (is_array($sheids) && count($sheids)) {
                   $ret[$mid] = join(',',$sheids);
               }
           }
       }
       $this->schedules_permissions = $ret;
    }
    
}

class CTrackExportController{
    
    var $filter;
    var $tracks = array();
    var $courses_free = array();
    var $track_current;
    var $course_current;
    var $messages;
    var $is_specialities_exists = true;
    var $filelist = array();
     
    function CTrackExportController(){
        $this->is_specialities_exists = is_specialities_exists();
    }
    
    function initialize_request(){
        $this->_check_empty();
        $this->_set_tracks();
    }
        
    function initialize_response(){
        $this->_check_empty_post();
        $this->_set_tracks();
    }
        
    function _check_empty(){        
        if (!is_array($this->tracks) && !count($this->tracks)){
            $GLOBALS['controller']->setMessage('Невозможно выполнить операцию.<br>Не создано ни одной оффлайн-версии курсов.');
            $GLOBALS['controller']->terminate();
            exit();
        }
    }

    function _check_empty_post(){       
        $not_empty = false;
        if (is_array($_POST['hid_tracks']) && count($_POST['hid_tracks'])){
            foreach ($_POST['hid_tracks'] as $key => $id) {
                if (is_array($_POST["ch_track_{$id}"]) && count($_POST["ch_track_{$id}"])){
                    $not_empty = true;
                    $this->filter[$id] = $_POST["ch_track_{$id}"];
                } else {
                    unset($_POST['hid_tracks'][$key]);
                }
            }
        }
        if (!$not_empty) {
            $GLOBALS['controller']->setMessage('Невозможно выполнить операцию.<br>Не отмечен ни один курс.', JS_GO_BACK);
            $GLOBALS['controller']->terminate();
            exit();
        }
    }
    
    function _set_tracks(){
        $query = "
            SELECT 
              tracks.trid,
              tracks.name,
              tracks2course.level,
              Courses.CID,
              Courses.Title
            FROM
              tracks
              INNER JOIN tracks2course ON (tracks.trid = tracks2course.trid)
              INNER JOIN Courses ON (tracks2course.cid = Courses.CID)       
            WHERE 
              Courses.Status > 1
            ORDER BY
              tracks.trid,
              tracks2course.level             
        ";
        $res = sql($query);
        while ($row = sqlget($res)){
            if (is_array($this->filter)){
                if (!array_key_exists($row['trid'], $this->filter)) continue;
                if (!in_array($row['CID'], $this->filter[$row['trid']])) continue;
                
            }
            if (!isset($this->tracks[$row['trid']])) {
                $this->tracks[$row['trid']] = new CTrack();
                $this->tracks[$row['trid']]->initialize_arr($row);
            }
            $track = &$this->tracks[$row['trid']];
            $course = new CCourse($row);
            $track->levels[$row['level']][] = $course;
        }
        $query = "
        SELECT 
          Courses.CID,
          Courses.Title
        FROM 
          Courses
          LEFT JOIN tracks2course ON (Courses.CID = tracks2course.CID)
        WHERE 
          tracks2course.CID IS NULL AND
          Courses.Status > 1 AND Courses.`type` = 0
        ";
        $res = sql($query);
        while ($row = sqlget($res)){
            if (is_array($this->filter)){
                if (!is_array($this->filter[COURSE_FREE]) || !in_array($row['CID'], $this->filter[COURSE_FREE])) continue;
            }
            $course = new CCourse($row);
            $this->courses_free[] = $course;
        }
        if (is_array($this->tracks) && count($this->tracks)) {
            foreach (array_keys($this->tracks) as $key) {
                $this->tracks[$key]->levels[COURSE_FREE] = &$this->courses_free;
            }
        } else {
            //if (!$this->is_specialities_exists) {
                $this->tracks[0] = new CTrack();
                $this->tracks[0]->attributes['title'] = 'courses_free';
                $this->tracks[0]->levels[COURSE_FREE] = &$this->courses_free;
                $this->tracks[0]->is_specialities_exists = $this->is_specialities_exists;               
            //}
        }
}
    
    function display(){
        $smarty = new Smarty_els();
        $smarty->assign('sitepath',$sitepath);
        $smarty->assign('action',$action);
        $smarty->assign('okbutton',okbutton());
        $smarty->assign('is_specialities_exists',is_specialities_exists());
        $smarty->assign_by_ref('this',$this);
        $smarty->assign('progressId',md5($_SESSION['s']['mid'].session_id()));
        $smarty->assign('progressTitle',_('Создание оффлайн версии'));
        $smarty->assign('progressAction',_('Обработка данных'));        
        $smarty->display('tracks_export.tpl');
    }
    
    function execute(){
        foreach (array_keys($this->tracks) as $id) {
            $this->track_current = &$this->tracks[$id];
            $this->track_current->_set_people();
            $this->track_current->_set_permissions();
            $this->_execute_track();
        }
    }
    
    function _create_commons($folder_name){
        
        $files = array();
                
        $folder_name_data = $folder_name . "/data";
        @mkdir($folder_name_data, 0777);
        @chmod($folder_name_data, 0777);
        $f = fopen ($folder_name_data . '/void', 'w');
        fclose($f);
        
        $files[] = $folder_name_data . '/void';

        $folder_name_library = $folder_name . "/library";
        @mkdir($folder_name_library, 0777);
        @chmod($folder_name_library, 0777);
        $f = fopen ($folder_name_library . '/void', 'w');
        fclose($f);
        
        $files[] = $folder_name_library . '/void';
        
        $folder_name_common = $folder_name . "/common";
        @mkdir($folder_name_common, 0777);
        @chmod($folder_name_common, 0777);
                
        $folder_name_images = $folder_name_common . '/images';
        @mkdir($folder_name_images, 0777);
        @chmod($folder_name_images, 0777);          
        //copyDir($GLOBALS['controller']->view_root->skin_dir . '/images', $folder_name_images . '/');
        copyDir($GLOBALS['wwf'] . '/images', $folder_name_images . '/');
        $files[] = $folder_name_images;

        @mkdir($folder_name_images.'/logo', 0777);
        @chmod($folder_name_images.'/logo', 0777);
        
        $filename = COption::get_value('logo');

        $this->logo = (!empty($filename) && file_exists(OPTION_FILES_REPOSITORY_PATH . $filename)) ? $filename : "logo.gif";
        $logo_file = (!empty($filename) && file_exists(OPTION_FILES_REPOSITORY_PATH . $filename)) ? OPTION_FILES_REPOSITORY_PATH . $filename : $GLOBALS['controller']->view_root->skin_dir . "/images/logo.gif";        
        copy($logo_file, $folder_name_images.'/logo/'.$this->logo);
        //$files[] = $folder_name_images.'/logo/'.$this->logo;           
        
        $folder_name_icons = $folder_name_images . '/icons';
        @mkdir($folder_name_icons, 0777);
        @chmod($folder_name_icons, 0777);           
        copyDir($_SERVER['DOCUMENT_ROOT'] . '/images/icons', $folder_name_icons . '/');
        
        $folder_name_events = $folder_name_images . '/events';
        @mkdir($folder_name_events, 0777);
        @chmod($folder_name_events, 0777);           
        copyDir($_SERVER['DOCUMENT_ROOT'] . '/images/events', $folder_name_events . '/');
        
        $document_css = new DocumentCss();
        $document_css->skin_url='.';
        $f = fopen ($folder_name_common . '/skin.css.php', 'w');
        fwrite($f, $document_css->fetch());
        fclose($f);
        $files[] = $folder_name_common . '/skin.css.php';
        
        $folder_name_js = $folder_name_common . '/js';
        @mkdir($folder_name_js, 0777);
        @chmod($folder_name_js, 0777);          

        copy($_SERVER['DOCUMENT_ROOT'] . '/js/FormCheck.js', $folder_name_js . '/FormCheck.js');
        copy($_SERVER['DOCUMENT_ROOT'] . '/js/img.js', $folder_name_js . '/img.js');
        copy($_SERVER['DOCUMENT_ROOT'] . '/js/hide.js', $folder_name_js . '/hide.js');
        copy($_SERVER['DOCUMENT_ROOT'] . '/js/dynamic.js', $folder_name_js . '/dynamic.js');
        //copy($_SERVER['DOCUMENT_ROOT'] . '/js/jquery.js', $folder_name_js . '/jquery.js');
                
        @mkdir($folder_name_js.'/lib', 0777);
        @chmod($folder_name_js.'/lib', 0777);          
        copyDir($_SERVER['DOCUMENT_ROOT'].'/js/lib', $folder_name_js.'/lib/');
        
        $files[] = $folder_name_js;
        
        @mkdir($folder_name_common.'/template', 0777);
        @chmod($folder_name_common.'/template', 0777);
        @mkdir($folder_name_common.'/template/images', 0777);
        @chmod($folder_name_common.'/template/images', 0777);
        $files = array_merge($files, copyDir($GLOBALS['controller']->view_root->skin_dir . '/images', $folder_name_common.'/template/images/'));                  
        @mkdir($folder_name_common.'/template/scripts', 0777);
        @chmod($folder_name_common.'/template/scripts', 0777);
        $files = array_merge($files, copyDir($GLOBALS['controller']->view_root->skin_dir . '/scripts', $folder_name_common.'/template/scripts/'));                  
        @mkdir($folder_name_common.'/template/stylesheets', 0777);
        @chmod($folder_name_common.'/template/stylesheets', 0777);
        $files = array_merge($files, copyDir($GLOBALS['controller']->view_root->skin_dir . '/stylesheets', $folder_name_common.'/template/stylesheets/'));
        
        $folder_name_admin = $folder_name_common . '/admin';
        @mkdir($folder_name_admin, 0777);
        @chmod($folder_name_admin, 0777);          

        copy($_SERVER['DOCUMENT_ROOT'] . '/admin/adm.js', $folder_name_admin . '/adm.js');
        
        $files[] = $folder_name_admin . '/adm.js';
        
        $folder_name_tests = $folder_name . '/tests';
        @mkdir($folder_name_tests, 0777);
        @chmod($folder_name_tests, 0777);

        @mkdir($folder_name  . '/tests/files', 0777);
        @chmod($folder_name  . '/tests/files', 0777);
        
        $files = array_merge($files, copyDir($_SERVER['DOCUMENT_ROOT'] . '/template/interface/offline', $folder_name_tests . '/'));
        

        //copy($GLOBALS['controller']->view_root->skin_dir . '/script.js', $folder_name_common . '/script.js');
        //copy($GLOBALS['controller']->view_root->skin_dir . '/oldstyle.css', $folder_name_common . '/oldstyle.css');
        copy($_SERVER['DOCUMENT_ROOT'] . '/images/menu/bullet.gif', $folder_name_common . '/images/bullet.gif');
        //$files[] = $folder_name_common . '/images/bullet.gif';
        
        return $files;
    }
    
    function _execute_track(){

        if(is_array($this->track_current->levels)){
            
            $this->track_current->folder_name = to_translit($this->track_current->attributes['title']);
            $folder_name_date = $this->track_current->folder_name . '_' . date("Y-m-d_H-i");
            $folder_name = $_SERVER['DOCUMENT_ROOT'] . '/temp/' . $folder_name_date;
            @mkdir($folder_name, 0777);
            @chmod($folder_name, 0777);

            $this->filelist = CTrackExportController::_create_commons($folder_name);

            $total = 1;
            $total += count($this->track_current->levels);
            if ($total>0) {
                $increase = 100/$total;
            }
            $GLOBALS['progress']->saveProgress();
            $GLOBALS['progress']->setAction(_('Инициализация структуры')/*.' #'.$this->track_current->id*/);
            $GLOBALS['progress']->setIncrease($increase);
            
            
            foreach (array_keys($this->track_current->levels) as $key){
                foreach (array_keys($this->track_current->levels[$key]) as $key_course) {
                    $this->track_current->levels[$key][$key_course]->_set_structure($folder_name);
                }
                $GLOBALS['progress']->increase();
            }
            $GLOBALS['progress']->increase();

            $fit = new CFileOffline();
            $fit->template_content = 'index_track.tpl';
            $fit->document = new DocumentPopupOffline();
            $fit->document->logo_url = 'common/images/logo/'.$this->logo;            
            $fit->document->initialize(LEVEL_TRACK);
            $fit->document->children['menu_breadcrumbs']->set_titles(array($this->track_current->attributes['title']));
            if (!$this->is_specialities_exists)
                $fit->document->header = 'Список курсов';
            else
                $fit->document->header = 'Специальность "' . $this->track_current->attributes['title'] . '"';

            $fit->prepare_content($this->track_current);
            $f = fopen($folder_name . "/index.html", 'w');
            fwrite($f, $fit->document->fetch());
            fclose($f);
            $this->filelist[] = $folder_name . "/index.html";

            $f = fopen($folder_name . "/people.csv", 'w');
            fwrite($f, implode("\n", $this->track_current->people));
            fclose($f);
            
            $this->filelist[] = $folder_name . "/people.csv";

            $this->messages[] = "{$folder_name_date}<br><a href='{$GLOBALS['protocol']}://{$_SERVER['HTTP_HOST']}/temp/{$folder_name_date}/people.csv' target='_blank'>открыть список слушателей</a>";

            $total = 1;
            foreach(array_keys($this->track_current->levels) as $key) { 
                $total++;               
                $total += count($this->track_current->levels[$key]);
            }
            if ($total>0) {
                $increase = 100/$total;
            }
            $GLOBALS['progress']->saveProgress();
            $GLOBALS['progress']->setAction(_('Cоздание структуры').' #'.$this->track_current->id);
            $GLOBALS['progress']->setIncrease($increase);
            
            
            foreach (array_keys($this->track_current->levels) as $key){
                $folder_name_level = $folder_name . '/term' . $key;
                @mkdir($folder_name_level, 0777);
                @chmod($folder_name_level, 0777);
                foreach (array_keys($this->track_current->levels[$key]) as $key_course) {
                    
                    $course_id = $this->track_current->levels[$key][$key_course]->id;
                    $course = &$this->track_current->levels[$key][$key_course];
                    $course->_set_tests();
                    //$course->_set_structure($folder_name);
                    $course->_set_metadata();
                    
                    $folder_name_course = $folder_name_level . '/course' . $course_id;
                    @mkdir($folder_name_course, 0777);
                    @chmod($folder_name_course, 0777);
                    //$folder_name_course_content = $folder_name_level . '/course' . $course_id . '/content';
                    //@mkdir($folder_name_course_content, 0777);
                    //@chmod($folder_name_course_content, 0777);
//                  $folder_name_course_cdata = $folder_name_course_content . '/cdata';
//                  @mkdir($folder_name_course_cdata, 0777);
//                  @chmod($folder_name_course_cdata, 0777);
//                  $folder_name_course_media = $folder_name_course_content . '/media';
//                  @mkdir($folder_name_course_media, 0777);
//                  @chmod($folder_name_course_media, 0777);
//                  $folder_name_course_media = $folder_name_course_content . '/mods';
//                  @mkdir($folder_name_course_media, 0777);
//                  @chmod($folder_name_course_media, 0777);

                    $this->filelist = array_merge($this->filelist, copyDir($_SERVER['DOCUMENT_ROOT'] . '/COURSES/course' . $course_id, $folder_name_course . '/'));
//                  @copy($_SERVER['DOCUMENT_ROOT'] . '/COURSES/course' . $course_id . "/course.xml", $folder_name_course_content . '/course.xml');
//                  copyDir($_SERVER['DOCUMENT_ROOT'] . '/COURSES/course' . $course_id . "/cdata", $folder_name_course_content . '/cdata/');
//                  copyDir($_SERVER['DOCUMENT_ROOT'] . '/COURSES/course' . $course_id . "/media", $folder_name_course_content . '/media/');
//                  copyDir($_SERVER['DOCUMENT_ROOT'] . '/COURSES/course' . $course_id . "/mods", $folder_name_course_content . '/mods/');
                    //copyDir($_SERVER['DOCUMENT_ROOT'] . '/template/interface/offline', $folder_name_course_content . '/');
                    
                    

/*                    $fic = new CFileOffline();
                    $fic->template_content = 'index_course.tpl';
                    $fic->document = new DocumentPopupOffline();
                    $fic->document->initialize(LEVEL_COURSE);
                    //$fic->document->children['menu_breadcrumbs']->set_titles(array($this->track_current->attributes['title'], $this->track_current->levels[$key][$key_course]->attributes['title']), $this->track_current->levels[$key][$key_course]->id);
                    $fic->document->children['menu_breadcrumbs']->set_titles(array($this->track_current->attributes['title'], $this->track_current->levels[$key][$key_course]->attributes['title']));
                    $fic->document->header = 'Курс "' . $this->track_current->levels[$key][$key_course]->attributes['title'] . '"';
                    $fic->prepare_content($this->track_current->levels[$key][$key_course]);
                    
                    $f = fopen($folder_name_level . "/index_{$course_id}.html", 'w');
                    fwrite($f, $fic->document->fetch());
                    fclose($f);
*/
                                        
                    $fim = new CFileOffline();
                    $fim->template_content = 'index_module.tpl';
                    $fim->template_dir = '/courses_export';
                    $fim->document->enable_course_navigation = ENABLE_EAUTHOR_COURSE_NAVIGATION;
                    $fim->document = new DocumentBlank();                    
                    $fim->document->initialize();
                    $fim->prepare_content(array('course_id' => $course_id));
    
                    $f = fopen($folder_name_level . "/index_{$course_id}.html", 'w');
                    fwrite($f, $fim->document->content);
                    fclose($f);
                    
                    $this->filelist[] = $folder_name_level . "/index_{$course_id}.html";
    
                    $fnm = new CFileOffline();
                    $fnm->template_content = 'navigation_module.tpl';
                    $fnm->document = new DocumentFrameOffline();
                    $fnm->document->logo_url = '../common/images/logo/'.$this->logo;                    
                    $fnm->document->initialize();
                    //$fnm->document->return_path = "index_{$course_id}.html";
                    $fnm->document->return_path = "../index.html";
                    $fnm->document->enable_navigation = true;
                    $fnm->prepare_content(array('course_id' => $course_id, 'tree' => $course->structureHtml, 'content_type' => CONTENT_EXPANDED), CONTENT_EXPANDED);
                        
                    $f = fopen($folder_name_level . "/navigation_{$course_id}.html", 'w');
                    fwrite($f, $fnm->document->fetch());
                    fclose($f);
                    
                    $this->filelist[] = $folder_name_level . "/navigation_{$course_id}.html";
                        
                    $fmm = new CFileOffline();
                    $fmm->template_content = 'metadata_module.tpl';
                    $fmm->document = new DocumentBlank();
                    $fmm->document->root_url = $fmm->document->skin_url = '../common';
                    $fmm->document->skin_url .= '/template';
                    $fmm->document->initialize();
                    $fmm->prepare_content(array('metadata' => $course->metadata, 'title' => $this->course_current->attributes['title']));
                        
                    $f = fopen($folder_name_level . "/metadata_{$course_id}.html", 'w');
                    fwrite($f, $fmm->document->fetch());
                    fclose($f);

                    $this->filelist[] = $folder_name_level . "/metadata_{$course_id}.html";

                    if (is_array($course->tests) && count($course->tests)) {
                        foreach($course->tests as $test) {
                            $test_xml = $folder_name  . "/tests/{$test->test_id}.xml";
                            if (!file_exists($test_xml)) {
                                
                                $files = prepare_files($folder_name  . '/tests/files', explode($GLOBALS['brtag'], $test->data));
                                if (is_array($files) && count($files)) {
                                    foreach($files as $file) {
                                        $this->filelist[] = $file;
                                    }
                                }
                                
                                $xml = prepare_xml(explode($GLOBALS['brtag'], $test->data), $test);
                                // hardcode
                                $xml = str_replace('./COURSES/','../term'.$key.'/',$xml);
                                $f = fopen($test_xml, 'w');
                                fwrite($f, $xml);                               
                                fclose($f);
                                
                                $this->filelist[] = $test_xml;
                            }
                        }
                    }                    
                    
/*                    foreach ($this->track_current->levels[$key][$key_course]->structure as $org) {  
                        if($org['mod_ref']){

                            $fim = new CFileOffline();
                            $fim->template_content = 'index_module.tpl';
                            $fim->document = new DocumentBlank();
                            $fim->document->initialize();
                            $fim->prepare_content(array('module_id' => $org['mod_ref'], 'course_id' => $course_id));

                            $f = fopen($folder_name_course . "/index_{$org['mod_ref']}.html", 'w');
                            fwrite($f, $fim->document->content);
                            fclose($f);
                            
                            $fnm = new CFileOffline();
                            $fnm->template_content = 'navigation_module.tpl';
                            $fnm->document = new DocumentFrameOffline();
                            $fnm->document->initialize();
                            $fnm->document->return_path = "../index_{$course_id}.html";
                            $fnm->prepare_content(array('item' => $org, 'content_type' => CONTENT_COLLAPSED), CONTENT_COLLAPSED);
                            $fnm->prepare_content(array('item' => $org, 'content_type' => CONTENT_EXPANDED), CONTENT_EXPANDED);
                            
                            $f = fopen($folder_name_course . "/navigation_{$org['mod_ref']}.html", 'w');
                            fwrite($f, $fnm->document->fetch());
                            fclose($f);
                            
                            $fmm = new CFileOffline();
                            $fmm->template_content = 'metadata_module.tpl';
                            $fmm->document = new DocumentBlank();
                            $fmm->document->root_url = $fmm->document->skin_url = '../../common';
                            $fmm->document->initialize();
                            $fmm->prepare_content(array('title' => $org['title'], 'metadata' => view_metadata_as_text(read_metadata(stripslashes($org['metadata']),'item'), 'item')), MESSAGE);
                            
                            $f = fopen($folder_name_course . "/metadata_{$org['mod_ref']}.html", 'w');
                            fwrite($f, $fmm->document->fetch());
                            fclose($f);                                                    
                            
                            if (is_array($org['tests']) && count($org['tests'])) {
                                foreach ($org['tests'] as $test) {
                                    $test_xml = $folder_name_course_content . "/{$test->test_id}.xml";
                                    if (!file_exists($test_xml)) {                                                                                    
                                        $xml = prepare_xml(explode($GLOBALS['brtag'], $test->data), $test);
                                        $f = fopen($test_xml, 'w');
                                        fwrite($f, $xml);
                                        fclose($f);
                                    }
                                }
                            }
                            
                        }
                    }*/
                    $GLOBALS['progress']->increase();
                }
            }
            $GLOBALS['progress']->increase();
        }
                
        // doZip
        if ($_POST['doZip'] && isset($folder_name) && isset($folder_name_date)) {
            if (CZipOffline::create($folder_name_date, $folder_name, $this->filelist)) {
                $this->messages[] = "{$folder_name_date}.zip<br><a href=\"{$GLOBALS['sitepath']}temp/{$folder_name_date}.zip\">скачать архив оффлайн версий</a>";
            }
        }        
        
    }
}


class CFileOffline {

    var $document;
    var $template_content;
    var $template_dir = '/tracks_export';
    
    function CFileOffline(){}
    
    function prepare_content($data, $content_type = CONTENT){
        $smarty = new Smarty_els();
        $smarty->assign('data',$data);
        $smarty->template_dir = $smarty->template_dir . $this->template_dir;
        switch ($content_type){
            case CONTENT_COLLAPSED:
                $area = &$this->document->content_collapsed;
                break;
            case CONTENT_EXPANDED:
                $area = &$this->document->content_expanded;
                break;
            case MESSAGE:
                $area = &$this->document->children['message']->content;
                break;
            default:
                $area = &$this->document->content;
        }
        $area = $smarty->fetch($this->template_content);
    }
}

class CTrack {
    
    var $id;
    var $attributes;
    var $people = array();
    var $permissions = array();
    var $permissions_str;
    var $levels;
    var $is_specialities_exists = true;
    var $folder_name;
    
    function CTrack(){}
    
    function initialize_arr($arr){
        $this->id = $arr['trid'];
        $this->attributes['title'] = $arr['name'];
    }
    
    function initialize_id($id){
        $this->id = (integer)$id;
        $query = "
            SELECT 
              tracks.trid,
              tracks.name,
              tracks2course.level,
              Courses.CID,
              Courses.Title
            FROM
              tracks
              INNER JOIN tracks2course ON (tracks.trid = tracks2course.trid)
              INNER JOIN Courses ON (tracks2course.cid = Courses.CID)       
            WHERE 
              tracks.trid='{$this->id}'
            ORDER BY
              tracks2course.level       
        ";
        $res = sql($query);
        while ($row = sqlget($res)) {
            if (!isset($this->attributes)) {
                $this->attributes['title'] = $row['name'];
            }
            $course = new CCourse($row);
            $this->levels[$row['level']][] = $course;
        }
    }

    function _set_people(){
        if (!$this->is_specialities_exists) {
            if (is_array($this->levels[COURSE_FREE]) && count($this->levels[COURSE_FREE])) {
                foreach($this->levels[COURSE_FREE] as $course){
                    $courses[] = $course->id;
                }
                $query = "
                    SELECT DISTINCT
                      People.MID,
                      People.Login,
                      People.Password,
                      People.LastName,
                      People.FirstName,
                      People.Patronymic
                    FROM
                      People
                      INNER JOIN Students ON (People.`MID` = Students.`MID`)
                    WHERE
                      Students.CID IN ('".join("','",$courses)."')
                ";
            }
        }
        else {
            $query = "
                SELECT 
                  People.MID,
                  People.Login,
                  People.Password,
                  People.LastName,
                  People.FirstName,
                  People.Patronymic
                FROM
                  People
                  INNER JOIN tracks2mid ON (People.`MID` = tracks2mid.`mid`)
                WHERE
                  (tracks2mid.level > 0) AND 
                  tracks2mid.trid = '{$this->id}'
            ";
        }
        if ($query) {
            $res = sql($query);
            while ($row = sqlget($res)) {
                $row['Password'] = randString(8);
                $this->people[$row['MID']] = implode(';', $row);
            }
        }
        
        if ($this->is_specialities_exists) {
            if (is_array($this->levels[COURSE_FREE]) && count($this->levels[COURSE_FREE])) {
                foreach($this->levels[COURSE_FREE] as $course){
                    $courses[] = $course->id;
                }
                $query = "
                    SELECT DISTINCT
                      People.MID,
                      People.Login,
                      People.Password,
                      People.LastName,
                      People.FirstName,
                      People.Patronymic
                    FROM
                      People
                      INNER JOIN Students ON (People.`MID` = Students.`MID`)
                    WHERE
                      Students.CID IN ('".join("','",$courses)."')
                ";
                
                $res = sql($query);
                while ($row = sqlget($res)) {
                    $row['Password'] = randString(8);
                    $this->people[$row['MID']] = implode(';', $row);
                }
            }
            
        }
    }
    
    function _set_permissions(){
        
        if (is_array($this->people) && count($this->people)) {
            $sql = "
                SELECT 
                  Students.CID, Students.MID
                FROM
                  Students
                  INNER JOIN Courses ON (Students.CID = Courses.CID)
                WHERE
                  Students.MID IN ('".join("','",array_keys($this->people))."') AND
                  Courses.Status > 1        
            "; 
            // LEFT OUTER JOIN tracks2course ON (Students.CID = tracks2course.cid)                  
            // AND (tracks2course.trid IS NULL)  
            $res = sql($sql);
            while($row = sqlget($res)) {
            	$this->permissions[$row['MID']][$row['CID']] = $row['CID'];
            }
        }               
        
        if (is_array($this->permissions) && count($this->permissions)) {
            while(list($mid,$cids)=each($this->permissions)) {
                if (is_array($cids) && count($cids))
                $this->permissions[$mid] = join(',',$this->permissions[$mid]);
            }
        }
        
/*        foreach ($this->people as $mid => $human) {
            $arr = array();
            $query = "
                SELECT 
                  Students.CID
                FROM
                  Students
                  LEFT OUTER JOIN tracks2course ON (Students.CID = tracks2course.cid)
                  INNER JOIN Courses ON (Students.CID = Courses.CID)
                WHERE
                  Students.MID = '{$mid}' AND
                  Courses.Status > 1 AND
                  (tracks2course.trid IS NULL)          
            ";
            $res_student = sql($query);
            while($row_student = sqlget($res_student)){
                $arr[] = $row_student['CID'];
            }
            if (count($arr)){
                $this->permissions[$mid] = implode(',', $arr);
            }
        }
*/    }
}

class CCourse {
    var $id;
    var $attributes;
    var $people;
    var $permissions;
    var $structure = array();
    var $structureHtml = '';
    var $modules2copy = array();
    var $tests = array();
    var $modules = array();
    var $schedules = array();   
    var $schedules_permissions;
    var $metadata = '';

    function CCourse($arr){
        $this->id = $arr['CID'];
        $this->attributes['title'] = $arr['Title'];
    }
    
    function _set_tests() {
        $this->tests = CCourse::get_tests($this->id);
    }
    
    function _getTestSchedulelinks($course_id) {
        // todo: неверно потому что одно задание может быть в многих занятиях
        $ret = array();
        if ($course_id) {
            $sql = "SELECT schedule.SHEID, scheduleID.toolParams
                    FROM schedule
                    INNER JOIN scheduleID ON (scheduleID.SHEID=schedule.SHEID)
                    WHERE schedule.CID='".(int) $course_id."'
                    AND scheduleID.toolParams LIKE '%tests_testID=%'
                    ORDER BY schedule.SHEID";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                if (preg_match("!tests_testID=([0-9]+)!",$row['toolParams'],$tid)) {
                    if ($tid = $tid[1]) {
                        $ret[$tid] = $row['SHEID'];
                    }                   
                }
            }
        }
        return $ret;
    }
    
    function _getModuleSchedulelinks($course_id) {
        // todo: неверно потому что один модуль может быть в многих занятиях
        $ret = array();
        if ($course_id) {
            $sql = "SELECT schedule.SHEID, scheduleID.toolParams
                    FROM schedule
                    INNER JOIN scheduleID ON (scheduleID.SHEID=schedule.SHEID)
                    WHERE schedule.CID='".(int) $course_id."'
                    AND scheduleID.toolParams LIKE '%module_moduleID=%'
                    ORDER BY schedule.SHEID";
            $res = sql($sql);
            while($row = sqlget($res)) {
                
                if (preg_match("!module_moduleID=([0-9]+)!",$row['toolParams'],$modID)) {
                    if ($modID = $modID[1]) {
                        $ret[$modID] = $row['SHEID'];
                    }                   
                }
            }
        }
        return $ret;
    }    
    
    // static
    function &get_tests($course_id) {
        if ($course_id) { 

            //$testScheduleLinks = CCourse::_getTestSchedulelinks($course_id);
            
            $testquestions = array();
            $query = "SELECT tid, questions FROM testquestions";
            $res = sql($query);
            
            while($row = sqlget($res)) {
                if (!empty($row['questions'])) {
                    $row['questions'] = unserialize($row['questions']);
                }
                $testquestions[$row['tid']] = $row['questions'];
            }
                       
            $query = "
                SELECT DISTINCT
                  test.tid as test_id,
                  test.random,
                  test.lim,
                  test.timelimit,
                  test.startlimit,
                  test.title,
                  test.endres,
                  test.mode,
                  test.skip,
                  test.qty
                FROM test
                LEFT JOIN organizations ON (organizations.vol1 = test.tid AND organizations.cid = '{$course_id}')
                WHERE test.cid = '{$course_id}' OR organizations.oid IS NOT NULL ORDER BY test.tid
            ";

            $res = sql($query);
            while($row = sqlget($res)) {
                if (isset($results[$row['test_id']])) continue;
                $row['selection_mode'] = 0;
                if (isset($testquestions[$row['test_id']])) {
                    $row['selection_mode'] = 1;
                    $row['questions'] = $testquestions[$row['test_id']];
                }
                $row['data'] = getField('test','data','tid',$row['test_id']);
                $task = new CCourseTask();
                //$row['schedule_id'] = $testScheduleLinks[$row['test_id']];
                $task->initialize($row);
                $results[$row['test_id']] = $task;
            }

            return $results;            
        }
    }
    
    function _set_modules() {
        $this->modules = CCourse::get_modules($this->id);
    }
    
    function _set_metadata() {
        $this->metadata = view_metadata_as_text_extended(read_metadata(stripslashes(getField('Courses', 'Description', 'CID', $this->id)),COURSES_DESCRIPTION), COURSES_DESCRIPTION);        
    }
    
    function _set_structure($folder_name = '') {
        
        $modules = array();
        $sql = "SELECT t1.bid, t1.filename
                FROM library t1
                LEFT JOIN organizations t2 ON (t2.module = t1.bid AND t2.cid = '{$this->id}')
                WHERE t1.cid = '{$this->id}' OR t2.oid IS NOT NULL";
        $res = sql($sql);
        
        $template = "/^\/..\/COURSES\/course{$this->id}\//i";
        while($row = sqlget($res)) {
            if (preg_match($template, $row['filename'])) {
                $row['filename'] = preg_replace($template, "course{$this->id}/", $row['filename']);
            } else {
                //$this->modules2copy[$row['bid']] = $row['bid'];
                @mkdir($folder_name.'/library/'.$row['bid'], 0777);
                @chmod($folder_name.'/library/'.$row['bid'], 0777);
                copyDir($_SERVER['DOCUMENT_ROOT'].'/library/'.$row['bid'], $folder_name.'/library/'.$row['bid'].'/');
                
                $row['filename'] = '../library'.$row['filename'];
            }
            
            $modules[$row['bid']] = $row['filename'];
        }
                
        $html = '';
        $content = CCourseContent::getChildren($this->id);
                
        $level = 0;
        if (is_array($content) && count($content)) {
            $html .= "<ul>\n";
            foreach($content as $index => $item) {
                if ($item->attributes['level'] > $level) {
                    $html .= "<ul>\n";
                } else {
                    if ($item->attributes['level'] < $level) {
                        $diff = $level - $item->attributes['level'];
                        for($i=0; $i<$diff; $i++) {
                            $html .= "</li>\n</ul>\n";
                        }
                    } else {
                        if ($index > 0) {
                            $html .= "</li>\n";
                        }       
                    }
                }         
                $html .= "<li ". 
                          "id = \"org_{$item->attributes['oid']}\" ".                
                          "type = \"".($item->attributes['module'] ? 'module' : ($item->attributes['vol1'] ? 'test' : 'container'))."\" ". 
                          "module_id = \"{$item->attributes['oid']}\" ".
                          "test_id = \"{$item->attributes['vol1']}\" ".
                          "run_id = \"{$item->attributes['vol2']}\"> ";
                if ($item->attributes['module'] || $item->attributes['vol1']) {
                    $html .= "<a target=\"mainFrame\" href=\"";
                    if ($item->attributes['module'] && isset($modules[$item->attributes['module']])) {
                        $html .= $modules[$item->attributes['module']];
                    }
                    if ($item->attributes['vol1']) {
                        $html .= "../tests/index-test.htm?id=".$item->attributes['vol1'];
                    }
                    $html .= "\">";
                }
                $html .= htmlspecialchars($item->attributes['title']);
                if ($item->attributes['module'] || $item->attributes['vol1']) {
                    $html .= "</a>";
                }
                $html .= "\n";
                $level = $item->attributes['level'];
            }
            if ($level > 0) {
                for($i=0; $i<$level; $i++) {
                    $html .= "</li>\n</ul>\n";
                }
            }
            $html .= "</li>\n</ul>\n";
        }
        $this->structureHtml = $html;
    }
    
    function set_structure(){
        $modules = CCourse::get_modules($this->id);     
        $mods=getModulesList($this->id);
        $items=get_organization($this->id);
        if (is_array($org=sort_organization($items))){
            foreach (array_keys($org) as $key) {
                if (array_key_exists($mod_id = $org[$key]['mod_ref'], $modules)) {
                    $org[$key]['modules'] = $modules[$mod_id]['modules'];
                    $org[$key]['tests'] = $modules[$mod_id]['tests'];
                }
            }
        }
        if (is_array($org)){
            $this->structure = array_reverse($org);
        }
    }
    
//  static
    function get_modules($course_id){
        $result = array();
        
        //$moduleScheduleLinks = CCourse::_getModuleSchedulelinks($course_id);
                
        $query = "
            SELECT DISTINCT
              mod_list.ModID as item_id,
              mod_content.Title as title,
              mod_content.mod_l as url
            FROM
              mod_list
              LEFT OUTER JOIN mod_content ON (mod_list.ModID = mod_content.ModID)
            WHERE
              mod_list.CID = '{$course_id}'
        ";
        $res = sql($query);
        $pattern = "COURSES/course{$course_id}/";
        $pattern_author = "COURSES/course{$course_id}/index.htm?id=";
        while ($row = sqlget($res)){
            //$row['schedule_id'] = $moduleScheduleLinks[$row['item_id']];
            if (substr($row['url'], 0, 1) == "/") $row['url'] = substr($row['url'], 1);
            if (strpos($row['url'], $pattern) !== false) {
                if (strpos($row['url'], $pattern_author) !== false) {
                    $row['lesson_id'] = str_replace($pattern_author, '', urldecode($row['url']));
                    $module = new CCourseModuleAuthor();
                    $module->initialize($row);
                } else {
                    $row['url'] = str_replace($pattern, '', urldecode($row['url']));
                    $module = new CCourseModuleCustom();
                    $module->initialize($row);
                }
                $result[$row['item_id']]['modules'][] = $module;
                $result[$row['item_id']]['schedule_id'] = $row['schedule_id'];
            }
        }

        $query = "
            SELECT DISTINCT
              mod_list.ModID as item_id,
              mod_list.test_id
            FROM
              mod_list
            WHERE
              mod_list.CID = '{$course_id}' AND 
              mod_list.test_id != ''
        ";      
        $res = sql($query);
        
        unset($arrTests);
        
        while ($row = sqlget($res)){
            //$row['schedule_id'] = $moduleScheduleLinks[$row['item_id']];
            foreach ($tests = explode(';', $row['test_id']) as $test_id){
                
                $arrTests[$test_id] = $test_id;
                $arrTestsMods[$test_id][$row['item_id']] = $row['item_id'];
                $arrModsShedules[$row['item_id']] = $row['schedule_id'];
/*
                $query = "
                    SELECT DISTINCT
                      test.tid as test_id, 
                      test.random,
                      test.lim,
                      test.timelimit,
                      test.startlimit,
                      test.title, 
                      test.data,
                      test.endres,
                      scheduleID.SHEID AS schedule_id                      
                    FROM test 
                    LEFT OUTER JOIN scheduleID ON (scheduleID.toolParams LIKE ".$GLOBALS['adodb']->Concat("'%tests_testID='","test.tid","'%'") .")
                    WHERE tid='{$test_id}' AND data";
                $res_test = sql($query);
                if ($row_test = sqlget($res_test)) {
                    $row_test['item_id'] = $row['item_id'];
                    $task = new CCourseTask();
                    $task->initialize($row_test);
                    $result[$row['item_id']]['tests'][] = $task;
                    $result[$row['item_id']]['schedule_id'] = $row['schedule_id'];
                }
*/                
            }
        }
        if (is_array($arrTests) && count($arrTests)) {
                //$testScheduleLinks = CCourse::_getTestSchedulelinks($course_id);     

                $testquestions = array();
                $query = "SELECT tid, questions FROM testquestions WHERE tid IN ('".join("','",$arrTests)."')";
                $res = sql($query);                
                
                while($row = sqlget($res)) {
                    if (!empty($row['questions'])) {
                        $row['questions'] = unserialize($row['questions']);
                    }                    
                    $testquestions[$row['tid']] = $row['questions'];
                }            
                
                $query = "
                    SELECT DISTINCT
                      test.tid as test_id, 
                      test.random,
                      test.lim,
                      test.timelimit,
                      test.startlimit,
                      test.title, 
                      test.data,
                      test.endres,
                      test.mode,
                      test.skip
                    FROM test 
                    WHERE tid IN ('".join("','",$arrTests)."') AND data";
                $res_test = sql($query);
                while($row_test = sqlget($res_test)) {
                    //$row_test['schedule_id'] = $testScheduleLinks[$row_test['test_id']];
                    if (is_array($arrTestsMods[$row_test['test_id']]) && count($arrTestsMods[$row_test['test_id']])) {                        
                        foreach($arrTestsMods[$row_test['test_id']] as $item_id) {
                            $row_test['selection_mode'] = 0;
                            if (isset($testquestions[$row_test['test_id']])) {
                                $row_test['selection_mode'] = 1;
                                $row_test['questions'] = $testquestions[$row_test['test_id']];
                            }
                            $row_test['item_id'] = $item_id;
                            $task = new CCourseTask();
                            $task->initialize($row_test);
                            $result[$item_id]['tests'][$row_test['test_id']] = $task;
                            $result[$item_id]['schedule_id'] = $arrModsShedules[$item_id];
                        }
                    }
                }            
        }

        return $result;
    }
    
    function &_get_structure_arr(){
        $result = array();
        $query = "SELECT * FROM organizations WHERE cid='{$this->id}'";
        $res = sql($query);
        while ($row = sqlget($res)) {
            $key = ($row['prev_ref'] == -1)  ? ROOT : $row['prev_ref'];
            $result[$key] = $row;
        }
        return $result;
    }
    
    function _set_people(){
        $query = "
            SELECT 
              People.MID,
              People.Login,
              People.Password,
              People.LastName,
              People.FirstName,
              People.Patronymic
            FROM
              People
              INNER JOIN Students ON (People.`MID` = Students.`MID`)
            WHERE
              Students.CID = '{$this->id}'
        ";
        $res = sql($query);
        while ($row = sqlget($res)) {
            $row['Password'] = randString(8);
            $this->people[$row['MID']] = implode(';', $row);
        }
    }

    function _set_permissions(){
        
        if (is_array($this->people) && count($this->people)) {
            $query = "
                SELECT 
                  Students.CID, Students.MID
                FROM
                  Students
                  INNER JOIN Courses ON (Students.CID = Courses.CID)
                WHERE
                  Students.MID IN ('".join("','",array_keys($this->people))."') AND
                  Courses.Status > 1
            ";
            
            $res_student = sql($query);
            while($row_student = sqlget($res_student)){
                $this->persmissions[$row_student['MID']][$row_student['CID']] = $row_student['CID'];
            }
            if (is_array($this->permissions) && count($this->permissions)) {
                while(list($mid,$cids)=each($this->permissions)) {
                    if (is_array($cids) && count($cids))
                    $this->permissions[$mid] = join(',',$this->permissions[$mid]);
                }
            }
        }
    }
    
}

class CCourseDummy {
    
    var $item_id;
    
    function CCourseDummy(){}
    
    function initialize($arr){
        foreach (get_class_vars(get_class($this)) as $key => $value) {
            if (isset($arr[$key])) {
                $this->$key = $this->prepare_attribute($key, $arr[$key]);
            }
        }
    }
    
    function prepare_attribute($key, $value){
        return $value;
    }
}

class CCourseModuleAuthor extends CCourseDummy{
    
    var $title;
    var $lesson_id;
    var $type = 'author';
    var $schedule_id;
    
    function CCourseModuleAuthor(){}
}

class CCourseModuleCustom extends CCourseDummy{
    
    var $title;
    var $url;
    var $type  = 'custom';
    var $schedule_id;
    
    function CCourseModule(){}
}

class CCourseTask extends CCourseDummy{
    
    var $title;
    var $test_id;
    var $data;
    var $lim;
    var $random;
    var $timelimit;
    var $startlimit;
    var $schedule_id;
    var $endres;
    var $mode;
    var $selection_mode;
    var $questions;
    var $qty;
    
    
    function CCourseTask(){}
    
    function prepare_attribute($key, $value){
        switch ($key) {
            case "lim":
                return ($value !== "0") ? $value : null;
                break;
            case "random":
                return ($value) ? "true" : "false";
                break;
            case "timelimit":
                return $value * 1000 * 60;
                break;
            case 'qty':
            case 'mode':
                return (int) $value;
                break;
            default:
                return $value;
        }
    }   
}

class CZipOffline {
    
    function prepareFiles($path) {
        $files = array();
        if ($handle = @opendir($path)) {
            while (false !== (@$file = readdir($handle))) {
                if (is_dir($path."/".$file)) {
                    if (($file != "..") && ($file != ".")) {
                        //$files[] = $path.'/'.$file;
                        $files = array_merge($files, CZipOffline::prepareFiles($path."/".$file));
                    }
                }
                else {
                    $files[] = $path.'/'.$file;
                }
            }
            @closedir($handle);
        }        
        return $files;
    }
    
    function create($zipFileName, $offlinePath = '', $files) {
        if (!empty($offlinePath)) {                                                
            require_once($GLOBALS['wwf']."/lib/PEAR/Archive/Zip.php");            
            
            //if ($files = CZipOffline::prepareFiles($offlinePath)) {            
                if ($zip = new Archive_Zip($GLOBALS['wwf'].'/temp/'.$zipFileName.'.zip')) {
                    if (is_array($files) && count($files)) {
                        $GLOBALS['progress']->saveProgress();
                        $GLOBALS['progress']->setAction(_('Архивирование оффлайн-версий'));
                        $increase = round(100/count($files));
                        if ($increase <= 0) $increase = 1;
                        $GLOBALS['progress']->setIncrease($increase);
                        foreach($files as $file) {
                            $zip->add($file, array('remove_path' => $GLOBALS['wwf'].'/temp'));
                            $GLOBALS['progress']->increase();
                        }
                    }
                    
/*
                    $GLOBALS['progress']->saveProgress();
                    $GLOBALS['progress']->setAction(_('Архивирование оффлайн-версий'));                    
                    $zip->create($offlinePath, array('remove_path' => $GLOBALS['wwf'].'/temp'));                
*/
                    return true;
                }            
            //}
        }
    }
}

?>