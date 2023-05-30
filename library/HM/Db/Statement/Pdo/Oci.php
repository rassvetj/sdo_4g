<?php
class HM_Db_Statement_Pdo_Oci extends Zend_Db_Statement_Pdo_Oci
{
    private $_sql = null;
    protected $_lobAsString = true;


    public function __construct($adapter, $sql)
    {
        $this->_sql = $sql;
        parent::__construct($adapter, $sql);
    }

    public function getLobAsString()
    {
        return $this->_lobAsString;
    }

    public function fetch($style = null, $cursor = null, $offset = null)
    {
        $result = parent::fetch($style, $cursor, $offset);

        $result = array_pop($this->_process(array($result)));

        return $result;
    }

    public function fetchAll($style = null, $col = null)
    {
        $result = parent::fetchAll($style, $col);

        $result = $this->_process($result);        
        return $result;
    }

    public function fetchAllRaw($style = null, $col = null)
    {
        return parent::fetchAll($style, $col);
    }

    protected function _process($result)
    {
        $sql = $this->_sql;
        if ($sql instanceof Zend_Db_Select) {
            $sql = $sql->__toString();
        }

        $caseFields = $this->_parseSql($sql);
        $reservedWords = $this->getAdapter()->getReservedWords();
        $tmp = $result;
        if (is_array($result) && count($result)) {
            foreach($result as $index => $values) {
                if (is_array($values) && count($values)) {
                    foreach($values as $key => $value) {

                        if (is_resource($value) && $this->getLobAsString()) {
                            $value = stream_get_contents($value);
                        }

                        $k = $key;
                        if (substr($key, -1) == '_') {
                            if (in_array(substr(strtolower($key), 0, -1), $reservedWords)) {
                                $key = substr($key, 0, -1);
                            }
                        }

                        if (isset($caseFields[$key])) {
                            unset($tmp[$index][$k]);
                            $tmp[$index][$caseFields[$key]] = $value;
			            }
                    }
                }
            }
        }

        return $tmp;
    }

    protected function _parseSql($strSql)
    {
        $arrReturn = array();
        $arrExclude = array('DISTINCT', 'distinct');
        //if (eregi("^select(.+?)from[[:space:]]+([_0-9a-zA-Z]+)",$strSql, $arrMat)) {
        if (preg_match("/select(.+?)from[\s]+([_0-9a-zA-Z]+)/ims",$strSql, $arrMat)) { // experimental, not tested        
           if ((trim($arrMat[1]) == "*") && strlen($arrMat[2])) {
                if (is_array($arrTableFieldNames = $GLOBALS['_arrFieldNames'][$arrMat[2]])) {
                    foreach ($arrTableFieldNames as $strField) {
                        $arrReturn[strtoupper($strField)] = $strField;
                    }
                }
           } else {
               $arrFields = explode(",", $arrMat[1]);
               foreach ($arrFields as $value) {
                    if (count($arrAlias = explode(" as ", $value)) > 1) {
                        $strToken = trim(array_pop($arrAlias));
                    } elseif (count($arrAlias = explode(" AS ", $value)) > 1) {
                        $strToken = trim(array_pop($arrAlias));
                    } else {
                        $strToken = trim(str_replace($arrExclude, '', $value));
                    }
                    $arrFieldName = explode(".", $strToken);
                    $strField = array_pop($arrFieldName);
                    if(trim($strField) == "*") {
                        if (is_array($arrTableFieldNames = $GLOBALS['_arrFieldNames'][$arrMat[2]])) {
                            foreach ($arrTableFieldNames as $strField) {
                                $arrReturn[strtoupper($strField)] = $strField;
                            }
                        }
                    }
                    $arrReturn[strtoupper($strField)] = $strField;
               }
           }
        }
        return $arrReturn;
    }

}