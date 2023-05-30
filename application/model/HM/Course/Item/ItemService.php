<?php
class HM_Course_Item_ItemService extends HM_Service_Abstract
{
    public function getCourse($itemId)
    {
        $return = false;
        $item = $this->getOne($this->find($itemId));
        if ($item) {
            $return = $item->cid;
        }
        return $return;
    }
    
    public function getParent($itemId)
    {
        $return = false;
        $item = $this->getOne($this->find($itemId));
        if ($item) {

            $level = $item->level;

            $collection = $this->fetchAll(
                $this->quoteInto(array('cid = ?', ' AND level >= ?'), array($item->cid, $item->level-1))
            );

            $items = array();
            if (count($collection)) {
                foreach($collection as $item) {
                    $items[$item->oid] = $item;
                }
            }
            if (isset($items[$itemId])) {
                $i = $items[$itemId];
                while($i->level >= $level) {
                    if (!isset($items[$i->prev_ref])) break;
                    $i = $items[$i->prev_ref];
                }
            }

            if ($i->oid != $itemId) {
                $return  = $i;
            }

        }
        return $return;
    }

    public function getChildrenLevel($courseId, $parent = -1, $oneLevel = true, $includeCurrentLevel = false)
    {
        $content = new HM_Collection(array());
        $content->setModelClass('HM_Course_Item_ItemModel');

        if ($courseId) {
            $items = array(); $order = array();

            $level = 0;
            if ($parent > 0) {
                $item = $this->getOne($this->find($parent));
                if ($includeCurrentLevel) $content[count($content)] = $item->getValues(); 
                if ($item) {
                    $level = $item->level + 1; // Вломак разбираться зачем + 1 - просто портировал низ из unmanaged
                }
            }

            $minLevel = $level - 1;
            if ($minLevel < 0) $minLevel = 0;
            if ($parent <= 0) $parent = -1;

            $collection = $this->fetchAll(
                $this->quoteInto(array('cid = ?', ' AND level >= ?'), array($courseId, $minLevel))
            );

            if (count($collection)) {
                //упорядочиваем структуру
                $tempItems = array();
                foreach($collection as $item) {
                    $tempItems[$item->prev_ref] = $item;
                }

                $collection = array();
                $collection[] = $tempItems[$parent];    //'-1']; - #17714
                while (count($collection) != count($tempItems)) {
                    $newItem = $tempItems[end($collection)->oid];
                    if (empty($newItem)) break;
                    $collection[] = $newItem;
                }

                foreach($collection as $item) {
                    $items[$item->oid] = $item;
                    $order[$item->prev_ref]['oid'] = $item->oid;
                    //$order[$item->prev_ref]['level'] = $item->level;
                }

                if($parent == -1){
                    foreach($items as $item){
                        if ($item->level < $level) break;
                        if ($oneLevel) {
                            if ($item->level == $level) {
                                $content[count($content)] = $item->getValues();
                            }
                        } else {
                            if ($item->level >= $level) {
                                $content[count($content)] = $item->getValues();
                            }
                        }
                    }
                }else{
                    while(true) {
                        if (!isset($order[$parent])) break;
                        if (!isset($items[$order[$parent]['oid']])) break;

                        $item = $items[$order[$parent]['oid']];

                        if ($item->level < $level) break;
                        if ($oneLevel) {
                            if ($item->level == $level) {
                                $content[count($content)] = $item->getValues();
                            }
                        } else {
                            if ($item->level >= $level) {
                                $content[count($content)] = $item->getValues();
                            }
                        }

                        $parent = $item->oid;
                    }
                }

            }

        }
        return $content;
    }

    // Возвращает true, если структура курса состоит всего из одного элемента
    public function isDegeneratedTree($courseId)
    {
        $tree = unserialize($this->getTreeData($courseId));
        $parents = $tree['parent'];
        $dest    = $tree['reference'];
        $result  = $tree['result'];

        if (!is_array($parents)) {
            $parents = array();
        }

        // Зачем-то в функции getTreeContent эта фильтрация производится,
        // поэтому здесь делаем то-же самое
        $result_filtered = array();
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $val) {
                if (isset($dest[$val])) {
                    $result_filtered[] = $val;
                }
            }
        }

        return count($result_filtered) == 0 || (
            count($result_filtered) == 1 && count($parents[$result_filtered[0]]) == 0
        );
    }

    public function isDegeneratedBranch($courseId, $branch)
    {
        $tree = unserialize($this->getTreeData($courseId));
        $parents = $tree['parent'];
        $dest    = $tree['reference'];

        if (!is_array($parents)) {
            $parents = array();
        }

        return !isset($dest[$branch]) || count($parents[$branch]) == 0;
    }

    public function getTreeContent($courseId, $opened = array(), $subject = 0, $lessonId = 0, $onlySections = false)
    {
            $tree = unserialize($this->getTreeData($courseId));
			
			$request = Zend_Controller_Front::getInstance()->getRequest();
			$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);		
			
            $current = $this->getService('CourseItemCurrent')->getCurrent($this->getService('User')->getCurrentUserId(), $subject, $courseId, $lessonId);
            if(!isset($tree['parent']) || !isset($tree['reference'])){
                 $tree = unserialize($this->getTreeData($courseId, true));
            }
            $parents = $tree['parent'];
            $dest    = $tree['reference'];
            $result  = $tree['result'];

            $all = $this->fetchAll(array('cid = ?' => $courseId));

            if ($onlySections) {
                $dest = array();
            }

            foreach($all as $key => $val) {
                if ($onlySections && ($val->module || $val->vol1 || $val->vol2)) {
                    continue;
                }

                $dest[$val->oid][0] = array('title' => $val->title, 'title_translation' => $val->title_translation, 'key' => (string)''.$val->oid);

                if($current == $val->oid){ $dest[$val->oid][0] = array('title' => $val->title, 'title_translation' => $val->title_translation, 'key' => (string)''.$val->oid, 'active' => true); }

                if(in_array($val->oid, $opened)){
                    $dest[$val->oid][0]['expand'] = true;
                }

                if (!($val->module || $val->vol1 || $val->vol2)) {
                    $dest[$val->oid][0]['isFolder'] = true;
                }

            }

            if(is_array($parents) && count($parents) > 0){
                foreach($parents as $key => $value){
                    if(in_array($key, $opened) || $opened === true){
                        foreach($value as $v){
                            if(is_array($dest[$key][1])){
                                $dest[$key][1] = array_merge($dest[$key][1], array($dest[$v][0], &$dest[$v][1]));
                            }else{
                                $dest[$key][1] = array();
                                $dest[$key][1] = array_merge($dest[$key][1], array($dest[$v][0], &$dest[$v][1]));
                            }
                        }

                    }else{
                        $dest[$key][1] = array();
                    }
                }
            }

            $ret = array();
			$reten = array();
            if(count($result) > 0){
	            foreach($result as $vals){
                    if (isset($dest[$vals])) {
	                    $ret = array_merge($ret, $dest[$vals]);
						$reten = array_merge($reten, $dest[$vals]);
                    }
	            }
            }
            else{
                if (isset($all[0])) {
                    $ret = array(array('title' => $all[0]->title, 'key' => $all[0]->oid));
					$reten = array(array('title_translation' => $all[0]->title_translation, 'key' => $all[0]->oid));
                }
            }
			if($lng == 'eng')
			return $reten;	
			else	
            return $ret;
    }

    public function getBranchPath($courseId, $branchId)
    {
        $path = array();

        $tree = unserialize($this->getTreeData($courseId));

        if (is_array($tree) && count($tree)) {

            if (isset($tree['reference'][$branchId][0])) {
                $level = $tree['reference'][$branchId][0]['level'];

                while(isset($tree['reference'][$branchId][0])) {
                    if ($tree['reference'][$branchId][0]['level'] < $level) {
                        $path[$branchId] = $branchId;
                    }

                    $branchId = $tree['reference'][$branchId][0]['prev_ref'];

                    if (!$branchId) break;
                }

            }

            while(isset($tree['parent'][$branchId][0])) {
                $branchId = $tree['parent'][$branchId][0];
                $path[$branchId] = $branchId;
            }
        }

        return $path;
    }



    public function getTreeData($courseId, $force = false)
    {

        $course = $this->getService('Course')->find($courseId);
        if(!$course){
            return false;
        }
        if($course[0]->tree != "" && $force == false){
            return $course[0]->tree;
        }else{

            $all = $this->fetchAll(
                                       array('cid = ?' => $courseId),
                                       array('prev_ref')
                                   );

            $dest    = array( 0 => array(
                                         array('oid' => 0),
                                         array()
                                   )
                       );
            $parents = array();
            $result  = array();

            if(count($all) > 0){
                $all = $all->asArray();

                //упорядочиваем структуру
                $tempItems = array();
                foreach($all as $item) {
                    $tempItems[(string)$item['prev_ref']] = $item;
                }
                $all = array();
                $all[] = $tempItems['-1'];
                while (count($all) != count($tempItems)) {
                    $last = end($all);
                    if (empty($last)) break;
                    $newItem = $tempItems[(string)$last['oid']];
                    $all[] = $newItem;
                }

                $t = 0;
                // Чтобы бесконечным цикл не был. Мало ли $all никогда не будет empty, хотя должны добавлятся по-очереди.
                // Циклов может быть больше чем элементов только если prev_ref элемента стало больше чем было в самом начале
                while(!empty($all) && $t < 10000){
                    foreach($all as $key => $val){
                        $val = array('oid' => $val['oid'], 'prev_ref' => $val['prev_ref'], 'level' => $val['level']);

                        if(isset($dest[$val['prev_ref']]) && $val['level']!=0){

                            if($dest[$val['prev_ref']][0]['level'] == $val['level']){
                                $val['parent'] = $dest[$val['prev_ref']][0]['parent'];
                            }elseif($dest[$val['prev_ref']][0]['level'] < $val['level']){
                                $val['parent'] = $dest[$val['prev_ref']][0]['oid'];
                            }elseif($dest[$val['prev_ref']][0]['level'] > $val['level']){

                                $distance = $dest[$val['prev_ref']][0]['level'] - $val['level'];
                                $parent = $dest[$dest[$val['prev_ref']][0]['parent']][0];

                                for($i=0; $i< $distance; $i++){
                                    $parent = $dest[$parent['parent']][0];
                                }
                                $val['parent'] = $parent['oid'];
                            }

                            $parents[$val['parent']][] = $val['oid'];
                            $dest[$val['oid']]         = array($val);
                            unset($all[$key]);

                        }elseif($val['level'] == 0){

                            $val['parent']     = 0;
                            $dest[$val['oid']] = array($val);
                            $result[]          = $val['oid'];
                            unset($all[$key]);
                        }
                    }
                    $t++;
                }
            }else{
                return false;
            }


            $parents = array_reverse($parents, true);

            $temp = array('parent'    => $parents,
        			      'reference' => $dest,
                          'result'    => $result
                    );

            $temp = serialize($temp);

            $this->getService('Course')->update(array('CID' => $courseId, 'tree' => $temp));

            return $temp;
        }
    }


   
    public function getBranchContent($courseId, $branch, $onlySections = false)
    {
        $charset = Zend_Registry::get('config')->charset;

        $tree = unserialize($this->getTreeData($courseId));

        $parents = $tree['parent'];
        $dest    = $tree['reference'];
        $result  = $tree['result'];

        $where = array_keys($dest);

        $all = count($dest) ? $this->fetchAll(array('oid IN (?)' => $where)) : array();

        if ($onlySections) {
            $dest = array();
        }

        foreach($all as $key => $val) {

            if ($onlySections && ($val->module || $val->vol1 || $val->vol2)) {
                continue;
            }

            $dest[$val->oid] = array('title' => iconv($charset,"UTF-8", $val->title), 'key' => $val->oid);

            if (!($val->module || $val->vol1 || $val->vol2)) {
                $dest[$val->oid]['isFolder'] = true;
            }
        }

        foreach($parents as $key => $value){
            if ($onlySections && !isset($dest[$key])) continue;

            $dest[$key]['isLazy'] = true;
            $dest[$key]['isFolder'] = true;
            foreach($value as $v){
                if ($onlySections && !isset($dest[$v])) continue;

                if(is_array($dest[$key]['children'])){
                    $dest[$key]['children'] = array_merge($dest[$key]['children'], array(&$dest[$v]));

                }else{
                    $dest[$key]['children'] = array();
                    $dest[$key]['children'] = array_merge($dest[$key]['children'], array(&$dest[$v]));
                }
            }
        }

        unset($dest[0]);

        if (is_array($dest[$branch]['children'])) {
            foreach($dest[$branch]['children'] as &$vall){
                $vall['children'] = array();
            }
        }
        return (is_array($dest[$branch]['children']) && count($dest[$branch]['children']))? $dest[$branch]['children'] : array();    
    }


    public function getOpenedBranch($courseId)
    {
        $defaultNamespace = new Zend_Session_Namespace('default');
        if(is_array($defaultNamespace->tree['course'][$courseId])){
            return $defaultNamespace->tree['course'][$courseId];
        }else{
            return array();
        }
    }

    public function addOpenedBranch($courseId, $branch)
    {
        $defaultNamespace = new Zend_Session_Namespace('default');
        $defaultNamespace->tree['course'][$courseId][$branch] = $branch;
        return true;
    }

    public function deleteOpenedBranch($courseId, $branch)
    {

        $defaultNamespace = new Zend_Session_Namespace('default');

        $data = unserialize($this->getTreeData($courseId));
        $parents = $data['parent'];
        $this->_unsetBranch($branch, &$defaultNamespace, $parents, $courseId);
        unset($defaultNamespace->tree['course'][$courseId][$branch]);
        return true;
    }

    private function _unsetBranch($branch, &$defaultNamespace, $parents, $courseId)
    {
        if(isset($parents[$branch])){
            foreach($parents[$branch] as $val){
                unset($defaultNamespace->tree['course'][$courseId][$val]);
                if(isset($parents[$val])){
                    $this->_unsetBranch($val, &$defaultNamespace, $parents, $courseId);
                }
            }
        }
    }

    public function append($data, $sectionId = 0)
    {
        $item = false;
        if ($data['cid'] > 0) {

            $data['prev_ref'] = -1;

            $section = null; $level = 0;
            if ($sectionId > 0) {
                $section = $this->getOne($this->find($sectionId));
                $level = $section->level + 1;
                $data['prev_ref'] = $sectionId;
            }

            $data['level'] = $level;

            $children = $this->getChildrenLevel($data['cid'], $sectionId, false);
            if (count($children)) {
                $lastChild = $children[count($children)-1];
                if ($lastChild) {
                    $data['prev_ref'] = $lastChild->oid;
                }
            }

            try {
                $this->getSelect()->getAdapter()->beginTransaction();

                if ($data['prev_ref'] > 0) {
                    $this->updateWhere(
                        array('prev_ref' => -999),
                        $this->quoteInto(
                            array('prev_ref = ?', ' AND cid = ?'),
                            array($data['prev_ref'], $data['cid'])
                        )
                    );
                }

                $item = $this->insert($data);

                if ($item && ($data['prev_ref'] > 0)) {
                    $this->updateWhere(
                        array('prev_ref' => $item->oid),
                        $this->quoteInto(
                            array('prev_ref = ?', ' AND cid = ?'),
                            array(-999, $data['cid'])
                        )
                    );
                }

                if ($item) {
                    $this->getService('Course')->update(
                        array('tree' => '', 'CID' => $item->cid)
                    );
                }

                $this->getSelect()->getAdapter()->commit();
            } catch (Zend_Db_Exception $e) {
                $this->getSelect()->getAdapter()->rollBack();
            }
        }
        return $item;
    }

    public function delete($id, $first = true, $force = false, $subjectId = 0)
    {
        $item = $this->getOne($this->find($id));
        if ($item) {

            $prev_ref = $id;

            // Удаляем детей
            $children = $this->getChildrenLevel($item->cid, $id);
            if (count($children)) {
                foreach($children as $child) {
                    $this->delete($child->oid, false, $force, $subjectId);
                    $prev_ref = $child->oid;
                }
            }

            if ($force && $item->vol2 && $subjectId) {
                $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($item->vol2));

                if($this->getService('Resource')->isEditable($resource->subject_id, $subjectId, $resource->location)){
                    $this->getService('Resource')->delete($resource->resource_id);
                }
            }

            $res = parent::delete($id);

            if ($res) {
                $this->updateWhere(
                    array('prev_ref' => $item->prev_ref),
                    $this->quoteInto(
                        array('cid = ?', ' AND prev_ref = ?'),
                        array($item->cid, $prev_ref)
                    )
                );
            }

            if ($first) {
                $this->getService('Course')->update(array('tree' => '', 'CID' => $item->cid));
            }

            return $res;
        }

        return false;
    }

    /**
     * Копирование структуры учебного модуля
     * @param $fromCourseID
     * @param $toCourseID
     * @todo: локальные инфоресурсы и пр. тоже бы надо копировать...
     * @todo: возможно, есть смысл сделать копирование с добавлением к имеющимся разделам, но в рамках задачи копирования курса это не актуально
     */
    public function copyItem($fromCourseID, $toCourseID)
    {
        $refs  = array();
        $items = $this->fetchAll(array('cid=?' => $fromCourseID),'oid');

        // дабы не попортить имеющееся, проверка, что имеется только один "пустой элемент" или модуль пустой
        $destinationItems = $this->fetchAll(array('cid=?' => $toCourseID),'oid');
        $destinationCount = count($destinationItems);
        if ( count($items) && $destinationCount <= 1 ) {
            if ($destinationCount) {
                $old = $this->getOne($destinationItems);
                $this->delete($old->oid);
            }

            foreach ($items as $originalItem) {
                $oldID = $originalItem->oid;
                unset($originalItem->oid);
                $originalItem->cid = $toCourseID;
                $newItem = $this->insert($originalItem->getValues());
                if ($newItem) {
                    $refs[$oldID] = $newItem->oid;
                }
            }

            $destinationItems = $this->fetchAll(array('cid=?' => $toCourseID),'oid');
            if (count ($destinationItems)) {
                foreach($destinationItems as $item) {
                    if (isset($refs[$item->prev_ref])) {
                        $item->prev_ref = $refs[$item->prev_ref];
                        $this->update($item->getValues());
                    }
                }
            }
        }
    }
}