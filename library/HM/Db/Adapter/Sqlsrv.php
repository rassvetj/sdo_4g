<?php
class HM_Db_Adapter_Sqlsrv extends Zend_Db_Adapter_Sqlsrv
{
    //protected $_defaultStmtClass = 'HM_Db_Statement_Sqlsrv';
    protected $_dateFormatSettingsSet = false;

    public function __construct($config)
    {
        parent::__construct($config);
    }
    
    protected function _setDateFormatSettings()
    {
        if (!$this->_dateFormatSettingsSet && $this->_connection) {
            $this->_dateFormatSettingsSet = true;
            $this->query('SET DATEFORMAT ymd');
            $this->query('SET DATEFIRST 1'); // week starts from Monday
        }
    }
    
    protected function _connect()
    {
        parent::_connect();
        $this->_setDateFormatSettings();
    }

    public function getProcedures()
    {
        return array(
            'UNIX_TIMESTAMP' => 'dbo.UNIX_TIMESTAMP',
            'FROM_UNIXTIME' => 'dbo.FROM_UNIXTIME',
            'PASSWORD' => 'dbo.PASSWORD',
            'GROUP_CONCAT' => 'dbo.GROUP_CONCAT',
            'CONCAT' => 'dbo.CONCAT',
            'LENGTH' => 'DATALENGTH',
            'NOW()' => 'GETDATE()',
            'GREATEST' => 'dbo.GREATEST',
            'LEAST' => 'dbo.LEAST',
            'WEEK(' => 'DATEPART(wk,',
            'HOUR(' => 'DATEPART(hour,',
            'hours24' => 'dbo.hours24',
            'weekday(' => 'DATEPART(weekday,',
            'dbo.GROUP_dbo.CONCAT' => 'dbo.GROUP_CONCAT', // fucking hack
            'LPAD' => 'dbo.LPAD',
            'RPAD' => 'dbo.RPAD'
        );
    }

    public function getReservedWords()
    {
        return array("begin", "end", "log","file","default","list","percent","user", "order", "current", "with", "all", 'from', 'to', 'public', 'show', 'left');
    }

    public function query($sql, $bind = array())
    {
        if ($sql instanceof Zend_Db_Select) {
            $sql = $sql->__toString();
        }
        return parent::query($this->replaceKeywords($sql), $bind);
    }

    protected function _replaceProcedures($sql)
    {
        if ($sql instanceof Zend_Db_Select) {
            $sql = $sql->__toString();
        }

        $procedures = $this->getProcedures();
        foreach($procedures as $procedure => $replace)
        {
            $sql = str_replace($procedure, $replace, $sql);
        }
        return $sql;
    }

    /**
     * @param  string | Zend_Db_Select $sql
     * @return void
     */
    public function replaceKeywords($sql)
    {
        if ($sql instanceof Zend_Db_Select) {
            $sql = $sql->__toString();
        }

        $pattern = "([[:space:]]|[\.,\(\)`=]){1}";
        $keywords = $this->getReservedWords();
        foreach($keywords as $keyword) {
            if (strpos($sql, $keyword) !== false) {
                $replaceKeyword = '['.$keyword.']';
                $sql = ereg_replace("({$pattern}){$keyword}({$pattern})", "\\1{$replaceKeyword}\\3", $sql . " ");                
            }
        }

        $sql = $this->_replaceProcedures($sql);
        return $sql;
    }

    /**
    * Преобразование даты
    *
    * @param unknown_type $data
    * @param unknown_type $len
    * @return unknown|string
    */
    public function _funcDatetime($date, $len = 0)
    {
        $locale = Zend_Locale::findLocale();
        if(strpos($date, ' ') !== false)
        {
            if (Zend_Date::isDate($date,Zend_Locale_Format::getDateTimeFormat($locale), $locale)) {
                $dateObject = new Zend_Date($date, Zend_Locale_Format::getDateTimeFormat($locale));
                return $dateObject->toString('yyyy-MM-dd H:mm:ss');
            } else {
                if (Zend_Date::isDate($date,Zend_Locale_Format::getDateTimeFormat('ru'), 'ru')) {
                    $dateObject = new Zend_Date($date, Zend_Locale_Format::getDateTimeFormat('ru'));
                    return $dateObject->toString('yyyy-MM-dd H:mm:ss');
                }
                return $date;
            }

        }elseif(strpos($date, '-') !== false){
            return $date;
        }else{

            // закомментировал автоопределение формата даты - работает неверно в случае en_US
            // все даты в системе имеют формат dd.MM.YYYY, даже при включенной англ.локали
            // корень проблемы в том, что вместо en_US надо en_GB
            if (0 && Zend_Date::isDate($date, Zend_Locale_Format::getDateFormat($locale), $locale)) {
                $dateObject = new Zend_Date($date, Zend_Locale_Format::getDateFormat($locale));
                return $dateObject->toString('yyyy-MM-dd');
            } else {
                if (Zend_Date::isDate($date,Zend_Locale_Format::getDateFormat('ru'), 'ru')) {
                    $dateObject = new Zend_Date($date, Zend_Locale_Format::getDateFormat('ru'));
                    return $dateObject->toString('yyyy-MM-dd');
                }
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
    
      
/**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param string $sql
     * @param integer $count
     * @param integer $offset OPTIONAL
     * @return string
     * @throws Zend_Db_Adapter_Sqlsrv_Exception
     */
     public function limit($sql, $count, $offset = 0)
     {
        $count = intval($count);
        if ($count <= 0) {
            require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            /** @see Zend_Db_Adapter_Exception */
            require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("LIMIT argument offset=$offset is not valid");
        }

        if ($offset == 0 && false) {
            $sql = preg_replace('/^SELECT\s/i', 'SELECT TOP ' . $count . ' ', $sql);
        } else {
            $orderby = stristr($sql, 'ORDER BY');

            if (!$orderby) {
                $over = 'ORDER BY (SELECT 0)';
            } else {
                $over = preg_replace('/\"[^,]*\".\"([^,]*)\"/i', '"inner_tbl"."$1"', $orderby);
            }

            // Remove ORDER BY clause from $sql
            $sql = preg_replace('/\s+ORDER BY(.*)/', '', $sql);

            // Add ORDER BY clause as an argument for ROW_NUMBER()
            $sql = "SELECT ROW_NUMBER() OVER ($over) AS \"ZEND_DB_ROWNUM\", * FROM ($sql) AS inner_tbl";

            $start = $offset + 1;
            $end = $offset + $count;

            $sql = "WITH outer_tbl AS ($sql) SELECT * FROM outer_tbl WHERE \"ZEND_DB_ROWNUM\" BETWEEN $start AND $end";
            }

        return $sql;
    }
    
    
    
               
}