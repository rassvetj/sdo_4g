<?php

class CCopy {
    
    var $bid, $assid, $mid, $start, $stop, $closed, $copies;    
    var $msg;
    
    function CCopy($bid=0,$assid=0,$mid=0,$start='',$stop='',$closed=0) {
        
        $this->bid = (int) $bid;
        $this->assid = (int) $assid;
        $this->mid = (int) $mid;
        $this->start = $start;
        $this->stop = $stop;
        $this->closed = (int) $closed;
        $this->msg = '';                
        
    }
    
    function set_bid($bid) {
        
        $this->bid = (int) $bid;
        
    }
    
    function updateItem() {
        
        if ($this->assid>0) {
            
            $sql = "UPDATE library_assign 
                    SET 
                        bid='".(int) $this->bid."', 
                        mid='".(int) $this->mid."', 
                        start='".$this->start."', 
                        stop='".$this->stop."', 
                        closed='".(int) $this->closed."'
                    WHERE assid='".(int) $this->assid."'";
                    
            sql($sql);
            
        }
        
    }
    
    function getInfo($assid=0) {
        
        if ($assid>0) {
            
            $sql = "SELECT 
                        library_assign.assid, library_assign.bid, library_assign.mid, library_assign.start, library_assign.stop, library_assign.closed, 
                        library.title 
                    FROM library_assign 
                    LEFT JOIN library ON (library_assign.bid=library.bid) 
                    WHERE 
                        library_assign.assid='".(int) $assid."'";
            $res = sql($sql);
            if (sqlrows($res)) {                
                $row = sqlget($res);
                $ret = $row;
            }
            
        }
        
        return $ret;
        
    }
    
    function assign($mid,$start,$stop) {
        
        if (($mid>0) && ($this->bid>0)) {
            
            if ($this->countCopies()>0) {
                
                $sql = "INSERT INTO library_assign (bid,mid,start,stop)
                        VALUES ('".(int) $this->bid."','".(int)$mid."','".$start."','".$stop."')";
                $res = sql($sql); 
                $this->msg = "Издание успешно выдано пользователю";                       
                
            } else $this->msg = "Нет свободных экземпляров издания";
            
        }        
        
    }
    
    function close($assid) {
    
        if ($assid>0) {
            
            $sql = "UPDATE library_assign 
                    SET closed='1' 
                    WHERE assid='".(int) $assid."'";
            sql($sql);
            
        }
    
    }    
        
    function countCopies() {
        
        $sql = "SELECT * FROM library WHERE bid='".$this->bid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            if ($row['quantity']<=0) return 0;
            $sql = "SELECT COUNT(assid) AS cnt 
                    FROM library_assign 
                    WHERE 
                        bid='".(int) $this->bid."'
                        AND closed='0'";
            $res = sql($sql);
            if (sqlrows($res)) {                
                $row2 = sqlget($res);
                $ret = (int) ($row['quantity']-$row2['cnt']);
            }
        }
        
        return $ret;
        
    }
    
    function getAssigned() {
        
        $sql = "SELECT * 
                FROM library_assign 
                WHERE 
                    bid='".(int)$this->bid."' AND
                    stop='0000-00-00 00:00:00' ORDER BY start";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $row['fio'] = get_login_and_lastname_and_firstname_by_mid($row['mid']);
            $ret[] = $row;
            
        }
        
        return $ret;
        
    }
    
    function getClosed() {
        
        $sql = "SELECT * 
                FROM library_assign 
                WHERE 
                    bid='".(int)$this->bid."' AND
                    stop<>'0000-00-00 00:00:00' ORDER BY start";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $row['fio'] = get_login_and_lastname_and_firstname_by_mid($row['mid']);            
            $ret[] = $row;
            
        }
        
        return $ret;
    }
    
    function getHistory() {
        $sql = "SELECT * 
                FROM library_assign 
                WHERE 
                    bid='".(int)$this->bid."' ORDER BY start";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $row['fio'] = get_login_and_lastname_and_firstname_by_mid($row['mid']);            
            $ret[] = $row;
            
        }
        
        return $ret;        
    }
    
}

?>