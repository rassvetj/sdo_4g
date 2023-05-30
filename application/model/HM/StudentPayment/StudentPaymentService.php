<?php
class HM_StudentPayment_StudentPaymentService extends HM_Service_Abstract
{
	# ушли от кэширования. Данные сохраняются в автризационной сессии в инфоблоке
	const CACHE_NAME 	 		= 'HM_StudentPayment_StudentPaymentService';
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
    
	public function hasDebt($personal_code){
		$res = $this->fetchAll($this->quoteInto('person_code = ? AND (fine > 0 OR sum_underpayment > 0)', $personal_code));
		if(count($res) < 1){ return false; }
		return true;
	}
	
	
	
	public function getCount($personal_code){
		$rows = $this->getByCode($personal_code);
		if(empty($rows)){ return 0; }
		return count($this->getByCode($personal_code));		
	}
	
	
	public function getByCode($personal_code){
		if(empty($personal_code)){ return false; }
		#$this->restoreFromCache();
		
		#if($this->_lifetime <= time() ){ # очищаем кэш, старше CACHE_LIFETIME
		#	$this->clearCache();  	
		#	$this->_lifetime = time() + self::CACHE_LIFETIME;
		#}
		
		#if(!empty($this->_personal_debts)){ return $this->_personal_debts; }
		
		$this->_personal_debts = $this->fetchAll($this->quoteInto('person_code = ?', $personal_code));
		
		#$this->saveToCache();		
		return $this->_personal_debts;
	}
	
	
}