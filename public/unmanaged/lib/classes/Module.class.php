<?php

class CModuleItem extends CDBObject {
    
}

class CMaterialModuleItem extends CModuleItem {
    
}

class CModule extends CDBObject {   
    
    function getModuleForums($modID) {
        $items = array();
        
        if ($modID) {
            $sql = "SELECT forum_id FROM mod_list WHERE ModID = '".(int) $modID."'";
            $res = sql($sql);
            if ($row = sqlget($res)) {
                if (!empty($row['forum_id'])) {
                    $forums = array();
                    foreach (explode(';',$row['forum_id']) as $forum) {
                        if (intval($forum) > 0) {
                            $forums[] = (int) $forum;
                        }
                    }
                    
                    if (count($forums)) {
                        $sql  = "SELECT * FROM forummessages WHERE thread IN ('".join("','",$forums)."') AND is_topic = '1' ORDER BY name";
                        $res = sql($sql);
                        while($row = sqlget($res)) {
                            $items[$row['thread']] = new CForumModuleItem($row);
                        }
                    }
                    
                }
            }
        }
        
        return $items;
    }    

    function getModuleRuns($modID) {
        $items = array();
        
        if ($modID) {
            $sql = "SELECT run_id FROM mod_list WHERE ModID = '".(int) $modID."'";
            $res = sql($sql);
            if ($row = sqlget($res)) {
                if (!empty($row['run_id'])) {
                    $runs = array();
                    foreach (explode(';',$row['run_id']) as $run) {
                        if (intval($run) > 0) {
                            $runs[] = (int) $run;
                        }
                    }
                    
                    if (count($runs)) {
                        $sql  = "SELECT * FROM training_run WHERE run_id IN ('".join("','",$runs)."') ORDER BY name";
                        $res = sql($sql);
                        while($row = sqlget($res)) {
                            $items[$row['run_id']] = new CRunModuleItem($row);
                        }
                    }
                    
                }
            }
        }
        
        return $items;
    }
    
    function getModuleTests($modID) {
        $items = array();
        
        if ($modID) {
            $sql = "SELECT test_id FROM mod_list WHERE ModID = '".(int) $modID."'";
            $res = sql($sql);
            if ($row = sqlget($res)) {
                if (!empty($row['test_id'])) {
                    $tests = array();
                    foreach (explode(';',$row['test_id']) as $test) {
                        if (intval($test) > 0) {
                            $tests[] = (int) $test;
                        }
                    }
                    
                    if (count($tests)) {
                        $sql  = "SELECT * FROM test WHERE tid IN ('".join("','",$tests)."') ORDER BY title";
                        $res = sql($sql);
                        while($row = sqlget($res)) {
                            $items[$row['tid']] = new CTask($row);
                        }
                    }
                    
                }
            }
        }
        
        return $items;
    }
    
    function getModuleMaterials($modID) {
        $items = array();
        if ($modID) {
            $sql = "SELECT * FROM mod_content WHERE ModID = '".(int) $modID."' ORDER BY McID";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $items[$row['McID']] = new CMaterialModuleItem($row);
            }            
        }
        return $items;
    }
    
    function getContent($modID) {
        $content = array();
        
        $content['materials'] = CModule::getModuleMaterials($modID);
        $content['tests']     = CModule::getModuleTests($modID);
        $content['runs']      = CModule::getModuleRuns($modID);
        $content['forums']    = CModule::getModuleForums($modID);
        
        return $content;
    }
    
    function getTests($mods) {
        $ret = array();
        if (is_array($mods) && count($mods)) {
            $sql = "SELECT ModID, test_id FROM mod_list WHERE ModID IN ('".join("','",$mods)."')";
            $res = sql($sql);
            while($row = sqlget($res)) {
                if (!empty($row['test_id'])) {
                    foreach (explode(';',$row['test_id']) as $test) {
                        $ret[$row['ModID']][$test] = $test;
                    }                    
                }
            }
        }
        return $ret;
    }
    
}

class CForumModuleItem extends  CModuleItem {
    
}

class CRunModuleItem extends  CModuleItem {
    function getList($cid) {
        $ret = array();
        $sql = "SELECT * FROM training_run WHERE cid = '".(int) $cid."' ORDER BY name";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $ret[$row['run_id']] = new CRunModuleItem($row);
        }
        return $ret;       
    }
    
    function getParameters($run_id) {
        $query = "SELECT * FROM training_run WHERE run_id = $run_id";
        $result = sql($query);
        $row = sqlget($result);
        $return_value['name'] = $row['name'];
        $items_of_path = explode("\\", $row['path']);
        $prev_value = "";
        $return_value['path_to'] = "";
        if(is_array($items_of_path)) {
 
            foreach($items_of_path as $key => $item) {
                    if($prev_value) {
                             $return_value['path_to'] .= $prev_value."\\";
                    }
                    $prev_value = $item;
            }
        }
        $return_value['path_to'] = trim(addslashes($return_value['path_to']), "\\");
        $parts = explode('/', $prev_value);        
        $return_value['exe'] = trim($parts[0]);
        $return_value['params'] = trim($parts[1]);

        return $return_value;
    }
}

?>