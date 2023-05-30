<?php

class CCategory {

    var $table;

    function CCategory() {
        // Constructor
    }

    function get_categories_select($name='categories', $size=1, $width="150px", $multiple='', $all=false, $selected=0) {
        $cats = CCategory::get_all_categories();
        $ret .= "<select $multiple id=\"categories\" name=\"{$name}\" size=".(int) $size." style=\"width: ".$width.";\">";
        if ($all) $ret .= "<option value='0'> "._("Все рубрики")."</option>";
        if (is_array($cats) && count($cats))
        foreach($cats as $v) {
            //$level = (int) (count(explode('.',$v['catid']))-1);
            $ret .= "<option label=\"".$v['Title']."\" value='{$v['CID']}'";
            if ($v['CID']==$selected) $ret .= " selected ";
            $ret .= "> "." {$v['Title']}</option>";
            //$prevLevel = $level;
        }
        $ret .= "</select>";
        return $ret;
    }
    function get_categories_list_for_json_output() {
		$cats = CCategory::get_all_categories();
		$i = 0;
		$ret[]['v'] = 0;
		$ret[0]['l'] = 'Все категории';
		$ret[0]['c'] = ' Все категории';
        if (is_array($cats) && count($cats))
			foreach($cats as $v) {
				$i++;
				$level = (int) (count(explode('.',$v['catid']))-1);
				$ret[$i]['v'] = $v['catid'];
				$ret[$i]['l'] = $v['name'];
				$ret[$i]['c'] = ' '.str_repeat('--',$level).' '.$v['name'];
			}
        return $ret;
    }
    function get_all_categories() {
        $ret = array();
        $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
        $sql = "SELECT CID, Title FROM Courses ORDER BY Title";
        $res = sql($sql);

        while($row = sqlget($res)) {
            if (!$courseFilter->is_filtered($row['CID'])) continue;
            $ret[$row['CID']] = $row;
        }
        return $ret;
        //
        $sql = "SELECT * FROM library_categories ORDER BY catid";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $ret[] = $row;
        }
        return $ret;
    }

    function import_categories($file,$table = 'library_categories') {
        global $tmpdir;
        if (move_uploaded_file($file['tmp_name'], $tmpdir."/".$file['name'])) {
            $this->table=$table;
            $this->parse_categories($tmpdir."/".$file['name']);
        }
        @unlink($tmpdir."/".$file['name']);
    }

    function parse_categories($filename) {

        if ($objects = $this->parse_xml($filename)) {

            if (count($objects->elements) > 0) {
                $sql = "DELETE FROM {$this->table}";
                sql($sql);
                foreach ($objects->elements as $identifier => $item) {
                    $sql = "INSERT INTO {$this->table}
                            (catid,name,parent)
                            VALUES
                            ('".$item->identifier."',".$GLOBALS['adodb']->Quote($item->name).",'".$item->parent."')";
                    sql($sql);
                }
            }

        }

    }

    function parse_xml($filename) {

//        $filename = $GLOBALS['sitepath'].'111/rubrics.xml';

        if (is_file($filename)) {
            $xmlstring = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'UTF-8',file_get_contents($filename));
            $xmlstring = preg_replace("/encoding\s*=\s*\".*\"/i",'',$xmlstring);
            $objXML = new xml2Array();
            $arrDOM = $objXML->parse($xmlstring);
            unset($xmlstring);

            $objects = new stdClass();
            $objects = $this->get_categories($arrDOM, $objects);

            $ret = $objects;
        }
        return $ret;
    }

    function get_categories($blocks, $objects) {
        static $parents = array();

        if (count($blocks) > 0) {
            foreach ($blocks as $block) {
                switch($block['name']) {
                    case 'RUBRICATOR':
                        $objects = $this->get_categories($block['children'],$objects);
                    break;
                    case 'RUBRIC':
                        $parent = array_pop($parents);
                        array_push($parents, $parent);

                        $identifier = addslashes($block['attrs']['EXT_INDEX']);
                        $objects->elements[$identifier]->identifier = $identifier;
                        $objects->elements[$identifier]->name = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$block['attrs']['NAME']);
                        $objects->elements[$identifier]->parent = $parent->identifier;

                        $parent = new stdClass();
                        $parent->identifier = $identifier;
                        array_push($parents, $parent);
                        $objects = $this->get_categories($block['children'],$objects);
                        array_pop($parents);

                    break;

                }
            }
        }
        return $objects;
    }

}

?>