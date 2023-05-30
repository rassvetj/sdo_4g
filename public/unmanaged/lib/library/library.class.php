<?php 

class CLibrary {
    
    var $msg;
    var $where='', $joinn='';
    var $fields = array('uid','title','author','publisher','publish_date','type','quantity','need_access_level','description','keywords');
    
    /**
    * Constructor
    */
    function CLibrary() {
        
        $this->msg = '';
        
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
        if (($bid>0) && isset($files['material']) && !empty($files['material']['name'])) {
        // Если присутствует материал в электронном виде
            if ($book->uploadMaterial($files['material'])) {
                $book->set_filename('/'.(int) $bid.'/'.to_translit($files['material']['name']));
                $book->updateItem();
            }            
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
        if (!$post['active_version']) $post['active_version'] = $post['bid'];
        CBook::setActiveVersion($post['bid'],$post['active_version']);
        
    }
    
    /**
    * Удалить элемент из библиотеки
    */
    function delItem($bid) {
        
        CBook::delItem((int) $_GET['del']);        
        
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
    
    /**
    * Возвращает массив доступных книг
    */
    function getItems($search='',$page=0,$npp=30,$sort=0) {
        
        if (isset($this->fields[(int) $sort])) $order = " ORDER BY ".$this->fields[(int) $sort];
        if (isset($search)) $this->parseSearch($search);
    
        if ($GLOBALS['s']['user']['meta']['access_level']>0) 
                $sql_access_level = " need_access_level>='".$GLOBALS['s']['user']['meta']['access_level']."' OR ";
        $sql = "SELECT library.bid, library.parent, library.cats, library.mid, library.uid, library.title, library.author, library.publisher, library.publish_date, library.description, library.keywords, library.filename, library.location, library.metadata, library.need_access_level, library.upload_date, library.is_active_version, library.type, library.is_package, library.quantity, library.pointId
                FROM library ".$this->joinn."
                WHERE library.parent='0' AND ({$sql_access_level} need_access_level = '0') AND cid = '0' ".
                $this->where." $order LIMIT ".(int) $page.",".(int) $npp;
        $res = sql($sql);
        
        while ($row = sqlget($res)) {
                    
            $row['is_edit'] = false;
            if ((($row['mid']==$GLOBALS['s']['mid']) && 
                $GLOBALS['controller']->checkPermission(LIB_PERM_EDIT_OWN)) 
                || ($GLOBALS['controller']->checkPermission(LIB_PERM_EDIT_OTHERS)))
                $row['is_edit'] = true;
                                                
            $row['is_ematerial'] = $row['type'] ? false : true;
            $row['type'] = $GLOBALS['lo_types'][$row['type']];
            $row['is_reserved'] = CBook::isReserved($row['bid'],$_SESSION['s']['mid']);
            $row['published_by'] = get_login_and_lastname_and_firstname_by_mid($row['mid']);
            if (!$row['is_active_version']) {
                $sql = "SELECT filename FROM library WHERE parent='".(int) $row['bid']."' AND is_active_version='1'";
                $res2 = sql($sql);
                if (sqlrows($res2)) {$row2 = sqlget($res2); $row['filename'] = $row2['filename'];}
                sqlfree($res2);
            }
            $ret[] = $row;
            
        }
        
        return $ret;
    
    }
    
    function parseSearch($search,$use_iconv = false) {
        if (isset($search)) { 
            foreach($search as $k=>$v) {
                if (empty($v)) {
					unset($search[$k]);
					continue;
				}
                if ($use_iconv)
					$search[$k] = iconv("UTF-8","CP1251",$v);
            }
        }
                
        /**
        * Обработка параметров поиска
        */
        if (is_array($search) && count($search)) {
            
            if (strlen($search['title'])) {
                $words = explode(' ', $search['title']);
                $i = 0;
                foreach($words as $word) {
                    $i++;
                    foreach(array('title', 'description', 'keywords', 'uid') as $key) {
                        if (!isset($where[$key])) {
                            $where[$key] = " (";
                        }
                        if ($i > 1) {
                            $where[$key] .= " AND ";
                        }
                        $where[$key] .= " ($key LIKE ".$GLOBALS['adodb']->Quote("%$word%")." 
                        OR $key LIKE ".$GLOBALS['adodb']->Quote("%".CObject::toLower($word)."%")."
                        OR $key LIKE ".$GLOBALS['adodb']->Quote("%".CObject::toUpper($word)."%").") ";
                    }
                }
                
                foreach(array('title', 'description', 'keywords', 'uid') as $key) {
                    $where[$key] .= ") ";
                }

                $where[] = " ({$where['title']} OR {$where['description']} OR {$where['keywords']} OR {$where['uid']}) ";
                foreach(array('title', 'description', 'keywords', 'uid') as $key) {
                    unset($where[$key]);
                }
                
            }
                        
            if (strlen($search['title'])) {
                $words = explode(' ', $search['title']);
                $i = 0;
                foreach($words as $word) {
                    $i++;
                    foreach(array('title', 'description', 'keywords', 'uid') as $key) {
                        if (!isset($where[$key])) {
                            $where[$key] = " (";
                        }
                        if ($i > 1) {
                            $where[$key] .= " AND ";
                        }
                        $where[$key] .= " ($key LIKE ".$GLOBALS['adodb']->Quote("%$word%")." 
                        OR $key LIKE ".$GLOBALS['adodb']->Quote("%".CObject::toLower($word)."%")."
                        OR $key LIKE ".$GLOBALS['adodb']->Quote("%".CObject::toUpper($word)."%").") ";
                    }
                }
                
                foreach(array('title', 'description', 'keywords', 'uid') as $key) {
                    $where[$key] .= ") ";
                }

                $where[] = " ({$where['title']} OR {$where['description']} OR {$where['keywords']} OR {$where['uid']}) ";
                foreach(array('title', 'description', 'keywords', 'uid') as $key) {
                    unset($where[$key]);
                }
                
            }
                        
            if (strlen($search['author'])) {
                $where[] = " author LIKE ".$GLOBALS['adodb']->Quote($search['author'])." ";                
            }
            
            if (strlen($search['publisher'])) {
                $where[] = " publisher LIKE ".$GLOBALS['adodb']->Quote($search['publisher'])." ";
            }

            if ($search['categories']) {
                $where[] = " cats LIKE ".$GLOBALS['adodb']->Quote('%#'.trim(strip_tags($search['categories'])).'%#%')." ";
            }
            
/*            foreach($search as $k=>$v) {
                
                if (is_array($this->fields) && in_array($k,$this->fields))
                $where[] = " library.".addslashes($k)." LIKE ".$GLOBALS['adodb']->Quote('%'.trim(strip_tags($v)).'%')."";
                if ($k == 'publish_date_from') $where[] = " library.publish_date>=".$GLOBALS['adodb']->Quote(trim(strip_tags($v)))."";
                if ($k == 'publish_date_to') $where[] = " library.publish_date<=".$GLOBALS['adodb']->Quote(trim(strip_tags($v)))."";
                if ($k == 'mid') {
                    $where[]  = " People.LastName LIKE ".$GLOBALS['adodb']->Quote('%'.trim(strip_tags($v)).'%')." OR People.FirstName LIKE ".$GLOBALS['adodb']->Quote('%'.trim(strip_tags($v)).'%')."";
                    $this->joinn = 
                    "RIGHT JOIN library_assign ON (library.bid=library_assign.bid)
                    INNER JOIN People ON (People.MID = library_assign.mid)";
                }            
                if (($k == 'categories')&&($v)) $where[] = " cats LIKE ".$GLOBALS['adodb']->Quote('%#'.trim(strip_tags($v)).'%#%')." ";
                
            }
*/            
            
            if (is_array($where) && count($where)) $where = " AND ".join(' AND ',$where);
                                    
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
                $copy->assign((int) $post['mid'],$start,$stop,$post['number'],$post['type']);
                
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
    
    function getAuthors() {
        $ret = array();
        
        $sql = "SELECT DISTINCT author FROM library WHERE parent = '0' ORDER BY author";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            if (strlen($row['author'])) {
                $ret[$row['author']] = $row['author'];
            }
        }
        
        return $ret;
    }
    
    function getPublishers() {
        $ret = array();
        
        $sql = "SELECT DISTINCT publisher FROM library WHERE parent = '0' ORDER BY publisher";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            if (strlen($row['publisher'])) {
                $ret[$row['publisher']] = $row['publisher'];
            }
        }
        
        return $ret;
    }
    
     
}



?> 