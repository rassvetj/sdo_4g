<?php

class CLaw {
    
    var $id;
    var $parent;
    var $categories;
    var $type;
    var $region;
    var $area_of_application;
    var $create_date;
    var $expire;
    var $title;
    var $initiator;
    var $author;
    var $annotation;
    var $modify_date;
    var $edit_reason;
    var $current_version;
    var $filename;
    var $access_level;
    
    function CLaw($data) {
        if (is_array($data) && count($data)) {
            $this->id = (int) (isset($data['id'])) ? $data['id'] : 0;
            $this->parent = (int) (isset($data['parent'])) ? $data['parent'] : 0;
            $this->categories = (is_array($data['categories'])) ? $data['categories'] : false;
            $this->type = (int) $data['type'];
            $this->region = (int) $data['region'];
            $this->area_of_application = (isset($data['area_of_application'])) ? trim(addslashes(strip_tags($data['area_of_application']))) : '';
            if (is_array($data['create_date']) && count($data['create_date'])) 
                $this->create_date = $data['create_date']['Date_Year'].'-'.$data['create_date']['Date_Month'].'-'.$data['create_date']['Date_Day'];
            $this->expire = (isset($data['expire'])) ? trim(addslashes(strip_tags($data['expire']))) : '';            
            $this->title = (isset($data['title'])) ? trim(addslashes(strip_tags($data['title']))) : '';
            if (empty($this->title)) $this->title = 'noname';            
            $this->initiator = (isset($data['initiator'])) ? trim(addslashes(strip_tags($data['initiator']))) : '';
            $this->author = (isset($data['author'])) ? trim(addslashes(strip_tags($data['author']))) : '';
            $this->annotation = (isset($data['annotation'])) ? trim(addslashes(strip_tags($data['annotation']))) : '';
            if (is_array($data['modify_date']) && count($data['modify_date'])) 
                $this->modify_date = $data['modify_date']['Date_Year'].'-'.$data['modify_date']['Date_Month'].'-'.$data['modify_date']['Date_Day'];
            $this->edit_reason = (isset($data['edit_reason'])) ? trim(addslashes(strip_tags($data['edit_reason']))) : '';
            $this->filename = (isset($data['filename'])) ? trim(addslashes(strip_tags($data['filename']))) : '';
            $this->current_version = (int) $data['current_version'];            
            $this->access_level = (int) $data['access_level'];
        }
    }
    
    function add() {
        if ($this->id==0) {
            if (is_array($this->categories) && count($this->categories)) $categories = '#'.join('#',$this->categories).'#';
            $sql = "INSERT INTO laws 
                    (categories,title,initiator,author,annotation,type,region,area_of_application,create_date,expire,
                    modify_date,edit_reason,current_version,filename,parent,upload_date,uploaded_by,access_level) 
                    VALUES 
                    ('{$categories}','{$this->title}','{$this->initiator}','{$this->author}','{$this->annotation}',
                    '{$this->type}','{$this->region}','{$this->area_of_application}','{$this->create_date}','{$this->expire}',
                    '{$this->modify_date}','{$this->edit_reason}','{$this->current_version}','{$this->filename}',
                    '{$this->parent}',NOW(),'".(int) $GLOBALS['s']['mid']."','{$this->access_level}')";
            sql($sql);
            $this->id = sqllast();
            return $this->id;
        }
    }

    function update() {
        if ($this->id > 0) {
            if (is_array($this->categories) && count($this->categories)) $categories = '#'.join('#',$this->categories).'#';
            $sql = "UPDATE laws SET 
                    categories='{$categories}',title='{$this->title}',initiator='{$this->initiator}',author='{$this->author}',
                    annotation='{$this->annotation}',type='{$this->type}',region='{$this->region}',
                    area_of_application='{$this->area_of_application}',create_date='{$this->create_date}',expire='{$this->expire}',                    
                    modify_date='{$this->modify_date}',edit_reason='{$this->edit_reason}',current_version='{$this->current_version}',
                    filename='{$this->filename}',parent='{$this->parent}',access_level='{$this->access_level}' 
                    WHERE id='{$this->id}'";
            sql($sql);
            return $this->id;
        }
    }

    function _del_files($dir) {
        if (file_exists($dir)) return deldir($dir);        
    }
    
    function del($id=0) {
        if ($id>0) {            
            $sql = "SELECT parent, filename, current_version FROM laws WHERE id='".(int) $id."'";
            $res = sql($sql);            
            if (sqlrows($res)) $row = sqlget($res);                        
            $sql = "DELETE FROM laws WHERE id='".(int) $id."'";
            $res = sql($sql);            
            
            CLaw::_del_files(LAW_REPOSITORY_PATH.'/'.(int) $id);
            
            if ($row['parent']>0) {
                // Если удаляется версия
                if ($row['current_version']) {
                    
                    $sql = "SELECT id FROM laws
                            WHERE parent='".(int) $row['parent']."' OR id='".(int) $row['parent']."'
                            ORDER BY upload_date DESC LIMIT 1";
                    $res = sql($sql);
                    if (sqlrows($res)) {
                        $row2 = sqlget($res);                        
                        $sql = "UPDATE laws SET current_version='1' WHERE id='".(int) $row2['id']."'";
                        sql($sql);
                    }
                    
                }
                
            } else {
                // Если удаляется хлафный файл
                $sql = "SELECT id FROM laws WHERE parent='".(int) $id."'";
                $res = sql($sql);
                while($row = sqlget($res)) {                    
                    CLaw::_del_files(LAW_REPOSITORY_PATH.'/'.(int) $row['id']);
                }
                
                $sql = "DELETE FROM laws WHERE parent='".(int) $id."'";
                sql($sql);

                /**
                * Удаление индексной информации                
                */
                $sql = "DELETE FROM laws_index WHERE id='".(int) $id."'";
                sql($sql);
                
                $sql = "SELECT laws_index_words.id 
                        FROM laws_index_words 
                        LEFT OUTER JOIN laws_index 
                        ON (laws_index_words.id=laws_index.word)
                        WHERE laws_index.word IS NULL";
                $res = sql($sql);
                while($row=sqlget($res)) $rows[] = $row['id'];
                if (is_array($rows) && count($rows)) {
                    $sql = "DELETE FROM laws_index_words WHERE id IN ('".join("','",$rows)."')";
                    sql($sql);
                }
            }
                        
        }
        
    }
    
    function _get_item_courses($id) {
        if ($id > 0) {
            $str = "laws_get.php?id=".(int) $id;
            $sql = "SELECT DISTINCT organizations.Title, organizations.cid
                    FROM organizations 
                    LEFT JOIN mod_content ON (mod_content.ModID=organizations.mod_ref)
                    WHERE mod_content.mod_l LIKE '%{$str}%'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $rows[] = array(cid2title($row['cid']),$row['Title']);
            }
        }
        return $rows;
    }
        
    function get_item($id) {

        if ($id>0) {            
            $sql = "SELECT * FROM laws WHERE id='".(int) $id."' AND parent='0'";
            $res = sql($sql);
            $ret['versions'] = false;
            if (sqlrows($res)) {
                $r = sqlget($res);
                //$r['assign'] = CLaw::_get_item_courses($id);
                $ret = $r;
                $ret['uploaded_by'] = get_login_and_lastname_and_firstname_by_mid((int) $ret['mid']);
                
                /**
                * Получение инфы по версиям
                */
                $sql = "SELECT * FROM laws WHERE parent='".(int) $id."' ORDER BY upload_date DESC";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    
                    $row['uploaded_by'] = get_login_and_lastname_and_firstname_by_mid((int) $row['mid']);
                    $ret['versions'][] = $row;
                    
                }
                $ret['versions'][] = $r;
                
                $cats = explode('#',$r['categories']);
                if (is_array($cats) && count($cats)) {                    
                    foreach($cats as $k=>$v) {
                        if (empty($v)) unset($cats[$k]);
                    }                    
                }                                
                /**
                * Информация о категориях
                */
                if (is_array($cats) && count($cats)) {                
                    $sql = "SELECT * FROM laws_categories WHERE catid IN ('".join("','",$cats)."')";
                    $res = sql($sql);
                    $j=0;
                    $ret['cats'] = array();
                    while($row = sqlget($res)) {
                        $ret['cats'][$j]['name'] = $row['name'];
                        $ret['cats'][$j++]['catid'] = $row['catid'];
                    }
                }                
            }            
        }
        
        return $ret;        
        
    }
    
    function add_version() {
        $this->title = _("Версия документа")." #".$this->parent;
        $id = $this->add();
        if ($id > 0) {
            $this->current_version = 1;
            $this->set_current_version($this->parent,$this->id);
        }        
        return $id;    
    }        
    
    function set_filename($filename) {
        $this->filename = $filename;
    }
    
    function set_current_version($id,$current_version_id) {
        $sql = "UPDATE laws SET current_version='0' WHERE parent='".(int) $id."' OR id='".(int) $id."'";
        sql($sql);        
        $sql = "UPDATE laws SET current_version='1' WHERE id='".(int) $current_version_id."'";
        sql($sql);        
    }
    
}

?>