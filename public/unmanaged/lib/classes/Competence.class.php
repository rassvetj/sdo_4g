<?php

class CCompetence extends CDBObject {
    
    function get_courses($coid) {
        if ($coid) {
           $sql = "SELECT * FROM competence_courses WHERE coid='".(int) $coid."'";
           $res = sql($sql);            
           while($row = sqlget($res)) {
               $ret[] = $row['cid'];
           }
        }
        return $ret;
    }
    
    function get($coid) {
        if ($coid) {
            $sql = "SELECT * FROM competence WHERE coid='".(int) $coid."'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) {
                $row['courses'] = CCompetence::get_courses($coid);
            }
        }
        return $row;
    }
    
}

class CCompetences {
    
    /**
     * Возвращает массив компетенций array[coid]=name
     *
     * @return array
     */
    function get_as_array_coid_name() {
        $sql = "SELECT * 
                FROM competence
                ORDER BY name";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $ret[$row['coid']] = $row['name'];
        }
        return $ret;
    }
    
    /**
     * Возвращает массив с названиями компетенций из $competences: array[coid]=name
     *
     * @param array $competences
     * @return array
     */
    function get_names($competences) {
        if (is_array($competences) && count($competences)) {
            $sql = "SELECT *
                    FROM competence
                    WHERE coid IN ('".join("','",$competences)."')
                    ORDER BY name";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $ret[$row['coid']] = $row['name'];
            }
            return $ret;
            
        }
    }
    
    function get_comments($competences) {
        if (is_array($competences) && count($competences)) {
            $sql = "SELECT coid, info
                    FROM competence
                    WHERE coid IN ('".join("','",$competences)."')
                    ORDER BY name";
            $res = sql($sql);
            while($row = sqlget($res)) {
                $ret[$row['coid']] = $row['info'];
            }
            return $ret;
            
        }       
    }    
    
}

?>