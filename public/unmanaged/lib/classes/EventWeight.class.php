<?php
class CEventWeight {
    
    function get_weight($event,$cid) {
        $sql = "SELECT * FROM eventtools_weight WHERE event='".(int) $event."' AND cid='".(int) $cid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            return $row['weight'];
        } 
    }

    function get_weights($cid) {
        $sql = "SELECT * FROM eventtools_weight WHERE cid='".(int) $cid."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $rows[$row['cid']][$row['event']] = $row['weight'];
        } 
        return $rows;
    }
    
    function is_exists($event,$cid) {
        $sql = "SELECT * FROM eventtools_weight WHERE event='".(int) $event."' AND cid='".(int) $cid."'";
        $res = sql($sql);
        return sqlrows($res);
    }
    
    function get_base_weight($event) {
        $sql = "SELECT * FROM EventTools WHERE TypeID='".(int) $event."'";
        $res = sql($sql);
        if (sqlrows($res) && ($row = sqlget($res))) return $row['weight'];
    }
    
    function add($arr) {
        if ($arr['id']==0) {
            if (!CWeight::is_exists($arr['event'],$arr['cid'])) {
                $arr['weight'] = CEventWeight::get_base_weight($arr['event']);
                $sql = "INSERT INTO eventtools_weight (event,cid,weight) VALUES ('".(int) $arr['event']."','".(int) $arr['cid']."','".(double) $arr['weight']."')";
                sql($sql);
                return sqllast();
            }
        }
    }
    
    function replace($arr) {
        if (!empty($arr['ch_disabled'])) {
    		$arr['weight'] = -1;
    	}
        
    	$sql = "SELECT cid, weight FROM eventtools_weight WHERE cid='{$arr['cid']}' AND event='{$arr['id']}'";
    	$res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            if (($row['weight']==-1) && empty($arr['ch_disabled'])) {
                $arr['weight'] = 0;
                
                $sql = "
                UPDATE eventtools_weight 
                SET 
                    weight='".(double) $arr['weight']."'
                WHERE
                    event='".(int) $arr['id']."' AND
                    cid='".(int) $arr['cid']."'
                ";
                sql($sql);
                            
                //                CEventWeight::delete($arr['id']);
//                return;
            }
            
            if (!empty($arr['ch_disabled'])) {
                $sql = "
                UPDATE eventtools_weight 
                SET 
                    weight='".(double) $arr['weight']."'
                WHERE
                    event='".(int) $arr['id']."' AND
                    cid='".(int) $arr['cid']."'
                ";
                sql($sql);            
            }
            
        } else {
            if ($arr['weight'] != -1) $arr['weight'] = CEventWeight::get_base_weight($arr['id']);            
            $sql = "INSERT INTO eventtools_weight 
            		(event,cid,weight) VALUES
 					('".(int) $arr['id']."','".(int) $arr['cid']."','".(double) $arr['weight']."')";
            sql($sql);            
        }
        return $arr['id'];
    }
        
    function update($cid,$event,$weight) {
        if ($cid && $event) {            
            $sql = "
                UPDATE eventtools_weight 
                SET 
                    weight='".(double) $weight."'
                WHERE
                    event='".(int) $event."' AND
                    cid='".(int) $cid."'
                ";
            sql($sql);        
        }
    }
    
    function delete($id) {
        $sql = "DELETE FROM eventtools_weight WHERE event=".(int) $id." AND cid='{$_REQUEST['cid']}'";
        sql($sql);
    }
    
    function get_events() {
        $sql = "SELECT TypeID as id, TypeName as name, tools FROM EventTools ORDER BY TypeName";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    function get_courses($arrCourses) {
        $sql = "SELECT CID as cid, Title as title FROM Courses WHERE CID IN ('".join("','",$arrCourses)."') ORDER BY Title";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $rows[] = $row;
        }
        return $rows;        
    }
    
    function get_courses_assoc($arrCourses) {
        $sql = "SELECT CID as cid, Title as title FROM Courses WHERE CID IN ('".join("','",$arrCourses)."') ORDER BY Title";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $rows[$row['cid']] = $row['title'];
        }
        return $rows;        
    }
    
    function get_shedules_weights($cid, $mid) {
    	$return = array();
    	$query = "
			SELECT 
			  schedule.typeID as type_id,
			  scheduleID.V_STATUS,
			  EventTools.weight as weight_base,
			  eventtools_weight.weight as weight_cid
			FROM
			  scheduleID
			  INNER JOIN schedule ON (scheduleID.SHEID = schedule.SHEID)
			  INNER JOIN EventTools ON (schedule.typeID = EventTools.TypeID)
			  LEFT OUTER JOIN eventtools_weight ON (EventTools.TypeID = eventtools_weight.event) 
              AND (eventtools_weight.cid = '{$cid}')
			WHERE
			  schedule.CID='{$cid}' AND
			  scheduleID.MID='{$mid}' AND
			  scheduleID.V_STATUS > -1 AND
              schedule.vedomost = 1    	
    	";
        $res = sql($query);
        while($row=sqlget($res)) {
        	$return[] = $row;
        }
    	return $return;
    }
    
    function get_as_array($cid) {
        $colors = array('red','green','blue','yellow','black','gray','salmon','#AABBCC','#BBCCAA');
        $sql = "SELECT eventtools_weight.weight as weight_cid, eventtools_weight.cid as cid,EventTools.TypeName AS name, 
                       EventTools.Icon AS icon, EventTools.weight AS weight_base, EventTools.TypeID AS id, 
                       EventTools.tools AS tools 
                FROM EventTools
				LEFT OUTER JOIN eventtools_weight ON (EventTools.TypeID = eventtools_weight.event) 
                AND (eventtools_weight.cid = '{$cid}')                
                ORDER BY EventTools.TypeName";
        $res = sql($sql);
        while($row=sqlget($res)) {
        	switch ($row['weight_cid']) {
        		case -1:
        			$row['weight'] = -1;
        			break;
        		case null:
        			//$row['weight'] = 0;
        			$row['weight'] = $row['weight_base'];
        			break;
        		default:
        			$row['weight'] = $row['weight_cid'];
        			break;
        	}
            $row['color'] = array_shift($colors);
            $row['course'] = cid2title($row['cid']);
            $rows[] = $row;
        }
        return $rows;        
    }
    
    function get($id, $cid) {
        $sql = "
	        SELECT event, eventtools_weight.weight as weight_cid, EventTools.weight as weight_base, EventTools.TypeName as name, EventTools.TypeID as id
                  FROM EventTools
				  LEFT OUTER JOIN eventtools_weight ON (EventTools.TypeID = eventtools_weight.event) AND (eventtools_weight.cid = '{$cid}')                
	        WHERE EventTools.TypeID='".(int) $id."'";
        $res = sql($sql);
        if ($row = sqlget($res)){
        	switch ($row['weight_cid']) {
        		case -1:
        			$row['disabled'] = true;
        			$row['weight'] = -1;
        			//$row['weight'] = $row['weight_base'];
        			break;
        		case null:
        			$row['disabled'] = false;
        			$row['weight'] = $row['weight_base'];
        			break;
        		default:
        			$row['disabled'] = false;        			
        			$row['weight'] = $row['weight_cid'];
        			break;
        	}
        	return $row;
        }
    }
    
    /**
    * 
    * @return bool
    * @param array $events
    */
    function check_sum($events,$cid) {
     
            $sum = 0; $count=0;
            foreach($events as $event) {
                if ($event['weight_cid']>0) {
                    $sum += $event['weight_cid'];
                    $count++;
                }
            }
            if ($count && $sum!=100) {
                if ($count) $w = floor(100/$count);
                $wplus = (100-($w*$count));
                $i=1;
                foreach($events as $event) {
                    if ($event['weight_cid']>0) {
                        $weight = $w;
                        if ($i==1) $weight = $w+$wplus;
                        //CEventWeight::replace(array('cid'=>,'id'=>,'weight'=>));
                        CEventWeight::update($cid,$event['id'],$weight);
                        $i++;
                    }
                }
                return false;
            }
            return true;
    }
    
}

class CEventWeightXMLParser {
    var $strXML;
    var $events;

    function init_string($str) {
        $this->strXML = $str;
    }
        
    function _parse_array($blocks) {        
        static $id;
        static $items = array();
        
        if (count($blocks)>0) {
            foreach($blocks as $block) {
                switch($block['name']) {
                    case 'PIE':
                        $this->_parse_array($block['children']);
                    break;
                    case 'PIECE':
                        $items[$block['attrs']['ID']] = $block['attrs']['VALUE'];
                    break;
                }
            }
        }
        
        return $items;
    }
        
    function parse() {
        if (!empty($this->strXML)) {
            $objXML = new xml2Array();
            $arrXML= $objXML->parse($this->strXML);
            if (is_array($arrXML) && count($arrXML)) {
                $this->events = $this->_parse_array($arrXML);
            }
        }
    }
    
    function update_events($cid) {
        if (is_array($this->events) && count($this->events)) {
            while(list($k,$v) = each($this->events)) {
                $sql = "SELECT id FROM eventtools_weight
                        WHERE event='".(int) $k."' AND cid='".(int) $cid."'";
                $res = sql($sql);
                if (sqlrows($res)) {
                    $sql = "UPDATE eventtools_weight
                            SET weight='".$v."'
                            WHERE event='".(int) $k."' AND cid='".(int) $cid."'";                                            
                } else {
                    $sql = "INSERT INTO eventtools_weight (event,cid,weight)
                            VALUES ('".(int) $k."','".(int) $cid."','".$v."')";
                }
                sql($sql);
            }
        }
    }

}
?>