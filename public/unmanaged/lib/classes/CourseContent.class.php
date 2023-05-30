<?php
define("ID_COURSE", 'c');
define("ID_ORG", 'org');
define("ID_TEST", 'test');
define('COURSE_STRUCTURE_EDIT_ARROWS', true);

class CCourseItem extends CDBObject {
    var $table = 'organizations';

    function setMaterial($id, $matId) {
        if ($id && $matId) {
            $cid   = getField('organizations','cid','oid',$id);
            if ($module = CMaterialModuleItem::get(array('name' => 'bid', 'value' => $matId), 'library', 'CMaterialModuleItem')) {
                if ($children   = CCourseContent::getChildren($cid,$id)) {
                    $prev = $id; $items = array();
                    foreach($children as $child) {
                        $items[$child->attributes['oid']] = $child->attributes['oid'];
                        $prev = $child->attributes['oid'];
                    }


                    if (count($items)) {
                        $sql = "DELETE FROM organizations WHERE oid IN ('".join("','",$items)."')";
                        sql($sql);
                    }

                    sql("UPDATE organizations SET prev_ref = '".(int) $id."' WHERE prev_ref = '".(int) $prev."' AND cid = '$cid'");
                }

                $sql = "UPDATE organizations SET title = ".$GLOBALS['adodb']->Quote($module->attributes['title']).", module = '".(int) $matId."', vol1 = '0', vol2 = '0' WHERE oid = '".(int) $id."'";
                sql($sql);

            }
        }
    }

    function setRun($id, $tid) {
        if ($id && $tid) {
            $cid   = getField('organizations','cid','oid',$id);
            if ($task = CRunModuleItem::get(array('name' => 'run_id', 'value' => $tid), 'training_run', 'CRunModuleItem')) {
                if ($children   = CCourseContent::getChildren($cid,$id)) {
                    $prev = $id; $items = array();
                    foreach($children as $child) {
                        $items[$child->attributes['oid']] = $child->attributes['oid'];
                        $prev = $child->attributes['oid'];
                    }


                    if (count($items)) {
                        $sql = "DELETE FROM organizations WHERE oid IN ('".join("','",$items)."')";
                        sql($sql);
                    }

                    sql("UPDATE organizations SET prev_ref = '".(int) $id."' WHERE prev_ref = '".(int) $prev."' AND cid = '$cid'");
                }

                $sql = "UPDATE organizations SET title = ".$GLOBALS['adodb']->Quote($task->attributes['name']).", module = '0', vol1 = '0', vol2 = '".(int) $tid."' WHERE oid = '".(int) $id."'";
                sql($sql);

            }
        }
    }

    function setTask($id, $tid) {
        if ($id && $tid) {
            $cid   = getField('organizations','cid','oid',$id);
            if ($task = CTask::get($tid)) {
                if ($children   = CCourseContent::getChildren($cid,$id)) {
                    $prev = $id; $items = array();
                    foreach($children as $child) {
                        $items[$child->attributes['oid']] = $child->attributes['oid'];
                        $prev = $child->attributes['oid'];
                    }

                    if (count($items)) {
                        $sql = "DELETE FROM organizations WHERE oid IN ('".join("','",$items)."')";
                        sql($sql);
                    }

                    sql("UPDATE organizations SET prev_ref = '".(int) $id."' WHERE prev_ref = '".(int) $prev."' AND cid = '$cid'");
                }

                $sql = "UPDATE organizations SET title = ".$GLOBALS['adodb']->Quote($task->attributes['title']).", module = '0', vol1 = '".(int) $tid."', vol2 = '0' WHERE oid = '".(int) $id."'";
                sql($sql);

            }
        }
    }


    function getParent($id) {
        $parent = -1; $structure = array(); $cid = 0;
        if ($id) {
            $sql  = "SELECT level, cid FROM organizations WHERE oid = '".(int) $id."'";
            $res = sql($sql);

            if ($row = sqlget($res)) {
                $level = $row['level'];
                $cid = $row['cid'];
            }

            if ($level == 0) return -1;
            $sql = "SELECT oid, prev_ref, level
                    FROM organizations
                    WHERE level >= '".(int) ($level-1)."' AND cid = '".(int) $cid."'";
            $res = sql($sql);

            while($row = sqlget($res)) {
               $structure[$row['oid']] = $row;
            }
            while(isset($structure[$id])) {
               $item = $structure[$id];
               if ($structure[$id]['level'] < $level) {
                   $parent = $structure[$id]['oid'];
                   break;
               }
               $id = $structure[$id]['prev_ref'];
            }
        }
        return $parent;
    }

    function getModuleId($id) {
        if ($id) {
            $sql = "SELECT mod_ref FROM organizations WHERE oid = '".(int) $id."'";
            $res = sql($sql);
            if ($row = sqlget($res)) {
                return $row['mod_ref'];
            }
        }
    }

    function moveHorizontal($index, $direction, &$structure, $subelements, $multiplier) {

        $items = array();

        switch($direction) {
            case 'left':
                    $multiplier = 0 - $multiplier;
                break;
        }

        if ($index && isset($structure[$index])) {
            $maxlevel = 0; $minlevel = 0;
            if (isset($structure[$index-1])) {
                $maxlevel = $structure[$index-1]->attributes['level']+1;
            }

            $level = $structure[$index]->attributes['level'];

            $structure[$index]->attributes['level'] = $structure[$index]->attributes['level'] + $multiplier;
            if ($structure[$index]->attributes['level'] < $minlevel) {
                $structure[$index]->attributes['level'] = $minlevel;
            }
            if ($structure[$index]->attributes['level'] > $maxlevel) {
                $structure[$index]->attributes['level'] = $maxlevel;
            }

            $diff = $structure[$index]->attributes['level'] - $level;

            $maxlevel = $minlevel = $structure[$index]->attributes['level']+1;

            $items[$index] = $structure[$index]->attributes['oid'];

            if ($subelements) {
                $i=1;
                while($structure[$index+$i]->attributes['level'] > $level) {
                    $structure[$index+$i]->attributes['level'] = $structure[$index+$i]->attributes['level'] + $diff;

                    $items[$index+$i] = $structure[$index+$i]->attributes['oid'];

                    $_SESSION['itemID'][$structure[$index+$i]->attributes['oid']] = true;

                    $i++;
                    if (!isset($structure[$index+$i])) break;
                }
            }

            foreach($items as $index => $oid) {
                $sql = "UPDATE organizations SET level = '".(int) $structure[$index]->attributes['level']."' WHERE oid = ".(int) $oid;
                sql($sql);
            }
        }
    }

    function moveVertical($index, $direction, &$structure, $subelements, $multiplier) {
        $items = $subitems = array();

        if (isset($structure[$index])) {

            $oid = $structure[$index]->attributes['oid'];

            $i = 0;
            if ($subelements) {
                $i++;
                while($structure[$index+$i]->attributes['level'] > $structure[$index]->attributes['level']) {
                    $subitems[$index+$i] = $structure[$index+$i]->attributes['oid'];
                    $oid = $structure[$index+$i]->attributes['oid'];
                    $_SESSION['itemID'][$oid] = true;
                    $i++;
                }
                $i--;
            }

            $maxindex = count($structure)-1;
            $minindex = 0;
            if ($direction == 'up') {
                $multiplier = 0 - $multiplier;
                $newindex = $index + $multiplier;
            } else {
                $newindex = $index + $i + $multiplier;
            }

            if ($newindex < $minindex) $newindex = $minindex;
            if ($newindex > $maxindex) $newindex = $maxindex;
            if ($index == $newindex) return true;
            if (count($subitems)) {
                if (in_array($structure[$newindex]->attributes['oid'], $subitems)) return true;
            }
            $items[$newindex] = $structure[$newindex]->attributes['oid'];
            $items[$index]    = $structure[$index]->attributes['oid'];

            $diff = $structure[$newindex]->attributes['level'] - $structure[$index]->attributes['level'];
            $structure[$index]->attributes['level'] = $structure[$newindex]->attributes['level'];

            switch($direction) {
                case 'up':
                    $structure[$index]->attributes['prev_ref']   = $structure[$newindex]->attributes['prev_ref'];
                    $structure[$newindex]->attributes['prev_ref'] = $oid; //$levelstructure[$levelindex]->attributes['oid'];
                    break;
                case 'down':
                    $structure[$index]->attributes['level']++; $diff++;

                    $structure[$index]->attributes['prev_ref'] = $structure[$newindex]->attributes['oid'];
                    if (isset($structure[$newindex+1])) {
                        $items[$newindex+1] = $structure[$newindex+1]->attributes['oid'];
                        $structure[$newindex+1]->attributes['prev_ref'] = $oid; //$levelstructure[$levelindex]->attributes['oid'];
                    }

                    if (!isset($structure[$index-1])) {
                        $structure[$index+$i+1]->attributes['prev_ref'] = -1;
                        $items[$index+$i+1]    = $structure[$index+$i+1]->attributes['oid'];
                    }

                    break;
            }

            if (isset($structure[$index+$i+1]) && isset($structure[$index-1])) {
                $items[$index+$i+1]    = $structure[$index+$i+1]->attributes['oid'];
                $items[$index-1]       = $structure[$index-1]->attributes['oid'];
                $structure[$index+$i+1]->attributes['prev_ref'] = $structure[$index-1]->attributes['oid'];
            }

            foreach($items as $index => $oid) {
                $sql = "UPDATE organizations SET level = '".(int) $structure[$index]->attributes['level']."', prev_ref = '".(int) $structure[$index]->attributes['prev_ref']."' WHERE oid = ".(int) $oid;
                sql($sql);
            }

            if (count($subitems)) {
                $sql = "UPDATE organizations SET level = level + '".(int) $diff."' WHERE oid IN ('".join("','",$subitems)."')";
                sql($sql);
            }
        }
    }

    function moveItem($id, $destId, $place) {
        $cid = getField('organizations','cid','oid',$id);

        $structure = CCourseContent::getChildren($cid, 0);


        $index = -1; $childIndex = -1; $destIndex = -1; $destChildIndex = -1;
        for($key = 0;$key < count($structure);$key++) {

            if ($structure[$key]->attributes['oid'] == $id) {
                $index = $childIndex = $key;
                for($i = $key+1; $key < count($structure); $i++) {
                    if ($structure[$i]->attributes['level'] <= $structure[$index]->attributes['level']) {
                        break;
                    }
                    $childIndex = $i;
                }
            }

            if ($structure[$key]->attributes['oid'] == $destId) {
                $destIndex = $destChildIndex = $key;
                for($i = $key+1; $key < count($structure); $i++) {
                    if ($structure[$i]->attributes['level'] <= $structure[$destIndex]->attributes['level']) {
                        break;
                    }
                    $destChildIndex = $i;
                }
            }

            if (($index>0) && ($destIndex>0)) {
                break;
            }

        }

        if (($index >= 0) && ($destIndex >= 0)
            /*&& (!in_array(($index - $destIndex),array(-1,1))
            && ($structure[$index]->attributes['level'] != $structure[$destIndex]->attributes['level']))*/) {
            if ($place == 'before') {
                $level = $structure[$destIndex]->attributes['level'];
                if (isset($structure[$destIndex-1])) {
                    $prev = $structure[$destIndex-1]->attributes['oid'];
                } else {
                    $prev = -1;
                }

                $diff = $structure[$index]->attributes['level'] - $level;
                $res = sql("UPDATE organizations SET prev_ref = '".(int) $prev."', level = '".(int) $level."' WHERE oid = '".(int) $id."'");
                if (!$res) return '-1';
                for($i = $index+1; $i<=$childIndex; $i++) {
                   $level = $structure[$i]->attributes['level'] - $diff;
                   $res = sql("UPDATE organizations SET level = '".$level."' WHERE oid = '".(int) $structure[$i]->attributes['oid']."'");
                   if (!$res) return '-1';
                }
                $res = sql("UPDATE organizations SET prev_ref = '".(int) $structure[$childIndex]->attributes['oid']."' WHERE oid = '".(int) $destId."'");
                if (!$res) return '-1';
            } else {
                $level = $structure[$destIndex]->attributes['level'];
                $diff = $structure[$index]->attributes['level'] - $level;

                if (/*($structure[$destChildIndex]->attributes['oid'] != $id) && */($childIndex != $destChildIndex)) {
                    $res = sql("UPDATE organizations SET prev_ref = '".$structure[$destChildIndex]->attributes['oid']."' WHERE oid = '".$id."'");
                    if (!$res) return '-1';
                }

                for($i = $index; $i<=$childIndex; $i++) {
                   $level = $structure[$i]->attributes['level'] - $diff;
                   if ($place == 'append') {
                       $level++;
                   }
                   $res = sql("UPDATE organizations SET level = '".$level."' WHERE oid = '".(int) $structure[$i]->attributes['oid']."'");
                   if (!$res) return '-1';
                }

                if (isset($structure[$destChildIndex+1])) {
                    if ($structure[$destChildIndex+1]->attributes['oid'] != $structure[$index]->attributes['oid']) {
                        $res = sql("UPDATE organizations SET prev_ref = '".$structure[$childIndex]->attributes['oid']."' WHERE oid = '".$structure[$destChildIndex+1]->attributes['oid']."'");
                        if (!$res) return '-1';
                    }
                }
            }

            $prev = -1;
            if (isset($structure[$index-1])) {
                $prev = $structure[$index-1]->attributes['oid'];
            }

            if (isset($structure[$childIndex+1])) {
                if (($structure[$destChildIndex+1]->attributes['oid'] != $structure[$index]->attributes['oid'])
                   && ($childIndex != $destChildIndex)) {
                    $res = sql("UPDATE organizations SET prev_ref = '".(int) $prev."' WHERE oid = '".(int) $structure[$childIndex+1]->attributes['oid']."'");
                    if (!$res) return '-1';
                }
            }
            return '1';
            //
        }

        //if (in_array(($index - $destIndex),array(-1,1))) return '1';

        return '-1';

    }

    /**
     * Переместить раздел курса
     *
     * @param int $id
     * @param string $direction up|down|left|right
     * @param boolean $subelements
     * @param int $multiplier
     */
    function move($id, $direction, $subelements = true, $multiplier=1) {
        $cid = getField('organizations','cid','oid',$id);

        $subelements = ($subelements)?$subelements:true;

        $structure = CCourseContent::getChildren($cid, 0);

        $index = -1;
        for($key = 0;$key < count($structure);$key++) {
            if ($structure[$key]->attributes['oid'] == $id) {
                $index = $key;
                break;
            }
        }
        switch($direction) {
            case 'down':
            case 'up':
                CCourseItem::moveVertical($index, $direction, $structure, $subelements, $multiplier);
                break;
            case 'right':
            case 'left':
                CCourseItem::moveHorizontal($index, $direction, $structure, $subelements, $multiplier);
                break;
        }
        CCourseContent::checkStructure($cid);
    }

}

class CCourseContentCurrentItem extends CCourseItem {
	var $table = 'sequence_current';

	function getCurrentItem($mid, $cid) {
	    if ($_SESSION['s']['perm'] > 1) $cid = 0;
		$sql = "SELECT current FROM sequence_current WHERE mid = '".(int) $mid."' AND cid = '".(int) $cid."'";
		$res = sql($sql);

		$row = sqlget($res);
        return @$row['current'];
	}

	function isExistsCurrentItem($cid, $item) {
	    if ($item[0] == '_') {
	        if ($oids = explode('_',substr($item,1))) {
	            $sql = "SELECT COUNT(oid) as oids FROM organizations WHERE " . ($cid ? "cid = {$cid} AND" : "") . " oid IN ('".join("','",$oids)."')";
	            $res = sql($sql);

	            if ($row = sqlget($res)) {
	               if ($row['oids'] == count($oids)) return true;
	            }
	        }
	        return false;
	    }
	    $cid = intval($cid);
		$sql = "SELECT module, vol1, vol2 FROM organizations WHERE " . ($cid ? "cid = {$cid} AND" : "") . " oid = '".(int) $item."'";
		$res = sql($sql);
		if ($row = sqlget($res)) {
			$sql = "SELECT bid FROM library WHERE bid = '".(int) $row['module']."'";
			$res = sql($sql);
			if (sqlrows($res)) {
			    return sqlrows($res);
			} else {
                $sql = "SELECT tid FROM test WHERE tid = '".(int) $row['vol1']."'";
                $res = sql($sql);
                if (sqlrows($res)) {
                    return sqlrows($res);
                } else {
                    $sql = "SELECT run_id FROM training_run WHERE run_id = '".(int) $row['vol2']."'";
                    $res = sql($sql);
                    return sqlrows($res);
                }
			}
		}
		return false;
	}

	function setCurrentItem($mid, $cid, $item) {
	    if ($_SESSION['s']['perm'] > 1) $cid = 0;
        sql("DELETE FROM sequence_current WHERE mid = '".(int) $mid."' AND cid = '".(int) $cid."'");

        $sql = "INSERT INTO sequence_current (mid, cid, current) VALUES ('".(int) $mid."','".(int) $cid."',".$GLOBALS['adodb']->Quote($item).")";
        return sql($sql);
	}
}

class CCourseContentSequenceHistoryItem extends CDBObject {
	var $table = 'sequence_history';

	function update($mid, $cid, $id) {
		sql("DELETE FROM sequence_history WHERE cid = '".(int) $cid."' AND mid = '".(int) $mid."' AND item = ".$GLOBALS['adodb']->Quote($id)."");
        $sql = "INSERT INTO sequence_history (mid, cid, item, `date`) VALUES ('".(int) $mid."','".(int) $cid."',".$GLOBALS['adodb']->Quote($id).", ".$GLOBALS['adodb']->DBTimeStamp(time()).")";
        return sql($sql);
	}

	function getItems($mid, $cid, $ids = array()) {
		$items = array();
		if (is_array($ids) && count($ids)) {
			$sql = "SELECT * FROM sequence_history WHERE cid = '".(int) $cid."' AND mid = '".(int) $mid."' AND item IN ('".join("','",$ids)."')";
		} else {
            $sql = "SELECT * FROM sequence_history WHERE cid = '".(int) $cid."' AND mid = '".(int) $mid."'";
		}

		$res = sql($sql);

	    while($row = sqlget($res)) {
		    $items[$row['item']] = new CCourseContentSequenceHistoryItem(array('oid' => $row['item'], 'allowed' => true));
	    }
		return $items;
	}

	function getItemsTest($mid, $cid){
		$items = array();
		$sql = "SELECT * FROM scorm_tracklog WHERE cid = '".(int) $cid."' AND mid = '".(int) $mid."' AND status != 'failed'";
		$res = sql($sql);
	    while($row = sqlget($res)) {
		    $items[$row['ModID']] = new CCourseContentSequenceHistoryItem(array('oid' => $row['ModID'], 'allowed' => true));
	    }
		return $items;
	}

}

class CCourseContentSequence extends CObject {

	var $enable = false;
	var $cid = 0;
	var $mid = 0;
	var $tree = 0;
	var $tpo_type = 0;

	function CCourseContentSequence($cid, $mid) {
        if ($cid) {
        	$this->enable = ($_SESSION['s']['perm'] <= 1);
        	$this->cid = $cid;
        	$this->mid = $mid;
        	//$this->tpo_type = getField('Courses','tpo_type','CID',$this->cid);
        	if ($this->tpo_type) $this->enable = false;
        }
	}

	function disable() {
	   $this->enable = false;
	}

	function isEnable() {
		return $this->enable;
	}

    function getFailedItems($cid = 0) {
        $failed = array();
        $sql = "SELECT ModID, McID, status FROM scorm_tracklog WHERE cid = '".(int) $cid."' AND mid='".(int) $_SESSION['s']['mid']."' ORDER BY start";
        $res = sql($sql);

        while($row = sqlget($res)) {
            if ($row['status'] == 'failed') {
                $failed[$row['ModID']] = $row['ModID'];
            } else {
                unset($failed[$row['ModID']]);
            }
        }
        return $failed;
    }

    function getPassedItems($cid = 0) {
	    $passed = array();
	    $sql = "SELECT ModID, McID, status FROM scorm_tracklog WHERE cid = '".(int) $cid."' AND mid = '".(int) $_SESSION['s']['mid']."' ORDER BY start";
	    $res = sql($sql);

	    while($row = sqlget($res)) {
	        if ($row['status'] != 'failed') {
	    	    $passed[$row['ModID']] = $row['ModID'];
	        } else {
	            unset($passed[$row['ModID']]);
	        }
	    }
	    return $passed;
	}

	function getSequence() {
        $items = $allowed = $leavesBuffer = array();
        foreach(CCourseContentSequenceHistoryItem::getItems($this->mid,$this->cid,array_keys($items)) as $item) {
            $allowed[$item->attributes['oid']] = true;
        }

        $last = true; $allow = false;
        //$children = CCourseContent::getChildren($this->cid,0);
        $passed = $failed = array();
        if ($this->cid) {
            $passed = CCourseContentSequence::getPassedItems($this->cid);
            $children = CCourseContent::getChildren($this->cid,0);
        } else {
            $children = array();
            $sql = "SELECT CID FROM Courses WHERE type = '1' ORDER BY Title";
            $res = sql($sql);

            while($row = sqlget($res)) {
                $children = array_merge($children, CCourseContent::getChildren($row['CID'],0));
            }
        }

        $i = 0;
        if (is_array($children) && count($children)) {
            $allow_next = true;

            foreach($children as $item) {
                if (!isset($level)) {
                    $level = $item->attributes['level'];
                    $leaves = array();
                } else {
                    if ($level < $item->attributes['level']) {
                        array_push($leavesBuffer, $leaves);
                        $leaves = array();
                        $level = $item->attributes['level'];
                    }
                    if ($level > $item->attributes['level']) {
/*                        if (count($leaves)) {
                            $status = true;
                            $allow_next = true;
                            foreach($leaves as $leaf) {
                                if (!$allowed[$leaf]) {
                                    $status = false;
                                }
                                if (!isset($passed[$leaf])) {
                                    $allow_next = false;
                                }
                            }
                            $id = '_'.join('_',$leaves);
                            if (!$this->isEnable()) {
                                $status = true;
                            }
                            if ($_SESSION['s']['perm'] <= 1) {
                                //$items[$id] = new CCourseContentSequenceHistoryItem(array('oid' => $id, 'allowed' => $status));
                            }
                        }
*/
                        $level = $item->attributes['level'];
                        $leaves = array();

                        if (count($leavesBuffer)) {
                            $leaves = array_pop($leavesBuffer);
                        }

                    }
                }

                if ($item->attributes['module'] || $item->attributes['vol1'] || $item->attributes['vol2']) {
                   $leaves[$item->attributes['oid']] = $item->attributes['oid'];
        	       $item->attributes['allowed'] = !$this->isEnable();

        	       if ($this->isEnable()) {
            	       if (isset($allowed[$item->attributes['oid']]) || ($i == 0)) {
            	           $item->attributes['allowed'] = true;
            	           if (!isset($allowed[$item->attributes['oid']])) {
            	               $allow = true;
            	           }
            	       }
            	       if (!$allow_next) $item->attributes['allowed'] = false;
            	       if ($last && !$item->attributes['allowed'] && !$allow && $allow_next) {
            		      $item->attributes['allowed'] = true;
            			  $allow = true;
            	       }
        	       }
        	       $last = $item->attributes['allowed'];

        		   $items[$item->attributes['oid']] = new CCourseContentSequenceHistoryItem($item->attributes);
        		   $i++;
        	   }

            }
/*            if (count($leaves)) {
                $status = true;
                foreach($leaves as $leave) {
                    if (!$items[$leave]->attributes['allowed']) {
                        $status = false;
                    }
                }
                $id = '_'.join('_',$leaves);
                if ($_SESSION['s']['perm'] <= 1) {
                    //$items[$id] = new CCourseContentSequenceHistoryItem(array('oid' => $id, 'allowed' => $status));
                }
            }
*/
        }

		return $items;
	}

	function getProgress() {
	    $progress = 0;
	    if (!$this->isEnable()) return 0;
	    $items = $this->getSequence();
	    if (count($items)) {
	        $i = 0;
	        foreach($items as $item) {
	            if (!$item->attributes['allowed']) break;
	            $i++;
	        }
	        if ($i) {
	            $progress = round($i/count($items)*100);
	        }
	    }

	    return $progress;
	}

	function isItemAllowed($item) {
		if (!$this->isEnable()) return true;

		$items = $this->getSequence();

		if (isset($items[$item]) && $items[$item]->attributes['allowed']) return true;

		return false;
	}

	function isItemAllowedTest($oid){
	    if (empty($this->tree)){
	        $this->tree_obj = new CCourseContentTreeDisplay();
	        $this->tree_obj->setFreeMode();
	        $this->tree_obj->initialize($this->cid);
	    }
	    if (empty($this->allowed_tests)){
	        $this->allowed_tests = CCourseContentSequenceHistoryItem::getItemsTest($this->mid, $this->cid);
	    }
	    if ($item = $this->tree_obj->search($this->tree_obj->tree, $oid)){
	        return count($item['children']) == count(array_intersect(array_keys($item['children']), $this->allowed_tests));
	    }
	    return false;
	}
}

class CCourseItemIrkut extends CCourseItem {

    function get($id) {
        if ($id) {
            $sql = "SELECT * FROM organizations WHERE oid = '".(int) $id."'";
            $res = sql($sql);

            while($row = sqlget($res)) {
                return $row;
            }
        }
        return false;
    }

    function getModuleId($id) {
        if ($id) {
            $sql = "SELECT module FROM organizations WHERE oid = '".(int) $id."'";
            $res = sql($sql);
            if ($row = sqlget($res)) {
                return $row['module'];
            }
        }
    }

    function getRunId($id) {
        if ($id) {
            $sql = "SELECT vol2 FROM organizations WHERE oid = '".(int) $id."'";
            $res = sql($sql);
            if ($row = sqlget($res)) {
                return $row['vol2'];
            }
        }
    }

    function getTaskId($id) {
        if ($id) {
            $sql = "SELECT vol1 FROM organizations WHERE oid = '".(int) $id."'";
            $res = sql($sql);
            if ($row = sqlget($res)) {
                return $row['vol1'];
            }
        }
    }
}

class CCourseContent {

    function checkStructure($cid) {
        if ($cid) {
            $items = array();
            $structure = CCourseContent::getChildren($cid, 0);
            for($i=0;$i<count($structure);$i++) {
                if (isset($structure[$i-1])) {
                    if (($structure[$i]->attributes['level'] - $structure[$i-1]->attributes['level']) > 1) {
                        $items[$structure[$i]->attributes['oid']] = $structure[$i-1]->attributes['level']+1;
                        $structure[$i]->attributes['level'] = $structure[$i-1]->attributes['level']+1;
                    }
                } else {
                    if ($structure[$i]->attributes['level']!=0) {
                        $items[$structure[$i]->attributes['oid']] = 0;
                        $structure[$i]->attributes['level'] = 0;
                    }
                }
            }

            foreach($items as $oid => $level) {
                sql("UPDATE organizations SET level = '".(int) $level."' WHERE oid = '".(int) $oid."'");
            }
        }
    }

    function getChildren($cid, $parent='-1') {
        $content = array(); $items = array(); $order = array();
        intval($cid); intval($parent);
        if ($cid) {
            if ($parent <= 0) {
            	$parent = '-1';
//            	$parent = -1; // в случае php4 и MSSQL
                $level  = -1;
            }

            $sql = "SELECT *
                    FROM organizations
                    WHERE cid = '$cid'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                if ($row['oid'] == $parent) {
                    $level = $row['level'];
                }
                $items[$row['oid']] = $row;
                $order[$row['prev_ref']]['oid']   = $row['oid'];
                $order[$row['prev_ref']]['level'] = $row['level'];
            }

            while(true) {
                if (!isset($order[$parent])) break;
                if (!isset($items[$order[$parent]['oid']])) break;

                $item = $items[$order[$parent]['oid']];

                if ($item['level'] <= $level) break;

                $content[] = new CCourseItem($item);
                $parent = $item['oid'];
            }
        }
        return $content;
    }

    function getLastChild($cid, $parent) {

        if ($children = CCourseContent::getChildren($cid, $parent)) {
            if (is_array($children) && count($children)) {
                $item = array_pop($children);
                if ($item->attributes['oid']) {
                    $parent = $item->attributes['oid'];
                }
            }
        }

        return $parent;
    }

    function getChildLevel($cid, $parent=-1, $level=0) {
        $content = array(); $items = array(); $order = array();
        intval($cid); intval($parent); intval($level);
        if ($cid) {
            $minLevel = $level - 1;
            if ($minLevel < 0) $minLevel = 0;
            if ($parent <= 0) $parent = -1;

            $sql = "SELECT *
                    FROM organizations
                    WHERE cid = '$cid'
                    AND level >= '$minLevel'";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $items[$row['oid']] = $row;
                $order[$row['prev_ref']]['oid']   = $row['oid'];
                $order[$row['prev_ref']]['level'] = $row['level'];
            }

            $parent = (string) $parent;

            while(true) {
                if (!isset($order[$parent])) break;
                if (!isset($items[$order[$parent]['oid']])) break;

                $item = $items[$order[$parent]['oid']];

                if ($item['level'] < $level) break;
                if ($item['level'] == $level) {
                    $content[] = new CCourseItem($item);
                }
                $parent = $item['oid'];
            }

        }
        return $content;
    }

	function getPositionAdd($oid){
	    $sql = "SELECT CID, level FROM organizations WHERE oid = ".(int) $oid;
	    $res = sql($sql);
	    if ($item = sqlget($res)) {
	    	$level = $item['level'];
	    	$CID = $item['CID'];
	    }
	    $current = $oid;
	    while ($current){
		    $sql = "SELECT oid, title FROM organizations WHERE prev_ref = '{$current}' AND level > '{$level}' AND cid='{$CID}'";
		    $res = sql($sql);
		    if ($item = sqlget($res)) {
		    	$current = $item['oid'];
		    } else {
		    	break;
		    }
	    }
		return array($level, $current, $CID);
	}

	function getPositionMoveUp($oid){
	    $sql = "SELECT CID, level, prev_ref FROM organizations WHERE oid = ".(int) $oid;
	    $res = sql($sql);
	    if ($item = sqlget($res)) {
	    	$level = $item['level'];
	    	$CID = $item['CID'];
	    	$prev_ref = $item['prev_ref'];
	    }
        //смотрим элементы вниз
	    $current = $oid;
	    while ($current){
		    $sql = "SELECT oid, level FROM organizations WHERE prev_ref = '{$current}' AND cid='{$CID}'";
		    $res = sql($sql);
		    if (($item = sqlget($res)) && ($item['level'] > $level)) {
				$current = $item['oid'];
	    	} else {
	    		$restore_chain = $item['oid']/*?$item['oid']:$current*/;
	    		break;
	    	}
	    }
	    $last_in_branch = $current;

	    /*
	    $current = $oid;
	    $skip_two = 2;
	    while ($current){
		    $sql = "SELECT oid, prev_ref, level FROM organizations WHERE oid = '{$current}' AND cid='{$CID}'";
		    $res = sql($sql);
	    	$prev = $current;
		    if (($item = sqlget($res)) && ($item['level'] > $level)) {
				$current = $item['prev_ref'];
		    } elseif ($skip_two){
		    	$current = $item['prev_ref'];
		    	$skip_two--;
	    	}
	    	if (!$skip_two) {
	    		break;
	    	}
	    }
	    */
	    $current = $prev_ref;
	    while ($current){
	        $sql = "SELECT oid, prev_ref, level FROM organizations WHERE oid = '{$current}'";
	        $res = sql($sql);

	        if ($item = sqlget($res)) {
	            $current = $item['prev_ref'];
	            $dummy   = sqlval("SELECT oid, prev_ref, level FROM organizations WHERE oid = '{$current}'"); 
	            if ($item['level'] == $level || 
	                /*(int)$dummy['level'] == $level-1 || 
	                (int)$dummy['level'] == $level+1 /*||*/
	                ((int)$dummy['level'] == $level && ($item['level'] < $level))) {
	                //$prev_ref = $dummy['prev_ref'];
	                break;
	            }
	            
	        } else {
                //дерево кончилось, а мы так и не нашли место под которым можно былоб встать
	            $current = $oid;
	            break;
	        }
	    }

	    //пресекаем попытку двинуть ветку выше верхнего элемента
	    if ($prev_ref == '-1' || ($current == -1 && $level != 0) ) {
	        $current = $oid;
	    }

		return array($current, $restore_chain, $last_in_branch, $prev_ref, $CID, $level);
	}

	function getPositionMoveDown($oid){
	    $sql = "SELECT CID, level, prev_ref FROM organizations WHERE oid = ".(int) $oid;
	    $res = sql($sql);
	    if ($item = sqlget($res)) {
	    	$level = $item['level'];
	    	$CID = $item['CID'];
	    	$prev_ref = $item['prev_ref'];
	    }

	    //вытащим всё дерево курса
	    $res = sql("SELECT oid, level, prev_ref FROM organizations WHERE cid='{$CID}'");
	    $aTree = array();
	    while ($row = sqlget($res)) {
	        $aTree[$row['prev_ref']] = $row;
	    }

	    $dummy = $before_skipped = $oid;
	    $current = 0;
	    while ($dummy) {
	        if ($item = $aTree[$dummy]) {
	            //ищем нижний край передвигаемой ветки
	            if ($item['level'] > $level && !$current) {
	               $before_skipped = $item['oid'];
	            }
	            //ишем нижний край ветки под которой хотим встать
	            if ($item['level'] <= $level && $current) {
	                $current = $dummy;
	                break;
	            }
	            //ищем верхний край ветки под которой хотим встать
	            if (($item['level'] == $level-1) || ($item['level'] == $level)) {
	                $before_skipped = ($before_skipped)?$before_skipped:$oid;
	                $current = $item['oid'];
	                if ($item['level'] != $level) {
	                    break;
	                }
	            }

	            $dummy = $item['oid'];
	        }else {
                /* дерево закончилось, а мы так и не нашли место для своей ветки
                 * или эта ветка последняя, тоогда встанем за последним элементом
                 */
                $current = ($current)?$dummy:$oid;
	            break;
	        }
	    }

	    $skipped = sqlvalue("SELECT oid FROM organizations WHERE prev_ref = '$before_skipped'");

	    /*
	    $skip_one = true;
	    while ($current){
		    $sql = "SELECT oid, level FROM organizations WHERE prev_ref = '{$current}' AND cid='{$CID}'";
		    $res = sql($sql);
	    	$prev = $current;
		    if (($item = sqlget($res)) && ($item['level'] > $level)) {
				$current = $item['oid'];
		    } elseif ($skip_one){
		    	$current = $item['oid'];
		    	$skipped = $item['oid'];
		    	$before_skipped = $prev;
		    	$skip_one = false;
	    	} else {
	    		break;
	    	}
	    }
	    */
		return array($current, $skipped, $before_skipped, $prev_ref, $CID, $level);
	}
}

class CCourseWorkAreaTreeDisplay extends CCourseContentTreeDisplay {

	var $smarty;

    function initialize($cid = 0) {
        if (isset($_SESSION['itemID']) && is_array($_SESSION['itemID']) && count($_SESSION['itemID'])) {
            $items = array_keys($_SESSION['itemID']);
            $this->current = $items[0];
        }
        parent::initialize($cid);
    }

    function _initializeTree(){
        $this->tree = array('level' => 0, 'children' => array());
        $sql = "SELECT CID, Title, tree FROM Courses WHERE CID = {$this->cid}";
        $res = sql($sql);
        while($row = sqlget($res)) {

            $this->course_titles['cid'.$row['CID']] = (strlen($row['Title']) > COURSE_STRUCTURE_TOC_MAX_CHARS) ? substr($row['Title'], 0, COURSE_STRUCTURE_TOC_MAX_CHARS) . '...' : $row['Title'];
            $this->course_titles_long['cid'.$row['CID']] = $row['Title'];
            $tree_obj = new CCourseContentTree();
            $tree = $tree_obj->getChildren($row['CID']);
            $GLOBALS['adodb']->UpdateClob('Courses', 'tree', serialize($tree), "CID='{$row['CID']}'");
            $this->tree['children']['cid'.$row['CID']] = $tree;
            $this->tree['children']['cid'.$row['CID']]['attributes']['oid'] = 'cid'.$row['CID'];

            $this->tpo_type = 0;
        }
    }

    function _iterateDisplay($item){

        $leaves = array();
        $class = $this->_getClass($item);
        $title = $this->_getTitle($item);
        $cid = $this->_getCid();
        $prefix = $this->_getPrefix($item);
        //if ($this->free_mode) $cid = 0;
        if ($item['attributes']['oid'] == strval((int)$item['attributes']['oid']) && $this->modules[$item['attributes']['oid']]) {
            $dblClick = "ondblclick=\"window.open('{$GLOBALS['sitepath']}lib_get.php?bid={$this->modules[$item['attributes']['oid']]}&cid=$cid&oid={$item['attributes']['oid']}', 'material_{$this->modules[$item['attributes']['oid']]}', 'statusbar,menubar,height=600,width=900')\"";
        }
        echo "<li class='{$class}' id='{$prefix}{$item['attributes']['oid']}' cid='{$cid}' module='{$this->modules[$item['attributes']['oid']]}' $dblClick";
        if ($this->preview_mode) echo " preview=\"true\""; else echo " preview=\"false\"";
        echo ">{$title}\n";
        if (is_array($item['children']) && count($item['children'])){
            echo "<ul>\n";
            foreach ($item['children'] as $child) {
                $this->_iterateDisplay($child);
                if (!count($child['children']) && ($this->force_class[$child['attributes']['oid']] != 'hasChildren')) {
                    if ($this->modules[$child['attributes']['oid']]) {
                        $leaves[] = $child['attributes']['oid'];
                    }
                }
            }
            echo "</ul>\n";
        }
        echo "</li>";
    }

    function display() {
        require_once($GLOBALS['wwf'].'/teachers/organization.lib.php');

		$this->smarty = new Smarty_els();
		$titles = array(
			'change' => _('подключить текущий элемент из дерева ресурсов'),
			'add' => _('добавить новый элемент'),
			'move_up' => _('переместить вверх'),
			'move_right' => _('на уровень ниже'),
			'move_down' => _('переместить вниз'),
			'move_left' => _('на уровень выше'),
			'edit' => _('редактировать'),
			'delete' => _('удалить'),
		);
		$this->smarty->assign('sitepath', $GLOBALS['sitepath']);
		$this->smarty->assign('titles', $titles);
		$this->smarty->assign('cid', $this->cid);

        if (is_array($this->tree['children']) && count($this->tree['children'])){
            echo "<ul>\n";
            foreach ($this->tree['children'] as $cid => $child) {
                $this->_iterateDisplay($child);
                if (!count($child['children']) && ($this->force_class[$child['attributes']['oid']] != 'hasChildren')) {
                    if ($this->modules[$child['attributes']['oid']]) {
                       $leaves[] = $child['attributes']['oid'];
                    }
                }
            }
        }
        echo "</ul>\n";
    }

    function _getTitle($item){
        $bgcolor1 = ''; $bgcolor2 = '#e0e7ff';
        if (isset($_SESSION['itemID'][$item['attributes']['oid']])) {
            $bgcolor1 = "#dee5ec";
            $bgcolor2 = '#fff';
        }

        $link = '';
        if ($item['level'] == 1){
            $title = "<span title=\"".htmlspecialchars($this->course_titles_long[$item['attributes']['oid']])."\">".$this->course_titles[$item['attributes']['oid']]."</span>";
        } elseif ($task = $this->tasks[$item['attributes']['oid']]) {
            $title = $this->titles[$item['attributes']['oid']];
            //$link = "{$GLOBALS['sitepath']}lib_get.php?bid=$module&cid=0&oid={$item['attributes']['oid']}&tid={$task}";
        } elseif ($run = $this->runs[$item['attributes']['oid']]) {
            $title = $this->titles[$item['attributes']['oid']];
            //$link = "{$GLOBALS['sitepath']}lib_get.php?bid=$module&cid=0&oid={$item['attributes']['oid']}&run={$run}";
        } elseif ($module = $this->modules[$item['attributes']['oid']]) {
            //$title = "<a href=\"{$GLOBALS['sitepath']}lib_get.php?bid=$module&cid=0&oid={$item['attributes']['oid']}\" target=mainFrame>".$this->titles[$item['attributes']['oid']]."</a>";
            $title = $this->titles[$item['attributes']['oid']];
        	$link = "{$GLOBALS['sitepath']}lib_get.php?bid=$module&cid=0&oid={$item['attributes']['oid']}";
        } else {
            $title = $this->titles[$item['attributes']['oid']];
        }

        if ($item['level'] > 1) {
//            $navy_keys="";//$title;
//            if (strlen($link)) {
//            	$navy_keys .= "<a href=\"$link\" target=mainFrame><img src=\"{$GLOBALS['sitepath']}images/icons/ico_list_.gif\" border=0></a>";
//            }
//            $navy_keys.=" ".change_key($item['attributes']['oid'], $this->cid);
//            $navy_keys.=" ".add_key($item['attributes']['oid'], $this->cid);
//            $navy_keys.=" ".edit_key($item['attributes']['oid']);
//            if (defined('COURSE_STRUCTURE_EDIT_ARROWS') && COURSE_STRUCTURE_EDIT_ARROWS) {
//                $navy_keys.=navi_key( $item['attributes']['oid'], $this->cid, "prev_level",getIcon("<"), _("на уровнь выше") );
//                $navy_keys.=navi_key( $item['attributes']['oid'], $this->cid, "next_level",getIcon(">"), _("на уровень ниже") );
//                $navy_keys.=navi_key( $item['attributes']['oid'], $this->cid, "up_item", getIcon("^"), _("переместить вверх") );
//                $navy_keys.=navi_key( $item['attributes']['oid'], $this->cid, "down_item",getIcon("v"), _("переместить вниз") );
//            }
//            $navy_keys.=navi_key( $item['attributes']['oid'], $this->cid, "deleteItem",getIcon("x"), _("удалить пункт") );

			$this->smarty->assign('oid', $item['attributes']['oid']);
			$navy_keys = $this->smarty->fetch('navy_keys.tpl');


            $title_alt = htmlspecialchars($this->titles_alts[$item['attributes']['oid']]);
            $title = "<div style=\"background: {$bgcolor1}\" id=\"row{$item['attributes']['oid']}\"
                onMouseOver=\"putElemInline('navi_".$item['attributes']['oid']."');document.getElementById('row{$item['attributes']['oid']}').style.backgroundColor='{$bgcolor2}';\"
                onMouseOut=\"removeElem('navi_".$item['attributes']['oid']."');document.getElementById('row{$item['attributes']['oid']}').style.backgroundColor='{$bgcolor1}';\"
                title=\"{$title_alt}\"
                ><table style=\"margin: 0px; padding: 0px;\" border=0 cellpaddin=0 cellspacing=0>
                        <tr>
                          <td>".$title."<img src={$GLOBALS['sitepath']}/images/spacer.gif width=1 height=16 align=absmiddle><span style=\"display: none;\" id=navi_".$item['attributes']['oid'].">".$navy_keys."</span></td>
                        </tr>
                      </table></div>".edit_box( $item['attributes']['oid'], $this->titles_alts[$item['attributes']['oid']], $meta, $this->cid, $extra );
        }

        return $title;
    }
}

class CCourseContentTree {

	var $cid;
	var $structure = array();
	var $levels;
	var $chains;
	var $tree;

	var $item_current;

	function getChildren($cid){
		$this->cid = (integer)$cid;
    	$this->_setStructure();
    	$this->_setLevels();
    	$this->_setChains();
    	$this->_setTree();
//    	if (is_array($this->tree['children']) && count($this->tree['children'])){
//    		return array_shift($this->tree['children']);
//    	} else return false;
		return $this->tree;
	}

	function _setStructure(){
    	$structure = CCourseContent::getChildren($this->cid);
    	foreach ($structure as $item) {
    		$this->structure[$item->attributes['oid']] = $item->attributes;
    	}
	}

	function _setLevels(){
    	foreach ($this->structure as $oid => $item){
    		// считаем уровни в "человеческом" формате: 1- уровень курса, 2 - верхний уровень organizations итд
    		$this->levels[$item['level'] + 2][$oid] = $item;
    	}
	}

	function _setChains(){
    	foreach ($this->structure as $oid => $item){
			$prev_ref = $item['prev_ref'];
			$this->chains[$oid] = array('-1' => -1);
//			$level = $item['attributes']['level'];
    		while($prev_ref != -1){
    			$level = $this->structure[$prev_ref]['level'];
				if (!isset($this->chains[$oid][$level]) && $level < $item['level']) {
					$this->chains[$oid][$level] = $prev_ref;
				}
    			$prev_ref = $this->structure[$prev_ref]['prev_ref'];
			}
    	}
	}

	function _setTree(){
    	$this->tree = array('level' => 1, 'attributes' => array('oid' => -1), 'children' => array());
		$this->_iterateTree(&$this->tree);
	}

	function _iterateTree($item){
		$next_level = $item['level'] + 1;
		if (isset($this->levels[$next_level])) {
			foreach ($this->levels[$next_level] as $oid => $child) {
				if ($this->_isDescendant($oid, $item)) {
					$item['children'][$oid] = array('level' => $next_level, 'attributes' => array('oid' => $child['oid']), 'children' => array());
//					$item['children'][$oid] = array('level' => $next_level, 'attributes' => $child, 'children' => array());
					$this->_iterateTree(&$item['children'][$oid]);
				}
			}
		}
	}

	function _isDescendant($oid, $ancestor){
		return in_array($ancestor['attributes']['oid'], $this->chains[$oid]);
	}
}

class CCourseContentTreeDisplay{

	var $cid;
	var $tree;
	var $tpo_type;
	var $free_mode = false;
	var $constructor_mode = false;
	var $preview_mode = false;
	var $course_titles;
	var $course_titles_long;
	var $titles;
	var $titles_alts;
	var $modules;
	var $tasks;
	var $runs;
	var $filenames;

	var $current;
	var $current_branch = array();
	var $sequence;
	var $failed;
	var $force_class;
	var $level;

	var $search_backtrace;
	var $search_backtrace_number;

    var $DMCs = array();

	function initialize($cid = 0){
		$this->cid = (integer)$cid;
		if (!$this->constructor_mode && !$this->preview_mode){
		      $this->current = CCourseContentCurrentItem::getCurrentItem($_SESSION['s']['mid'],$cid);
		}
		$this->_initializeTree();
		$this->_initializeOrganizations();
		$this->_initializeCurrent();
        $this->_initializeDMCs();

		if (!$this->free_mode){
			$sequence = new CCourseContentSequence($this->cid,$_SESSION['s']['mid']);
			$this->sequence = $sequence->getSequence();
			$this->failed = CCourseContentSequence::getFailedItems($cid);
		}
		$this->filterByLevel();
	}

	function setFreeMode(){
		$this->free_mode = true;
	}

	function setConstructorMode(){
		$this->constructor_mode = true;
	}

	function setPreviewMode() {
	    $this->preview_mode = true;
	}

	function setLevel($level){
		$this->level = (integer)$level;
	}

	function _initializeTree(){
		$this->tree = array('level' => 0, 'children' => array());
		$cond_status = ($this->constructor_mode || $this->preview_mode) ? "1 = 1" : " Status > 0";
		$sql = !$this->cid ? "SELECT CID, Title, tree FROM Courses WHERE type = '1' AND Status > 1 ORDER BY Title" : "SELECT CID, Title, tree FROM Courses WHERE {$cond_status} AND CID = {$this->cid}";
		$res = sql($sql);
		while($row = sqlget($res)) {
		    $this->course_titles['cid'.$row['CID']] = (strlen($row['Title']) > COURSE_STRUCTURE_TOC_MAX_CHARS) ? substr($row['Title'], 0, COURSE_STRUCTURE_TOC_MAX_CHARS) . '...' : $row['Title'];
		    $this->course_titles_long['cid'.$row['CID']] = $row['Title'];
			if (strlen(trim($row['tree']))) {
				$this->tree['children']['cid'.$row['CID']] = unserialize($row['tree']);
			} else {
			    $tree_obj = new CCourseContentTree();
			    $tree = $tree_obj->getChildren($row['CID']);
			    $GLOBALS['adodb']->UpdateClob('Courses', 'tree', serialize($tree), "CID='{$row['CID']}'");
				$this->tree['children']['cid'.$row['CID']] = $tree;
			}
			$this->tree['children']['cid'.$row['CID']]['attributes']['oid'] = 'cid'.$row['CID'];
			if ($this->cid){
			    $this->tpo_type = $row['tpo_type'];
			}
		}
	}

	function _initializeOrganizations(){
	    if ($this->cid) {
	        $sql = "SELECT oid, title, module, vol1, vol2 FROM organizations WHERE CID = {$this->cid}";
	    } else {
	        if ($this->level && $this->current) {
	            $res = sql("SELECT cid FROM organizations WHERE oid = '".(int) $this->current."'");
	            if ($row = sqlget($res)){
	                $current_cid = $row['cid'];
            	    $level_db = $this->level - 2;
	                $skip = "WHERE (organizations.level <= {$level_db}) OR (organizations.cid = {$current_cid})";
	            } else {
	                $skip = '';
	            }
	        }
	        $sql = "SELECT oid, title, module, vol1, vol2 FROM organizations {$skip}";
	    }
	    $res = sql($sql);
	    while($row = sqlget($res)) {
	        $this->titles[$row['oid']] = (strlen($row['title']) > COURSE_STRUCTURE_TOC_MAX_CHARS) ? substr($row['title'], 0, COURSE_STRUCTURE_TOC_MAX_CHARS) . '...' : $row['title'];
	        $this->titles_alts[$row['oid']] = $row['title'];
	        $this->modules[$row['oid']] = $row['module'];
	        $this->tasks[$row['oid']] = $row['vol1'];
	        $this->runs[$row['oid']] = $row['vol2'];
	    }

	    if (is_array($this->modules) && count($this->modules)) {
	    	if ($this->cid) {
			    $sql = "
		            SELECT DISTINCT
		              library.filename, library.bid
		            FROM
		              library
		              INNER JOIN organizations ON (library.bid = organizations.module)
		            WHERE
		              (organizations.cid = '{$this->cid}')";
	    	} else {
                $sql = "
                    SELECT DISTINCT
                      library.filename, library.bid
                    FROM
                      library
                      INNER JOIN organizations ON (library.bid = organizations.module) {$skip}";
		    }

		    $res = sql($sql);

		    while($row = sqlget($res)) {
		        $this->filenames[$row['bid']] = $row['filename'];
		    }

         }

	}

	function _initializeCurrent(){
	    if (empty($this->current)) return;
	    $current = $this->current;
	    if ($this->current[0] == '_') {
	        $oids = explode('_',substr($this->current,1));
	        $current = @$oids[0];
	    }
		$this->search($this->tree, $current);
		if (!empty($this->search_backtrace)){
			$this->current_branch = $this->search_backtrace;
		}
	}

    function _initializeDMCs(){
        if ($this->free_mode || $this->constructor_mode || $this->preview_mode) return;
        $sql = "
            SELECT DISTINCT
              library.filename
            FROM
              library
              INNER JOIN organizations ON (library.bid = organizations.module)
            WHERE
              (organizations.cid = '{$this->cid}')
	    ";
        $res = sql($sql);
        while($row = sqlget($res)){
            $this->DMCs[] = str_replace(array('/../COURSES/content/', '.xml', '.html', '.htm'), '', $row['filename']);
        }
    }

    function _iterateCurrent($item){
		if ($item['attributes']['parent'] != -1) {
			$this->current_branch[$item['level']] = $item['attributes']['oid'];
			$this->_iterateCurrent();
		}
	}

	function display(){
	    if (empty($this->tpo_type)){
	        $this->displayCommon();
	    } else {
	        $this->displayTpoType1();
	    }
	    $this->displayDMCs();
	}
	function displayTpoType1(){ // зачёт. всегда показывается как дерево из одного элемента
	   $leaves = $tests = array();
	   foreach($this->modules as $oid=>$module) {
	       if ($module) {
	           $leaves[$oid] = $oid;
	           if (strstr(strtolower($this->filenames[$module]), 'index.htm?id=%') === false) {
                   $tests[$oid] = $oid;
	           }
	       }
	   }
	   echo $this->_getTestTitle($leaves, '', $tests);
	}

    function _displayRuns($cid) {
        $html = '';
        if ($this->constructor_mode) {
            if ($cid) {
                $sql = "SELECT t1.* FROM training_run t1
                        LEFT JOIN organizations t2 ON (t2.vol2 = t1.run_id)
                        WHERE (t1.cid = '".(int) $cid."' OR (t2.cid = '".(int) $cid."' AND t2.oid IS NOT NULL)) ORDER BY t1.name";
                $res = sql($sql);
                if (sqlrows($res)) {
                    //$html .= "<ul>\n";
                    $class = 'branch';
                    if ($this->level >= 3) $class = 'branch-expanded';
                    $html .= "<li class=\"$class sandbox\"> "._("Запустить программу")."\n";
                    $html .= "<ul>\n";
                    $used = array();
                    while($row = sqlget($res)) {
                        if (isset($used[$row['run_id']])) continue;
                        $html .= "<li> <a href=\"javascript:void(0);\" onClick=\"window.organizationItemId = 'run_{$row['run_id']}'; courseItemClick(this);\">".htmlspecialchars($row['name'], ENT_QUOTES)."</a></li>";
                        $used[$row['run_id']] = $row['run_id'];
                    }
                    $html .= "</ul></li>\n";

                    //$html .= "</ul>\n";
                }

            }
        }
        return $html;
    }

	function _displayTasks($cid) {
	    $html = '';
	    if ($this->constructor_mode) {
	        if ($cid) {
	            $sql = "SELECT t1.* FROM test t1
	                    LEFT JOIN organizations t2 ON (t2.vol1 = t1.tid)
	                    WHERE (t1.cid = '".(int) $cid."' OR (t2.cid = '".(int) $cid."' AND t2.oid IS NOT NULL)) AND t1.status > 0 ORDER BY t1.title";
	            $res = sql($sql);
	            if (sqlrows($res)) {
                    //$html .= "<ul>\n";
                    $class = 'branch';
                    if ($this->level >= 3) $class = 'branch-expanded';
                    $html .= "<li class=\"$class sandbox\"> "._("Выполнить задания")."\n";
                    $html .= "<ul>\n";
                    $used = array();
    	            while($row = sqlget($res)) {
    	                if (isset($used[$row['tid']])) continue;
    	                $html .= "<li> <a href=\"javascript:void(0);\" onClick=\"window.organizationItemId = 'task_{$row['tid']}'; courseItemClick(this);\">".htmlspecialchars($row['title'], ENT_QUOTES)."</a></li>";
    	                $used[$row['tid']] = $row['tid'];
    	            }
                    $html .= "</ul></li>\n";
                    //$html .= "</ul>\n";
	            }
	        }
	    }
	    return $html;
	}

    function _displayMaterials($cid) {
        $html = '';
        if ($this->constructor_mode) {
            if ($cid) {
                $sql = "SELECT t1.* FROM library t1
                        LEFT JOIN organizations t2 ON (t2.module = t1.bid)
                        WHERE (t1.cid = '".(int) $cid."' OR (t2.cid = '".(int) $cid."' AND t2.oid IS NOT NULL)) AND t1.parent = 0 ORDER BY t1.title";
                $res = sql($sql);
                if (sqlrows($res)) {
                    //$html .= "<ul>\n";
                    $class = 'branch';
                    if ($this->level >= 3) $class = 'branch-expanded';
                    $html .= "<li class=\"$class sandbox\"> "._("Изучить материал")."\n";
                    $html .= "<ul>\n";
                    $used = array();
                    while($row = sqlget($res)) {
                        if (isset($used[$row['bid']])) continue;
                        $url = "{$GLOBALS['sitepath']}lib_get.php?bid={$row['bid']}&cid=";
                        if ($this->free_mode) {
                            $url .= '0';
                        } else {
                            $url .= $this->cid;
                        }
                        $url .= "&oid=0";

                        $html .= "<li> <a href=\"javascript:void(0);\" onDblClick=\"window.open('{$url}', 'material_{$row['bid']}', 'statusbar,menubar,height=600,width=900')\" onClick=\"window.organizationItemId = 'material_{$row['bid']}'; courseItemClick(this);\">".$row['title']."</a></li>";
						$used[$row['bid']] = $row['bid'];
                    }
                    $html .= "</ul></li>\n";
                    //$html .= "</ul>\n";
                }
            }
        }
        return $html;
    }

	function displayCommon(){
		if (is_array($this->tree['children']) && count($this->tree['children'])){
			echo "<ul>\n";
	        if (!($this->constructor_mode && ($_SESSION['s']['perm'] == 2))) {
			foreach ($this->tree['children'] as $cid => $child) {
				$this->_iterateDisplay($child);
/*				if (!count($child['children']) && ($this->force_class[$child['attributes']['oid']] != 'hasChildren')) {
                    $module = $this->modules[$child['attributes']['oid']];
					if ($module) {
					   $leaves[] = $child['attributes']['oid'];
                        if (strstr(strtolower($this->filenames[$module]), 'index.htm?id=%') === false) {
                            $tests[] = $child['attributes']['oid'];
                        }
					}
				}
*/
			}
			} // if constructor mode
//            if (($child['level'] == 2) && $this->cid) { // в режиме конструктора не показываем список всех модулей по шаблонам курсов
//                echo $this->_displayMaterials($this->cid);
//                echo $this->_displayTasks($this->cid);
//                echo $this->_displayRuns($this->cid);
//            }

            if (isset($_REQUEST['destinationCID']) && ($_REQUEST['destinationCID'] > 0) && ($_SESSION['s']['perm'] != 3)) {
                $class = 'branch';
                if ($this->level >= 2) $class = 'branch-expanded';
//                echo "<li class='$class'> ".cid2title($_REQUEST['destinationCID'])."\n";
                echo "<li class='$class sandbox'>".($_SESSION['s']['perm'] == 2 ? _('Все модули данного курса') : _('Модули вне структуры'))."\n";
                echo "<ul>\n";
                echo $this->_displayMaterials($_REQUEST['destinationCID'],3);
                echo $this->_displayTasks($_REQUEST['destinationCID'],3);
                echo $this->_displayRuns($_REQUEST['destinationCID'],3);
                echo "</ul></li>\n";
            }

			//echo $this->_getTestTitle($leaves, -1, $tests);
			//echo $this->_displayTasks(-2);
		} else {
		    echo "<ul>\n";
            if (isset($_REQUEST['destinationCID']) && ($_REQUEST['destinationCID'] > 0)) {
                $class = 'branch';
                if ($this->level >= 2) $class = 'branch-expanded';
                echo "<li class='$class'> ".cid2title($_REQUEST['destinationCID'])."\n";
                echo "<ul>\n";
                echo $this->_displayMaterials($_REQUEST['destinationCID'],3);
                echo $this->_displayTasks($_REQUEST['destinationCID'],3);
                echo $this->_displayRuns($_REQUEST['destinationCID'],3);
                echo "</ul></li>\n";
            }
            echo "</ul>\n";
		}
		echo "</ul>\n";
	}

	function _iterateDisplay($item, $level = 0){
		$leaves = $tests = array();
		$class = $this->_getClass($item);
		$title = $this->_getTitle($item);
		$cid = $this->_getCid();
		$prefix = $this->_getPrefix($item);
		if ($this->free_mode) $cid = 0;
		echo "<li class='{$class}' id='{$prefix}{$item['attributes']['oid']}' cid='{$cid}'";
		if ($this->preview_mode) echo " preview=\"true\""; else echo " preview=\"false\"";
		echo ">{$title}\n";
		if (is_array($item['children']) && count($item['children'])){
		    echo "<ul>\n";
			foreach ($item['children'] as $child) {
			    $this->_iterateDisplay($child, $level+1);
/*				if (!count($child['children']) && ($this->force_class[$child['attributes']['oid']] != 'hasChildren')) {
					$module = $this->modules[$child['attributes']['oid']];
                    if ($module) {
				        $leaves[] = $child['attributes']['oid'];
                        if (strstr(strtolower($this->filenames[$module]), 'index.htm?id=%') === false) {
                        	$tests[] = $child['attributes']['oid'];
				        }
                    }
				}
*/
			}
//            if ((($item['level'] == 1) && !$cid)) { // в режиме просмотра дерева не показываем песочницу
//                echo $this->_displayMaterials(substr($item['attributes']['oid'],3));
//                echo $this->_displayTasks(substr($item['attributes']['oid'],3));
//                echo $this->_displayRuns(substr($item['attributes']['oid'],3));
//            }
			//echo $this->_getTestTitle($leaves, $item['attributes']['oid'], $tests);
			echo "</ul>\n";
		} else {
/*            if (($item['level'] == 2) && $this->cid) {
                echo $this->_displayMaterials($this->cid);
                echo $this->_displayTasks($this->cid);
                echo $this->_displayRuns($this->cid);
            }
*/
		}
		echo "</li>";
	}

	function _getPrefix($item){
	    return ($item['level'] == 1) ? "c" : "org";
	}

	function _getTestTitle($leaves, $id = '', $tests){
		if (count($leaves) && count($tests) && !$this->constructor_mode && !$this->preview_mode) {
		    $testId = '_'.join('_',$leaves);
		    shuffle($leaves);
			$oids = implode(",", $leaves);
			//test{$item['attributes']['oid']}
			$class = "test{$id}";
			$title = _("Контрольные вопросы");
            if (@$this->sequence[$testId]->attributes['allowed'] || $this->free_mode || $this->tpo_type) {
                $class .= " courseStructureAllowedItem";
            } else {
                $class .= " courseStructureForbiddenItem";
                $title = _("Для изучения этого модуля необходимо пройти предыдущие");
            }
            if ($this->current == $testId) {
                $class .= " courseStructureCurrentItem";
            }

            $cid = $this->cid;
            if ($this->free_mode) {
                $cid = 0;
            }

            return "<li class='{$class}' id='org{$testId}' cid='{$cid}'><a target=mainFrame class='test' href=\"{$GLOBALS['sitepath']}lib_get.php?bid=1&cid={$cid}&oid={$testId}&tests=".'_'.join('_',$tests)."\" title='{$title}'>" . _("Контрольные вопросы") . "</a></li>\n";
            //return "<li class='{$class}' id='org{$testId}' cid='{$cid}'><a class='test' href=\"javascript:top.start_test('{$oids}');\">" . _("Контрольные вопросы") . "</a>\n";
		}
    	return '';
	}

	function _getCid(){
		return ($this->free_mode && empty($this->cid)) ? 0 : $this->cid;
	}

	function _getTitle($item){
    	$cid = ($this->free_mode) ? '0' : $title .= $this->cid;
		$alt = htmlspecialchars($this->titles_alts[$item['attributes']['oid']]);
        $class = '';
        if ((!empty($this->modules[$item['attributes']['oid']]) || $this->tasks[$item['attributes']['oid']] || $this->runs[$item['attributes']['oid']]) && ($item['attributes']['oid'] == $this->current)) {
            if ($item['level'] > 1) {
            $class = "class='current-item'";
            }
        }
        //$isBranch = ' '.strpos($this->_getClass($item),'branch')?true:false;

        $ondblclick = '';
		if ($item['level'] == 1){
			$title = "<span title=\"".$this->course_titles_long[$item['attributes']['oid']]."\">".$this->course_titles[$item['attributes']['oid']]."</span>";
		} elseif (($task = $this->tasks[$item['attributes']['oid']]) /*&& !$isBranch*/) {

        	$url = "{$GLOBALS['sitepath']}lib_get.php?bid={$module}&cid={$cid}&oid={$item['attributes']['oid']}&tid={$task}";
            $onclick = ($this->constructor_mode) ? "onClick=\"window.organizationItemId = 'task_$task'; courseItemClick(this);\"" : "onClick=\"courseItemClick(this);\"";
			if ($this->constructor_mode) $ondblclick = "onDblClick=\"window.open('{$url}', 'material_{$task}', 'statusbar,menubar,height=600,width=900')\"";
	        $href = ($this->constructor_mode) ? "href=\"javascript:void(0);\"" : "href=\"{$url}\" target=\"mainFrame\"";

            $title = "<a {$href} {$onclick} {$ondblclick} {$class} title=\"{$alt}\">{$this->titles[$item['attributes']['oid']]}</a>";

        } elseif (($run = $this->runs[$item['attributes']['oid']]) /*&& !$isBranch*/) {

        	$url = "{$GLOBALS['sitepath']}lib_get.php?bid={$module}&cid={$cid}&oid={$item['attributes']['oid']}&run={$run}";
            $onclick = ($this->constructor_mode) ? "onClick=\"window.organizationItemId = 'run_$run'; courseItemClick(this);\"" : "onClick=\"courseItemClick(this);\"";
			if ($this->constructor_mode) $ondblclick = "onDblClick=\"window.open('{$url}', 'material_{$run}', 'statusbar,menubar,height=600,width=900')\"";
	        $href = ($this->constructor_mode) ? "href=\"javascript:void(0);\"" : "href=\"{$url}\" target=\"mainFrame\"";

            $title = "<a {$href} {$onclick} {$ondblclick} {$class} title=\"{$alt}\">{$this->titles[$item['attributes']['oid']]}</a>";

		} elseif (($module = $this->modules[$item['attributes']['oid']]) /*&& !$isBranch*/) {

            $url = "{$GLOBALS['sitepath']}lib_get.php?bid={$module}&cid={$cid}&oid={$item['attributes']['oid']}";
			$onclick = ($this->constructor_mode) ? "onClick=\"window.organizationItemId = {$item['attributes']['oid']}; courseItemClick(this);\"" : "onClick=\"courseItemClick(this);\"";
			if ($this->constructor_mode) $ondblclick = "onDblClick=\"window.open('{$url}', 'material_{$module}', 'statusbar,menubar,height=600,width=900')\"";
	        $href = ($this->constructor_mode) ? "href=\"javascript:void(0);\"" : "href=\"{$url}\" target=\"mainFrame\"";

            $title = "<a {$href} {$onclick} {$ondblclick} {$class} title=\"{$alt}\">{$this->titles[$item['attributes']['oid']]}</a>";

		} else {
		    if ($this->constructor_mode){
				$title = "<a href='javascript:void(0);' onClick=\"window.organizationItemId = {$item['attributes']['oid']}; courseItemClick(this);\" title='" . htmlspecialchars($this->titles_alts[$item['attributes']['oid']]) ."'>{$this->titles[$item['attributes']['oid']]}</a>";
			} else {
				$title = $this->titles[$item['attributes']['oid']];
			}
		}
		return $title;
	}

	function _getClass($item){
		$class = '';

		if ((!empty($this->modules[$item['attributes']['oid']]) || $this->tasks[$item['attributes']['oid']] || $this->runs[$item['attributes']['oid']]) && ($item['attributes']['oid'] == $this->current)) {
	        if ($item['level'] > 1) {
		        $class .= '';
	        }
	    }
	    if (!count($item['children'])){
		    if (@$this->sequence[$item['attributes']['oid']]->attributes['allowed'] || $this->free_mode) {
		    	$class .= ' courseStructureAllowedItem';
		    } elseif (!empty($this->modules[$item['attributes']['oid']])) {
		    	$class .= ' courseStructureForbiddenItem';
		    	$this->titles_alts[$item['attributes']['oid']] = _("Для изучения этого модуля необходимо пройти предыдущие");
		    }
		    if (isset($this->failed[$item['attributes']['oid']])) {
		        $class .= ' courseStructureFailedItem';
		    }
	    }
    	if (isset($this->force_class[$item['attributes']['oid']])){
	    		$class .= " {$this->force_class[$item['attributes']['oid']]}";
    	} elseif (count($item['children'])){
	    		$class = " hasChildren";
    	}
//	    	$class .= " {$this->has_children[$item['attributes']['oid']]}";

		return trim($class);
	}

	function filterByLevel(){
		if ($this->level < 1) return;
		$this->_iterateFilterByLevel(&$this->tree);
	}

	function _iterateFilterByLevel($item){
		if ($item['level'] <= $this->level) {
			$this->force_class[$item['attributes']['oid']] = "branch-expanded";
		}
		if ($item['level'] >= $this->level) {
			if ($item['attributes']['oid'] != $this->current_branch[$item['level']] && count($item['children'])) {
				$item['children'] = array();
				$this->force_class[$item['attributes']['oid']] = "branch";
			} else {
			    if (count($item['children'])) {
				    $this->force_class[$item['attributes']['oid']] = "branch-expanded";
			    } else {
			        $this->force_class[$item['attributes']['oid']] = 'open';
			    }
			}
		}
		if (is_array($item['children']) && count($item['children'])) {
    		foreach ($item['children'] as $key => $dummy) {
    			$this->_iterateFilterByLevel(&$item['children'][$key]);
    		}
		}
	}

	function filterByBranch($type, $branch, $filterByLevel = true){
	    if ($type == ID_ORG){
	        if (empty($branch) || !isset($this->modules[$branch])) return;
    		if ($item = $this->search($this->tree, $branch)){
    		    $this->tree = $item;
    		    if ($filterByLevel) {
        			$this->level = $item['level'] + 1;
        			$this->filterByLevel();
    		    }
    		}
	    } elseif ($type == ID_COURSE){
	        $this->tree = $this->tree['children'][$branch];
	        $this->level = 2;
            $this->filterByLevel();
	    }
	}

	// todo: это перенести метод в CCourseContentTree
	function search($item_haystack, $needle){
		if (($item_haystack['level'] > 1) && isset($item_haystack['attributes']['oid']) && ($item_haystack['attributes']['oid'] == $needle)) {
			return $item_haystack;
		}
		$keys = @array_keys($item_haystack['children']);
		for ($i = 0; $i < count($item_haystack['children']); $i++) {
			if ($item = $this->search($item_haystack['children'][$keys[$i]], $needle)){
				$this->search_backtrace[$item_haystack['level']] = $item_haystack['attributes']['oid'];
				$this->search_backtrace_number[$item_haystack['level']] = $i+1;
				return $item;
			}
		}
	}

	function getType($id){
        if (!ereg("^([a-z]+)([0-9]+)$", $id, $arr)){
            return false;
        }
	    if ($arr[1] == 'c') return array('type' => ID_COURSE, 'value' => $arr[2]);
	    if ($arr[1] == 'org') return array('type' => ID_ORG, 'value' => $arr[2]);
	}

	function displayDMCs(){
        if (count($this->DMCs)) {
            $DMCs = implode(',', $this->DMCs);
            echo "<script language='JavaScript'>if (top.setDMCs) top.setDMCs('{$DMCs}');</script>";
        }
    }
}
?>