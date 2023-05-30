<?php
class Bvb_Grid_Filters_Render_MarksheetDate extends Bvb_Grid_Filters_Render_Date
{
    public function hasConditions()
    {
        return false;
    }

    public function buildQuery(array $filter)
    {
		$serviceMarksheet 	= Zend_Registry::get('serviceContainer')->getService('Marksheet');
		$select 			= $serviceMarksheet->getSelect();
		
		if (isset($filter['from'])) {
            $where .= '(';
            $where .= $serviceMarksheet->quoteInto('date_issue >= ?', $this->transform($filter['from'], 'from'));			
		}
		if (isset($filter['to'])) {
            if (isset($filter['from'])) {
                $where .= ' AND ';
            } else {
                $where .= '(';
            }
            $where .= $serviceMarksheet->quoteInto('date_issue <= ?',$this->transform($filter['to'], 'to'));
        }
		if (strlen($where)) {
            $where .= ')';
        }
		if(empty($where)){ return; }
		
		$select = $serviceMarksheet->getSelect();
		$select->from(array('mi' => 'marksheet_info'), array('subject_id' => 's.subid'));
		$select->join(array('s'  => 'subjects'), 's.external_id = mi.subject_external_id', array());
		$select->where($where);
		$res = $select->query()->fetchAll();
		
		if(empty($res)){		
			$this->getSelect()->where(' 1=0 ');
			return;
		}
		
		$subject_IDs = array();
		foreach($res as $i){
			$subject_IDs[$i['subject_id']] = $i['subject_id'];
		}
		
		$this->getSelect()->where(
			$this->getSelect()->getAdapter()->quoteInto(
				'subid IN (?)',
				$subject_IDs
			)
		);		
		return;
    }
    
    public function getTableName(){
        $from = $this->getSelect()->getPart(Zend_Db_Select::FROM);

        foreach($from as $key => $tables){
            if($tables['joinType'] == 'from' || count($from) == 1){
                $name = $key;
                break;
            }
        }

        return $name;
    }
}