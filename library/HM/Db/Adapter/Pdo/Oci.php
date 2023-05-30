<?php
class HM_Db_Adapter_Pdo_Oci extends Zend_Db_Adapter_Pdo_Oci
{
    protected $_defaultStmtClass = 'HM_Db_Statement_Pdo_Oci';
    protected $_useTriggerForSequence = true;

    public function isUseTriggerForSequence()
    {
        return $this->_useTriggerForSequence;
    }

    public function getReservedWords()
    {
        return array("all", "type","date","default","begin","end","start", "stop", "level", "work", "data", "random", "sort", "skip", "file", "sum", "number", "size", "char","user","uid", "sequence", "access", "mode", "order", "comment", "current", "object");
    }

    public function query($sql, $bind = array())
    {        
        if ($sql instanceof Zend_Db_Select) {
            $sql = $sql->__toString();
        }


        return parent::query($this->_replaceKeywords($sql), $bind);
    }

    /**
     * @param  string | Zend_Db_Select $sql
     * @return void
     */
    protected function _replaceKeywords($sql)
    {
        if ($sql instanceof Zend_Db_Select) {
            $sql = $sql->__toString();
        }

        $pattern = "([[:space:]]|[\.,\(\)`=]){1}";
        $keywords = $this->getReservedWords();
        foreach($keywords as $keyword) {
            if (strpos($sql, $keyword) !== false) {
                $replaceKeyword = $keyword.'_';
                $sql = ereg_replace("({$pattern}){$keyword}({$pattern})", "\\1{$replaceKeyword}\\3", $sql . " ");                
            }
        }

        return $sql;
    }
    
    
    
    
     /**
     * Преобразование даты
     * 
     * @param unknown_type $data
     * @param unknown_type $len
     * @return unknown|string
     */
    public function _funcDate($date, $len = 0)
    {
        $locale = Zend_Locale::findLocale();
        if(strpos($date, ' ') !== false)
        {
            if (Zend_Date::isDate($date,Zend_Locale_Format::getDateTimeFormat($locale), $locale)) {
                $dateObject = new Zend_Date($date, Zend_Locale_Format::getDateTimeFormat($locale));
                return $dateObject->toString('yyyy-MM-dd H:mm:ss');
            } else {
                return $date;
            }

        }elseif(strpos($date, '-') !== false){
            return $date;
        }else{

            if (Zend_Date::isDate($date, Zend_Locale_Format::getDateFormat($locale), $locale)) {
                $dateObject = new Zend_Date($date, Zend_Locale_Format::getDateFormat($locale));
                return $dateObject->toString('yyyy-MM-dd');
            } else {
                return $date;
            }


        }

        return $date;

         
    }
    
    /**
     * Преобразование строки с учетом ее длины
     * 
     * @param unknown_type $data
     * @param unknown_type $len
     * @return string
     */
    public function _funcVarchar($data, $len = 0)
    {
        if(strlen($data) > $len){
            $data = substr($data, 0, $len);
        }
       
        return $data;
    }
    
    /**
     * Преобразование числа
     * 
     * @param unknown_type $data
     * @param unknown_type $len
     * @return string
     */
    public function _funcInt($data, $len = 0)
    {
        return intval($data);
    }
    
    
    
    
   

}