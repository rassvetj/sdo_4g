<?php
class HM_Classifier_ClassifierService extends HM_Service_Nested
{

    public function linkItem($itemId, $itemType, $classifierId)
    {
        return $this->getService('ClassifierLink')->insert(
            array(
                'item_id' => $itemId,
                'type' => $itemType,
                'classifier_id' => $classifierId
            )
        );
    }

    public function unlinkItem($itemId, $itemType)
    {
        return $this->getService('ClassifierLink')->deleteBy(
            $this->quoteInto(
                array('item_id = ?', ' AND type = ?'),
                array($itemId, $itemType)
            )
        );
    }

    public function linkExists($itemId, $itemType, $classifierId)
    {
        $collection = $this->getService('ClassifierLink')->fetchAll(
            $this->getService('ClassifierLink')->quoteInto(
                array('item_id = ?', ' AND type = ?', ' AND classifier_id = ?'),
                array($itemId, $itemType, $classifierId)
            )
        );

        return count($collection);

    }

    public function getTreeContent($itemType = null, $parent = 0, $type = null, $notEncode = false, $classifierId = 0)
    {
        $res = array();
        if (null !== $type) {
            $categories = $this->getChildren($parent, true, 'node.type = '.(int) $type);
        } else {
            $categories = $this->getChildren($parent);
        }
        $classifiersCount = 0;
        if (null !== $itemType) {
            $classifiersCount = $this->getElementCount($itemType, $categories);
        }
        $userId = $this->getService('User')->getCurrentUserId();
        if (count($categories)) {
        	$categories = $categories->asArrayOfObjects('name'); // sort by name
            foreach($categories as $category) {
            	//$subcategories = $this->getChildren($category->classifier_id);
            	$isFolder = (($category->rgt - $category->lft) > 1) ? true : false;
                $item = array(
                    'title' => _( (($parent > 0 && $notEncode === false) ? iconv(Zend_Registry::get('config')->charset, 'UTF-8', $category->name) : $category->name) ),
                	'count' => (int) $classifiersCount[$category->classifier_id],
                    'key' => $category->classifier_id,
                    'isLazy' => ($isFolder ? true  : false),
                    'isFolder' => $isFolder
                );
                /* что за магия?
                if($classifierId && $category->lft == 14 && $category->rgt > 16 && $parent){
                	$sub = $this->getTreeContent($itemType, $category->classifier_id, $type, false, $classifierId);
	                if(count($sub)) {
		                $item['expand'] = true;
	                	$res[] = $item; $res[] = $sub;
	                }
                }
                else */ 
                $res[] = $item;

            }
        }

        if($parent === 0){
            if (count($res)) {
                $result = array();
                foreach($res as $r) {
                    $r['expand'] = true;
                    $result[] = $r;
                    $temp = $this->getTreeContent($itemType, $r['key'], $type, true, $classifierId);
                    $result[] = $temp;
                }
                $res = $result;
            }
            /*
            $temp = $this->getTreeContent($itemType, $res[0]['key'], $type, true, $classifierId);
            $res[1] = $temp;
            $res[0]['expand'] = true;

             */
        }
        return $res;
    }


    /**
     * deprecated! use $this->getService('ClassifierType')->getClassifierTypes($link_type)->getList('type_id', 'name')
     * @param  $link_type
     * @return array
     */
    public function getTypes($link_type)
    {
        $types = array();
        $res = $this->getService('ClassifierType')->getClassifierTypes($link_type);
        foreach($res as $value){
            $types[$value->type_id] = _($value->name);
        }

        return $types;
    }

    private function _getSubjectsInCategories($type, $categories)
    {

        $userId = $this->getService('User')->getCurrentUserId();

        $lft = null;
        $rgt = null;

        foreach($categories as $value){
            if($lft == null || $lft > $value->lft){
                $lft = $value->lft;
            }
            if($rgt == null || $rgt < $value->rgt){
                $rgt = $value->rgt;
            }

        }

        if($type == HM_Classifier_Link_LinkModel::TYPE_SUBJECT){
            // some shitcode here
            $select = $this->getSelect();
            $select->from(array('cls' => 'classifiers_links'), array())
                   ->joinInner(array('s' => 'subjects'), 's.subid = cls.item_id',array('subid', 'last_updated'))
                   ->where($this->quoteInto(
                        array(
                            's.period IN (?) OR ',
                            's.period_restriction_type = ? OR ',
                            '(s.period_restriction_type = ? AND ',
                            's.state = ?) OR ',
                            '(s.period = ? AND ',
                            's.end > ?)',
                        ),
                        array(
                            array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED),
                            HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                            HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                            HM_Subject_SubjectModel::STATE_ACTUAL,
                            HM_Subject_SubjectModel::PERIOD_DATES,
                            $this->getService('Subject')->getDateTime()
                        )
                    ))
                   ->where('s.reg_type IN (?)', array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_MODER))
                   ->joinInner(array('c' => 'classifiers'), 'cls.classifier_id = c.classifier_id',array('c.lft', 'c.rgt'))
                   ->where('cls.type = ?', HM_Classifier_Link_LinkModel::TYPE_SUBJECT);

           if ($userId > 0) {
               $select->joinLeft(array('st' => 'Students'), 's.subid = st.CID AND st.MID = ' . $userId, array())
                      ->where('st.CID IS NULL');
               $select->joinLeft(array('cl' => 'claimants'), 's.subid = cl.CID AND cl.MID = ' . $userId, array())
                      ->where('cl.CID IS NULL');
           }

           $query = $select->query();
           $res = $query->fetchAll();

        }elseif($type == HM_Classifier_Link_LinkModel::TYPE_RESOURCE){
            $select = $this->getSelect();
            $select->from(array('cls' => 'classifiers_links'), array())
                   ->joinInner(array('r' => 'resources'), 'r.resource_id = cls.item_id',array())
                   ->joinInner(array('c' => 'classifiers'), 'cls.classifier_id = c.classifier_id',array('c.lft', 'c.rgt'))
                   ->where('cls.type = ?', HM_Classifier_Link_LinkModel::TYPE_RESOURCE);

           $query = $select->query();

           $res = $query->fetchAll();
        }elseif($type == HM_Classifier_Link_LinkModel::TYPE_USER){

            /*
             * Сделать запрос для классификации юзеров
             */


        }

        if(count($res) == 0){
            return array();
        }
        return $res;
    }

    public function getElementCount($type, $categories)
    {
    	$resArray = array();
    	$subjects = $this->_getSubjectsInCategories($type, $categories);

        foreach($subjects as $val){
            foreach($categories as $val2){
                if($val['lft'] >= $val2->lft && $val['rgt'] <= $val2->rgt){
                    $resArray[$val2->classifier_id]++;
                }
            }
        }
        return $resArray;
    }

    public function deleteNode($id, $recursive = false)
    {
        $classifier = $this->getOne($this->find($id));
        if ($classifier) {
            $classifiers = $this->fetchAll(
                $this->quoteInto(
                    array('lft >= ?', ' AND rgt <= ?'),
                    array($classifier->lft, $classifier->rgt)
                )
            );

            if (count($classifiers)) {
                foreach($classifiers as $classifier) {
                    $this->getService('DeanResponsibility')->deleteBy(
                        $this->quoteInto('classifier_id = ?', $classifier->classifier_id)
                    );
                }
            }
        }
        return parent::deleteNode($id, $recursive);
    }
    public function getCategoriesFreshness($categories)
    {
        $resArray = $resCount = $resTotal = array();
        $subjects = $this->_getSubjectsInCategories(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $categories);
		$service = $this->getService('Subject');

        foreach($subjects as $val){
            foreach($categories as $val2){
                if($val['lft'] >= $val2->lft && $val['rgt'] <= $val2->rgt){
                	$resTotal[$val2->classifier_id] += (($val['last_updated']) ? HM_Subject_SubjectService::calcFreshness(strtotime($val['last_updated'])) : 0);
                	$resCount[$val2->classifier_id]++;
                }
            }
        }
        foreach ($resTotal as $classifierId => $total) {
        	$resArray[$classifierId] = floor($total/$resCount[$classifierId]);
        }
        return $resArray;
    }

    public function getUnclassifiedElementCount($type)
    {
    	$userId = $this->getService('User')->getCurrentUserId();

        $select = $this->getSelect()
        	->from(array('s' => 'subjects'), array('subid'))
               ->joinLeft(array('cls' => 'classifiers_links'), "s.subid = cls.item_id AND cls.type = {$type}", array())
               ->where('s.end > ?', $this->getService('Subject')->getDateTime())
               ->where('s.reg_type IN (?)', array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_MODER))
               ->where('cls.item_id IS NULL');

        if ($userId > 0) {
            $select->joinLeft(array('st' => 'Students'), 's.subid = st.CID AND st.MID = ' . $userId, array())
                   ->where('st.CID IS NULL');
            $select->joinLeft(array('cl' => 'claimants'), 's.subid = cl.CID AND cl.MID = ' . $userId, array())
                   ->where('cl.CID IS NULL');
        }

        $query = $select->query();
        $res = $query->fetchAll();

        return count($res);
    }

    public function deleteByType($type)
    {

        $classifiers = $this->getService('Classifier')->fetchAll(
            $this->quoteInto(
                array(
                    'type = ?', ' AND level = ?'
                ), array(
                    $type, 0
                )
            )
        );

        if (count($classifiers)) {
            foreach($classifiers as $classifier) {
                $this->getService('Classifier')->deleteNode($classifier->classifier_id, true);
            }
        }
    }

    public function delete($id)
    {
        $this->getService('ClassifierLink')->deleteBy(
            $this->quoteInto('classifier_id = ?', $id)
        );

        $this->getService('DeanResponsibility')->deleteBy(
            $this->quoteInto('classifier_id = ?', $id)
        );

        return parent::delete($id);
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('рубрика во множественном числе', '%s рубрика', $count), $count);
    }

    public function pluralFormCountTypes($count)
    {
        return !$count ? _('Нет') : sprintf(_n('область применения во множественном числе', '%s область применения', $count), $count);
    }
    
    // @todo: добавить атрибут классификатора "plain",
    // чтобы этим методом ожно было всегда пользоваться для такого классификатора
    public function getTopLevel($type)
    {
        $return = array();
        $collection = $this->fetchAll("level = 0 AND type=" . intval($type));
        if (count($collection)) {
            foreach($collection as $rubric) {
                $return[$rubric->classifier_id] = $rubric->name;
            }
        }
        return $return;
    }    

}