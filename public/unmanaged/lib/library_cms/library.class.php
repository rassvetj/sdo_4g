<?php

class CLibrary {

    var $msg;
    var $where='', $joinn='';
    var $fields = array('bid','title','author','publisher','publish_date','type','quantity','need_access_level','description','keywords');
    var $mostRelevantBids = array(); //временное хранилище для наиболее релевантных поисковому запросу материалов

    /**
    * Constructor
    */
    function CLibrary() {

        $this->msg = '';

    }

    function copyItem($bid, $cid) {
        if ($bid && $cid) {
            $sql = "SELECT * FROM library WHERE bid = '".(int) $bid."'";
            $res = sql($sql);

            while($row = sqlget($res)) {
                unset($row['bid']);
                $row['title'] = _('Копия: ').$row['title'];
                $row['cid'] = $cid;
                $book = new CBook($row);
                $id = $book->addItem();
                if ($id) {
                    sql("UPDATE library SET is_active_version = '{$row['is_active_version']}', upload_date = '{$row['upload_date']}' WHERE bid = '$id'");

                    $sql = "SELECT * FROM library WHERE parent = '".(int) $bid."' ORDER BY bid";
                    $res = sql($sql);

                    while($row = sqlget($res)) {
                        unset($row['bid']);
                        $row['parent'] = $id;
                        $row['cid'] = $cid;
                        $book = new CBook($row);
                        $last = $book->addItem();
                        if ($last) {
                            sql("UPDATE library SET is_active_version = '{$row['is_active_version']}', upload_date = '{$row['upload_date']}' WHERE bid = '$last'");                            
                        }
                    }
                }
            }
        }

    }

    function copyItems($items = array(), $cid = 0) {
        if (is_array($items) && count($items) && $cid) {
            foreach($items as $item) {
                $this->copyItem((int) $item, $cid);
            }
        }
    }

    /**
    * Добавить новый элемент в библиотеку
    */
    function addItem($post,$files) {
    	    	
        $post['is_active_version'] = 1;
        if (isset($files['metafile']) && !empty($files['metafile']['name'])) {
            // Если присутствует файл метаданных, то игнорится остальная инфа
            $resource = new CRusLom($post);
            $bid = $resource->addItem();
            if ($bid>0) {
                if ($resource->parse_standalone_metafile($files['metafile']))
                    $resource->apply_metadata($bid);
            }
            $book = $resource;
        } else {
            $book = new CBook($post);
            $bid = $book->addItem();
        }

        if ($bid > 0 && isset($post['loaded_material']) && !empty($post['loaded_material'])) {
            $book->set_filename('/..'.$post['loaded_material']);
            $book->updateItem();
        }

        if (($bid>0) && isset($files['material']) && !empty($files['material']['name'])) {
        // Если присутствует материал в электронном виде
            if ($book->uploadMaterial($files['material'])) {
                $book->set_filename('/'.(int) $bid.'/'.to_translit($files['material']['name']));
                $book->updateItem();
            }
        }

        if (($bid > 0) && isset($files['index']) && !empty($files['index']['name'])) {
            // Индексирование текстовой версии материала
            $indexFileName = $GLOBALS['wwf'].'/temp/'.to_translit($files['index']['name']);
            if (move_uploaded_file($files['index']['tmp_name'], $indexFileName)) {
                $dummy = @file_put_contents ($indexFileName,@file_get_contents($indexFileName).' '.$post['description'].' '.$post['keywords'].' '.$post['title']);
                $index = new CIndexerTextFile($indexFileName);
                $index->index($bid);
            }
        }elseif ($bid>0) {
            //Индексирование материала без текстовой версии
            $indexFileName = $GLOBALS['wwf'].'/temp/'.md5(time().$GLOBALS['s']['user']['email']).'.tmp';
            $dummy = fopen($indexFileName,'w');
            if (file_exists($indexFileName)) {
                $dummy = @file_put_contents ($indexFileName,@file_get_contents($indexFileName).' '.$post['description'].' '.$post['keywords'].' '.$post['title']);
                $index = new CIndexerTextFile($indexFileName);
                $index->index($bid);
            }
            @unlink($indexFileName);
        }

    }

    /**
    * Добавить новую версию элемента библиотеки
    */
    function addVersion($post,$files) {
        if (isset($files['material']) && !empty($files['material']['name'])) {
            $book = new CBook($post);
            $bid = $book->addVersion();
            if ($bid>0) {
                if ($book->uploadMaterial($files['material'])) {
                    $book->set_filename('/'.(int) $bid.'/'.to_translit($files['material']['name']));
                    $book->updateItem();
                }
            }
        }elseif (isset($post['loaded_material']) && !empty($post['loaded_material'])) {
            //$files['material']['use_already_loaded'] = $post['loaded_material'];
            //$files['material']['name'] = substr($post['loaded_material'], strrpos($post['loaded_material'],'/')+1);
            $book = new CBook($post);
            $bid = $book->addVersion();
            if ($bid>0) {
                $book->set_filename('/..'.$post['loaded_material']);
                $book->updateItem();
            }
        }

    }

    /**
    * Обновить элемент библиотеки
    */
    function updateItem($post,$files) {

        if ($post['type']!=0) $post['filename'] = '';
        if (isset($files['metafile']) && !empty($files['metafile']['name'])) {
            // Если присутствует файл метаданных, то игнорится остальная инфа
            $resource = new CRusLom($post);
            $resource->updateItem();
            if ($post['bid']>0) {
                if ($resource->parse_standalone_metafile($files['metafile']))
                    $resource->apply_metadata($post['bid']);
            }
            $book = $resource;
        } else {
            $book = new CBook($post);
            $book->updateItem($post);
        }
        if (($post['bid']>0) && isset($files['material']) && !empty($files['material']['name'])) {
            if ($book->updateMaterial($files['material'])) {
                $book->set_filename('/'.(int) $post['bid'].'/'.to_translit($files['material']['name']));
                $book->updateItem();
            }
        }

        if (($post['bid']>0) && isset($files['index']) && !empty($files['index']['name'])) {
            // Индексирование текстовой версии материала
            $indexFileName = $GLOBALS['wwf'].'/temp/'.to_translit($files['index']['name']);
            if (move_uploaded_file($files['index']['tmp_name'], $indexFileName)) {
                $dummy = @file_put_contents ($indexFileName,@file_get_contents($indexFileName).' '.$post['description'].' '.$post['keywords'].' '.$post['title']);
                $index = new CIndexerTextFile($indexFileName);
                $index->index($post['bid']);
            }
        }elseif ($post['bid']>0) {
            //Индексирование материала без текстовой версии
            $indexFileName = $GLOBALS['wwf'].'/temp/'.md5(time().$GLOBALS['s']['user']['email']).'.tmp';
            $dummy = fopen($indexFileName,'w');
            if (file_exists($indexFileName)) {
                $dummy = @file_put_contents ($indexFileName,@file_get_contents($indexFileName).' '.$post['description'].' '.$post['keywords'].' '.$post['title']);
                $index = new CIndexerTextFile($indexFileName);
                $index->index($post['bid']);
            }
            @unlink($indexFileName);
        }

        if (!$post['active_version']) $post['active_version'] = $post['bid'];
        CBook::setActiveVersion($post['bid'],$post['active_version']);

    }

    /**
    * Удалить элемент из библиотеки
    */
    function delItem($bid) {

        CBook::delItem((int) $bid);//$_GET['del']);

        //$bid = (int) $_GET['del'];
        if ($bid) {
            $cids = array();
            $sql = "SELECT oid, cid FROM organizations WHERE module = '".(int) $bid."'";
            $res = sql($sql);

            while($row = sqlget($res)) {
                delete_item($row['oid'], false, $row['cid']);
                $cids[$row['cid']] = $row['cid'];
            }

            if (count($cids)) {
                foreach($cids as $cid) {
                    CCourseContent::checkStructure($cid);
                    sql("UPDATE Courses SET tree = '' WHERE CID = '".(int) $cid."'");
                }
            }
            //sql("UPDATE organizations SET module = '0' WHERE module = '".(int) $bid."'");
        }

    }

    /**
    * Импортировать элементы библиотеки из package (IMS MANIFEST)
    */
    function importItems($post,$files) {
        if (isset($files['lop']) && !empty($files['lop']['name'])) {
            $package = new CPackage($post);
            $bid = $package->addItem();
            if ($bid>0) $package->parseLOP($files['lop']);
            if (!empty($package->msg)) {
                if ($bid>0) $package->delItem($bid);
                $GLOBALS['controller']->setView('DocumentBlank');
                $GLOBALS['controller']->setMessage($package->msg,JS_GO_URL,"{$GLOBALS['sitepath']}lib.php?page=$page");
                $GLOBALS['controller']->terminate();
                exit();
            }
        }
    }

    /**
    * Импортирование рубрик
    */
    function importRubrics($files) {
        if (isset($files['rubrics']) && !empty($files['rubrics']['name'])) {
            $cat = new CCategory();
            $cat->import_categories($files['rubrics']);
        }
    }

    /**
    * Возвращает where
    */
    function get_where() {
        return $this->where;
    }

    function get_join() {

        return $this->joinn;

    }

    function _parseCourses($whereCids = '', $search = '') {
        if (isset($search['categories']) && $search['categories']) {
            $whereCids = " AND cid = '".(int) $search['categories']."' ";
        }

        $sql = "SELECT module, cid FROM organizations WHERE module > 0 {$whereCids}";
        $res = sql($sql);

        $modules = $courses = array();
        while($row = sqlget($res)) {
            $courses[$row['cid']]    = $row['cid'];
            $modules[$row['module']] = $row['module'];
        }

        $sql = "SELECT bid, cid FROM library WHERE parent = '0' {$whereCids}";
        $res = sql($sql);

        while($row = sqlget($res)) {
            $modules[$row['bid']] = $row['bid'];
            $courses[$row['cid']] = $row['cid'];
        }

        $where = '';
        if (count($modules)) {
            $modules = array_chunk($modules, 50);

            $count = count($modules);
            if ($count) {
                for($i=0;$i<$count;$i++) {
                    if (is_array($modules[$i]) && count($modules[$i])) {
                        if ($i > 0) {
                            $where .= " OR ";
                        }
                        $where .= " bid IN ('".join("','",$modules[$i])."') ";
                    }
                }
            }
        }

        if (!empty($where)) {
//            if (!empty($this->where)) {
                $this->where .= ' AND ('.$where.')';
//            } else {
//                $this->where .= ' AND ('.$where.')';
//            }
        } else {
            $this->where .= ' AND (bid = 0)';
        }
        return $courses;
    }

    /**
    * Возвращает массив доступных книг
    */
    function getItems($search='',$page=0,$npp=30,$sort=0, $whereCids = '') {

        if (isset($this->fields[(int) $sort])) $order = " ORDER BY ".$this->fields[(int) $sort];
        if (isset($search)) $this->parseSearch($search);

        $courses = $this->_parseCourses($whereCids, $search);

        if ($GLOBALS['s']['user']['meta']['access_level']>0)
                $sql_access_level = " need_access_level>='".$GLOBALS['s']['user']['meta']['access_level']."' OR ";
        $sql = "SELECT library.bid, library.parent, library.cats, library.mid, library.uid, library.title, library.author, library.publisher, library.publish_date, library.description, library.keywords, library.filename, library.location, library.metadata, library.need_access_level, library.upload_date, library.is_active_version, library.type, library.is_package, library.quantity, library.cid
                FROM library ".$this->joinn."
                WHERE library.parent='0'".
                $this->where." $order LIMIT ".(int) $page.",".(int) $npp;
        $res = sql($sql);

        while ($row = sqlget($res)) {
            $row['is_edit'] = false;
            if ((($row['mid']==$GLOBALS['s']['mid']) &&
                $GLOBALS['controller']->checkPermission(LIB_CMS_PERM_EDIT_OWN))
                || ($GLOBALS['controller']->checkPermission(LIB_CMS_PERM_EDIT_OTHERS)))
                    $row['is_edit'] = true;
            if (!isset($courses[$row['cid']])) $row['is_edit'] = false;
            $row['type'] = $GLOBALS['lo_types'][$row['type']];
            $row['published_by'] = get_login_and_lastname_and_firstname_by_mid($row['mid']);
            if (!$row['is_active_version']) {
                $sql = "SELECT filename FROM library WHERE parent='".(int) $row['bid']."' AND is_active_version='1'";
                $res2 = sql($sql);
                if (sqlrows($res2)) {$row2 = sqlget($res2); $row['filename'] = $row2['filename'];}
                sqlfree($res2);
            }
            $ret[] = $row;

        }

        //выносим наиболее релевантные материалы в начало выдачи (при отсутствии сортировки)
        if (is_array($ret) && count($ret) && !$sort) {
            $relevantRet = array();
            foreach ($ret as $key=>$dummy) {
                if (array_search($dummy['bid'], $this->mostRelevantBids)) {
                    $relevantRet[] = $dummy;
                    unset($ret[$key]);
                }
            }
            $ret = array_merge($relevantRet, $ret);
        }
        return $ret;

    }

    function _parseFullTextSearch($str,$condition=0) {
        $str = trim($str);
        if (!empty($str)) {
                $arr = explode(' ',$str);
                $i=1;
                foreach($arr as $v) {
                    if (!empty($v)) {
                        $sql1[] = "SUM(IF(library_cms_index_words.word LIKE '%".trim($v)."%',1,0)) AS word".(int) $i."match";
                        $sql2[] = "(library_cms_index_words.word LIKE '%".trim($v)."%')";
                        $sql3[] = "word".(int) $i."match>0";
                        $i++;
                    }
                }
                if ($condition==2) $separator = ' OR '; else $separator=' AND ';
                if (is_array($sql1) && count($sql1)) {
                    if (is_array($GLOBALS['id_not_in']) && count($GLOBALS['id_not_in'])) {
                        $sql_id_not_in = "AND library_cms_index.id NOT IN ('".join("','",$GLOBALS['id_not_in'])."')";
                    }

                    $sql = "SELECT library_cms_index.id, SUM(library_cms_index.count) AS `sum`, ".join(', ',$sql1)."
                            FROM library_cms_index
                            INNER JOIN library_cms_index_words ON (library_cms_index_words.id=library_cms_index.word)
                            WHERE ".join(' OR ',$sql2)." {$sql_id_not_in}
                            GROUP BY library_cms_index.id
                            HAVING ".join($separator,$sql3)." ORDER BY `sum` DESC";
                    $res = sql($sql);
                    while($row = sqlget($res)) {
                        $ids[] = $row['id'];
                    }
                }

                if (is_array($arr) && count($arr)) {
                    $sql = "SELECT bid
                            FROM library
                            WHERE title LIKE '%".implode("%' OR title LIKE '%",$arr)."%'
                               OR keywords LIKE '%".implode("%' OR keywords LIKE '%",$arr)."%'";
                    $res = sql($sql);
                    while($row = sqlget($res)) {
                        $ids[] = $this->mostRelevantBids[$row['bid']] = $row['bid'];
                    }
                }

                    if (is_array($ids) && count($ids)) {
                        $ids = array_chunk($ids, 50);
                        $where = "";
                        for($i=0;$i<count($ids);$i++) {
                            if ($i>0) {
                                $where .= ' OR ';
                            }
                            $where .= "bid IN ('".join("','",$ids[$i])."')";
                        }
                        return $where;
                }
        }
        return "bid = 0";

    }

    function parseSearch($search,$use_iconv = false) {
        if (is_array($search) && count($search)) {
            foreach($search as $k=>$v) {
                if (empty($v)) {
					unset($search[$k]);
					continue;
				}
                if ($use_iconv) {
					$search[$k] = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$v);
                }
            }
        }

        /**
        * Обработка параметров поиска
        */
        if (is_array($search) && count($search)) {

//            foreach($search as $k=>$v) {

//                if (is_array($this->fields) && in_array($k,$this->fields))
//                $where[] = " library.".addslashes($k)." LIKE ".$GLOBALS['adodb']->Quote('%'.trim(strip_tags($v)).'%')."";
                //if ($k == 'publish_date_from') $where[] = " library.publish_date>=".$GLOBALS['adodb']->Quote(trim(strip_tags($v)))."";
                //if ($k == 'publish_date_to') $where[] = " library.publish_date<=".$GLOBALS['adodb']->Quote(trim(strip_tags($v)))."";
/*                if ($k == 'mid') {
                    $where[]  = " People.LastName LIKE ".$GLOBALS['adodb']->Quote('%'.trim(strip_tags($v)).'%')." OR People.FirstName LIKE ".$GLOBALS['adodb']->Quote('%'.trim(strip_tags($v)).'%')."";
                    $this->joinn =
                    "RIGHT JOIN library_assign ON (library.bid=library_assign.bid)
                    INNER JOIN People ON (People.MID = library_assign.mid)";
                }
*/
                //if (($k == 'categories')&&($v)) $where[] = " cid = ".$GLOBALS['adodb']->Quote((int) ($v))." ";

//            }
/*            if (is_array($where) && count($where)) {
                $where = " AND ((".join(' AND ',$where).")";
            }
*/
            if (strlen($search['keywords'])) {
                $fulltext = $this->_parseFullTextSearch($search['keywords']);
                if (strlen($fulltext)) {
                    $where .= " AND (".$fulltext.")";
                }
            }

        }
        $this->where = $where;
        return $search;
    }


    /**
    * Добавить элемент в библиотеку учебных материалов курса
    */
    function addItemToMod($ModID, $bid) {
        if (($bid>0)&&($ModID>0)) {

            $sql = "SELECT * FROM library WHERE bid='".(int) $bid."' AND parent='0'";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                $sql = "INSERT INTO mod_content (Title,ModID,mod_l,type,conttype)
                        VALUES ('".$row['title']."','".(int) $ModID."','lib_get.php?bid=".$row['bid']."','html','text/html')";
                sql($sql);
            }

        }
    }

    /**
    * Выдать экземпляры материалов пользователю
    * bids - массив id материалов
    */
    function assignItems($post) {

        $start = (int) $post['startYear'].'-'.(int) $post['startMonth'].'-'.(int) $post['startDay'];
        $stop = (int) $post['stopYear'].'-'.(int) $post['stopMonth'].'-'.(int) $post['stopDay'];

        if (is_array($post['bids']) && count($post['bids']) && ($post['mid']>0)) {

            $copy = new CCopy(0);

            foreach($post['bids'] as $bid) {

                $copy->set_bid((int) $bid);
                $copy->assign((int) $post['mid'],$start,$stop);

            }

            if ($copy->msg)
            echo "<script>alert('".$copy->msg."');</script>";
        }

    }

    /**
    * Изменить карточку выдачи материала
    */
    function updateAssign($post) {

        $start = (int) $post['startYear'].'-'.(int) $post['startMonth'].'-'.(int) $post['startDay'];
        $stop = (int) $post['stopYear'].'-'.(int) $post['stopMonth'].'-'.(int) $post['stopDay'];
        $copy = new CCopy($post['bids'][0],$post['assid'],$post['mid'],$start,$stop,$post['closed']);
        $copy->updateItem();

    }


}



?>