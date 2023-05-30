<?php

define('QUESTION_BRTAG',"~\x03~");

class CQuestion extends CDBObject {    
    
    var $table = 'list';
           
    function create() {
        if (is_array($this->attributes) 
            && count($this->attributes) 
            && !empty($this->attributes['qdata'])
            && $this->attributes['cid']) {
                
            $this->attributes['created_by'] = $_SESSION['s']['mid'];            
            $this->attributes['last']       = time();
            $this->attributes['kod']        = $this->getKod($this->attributes['cid']);
            unset($this->attributes['cid']);
            
            parent::create();
            return $this->attributes['kod'];
            
        }
        return false;
    }

    /**
     * Аналог newQuestion($cid) из test.inc.php
     * 
     */
    function getKod($cid) { 
    	$id_base = sqlvalue("SELECT autoindex FROM conf_cid WHERE cid='".(int) $cid."'","errTL837");
    
    	if (!(int) $id_base) {
    		$id = 1;
    		$res = sql("INSERT INTO conf_cid (cid) values ($cid)");
    		sqlfree($res);
    	}
    	
    	$ok   = 0;
    	$incr = 1;
    	$id   = $id_base;
    	for($i=0; $i<100; $i++) {
    	    
    		$testkod="$cid-".$test_list_kod.sprintf("%0{$test_list_null}d",$id);
    		$cnt=sqlvalue("SELECT COUNT(*) FROM list WHERE kod='".addslashes($testkod)."'","errTL837");
    		if ($cnt) {
    		    $id=$id_base+$i*ceil(doubleval($i/5))+mt_rand(0,1+ceil(doubleval($i/5)));
    		} else {
    		    $ok=1;
    		    break;
    		}
    		
    	}
    	$res=sql("UPDATE conf_cid SET autoindex=".($id+1)." WHERE cid = $cid");
    	if ($ok == 0) {
    		exitmsg(_("Извините, невозможно автоматически сгенерировать ни одного нового номера, т.к. все они оказываются занятыми!"), $GLOBALS['sitepath']);
    	}
    	$kod = $testkod;
    	return $kod;

    }
    
}

?>