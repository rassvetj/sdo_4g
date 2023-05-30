<?php

define('LAW_REPOSITORY_PATH',$wwf.'/laws');
define('LAW_FILE_MAX_UPLOAD_SIZE',100000000);
define('LAWS_PER_PAGE',30);

$res = sql("SELECT `rtid`,`template_name` FROM `report_templates`");
$law_types = array();
while ($row = sqlget($res)) {
    $law_types[$row['rtid']] = $row['template_name'];
}
/*
$law_types = array(
1 => _("Кадровый приказ"),
2 => _("Производственный приказ"),
3 => _("Нормативный документ"),
4 => _("Рекомендованная технология"),
5 => _("Стандартная форма"),
6 => _("Рекомендованная форма"),
7 => _("Инструкция пользователя"),
8 => _("Прочие документы"),
);
*/
$law_regions = array(
1 => _("Все регионы"),
2 => _("РБ"),
3 => _("РФ"),
4 => _("Украина")
);

class CTextFileIndex {
    
    var $file;
    var $table;
    var $table_words;
    var $words_count;
    var $errors = false;    
    
    function CTextFileIndex($file,$table='laws_index',$table_words='laws_index_words') {
        if (!move_uploaded_file($file['tmp_name'], $GLOBALS['wwf'].'/temp/'.to_translit($file['name'])))
            $this->errors[] = _("Нет файла для индексации")." ".$file['name'];            
        $this->file = $GLOBALS['wwf'].'/temp/'.to_translit($file['name']);
        $this->table = $table;
        $this->table_words = $table_words;
    }
    
    function _is_word_exists($word) {
        $sql = "SELECT id FROM {$this->table_words} WHERE BINARY word='".addslashes($word)."'";
        $res = sql($sql);
        if (sqlrows($res)) {$row = sqlget($res); return $row['id'];}
        return false;
    }
    
    function _save_word($word) {
        $id = $this->_is_word_exists($word);
        if (!$id) {
            $sql = "INSERT INTO {$this->table_words} (word) VALUES ('".addslashes($word)."')";
            $res = sql($sql);
            $id = sqllast();
        }
        return $id;
    }
    
    function save($id,&$words) {
        if ($id>0) {
            sql("DELETE FROM {$this->table} WHERE id='".(int) $id."'");
            while(list($k,$v)=each($words)) {
                if (!empty($k)) {
                    $word = $this->_save_word($k);
                    if ($word>0) {
                        $sql = "INSERT INTO {$this->table} (id,word,count) VALUES ('".(int) $id."','".(int) $word."','".(int) $v."')";
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
        $content = toLower($content);
        $words = explode(" ",$content);
        array_walk($words,'trim3');
        $words = array_count_values($words);
        return $words;
    }
    
    function index($id) {
        if (($id>0) && (!$this->errors) && is_file($this->file)) {
            $content = @file_get_contents($this->file);
            $words = $this->parse($content);
            $this->save($id,$words);
            $this->unlink();
        }
    }
    
    function unlink() {
        @unlink($this->file);
    }
}

function trim3(&$item) {
    if (strlen($item)<3) $item="";
    $item = trim($item);
}

function toUpper($content) {
  $content = strtr($content, "абвгдеёжзийклмнорпстуфхцчшщъьыэюя","АБВГДЕЁЖЗИЙКЛМНОРПСТУФХЦЧШЩЪЬЫЭЮЯ");
  return strtoupper($content);
}

function toLower($content) {
  $content = strtr($content, "АБВГДЕЁЖЗИЙКЛМНОРПСТУФХЦЧШЩЪЬЫЭЮЯ","абвгдеёжзийклмнорпстуфхцчшщъьыэюя");
  return strtolower($content);
}
?>