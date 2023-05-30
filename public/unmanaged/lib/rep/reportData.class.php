<?php

class CReportData {
    
    var $data;
    
    var $headers, $fields, $selects;
    var $inputFields, $inputData;
    var $filterData, $filterDataBackup, $commonCalcFields, $commonCalcData=false;
    var $sort, $sort_after_query;
    var $additionalData=false;
    var $dontSort = array();
    
    /**
    * Конструктор
    */
    function CReportData($fields,$inputFields=false,$inputData=false,$filterData=false,$commonCalcFields=false,$sort=false,$sort_after_query=false) {

        $this->parseFields($fields);
                
        $this->parseInputData($inputFields, $inputData);
        
        // Если не стандартная таблица то используется
        $this->filterDataBackup = $filterData;
        
        $this->parseFilterData($filterData);                
        
        $this->commonCalcFields = $commonCalcFields;
                
        $this->sort = $sort;
        
        $this->sort_after_query = $sort_after_query;
                
    }
    /**
    * Обрабатывает массив данных отчета
    * запускается из дочернего класса
    * производит фильтрацию данных, жёсткую (программную сортировку) если нуно,
    * вычисляет агрегатные функции по необходимым полям (max, min, sum, avg)
    */
    function getReportData($data) {
        
        $ret = $data;
        /**
        * Фильтрация
        */
        if (is_array($data) && count($data) 
        && is_array($this->filterData) && count($this->filterData)) {
            
            reset($data);
            $ret = false;
            
            while(list(,$v) = each($data)) {
                
                $good = 1;
                
                foreach($this->filterData as $filterField=>$filterValue) {
                    
                    switch($filterValue[0]) {
                        case '=':
                            if (trim(substr($filterValue,1)) != trim($v[$filterField])) $good = 0;
                            break;
                        case '<':
                            if (trim(substr($filterValue,1)) <= trim($v[$filterField])) $good = 0;
                            //if ((double) trim(substr($filterValue,1)) <= (double) trim($v[$filterField])) $good = 0;
                            break;
                        case '>':
                            if (trim(substr($filterValue,1)) >= trim($v[$filterField])) $good = 0;
                            //if ((double) trim(substr($filterValue,1)) >= (double) trim($v[$filterField])) $good = 0;
                            break;
                        case '!':
                            if ($filterValue[1] == '=') {
                                if (trim(substr($filterValue,2)) == trim($v[$filterField])) $good = 0;                                
                            }
                            break;
                        default:
                            $pattern = mask2pattern(trim($filterValue));
                            if (preg_match($pattern,trim($v[$filterField])) <= 0) $good=0;
                            // if ($v[$filterField] != $filterValue) $good=0;
                            
                    }
                                        
                }
                
                if ($good) $ret[] = $v;
                
            }
            
        }
        
        /**
        * Жесткая сортировка
        */
                
        if (isset($this->fields[abs($this->sort) - 1]) && !in_array($this->fields[abs($this->sort)-1],$this->dontSort)
        && $this->sort_after_query) {
            switch($this->types[$this->fields[abs($this->sort) - 1]]) {
                case 'datetime':
                case 'date':
                    $ret = $this->prepareDates($ret, $this->fields[abs($this->sort) -1],$this->formats[$this->fields[abs($this->sort) - 1]]);
                    $ret = $this->columnSort($ret, 'system:timestamp', $this->sort);
                break;
                default:
                    $ret = $this->columnSort($ret,$this->getSortField(),$this->sort);                    
            }
        }
               
        $this->data = $ret;
        
        // Max, Min, Avg, Sum
        $commonCalcFields = $this->commonCalcFields;
        if (is_array($ret) && (is_array($commonCalcFields['max']) ||
            is_array($commonCalcFields['min']) ||
            is_array($commonCalcFields['avg']) ||
            is_array($commonCalcFields['sum']))) {

            reset($ret);

            while(list(,$v) = each($ret)) {
                
                // MAXIMUM
                if (is_array($commonCalcFields['max']))
                foreach($commonCalcFields['max'] as $key=>$value) {
                    
                    if (isset($v[$key])) {
                                                                        
                        $commonCalcData[$key]['max'] = ($v[$key]>$commonCalcData[$key]['max']) ? (int) $v[$key] : (int) $commonCalcData[$key]['max'];
                        
                    }
                    
                }
                
                // MINIMUM
                if (is_array($commonCalcFields['min']))
                foreach($commonCalcFields['min'] as $key=>$value) {
                    
                    if (isset($v[$key])) {
                        
                        if (!isset($commonCalcData[$key]['min'])) $commonCalcData[$key]['min'] = $v[$key];
                                                                        
                        $commonCalcData[$key]['min'] = ($v[$key]<$commonCalcData[$key]['min']) ? (int) $v[$key] : (int) $commonCalcData[$key]['min'];
                        
                    }
                    
                }

                // AVG
                if (is_array($commonCalcFields['avg']))
                foreach($commonCalcFields['avg'] as $key=>$value) {
                    
                    if (isset($v[$key])) {
                                                                        
                        $commonCalcData[$key]['avg'] += $v[$key];
                        $commonCalcData[$key]['count']++;
                    }
                    
                }

                // SUM
                if (is_array($commonCalcFields['sum']))
                foreach($commonCalcFields['sum'] as $key=>$value) {
                    
                    if (isset($v[$key])) {
                                                                        
                        $commonCalcData[$key]['sum'] += $v[$key];
                        
                    }
                    
                }
                
            }                        

        $this->commonCalcData = $commonCalcData;
            
        }
                

        return $ret;        
        
    }
    
    function parseDate($date, $format) {
        if( !preg_match_all( "/%([YmdHMsu])([^%])*/", $format, $formatTokens, PREG_SET_ORDER ) ) {
            return false;
        }
        foreach( $formatTokens as $formatToken ) {
                $delimiter = preg_quote( $formatToken[2], "/" );
            if($formatToken[1] == 'Y') {
                $datePattern .= '(.{1,4})'.$delimiter;
            } elseif($formatToken[1] == 'u') {
                $datePattern .= '(.{1,5})'.$delimiter;
            } else {
                $datePattern .= '(.{1,2})'.$delimiter;
            } 
        }

        // Splits up the given $date
        if( !preg_match( "/".$datePattern."/", $date, $dateTokens) ) {
            return false;
        }
        $dateSegments = array();
        for($i = 0; $i < count($formatTokens); $i++) {
            $dateSegments[$formatTokens[$i][1]] = $dateTokens[$i+1];
        }
 
        // Reformats the given $date into rfc3339
 
        if( $dateSegments["Y"] && $dateSegments["m"] && $dateSegments["d"] ) {
            if( ! checkdate ( $dateSegments["m"], $dateSegments["d"], $dateSegments["Y"] )) { return false; }
                $dateReformated =
                str_pad($dateSegments["Y"], 4, '0', STR_PAD_LEFT)
                ."-".str_pad($dateSegments["m"], 2, '0', STR_PAD_LEFT)
                ."-".str_pad($dateSegments["d"], 2, '0', STR_PAD_LEFT);
            } else {
                return false;
            }
            if( $dateSegments["H"] && $dateSegments["M"] ) {
                $dateReformated .=
                "T".str_pad($dateSegments["H"], 2, '0', STR_PAD_LEFT)
                .':'.str_pad($dateSegments["M"], 2, '0', STR_PAD_LEFT);
     
                if( $dateSegments["s"] ) {
                    $dateReformated .=
                    ":".str_pad($dateSegments["s"], 2, '0', STR_PAD_LEFT);
                    if( $dateSegments["u"] ) {
                        $dateReformated .=
                        '.'.str_pad($dateSegments["u"], 5, '0', STR_PAD_RIGHT);
                    }
                }
            }

        return $dateReformated;
    }
    
    function prepareDates($data, $field, $format) {
        if (!empty($format) && !empty($field)) {
            for($i=0;$i<count($data);$i++) {
                $data[$i]['system:timestamp'] = strtotime($this->parseDate($data[$i][$field],$format));
            }
        }
        return $data;
    }
    
    /**
    * Возвращает строку: SELECT (строка) FROM
    * формируется по 'select'
    */
    function getSQLSelectString() {
        
        $ret = join(',',$this->selects);
        
        return $ret;
        
    }
    
    /**
    * Возвращает строку WHERE в запрос
    */
    function getSQLWhereString($arrFields=false) {
        
        $ret = '';
        
        /**
        * inputData
        */
        foreach($this->inputFields as $k=>$v) {
        
            if (!$arrFields || (in_array($k,$arrFields))) {
            
                if (isset($this->inputData[$k]) && ($this->inputData[$k]!=$v['useless'])) {
                
                    if (!empty($ret)) $ret .= ' AND ';
                    $ret .= "$k {$v['relation']} ".$GLOBALS['adodb']->Quote($this->inputData[$k])."";
                
                }
            
            }
        
        }            
        
        if (!empty($ret)) $ret = 'WHERE '.$ret;
        
        return $ret;
        
    }
       
    function getSQLOrderString($fields=false) {
        
        $ret = '';
        
        if (isset($this->fields[abs($this->sort) - 1])) {

            $field = $this->fields[abs($this->sort) - 1];
            
            if (!$fields || (in_array($field,$fields))) {
            
                $ret .= " ORDER BY $field ";
                if ($this->sort < 0) $ret .= "DESC ";
            
            }
            

        }

        return $ret;
        
    }
        
    /**
    * Возвращает массив с данными для вывода отчета
    */
    function getData() {
     
        return $this->data;
        
    }
    
    /**
    * Возвращает массив данных фильтров
    */
    function getFilterData() {
        
        return $this->filterData;
        
    }    
    
    /**
    * Возвращает массив входных данных
    */
    function getInputData($arrFields=false) {
        
        $ret = false;
        
        foreach($this->inputFields as $k=>$v) {
        
            if (!$arrFields || (in_array($k,$arrFields))) {
            
                    $ret[$k] = $this->inputData[$k];
                
            }
            
        }
                        
        return $ret;
        
    }
    
    /**
    * Возвращает массив содержащий дополнительную инфу по отчету
    * которая помещается в шапку
    */
    function getAdditionalData() {
        
        return $this->additionalData;
        
    }
    
    function getCommonCalcData() {
        
        return $this->commonCalcData;
        
    }
    
    /**
    * Парсит опции отчета
    */
    function parseFields($fields) {
        
        $this->headers = false;
        $this->fields = false;
        $this->selects = false;
        if (is_array($fields) && count($fields)) {
        
            foreach($fields as $k=>$v) {
                
                //if ($v['type'] == 'date') $this->dontSort[] = $v['field'];
                
                if (!isset($v['type'])) $v['type'] = 'string';
                $this->types[$v['field']] = $v['type'];
                
                if (isset($v['format'])) $this->formats[$v['field']] = $v['format'];
                
                $this->headers[] = $k;
                $this->fields[] = $v['field'];
                $this->selects[] = $v['select'];
                
            }
            
        }
        
    }
    
    function parseInputData($inputFields, $inputData) {
        
        $ret = false;
        
        if (is_array($inputFields) && count($inputFields) 
            && is_array($inputData) && count($inputData)) {
                
                $this->inputFields = $inputFields;
                
                foreach($inputFields as $field=>$v) {
                    
                    if (isset($inputData[$field])) $ret[$field] = $inputData[$field];
                    
                }
                
        }
        
        $this->inputData = $ret;
        
    }
    
    function parseFilterData($filterData) {

        $ret = false;
                
        if (is_array($filterData) && count($filterData) 
            && is_array($this->fields) && count($this->fields)) {
                
                foreach($this->fields as $v) {
                    
                    if (isset($filterData[$v])) {
                        
                        $ret[$v] = $filterData[$v];
                        
                    }
                    
                }
                
        }
        
        $this->filterData = $ret;
        
    }
    
    /**
    * Возвращает названия заголовков данных
    */
    function getHeaders() {
        
        return $this->headers;
        
    }

    function getFields() {
        
        return $this->fields;
        
    }
    
    function getSortField() {

        return $this->fields[abs($this->sort)-1];
        
    }
    
    function columnSort($unsorted, $column, $direction) {
        $sorted = $unsorted;
        for ($i=0; $i < sizeof($sorted)-1; $i++) {
            for ($j=0; $j<sizeof($sorted)-1-$i; $j++)
                if ($direction > 0) {
                if ($sorted[$j][$column] > $sorted[$j+1][$column]) {
                $tmp = $sorted[$j];
                $sorted[$j] = $sorted[$j+1];
                $sorted[$j+1] = $tmp;
                }
            } else {
                if ($sorted[$j][$column] < $sorted[$j+1][$column]) {
                $tmp = $sorted[$j];
                $sorted[$j] = $sorted[$j+1];
                $sorted[$j+1] = $tmp;
                }                
            }
        }
        return $sorted;
    }    
    
            
}

?>