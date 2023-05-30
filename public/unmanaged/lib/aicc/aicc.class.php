<?php
// require organization_exp.php - function getSalt()
class CAicc {

    var $errors = array();
    var $salt;
    var $courses = array();

    function get_errors() {
        if (is_array($this->errors) && count($this->errors)) return $this->errors;
    }

    function parse_package($pkg, $cid, $change_course_info = false) {
        if (!move_uploaded_file($pkg['tmp_name'], $GLOBALS['tmpdir']."/".$pkg['name'])) {
            if (($pkg['error'] != UPLOAD_ERR_OK) || !file_exists($GLOBALS['tmpdir'].'/'.$pkg['name'])) {
                $this->errors[] = "Нет файла содержания курса ".$pkg['name'];
            }
        }
        if (!count($this->errors)) {
            $this->salt = getSalt();
            $course_dir = $GLOBALS['wwf'].COURSES_DIR_PREFIX."/COURSES/course".(int) $cid;
            if ($this->_unzip_package($GLOBALS['tmpdir'].'/'.$pkg['name'],$course_dir)) {
                // Обработка пути к файлам в xml
                // Обработка файлов описания курса
                if ($this->_parse_aicc($course_dir)) {
                    $this->_process_courses($cid, $change_course_info);
                }
                //pr($this->courses);
                //die();

                @unlink($GLOBALS['tmpdir'].'/'.$pkg['name']);
            }
        }

        if (!count($this->errors)) return true; else return false;
    }

    function _create_module($module,$cid, $version = 'AICC') {
        if (strstr('http://',$module['file_name'])===false)
            $module['file_name'] = '/../COURSES/course'.(int) $cid.'/'.$module['file_name'];
        if (strlen($module['web_launch'])) {
            $module['file_name'] .= '?'.$module['web_launch'];
        }

        if (isset($module['title'])) {
            $module['title'] = htmlspecialchars($module['title'], ENT_QUOTES);
        }

        if (isset($module['description'])) {
            $module['descriptiion'] = htmlspecialchars($module['description'], ENT_QUOTES);
        }
        $mid = ($_SESSION['s']['mid']) ? $_SESSION['s']['mid'] : 0;
        $sql = "INSERT INTO library (
                    cid,
                    mid,
                    title,
                    filename,
                    upload_date,
                    is_active_version,
                    content,
                    scorm_params".(count($metaData) ? ','.join(',',array_keys($metaData)) : '').")
                VALUES (
                    $cid,
                    {$mid},
                    ".$GLOBALS['adodb']->Quote($module['title']).",
                    ".$GLOBALS['adodb']->Quote($module['file_name']).",
                    NOW(),
                    1,
                    ".$GLOBALS['adodb']->Quote($version).",
                    ".$GLOBALS['adodb']->Quote(serialize($module)).")";
        sql($sql);

        $ret = sqllast();
        return $ret;

    }

    function _change_course_info($cid, $info) {
        if (($cid > 0) && is_array($info)) {
            $sql = "UPDATE Courses
                    SET Title=".$GLOBALS['adodb']->Quote($info['title'])."
                    WHERE CID='".(int) $cid."'";
            sql($sql);
        }
    }

    function _process_tree($cid, &$tree, $parent, &$elements, &$ids, $version = 'AICC') {
        if (is_array($tree[$parent]) && count($tree[$parent])) {
            if (isset($ids[$parent])) {
                $prev_ref = $ids[$parent]['id'];
                $level    = $ids[$parent]['level'] + 1;
            }
            foreach($tree[$parent] as $element) {
                if (isset($elements[$element]) && isset($ids[$parent])) {

                    $vv = $elements[$element];

                    if (empty($vv['title'])) $vv['title'] = $element;
                    $mod_ref = '';
                    if (isset($vv['file_name']) && !empty($vv['file_name'])) {
                        $mod_ref = $this->_create_module($vv,$cid,$version);
                    }
                    $sql = "INSERT INTO organizations
                            (title,cid,level,prev_ref,module,mod_ref,vol1,vol2)
                            VALUES
                            (".$GLOBALS['adodb']->Quote($vv['title']).",
                            '".(int) $cid."',
                            '".(int) $level."',
                            '".(int) $prev_ref."',
                            '".(int) $mod_ref."',
                            '0',
                            '0',
                            '0')";
                    sql($sql);
                    $ids[$element]['id']    = sqllast();
                    $ids[$element]['level'] = $level;

                    $prev_ref = $ids[$element]['id'];

                    if (isset($tree[$element])) {
                        $prev_ref = $this->_process_tree($cid, $tree, $element, $elements, $ids);
                    }
                }
            }
        }

        return $prev_ref;
    }

    function _process_courses($cid, $change_course_info = false) {

        $ids = array();

        if (isset($GLOBALS['progress'])) {
            $GLOBALS['progress']->setAction(_('Обработка файлов курса'));
            $GLOBALS['progress']->saveProgress(0);
            $GLOBALS['progress']->setIncrease(count($this->courses)/100);
        }

        $this->_prepare_course($cid);
        foreach($this->courses as $k=>$v) {
            if (empty($v['title'])) $v['title'] = $k;
/*            $sql = "INSERT INTO organizations
                    (title,cid,level,prev_ref)
                    VALUES
                    (".$GLOBALS['adodb']->Quote($v['title']).",'".(int) $cid."','0','-1')";
            sql($sql);
*/
            $ids['root']['id'] = -1;
            $ids['root']['level'] = -1;

            if ($change_course_info && !empty($v['title'])) $this->_change_course_info($cid, $v);

            $this->_process_tree($cid, $v['tree'], 'root', $v['elements'], $ids, $v['version']);

            /*
            if (is_array($v['elements']) && count($v['elements'])) {
                foreach($v['elements'] as $kk=>$vv) {
                    if (empty($vv['title'])) $vv['title'] = $kk;
                    if (isset($ids[strtolower($vv['parent'])])) {
                        $prev_ref = $ids[strtolower($vv['parent'])]['id'];
                        $level = (int) ($ids[strtolower($vv['parent'])]['level'] + 1);
                    }
                    $mod_ref = '';
                    if (isset($vv['file_name']) && !empty($vv['file_name']))
                        $mod_ref = $this->_create_module($vv,$cid,(isset($v['version']) ? $v['version'] : 'AICC'));
                    $sql = "INSERT INTO organizations
                            (title,cid,level,prev_ref,module,mod_ref,vol1,vol2)
                            VALUES
                            (".$GLOBALS['adodb']->Quote($vv['title']).",
                            '".(int) $cid."',
                            '".(int) $level."',
                            '".(int) $prev_ref."',
                            '".(int) $mod_ref."',
                            '0',
                            '0',
                            '0')";
                    sql($sql);
                    $ids[strtolower($kk)]['id'] = sqllast();
                    $ids[strtolower($kk)]['level'] = $level;

                }
            }
            */

            if (isset($GLOBALS['progress'])) {
                $GLOBALS['progress']->increase();
            }
        }
        if (isset($GLOBALS['progress'])) {
            $GLOBALS['progress']->saveProgress(100);
        }
        return (count($ids)>0);
    }

    function _prepare_course($cid) {
        CCourseAdaptor::clearForImport($cid);
        //удаляем тесты и вопросы если нужно
        if ($_POST['test_delete']) {
            CCourseAdaptor::deleteTests($cid);
        }
        //удаляем материалы если нужно
        if ($_POST['materials_delete']) {
            CCourseAdaptor::deleteMaterials($cid);
        }
    }

    function _get_aicc_columns($row,$mastername='system_id') {
        $tok = strtok(strtolower($row),"\",\n\r");
        $result->columns = array();
        $i=0;
        while ($tok) {
            $tok = trim($tok);
            if ($tok !='') {
                $result->columns[] = $tok;
                if ($tok == $mastername) {
                    $result->mastercol = $i;
                }
                $i++;
            }
            $tok = strtok("\",\n\r");
        }
        return $result;
    }

    function _forge_cols_regexp($columns,$remodule='(".*")?\s*,\s*') {
        $regexp = '/^';
        foreach ($columns as $column) {
            $regexp .= $remodule;
        }
        if (substr($regexp,-4) == ',\s*') {
            $regexp = substr($regexp,0,-4);
        }
        $regexp .= '/';
        return $regexp;
    }

    function _parse_aicc($path) {

        $files = array();
        if ($handle = opendir($path)) {
            while (($file = readdir($handle)) !== false) {
                $ext = substr($file,strrpos($file,'.'));
                $extension = strtolower(substr($ext,1));
                $id = strtolower(basename($file,$ext));
                if (!empty($extension))
                $files[$id]->$extension = $file;
            }
            closedir($handle);
        }

        if (isset($GLOBALS['progress'])) {
            $GLOBALS['progress']->setAction(_('Обработка файлов курса'));
            $GLOBALS['progress']->saveProgress(0);
            $GLOBALS['progress']->setIncrease(count($files)/100);
        }
        foreach ($files as $courseid => $id) {
            if (isset($id->crs) && is_file($path.'/'.$id->crs))
                $this->_parse_crs($path.'/'.$id->crs,$courseid);

            if (isset($id->des) && is_file($path.'/'.$id->des))
                $this->_parse_des($path.'/'.$id->des,$courseid);

            if (isset($id->au) && is_file($path.'/'.$id->au))
                $this->_parse_au($path.'/'.$id->au,$courseid);

            if (isset($id->cst) && is_file($path.'/'.$id->cst))
                $this->_parse_cst($path.'/'.$id->cst,$courseid);

            if (isset($id->ort)) {
            }

            if (isset($id->pre) && is_file($path.'/'.$id->pre))
                $this->_parse_pre($path.'/'.$id->pre,$courseid);

            if (isset($id->cmp)) {
            }

            if (isset($GLOBALS['progress'])) {
                $GLOBALS['progress']->increase();
            }
        }

        if (isset($GLOBALS['progress'])) {
            $GLOBALS['progress']->saveProgress(100);
        }

        return (is_array($this->courses) && count($this->courses));
    }

    function convertFileToUtf($file)
    {
        if (is_readable($file) && is_writable($file)) {
            $content = file_get_contents($file);
            $charset = detectEncoding($content);
            $content = iconv($charset, 'UTF-8', $content);
            return file_put_contents($file, $content);
        }
    }

    function _parse_crs($file,$courseid) {
        $this->convertFileToUtf($file);
        $rows = file($file);
        foreach ($rows as $line=>$row) {
            if (preg_match("/^(.+)=(.+)$/",$row,$matches)) {
                switch (strtolower(trim($matches[1]))) {
                    case 'course_creator':
                        $this->courses[$courseid]['creator'] = trim($matches[2]);
                    break;
                    case 'course_id':
                        $this->courses[$courseid]['id'] = trim($matches[2]);
                    break;
                    case 'course_system':
                        $this->courses[$courseid]['system'] = trim($matches[2]);
                    break;
                    case 'course_title':
                        $this->courses[$courseid]['title'] = trim($matches[2]);
                    break;
                    case 'version':
                        $this->courses[$courseid]['version'] = 'AICC_'.trim($matches[2]);
                    break;
                }
            }
            if (!isset($this->courses[$courseid]['version'])) {
                $this->courses[$courseid]['version'] = 'AICC';
            }
            if (preg_match("/\[course_description\]/i",$row)) $this->courses[$courseid]['description'] = $this->_parse_crs_description($rows,$line);
        }
    }

    function _parse_crs_description($rows,$line) {
        $line++;
        while($line<=count($rows)) {
            if (preg_match("/\[[_\w]+\]/",$rows['$line'])) break;
            $ret .= $rows[$line++].'\n';
        }
        return $ret;
    }

    function _parse_des($file,$courseid) {
        $this->convertFileToUtf($file);
        $rows = file($file);
        $columns = $this->_get_aicc_columns($rows[0]);
        $regexp = $this->_forge_cols_regexp($columns->columns);
        for ($i=1;$i<count($rows);$i++) {
            if (preg_match($regexp,$rows[$i],$matches)) {
                for ($j=0;$j<count($matches)-1;$j++) {
                    $column = $columns->columns[$j];
                    $this->courses[$courseid]['elements'][strtolower(substr(trim($matches[$columns->mastercol+1]),1,-1))][trim($column)] = trim(substr(trim($matches[$j+1]),1,-1));
                }
            }
        }
    }

    function _parse_au($file,$courseid) {
        $this->convertFileToUtf($file);
        $rows = file($file);
        $columns = $this->_get_aicc_columns($rows[0]);
        $regexp = $this->_forge_cols_regexp($columns->columns);
        for ($i=1;$i<count($rows);$i++) {
            if (preg_match($regexp,$rows[$i],$matches)) {
                for ($j=0;$j<count($matches)-1;$j++) {
                    $column = $columns->columns[$j];
                    $this->courses[$courseid]['elements'][strtolower(substr(trim($matches[$columns->mastercol+1]),1,-1))][trim($column)] = trim(substr(trim($matches[$j+1]),1,-1));
                }
            }
        }
    }

    function _parse_cst($file,$courseid) {
        $this->convertFileToUtf($file);
        $rows = file($file);
        $columns = $this->_get_aicc_columns($rows[0],'block');
        $regexp = $this->_forge_cols_regexp($columns->columns,'("[\w]+")?\s*,?\s*');
        for ($i=1;$i<count($rows);$i++) {
            if (preg_match($regexp,$rows[$i],$matches)) {
                for ($j=0;$j<count($matches)-1;$j++) {
                    if ($j != $columns->mastercol) {
                        //$this->courses[$courseid]['elements'][substr(trim($matches[$j+1]),1,-1)]['parent'] = substr(trim($matches[$columns->mastercol+1]),1,-1);
                        $this->courses[$courseid]['tree'][strtolower(substr(trim($matches[$columns->mastercol+1]),1,-1))][] = strtolower(substr(trim($matches[$j+1]),1,-1));
                    }
                }
            }
        }
    }

    function _parse_pre($file,$courseid) {
        $this->convertFileToUtf($file);
        $rows = file($file);
        $columns = $this->_get_aicc_columns($rows[0],'structure_element');
        $regexp = $this->_forge_cols_regexp($columns->columns,'(.+),');
        for ($i=1;$i<count($rows);$i++) {
            if (preg_match($regexp,$rows[$i],$matches)) {
                $this->courses[$courseid]['elements'][$columns->mastercol+1]['prerequisites'] = substr(trim($matches[1-$columns->mastercol+1]),1,-1);
            }
        }
    }

    function _unzip_package($pkg, $dest) {

        if (isset($GLOBALS['progress'])) {
            $GLOBALS['progress']->setAction(_('Распаковка архива курса'));
            $GLOBALS['progress']->saveProgress(0);
        }

        $cwd = getcwd(); // current work dir
        if (!file_exists($dest)) mkdirs($dest);
        chdir($dest);

        $path_parts = pathinfo($pkg);
        if (strtolower($path_parts["extension"])!="zip") {
            $this->errors[] = "Неверный формат файла содержания курса";
            return false;
        }

        //$strPath = "media/".$this->salt;
        //if(!file_exists($strPath)) @mkdirs($strPath);

        $zip = zip_open($pkg);
        if ($zip) {
            while ($zip_entry = zip_read($zip)) {
            if (zip_entry_open($zip, $zip_entry, "r")) {

                $fSize = zip_entry_filesize($zip_entry);
                $eName = zip_entry_name($zip_entry);
                //$eName = str_replace("Files", $strPath, $eName);
                $eName = str_replace("\\", "/", $eName);

                $pathinfo = pathinfo($eName);
                if (!file_exists($pathinfo['dirname'])) @mkdirs($pathinfo['dirname']);

                if($fSize==0) {
                    $s = dirname($eName);
                    if(!file_exists($eName)) @mkdirs($eName);
                }
                else
                {
                    if ($providerDetected = Zend_Registry::get('serviceContainer')->getService('Provider')->autodetectProvider($pathinfo['basename'])) {
                        Zend_Registry::set('providerDetected', $providerDetected);
                        Zend_Registry::set('providerOptions', $eName);
                    }

                    @$buf=zip_entry_read($zip_entry, $fSize);
                    //@$fp = fopen(to_translit($eName), "wb+");
                    @$fp = fopen($eName, "wb+");
                    @fwrite($fp,$buf);
                    @fclose($fp);
                }

                zip_entry_close($zip_entry);
            }
            }
            zip_close($zip);
        }
        chdir($cwd);

        if (isset($GLOBALS['progress'])) {
            $GLOBALS['progress']->saveProgress(100);
        }

        return true;
    }

}

?>