<?php
class HM_StudentDebt_StudentDebtService extends HM_Service_Abstract
{
	const CACHE_NAME 	 		= 'HM_StudentDebt_StudentDebtService';
	const CACHE_LIFETIME 		= 10; #86400; #60*60*24; # время жизни кэша. 1 день
	
	private $_personal_debts 	= null; # список долгов студента
	private $_lifetime 			= null; # дата истечения жизни кэша
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'personal_debts'	=> $this->_personal_debts,
                'lifetime'			=> $this->_lifetime,
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_personal_debts	= $actions['personal_debts'];
            $this->_lifetime		= $actions['lifetime'];
            return true;
        }
        return false;
    }
    
	public function getIndexSelect() {
        $select = $this->getSelect();
        $select->from(
            array(
                't' => 'student_debts'
            ),
            array(
                'student_debt_id' 	=> 't.student_debt_id',
                'mid_external'    	=> 't.mid_external',
                'discipline'    	=> 't.discipline',
                'type'     			=> 't.type',
                'date_revision'     => 't.date_revision',
                'semester'     		=> 't.semester',
                'isMarksheet'     	=> 't.isMarksheet',
                'date_end'     		=> 't.date_end',
                'state'     		=> 't.isMarksheet',
                'alternative_name'  => 't.alternative_name',
                //'date_revision'	=> new Zend_Db_Expr('CONVERT(VARCHAR, t.date_revision, 104)'),				
            )
        );
		
		//$select->order('t.date_revision DESC');
		
        return $select;
    }
	
	
	
	public function getCount($mid_external){
		$count = count($this->getByCode($mid_external));
		return $count;
	}
	
	
	public function getByCode($mid_external){
		
		#$this->restoreFromCache();
		
		#if($this->_lifetime <= time() ){ # очищаем кэш, старше CACHE_LIFETIME
		#	$this->clearCache();  	
		#	$this->_lifetime = time() + self::CACHE_LIFETIME;
		#}
		
		#if(!empty($this->_personal_debts)){ return $this->_personal_debts; }
		
		$this->_personal_debts = $this->fetchAll($this->quoteInto('mid_external = ?', $mid_external));
		#$this->saveToCache();		
		return $this->_personal_debts;
	}
	
	
}