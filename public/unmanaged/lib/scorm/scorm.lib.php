<?php
/**
* Библиотека для работы с SCORM
*/

define('IMS_MANIFEST_FILENAME','imsmanifest.xml');
define('SCORM_DEBUG', false);

$scorm_completion_statuses = array('completed'=>'завершено','incomplete'=>'не завершено','incompleted'=>'не завершено','not_attempted'=>'not attempted','unknown'=>'неизвестно');

function scorm_get_track($userID, $ModID, $McID, $cid) {
    
    $sql = "SELECT trackdata FROM scorm_tracklog 
            WHERE 
            mid='".(int) $userID."' AND ModID='".(int) $ModID."' AND McID='".(int) $McID."' AND cid = '".(int) $cid."' 
            ORDER BY stop DESC LIMIT 1";
    $res =  sql($sql);
    
    if (sqlrows($res)) {
        
        $row = sqlget($res);
        $tracks = unserialize($row['trackdata']);
        
        if (is_array($tracks) && count($tracks)) {
 
            $usertrack->score_raw = '';
            $usertrack->status = '';
            $usertrack->total_time = '00:00:00';
            $usertrack->session_time = '00:00:00';
            $usertrack->timemodified = 0;
            
            foreach($tracks as $element=>$value) {
             
                $usertrack->{$element} = $value;
                switch ($element) {
                    case 'cmi.core.lesson_status':
                    case 'cmi.completition_status':
                        if ($value == 'not attempted') {
                            $value = 'notattempted';
                        }
                        $usertrack->status = $value;
                    break;
                    case 'cmi.core.score.raw':
                    case 'cmi.score.raw':
                        $usertrack->score_raw = $value;
                    break;
                    case 'cmi.core.session_time':
                    case 'cmi.session_time':
                        $usertrack->session_time = $value;
                    break;
                    case 'cmi.core.total_time':
                    case 'cmi.total_time':
                        $usertrack->total_time = $value;
                    break;
                }
                
            }
        
        } else return false;
    
        return $usertrack;
    
    } else return false;
    
}

/**
* Возвращает title из сыновей элемента
* <node element>
*   <title>...</title>
* </node element>.
* @return string
*/
function scorm_getTitle($node) {
    
    $childnodes = $node->child_nodes();
    foreach($childnodes as $v) {
        
        if ($v->tagname == 'title') {
            
            $ret = $v->get_content();
            
            if (empty($ret)) $ret = 'не задано';
            
            return $ret;
            
        }
        
    }
    
    return 'не задано';
    
}

function scorm_get_item_info($node) {
    
    $ret['title'] = 'unknown';
    
    $childnodes = $node->child_nodes();
    
    foreach($childnodes as $v) {

        switch($v->tagname) {
            case 'title':
                $ret['title'] = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$v->get_content());
                if (empty($ret['title'])) $ret['title'] = 'noname';
            break;
            case 'prerequisites':
            case 'adlcp:prerequisites':
                $ret['prerequisites'] = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$v->get_content());
            break;
            case 'maxtimeallowed':
            case 'adlcp:maxtimeallowed':
                $ret['maxtimeallowed'] = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$v->get_content());
            break;
            case 'masteryscore':
            case 'adlcp:masteryscore':
                $ret['masteryscore'] = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$v->get_content());
            break;
            case 'completionThreshold':
                $ret['completionthreshold'] = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$v->get_content());
            break;
            case 'timeLimitAction':
            case 'adlcp:timelimitaction':
                $ret['timelimitaction'] = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$v->get_content());
            break;
            case 'dataFromLMS':
            case 'adlcp:datafromlms':
                $ret['datafromlms'] = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$v->get_content());
            break;
        }
    }
    
    return $ret;
}

/**
* @return dom
*/
function scorm_getResourceElements($dom) {
    
    $resources = false;
    
//    $dom = domxml_open_mem($strXml);
    $resources_tmp = $dom->get_elements_by_tagname('resources');
    
    if (is_array($resources_tmp) && count($resources_tmp)) {
    
        reset($resources_tmp);
        while(list($k,$v) = each($resources_tmp)) {
            
            $basePath = '';
        
            if ($v->has_attribute('base')) $basePath .= iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$v->get_attribute('base'));
            
            $resource_tmp = $v->get_elements_by_tagname('resource');
        
            if (is_array($resource_tmp) && count($resource_tmp)) {
                
                reset($resource_tmp);
                
                while(list($kk,$vv) = each($resource_tmp)) {
                    
                    $basePathResource = '';
                        
                    if (($vv->has_attribute('identifier'))
                    && ($vv->has_attribute('href'))) {
                    
                        if ($vv->has_attribute('base')) $basePathResource = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$vv->get_attribute('base'));
                    
                        $resources[$vv->get_attribute('identifier')]['href'] = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$basePath.$basePathResource.$vv->get_attribute('href'));
                        $resources[$vv->get_attribute('identifier')]['type'] = iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$vv->get_attribute('scormType'));
                
                    }
            
                }
            }
            
            
        }
    
    } // is_array
        
    return $resources;
    
}

/**
* Возвращает href ресурсов связанных с ref item'а
* @return array
*/
function scorm_getResourcesById($ref) {
    
    global $scorm;

    if (isset($scorm->resources[$ref])) $ret[] = $scorm->resources[$ref];
    
    if (isset($ret) && is_array($ret) && count($ret)>0) return $ret;
    
    return false;
}

function scorm_getVersion($dom) {
    
//    $dom = domxml_open_mem($strContent);
    
    $nodes = $dom->get_elements_by_tagname('metadata');
    
    if (is_array($nodes) && count($nodes)) {
        
        reset($nodes);
        while(list(,$node) = each($nodes)) {
    
            if ($node->has_child_nodes()) {
     
                $children = $node->child_nodes();
                foreach($children as $value) {
            
                    if ($value->tagname == 'schemaversion') {
             
                        if (preg_match("/^(1\.2)$|^(CAM )?(1\.3)$/",$value->get_content(),$matches)) {
                    
                            $version = 'SCORM_'.$matches[count($matches)-1];
                
                        } else {
                            
                            if (strstr($value->get_content(), '2004')!==false) {
                                $version = 'SCORM_1.3';
                            } else {                    
                                $version = 'SCORM_1.2';
                            }
                
                        }                    
                
                    }
            
                }
        
            }
    
        } // while
    
    }
    
    if (isset($version)) return $version; else return "SCORM";
    
}

function scorm_validate($packagedir) {
    
    if (is_file($packagedir.'/'.IMS_MANIFEST_FILENAME)) {
        
        $validation->result = 'found';
        $validation->pkgtype = 'SCORM';
        
    } else {
        
        if ($handle = opendir($packagedir)) {
            
            while (($file = readdir($handle)) !== false) {
                
                $ext = substr($file,strrpos($file,'.'));
                if (strtolower($ext) == '.cst') {
                    $validation->result = 'found';
                    $validation->pkgtype = 'AICC';
                    break;
                    
                }
                
            }
            
            closedir($handle);
            
        }
        if (!isset($validation)) {
            
            $validation->result = 'nomanifest';
            $validation->pkgtype = 'SCORM';
            
        }
    }
    return $validation;
}

/**
* Transliterate ссылок в xml файле
*/
function ims_transliterateXMLHrefs($file_name) {

        if( !defined("IS_TRANSLITERATE_SRC_VALUE") ) {
                define("IS_TRANSLITERATE_SRC_VALUE", false);
        }

        if(IS_TRANSLITERATE_SRC_VALUE) {

                $domxml_object = domxml_open_file($file_name);
                $elements_array = $domxml_object->get_elements_by_tagname("file");
                foreach($elements_array as $key => $element) {
                        $element->set_attribute("href", iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8",to_translit(iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$element->get_attribute("href")))));
                }
                
                $elements_array = $domxml_object->get_elements_by_tagname("resource");
                foreach($elements_array as $key => $element) {
                        $element->set_attribute("href", iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8",to_translit(iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$element->get_attribute("href")))));
                }
                $domxml_object->dump_file($file_name);
                
        }

}

function is_scorm_module($ModID) {
    
    if ($ModID>0) {
        $sql = "SELECT bid FROM library WHERE bid='".(int) $ModID."' AND (content LIKE 'SCORM%' OR content LIKE 'AICC%')";
        $res = sql($sql);
        $ret = sqlrows($res);
    }
    
    return $ret;
}

function get_scorm_number_of_qestions($trackdata) {
    $questall = 0;
    if (!empty($trackdata)) {
        $arrTrackdata = unserialize($trackdata);
        if (is_array($arrTrackdata)) {
            $i=0;
            while(isset($arrTrackdata['cmi.interactions.'.$i.'.type'])) $i++;
            $questall = $i;
        }
    }
    return $questall;
}

function get_scorm_tracks_by_mid($mid,$McID,$CID, $a_mark,$additional_col,$vars) {
    if (($mid>0) && ($McID>0)) {        
        $sql = "SELECT scorm_tracklog.* 
                FROM scorm_tracklog 
                WHERE scorm_tracklog.mid='".(int) $mid."' AND scorm_tracklog.McID='".(int) $McID."'
                AND scorm_tracklog.cid = '".(int) $CID."'
                ORDER BY scorm_tracklog.start DESC";
        $res = sql($sql);
        
        $counter_in_cycle = 0;
        while($row = sqlget($res)) {
            $counter_in_cycle++;
                    
            $questall = get_scorm_number_of_qestions($row['trackdata']);
            
            $status = isset($GLOBALS['scorm_completion_statuses'][$row['status']]) ? $GLOBALS['scorm_completion_statuses'][$row['status']] : $row['status'];
            if (empty($status)) $status = "не завершено";
            if ($row['scoremax']!=0) $est = @sprintf("%1.0f",$row['score']*100/$row['scoremax']);
            else $est = '0';
            $str = ($GLOBALS['intGid']) ? "<td class='small'>&nbsp;</td>" : "";
            $str .=
                   "<td class='small'>".date("d.m.y H:i",strtotime($row['start']))."</td>
                   <td class='small'><FONT SIZE=+1>".$row['score']."</FONT></td>
                   <td class='small'>".$row['scoremax']."</td>
                   <td class='small'>".$questall."</td>
                   <td class='small'><a href='[PATH]scorm_track_log.php?[SESSID]&trackid=".$row['trackID']."
                             ' onclick='wopen(\"\",\"log\",790,575,1)' target='log'> ".$status." (".$est."%)>></a></a>
                   </td>";
            // WARNING - РАБОТА С ГЛОБАЛЬНЫМИ ПЕРЕМЕННЫМИ       
                 if ($GLOBALS['method_id'] > 0) { 

                   $formula_query = "SELECT * FROM formula WHERE id = ".$GLOBALS['method_id'];
                   $formula_result = sql($formula_query, "errfn4435");
                   $formula_row = sqlget($formula_result);
                   switch($formula_row['type']) {
                          case "1":
                            $mark = viewFormula($vars['formula'],$vars['text'],0,$row['scoremax'],$row['score']);
                            if ($counter_in_cycle == 1) {
                                $a_mark[$mid] = $mark;
                            }
                            $str.="<td class='small'><FONT COLOR=red><B>".intval($mark)."</B></FONT></td>";
                            $additional_col = true;
                            
                          break;
                   }
                 }
            // WARNING!!
            if ($row['start']) {
                $ret[strtotime($row['start'])] = $str;
            } else {
                $ret[] =  $str;
            }
        }
    }    
    return $ret;
}

function scorm_reconstitute_array_element($sversion, $userdata, $element_name, $children) {
    // reconstitute comments_from_learner and comments_from_lms
    $current = '';
    $current_subelement = '';
    $current_sub = '';
    $count = 0;
    $count_sub = 0;

    // filter out the ones we want
    $element_list = array();
    foreach($userdata as $element => $value){
        if (substr($element,0,strlen($element_name)) == $element_name) {
            $element_list[$element] = $value;
        }
    }

    // sort elements in .n array order
    uksort($element_list, "scorm_element_cmp");

    // generate JavaScript
    foreach($element_list as $element => $value){
        if ($sversion == 'scorm_13') {
            $element = preg_replace('/\.(\d+)\./', ".N\$1.", $element);
            preg_match('/\.(N\d+)\./', $element, $matches);
        } else {
            $element = preg_replace('/\.(\d+)\./', "_\$1.", $element);
            preg_match('/\_(\d+)\./', $element, $matches);
        }
        if (count($matches) > 0 && $current != $matches[1]) {
            if ($count_sub > 0) {
                echo '    '.$element_name.'_'.$current.'.'.$current_subelement.'._count = '.$count_sub.";\n";
            }
            $current = $matches[1];
            $count++;
            $current_subelement = '';
            $current_sub = '';
            $count_sub = 0;
            $end = strpos($element,$matches[1])+strlen($matches[1]);
            $subelement = substr($element,0,$end);
            echo '    '.$subelement." = new Object();\n";
            // now add the children
            foreach ($children as $child) {
                echo '    '.$subelement.".".$child." = new Object();\n";
                echo '    '.$subelement.".".$child."._children = ".$child."_children;\n";
            }
        }

        // now - flesh out the second level elements if there are any
        if ($sversion == 'scorm_13') {
            $element = preg_replace('/(.*?\.N\d+\..*?)\.(\d+)\./', "\$1.N\$2.", $element);
            preg_match('/.*?\.N\d+\.(.*?)\.(N\d+)\./', $element, $matches);
        } else {
            $element = preg_replace('/(.*?\_\d+\..*?)\.(\d+)\./', "\$1_\$2.", $element);
            preg_match('/.*?\_\d+\.(.*?)\_(\d+)\./', $element, $matches);
        }

        // check the sub element type
        if (count($matches) > 0 && $current_subelement != $matches[1]) {
            if ($count_sub > 0) {
                echo '    '.$element_name.'_'.$current.'.'.$current_subelement.'._count = '.$count_sub.";\n";
            }
            $current_subelement = $matches[1];
            $current_sub = '';
            $count_sub = 0;
            $end = strpos($element,$matches[1])+strlen($matches[1]);
            $subelement = substr($element,0,$end);
            echo '    '.$subelement." = new Object();\n";
        }

        // now check the subelement subscript
        if (count($matches) > 0 && $current_sub != $matches[2]) {
            $current_sub = $matches[2];
            $count_sub++;
            $end = strrpos($element,$matches[2])+strlen($matches[2]);
            $subelement = substr($element,0,$end);
            echo '    '.$subelement." = new Object();\n";
        }

        echo '    '.$element.' = \''.$value."';\n";
    }
    if ($count_sub > 0) {
        echo '    '.$element_name.'_'.$current.'.'.$current_subelement.'._count = '.$count_sub.";\n";
    }
    if ($count > 0) {
        echo '    '.$element_name.'._count = '.$count.";\n";
    }
}

function scorm_element_cmp($a, $b) {
    preg_match('/.*?(\d+)\./', $a, $matches);
    $left = intval($matches[1]);
    preg_match('/.?(\d+)\./', $b, $matches);
    $right = intval($matches[1]);
    if ($left < $right) {
        return -1; // smaller
    } elseif ($left > $right) {
        return 1;  // bigger
    } else {
        // look for a second level qualifier eg cmi.interactions_0.correct_responses_0.pattern
        if (preg_match('/.*?(\d+)\.(.*?)\.(\d+)\./', $a, $matches)) {
            $leftterm = intval($matches[2]);
            $left = intval($matches[3]);
            if (preg_match('/.*?(\d+)\.(.*?)\.(\d+)\./', $b, $matches)) {
                $rightterm = intval($matches[2]);
                $right = intval($matches[3]);
                if ($leftterm < $rightterm) {
                    return -1; // smaller
                } elseif ($leftterm > $rightterm) {
                    return 1;  // bigger
                } else {
                    if ($left < $right) {
                        return -1; // smaller
                    } elseif ($left > $right) {
                        return 1;  // bigger
                    }
                }
            }
        }
        // fall back for no second level matches or second level matches are equal
        return 0;  // equal to
    }
}

?>