<?php

abstract class CCourseImportAbstract {
    var $errors = array();
    var $params = array();
    var $zipFileName;
    var $salt;
    
    abstract function init($params);
    
    abstract function import();
    
    abstract function getErrors();
}

class CCourseImportEauthor3 extends CCourseImportAbstract {
    
    var $courseDir;
    var $dirs = array('images', 'doc', 'dwf', 'exe', 'pdf', 'wrl', 'ppt', 'sounds', 'flash', 'html', 'HtmlStuff', 'video');    
    var $courseXmlFileName = 'course.xml';
    var $courseXmlContent;
    var $metadata;
    var $prev = -1;
    var $testQuestionsGroups = array();
    var $objects = array();
    
    protected function _prepareDirs($courseDir) {
        $path = $this->salt ? $courseDir."/media/".$this->salt : $courseDir."/media";
        $pathMedia = $courseDir.'/media';
        if(!file_exists($pathMedia)) {
            mkdir($pathMedia,0775);
            chmod($pathMedia,0775);
        } 
               
        if(!file_exists($path)) {
            mkdir($path,0775);
            chmod($path,0775);
        }
                
        foreach($this->dirs as $dir) {
            $dir = $path.'/'.$dir;
            if(!file_exists($dir)) {
                @mkdir($dir,0775);                        
                @chmod($dir,0775);                        
            }            
        }        
    }
    
    protected function _preparePatterns() {
        $patterns = $replacements = array();
        foreach($this->dirs as $dir) {
            $patterns[]     = $this->salt ? '!^media/'.$this->salt.'/'.$dir.'/!i' : '!^media/'.$dir.'/!i';
            $replacements[] = $this->salt ? 'media/'.$this->salt.'/'.$dir.'/' : 'media/'.$dir.'/';
        }
        
        return array($patterns, $replacements);
    }
    
    protected function _extractFiles($zip) {
        if (isset($zip['tmp_name']) && isset($zip['name'])) {
            $this->zipFileName = $GLOBALS['wwf'].'/temp/'.$zip['name'];
            if (!move_uploaded_file($zip['tmp_name'], $this->zipFileName)) {
                $this->errors[] = sprintf(_('Нет файла данных курса (%s)'), $zip['name']);
                return false;
            }
            
            // extract files
            $pathParts = pathinfo($this->zipFileName);
            if (!strtolower($pathParts["extension"])=="zip") {
                $this->error[] = sprintf(_('Неверный формат файла данных курса (%s)'), $zip['name']);
                return false;
            }
            
            $this->_prepareDirs($this->courseDir);
            list($patterns, $replacements) = $this->_preparePatterns();
            
            $path = $this->salt ? 'media/'.$this->salt : 'media';
            
            if ($zip = zip_open($this->zipFileName)) {
                while ($entry = zip_read($zip)) {
                    if (zip_entry_open($zip, $entry, "r")) {
                        $fileSize = zip_entry_filesize($entry);
                        $fileName = zip_entry_name($entry);
                        $fileName = preg_replace('/^(?:files)(.*)/i',$path.'\\1',$fileName,1);
                        if (in_array($fileName[0],array("/","\\"))) $fileName = substr($fileName,1);
                        $fileName = str_replace("\\", "/", $fileName);
                        $fileNameTmp = iconv('cp866', $GLOBALS['controller']->lang_controller->lang_current->encoding, $fileName);
                        if (!empty($fileNameTmp)) $fileName = $fileNameTmp;
                        $fileName = preg_replace($patterns,$replacements,$fileName,1);

                        if (defined("IS_TRANSLITERATE_SRC_VALUE") && IS_TRANSLITERATE_SRC_VALUE) {
                            $fileName = to_translit($fileName);
                        }

                        $pathInfo = pathinfo($fileName);
                        if (!file_exists($pathInfo['dirname'])) mkdirs($pathInfo['dirname']);

                        if ($fileSize == 0) {
                            $s = dirname($fileName);
                            if(!file_exists($fileName)) {
                               @mkdirs($fileName);
                            }
                        } else {
                            if (strtolower($fileName) == $this->courseXmlFileName) {
                                $fileName = $GLOBALS['wwf'].'/temp/'.$fileName;
                            }

                            @$buf = zip_entry_read($entry, $fileSize);
                            @$fp = fopen ($fileName, "wb+");
                            @fwrite($fp,$buf);
                            @fclose($fp);
                        }

                        zip_entry_close($entry);
                    }                    
                }
                
                zip_close($zip);
            }
            
            chdir($cwd);
            
            @unlink($this->zipFileName);
            
        }
    }
    
    protected function _transliterateSources() {
        
        if(!defined("IS_TRANSLITERATE_SRC_VALUE")) {
            define("IS_TRANSLITERATE_SRC_VALUE", false);
        }

        if (IS_TRANSLITERATE_SRC_VALUE) {
            
            if ($xml = simplexml_load_string($this->courseXmlContent)) {
                
                foreach(array('//object', '//img') as $xpath) {                
                    $result = $xml->xpath($xpath);
                    if (is_array($result) && count($result)) {
                        while(list(,$node) = each($result)) {
                            if (isset($node['src'])) {
                                $node['src'] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", to_translit(iconv('UTF-8', $GLOBALS['controller']->lang_controller->lang_current->encoding, $node['src'])));
                                if (isset($node['DB_ID']) && strtolower($node->getName()) == 'object') {
                                    $this->objects[(string) $node['DB_ID']] = $node;
                                }
                            }
                        }
                    }
                }
                
                $this->courseXmlContent = $xml->asXML();
                
            }
                        
        }
        
        return true;        
    }

    protected function _rewriteSources() {
        $pattern = "/(src=\")\.\/(files)/i";
        $replace = $this->salt ? "./media/".$this->salt : "./media";
        $this->courseXmlContent = preg_replace($pattern, "\\1{$replace}", $this->courseXmlContent);        
    }
    
    protected function _prepareCourse() {
        $courseId = (int) $this->params['courseId'];
        CCourseAdaptor::clearForImport($courseId);
        //удаляем тесты и вопросы если нужно
        if ($_POST['test_delete']) {
            CCourseAdaptor::deleteTests($courseId);
        } 
        //удаляем материалы если нужно
        if ($_POST['materials_delete']) {
            CCourseAdaptor::deleteMaterials($courseId);
        }
        
        return true;
    }
    
    /**
     * import initialization
     * $params = array(
     *     'zip'      => $_FILES['file'],
     *     'courseId' => int,
     *     'renameCourse' => boolean
     * )
     *
     * @param array $params
     */        
    public function init($params) {        
        if (empty($params['salt'])) {
            $this->salt = getSalt();
        } else {
        	$this->salt = $params['salt'];
        }
        $this->params = $params;
        if (isset($this->params['zip'])) {

        	/**********
        		Вынесено из _extractFiles (иногда распаковка не нужна, но инициализация courseDir должна быть)
        	***********/
            $this->courseDir = $GLOBALS['wwf'].COURSES_DIR_PREFIX."/COURSES/course".$this->params['courseId'];
            $cwd = getcwd();
            chdir($this->courseDir);

            // extract files
            if (!$this->_extractFiles($this->params['zip'])) {
                return false;
            }                        
        }
        
        return true;
    }
    
    function import() {
        
    }
    
    function getErrors() {
        return $this->errors;
    }
}

class CCourseImportEauthor3_2 extends CCourseImportEauthor3 {
        
	protected $_location;
	protected $_subjectId;
	protected $_testonly;
	
    public function init($params) {
        return parent::init($params);        
    }
    
    protected function _optimizeCourseXml() {
        
        $t1 = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $t2 = "<text><![CDATA[";
        $t3 = "]]></text>";                
        
        @mkdir($this->courseDir."/cdata", 0755);
        @chmod($this->courseDir."/cdata", 0755);

        if ($xml = simplexml_load_string($this->courseXmlContent, 'SimpleXMLElement', LIBXML_NOCDATA)) {

            foreach(array('//intro/text', '//subject', '//studiedproblem/text') as $xpath) {
                $result = $xml->xpath($xpath);
                while(list(, $node) = each($result)) {
                    if (isset($node['DB_ID'])) {
                        $id = $node['DB_ID'];
                        
                        if (strlen($node)) {
                            $cdataFileName = $this->courseDir."/cdata/$id.xml";
                            if ($handle = fopen($cdataFileName, "w")) {
                                fwrite($handle, $t1.$t2.$node.$t3);
                                fclose($handle);
                            }
                            $node['src'] = "cdata/$id.xml";
                            $node[0] = '';
                        }                   
                    }
                }
            }

            $this->courseXmlContent = $xml->asXML();

        }        
                
        return true;           
    }
    
    protected function _processOrganization($node) {
        if (isset($this->params['renameCourse']) && $this->params['renameCourse'] &&
            isset($node['title']) && strlen($node['title'])) {
            sql("UPDATE Courses SET Title = ".iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $GLOBALS['adodb']->Quote($node['title']))." WHERE CID = '".$this->params['courseId']."'");
        }               
        
        if (isset($node['sequencing-mode'])) {
            $this->metadata['sequencing-mode'] = (string) $node['sequencing-mode'];        
        }
        

        $attributes = $node->attributes('eAu', true);
        if (isset($attributes['base-template-id'])) {
            $this->metadata['base-template-id'] = (string) $attributes['base-template-id'];        
        }                
                                
    }
    
    protected function _processContainer($node, $level) {
        if (isset($node['visibility']) && (strtolower($node['visibility']) == 'hidden')) return false;
        
        $title = trim(iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $node['title']));
        if (!strlen($title)) $title = _('Нет заголовка');

        $sql = "INSERT INTO organizations 
                (
                    title,
                    level,
                    cid,
                    prev_ref,
                    vol1,
                    vol2,
                    mod_ref,
                    module
                ) VALUES (
                    ".$GLOBALS['adodb']->Quote($title).",
                    '{$level}',
                    '{$this->params['courseId']}',
                    '{$this->prev}',
                    0,
                    0,
                    0,
                    0                
                )";
        $res = sql($sql);
        if (!$res) {
            $this->errors[] = _('Ошибка при создании оглавления курса');
            return false;
        }
        
        $this->prev = sqllast();
                
        return $this->prev;
    }
        
    protected function _processElement($node, $level, $test = 0) {
        if (isset($node['visibility']) && (strtolower($node['visibility']) == 'hidden')) return false;

        $title = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $node['title']);
        if (!strlen($title)) $title = _('Нет заголовка');
        
        $bid = 0;
        $isEAU3 = (isset($GLOBALS['isEAU3']) && $GLOBALS['isEAU3'] == true);
        if (!$test || $isEAU3) {
            $sql = "INSERT INTO library 
                    (
                        cid,
                        mid,
                        title,
                        filename,
                        upload_date,
                        is_active_version,
                        content,
                        scorm_params
                    )
                    VALUES (
                        '{$this->params['courseId']}', 
                        {$_SESSION['s']['mid']},"
                        .$GLOBALS['adodb']->Quote($title).","
                        .$GLOBALS['adodb']->Quote("/../COURSES/course{$this->params['courseId']}/index.htm?id=".urlencode($node['DB_ID'])).",
                        NOW(),
                        1,
                        'html',
                        ''                    
                    )";
                        
            $res = sql($sql);
                        
            if (!$res) {
                $this->errors[] = _('Ошибка при создании оглавления курса');
                return false;
            } elseif ($isEAU3) {
                $test = 0;
            }
            
            $bid = sqllast();
        }
        
        $sql = "INSERT INTO organizations 
                (
                    title,
                    level,
                    cid,
                    prev_ref,
                    vol1,
                    vol2,
                    mod_ref,
                    module
                ) VALUES (
                    ".$GLOBALS['adodb']->Quote($title).",
                    '{$level}',
                    '{$this->params['courseId']}',
                    '{$this->prev}',
                    '$test',
                    0,
                    0,
                    '{$bid}'                
                )";
        $res = sql($sql);
        if (!$res) {
            $this->errors[] = _('Ошибка при создании оглавления курса');
            return false;
        }
        
        $this->prev = sqllast();
        
        return $this->prev;
        
    }

    protected function _processContainerFirstChild($node, $level) {        
        // todo: проверяем есть ли subject, references и пишем в оглавление
        
        if (isset($node->subject) && isset($node->subject[0])) {
            if (!$this->_processElement(&$node->subject[0], $level)) {
                return false;
            }
        }
        
        if (isset($node->references) && isset($node->references[0])) {
            if (!$this->_processElement(&$node->references[0], $level)) {
                return false;
            }
        }
        
        return true;
    }
    
    protected function _processTestParams($node) {
        $title = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $node['title']);
        if (!strlen($title)) $title = _('Нет заголовка');
        
        $params = array(
            'cid'        => $this->params['courseId'],
            'cidowner'   => $this->params['courseId'],
            'title'      => $title,
            'mode'       => 0,
            'lim'        => 0,
            'random'     => 0,
            'timelimit'  => 0,
            'endres'     => 1,
            'skip'       => 0,
            'datatype'   => 1,
            'data'       => '',
            'qty'        => 1,
            'sort'       => 0,
            'free'       => 0,
            'rating'     => 0,
            'status'     => 1,
            'last'       => time(),
            'created_by' => $_SESSION['s']['mid']
        );
        
        if (isset($node['navigation-mode'])) {
            $params['mode'] = (int) $node['navigation-mode'];
        }
        
        if (isset($node['selection-mode']) && isset($node['number-of-questions'])) {
            if (!intval($node['selection-mode'])) {
                $params['lim'] = (int) $node['number-of-questions'];                
            }
        }
        
        if (isset($node['shuffle-questions'])) {
            if (strtolower($node['shuffle-questions']) == 'true') {
                $params['random'] = 1;
            }
        }
        
        if (isset($node['time-limit'])) {
            $params['timelimit'] = (int) ($node['time-limit']/60000);
        }
        
        if (isset($node['show-test-stats'])) {
            if (strtolower($node['show-test-stats']) == 'false') {
                $params['endres'] = 0;
            }
        }
        
        if (isset($node['allow-test-skip'])) {
            if (strtolower($node['allow-test-skip']) == 'true') {
                $params['skip'] = 1;
            }
        }
        
        return $params;
    }

    protected function _processTestFeedback($node, $testId)
    {
        if(count($node->feedback) == 1){
            $feedback = $node->feedback;
            $this->_processFeedbackTest($feedback, $testId);
        }else{
            foreach($node->feedback as $feedback){
                $this->_processFeedbackTest($feedback, $testId);
            }

        }

    }


    protected function _processFeedbackTest($feedback, $testId)
    {
        sql("INSERT INTO test_feedback
                 (
                     title,
                     type,
                     text,
                     parent,
                     treshold_min,
                     treshold_max,
                     test_id,
                     question_id,
                     answer_id,
                     show_event,
                     show_on_values
                 )
                 VALUES
                 (
                     '" . $feedback['title'] . "',
                     " . HM_Test_Feedback_FeedbackModel::TYPE_TEST . ",
                     '". $feedback[0] ."',
                     0,
                     0,
                     0,
                     " .(int) $testId . ",
                     0,
                     0,
                     " . HM_Test_Feedback_FeedbackModel::EVENT_ANY . ",
                     ''
                 )");
    }



    protected function _processTest($node, $level) {
        
        $params = $this->_processTestParams(&$node);

        $task = new CTask($params);
        $testId = $task->create();
        $this->_processTestFeedback($node, $testId);

        if (!$testId) {
            $this->errors[] = sprintf(_('Ошибка при создании теста (%s)'), $node['DB_ID']);
            return false;
        }

        $this->testQuestionsGroups[$testId] = array();
        $kods = $this->_processQuestions(&$node, $testId);
        
        if (is_array($kods) && count($kods)) {
            sql("UPDATE test SET data = '".join(QUESTION_BRTAG, $kods)."' WHERE tid = '$testId'");
        }
        
        // process questions groups
        if (isset($node['selection-mode']) && 
            (1 == $node['selection-mode']) &&
            is_array($this->testQuestionsGroups[$testId]) && 
            count($this->testQuestionsGroups[$testId])) {

            if (isset($this->testQuestionsGroups[$testId][''])) {
                if (isset($this->testQuestionsGroups[$testId][_('Без названия')])) {
                    unset($this->testQuestionsGroups[$testId]['']);
                } else {
                    $this->testQuestionsGroups[$testId][_('Без названия')] = count($this->testQuestionsGroups[$testId]['']);                    
                    unset($this->testQuestionsGroups[$testId]['']);
                }
            }
                            
            sql("INSERT INTO testquestions 
                 (
                     tid, 
                     cid, 
                     questions
                 )
                 VALUES 
                 (
                     '$testId', 
                     '{$this->params['courseId']}', 
                     '".serialize($this->testQuestionsGroups[$testId])."'
                 )");
        }
        
        // todo: processElement
        return $this->_processElement($node, $level, $testId);        
    }
    
    protected function _processTestAbstract($node, $level) {

        if (isset($node['visibility']) && (strtolower($node['visibility']) == 'hidden')) {
            return false;
        }

        if (!$this->_subjectId) {
            $this->_location = 1;
        }

        $params = $this->_processTestParams(&$node); 
        $rq = " INSERT INTO test_abstract (title, status, created, updated, description, keywords, subject_id, location)
                VALUES ('".$params['title']."', 0,  NOW(), NOW(), '', '', ".$this->_subjectId.", ".intval($this->_location).")";
        sql($rq);
        $testId = sqllast();

        $this->_processTestFeedback($node, $testId);
        if($this->_subjectId && $testId){
        	$rq = "INSERT INTO subjects_tests (subject_id, test_id) VALUES (".$this->_subjectId.", ".$testId.")";
        	sql($rq);
        }
        if (!$testId) {
            $this->errors[] = sprintf(_('Ошибка при создании теста (%s)'), $node['DB_ID']);
            return false;
        }

        $this->testQuestionsGroups[$testId] = array();
        $kods = $this->_processQuestions(&$node, $testId);
        
        
        if (is_array($kods) && count($kods)) {
            sql("UPDATE test_abstract SET data = '".join(QUESTION_BRTAG, $kods)."', questions = ".count($kods)." WHERE test_id = '$testId'");
            foreach($kods as $kod) {
                sql(sprintf("INSERT INTO tests_questions (subject_id, test_id, kod) VALUES (%d, %d, %s)", $this->_subjectId, $testId, $GLOBALS['adodb']->Quote($kod)));
            }
        }

/*        // process questions groups
        if (isset($node['selection-mode']) && 
            (1 == $node['selection-mode']) &&
            is_array($this->testQuestionsGroups[$testId]) && 
            count($this->testQuestionsGroups[$testId])) {

            if (isset($this->testQuestionsGroups[$testId][''])) {
                if (isset($this->testQuestionsGroups[$testId][_('Без названия')])) {
                    unset($this->testQuestionsGroups[$testId]['']);
                } else {
                    $this->testQuestionsGroups[$testId][_('Без названия')] = count($this->testQuestionsGroups[$testId]['']);                    
                    unset($this->testQuestionsGroups[$testId]['']);
                }
            }
                            
            sql("INSERT INTO testquestions 
                 (
                     tid, 
                     cid, 
                     questions
                 )
                 VALUES 
                 (
                     '$testId', 
                     '{$this->params['courseId']}', 
                     '".serialize($this->testQuestionsGroups[$testId])."'
                 )");
        }*/
        $GLOBALS['testAbstractId'] = $testId;
        // todo: processElement
        return $this->_processElement($node, $level, $testId);        
    }
       
    protected function _is33AllowedQtype($node) {
        $allowed_types = array(
            "single"   => 1, // одиночный выбор
            "multiple" => 2, // множественный
            "fill"     => 5, // заполнение пропусков
            "compare"  => 3, // соответствие
            "classify" => 13, // классификация
            "sort"     => 12,  // упорядочивание
            "exercise" => 9,   // упражнение
            "fillingaps" => 14 // модернизированный на заполнение пропусков
        );

        if (isset($node['type']) && isset($allowed_types[(string) $node['type']])) {
            return true;
        }
        return false;
    }

    protected function _processQuestionParams($node, $theme = '') {
        
        $strQuestion = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $node->text);
        if (!strlen($strQuestion)) $strQuestion = _('Нет текста');
        
        $params = array(
            'qtype'       => 1,
            'qtema'       => $theme,
            'balmin'      => 0,
            'balmax'      => 1,
            'timelimit'   => 0,
            'qmoder'      => 0,
            'is_shuffled' => 0,
            'qdata'       => array($strQuestion),
            'adata'       => array(),
            'weight'      => array(),
            'cid'         => $this->params['courseId'],
            'stubs'       => array(),
            'match-case'  => 0
        );

        if (isset($node['shuffle-answers']) && (strtolower($node['shuffle-answers']) == 'true')) {
            $params['is_shuffled'] = 1;
        }
        
        if (isset($node['score-min'])) {
            $params['balmin'] = (double) $node['score-min'];
        }
        
        if (isset($node['score-max'])) {
            $params['balmax'] = (double) $node['score-max'];
        }
        
        if (isset($node['time-limit'])) {
            $params['timelimit'] = (int) $node['time-limit'];
        }
        
        $types = array(
            "single"   => 1, // одиночный выбор
            "multiple" => 2, // множественный
            "fill"     => 5, // заполнение пропусков
            "compare"  => 3, // соответствие
            "classify" => 13, // классификация
            "sort"     => 12,  // упорядочивание
            "exercise" => 9,   // упражнение
            "fillingaps" => 14, // модернизированный на заполнение пропусков
        );
        
        if (isset($node['type']) && isset($types[(string) $node['type']])) {
            $params['qtype'] = $types[(string) $node['type']];
        }
        
        if (isset($node['match-case'])) {
            $params['match-case'] = $node['match-case'] == 't' ? 1 : 0;
        }
        
        if (isset($node['stubs'])) {
            $params['stubs'] = $node['stubs'];
        }
                
        return $params;
        
    }
    
    protected function _getExerciseSize($xmlFileName) {
        $width = 800; $height = 600;
        
        if (file_exists($xmlFileName) && is_readable($xmlFileName)) {
            if ($xml = simplexml_load_file($xmlFileName)) {
                if (isset($xml['width']) && strlen($xml['width'])) {
                    $width = (string) $xml['width'];
                }
                if (isset($xml['height']) && strlen($xml['height'])) {
                    $height = (string) $xml['height'];
                }
            }
        }
        
        return array('width' => $width, 'height' => $height);
    }


    protected function _processQuestionFeedback($node, $testId, $questionId)
    {
        if(count($node->feedback) == 1){
            $feedback = $node->feedback;
            $this->_processFeedbackQuestion($feedback, $testId, $questionId);
        }else{
            foreach($node->feedback as $feedback){
                $this->_processFeedbackQuestion($feedback, $testId, $questionId);
            }

        }
    }

    protected function _processFeedbackQuestion($feedback, $testId, $questionId)
    {
        if(substr($questionId, 0,1) == '-'){
            $questionId = '0' . $questionId;
        }

        $event = HM_Test_Feedback_FeedbackModel::getEventId($feedback['show-on']);

        $treshold = HM_Test_Feedback_FeedbackModel::getTreshold($feedback['treshold']);

        sql("INSERT INTO test_feedback
                 (
                     title,
                     type,
                     text,
                     parent,
                     treshold_min,
                     treshold_max,
                     test_id,
                     question_id,
                     answer_id,
                     show_event,
                     show_on_values
                 )
                 VALUES
                 (
                     '" . $feedback['title'] . "',
                     " . HM_Test_Feedback_FeedbackModel::TYPE_QUESTION. ",
                     '". $feedback[0] ."',
                     0,
                     " . $treshold['min'] . ",
                     " . $treshold['max'] . ",
                     " .(int) $testId . ",
                     '" . $questionId . "',
                     0,
                     " . $event . ",
                     ''
                 )");
    }


    protected function _processAnswerFeedback($node, $testId, $questionId, $answerId, $accumulate, $count)
    {

        $xml = $this->getXmlData();
        if(!$xml){
            return false;
        }

        //todo Пока только те что идут после. Нужно будет для первого еще клеить те, что спереди
        $siblings = $xml->xpath("//following-sibling::feedback[preceding-sibling::answer[1][@DB_ID='" . $node['DB_ID'] . "']]");

        //print_r($siblings); exit;
        foreach($siblings as $feedback){
            $accumulate[] = $this->_processFeedbackAnswer($feedback, $testId, $questionId, $answerId);
        }


    }

    protected function _processFeedbackAnswer($feedback, $testId, $questionId, $answerId)
    {

        $event = HM_Test_Feedback_FeedbackModel::getEventId($feedback['show-on']);

        return "INSERT INTO test_feedback
                 (
                     title,
                     type,
                     text,
                     parent,
                     treshold_min,
                     treshold_max,
                     test_id,
                     question_id,
                     answer_id,
                     show_event,
                     show_on_values
                 )
                 VALUES
                 (
                     " . $GLOBALS['adodb']->Quote($feedback['title']) . ",
                     " . HM_Test_Feedback_FeedbackModel::TYPE_ANSWER. ",
                     ". $GLOBALS['adodb']->Quote($feedback[0]) .",
                     0,
                     0,
                     0,
                     " .(int) $testId . ",
                     '\$questionId',
                     " . $answerId . ",
                     " . $event . ",
                     " . $GLOBALS['adodb']->Quote(serialize(HM_Test_Feedback_FeedbackModel::getAnswerValues($feedback['show-on-value']))) . "
                 )";
    }

    protected function _processQuestion($node, $testId, $exercise = false, $theme = '') {
        if (!$this->_is33AllowedQtype(&$node)) {
        	$title = trim(iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $node['title']));
        	$title = (strlen($title) > 100) ? substr($title, 0, 100) : $title;
            $this->errors[] = sprintf(_('Ошибка при создании вопроса "%s"<br>(не поддерживается тип вопроса)'), $title);
            return false;
        }

        $params = $this->_processQuestionParams(&$node);
        if ($exercise) {
            if (isset($node->object['src']) && strlen($node->object['src'])) {
                if (substr($node->object['src'], -9) == 'index.htm') {
                    $params['url'] = substr($node->object['src'], 1, -9).'FlashExercise.swf';
                    $size = $this->_getExerciseSize($this->courseDir.substr($node->object['src'], 1, -9).'course.xml');
                    $params['qdata'][] = $size['width'];
                    $params['qdata'][] = $size['height'];
                }
            }
            $params['qtype'] = 9;
        }
        
        // prepare qdata, adata and weight (process answers)        
        $count = 1;
        $answerAccumulator = array();
        foreach($node->answers->answer as $answer) {
            switch($params['qtype']) {
                case 1: // single
                    $params['qdata'][] = $count;
                    $params['qdata'][] = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $answer);
                    if (isset($node['score-by-weights']) && (strtolower($node['score-by-weights']) == 'true')) {
                        $params['weight'][$count] = (double) $answer['weight'];
                    }
                    if (isset($answer['type']) && (strtolower($answer['type']) == 'true')) {
                        $params['adata'][] = (int) $count;
                    }
                    // тут не ансвер, а их сиблингов.
                    $this->_processAnswerFeedback($answer, $testId, '', $count ,&$answerAccumulator, $count);
                    break;
                case 2: // multiple
                    $params['qdata'][] = $count;
                    $params['qdata'][] = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $answer);
                    if (isset($node['score-by-weights']) && (strtolower($node['score-by-weights']) == 'true')) {
                        $params['weight'][$count] = (double) $answer['weight'];
                    }
                    if (isset($answer['type']) && (strtolower($answer['type']) == 'true')) {
                        $params['adata'][] = 1;
                    } else {
                        $params['adata'][] = 0;
                    }
                    // тут не ансвер, а их сиблингов.
                    $this->_processAnswerFeedback($answer, $testId, '', $count ,&$answerAccumulator, $count);
                    break;
                case 3: //compare
                    $params['qdata'][] = $count;
                    $params['qdata'][] = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $answer);
                    $params['qdata'][] = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $answer['right']);
                    if (isset($node['score-by-weights']) && (strtolower($node['score-by-weights']) == 'true')) {
                        $params['weight'][$count] = $answer['weight'];
                    }
                    // тут не ансвер, а их сиблингов.
                    $this->_processAnswerFeedback($answer, $testId, '', $count ,&$answerAccumulator, $count);
                    break;
                case 5: // fill
                    if ($count == 1) {
                        $params['qdata'][] = '';
                    }
                    $answer['right'] = str_replace(array('[',']','"'), '', $answer['right']);
                    $params['qdata'][] = str_replace('|',';', iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $answer['right']));
                    $params['qdata'][] = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $answer);
                    // тут не ансвер, а их сиблингов.
                    $this->_processAnswerFeedback($answer, $testId, '', $count ,&$answerAccumulator, $count);
                    break;
                case 12: // sort
                    $params['qdata'][] = $count;
                    $params['qdata'][] = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $answer);
                    $params['qdata'][] = '';
                    if (isset($node['score-by-weights']) && (strtolower($node['score-by-weights']) == 'true')) {
                        $params['weight'][$count] = $answer['weight'];
                    }
                    // тут не ансвер, а их сиблингов.
                    $this->_processAnswerFeedback($answer, $testId, '', $count ,&$answerAccumulator, $count);
                    break;
                case 13:
                    $params['qdata'][] = $count;
                    $params['qdata'][] = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $answer);
                    $params['qdata'][] = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $answer['class-group']);
                    if (isset($node['score-by-weights']) && (strtolower($node['score-by-weights']) == 'true')) {
                        $params['weight'][$count] = $answer['weight'];
                    }
                    // тут не ансвер, а их сиблингов.
                    $this->_processAnswerFeedback($answer, $testId, '', $count ,&$answerAccumulator, $count);
                    break;
                case 14: // fillingaps
                    if (!isset($answers)) {
                        $answers = array();
                        $db_ids = array();
                        $stubs = isset($params['stubs']) && $params['stubs'] ? json_decode($params['stubs']) : array();
                        array_walk($stubs, create_function('&$v,$k', '$v = (string) iconv("UTF-8", $GLOBALS["controller"]->lang_controller->lang_current->encoding, $v);'));
                    }
                    
                    $tmp = array();
                    $tmp['right'] = isset($answer['right']) && $answer['right'] ? json_decode($answer['right']) : array('');
                    array_walk($tmp['right'], create_function('&$v,$k', '$t = $v; $v = (string) iconv("UTF-8", $GLOBALS["controller"]->lang_controller->lang_current->encoding, $t);'));
                    $tmp['dd'] = $answer['dd'] == 't' ? 1 : 0;
                    $tmp['multiple'] = $answer['multiple'] == 't' ? 1 : 0;
                    
                    if ($tmp['dd']) {
                        $stubs = array_merge($stubs, $tmp['right']);
                    }
                    $answers[] = $tmp;
                    $db_ids[] = $answer['DB_ID'];
                    $this->_processAnswerFeedback($answer, $testId, '', $count, &$answerAccumulator, $count);
                    break;
            }
            
            $count++;
        }
        if ($params['qtype'] == 14) {
            $title = $params['qdata'][0];
            if (preg_match_all("#<a [^<]*>(.*)</a>#iuUs", $title, $matches)) {
            //if (preg_match_all("/<a [^>]*>[^<]*<\/a>/i", $title, $matches)) {
                foreach($matches[0] as $match) {
                    if (preg_match("/EUL:({[0-9A-Za-z-]+})/i", $match, $db_id)) {
                        if (in_array($db_id[1], $db_ids)) {
                            $key = array_search($db_id[1], $db_ids);
                            $answer = array('dd'            => $answers[$key]['dd'], 
                                            'multiple'      => $answers[$key]['multiple'],
                                            'right'         => $answers[$key]['right'],
                                            'match-case'    => $params['match-case'],
                                            'stubs'         => $stubs);
                            //$replace = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, serialize($answer));
                            $replace = serialize($answer);
                            $title = str_replace($match,  QUESTION_BRTAG . $replace . QUESTION_BRTAG, $title);
                        }
                    } 
                }
            }
            $params['qdata'] = explode(QUESTION_BRTAG, $title);
        }
        
        // save
        if (is_array($params['qdata']) && count($params['qdata'])) {
            $params['qdata'] = join(QUESTION_BRTAG, $params['qdata']);
        } else {
            $params['qdata'] = '';
        }
        
        // на случай картинок в текстах вопроса
        $pattern = $this->salt ? "/(src=\"\.)(\/media\/{$this->salt}\/)/i" : "/(src=\"\.)(\/media\/)/i";
        $replace = "/COURSES/course{$this->params['courseId']}";
        $params['qdata'] = preg_replace($pattern, "\\1{$replace}\\2", $params['qdata']);

        if (defined("IS_TRANSLITERATE_SRC_VALUE") && IS_TRANSLITERATE_SRC_VALUE) {
            $params['qdata'] = preg_replace_callback(
                "/src=\".+?\"/i",
                create_function('$matches', 'return to_translit($matches[0]);'),
                $params['qdata']);
        }
        if (is_array($params['adata']) && count($params['adata'])) {
            $params['adata'] = join(QUESTION_BRTAG, $params['adata']);
        } else {
            $params['adata'] = '';
        }        
        
        if (is_array($params['weight']) && count($params['weight'])) {
            $params['weight'] = serialize($params['weight']);
        } else {
            $params['weight'] = '';
        }

        $params['qtema'] = $theme;

        unset($params['stubs']);
        unset($params['match-case']);
        
        $question = new CQuestion($params);
        $kod = $question->create();

        $this->_processQuestionFeedback($node, $testId, $kod);
       // pr($answerAccumulator); exit;

        foreach($answerAccumulator as $query){
            sql(str_replace("'\$questionId'", "'" . $kod . "'", $query));
        }

        if (!$kod) {
            $this->errors[] = sprintf(_('Ошибка при создании вопроса (%s)'), $node['DB_ID']);
            return false;
        }
        
        // is-required ?
        if (isset($node['is-required']) && (strtolower($node['is-required']) == 'true')) {
            sql("INSERT INTO testneed (tid, kod) VALUES ('$testId', '$kod')");
        }

        return $kod;
        
    }
    
    protected function _processQuestionGroup($node, $testId) {
        $kods = array();
        $theme = '';
        if (isset($node['name']) && isset($node['selection-limit'])) {
            $theme = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $node['name']);
            if (!strlen($theme)) {
                $theme = _('Без названия');
            }
            
            if (!isset($this->testQuestionsGroups[$testId][$theme])) {
                $this->testQuestionsGroups[$testId][$theme] = (int) $node['selection-limit'];
            }
        }
        
        foreach($node->children() as $child) {
            $exercise = false;
            switch(strtolower($child->getName())) {
                case 'question-exercise':
                    $exercise = true;
                case 'question':                    
                    if ($kod = $this->_processQuestion(&$child, $testId, $exercise, $theme)) {
                        $kods[] = $kod;
                    }
                    break;                
            }
        }
        
        return $kods;
    }
    
    protected function _processQuestions($node, $testId) {
        $kods = array();
        foreach($node->children() as $child) {
            $exercise = false;
            switch(strtolower($child->getName())) {
                case 'question-exercise':
                    $exercise = true;                
                case 'question':
                    if ($kod = $this->_processQuestion(&$child, $testId, $exercise)) {
                        $kods[] = $kod;
                        $this->testQuestionsGroups[$testId][''][$kod] = $kod;  
                    }
                    break;
                case 'question-group':
                    $kod = $this->_processQuestionGroup(&$child, $testId);
                    if (is_array($kod) && count($kod)) {
                        $kods = array_merge($kods, $kod);
                    }
                    break;
            }
        }
        return $kods;
    }
    
    protected function _processGlossary($node) {
        foreach($node->children() as $child) {
            if (strtolower($child->getName()) == 'text') {
                $title = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $child['title']);
                if (strlen($title)) {
                    $description = iconv("UTF-8", $GLOBALS['controller']->lang_controller->lang_current->encoding, $child);
                    sql("INSERT INTO glossary (name, cid, description) VALUES (".$GLOBALS['adodb']->Quote($title).", '{$this->params['courseId']}', ".$GLOBALS['adodb']->Quote($description).")"); 
                }
            }
        }
    }
            
    protected function _parse($node, $level) { 	
        switch(strtolower($node->getName())) {
            case 'glossary':
                $this->_processGlossary(&$node);
                return true;
                break;
            case 'test':
            	if($this->_testonly) $this->_processTestAbstract(&$node, $level);
                else $this->_processTest(&$node, $level);
                return true;
                break;
            case 'appendix-item':
                return true;
                break;
            case 'studiedproblem':
                if (!$this->_processElement(&$node, $level)) {
                    return true;
                }
                break;
            case 'lesson':
            case 'unit':
                if (!$this->_processContainer(&$node, $level++)) {
                    return true;
                }
                $this->_processContainerFirstChild(&$node, $level);
                break;
            case 'organization':
                if(!$this->_testonly) $this->_prepareCourse();
                $this->_processOrganization(&$node);
                break;
        }
        
        foreach($node->children() as $child) {
            $this->_parse($child, $level);
        }
    }

    protected function _import() {
        if ($xml = simplexml_load_string($this->courseXmlContent, 'SimpleXMLElement', LIBXML_NOCDATA)) {
            $this->_xmlData = $xml;

            $this->_parse($xml, 0);
        }
    }

    public function getXmlData(){
        return $this->_xmlData;
    }

    public function _parseTestResources()
    {
        $GLOBALS['resources'] = array();
        if ($this->_testonly) {
            $resourceIds = array();
            if (preg_match_all('/InnerLink="EUL\:(.+?)"/i', $this->courseXmlContent, $matches)) {
                if (isset($matches[1]) && is_array($matches[1]) && count($matches[1])) {
                    foreach($matches[1] as $match) {
                        $resourceIds[$match] = $match;
                    }
                }
            }

            if (count($resourceIds)) {

                if ($xml = simplexml_load_string($this->courseXmlContent)) {

                    foreach($resourceIds as $resourceId) {
                        foreach(array('//object[@DB_ID="'.$resourceId.'"]') as $xpath) {
                            $result = $xml->xpath($xpath);
                            if (is_array($result) && count($result)) {
                                while(list(,$node) = each($result)) {
                                    if (isset($node['src'])) {
                                        $GLOBALS['resources'][] = array(
                                            'title' => basename((string) $node['src']),
                                            'db_id' => (string) $node['DB_ID'],
                                            'filename' => (string) $node['src']
                                        );
                                    }
                                }
                            }
                        }
                    }
                }

            }
        }

    }
        
    public function import($testonly = false, $subjectId = 0, $location = 0) {
    	$this->_testonly = $testonly;
    	$this->_subjectId = $subjectId;
        $this->_location = $location;
    	
        if (count($this->errors)) return false;
        $courseXml = $GLOBALS['wwf'].'/temp/'.$this->courseXmlFileName;
        
        if (!file_exists($courseXml)) {
            $this->errors = sprintf(_('') ,$this->courseXmlFileName);
            return false;
        }

        $this->courseXmlContent = file_get_contents($courseXml);
        
        // transliterate sources OBJECT and IMG tags
        $this->_transliterateSources();
        
        // rewrite sources
        $this->_rewriteSources();
        
        // optimize course.xml (cut cdata)
        $this->_optimizeCourseXml();

        // Парсим ресурсы если они есть
        $this->_parseTestResources();
        
        // save course.xml
        file_put_contents($this->courseDir.'/'.$this->courseXmlFileName, $this->courseXmlContent);
        @unlink($courseXml);
        
        // import
        $this->_import();
                                        
        @unlink($courseXml);
    }
}

class CCourseImportFactory {
    public function factory($format) {
        switch($format) {
            case IMPORT_TYPE_EAU3_2:
                return new CCourseImportEauthor3_2();
                break;
        }
    }
}

?>