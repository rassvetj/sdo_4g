<?php
class HM_Db_Statement_Oracle extends Zend_Db_Statement_Oracle
{
    private $_sql = null;


    public function __construct($adapter, $sql)
    {
        $this->_sql = $sql;
        parent::__construct($adapter, $sql);
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

                        $k = $key;
                        if (substr($key, -1) == '_') {
                            if (in_array(substr(strtolower($key), 0, -1), $reservedWords)) {
                                $key = substr($key, 0, -1);
                            }
                        }
                       
                        if (isset($caseFields[$key])) {
                            unset($tmp[$index][$k]);
                            $tmp[$index][$caseFields[$key]] = $value;
			            } else {
                            if (isset($caseFields[$k])) { // experimental feature
                                unset($tmp[$index][$k]);
                                $tmp[$index][substr($caseFields[$k], 0, -1)] = $value;
                            }
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
                   $alias = false;
                    if (count($arrAlias = explode(" as ", $value)) > 1) {
                        $alias = true;
                        $strToken = trim(array_pop($arrAlias));
                    } elseif (count($arrAlias = explode(" AS ", $value)) > 1) {
                        $alias = true;
                        $strToken = trim(array_pop($arrAlias));
                    } else {
                        $strToken = trim(str_replace($arrExclude, '', $value));
                    }
                    $arrFieldName = explode(".", $strToken);
                    $strField = array_pop($arrFieldName);
                    if(trim($strField) == "*") {
                        $strTableName = array_pop($arrFieldName);
                        if (strlen($strTableName) && is_array($arrTableFieldNames = $GLOBALS['_arrFieldNames'][$strTableName])) {
                            if (is_array($arrTableFieldNames = $GLOBALS['_arrFieldNames'][$strTableName])) {
                                foreach ($arrTableFieldNames as $strField) {
                                    $arrReturn[strtoupper($strField)] = $strField;
                                }
                            }
                        } else {
                            if (is_array($arrTableFieldNames = $GLOBALS['_arrFieldNames'][$arrMat[2]])) {
                                foreach ($arrTableFieldNames as $strField) {
                                    $arrReturn[strtoupper($strField)] = $strField;
                                }
                            }
                        }
                    } elseif (strlen($arrMat[2]) && !$alias) {

                        if (is_array($arrTableFieldNames = $GLOBALS['_arrFieldNames'][$arrMat[2]])) {
                            foreach ($arrTableFieldNames as $field) {
                                if (strtoupper($strField) == strtoupper($field)) {
                                    $arrReturn[strtoupper($strField)] = $field;                                    
                                    continue 2;
                                }
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