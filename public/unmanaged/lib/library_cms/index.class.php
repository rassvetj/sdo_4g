<?php

class CIndexerDispatcher {
    function factory($file) {
        $pathInfo = pathinfo($file);
        if (isset($pathInfo['extension']) && strlen($pathInfo['extension'])) {
            
            if (in_array($pathInfo['extension'], array('html', 'htm', 'xhtml'))) {
                return new CIndexerHtmlFile($file);
            }

            if (in_array($pathInfo['extension'], array('xml'))) {
                return new CIndexerXmlFile($file);
            }

            if (in_array($pathInfo['extension'], array('txt'))) {
                return new CIndexerTextFile($file);
            }
            
        }
            return false;
            
    }
}

class CIndexerTextFile {
    
    var $file;
    var $words = array();
    var $words_count;
    var $errors = false;    
    
    function CIndexerTextFile($file) {
        if (!file_exists($file) || !is_readable($file)) {
            $this->errors[] = _("Нет файла для индексации")." ".$file;            
        }
        $this->file = $file;
    }
        
    function _is_word_exists($word) {
        $sql = "SELECT id FROM library_cms_index_words WHERE BINARY word=".$GLOBALS['adodb']->Quote($word);
        $res = sql($sql);
        if (sqlrows($res)) {$row = sqlget($res); return $row['id'];}
        return false;
    }
    
    function _save_word($word) {
        //$id = $this->_is_word_exists($word);
        if (isset($this->words[$word])) {
            $id = $this->words[$word];
        }
        if (!$id) {
            $sql = "INSERT INTO library_cms_index_words (word) VALUES (".$GLOBALS['adodb']->Quote($word).")";
            $res = sql($sql);
            $id = sqllast();
        }
        return $id;
    }
    
    function clean_index($id) {
        sql("DELETE FROM library_cms_index WHERE id='".(int) $id."'");
        CIndexerTextFile::_clean_words($id);
    }
    
    function _clean_words($id) {
        $wordsId = array();
        $sql = "SELECT word FROM library_cms_index WHERE id = '".(int) $id."'";
        $res = sql($sql);
        
        while($row = sqlget($res)) {
            $wordsId[$row['word']] = $row['word'];
        }
        
        if (count($wordsId)) {
            $sql = "SELECT word FROM library_cms_index WHERE id <> '".(int) $id."'";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                if (isset($wordsId[$row['word']])) unset($words[$row['word']]);
            }
        }
                

        if (count($wordsId)) {
            $where = '';
            $wordsId = array_chunk($wordsId, 50);
            for($i=0;$i<count($wordsId);$i++) {
                if ($i>0) {
                    $where .= ' OR ';
                }
                $where .= "id IN ('".join("','", $wordsId[$i])."')";
            }
            sql("DELETE FROM library_cms_index_words WHERE $where");
        }
        
}
    
    function save($id,&$words) {
        if ($id>0) {
                        
            $this->clean_index($id);
            
            $sql = "SELECT * FROM library_cms_index_words";
            $res = sql($sql);
            
            while($row = sqlget($res)) {
                $this->words[$row['word']] = $row['id'];
            }
            while(list($k,$v)=each($words)) {
                if (!empty($k)) {
                    $word = $this->_save_word($k);
                    if ($word>0) {
                        $sql = "INSERT INTO library_cms_index (id,word,count) VALUES ('".(int) $id."','".(int) $word."','".(int) $v."')";
                        sql($sql);
                    }
                }
            }
        }
    }
        
    function parse(&$content) {
        $content = str_replace(array("\n","\t")," ", $content);
        $content = str_replace("\r","", $content);
        $content = preg_replace("/[^0-9a-zA-Zа-яА-ЯёЁ]/"," ",$content);
        //setlocale(LC_ALL, 'ru_RU.CP1251');
        //$content = strtolower($content);
        $content = CObject::toLower($content);
        $words = explode(" ",$content);
        array_walk($words,'trim3');
        $words = array_count_values($words);
        return $words;
    }
    
    function index($id, $unlink = true) {
        if (($id>0) && (!$this->errors) && is_file($this->file)) {
            if ($content = @file_get_contents($this->file)) {
                $words = $this->parse($content);
                $this->save($id,$words);
            }
            if ($unlink) {
                $this->unlink();
            }
        }
    }
    
    function unlink() {
        @unlink($this->file);
    }
}

class CIndexerHtmlFile extends CIndexerTextFile {
    
    var $charsetPatterns = array();
    
    function _init_charsetPatterns() {
        $this->charsetPatterns[] = "/\\<meta.+?charset *?= *?[\"'](.+?)[\"'].*?\\>/isu";
        $this->charsetPatterns[] = "/\\<meta.+?charset *?= *?[\"'](.+?)[\"'].*?\\>/is";        
    }

    function convert_charset(&$content) {
        $charset = '';
        
        $this->_init_charsetPatterns();
                
        foreach($this->charsetPatterns as $pattern) {
            if (preg_match($pattern, $content, &$matches)) {
                if (strlen($matches[1])) {
                    $charset = $matches[1];
                    break;
                }
            }
        }
                
        if (strlen($charset)) {
            $converted = iconv($charset, $GLOBALS['controller']->lang_controller->lang_current->encoding, $content);
        }
        
        if (!empty($converted)) {
            $content = $converted;
        }
        
    }   

    function parse(&$content) {
        $this->convert_charset($content);
        $content = strip_tags($content);
        return parent::parse($content);
    }
    
}

class CIndexerXmlFile extends CIndexerHtmlFile {
    
    function _init_charsetPatterns() {
        $this->charsetPatterns[] = "/\\?xml.+?encoding *?= *?[\"'](.+?)[\"']/isu";
        $this->charsetPatterns[] = "/\\?xml.+?encoding *?= *?[\"'](.+?)[\"']/is";        
    }
    
}


function trim3(&$item) {
    if (strlen($item)<=3) $item="";
    if (preg_match('/[0-9]+/', $item, $matches)) $item = "";
    $item = trim($item);
}

?>