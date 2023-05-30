<?php

class CBook {

    var $msg;

    var $bid, $mid, $uid, $title, $filename, $need_access_level, $publish_date, $keywords, $author, $description, $location;
    var $publisher, $is_package, $metadata, $parent, $type, $is_active_version, $quantity;
    var $cid;
    var $cats;

    /**
    * Constructor
    */
    function CBook($data) {

        $this->bid = isset($data['bid'])? (int) $data['bid'] : 0;
        $this->uid = isset($data['uid'])? $data['uid'] : '';
        $this->title = isset($data['title']) ? trim(strip_tags($data['title'])) : _('Материал');
        $this->filename = isset($data['filename']) ? trim(strip_tags($data['filename'])) : '';
        $this->mid = isset($GLOBALS['s']['mid']) ? (int) $GLOBALS['s']['mid'] : 0;
        $this->need_access_level = isset($data['need_access_level']) ? (int) $data['need_access_level'] : 0;
        $this->keywords = isset($data['keywords']) ? trim(strip_tags($data['keywords'])) : '';
        $this->author = isset($data['author']) ? trim(strip_tags($data['author'])) : '';
        $this->description = isset($data['description']) ? trim(strip_tags($data['description'])) : '';
        $this->location = isset($data['location']) ? trim(strip_tags($data['location'])) : '';
        $this->publisher = isset($data['publisher']) ? trim(strip_tags($data['publisher'])) : '';
        $this->publish_date = isset($data['publish_date']) ? trim(strip_tags($data['publish_date'])) : '';
        $this->is_package = isset($data['is_package']) ? (int) $data['is_package'] : 0;
        $this->metadata = isset($data['metadata']) ? trim($data['metadata']) : '';
        $this->parent = isset($data['parent']) ? (int) $data['parent'] : 0;
        $this->type = isset($data['type']) ? (int) $data['type'] : 0;
        $this->is_active_version = isset($data['is_active_version']) ? (int) $data['is_active_version'] : 0;
        $this->quantity = isset($data['quantity']) ? (int) $data['quantity'] : 0;
        $this->cats = (isset($data['cats']) && is_array($data['cats'])) ? $data['cats'] : '';
        $this->cid = (int) $data['cid'];

        if (empty($this->title)) $this->title = _('без имени');

    }

    function set_bid($bid=0) {
        $old = $this->bid;
        $this->bid = (int) $bid;
        return $old;
    }

    function set_filename($filename) {
        $old = $this->filename;
        $this->filename = $filename;
        return $old;

    }

    /**
    * Добавить документ в бд
    */
    function addItem() {

        $this->is_active_version = 1;

        if (is_array($this->cats) && count($this->cats))
        $cats = '#'.join('#',$this->cats).'#';

        $sql = "INSERT INTO library
                (parent,cats,mid,uid,title,author,publisher,publish_date,description,keywords,filename,
                location,metadata,need_access_level,upload_date,is_active_version,type,is_package,quantity,cid)
                VALUES
                (".$GLOBALS['adodb']->Quote($this->parent).",".$GLOBALS['adodb']->Quote($cats).",'".(int) $this->mid."','".(int) $this->uid."',".
                $GLOBALS['adodb']->Quote($this->title).",".$GLOBALS['adodb']->Quote($this->author).",".$GLOBALS['adodb']->Quote($this->publisher).",".
                $GLOBALS['adodb']->Quote($this->publish_date).",".$GLOBALS['adodb']->Quote($this->description).",".$GLOBALS['adodb']->Quote($this->keywords).",".
                $GLOBALS['adodb']->Quote($this->filename).",".$GLOBALS['adodb']->Quote($this->location).",".$GLOBALS['adodb']->Quote($this->metadata).",".
                $GLOBALS['adodb']->Quote($this->need_access_level).",NOW(),".
                $GLOBALS['adodb']->Quote($this->is_active_version).",'".(int) $this->type."','".(int) $this->is_package."',".$GLOBALS['adodb']->Quote($this->quantity).",
                '".$this->cid."')
                ";

        $res = sql($sql);

        $ret = sqllast();
        $this->bid = $ret;

        return $ret;

    }

    /**
    * Добавить новую версию существующего документа в бд
    */
    function addVersion() {

        $this->title = "Версия материала #".$this->parent;
        $ret = $this->addItem();
        if ($ret > 0) {
            $this->is_active_version = 1;
            $this->setActiveVersion($this->parent,$this->bid);
        }

        return $ret;

    }

    function updateItem() {

        if (!empty($this->metadata)) $metadata = ",metadata=".$GLOBALS['adodb']->Quote($this->metadata)."";

        if (is_array($this->cats) && count($this->cats))
        $cats = '#'.join('#',$this->cats).'#';

        $sql = "UPDATE library
                SET
                    parent='".(int) $this->parent."',
                    cats=".$GLOBALS['adodb']->Quote($cats).",
                    mid='".(int) $this->mid."',
                    uid=".$GLOBALS['adodb']->Quote($this->uid).",
                    title=".$GLOBALS['adodb']->Quote($this->title).",
                    author=".$GLOBALS['adodb']->Quote($this->author).",
                    publisher=".$GLOBALS['adodb']->Quote($this->publisher).",
                    publish_date=".$GLOBALS['adodb']->Quote($this->publish_date).",
                    description=".$GLOBALS['adodb']->Quote($this->description).",
                    keywords=".$GLOBALS['adodb']->Quote($this->keywords).",
                    filename=".$GLOBALS['adodb']->Quote($this->filename).",
                    location=".$GLOBALS['adodb']->Quote($this->location).",
                    need_access_level='".(int) $this->need_access_level."',
                    is_package='".(int) $this->is_package."',
                    type='".(int) $this->type."',
                    is_active_version='".(int) $this->is_active_version."',
                    quantity='".(int) $this->quantity."'
                    $metadata
                WHERE
                    bid='".(int) $this->bid."'
                ";

        $res = sql($sql);

    }

    function delItem($bid=0) {

        global $wwf;

        if ($bid>0) {

            $sql = "SELECT parent, filename, is_active_version FROM library WHERE bid='".(int) $bid."'";
            $res = sql($sql);

            if (sqlrows($res)) $row = sqlget($res);

            $sql = "DELETE FROM library WHERE bid='".(int) $bid."'";
            $res = sql($sql);

            $dir = $wwf.COURSES_DIR_PREFIX.'/library/'.(int) $bid;
            if (file_exists($dir)) deldir($dir);

            if ($row['parent']>0) {
                // Если удаляется версия
                if ($row['is_active_version']) {

                    $sql = "SELECT bid FROM library
                            WHERE parent='".(int) $row['parent']."' OR bid='".(int) $row['parent']."'
                            ORDER BY upload_date DESC LIMIT 1";
                    $res = sql($sql);
                    if (sqlrows($res)) {
                        $row2 = sqlget($res);
                        $sql = "UPDATE library SET is_active_version='1' WHERE bid='".(int) $row2['bid']."'";
                        sql($sql);
                    }

                }

            } else {
                // Если удаляется хлафный файл
                $sql = "SELECT bid FROM library WHERE parent='".(int) $bid."'";
                $res = sql($sql);
                while($row = sqlget($res)) {

                    $dir = $wwf.COURSES_DIR_PREFIX.'/library/'.(int) $row['bid'];
                    if (file_exists($dir)) deldir($dir);

                }

                $sql = "DELETE FROM library WHERE parent='".(int) $bid."'";
                sql($sql);

                CIndexerTextFile::clean_index($bid);

            }

        }

    }

    function getItem($bid) {

        if ($bid>0) {

            if ($GLOBALS['s']['user']['meta']['access_level']>0)
                $sql_access_level = " need_access_level>='".$GLOBALS['s']['user']['meta']['access_level']."' OR ";
            $sql = "SELECT * FROM library WHERE bid='".(int) $bid."'";
                    //AND ({$sql_access_level} need_access_level='0')";
            $res = sql($sql);

            $ret['versions'] = false;

            if (sqlrows($res)) {
                $r = sqlget($res);
                $ret = $r;
                $ret['uploaded_by'] = get_login_and_lastname_and_firstname_by_mid((int) $ret['mid']);
                $copy = new CCopy((int) $bid);
                $ret['copies'] = $copy->countCopies();

                /**
                * Получение инфы по версиям
                */
                $sql = "SELECT * FROM library WHERE parent='".(int) $bid."' ORDER BY upload_date DESC";
                $res = sql($sql);
                while($row = sqlget($res)) {

                    $row['uploaded_by'] = get_login_and_lastname_and_firstname_by_mid((int) $row['mid']);
                    $ret['versions'][] = $row;

                }
                $ret['versions'][] = $r;

                $cats = explode('#',$r['cats']);
                if (is_array($cats) && count($cats)) {
                    foreach($cats as $k=>$v) {
                        if (empty($v)) unset($cats[$k]);
                    }
                }
                /**
                * Информация о категориях
                */
                if (is_array($cats) && count($cats)) {
                    $sql = "SELECT * FROM library_categories WHERE catid IN ('".join("','",$cats)."')";
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

    function uploadMaterial($file) {

        global $wwf;

        $destPath = $wwf.COURSES_DIR_PREFIX."/library/".(int) $this->bid.'/';

        if (!file_exists($destPath)) mkdirs(substr($destPath,0,-1));

        if ($file['size'] > LOP_MAX_UPLOAD_SIZE) {
            $this->msg .= "<span style='color:red'><b>Размер файла превышает порог</b></span><br>";
            return false;
        }

        $destFile = $destPath.to_translit($file['name']);
        if (!move_uploaded_file($file['tmp_name'], $destFile)) {
            $this->msg .= "<span style='color:red'><b>Нет файла учебных материалов ".$file['name']."</b></span><BR>";
            return false;
        }

        if ($index = CIndexerDispatcher::factory($destFile)) {
            if ($this->parent) {
                $index->index($this->parent, false);
            } else {
                $index->index($this->bid, false);
            }
        }

        return true;

    }

    function updateMaterial($file) {

        global $wwf;

        $destPath = $wwf.COURSES_DIR_PREFIX."/library";

        @unlink($destPath.$this->filename);

        return $this->uploadMaterial($file);

    }

    function setActiveVersion($materialBid,$activeBid) {

        $sql = "UPDATE library SET is_active_version='0' WHERE parent='".(int) $materialBid."' OR bid='".(int) $materialBid."'";
        sql($sql);

        $sql = "UPDATE library SET is_active_version='1' WHERE bid='".(int) $activeBid."'";
        sql($sql);

    }

} // END CLASS

?>