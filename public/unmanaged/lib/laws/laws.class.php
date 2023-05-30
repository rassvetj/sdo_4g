<?php

class CLaws {
    
    var $errors = array();        
    
    function _check_permissions($action) {
        switch($action) {
            case 'edit':
                if ($GLOBALS['s']['perm']>=2) return true;
            break;
        }
        return false;
    }
    
    function upload_file($file,$id) {
        $destPath = LAW_REPOSITORY_PATH.'/'.(int) $id.'/';
        if (!file_exists($destPath)) @mkdirs(substr($destPath,0,-1));
        if ($file['size'] > LAW_FILE_MAX_UPLOAD_SIZE) {          
            $this->errors[] = _("Размер файла превышает порог");
            return false;            
        }
        
        if (!move_uploaded_file($file['tmp_name'], $destPath.to_translit($file['name']))
            && !rename($file['tmp_name'], $destPath.to_translit($file['name']))) {
            $this->errors[] = _("Нет файла учебных материалов")." ".$file['name'];
            return false;
        }
        return ('/'.(int) $id.'/'.to_translit($file['name']));                            
    }
    
    function update_file($file,$id,$old_filename) {
        $destPath = LAW_REPOSITORY_PATH;
        @unlink($destPath.$old_filename);
        return CLaws::upload_file($file,$id);
    }
    
    function add($data,$files) {
        if (!CLaws::_check_permissions('edit')) return false;
        $data['current_version'] = 1;
        $law = new CLaw($data);
        $id = $law->add();
        if (($id>0) && is_array($files['material'])) {
            $law->set_filename(CLaws::upload_file($files['material'],$id));
            $law->update();
        }
        if (($id>0) && is_array($files['index'])) {
            // text file indexing
            $index = new CTextFileIndex($files['index']);
            $index->index($id);
            $index->unlink();
        }
    }
    
    function update($data,$files) {
        if (!CLaws::_check_permissions('edit')) return false;
        $law = new CLaw($data);
        $id = $law->update();
        if (($id>0) && isset($files['material']) &&!empty($files['material']['name'])) {
            $law->set_filename(CLaws::update_file($files['material'],$id,$data['filename']));
            $law->update();
        }
        if (($id>0) && is_array($files['index'])) {
            // text file indexing
            $index = new CTextFileIndex($files['index']);
            $index->index($id);
            $index->unlink();
        }
        CLaw::set_current_version($id,$data['current_version_id']);
    }
    
    function add_version($data,$files) {
        if (!CLaws::_check_permissions('edit')) return false;
        if (isset($files['material']) && !empty($files['material']['name'])) {
            $law = new CLaw($data);
            $id = $law->add_version();
            if ($id>0) {
                $law->set_filename(CLaws::upload_file($files['material'],$id));
                $law->update();
            }
        }        
    }
    
    function _get_current_version($id) {
        $sql = "SELECT filename FROM laws WHERE parent='".(int) $id."' AND current_version='1'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res); 
            if (!empty($row['filename']))
                return $row['filename'];
        }
    }
    
    function _get_order_sql($sort=0) {
        $sort_fields = 
            array('title','author','type','region','create_date','initiator','area_of_application','expire','modify_date','edit_reason','access_level','id');
        if (isset($sort_fields[(int) $sort]))
            return 'ORDER BY '.$sort_fields[(int) $sort];        
    }
    
    function _get_where_sql_by_fulltext_search($str,$condition=0) {
        unset($GLOBALS['ids']);
        $str = trim($str);
        if (!empty($str)) {
                $arr = explode(' ',$str);
                $i=1;
                foreach($arr as $v) {
                    if (!empty($v)) {
                        $sql1[] = "SUM(IF(laws_index_words.word LIKE '%".trim($v)."%',1,0)) AS word".(int) $i."match";
                        $sql2[] = "(laws_index_words.word LIKE '%".trim($v)."%')";
                        $sql3[] = "word".(int) $i."match>0";
                        $i++;
                    }
                }
                if ($condition==2) $separator = ' OR '; else $separator=' AND ';
                if (is_array($sql1) && count($sql1)) {
                    if (is_array($GLOBALS['id_not_in']) && count($GLOBALS['id_not_in'])) 
                        $sql_id_not_in = "AND laws_index.id NOT IN ('".join("','",$GLOBALS['id_not_in'])."')";
                    
                    $sql = "SELECT laws_index.id, SUM(laws_index.count) AS `sum`, ".join(', ',$sql1)."
                            FROM laws_index
                            INNER JOIN laws_index_words ON (laws_index_words.id=laws_index.word)
                            WHERE ".join(' OR ',$sql2)." {$sql_id_not_in}
                            GROUP BY laws_index.id
                            HAVING ".join($separator,$sql3)." ORDER BY `sum` DESC";
                    $res = sql($sql);
                    while($row = sqlget($res)) {
                        $ids[] = $row['id'];
                    }
                    if (is_array($ids) && count($ids)) {
                        $GLOBALS['ids'] = $ids;
                        return "id IN ('".join("','",$ids)."')";
                    }
                }
        }
        return "id=0";
    }
    
    function _get_where_sql($search_array) {
        if (is_array($search_array) && count($search_array)) {
            foreach($search_array as $k=>$v) {
                if (!empty($v))
                    switch($k) {
                        case 'string':
                            $GLOBALS['search_exists'] = true;
                            $v_arr = explode(' ',$v);
                            while(list(,$value) = each($v_arr)) {
                                $value = trim($value);
                                if (!empty($value))
                                    $tmp[] = "(title LIKE '%{$value}%' OR initiator LIKE '%{$value}%' OR author LIKE '%{$value}%' OR annotation LIKE '%{$value}%' OR area_of_application LIKE '%{$value}%' OR edit_reason LIKE '%{$value}%')";
                            }
                            $ret[] = join(($search_array['fulltext_condition']==2) ? ' OR ' : ' AND ',$tmp);
                            //$ret[] = "title LIKE '%{$v}%' OR initiator LIKE '%{$v}%' OR author LIKE '%{$v}%' OR annotation LIKE '%{$v}%' OR area_of_application LIKE '%{$v}%' OR edit_reason LIKE '%{$v}%'";
                        break;
                        case 'type':
                            $GLOBALS['search_exists'] = true;
                            $ret[] = "type = '{$v}'";
                        break;
                        case 'author':
                            $GLOBALS['search_exists'] = true;
                            $ret[] = "author LIKE '{$v}'";
                        break;
                        case 'initiator':
                            $GLOBALS['search_exists'] = true;
                            $ret[] = "initiator LIKE '{$v}'";
                        break;
                        case 'area':
                            $GLOBALS['search_exists'] = true;
                            $ret[] = "area_of_application LIKE '%{$v}%'";
                        break;
                        case 'region':
                            $GLOBALS['search_exists'] = true;
                            $ret[] = "region = '{$v}'";
                        break;
                        case 'category':
                            $GLOBALS['search_exists'] = true;
                            $ret[] = "categories LIKE '%#{$v}#%'";
                        break;
                        case 'access_level':
                            $GLOBALS['search_exists'] = true;
                            $ret[] = "access_level='".(int) $v."'";
                        break;
                        case 'fulltext':
                            $GLOBALS['search_exists'] = true;
                            $tmp = CLaws::_get_where_sql_by_fulltext_search($v,$search_array['fulltext_condition']);
                            if (!empty($tmp)) $ret[] = $tmp;
                            $GLOBALS['fulltext_search'] = true;
                        break;
                    }
            }
            if (is_array($ret) && count($ret))
                return ' AND '.join(' AND ',$ret);
        }
    }
    
    function get_list($search_array,$sort=0,$page,$npp=30,$all=false) {
        $GLOBALS['id_not_in'] = array();    
        $search_array2 = $search_array;
        $search_array2['fulltext'] = $search_array['string'];
        $search_array3 = $search_array2;
        unset($search_array3['string']);
        $rows = CLaws::_get_list($search_array2,$sort,$page,$npp,$all);
        if (!empty($search_array['string'])) {
            $rows = array_merge($rows,CLaws::_get_list($search_array,$sort,$page,$npp,$all));
            $rows = array_merge($rows,CLaws::_get_list($search_array3,$sort,$page,$npp,$all));
        }
        if (is_array($rows) && count($rows)) {
            reset($rows);
            while(list($k,$v) = each($rows)) {
                if (!isset($tmp[$v['id']])) {
                    $tmp[$v['id']] = 1;
                    
                    $rows[$k]['is_edit'] = false;
                    if ((($rows[$k]['uploaded_by']==$GLOBALS['s']['mid']) && 
                    $GLOBALS['controller']->checkPermission(LAWS_PERM_EDIT_OWN)) 
                    || ($GLOBALS['controller']->checkPermission(LAWS_PERM_EDIT_OTHERS)))
                    $rows[$k]['is_edit'] = true;
                    
                    $rows[$k]['assign'] = CLaw::_get_item_courses($rows[$k]['id']);
                    $rows[$k]['type'] = $GLOBALS['law_types'][$rows[$k]['type']];
                    $rows[$k]['region'] = $GLOBALS['law_regions'][$rows[$k]['region']];
                    $rows[$k]['categories'] = CLaws::_get_attributes_names($rows[$k]['categories']);
                    if (!$rows[$k]['current_version']) $rows[$k]['filename'] = CLaws::_get_current_version($rows[$k]['id']);
                } else unset($rows[$k]);
            }
        }
        return $rows;
    }

    /**
     * Возвращает название отделений, должностей и других аттрибутов
     *
     * @param string $attributes
     * @return array
     */
    function _get_attributes_names($departments,$type=0) {
        $cats = explode('#',$departments);
        if (is_array($cats) && count($cats)) {                    
            foreach($cats as $k=>$v) {
                if (empty($v)) unset($cats[$k]);
            }                    
        }                                
        if (is_array($cats) && count($cats)) {                
            $sql = "SELECT catid, name FROM laws_categories WHERE parent={$type} AND catid IN ('".join("','",$cats)."')";
            $res = sql($sql);
            $j=0;
            while($row = sqlget($res)) {
                $ret[$row['catid']] = $row['name'];
            }
        }
        return $ret;       
    }
            
    function _get_list($search_array,$sort=0,$page,$npp=30,$all=false) {
    	$rows = array();
        if (is_array($GLOBALS['id_not_in']) && count($GLOBALS['id_not_in'])) 
            $sql_id_not_in = "AND id NOT IN ('".join("','",$GLOBALS['id_not_in'])."')";
            
        $sql_where = CLaws::_get_where_sql($search_array);
        if (empty($sql_where)) 
            $sql_limit = " LIMIT ".(int) $page.",".(int) $npp;
            
        if ($GLOBALS['s']['user']['meta']['access_level']>0) 
            $sql_access_level = " access_level>='".$GLOBALS['s']['user']['meta']['access_level']."' OR ";
        $sql = "SELECT * 
                    FROM laws 
                    WHERE parent='0' AND ({$sql_access_level} access_level = '0') ".
                    $sql_where." ".$sql_id_not_in." ".
                    CLaws::_get_order_sql($sort)." ".$sql_limit;
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (is_array($GLOBALS['ids']) && count($GLOBALS['ids']) && in_array($row['id'],$GLOBALS['ids'])) {
                $key = array_search($row['id'],$GLOBALS['ids']);
                $rows[(int) $key] = $row;
            } else {
                $rows[] = $row;
            }
            if (!in_array($row['id'],$GLOBALS['id_not_in']))
                $GLOBALS['id_not_in'][] = $row['id'];
        }
        if (is_array($GLOBALS['ids']) && count($GLOBALS['ids']) && is_array($rows) && count($rows)) ksort($rows);
        return $rows;
    }
        
    function get_categories() {
        $sql = "SELECT * FROM laws_categories ORDER BY catid ASC";
        $res = sql($sql);
        while ($row = sqlget($res)) {
            $rows[] = $row;
        }        
        return $rows;
    }

    function get_categories_options() {
        $sql = "SELECT * FROM laws_categories ORDER BY catid ASC";
        $res = sql($sql);
        while ($row = sqlget($res)) {
            $rows[$row['catid']] = $row['name'];
        }        
        return $rows;
    }
    
    function import($files) {
        if (!CLaws::_check_permissions('edit')) return false;
        if (isset($files['rubrics']) && !empty($files['rubrics']['name'])) {
            $category = new CCategory();
            $category->import_categories($files['rubrics'],'laws_categories');
        }        
    }
    
    function export_to_module($id,$ModID) {
        if (($id>0)&&($ModID>0)) {

            if ($GLOBALS['s']['user']['meta']['access_level']>0) 
                $sql_access_level = " access_level>='".$GLOBALS['s']['user']['meta']['access_level']."' OR ";            
            $sql = "SELECT * FROM laws WHERE id='".(int) $id."' AND parent='0' AND 
            ({$sql_access_level} access_level='0')";
            $res = sql($sql);
            if (sqlrows($res)) {
                $row = sqlget($res);
                $sql = "INSERT INTO mod_content (Title,ModID,mod_l,type,conttype) 
                        VALUES ('".$row['title']."','".(int) $ModID."','laws_get.php?id=".$row['id']."','html','text/html')";
                sql($sql);
            }
            
        }
    }    
    
    function get_authors_array() {
        $sql = "SELECT DISTINCT author FROM laws ORDER BY author";
        $res = sql($sql);
        while($row = sqlget($res)) $rows[$row['author']]=$row['author'];
        return $rows;
    }
    
    function get_initiators_array() {
        $sql = "SELECT DISTINCT initiator FROM laws ORDER BY initiator";
        $res = sql($sql);
        while($row = sqlget($res)) $rows[$row['initiator']]=$row['initiator'];
        return $rows;
    }
    
    function get_access_levels_array() {
        return array(0,1,2,3,4,5,6,7,8,9,10);
    }
}

?>