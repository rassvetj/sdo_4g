<?php
class HM_Classifier_Link_LinkService extends HM_Service_Abstract
{
	
    public function getRelevantSubjectsForUser($user_id, $classifier_type = 1){

	   	$select = $this->getSelect();
		$select->from(
						array('clu' => 'classifiers_links'),
						array('subject_id' => 's.subid')
					);
		$select->join(
						array('cls' => 'classifiers_links'),
						'cls.classifier_id = clu.classifier_id AND cls.type = '.HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
						array(/*'count' => 'COUNT(cls.classifier_id)'*/)
					);
		$select->join(
						array('c' => 'classifiers'),
						'c.classifier_id = clu.classifier_id AND c.type = '.$classifier_type.' AND c.level != 0',
						array()
				);
		$select->join(
						array('s' => 'subjects'),
						's.subid = cls.item_id AND (s.reg_type = '.HM_Subject_SubjectModel::REGTYPE_FREE.' OR s.reg_type = '.HM_Subject_SubjectModel::REGTYPE_MODER.')',
						array()
					);
		$select->joinLeft(
						array('st' => 'Students'),
						'st.CID = s.subid AND st.MID = '.$user_id,
						array('registeged' => 'st.registered')
					);
		$select->group(array('s.subid', 'st.registered'));
		$select->where('registered IS NULL');
		$select->where('clu.type = ?', HM_Classifier_Link_LinkModel::TYPE_PEOPLE);
		$select->where('clu.item_id = ?', $user_id);
		
		$tmp = $select->query()->fetchAll();
		$tmp = (is_array($tmp)) ? $tmp : array();
		
		$relevant_subjects = array();
		foreach ($tmp as $value) {
            $relevant_subjects[] = $value['subject_id'];
		}
    
		return $relevant_subjects;
		
    }

    /**
     * with rewrite all classifiers of this $item_id and $type!
     * @param  $item_id
     * @param  $type
     * @param  $classifiers
     * @return
     */
    public function setClassifiers($item_id, $type, $classifiers)
    {
        $this->getService('ClassifierLink')->deleteBy($this->getService('ClassifierLink')->quoteInto(
                                                          array('item_id = ?', 'AND type = ?'),
                                                          array($item_id, $type)
                                                      ));

        if (is_array($classifiers) && count($classifiers)) {
            foreach($classifiers as $classifierId) {
                $res = $this->getService('ClassifierLink')->insert(array(
                                                                        'item_id' => $item_id,
                                                                        'classifier_id' => $classifierId,
                                                                        'type' => $type
                                                                   ));
            }
        }
        return $res;

    }
    
    public function getClassifiers($itemId, $typeId)
    {
        $return = array();
        $links = $this->fetchAllDependenceJoinInner('Classifier', $this->quoteInto(
            array('self.item_id = ?', ' AND Classifier.type = ?'), // не работает
            array($itemId, $typeId)
        ));
        if (count($links)) {
            foreach ($links as $link) {
                if (count($link->classifiers)) {
                    foreach ($link->classifiers as $classifier) {
                        if ($classifier->type != $typeId) continue;
                        $return[] = $classifier;
                    }
                }
            }
        }
        return $return;
    }
	
	public function getSubjectClassifiersList(){
		$select = $this->getSelect();
		$select->from(array('cl' => 'classifiers_links'), array(
			'subject_id' => 'cl.item_id',
			'name'		 => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT c.name)'),	
		));
		$select->join(array('c' => 'classifiers'), 'c.classifier_id = cl.classifier_id', array());
		$select->where('cl.type = ?', HM_Classifier_Link_LinkModel::TYPE_SUBJECT );
		$select->group(array('cl.item_id'));
		$res = $select->query()->fetchAll();
		if(!$res){ return false; }
		$data = array();
		foreach($res as $i){
			$data[$i['subject_id']] = $i['name'];
		}
		return $data;
	}
	
	
}