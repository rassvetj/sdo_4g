<?php

class CChain {
    var $id;
    var $name;
    var $order;
    var $chain;

    function init($arrData) {
        if (is_array($arrData) && count($arrData)) {
            $this->id = (int) $arrData['id'];
            $this->name = isset($arrData['name']) ? trim(strip_tags($arrData['name'])) : 'Цепочка согласований';
            $this->order = (int) $arrData['order'];
            $this->chain = (is_array($arrData['chain']) && count($arrData['chain'])) ? $arrData['chain'] : array();
        }
    }

    function _process_chain() {
        if (is_array($this->chain) && count($this->chain)) {
            $chainItems = new CChainItems();
            $chainItems->init($this->chain);
            $chainItems->create($this->id);
        }
    }

    function create() {
        if (!$this->id) {
            $sql = "INSERT INTO chain (name,`order`) VALUES
                   ('{$this->name}','{$this->order}')";
            sql($sql);
            $this->id = sqllast();
            $this->_process_chain();
            return $this->id;
        }
    }

    function modify() {
        if ($this->id > 0) {
            $sql = "UPDATE chain
                    SET name='{$this->name}', `order`='{$this->order}'
                    WHERE id='{$this->id}'";
            sql($sql);
            $this->_process_chain();
            return $this->id;
        }
    }

    function delete($id) {
        if ($id) {
            $sql = "DELETE FROM chain_item WHERE chain='".(int) $id."'";
            sql($sql);
            $sql = "DELETE FROM chain WHERE id='".(int) $id."'";
            sql($sql);
        }
    }

    function get_as_array($id) {
        if ($id) {
            $sql = "SELECT id as id, name, `order` FROM chain WHERE id='".(int) $id."'";
            $res = sql($sql);
            if (sqlrows($res) && ($row=sqlget($res))) {
                $row['items'] = CChainItems::get_as_select_array($id);
                return $row;
            }
        }
    }

    function get_chain_by_cid($cid) {
        if ($cid) {
            $sql = "SELECT TypeDes, chain FROM Courses WHERE CID='".(int) $cid."'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) {
                if ($row['TypeDes']<0) $row['TypeDes'] = $row['chain'];
                return $row['TypeDes'];
            }
        }
    }

}

class CChainItem {
    var $id;
    var $chain;
    var $item;
    var $type;
    var $place;

    function init($arrData) {
        $this->id = $arrData['id'];
        $this->chain = $arrData['chain'];
        $this->item = $arrData['item'];
        $this->type = $arrData['type'];
        $this->place = $arrData['place'];
    }
}

/**
* types:
*   1 - position
*   2 - department
*   3 - boss
*   4 - curator
*   5 - teacher
*/
class CChainItems {

    var $items = array();

    function init($arrData) {
        if (is_array($arrData) && count($arrData)) {
            $i=0;
            foreach($arrData as $k=>$v) {
                $chainItem = new CChainItem();
                switch ($v) {
                    case 'boss':
                        $chainItem->init(array('item'=>0,'type'=>3,'place'=>(int) $i));
                        array_push($this->items,$chainItem);
                    break;
                    case 'curator':
                        $chainItem->init(array('item'=>0,'type'=>4,'place'=>(int) $i));
                        array_push($this->items,$chainItem);
                    break;
                    case 'teacher':
                        $chainItem->init(array('item'=>0,'type'=>5,'place'=>(int) $i));
                        array_push($this->items,$chainItem);
                    break;
                    default:
                        if (strstr($v,'p:')!==false) {
                            $chainItem->init(array('item'=>(int) substr($v,2),'type'=>1,'place'=>(int) $i));
                            array_push($this->items,$chainItem);
                        }
                        if (strstr($v,'d:')!==false) {
                            $chainItem->init(array('item'=>(int) substr($v,2),'type'=>2,'place'=>(int) $i));
                            array_push($this->items,$chainItem);
                        }
                }
                $i++;
                unset($chainItem);
            }
        }
    }

    function create($chain) {
        $sql = "DELETE FROM chain_item WHERE chain='".(int) $chain."'";
        sql($sql);
        if ($chain && is_array($this->items) && count($this->items)) {
            while(list($k,$v) = each($this->items)) {
                if (is_a($v,'CChainItem')) {
                    $sql = "INSERT INTO chain_item (chain,item,type,place) VALUES
                            ('".(int) $chain."','{$v->item}','{$v->type}','{$v->place}')";
                    sql($sql);
                }
            }
        }
    }

    function get_as_array($chain) {
        if ($chain) {
            $sql = "SELECT id as id, chain, item, type, place FROM chain_item WHERE chain='".(int) $chain."' ORDER BY place";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $rows[] = $row;
            }
            return $rows;
        }
    }

    /**
    * @return mixed (int or array of ints)
    */
    function get_subject($cid,$mid,$item) {
        if ($cid && is_array($item) && count($item)) {
            switch($item['type']) {
                case 1:
                    $sql = "SELECT mid FROM structure_of_organ WHERE soid='".(int) $item['item']."'";
                    $res = sql($sql);
                    if (sqlrows($res) && $row=sqlget($res)) {
                        return $row['mid'];
                    }
                break;
                case 2:
                    $sql = "SELECT mid FROM departments WHERE did='".(int) $item['item']."'";
                    $res = sql($sql);
                    if (sqlrows($res) && $row=sqlget($res)) {
                        return $row['mid'];
                    }
                break;
                case 3://больше не используется
                    require_once("{$sitepath}positions.lib.php");
                    $soids = get_soids_by_person($mid);
                    if (is_array($soids) && count($soids))
                    foreach($soids as $soid) {
                        $boss = get_boss($soid);
                        $ret[] = $boss['mid'];
                    }
                    return $ret;
                break;
                case 4:
                    /*
                    require_once("positions.lib.php");
                    if (defined('APPLICATION_BRANCH') && (APPLICATION_BRANCH==APPLICATION_BRANCH_ACADEMIC)) {
                        $ret = get_department_curators($mid);
                        return $ret;
                    }
                    else {
                        $ret = array();
                        $soids = get_soids_by_person($mid);
                        if (is_array($soids) && count($soids))
                        foreach($soids as $soid) {
                            $curators = get_position_curators($soid);
                            $ret = array_merge($ret,$curators);
                        }
                        return $ret;
                    }

                    return 'куратор';
                    */
                    $res = sql("SELECT DISTINCT d.mid
                                FROM departments d
                                LEFT JOIN departments_courses dc ON (d.did = dc.did)
                                WHERE dc.cid = '$cid' AND d.mid <> 0");
                    if (sqlrows($res) && $row=sqlget($res)) {
                        return $row['mid'];
                    }
                break;
                case 5:
                    $sql = "SELECT MID FROM Teachers WHERE CID='".(int) $cid."'";
                    $res = sql($sql);
                    if (sqlrows($res) && $row=sqlget($res)) {
                        return $row['MID'];
                    }
                break;
            }
        }
    }

    function get_type_name($type,$item) {
        switch($type) {
            case 1:
                $ret = CChainItems::_get_position_name($item);
            break;
            case 2:
                $ret = CChainItems::_get_department_name($item);
            break;
            case 3:
                $ret = _('начальник');
            break;
            case 4:
                $ret = _('куратор');
            break;
            case 5:
                $ret = _('преподаватель');
            break;
        }
        return $ret;
    }

    function get_as_select_array($chain) {
        if ($chain) {
            $items = CChainItems::get_as_array($chain);
            if (is_array($items) && count($items)) {
                while(list(,$v) = each($items)) {
                    switch($v['type']) {
                        case 1:
                            $rows['p:'.$v['item']] = CChainItems::_get_position_name($v['item']);
                        break;
                        case 2:
                            $rows['d:'.$v['item']] = CChainItems::_get_department_name($v['item']);
                        break;
                        case 3:
                            $rows['boss'] = _('начальник');
                        break;
                        case 4:
                            $rows['curator'] = _('куратор');
                        break;
                        case 5:
                            $rows['teacher'] = _('преподаватель');
                        break;
                    }
                }
            }
            return $rows;
        }
    }

    function _get_position_name($soid) {
        if ($soid) {
            $sql = "SELECT name FROM structure_of_organ WHERE soid='".(int) $soid."'";
            $res = sql($sql);
            if (sqlrows($res) &&( $row = sqlget($res))) return $row['name'];
        }
        return _("неизвестно");
    }

    function _get_department_name($did) {
        if ($did) {
            $sql = "SELECT name FROM departments WHERE did='".(int) $did."'";
            $res = sql($sql);
            if (sqlrows($res) &&( $row = sqlget($res))) return $row['name'];
        }
        return _("неизвестно");
    }

    function get_all_positions() {
/*        $sql = "SELECT t1.soid, t1.name, t2.LastName, t2.FirstName, t2.Login
                FROM structure_of_organ AS t1
                LEFT JOIN People AS t2 ON (t2.MID=t1.mid)
                WHERE type=1
                ORDER BY name";
*/
        $sql = "SELECT soid, name
                FROM structure_of_organ
                WHERE type=1
                ORDER BY name";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $rows[] = $row;
        }
        return $rows;
    }

    function get_all_departments() {
        $sql = "SELECT did, name
                FROM departments
                WHERE application = '".DEPARTMENT_APPLICATION."'
                ORDER BY name";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $rows[] = $row;
        }
        return $rows;
    }

    function get_all_others() {

        if (defined('APPLICATION_BRANCH') && (APPLICATION_BRANCH==APPLICATION_BRANCH_ACADEMIC)) {
            $others = array(
                array('name'=>_('куратор'),'value'=>'curator'),
                array('name'=>_('преподаватель'),'value'=>'teacher'),
                );
        } else {
            $others = array(
                //array('name'=>_('начальник'),'value'=>'boss'),
                array('name'=>_('куратор'),'value'=>'curator'),
                array('name'=>_('преподаватель'),'value'=>'teacher'),
                );
        }

        return $others;
    }

}

class CChainsList {

    function get_as_array() {
        $sql = "SELECT id as id, name, `order` FROM chain ORDER BY name";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $row['chain'] = CChainItems::get_as_select_array($row['id']);
//            $rows[$row['place'] = $row;
            $rows[] = $row;
        }
        return $rows;
    }

    function get_as_options($selected,$free=true,$appoint=true) {
        $list = CChainsList::get_as_array();

        if ($free) {
            $ret.="<option value=\"0\"";
            if (!$selected) $ret.=" selected ";
            $ret.=">---"._("без согласования")."---</option>";
        }

        // Назначаемый курс
        //$ret .= ($appoint) ? "<option value=\"-1\"" . (($selected == -1) ? " selected" : "") . ">Назначаемый курс</option>" : "";

        if (is_array($list) && count($list)) {
            while(list(,$v) = each($list)) {
                $ret.="<option value=\"{$v['id']}\"";
                if ($v['id']==$selected) $ret.=" selected ";
                $ret.="> "._("цепочка согласований").": {$v['name']}</option>";
            }
        }
        return $ret;
    }

}

class CChainFilter {

    var $mid;
    var $cid;
    var $place = 'unknown';

    var $chain_by_course = array();
    var $chains = array();
    var $my_chain = array();
    var $filtered = array();

    function init($cid, $mid) {
        $this->mid = $mid;
        $this->cid = $cid;

        $this->_get_chains();
    }

    function _get_chains_by_courses() {
        $sql = "SELECT DISTINCT Courses.CID, Courses.TypeDes, Courses.chain
                FROM Courses
                INNER JOIN claimants ON (claimants.CID=Courses.CID)
                ORDER BY Courses.CID";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if ($row['TypeDes']<0) $row['TypeDes'] = $row['chain'];
            $this->chain_by_course[$row['CID']] = $row['TypeDes'];
        }
    }

    function _put_item_to_my_chain($cid,$item) {
        $this->my_chain[$cid][$item['place']] = $item;
    }

    function _is_teacher($cid, $mid) {
        $sql = "SELECT * FROM Teachers WHERE CID='".(int) $cid."' AND MID='".(int) $mid."'";
        $res = sql($sql);
        return sqlrows($res);
    }

    function _is_curator($mid) {
        $sql = "SELECT * FROM departments WHERE mid='".(int) $mid."' AND application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        return sqlrows($res);
    }

    function _is_boss($mid) {
        $sql = "SELECT * FROM structure_of_organ WHERE mid='".(int) $mid."' AND type=1";
        $res = sql($sql);
        return sqlrows($res);
    }

    function _is_department($mid,$did) {
        $sql = "SELECT * FROM departments WHERE mid='".(int) $mid."' AND did='".(int) $did."'";
        $res = sql($sql);
        return sqlrows($res);
    }

    function _is_position($mid,$soid) {
        $sql = "SELECT * FROM structure_of_organ WHERE mid='".(int) $mid."' AND soid='".(int) $soid."' AND type=1";
        $res = sql($sql);
        return sqlrows($res);
    }

/*
    function _teacher_process_log($cid,$item) {
        if (is_array($item) && count($item)) {
            $sql = "SELECT mid FROM chain_agreement WHERE cid='".(int) $cid."' AND place='".(int) ($item['place']-1)."'";
            $res = sql($sql);
            while($row = sqlget($res)) $this->filtered[$cid][] = $row['mid'];
        }
    }

    function _teacher_process_all_wo_log($cid) {
        $sql = "SELECT DISTINCT MID
                FROM claimants LEFT JOIN chain_agreement ON (chain_agreement.mid=claimants.MID)
                WHERE CID='".(int) $cid."' AND chain.agreement.mid IS NULL";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$cid][] = $row['MID'];
    }
*/
    function _teacher_process_all($cid,$place) {
        $sql = "SELECT DISTINCT MID FROM claimants WHERE CID='".(int) $cid."'";
        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$cid][$place][] = $row['MID'];
    }

    function _process_chain_item_teacher($cid,$item) {
        $this->_put_item_to_my_chain($cid,$item);
        $this->_teacher_process_all($cid,$item['place']);

/*        if (!$item['order']) {
            $this->_teacher_process_all($cid);
        } else {
            if ($item['place']==0) {
                $this->_teacher_process_all_wo_log($cid);
            } else {
                $this->_teacher_process_log($cid,$item);
            }
        }
*/
    }

    function _get_slaves_by_soid($cid,$soid,$not_mid,$place,$table,$recurse=true) {
        if ($soid>0) {
            $sql = "SELECT {$table}.CID, structure_of_organ.soid as soid, structure_of_organ.type as type, structure_of_organ.mid as mid
                    FROM structure_of_organ
                    LEFT JOIN {$table} ON ({$table}.MID=structure_of_organ.mid)
                    WHERE structure_of_organ.owner_soid='".(int) $soid."' AND
                    structure_of_organ.mid NOT IN ('".$not_mid."')";
            $res = sql($sql);
            while($row=sqlget($res)) {
                if ($recurse && ($row['type']==2)) $this->_get_slaves_by_soid($cid,$row['soid'],$not_mid,$place,$table);
                if (($row['mid']>0) && ($row['CID']==$cid)) $this->filtered[$cid][$place][] = $row['mid'];
            }
        }
    }

    function _get_subordinates_corporate($cid,$mid,$place,$table) {
        $sql = "SELECT DISTINCT departments_soids.soid as soid
                FROM departments_soids
                INNER JOIN departments ON (departments.did=departments_soids.did)
                WHERE departments.mid='".(int) $mid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if ($row['soid']>0) $this->_get_slaves_by_soid($cid,$row['soid'],$mid,$place,$table);
        }
    }

    function _get_subordinates_academic($cid,$mid,$place,$table) {
        //выберем людей из куррируемых групп
        $sql = "SELECT DISTINCT {$table}.MID
                FROM {$table}
                INNER JOIN groupuser ON (groupuser.mid={$table}.MID)
                INNER JOIN departments_groups ON (departments_groups.gid=groupuser.gid)
                INNER JOIN departments ON (departments.did=departments_groups.did)
                WHERE departments.mid='".(int) $mid."' AND {$table}.CID='".(int) $cid."' AND departments.application = '".DEPARTMENT_APPLICATION."'";

        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$cid][$place][] = $row['MID'];
        //выберем людей из на куррируемых курсов
        $sql = "SELECT DISTINCT {$table}.MID
                FROM departments
                LEFT JOIN departments_courses ON (departments_courses.did = departments.did)
                LEFT JOIN {$table} ON ({$table}.CID = departments_courses.cid)
                WHERE
                    departments.mid = '".(int) $mid."' AND
                    {$table}.CID = '".(int) $cid."' AND
                    departments.application = '".DEPARTMENT_APPLICATION."'";

        $res = sql($sql);
        while($row = sqlget($res)) $this->filtered[$cid][$place][] = $row['MID'];
    }

    function _process_chain_item_curator($cid,$mid,$item) {
        $this->_put_item_to_my_chain($cid,$item);

        //if (defined('APPLICATION_BRANCH') && (APPLICATION_BRANCH==APPLICATION_BRANCH_ACADEMIC)) {
            $this->_get_subordinates_academic($cid,$mid,$item['place'],'claimants');
        //} else {
        //    $this->_get_subordinates_corporate($cid,$mid,$item['place'],'claimants');
        //}
    }

    function _process_chain_item_boss($cid,$mid,$item) {
        $this->_put_item_to_my_chain($cid,$item);
        $sql = "SELECT owner_soid as owner_soid FROM structure_of_organ WHERE mid='".(int) $mid."' AND type='1'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if ($row['owner_soid']>0) {
                $this->_get_slaves_by_soid($cid,$row['owner_soid'],$mid,$item['place'],'claimants',false);
            }
        }
    }

    function _process_chain_item_department($cid,$mid,$item) {
        $this-> _process_chain_item_teacher($cid,$item); // берём всех с курса
    }

    function _process_chain_item_position($cid,$mid,$item) {
        $this-> _process_chain_item_teacher($cid,$item); // берём всех с курса
    }

    function _process_chain_item($cid,$item) {
        if (($cid>0) && is_array($item) && count($item)) {
            switch($item['type']) {
                case 1: // биз структура
                    if ($this->_is_position($this->mid,$item['item'])) {
                        $this->_process_chain_item_position($cid,$this->mid,$item);
                    }
                break;
                case 2: // уч структура
                    if ($this->_is_department($this->mid,$item['item'])) {
                        $this->_process_chain_item_department($cid,$this->mid,$item);
                    }
                break;
                case 3: // босс
                    if ($this->_is_boss($this->mid)) {
                        $this->_process_chain_item_boss($cid,$this->mid,$item);
                    }
                break;
                case 4: // куратор
                    if ($this->_is_curator($this->mid)) {
                        $this->_process_chain_item_curator($cid,$this->mid,$item);
                    }
                break;
                case 5: // препод
                    if ($this->_is_teacher($cid,$this->mid)) {
                        $this->_process_chain_item_teacher($cid,$item);
                    }
                break;
            }
            if (is_array($this->filtered[$cid][$item['place']]) && count($this->filtered[$cid][$item['place']]))
                $this->filtered[$cid][$item['place']] = array_unique($this->filtered[$cid][$item['place']]);
        }
    }

    function _get_chains() {
        $this->_get_chains_by_courses();
        if ($this->cid > 0) $arrChains[$this->cid] = $this->chain_by_course[$this->cid];
        else $arrChains = $this->chain_by_course;
        if (is_array($arrChains) && count($arrChains)) {
            foreach($arrChains as $k=>$v) {
                $sql = "SELECT chain.`order`, chain_item.id as id, chain_item.chain, chain_item.item, chain_item.type, chain_item.place
                        FROM chain LEFT JOIN chain_item ON (chain_item.chain=chain.id)
                        WHERE chain.id='".(int) $v."' ORDER BY chain_item.place";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    //if (!count($this->my_chain))
                    $this->_process_chain_item($k,$row);
                    $this->chains[$k][$row['place']] = $row;
                }

            }
        }
    }

    function _is_success($cid,$mid,$object) {
        $sql = "SELECT id as id, cid, mid, subject, object, place, comment, ".$GLOBALS['adodb']->SQLDate("Y-m-d H:i:s", "date")." as date 
        		FROM chain_agreement
                WHERE cid='".(int) $cid."' AND
                      mid='".(int) $mid."' AND object='".(int) $object."'";
        $res = sql($sql);
        return sqlrows($res);
    }

    function _is_my_turn($cid,$mid,$place) {
        $sql = "SELECT id as id, cid, mid, subject, object, place, comment, ".$GLOBALS['adodb']->SQLDate("Y-m-d H:i:s", "date")." as date 
        		FROM chain_agreement
                WHERE cid='".(int) $cid."' AND mid='".(int) $mid."'
                ORDER BY place DESC LIMIT 1";
        $res = sql($sql);
        if (!sqlrows($res)) {
            if ($place == 0) return true;
        } else {
            $row = sqlget($res);
            if (($row['place']+1)==$place) return true;
        }
    }

    function is_filtered($cid,$mid) {
        if (is_array($this->my_chain[$cid]) && count($this->my_chain[$cid])) {
            foreach($this->my_chain[$cid] as $k=>$v) {
                if (in_array($mid,$this->filtered[$cid][$k])) {
                    // проверка на порядок согласования и ваще...
                    if (isset($this->chains[$cid][0]['order'])) {
                        if ($this->chains[$cid][0]['order']==0) {
                            // беспорядок
                            if (!$this->_is_success($cid,$mid,$v['type'])) {
                                $this->place = (int) $k;
                                return true;
                            }
                        } else {
                            // порядок
                            if ($this->_is_my_turn($cid,$mid,$v['place'])) {
                                $this->place = (int) $k;
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    function _accept_log($cid, $mid, $comment) {
        return
            CChainLog::add($cid,$mid,$this->mid,$this->my_chain[$cid][$this->place]['type'],$this->place,$comment);
    }

    function _is_last($cid,$mid) {
        $sql = "SELECT id as id, cid, mid, subject, object, place, comment, ".$GLOBALS['adodb']->SQLDate("Y-m-d H:i:s", "date")." as date 
        		FROM chain_agreement 
        		WHERE mid='".(int) $mid."' AND cid='".(int) $cid."'
                ";
        $res = sql($sql);
        return (sqlrows($res)==count($this->chains[$cid]));
    }

    /**
    * Согласование с проверками
    */
    function accept($cid,$mid,$comment) {
        // проверка
        if ($this->is_filtered($cid,$mid)) {
            // запись в лог
            if ($this->_accept_log($cid,$mid,$comment)) {
                // последний?
                if ($this->_is_last($cid,$mid)) {
                    // тост, господа! =)
                    CChainLog::erase($cid,$mid);
                    tost($mid,$cid);
                } else {
                    CChainLog::email($cid,$mid,($this->place + 1));
                }
            }
        }
    }

    /**
    * Принудительное согласование без проверок
    */
    function accept_now($cid,$mid) {
        CChainLog::erase($cid,$mid);
        tost($mid,$cid);
    }

    /**
    * Отклонить
    */
    function deny($cid,$mid) {
        CChainLog::erase($cid, $mid);

        $sql = "DELETE FROM claimants WHERE MID='".(int) $mid."' AND CID='".(int) $cid."' AND Teacher='0'";
        sql($sql);

        mailToStud('del',$mid,$cid,'');
    }

}

class CChainLog {
    function get_as_array($cid,$mid) {
        $sql = "SELECT id as id, cid, mid, subject, object, place, comment, ".$GLOBALS['adodb']->SQLDate("Y-m-d H:i:s", "date")." as date 
        		FROM chain_agreement
                WHERE cid='".(int) $cid."' AND
                      mid='".(int) $mid."'
                ORDER BY place";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $rows[$row['place']] = $row;
        }
        return $rows;
    }

    function add($cid,$mid,$subject,$object,$place,$comment='') {
        $sql = "INSERT INTO chain_agreement (cid,mid,subject,object,place,comment,date) VALUES
                ('".(int) $cid."','".(int) $mid."','".(int) $subject."',
                '".(int) $object."','".(int) $place."',".$GLOBALS['adodb']->Quote($comment).",".$GLOBALS['adodb']->DBTimestamp(time()).")";
        sql($sql);
        return sqllast();
    }

    function eraseCourses($cids,$mid) {
        if (is_array($cids) && count($cids) > 0) {
            sql("DELETE FROM chain_agreement WHERE cid IN ('".join("','",$cids)."') AND mid='".(int) $mid."'");
        }
    }

    function erase($cid,$mid) {
        $sql = "DELETE FROM chain_agreement WHERE cid='".(int) $cid."' AND mid='".(int) $mid."'";
        $res = sql($sql);
    }

    function email($cid, $mid, $step=0, $type='about_reg_student') {
        if ($cid && $mid) {

            if ($chain = CChain::get_chain_by_cid($cid)) {
                $order = getField('chain',"`order`",'id',$chain);
                if (!$order && $step) return;
                $chain_items = CChainItems::get_as_array($chain);
                if (is_array($chain_items) && count($chain_items)) {
                    foreach($chain_items as $k=>$v) {
                        if ($order && ($v['place'] != $step)) continue;
                        $mids[] = CChainItems::get_subject($cid, $mid, $v);
                    }
                }
            }

            if (is_array($mids) && count($mids)) {
                $mids = array_unique($mids);
                foreach($mids as $v) {
                    mailToelearn($type, $mid, $cid, $more, $v);
                }
            }



        }
    }

}
?>