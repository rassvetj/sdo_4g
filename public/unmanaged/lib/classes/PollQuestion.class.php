<?php

define('POLL_QUESTION_TYPE',11);
define('SOID_POLL_QUESTION_TYPE',11);

class CPollQuestion extends CQuestion {

    function CPollQuestion($attributes) {
        parent::CQuestion($attributes);
    }
    
    function create() {
        $this->attributes['qtype'] = POLL_QUESTION_TYPE;
        return parent::create();
    }
               
}

class CSoidPollQuestion extends CPollQuestion {
    
    function _getQtema($soid) {
        return CPosition::getPersonName($soid);
    }
    
    
    function create($soid) {
        
        $ret = false;
        
        if (is_array(CPosition::get_roles($soid))) {
            foreach(CPosition::get_roles($soid) as $role) {
                $rolesArray[$role] = $role;
            //$roles[$role] = CCompetences::get_names(CCompetenceRole::get_competences_id($role));
            }   
        }
        
        if (is_array($rolesArray) && count($rolesArray)) {
            $roles = CCompetenceRoles::get($rolesArray);
        }
        
        if (/*($formula = CFormula::get($formulaId)) && */ is_array($roles) && count($roles)) {
           //if ($conditions = $formula->getFormulaConditions()) {
               $cid = $this->attributes['cid'];
               foreach($roles as $roleId => $role) {
                   if (($formula = CFormula::get($role->_attributes['formula'])) &&
                   ($conditions = $formula->getFormulaConditions())) {
                   
                   $competences = $role->_attributes['competences'];
                   
                   if (is_array($competences) && count($competences)) {
                       
                       $this->attributes['cid'] = $cid;
                       $this->attributes['balmax'] = intval(count($competences)*max(array_keys($conditions)));
                       $this->attributes['balmin'] = intval(min(array_keys($conditions)));
                       $this->attributes['qtema']  = CPosition::getPersonName($soid).' ('.$role->_attributes['name'].' / '.$formula->attributes['name'].' )';
                       $this->attributes['url']     = '';
                       $this->attributes['is_shuffled'] = 0;
                       
                       $qdata = array();
                       $qdata[0] = "Оценка сотрудника по критериям"; // todo: smarty->fetch header!!!

                       $count = max(array(count($competences),count($conditions)))*2;
                       $j=1;
                       for($i=1;$i<=$count;$i += 2) {
                           $qdata[$i]     = array_shift($competences);
                           $condition     = $conditions[$j-1];
                           $qdata[$i+1]   = $condition['value'];                                                     
                           $weight[$j++]  = $condition['condition'];
                       }
                                              
                       //ksort($qdata);
                       
                       $this->attributes['qdata'] = join(QUESTION_BRTAG,$qdata);
                       $this->attributes['weight'] = serialize($weight);
                       
                       if ($kod = parent::create()) {
                           $ret[$role->_attributes['id']] = $kod;
                       }
                       
                   }
                   
                   } // if $formula...
               }
           //}
        }
        
        return $ret;
    }
    
}

?>