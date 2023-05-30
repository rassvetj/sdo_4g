<?php

class CObject {
    var $attributes = array();
    
    function CObject($attributes=array()) {
        $this->attributes = $attributes;
    }

    function rusDate($str, $format="d.m.Y H:i:s") {
        return date($format,strtotime($str));
    }
    
    function engDate($str) {
        if (preg_match("/^([0-9]{1,2}).([0-9]{1,2}).([0-9]{4}) ([0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})$/",$str, &$matches)) {
            return $matches[3].'-'.$matches[2].'-'.$matches[1].' '.$matches[4];
        }

        if (preg_match("/^([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})$/",$str, &$matches)) {
            return $matches[3].'-'.$matches[2].'-'.$matches[1];
        }
        return $str;
    }    
    
    function unixDate($str) {
        return strtotime($str);
    }
    
    function toUpperFirst($content) {
        $content = strtoupper(str_replace(
            array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','р','п','с','т','у','ф','х','ц','ч','ш','щ','ъ','ь','ы','э','ю','я'),
            array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','Р','П','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ь','Ы','Э','Ю','Я'),
            substr($content, 0, 1)
        )).substr($content, 1);
        return $content;
    }
    
    function toUpper($content) {
        $content = str_replace(
            array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','р','п','с','т','у','ф','х','ц','ч','ш','щ','ъ','ь','ы','э','ю','я'),
            array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','Р','П','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ь','Ы','Э','Ю','Я'),
            $content
        );
        return strtoupper($content);
    }

    function toLower($content) {
        $content[0] = str_replace(
            array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','Р','П','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ь','Ы','Э','Ю','Я'),
            array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','р','п','с','т','у','ф','х','ц','ч','ш','щ','ъ','ь','ы','э','ю','я'),
            $content[0]
        );
        return strtolower($content);
    }

}

class CDBObject extends CObject{
    var $table = false;
            
    function create() {
        $keys = array(); $values = array();
        if (!empty($this->table) && is_array($this->attributes) && count($this->attributes)) {
            foreach($this->attributes as $key => $value) {
                $keys[] = $key;
                $values[] = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})$/",$value, &$matches) ? $GLOBALS['adodb']->DBDate($value) : $GLOBALS['adodb']->Quote($value);
            }
            if (count($keys) && count($values)) {
                $sql = "INSERT INTO ".$this->table." (".join(',',$keys).") VALUES (".join(',',$values).")";
                if ($res = sql($sql)) {
                    return sqllast();
                }
            }
        }
        return false;        
    } 
    
    function update($id) {
        $values = array();
        if ($id && is_array($this->attributes) && count($this->attributes)) {
            foreach($this->attributes as $key => $value) {
                $values[] = $key.' = '.$GLOBALS['adodb']->Quote($value);
            }
            if (count($values)) {
                $sql = "UPDATE ".$this->table." SET ".join(',',$values)." WHERE ".$id['name']." = ".$GLOBALS['adodb']->Quote($id['value']);
                if (sql($sql)) return true;
            }
        }
        return false;
    }
        
    function get($id, $table, $class) {
        if ($id['name'] && $id['value']) {
            $sql = "SELECT * FROM ".$table." WHERE ".$id['name']." = ".$GLOBALS['adodb']->Quote($id['value']);
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                return new $class($row);
            }
        }
    }

    function get_all($table, $class) {
    	$return = array();
        $sql = "SELECT * FROM ".$table;
        $res = sql($sql);
        while($row = sqlget($res)) {
            $return[] = new $class($row);
        }
        return $return;
    }

    function delete($id) {
        if (is_array($id) && count($id) && $id['value']) {
            $sql = "DELETE FROM ".$this->table." WHERE ".$id['name']." = ".$GLOBALS['adodb']->Quote($id['value']);
            sql($sql);
        }
    }

}
?>