<?php

$positions_types = array(0 => _("должность"), 1 => _("рук. должность"), 2 => _("оргединица"));

function get_mid_by_soid($soid) {
         $query = "SELECT mid FROM structure_of_organ WHERE soid='{$soid}'";
         $result = sql($query,"err");
         $row = sqlget($result);

         //echo "</pre>";

         return $row['mid'];
}

function get_lastname_and_firstname_by_mid($mid) {
         $query = "SELECT LastName, FirstName FROM People WHERE MID=$mid";
         $result = sql($query);
         $row = sqlget($result);
         return $row;
}

function get_head_by_soid($soid) {
        $boss = get_boss($soid);
        if (is_array($boss) && count($boss) && ($boss['mid']>0)) {
            $boss['lastname'] = $boss['LastName'];
            $boss['firstname'] = $boss['FirstName'];
            $ret = $boss;
        }
        return $ret;
        /*
         $query = "SELECT owner_soid FROM structure_of_organ WHERE soid=$soid";
         $result = sql($query);
         $row = sqlget($result);
         if($row['owner_soid'] != 0) {
            $head_mid = get_mid_by_soid($row['owner_soid']);
            $temp = get_lastname_and_firstname_by_mid($head_mid);
            $return_value['mid'] = $head_mid;
            $return_value['lastname']   = $temp['LastName'];
            $return_value['firstname']  = $temp['FirstName'];
            return $return_value;
         }
         else {
            return false;
         }
         */
}

function get_colleagues_by_soid($soid) {
         $ret = false;
         $sql = "SELECT owner_soid, type FROM structure_of_organ WHERE soid=$soid";
         $result = sql($sql);
         if (sqlrows($result)) {
             $row = sqlget($result);
             switch($row['type']) {
                 case 1:
                    $sql = "SELECT owner_soid FROM structure_of_organ WHERE soid='".(int) $row['owner_soid']."'";
                    $res = sql($sql);
                    if (sqlrows($res)) {
                        $row = sqlget($res);
                        $sql = "SELECT soid FROM structure_of_organ WHERE owner_soid='".(int) $row['owner_soid']."'";
                        $res = sql($sql);
                        while ($row = sqlget($res)) $soids[] = $row['soid'];
                        if (is_array($soids) && count($soids)) {
                            $sql = "SELECT structure_of_organ.*, People.LastName as lastname, People.FirstName as firstname 
                            FROM structure_of_organ INNER JOIN People ON (structure_of_organ.mid = People.MID) 
                            WHERE structure_of_organ.type = '1' AND structure_of_organ.owner_soid IN ('".join("','",$soids)."') 
                            AND structure_of_organ.soid <> '".(int) $soid."'
                            ORDER BY People.LastName, People.FirstName, People.Login";
                            $res = sql($sql);
                            while($row = sqlget($res)) {
                                $ret[$row['mid']] = $row;
                            }
                        }
                    }
                 break;
                 default:
                    $sql = "SELECT structure_of_organ.*, People.LastName as lastname, People.FirstName as firstname 
                    FROM structure_of_organ INNER JOIN People ON (structure_of_organ.mid = People.MID) 
                    WHERE structure_of_organ.type <> '1' AND structure_of_organ.soid <> '".(int) $soid."'
                    AND structure_of_organ.owner_soid = '".(int) $row['owner_soid']."'
                    ORDER BY People.LastName, People.FirstName, People.Login";
                    $res = sql($sql);
                    while($row = sqlget($res)) {
                        $ret[$row['mid']] = $row;        
                    }
                 break;
             }
         }
         return $ret;
}

/*
function get_colleagues_by_soid($soid) {
         $query = "SELECT owner_soid, type FROM structure_of_organ WHERE soid=$soid";
         $result = sql($query,"err");
         if (sqlrows($result)) {
             $row = sqlget($result);
             $owner_soid = $row['owner_soid'];
             //$query = "SELECT * FROM structure_of_organ WHERE owner_soid = '$owner_soid' AND soid <> $soid";
             $query = "SELECT * FROM structure_of_organ WHERE owner_soid = '$owner_soid' AND soid <> $soid AND type='0'";
             $result = sql($query, "err");
             if(sqlrows($result) > 0) {
                 while($row = sqlget($result)) {
                   if($row['mid'] != 0) {
                      $temp = get_lastname_and_firstname_by_mid($row['mid']);
                      $return_value[$row['mid']]['lastname']  =  $temp['LastName'];
                      $return_value[$row['mid']]['firstname'] =  $temp['FirstName'];
                   }
                 }
                 return $return_value;
             }
             else {
                 return false;
             }
         }
}
*/

function get_subordinates_by_soid($soid) {
         $slaves = get_slaves($soid);
         if (is_array($slaves) && count($slaves)) {
             while(list(,$v) = each($slaves)) {
                 if ($v['mid']>0) {
                     $v['lastname'] = $v['LastName'];
                     $v['firstname'] = $v['FirstName'];
                     $ret[$v['mid']] = $v;
                 }
             }
         }
         return $ret;
         /*
         $query = "SELECT * FROM structure_of_organ WHERE owner_soid = $soid";
         $result = sql($query, "err");
         if(sqlrows($result) > 0) {
            while($row = sqlget($result)) {
               if($row['mid'] != 0) {
                  $temp = get_lastname_and_firstname_by_mid($row['mid']);
                  $return_value[$row['mid']]['lastname']  = $temp['LastName'];
                  $return_value[$row['mid']]['firstname'] = $temp['FirstName'];
               }
            }
            return $return_value;
         }
         else {
            return false;
         }
         */
}

// FROM positions.php

function get_structure( ){

  $tmp= "SELECT soid as soid, soid_external, name as name, mid as mid, info as info, owner_soid as owner_soid, agreem as agreem, 
         type as type, code as code 
         FROM structure_of_organ
         ";
  $res=sql( $tmp );

  while( $r=sqlget( $res ) ){
     $divs[ $r[ soid ] ][ soid ]= $r[ soid ];
     $divs[ $r[ soid ] ][ owner_soid ]= $r[ owner_soid ];
     $divs[ $r[ soid ] ][ name ]= $r[ name ];
     $divs[ $r[ soid ] ][ type ] = $r[ type ];
     //$divs[ $r[ did ] ][ color ]= $r[ color ];
     $divs[ $r[ soid ] ][ mid ]= $r[ mid ];
     $divs[ $r[ soid ] ][ info ]= $r[ info ];
     $divs[ $r[ soid ] ][ code ]= $r[ code ];
     $divs[ $r[ soid ] ][ soid_external ]= $r[ soid_external ];
  }
  sqlfree($r);
  return( $divs );
}

function get_structure_level( $divs, $div, $i=0 ){
  // check infinite loop
  $i++;
  if( ( $divs[ $div ][ owner_soid ] > 0 ) && ( $i < count ( $divs ) ) ){
     $level=get_structure_level( $divs, $divs[ $div ][ owner_soid ], $i ) + 1;
//     echo "level= $level !! ";
  }else
    $level = 0;
  return( $level );
}

function set_structure_levels( &$divs ){
if (is_array($divs)) {
  foreach( $divs as $div ){
//    echo $div[ did ]."; ";
    $level = get_structure_level( $divs, $div[ soid ] );
    $divs[ $div[ soid ] ] [ level ] = $level;
    $divs[ $div[ soid ] ] [ org ] = $i++;
  }
  usort($divs,'uksort_function');
}
}

function uksort_function($a,$b) {
    if (($a['type'] == 2) && ($b['type'] == 2)) {
		return ($a['code']<=$b['code']) ? -1 : 1;    	
    }
    elseif (($a['type'] == 2) || ($b['type'] == 2)) {
    	return ($b['type'] == 2) ? -1 : 1;
    }
    elseif (($a['type'] == 1) || ($b['type'] == 1)) {
    	return ($a['type'] == 1) ? -1 : 1;
    }
}

function org_structure_levels( &$divs ){
if (is_array($divs)) {
  foreach( $divs as $div ){
    $level = get_structure_level( $divs, $div[ soid ] );
    $divs[ $div[ soid ] ] [ level ] = $level;
  }
}
}

function selOrganization ($mid) {
	$array = array (0 => "---");
	
	$query = "SELECT * FROM structure_of_organ WHERE mid = '{$mid}'";
	$res = sql($query);
	if(sqlrows($res)) {
		$row = sqlget($res);
		$tmp_array = getChildSoids($row['soid']);
		foreach ($tmp_array as $value) {
			$array[$value['soid']] = $value['name'];
		}
	}
	
	$str = "<select name=sel_soid onchange=\"navigate('abitur.php4?CID='+ChCourse.value+'&soid='+sel_soid.value);\" class=\"selcselect\">\n";
	foreach ($array as $k => $v) {
		$ch = ($_GET['soid'] == $k) ? "selected" : "";
		$str .= "<option value={$k} $ch>{$v}</option>\n";
	}
	$str .= "</select>";
	return $str;
}

function getOrganizations($mid) {
	$array = array();
	$query = "SELECT * FROM structure_of_organ WHERE mid = '{$mid}'";
	$res = sql($query);
	if(sqlrows($res)) {
		$row = sqlget($res);
		$tmp_array = getChildSoids($row['soid']);
		foreach ($tmp_array as $value) {
			$array[] = $value['soid'];
		}
	}
	return $array;
}

function getOrganizations2Agreem($mid) {
	$array = array();
	$query = "SELECT * FROM structure_of_organ WHERE mid = '{$mid}'";
	$res = sql($query);
	if(sqlrows($res)) {
		$row = sqlget($res);
		$tmp_array = getChildSoids2Agreem($row['soid']);
//        pr($tmp_array);
//        die();
		foreach ($tmp_array as $value) {
			$array[] = $value['soid'];
		}
	}
	return $array;
}

function getOrgNameByMid($mid = 0) {
	$q = "SELECT name FROM structure_of_organ WHERE mid = '{$mid}'";
	$r = sql($q);
	$row = sqlget($r);
	return $row['name'];
}

function getChildSoids ($owner_soid) {
	$array = array();
	$query = "SELECT * FROM structure_of_organ WHERE owner_soid = '{$owner_soid}'";
	$res = sql($query);
	if(sqlrows($res)) {
		while($row = sqlget($res)) {
			if($row['soid'] != $owner_soid) {
				$array[] = $row;
				$tmp_array = getChildSoids($row['soid']);				
				foreach ($tmp_array as $value) {
					$array[] = $value;
				}
			}
		}
	}
	return $array;
}



function getChildSoids2Agreem ($owner_soid,$orgunit=false) {
	$array = array();
    
    if (!$orgunit) {
        $sql = "SELECT owner_soid FROM structure_of_organ WHERE soid='".(int) $owner_soid."' AND type='1'";
        $res = sql($sql);
    }
    if ($orgunit || sqlrows($res)) {
        $row = @sqlget($res);
        if ($orgunit) {
            $sql = "SELECT * FROM structure_of_organ WHERE owner_soid = '".(int) $owner_soid."'";
        }
        else {
            $sql = "SELECT * FROM structure_of_organ WHERE owner_soid = '".(int) $row['owner_soid']."' AND soid<>'".(int) $owner_soid."'";
        }
        $res = sql($sql);
        while($row = sqlget($res)) {
            if ($row['type']==2) {
                $sql = "SELECT * FROM structure_of_organ WHERE owner_soid='".(int) $row['soid']."' AND type='1'";
                $res2 = sql($sql);
                if (sqlrows($res2)) {
                    $row2 = sqlget($res2);
                    $array[] = $row2;
                    if (!$row2['agreem']) {
                        $array = array_merge($array,getChildSoids2Agreem($row2['soid']));
                    }
                } else {
                    $array = array_merge($array,getChildSoids2Agreem($row['soid'],true));                    
                }
            } else {
                $array[] = $row;
            }
        }
    }
    
    return $array;        
}

/*
function getChildSoids2Agreem ($owner_soid) {
	$array = array();
	$query = "SELECT * FROM structure_of_organ WHERE owner_soid = '{$owner_soid}'";
	$res = sql($query);
	if(sqlrows($res)) {
		while($row = sqlget($res)) {
			if($row['soid'] != $owner_soid) {
				$array[] = $row;
				if ($row['agreem'] == 0) {
                    $tmp_array = getChildSoids2Agreem($row['soid']);				
				    foreach ($tmp_array as $value) {
					    $array[] = $value;
				    }
                }
			}
		}
	}
	return $array;
}
*/
function print_child_organizations($current_soid, $level=1, $page='positions.php?c=assignement') {
    global $sitepath;
	//$query = "SELECT structure_of_organ.name, structure_of_organ.mid, 
    //         structure_of_organ.soid, People.LastName, People.FirstName 
    //         FROM structure_of_organ LEFT JOIN People
    //         ON structure_of_organ.mid=People.mid
    //         WHERE structure_of_organ.owner_soid = '{$current_soid}' 
    //         GROUP BY structure_of_organ.soid";
	//$res = sql($query);
    $slaves = get_slaves($current_soid);
    if (is_array($slaves) && count($slaves))
    foreach($slaves as $row)
//	while ($row = sqlget($res)) {
        $ret .= "<tr><td><a href=\"{$sitepath}{$page}&soid={$row['soid']}\">{$row['name']} >></a></td><td>{$row['LastName']} {$row['FirstName']}</td></tr>";
//		$ret .= print_child_organizations($row['soid'],$level+1);
//	}
    if (!isset($ret)) $ret .= "<tr><td colspan=2 align=center>"._("Подчинённые отсутствуют")."</td></tr>";
    return $ret;
}

function print_child_organizations_no_links($current_soid, $level=1) {
/*	$query = "SELECT structure_of_organ.name, structure_of_organ.mid, 
             structure_of_organ.soid, People.LastName, People.FirstName 
             FROM structure_of_organ LEFT JOIN People
             ON structure_of_organ.mid=People.mid
             WHERE structure_of_organ.owner_soid = '{$current_soid}' 
             GROUP BY structure_of_organ.soid";
	$res = sql($query);
	while ($row = sqlget($res)) {
    */
    $slaves = get_slaves($current_soid);
    if (is_array($slaves) && count($slaves))
    foreach($slaves as $row)
        $ret .= "<tr><td>{$row['LastName']} {$row['FirstName']}</td><td>{$row['name']}</td></tr>";
//		$ret .= print_child_organizations($row['soid'],$level+1);
//	}
    if (!isset($ret)) $ret .= "<tr><td colspan=2 align=center>"._("Подчинённые отсутствуют")."</td></tr>";
    return $ret;
}

function print_child_organizations_by_mid_no_links($mid, $agreem=1) {
    
    $ret = '';
    
    if ($mid > 0) {
        
//        $sql = "SELECT soid FROM structure_of_organ WHERE mid='".(int) $mid."' AND agreem='".(int) $agreem."'";
        $sql = "SELECT soid FROM structure_of_organ WHERE mid='".(int) $mid."' AND type=1";
        $res = sql($sql);
        
        if (sqlrows($res)) {
            
            $row = sqlget($res);
            $ret .= "
                    <table width=100% class=main cellspacing=0>
                    <tr>
                    <th>"._("Подчиненный")."</th><th>"._("В должности")."</th>
                    </tr>";
            $ret .= print_child_organizations_no_links($row['soid']);
            $ret .= "</table>
                    ";
            
        }
        
    }
    
    return $ret;
    
}

function isAgreem($soid,$cycle=false) {
    $ret = false;
    
    /*    
    if ($soid > 0) {
        $sql = "SELECT type,owner_soid,agreem FROM structure_of_organ WHERE soid='".(int) $soid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            if ($cycle && ($row['type']==1) && $row['agreem']) return true;
            if ($row['owner_soid']>0) {
                if ($row['type']==1) {
                    $ret = isAgreem($row['owner_soid'],true);
                } else {
                    $sql = "SELECT *
                            FROM structure_of_organ 
                            WHERE owner_soid='".(int) $row['owner_soid']."' AND type='1'";
                    $res = sql($sql);
                    if (sqlrows($res)) {
                        $row2 = sqlget($res);
                        $ret = isAgreem($row2['soid'],true);
                    }
                    else {
                        $ret = isAgreem($row['owner_soid'],true);
                    }
                }
            }
        }
    }
    return $ret;
    */
}

/*
function isAgreem($soid=0) {
    
    if ($soid <= 0) return false;
    
    $sql = "SELECT owner_soid, agreem FROM structure_of_organ WHERE soid='".(int) $soid."'";
    $res = sql($sql);
    
    while ($row = sqlget($res)) {
     
        if ($row['agreem'] == 1) return true;
        if (isAgreem($row['owner_soid'])) return true;
        
    }
    
    return false;
}
*/

function isAgreemMid($mid=0) {
    
    if ($mid <= 0) return false;
    
    $sql = "SELECT agreem FROM structure_of_organ WHERE mid='".(int) $mid."'";
    $res = sql($sql);
    
    while ($row = sqlget($res)) {
     
        if ($row['agreem'] == 1) return true;
        
    }
    
    return false;
}

function get_study_rank_orgunit($soid) {
    global $study_ranks;
    if (isset($study_ranks[$soid])) return $study_ranks[$soid];
    
    $count = 0;
    $total_rank = 0;
    $sql = "SELECT * FROM structure_of_organ WHERE owner_soid='".(int) $soid."'";
    $res = sql($sql);
    while($row = sqlget($res)) {
        switch($row['type']) {
            case 2:
                $total_rank += get_study_rank_orgunit($row['soid']);
            break;
            default:
                $total_rank += get_study_rank_position($row['soid'],$row['mid']);
            break;
        }
        $count++;
    }
    if ($count>0) $study_ranks[$soid] = (int) ($total_rank / $count);
    else $study_ranks[$soid] = 0;
    return $study_ranks[$soid];
}

function get_study_rank_position($soid,$mid) {
    global $study_ranks;
    if (isset($study_ranks[$soid])) return $study_ranks[$soid];
    
    if ($mid > 0) {        
        
        $roles = CCompetenceRoles::getCompetencesBySoid($soid);
        if (count($roles)) {
            foreach($roles as $role) {
                if (is_array($role) && count($role)) {
                    foreach($role as $comp) {
                        $people = getPeopleByCompetences(array($comp['competence']), array($comp['threshold']));                
        	            foreach ($people as $v) {
        		            if($v['people_mid'] == $mid) $coids[$comp['competence']] = 100;
                        }
                        if (!isset($coids[$comp['competence']])) $coids[$comp['competence']] = 0;                        
                    }
                }
            }
            $total_rank = 0;
            foreach($coids as $key=>$rank) $total_rank += $rank;
            $total_rank = (int) ($total_rank / count($coids));
        } else $total_rank = 100;
        
/*        $sql = "SELECT coid, percent FROM str_of_organ2competence WHERE soid='".(int) $soid."'";
        $res = sql($sql);        
        if (sqlrows($res)) {            
            while ($row = sqlget($res)) {
                $people = getPeopleByCompetences(array($row['coid']), array($row['percent']));                
	            foreach ($people as $v) {
		            if($v['people_mid'] == $mid) $coids[$row['coid']] = 100;
                }                
                if (!isset($coids[$row['coid']])) $coids[$row['coid']] = 0;
            }
            $total_rank = 0;
            foreach($coids as $key=>$rank) $total_rank += $rank;
            $total_rank = (int) ($total_rank / count($coids));
            sqlfree($res);            
        } else $total_rank = 100;    
*/
    } else $total_rank = 0;
    $study_ranks[$soid] = $total_rank;
    return $study_ranks[$soid];
}

function get_study_rank($soid,$type,$mid) {
    global $study_ranks;    
    if (isset($study_ranks[$soid])) return $study_ranks[$soid];    
    switch($type) {
        case '2':
            $study_ranks[$soid] = (int) get_study_rank_orgunit($soid);
        break;
        default:
            $study_ranks[$soid] = (int) get_study_rank_position($soid,$mid);
        break;
    }
    return $study_ranks[$soid];
}

/**
* Расчитать уровень обученности
*/
/*
function get_study_rank($soid, $mid) {
    
    global $study_ranks;
    
    $count = 1;
    
    if (isset($study_ranks[$soid])) return $study_ranks[$soid];
        
    if ($mid > 0) {
        
    $sql = "SELECT coid, percent FROM str_of_organ2competence WHERE soid='".(int) $soid."'";
    $res = sql($sql);
    
    if (sqlrows($res)) {
        
        while ($row = sqlget($res)) {
            
            $people = getPeopleByCompetences(array($row['coid']), array($row['percent']));
            
	        foreach ($people as $v) {
		        if($v['people_mid'] == $mid) $coids[$row['coid']] = 100;
            }
            
            if (!isset($coids[$row['coid']])) $coids[$row['coid']] = 0;
            
        }
        
        $total_rank = 0;
        foreach($coids as $key=>$rank) $total_rank += $rank;
        $total_rank = (int) ($total_rank / count($coids));                                

        sqlfree($res);
        
    } else $total_rank = 100;
    
    } else $total_rank = 0;

    // Анализ дочерних элементов
        
    $sql = "SELECT soid, mid FROM structure_of_organ WHERE owner_soid='".(int) $soid."'";
    $res = sql($sql);
        
    if (sqlrows($res)) {
            
        while ($row = sqlget($res)) {
                
            $sub_rank = get_study_rank($row['soid'],$row['mid']);
//            $total_rank = (int) (($total_rank + $sub_rank) / 2);

            $total_rank += $sub_rank;
            $count++;

//            $total_rank += $sub_rank['total_rank'];
//            $count += $sub_rank['count'];
            
            //$count++;
                
        }
            
    }
    // ---
            
    $study_ranks[$soid] = (int) ($total_rank / $count);
    
    //$ret['total_rank'] = $total_rank;
    //$ret['count'] = $count;
    
    $ret = (int) ($total_rank / $count);

    return $ret;
    
}
*/
function peopleSelect4Position($table="", $mid=0, $soid=0, $exclTable="", $boolShowAdmin = true, $where='') {
    //$peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
    if ($mid==0) $mid = $GLOBALS['s']['mid'];    
    $html="";
    $sqlExtraWhere = (!$boolShowAdmin) ? " AND People.`MID` != '1' " : " ";
    $tables = (!strlen($table)) ? array("admins", "deans", "Teachers", "Students") : array($table);
    $arrMids = array();
    if($exclTable != "") {
        $r = sql("SELECT * FROM {$exclTable}", "err343223");
        while($a = sqlget($r)) {
            //if ($peopleFilter->is_filtered($a['MID']))
            $arrMids[] = $a['MID'];
        }
    }
    $strMids = implode(",", $arrMids);
    if (strlen($strMids)) $sqlExtraWhere .= " AND People.`MID` NOT IN ({$strMids}) ";
    
    /**
    * Подчинённые
    */
    $sql = "
    SELECT DISTINCT People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname
    FROM People, structure_of_organ WHERE People.MID=structure_of_organ.mid AND 
    structure_of_organ.owner_soid='".(int) $soid."'
    $where
    ORDER BY `People`.LastName, People.FirstName, People.Login";
    $res = sql($sql);
    while($row = sqlget($res)) {
        //if (!$peopleFilter->is_filtered($row['MID'])) continue;
        $strSel = ($row['MID'] == $mid) ? "selected" : "";
        $rows[]="<option value=".$row['MID']." $strSel>".$row['lname']." ".$row['fname']." (".$row['login'].")</option>\n";
        $doneMids[] = $row['mid'];
        
    }
    
    if (isset($doneMids) && count($doneMids)) $rows[] = "<option>---</option>";

    /**
    * Кто не входит в организацию
    */
    
    unset($doneMids);
    
    $sql = "
    SELECT DISTINCT People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname
    FROM People LEFT JOIN structure_of_organ ON People.MID = structure_of_organ.mid WHERE structure_of_organ.MID IS NULL
    $where
    ORDER BY `People`.LastName, People.FirstName, People.Login";
    $res = sql($sql);
    while($row = sqlget($res)) {
        //if (!$peopleFilter->is_filtered($row['MID'])) continue;
     
        $strSel = ($row['MID'] == $mid) ? "selected" : "";
        $rows[]="<option value=".$row['MID']." $strSel>".$row['lname']." ".$row['fname']." (".$row['login'].")</option>\n";
        $doneMids[] = $row['mid'];
        
    }
    
    if (isset($doneMids) && count($doneMids)) $rows[] = "<option>------</option>";

    foreach ($tables as $table) {
        $sql = "
               SELECT DISTINCT
                People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname
               FROM
                People
                LEFT JOIN {$table} ON (People.`MID` = {$table}.`MID`)
               WHERE 1=1
                {$sqlExtraWhere}
                {$where}
               ORDER BY
               `People`.LastName, People.FirstName, People.Login";
        $res=sql($sql,"admEr02");
        while ($row=sqlget($res)) {
            //if (!$peopleFilter->is_filtered($row['MID'])) continue;
            $strSel = ($row['MID'] == $mid) ? "selected" : "";
            $rows[]="<option value=".$row['MID']." $strSel>".$row['lname']." ".$row['fname']." (".$row['login'].")</option>\n";
        }
    }
    if (!count($rows)) return "<option>" . _('не найдено') . "</option>";
    $rows = array_unique($rows);
    foreach ($rows as $row) {
        $html.=$row;
    }
    return $html;
}


function makeProgressBar($proccompl,$procneed, $digits=false){

  $str.="<table width=100% class=main cellspacing=0><tr>";
  if ($proccompl) {
    
    $str.=" <td width='".$proccompl."%' class='tdr' style='background: #999999;' align='right'>&nbsp;";
    if ($digits && ($proccompl>50)) $str.=(int) $proccompl."%";
      $str.="</td>";
    }

    if ($procneed > $proccompl) {
        
        $str.=" <td width='".($procneed-$proccompl)."%' class='tdr' style='background: #DDDDDD;' align='right'>&nbsp;";
//        if ($proccompl>50) $str.=$proccompl."%";
        $str.="</td>";
    
    }    

  if ($proccompl<100) {
    $str.="<td align='left'>&nbsp;";
    if ($digits && ($proccompl<=50)) $str.=(int) $proccompl."%";
    $str.="</td>";
  }
  $str.="</tr></table>";
  return $str;

}

function get_course_cost($cid) {
    global $valuta;
    
    $ret = array('Fee' => 0, 'valuta' => 0, 'cost' => '');
    
    $sql = "SELECT Fee, valuta FROM Courses WHERE CID='".(int) $cid."'";
    $res = sql($sql);
    
    if (sqlrows($res)) {
        
        $ret = sqlget($res);
        $ret['cost'] = '';
        if (($ret['Fee']>0) && ($ret['valuta']>0)) $ret['cost'] = $ret['Fee'].' '.$valuta[$ret['valuta']][0];
        
    }
    
    return $ret;
    
}

function get_polls_by_mid($mid) {
    
    $ret = false;
    
    $sql = "SELECT polls FROM People WHERE MID='".(int) $mid."' AND polls IS NOT NULL";
    $res = sql($sql);
    
    if (sqlrows($res)) {
        
        $row = sqlget($res);
        $ret = unserialize($row['polls']);
        
    }
    
    return $ret;   
    
}

function set_polls_by_mid($mid,$arrPolls) {
        
    $sql = "UPDATE People SET polls='".serialize($arrPolls)."' WHERE MID='".(int) $mid."'";
    $res = sql($sql);
    
    return true;
    
}

function showPollStatistic( $sheid, $tid, $cid, $kl="", $is_quiz = true ) {
    
         $results = false;
         
         $GLOBALS['brtag']="~\x03~";
         if($kl==""){
            
             // если для всех вопросов задания
            $q = "SELECT * FROM test WHERE tid=$tid";
            $res=sql($q,"errTT71");
            if (sqlrows($res)==0) return '';
            $r=sqlget($res);
            sqlfree($res);
  
            //$tmp .= "<br />";
            $results['title'] = $r[title];            
            
            $sql = "SELECT begin, end FROM schedule WHERE SHEID='".(int) $sheid."'";
            $res = sql($sql);
            if (sqlrows($res)) {
            
                $row = sqlget($res);
                $results['date_begin'] = '['.mydate($row['begin']).']';
                $results['date_end'] = '['.mydate($row['end']).']';
                
            }
  
            $cid = $r['cid'];
            if(strpos($r[data],"%") === false){
                $kodlist=explode($GLOBALS['brtag'],$r[data]);
            }
            else{                
                $qeu_t = "SELECT kod FROM list WHERE kod LIKE '{$cid}-%'"; 
                $res_t = sql($qeu_t,"errTK023");
                while($t_get = sqlget($res_t)){
                    $kodlist[] = $t_get[kod];                               
                }
            }


         }
         else {
               // если только для одного вопроса
               $kodlist[1]=$kl;
         }
         
         $i = 0;
         $qmoderCount = 0;         
         foreach ( $kodlist as $kk=>$vv ) {
                     // ДЛЯ ВОПРОСА ТЕКУЩЕГО
                   $q = "SELECT 
                             logseance.stid as stid, 
                             logseance.kod, 
                             logseance.number,
                             logseance.text,
                             list.qtype,
                             list.qdata as qdata,
                             list.adata as adata,
                             list.weight,
                             loguser.log as log
                             FROM logseance
                             LEFT JOIN list ON (list.kod=logseance.kod)
                             LEFT JOIN loguser ON (loguser.stid=logseance.stid)
                             WHERE logseance.kod ='".$kodlist[$kk]."'
                             AND logseance.sheid = '".(int) $sheid."'";
                   $res=sql($q,"errTL337");

                   while( $r = sqlget($res) ) {
                       
                       if (!in_array($r['qtype'],array(1,2,6))) continue;
                       if (!isset($results[$r['kod']])) {                           
                           require_once('template_test/'.(int) $r['qtype'].'-v.php');
                           $func = "v_sql2php_".(int) $r['qtype'];                           
                           $vopros = $func($r);
                           $results[$r['kod']]['type'] = $r['qtype'];
                           $results[$r['kod']]['title'] = $vopros['vopros'];
                           if (is_array($vopros['variant']) && count($vopros['variant'])) {
                               foreach($vopros['variant'] as $k=>$v) {
                                   $results[$r['kod']]['otvets'][$k]['text'] = $v;
                               }
                               $results[$r['kod']]['weight'] = unserialize($r['weight']);
                           }
                       }
                                              
                       if (!empty($r['log'])) {
                           $r['log'] = unserialize($r['log']);
                           if (is_array($r['log']['aotv'][$r['number']]) && count($r['log']['aotv'][$r['number']])) {
                               foreach($r['log']['aotv'][$r['number']] as $key=>$value) {
                                   $results[$r['kod']]['totalanswers']++;
                                   switch($r['qtype']) {
                                       case 1:
                                           $results[$r['kod']]['otvets'][$value]['count']++;
                                           $results[$r['kod']]['totalbal']+=$results[$r['kod']]['weight'][$value];
                                           $results[$r['kod']]['totalcount']+=max($results[$r['kod']]['weight']);
                                       break;
                                       case 2:
                                           if ($value==1) {
                                               $results[$r['kod']]['otvets'][$key+1]['count']++;
                                               $results[$r['kod']]['totalbal']+=$results[$r['kod']]['weight'][$value];
                                           }
                                           $results[$r['kod']]['totalcount']+=$results[$r['kod']]['weight'][$value];
                                       break;
                                   }
                               }
                           }
                       }
                       if ($r['qtype']==6) {
                           $push = array('text'=>$r['text']);
                           $results[$r['kod']]['otvets'][] = $push;
                       }
                   }
                       
                       // Загоняем в массив все варианты ответов
/*                       if (!isset($results[$r[kod]])) {
                            $j=1;
                            $otvs = explode($brtag,$r[qdata]);
                            for($i=1;$i<count($otvs);$i++) {

                                if (isset($otvs[$i+1])) {
                                    $results[$r[kod]]['otvets'][$otvs[$i]]['text'] = $otvs[$i+1];
                                    $results[$r[kod]]['totalansws'] = $j++;
                                }
                            
                                $i++;
                       
                            }
                       }                                              
                       
                        $ok = unserialize($r[otvet]);
                        if (is_array($ok) && count($ok)) {
                        
                            if (is_array($ok[main]) && count($ok[main])) {
                            
                                foreach($ok[main] as $k=>$v) {
                                    
                                    $otv = str_replace(_("Выбран вариант")." N", "",$v);
                                    $otv = explode(':',$otv);
                                    
                                    if (is_array($otv) && count($otv)==2) {
                                        
                                        if (isset($results[$r[kod]][totalbal])) {
                                            
                                            $results[$r[kod]][totalbal] = round(($results[$r[kod]][totalbal] + $otv[0]) / 2);
                                            
                                        } else $results[$r[kod]][totalbal] = $otv[0];
                                 
                                        if (isset($results[$r[kod]]['otvets'][$otv[0]][count])) $results[$r[kod]]['otvets'][$otv[0]][count]++;
                                        else {
                                                                            
                                            $results[$r[kod]]['otvets'][$otv[0]][count]=1;
                                            $results[$r[kod]][title]=$otvs[0];
                                    
                                        }   
                                    
                                    }
                                    
                                }
                                
                            }
                        
                        } 
                   }
*/                   sqlfree($res);
         } // foreach по вопросам
         return $results;
}

function get_last_polls($polls) {
    
    $ret = false;
    
    foreach($polls as $poll) {
        
        $p = explode('#',$poll);
        
        if (is_array($p) && (count($p)==2)) $tmp[$p[1]][] = $p[0];
        
    }
    
    if (is_array($tmp) && count($tmp)) {
        
        foreach($tmp as $k=>$v) $ret[] = intval(max($v)).'#'.intval($k);
        
    }
    
    return $ret;
    
}

function get_polls($mid,$last=false) {
    
    require_once('lib/classes/Poll.class.php');
    if ($polls = CPolls::getResults($mid,$last)) {

        if (is_array($polls) && count($polls)) {
            
            $sql = "SELECT name, id FROM competence_roles";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $roles[$row['id']] = $row['name'];
            }
            
            $tmp = "<table width=100% class=main cellspacing=0>";
            foreach($polls as $poll) {
                $results = $poll->attributes['results'];
                if (is_array($results) && count($results)) {
                    $tmp .= "<tr><th colspan=3>{$poll->attributes['name']} (".$poll->rusDate($poll->attributes['begin'],"d.m.Y").' - '.$poll->rusDate($poll->attributes['end'],"d.m.Y").")</th></tr>";
                    foreach($results as $result) {
                        if (isset($roles[$result->role])) {
                            $tmp .= "<tr><td colspan=3><b>".$roles[$result->role]."</b></td></tr>";
                            $info = $result->result['info'];
                            if (is_array($info) && count($info)) {
                                foreach($info as $competence=>$values) {
                                    $procent = 0;
                                    if ($diff = ($result->result['max'] - $result->result['min'])) {                                        
                                        $procent = round(($values['avg'] * 100) / $diff);
                                    }
                                    $tmp .= "<tr><td>{$competence}</td><td>{$values['avg']}</td><td>".makeProgressBar($procent,0, true)."</td></tr>";
                                }
                            } else {
                                $tmp .= "<tr><td colspan=3 align=center>"._('Нет результатов')."</td></tr>";
                            }
                            $tmp .= "<tr><td colspan=3>&nbsp;</td></tr>";
                        }
                    }
                }
                
            }
            $tmp .= "</table>";
        }
    }
    return $tmp;
    /////////////////////////////////////////////
   global $sitepath;
    
   $polls = get_polls_by_mid($mid);
   if ($polls && is_array($polls)) {
       
       if ($last) $polls = get_last_polls($polls);
   
       if (!$GLOBALS['controller']->enabled)
       $tmp .= "<span style='font-size: 11px;'>"._("Результаты опросов:")."</span><br /><br />";
   
       $tmp .= "<table width=100% class=main cellspacing=0>";
       $tmp .= "<tr><th>"._("Компетенция")."</th><th>"._("Оценки")."</th><th>"._("Средняя")."</th><th></th></tr>";

       $n = 1;
       $m = 1;
       foreach($polls as $poll) {
           
           $p = explode('#',$poll);
           
           if (is_array($p) && (count($p)==2)) {
                                                     
                $stats = showPollStatistic($p[0],$p[1],0,"");
                if ($stats) {
                unset($totalprocentByPoll);
                $onClickPutElement = "document.getElementById('show_stats_$m').style.display='none'; document.getElementById('hide_stats_$m').style.display='inline';";
                $onClickRemoveElement = "document.getElementById('show_stats_$m').style.display='inline'; document.getElementById('hide_stats_$m').style.display='none';";
                for($i=1;$i<=(count($stats)-3);$i++) {
                    $onClickPutElement .= "putElement('stats_{$m}_{$i}','table-row');";
                    $onClickRemoveElement .= "removeElem('stats_{$m}_{$i}');";
                }
                $tmp2 = "<tr><td colspan=3> ";
/*                <a href=\"javascript:void(0);\" id=\"show_stats_$m\" onCLick=\"{$onClickPutElement}\"><span class=webdna style='cursor: auto;'>4</span></a>
                <a style='display: block;' href=\"javascript:void(0);\" id=\"hide_stats_$m\" onCLick=\"{$onClickRemoveElement}\"><span class=webdna style='cursor: auto;'>6</span></a>
*/                
                $tmp2 .= "<b>{$stats['title']} {$stats['date_end']}</b></td><td>[POLL-RESULT]</td></tr>";
//                $tmp2 .= "<tr><td colspan=4>Результаты";
                $n=1;
                foreach($stats as $k=>$v) {
                    if (is_array($v)) {
//                    $tmp2 .= "<tr><td colspan=4>";
//                    $tmp2 .= "<div id='stats_$n' style='display: none;'>
//                              <table width=100% border=0 cellpadding=0 cellspacing=0><tr>";
                    $tmp2 .= "<tr id='stats_{$m}_{$n}'><td>{$v['title']}</td>";
                    $tmp2 .= "<td>";
                    
                    $n++;
                    
                    if (is_array($v['otvets']) && count($v['otvets'])) {
                        $tmp2 .= "<table cellspacing=0 cellpadding=0 border=0>";
                        foreach($v['otvets'] as $vvv) {
                     
                            $tmp2 .= "<tr><td style='padding: 0px; margin: 0px;'>{$vvv['text']}";
                            if ($v['type']!=6) {
                                $tmp2 .= ": &nbsp; </td>";
                                $tmp2 .= "<td style='padding: 0px; margin: 0px;'>".(int) $vvv['count']."</td>";
                            }
                            $tmp2 .= "</tr>";
                        
                        }
                        $tmp2 .= "</table>";
                    }
                    
                    $tmp2 .= "</td>";
                    $tmp2 .= "<td>";
                    if ($v['type']==1) {
                        $tmp2 .= "{$v['otvets'][round($v['totalbal']/$v['totalanswers'])]['text']}";
                    }
                    $tmp2 .= "</td>";
                    if (($v['type']==1) && $v['totalcount']) {
                        $procent = (int) (($v['totalbal'] * 100) / $v['totalcount']);
                        
                        if (isset($totalprocentByPoll)) $totalprocentByPoll = (int) (($totalprocentByPoll + $procent) / 2);
                        else $totalprocentByPoll = $procent;
                    }
                    $tmp2 .= "<td>";
                    if ($v['type']==1) {
                        $tmp2 .= makeProgressBar($procent,0, true);
                    }
                    $tmp2 .="</td>";
                    $tmp2 .= "</tr>";
                    
//                    $tmp2 .= "</table>";
//                    $tmp2 .= "</td></tr>";
                    }
                }
                
                if (isset($totalprocent)) $totalprocent = (int) (($totalprocent + $totalprocentByPoll) / 2);
                else $totalprocent = $totalprocentByPoll;
                
                $tmpByPoll = makeProgressBar($totalprocentByPoll,0, true);
                $tmp2 = str_replace('[POLL-RESULT]',$tmpByPoll,$tmp2);
                $tmp .= $tmp2;                
                }
           
           }
           $m++;
       }
       if ($last) {
           $tmp .= "<tr><td colspan=4>&nbsp;</td></tr>";
           $tmp .= "<tr><td colspan=3 align=right>"._("Итого:")."</td><td>".makeProgressBar($totalprocent,0, true)."</td></tr>";
       }
       $tmp .= "</table>";

       if ($last) {
           if (!$GLOBALS['controller']->enabled)
           $tmp .= "<br /><span style='font-size: 11px;'><a href='{$sitepath}positions.php?c=oldpolls&mid={$mid}'>"._("Архив результатов опросов")." >></a></span><br /><br />";
       }
   
   } 
   
   return $tmp;   
    
}

function copyStructure($soid,$owner='none') {
    if (!isset($GLOBALS['new_soids'])) $GLOBALS['new_soids'] = array();

    if (isset($GLOBALS['new_soids'][$soid]) && ($GLOBALS['new_soids'][$soid]>0)) return;
    
    $sql = "SELECT * FROM structure_of_organ WHERE soid='".(int) $soid."'";
    $res = sql($sql);
    
    if (sqlrows($res)) {
        $row = sqlget($res);
        $owner = $row['owner_soid'];
        if (isset($GLOBALS['new_soids'][$owner]) && ($GLOBALS['new_soids'][$owner]>0)) $owner = $GLOBALS['new_soids'][$owner];
        $sql = "INSERT INTO structure_of_organ (name,info,owner_soid,agreem,type,code)
                VALUES ('".$row['name']."','".$row['info']."','".$owner."','".$row['agreem']."','".(int) $row['type']."','".(int) $row['code']."')";
        $res2 = sql($sql);
        
        $id = sqllast();
        
        if ($id > 0) {
            $GLOBALS['new_soids'][$soid] = $id;
            copyCompetences($row['soid'],$id);

            $sql = "SELECT * FROM structure_of_organ WHERE owner_soid='".$row['soid']."'";
            $res3 = sql($sql);
        
            while($row3 = sqlget($res3))
                copyStructure($row3['soid'],$id);
        }
        
    }
}

/*
function copyStructure($soid, $owner=0) {
    
    $ret = false;
    
    $sql = "SELECT * FROM structure_of_organ WHERE soid='".(int) $soid."'";
    $res = sql($sql);
    
    if (sqlrows($res)) {
        
        $row = sqlget($res);
        
        if ($owner == 0) $owner = $row['owner_soid'];
        
        $sql = "INSERT INTO structure_of_organ (name,info,owner_soid,agreem,type) 
                VALUES ('".$row['name']."','".$row['info']."','".$owner."','".$row['agreem']."','".(int) $row['type']."')";
        $res2 = sql($sql);
        
        $id = sqllast();
        
        if ($id) {
                        
            $ret = true;

            copyCompetences($row['soid'],$id);
            
            $sql = "SELECT * FROM structure_of_organ WHERE owner_soid='".$row['soid']."'";
            $res3 = sql($sql);
        
            while($row3 = sqlget($res3)) {
            
                $ret = copyStructure($row3['soid'],$id);
            
            }
            
        }
        
    }
        
    return $ret;
}
*/

function copyCompetences($soid_from, $soid_to) {
 
    $sql = "SELECT * FROM str_of_organ2competence WHERE soid='".$soid_from."'";
    $res = sql($sql);
    
    while($row = sqlget($res)) {
        
        $sql = "INSERT INTO str_of_organ2competence (coid,soid,percent) 
                VALUES ('".$row['coid']."','".(int) $soid_to."','".$row['percent']."')";
        $res2 = sql($sql);
        
    }
    
}

/**
* Возвращает инфу по ближайшему начальнику
*/
function get_boss($soid) {
    $ret = false;
    
    if ($soid > 0) {
        $sql = "SELECT type,owner_soid FROM structure_of_organ WHERE soid='".(int) $soid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            if ($row['owner_soid']>0) {
                if ($row['type']==1) {
                    $ret = get_boss($row['owner_soid']);                
                } else {
                    $sql = "SELECT structure_of_organ.*, People.LastName, People.FirstName
                            FROM structure_of_organ LEFT JOIN People ON (structure_of_organ.mid=People.MID)
                            WHERE owner_soid='".(int) $row['owner_soid']."' AND type='1'";
                    $res = sql($sql);
                    if (sqlrows($res)) $ret = sqlget($res);
                    else {
                        $ret = get_boss($row['owner_soid']);
                    }
                }
            }
        }
    }
    return $ret;
}

function get_orgunit_boss($orgunit_soid) {
    $ret = false;
    if ($orgunit_soid > 0) {
        $sql = "SELECT structure_of_organ.*, People.LastName, People.FirstName  
                FROM structure_of_organ LEFT JOIN People ON (structure_of_organ.mid=People.MID)
                WHERE owner_soid='".(int) $orgunit_soid."' AND type='1'";
        $res = sql($sql);
        if (sqlrows($res)) $ret = sqlget($res);
    }
    return $ret;
}

/**
* Возвращает список подчинённых
*/
function get_slaves($soid) {
    $ret = false;
    if ($soid>0) {
        $sql = "SELECT type,owner_soid FROM structure_of_organ WHERE soid='".(int) $soid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            if (($row['type']==1) && ($row['owner_soid']>0)) {
                $sql = "SELECT structure_of_organ.*, People.LastName, People.FirstName, People.MID
                        FROM structure_of_organ LEFT JOIN People ON (structure_of_organ.mid=People.MID)
                        WHERE owner_soid='".(int) $row['owner_soid']."' AND soid<>'".(int) $soid."'";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if ($row['type']==2) $row = get_orgunit_boss($row['soid']);
                    if (is_array($row) && count($row))
                        $ret[] = $row;
                }
            }
        }
    }
    return $ret;
}

function get_info_by_soid($soid,$info='*') {
    $ret = false;
    if ($soid > 0) {
        $sql = "SELECT {$info} FROM structure_of_organ WHERE soid='".(int) $soid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            if ($info=='*') $ret = $row;
            else $ret = $row[$info];
        }
    }
    return $ret;
}

function get_all_courses_array() {
    $sql = "SELECT CID, Title FROM Courses WHERE Status>0";
    $res = sql($sql);
    while($row = sqlget($res)) $rows[$row['CID']] = $row['Title'];
    return $rows;
}

function get_courses_array_by_mid($mid) {
    $sql = "SELECT Courses.CID, Courses.Title 
            FROM Students 
            LEFT JOIN Courses ON (Courses.CID=Students.CID) 
            WHERE Students.MID='".(int) $mid."'";
    $res = sql($sql);
    while($row = sqlget($res)) $rows[$row['CID']] = $row['Title'];
    $sql = "SELECT Courses.CID, Courses.Title 
            FROM Claimants
            LEFT JOIN Courses ON (Courses.CID=Claimants.CID) 
            WHERE Claimants.MID='".(int) $mid."' AND Teacher='0'";
    $res = sql($sql);
    while($row = sqlget($res))
        if (!isset($rows[$row['CID']])) 
            $rows[$row['CID']] = $row['Title'];    
    
    return $rows;
}

function get_soid_by_mid($mid) {
    $sql = "SELECT soid FROM structure_of_organ WHERE mid='".(int) $mid."'";
    $res = sql($sql);
    if ($row = sqlget($res)) return $row['soid'];
    return false;
}

function is_head_orgunit_exists() {
    $sql = "SELECT soid FROM structure_of_organ WHERE owner_soid='0' AND type='2'";    
    $res = sql($sql);    
    if (sqlrows($res)) {
        $row=sqlget($res);
    }
    return $row['soid'];
}

function check_logic_of_structure($owner,$type,$action='add',$soid=0) {
    if ($owner==0) {
        if (is_head_orgunit_exists()!=$soid) return _("Корневой элемент уже присутствует");
        if ($type!=2) return _("Корневым элементом может быть только оргединица");
    } else {
        if (($action=='add') && $type==1) {
            $sql = "SELECT soid FROM structure_of_organ WHERE type='1' AND owner_soid='".(int) $_POST['owner_soid']."'";
            $res = sql($sql);
            if (sqlrows($res)) return _("У").' '.get_info_by_soid($owner,'name').' '._("уже присутствует руководящая должность");
        }
    }
    return "";
}

function get_soids_by_person($mid) {
    $sql = "SELECT soid FROM structure_of_organ WHERE mid='{$mid}'";
    $res = sql($sql);
    while ($row = sqlget($res)) $rows[] = $row['soid'];
    return $rows;
}

function get_positions_by_person($mid) {
    $sql = "SELECT name FROM structure_of_organ WHERE mid='{$mid}'";
    $res = sql($sql);
    while ($row = sqlget($res)) $rows[] = $row['name'];
    return $rows;
}

function get_own_orgunit_names_by_person($mid) {
    $sql = "SELECT owner_soid FROM structure_of_organ WHERE mid='{$mid}'";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if ($row['owner_soid']) {
            $rows[] = get_info_by_soid($row['owner_soid'],'name');
        }
    }
    return $rows;
}

function get_own_orgunit_codes_by_person($mid) {
    $sql = "SELECT owner_soid FROM structure_of_organ WHERE mid='{$mid}'";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if ($row['owner_soid']) {
            $rows[] = get_info_by_soid($row['owner_soid'],'code');
        }
    }
    return $rows;    
}

function is_position_curator_exists($soid) {
    if ($soid >0) {
        $sql = "SELECT DISTINCT mid FROM departments 
                INNER JOIN departments_soids ON (departments_soids.did = departments.did)
                WHERE departments_soids.soid = '".(int) $soid."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $ret[] = $row['mid'];
        }
        return $ret;
    }
}

function get_position_curators($soid) {
    static $curators = array();
    if ($soid > 0) {
        $sql = "SELECT owner_soid FROM structure_of_organ WHERE soid='".(int) $soid."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $curatorsArr = is_position_curator_exists($row['owner_soid']);
            if (is_array($curatorsArr) && count($curatorsArr)) {
                $curators = array_merge($curators, $curatorsArr);
            }
            $curators = array_merge($curators, get_position_curators($row['owner_soid']));
        }
    }
    $curators = array_unique($curators);
    return $curators;
}

function is_department_curator_exists($gid) {
    if ($gid) {
       $sql = "SELECT mid FROM departments 
               INNER JOIN departments_groups ON (departments_groups.did=departments.did)
               WHERE departments_groups.gid='".(int) $gid."'";
       $res = sql($sql);
       while($row = sqlget($res)) {
            $ret[] = $row['mid'];
       }
       return $ret;        
    }
}

function get_department_curators($mid) {
    $curators = array();
    if ($mid) {
        $sql = "SELECT gid FROM groupuser WHERE mid='".(int) $mid."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $curatorsArr = is_department_curator_exists($row['gid']);
            if (is_array($curatorsArr) && count($curatorsArr)) {
                $curators = array_merge($curators, $curatorsArr);
            }            
        }
        $curators = array_unique($curators);
    }
    return $curators;
}

function get_code_recursive($soid) {
    if ($soid > 0) {
        $sql = "SELECT owner_soid FROM structure_of_organ WHERE soid='".(int) $soid."'";
        $res = sql($sql);
        if (sqlrows($res) && $row=sqlget($res)) {
            if ($row['owner_soid']) {
                $sql = "SELECT code FROM structure_of_organ WHERE soid='".(int) $row['owner_soid']."'";
                $res = sql($sql);
                if (sqlrows($res) && $row2=sqlget($res)) {
                    if (!$row2['code']) $code = get_code_recursive($row['owner_soid']);
                    else $code = $row2['code'];
                }                
            }
        }
    }
    return $code;
}

function get_people_recursive_down($soid, $mids, $org_name_stack) {
    
    static $people;
    static $soids;
    
    if (!is_array($people)){
        $people = array();
        $res = sql("SELECT MID, LastName, FirstName, Patronymic FROM People");
        while ($row = sqlget($res)) $people[$row['MID']] = "{$row['LastName']} {$row['FirstName']} {$row['Patronymic']}";
    }
    
    if ($soid > 0) {
        $sql = "SELECT mid, type, name FROM structure_of_organ WHERE soid='".(int) $soid."'";
        $res = sql($sql);
        if ($row = sqlget($res)) {
            if ($row['type'] == TYPE_ITEM) {
                $org_name_stack[] = $row['name'];
                $sql = "SELECT soid FROM structure_of_organ WHERE owner_soid='".(int) $soid."'";
                $res = sql($sql);
                while ($row=sqlget($res)) {
                    $names = array_merge($names, get_people_recursive_down($row['soid'], $names, $org_name_stack));
                }
            } elseif (!empty($row['mid'])){
                array_push($org_name_stack, $people[$row['mid']]);
                $names = array("mid{$row['mid']}" => implode("&nbsp;&raquo;&nbsp;", $org_name_stack));
            }
        } 
        return $names;
    }
}

?>