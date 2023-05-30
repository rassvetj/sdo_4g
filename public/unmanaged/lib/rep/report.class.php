<?php

class CReport {

    var $data, $filterData, $commonCalcData, $additionalData, $sort;

    var $enable_counter, $disable_sort, $disable_filter, $enable_group = false;

    var $print;

    var $workbook, $worksheet;

    var $minCommonTD=999999;

    var $fieldTypes;

    var $plots;

    function CReport($enable_counter,$disable_sort,$disable_filter,$enable_group) {

        $this->enable_counter = (boolean) $enable_counter;
        $this->disable_sort = (boolean) $disable_sort;
        $this->disable_filter = (boolean) $disable_filter;
        $this->enable_group = (boolean) $enable_group;

    }

    function _setPlots($plots) {
        $this->plots = $plots;
        unset($_SESSION['s']['report']['current']['plots']['process']);
    }

    function setData($data, $headers=false, $fields=false, $filterData=false, $commonCalcData=false, $additionalData=false, $sort=false, $fieldTypes=false, $plots=false) {

        $this->data['data'] = $data;
        $this->data['headers'] = $headers;
        $this->data['fields'] = $fields;

        $this->filterData = $filterData;
        $this->commonCalcData = $commonCalcData;
        $this->additionalData = $additionalData;

        if (is_array($fieldTypes))
        foreach($fieldTypes as $v)
        if (isset($v['type'])) $this->fieldTypes[$v['field']] = $v['type'];

        $this->sort = $sort;

        $this->_setPlots($plots);

    }

    function getAdditionalData() {

        return $this->additionalData;

    }

    function getPlots() {
        $ret = false;
        if (is_array($this->plots) && count($this->plots)) {
            foreach($this->plots as $order => $plot) {
                if (!is_array($plot['process']) || !count($plot['process'])) continue;

                $url = '';
                switch($plot['type']) {
                    case 'line':
                    case 'bar':
                    case 'pie':
                    case 'pie3d':
                    case 'radar':
                        if (is_array($this->data['data']) && count($this->data['data'])) {
                            $plot['data'] = array();
                            foreach($plot['process'] as $process) {
                                $data = array();
                                foreach($this->data['data'] as $piece) {
                                    if (isset($process['xfield'])) {
                                        $data['x'][] = $piece[$process['xfield']];
                                    }
                                    if (isset($process['yfield'])) {
                                        $data['y'][] = $piece[$process['yfield']];
                                    }
                                }

                                $data['color'] = $process['color'];
                                if (isset($process['legend'])) {
                                    $data['legend'] = $process['legend'];
                                }

                                if (isset($process['fill'])) {
                                    $data['fill'] = $process['fill'];
                                }

                                $plot['data'][] = $data;
                            }
                            $url = $GLOBALS['sitepath']."lib/rep/plots/{$plot['type']}.php?plot=$order";
                        }
                    break;
                }

                if (strlen($url)) {
                    $ret[] = array('url' => $url);
                }

                $_SESSION['s']['report']['current']['plots']['process'][$order] = $plot;

            }
        }
        return $ret;
    }

    function getTable($print=false) {

        $this->print = $print;

        $ret = '';

        /**
        * Вывод заголовков таблицы результатов
        */
        if (is_array($this->data['headers'])) {

            $ret .= $this->getHeadRow($this->enable_counter);

        }

        /**
        * Вывод колонки фильтров
        */
        if (!$print && !$this->disable_filter && is_array($this->data['headers'])) {

            $ret .= $this->getFilterRow($this->enable_counter);

        }

        if (is_array($this->data['data']) && count($this->data['data'])) {

            reset($this->data['data']);

            $j=1;

            $fieldsAttributes = array();
            while(list($k,$data) = each($this->data['data'])) {
                if ($this->enable_group) {
                    //for($fieldNumber=0; $fieldNumber < count($this->data['fields']);$fieldNumber++) {

                        $field = $this->data['fields'][abs($this->sort) - 1];

                        if (isset($fieldsAttributes[$field]['count'])) {
                            if ($k > $fieldsAttributes[$field]['count']) {
                                unset($fieldsAttributes[$field]);
                            } else {
                                $fieldsAttributes[$field]['hide'] = true;
                            }
                        }

                        if (!isset($fieldsAttributes[$field]['hide'])) {
                            $rowspan = 0;
                            while($data[$field] == $this->data['data'][$k+$rowspan+1][$field]) {
                                $rowspan++;
                                if ($k+$rowspan+1 > count($this->data['data'])) break;
                            }
                            if ($rowspan>0) {
                                $fieldsAttributes[$field]['rowspan'] = $rowspan + 1;
                                $fieldsAttributes[$field]['count'] = $k+$rowspan;
                            }
                        }

                    //}
                }
                $ret .= $this->getDataRow($data,$this->enable_counter,$j,$fieldsAttributes);

                $j++;
            }

//            $ret .= $this->getCommonMaxRow($this->enable_counter);
//            $ret .= $this->getCommonMinRow($this->enable_counter);
//            $ret .= $this->getCommonAvgRow($this->enable_counter);
//            $ret .= $this->getCommonSumRow($this->enable_counter);
            $max = $this->getCommonMaxRow($this->enable_counter);
            $min = $this->getCommonMinRow($this->enable_counter);
            $avg = $this->getCommonAvgRow($this->enable_counter);
            $sum = $this->getCommonSumRow($this->enable_counter);

            if ($max && is_array($max) && count($max)) $ret .= $this->printCommonRow($max,_("Максимум:")." ");
            if ($min && is_array($min) && count($min)) $ret .= $this->printCommonRow($min,_("Минимум:")." ");
            if ($avg && is_array($avg) && count($avg)) $ret .= $this->printCommonRow($avg,_("Среднее:")." ");
            if ($sum && is_array($sum) && count($sum)) $ret .= $this->printCommonRow($sum,_("Сумма:")." ");

        }


        return $ret;
    }

    function printCommonRow($res,$name) {

        $ret = '';

        $ret .= '<tr>';
        $ret .= '<td align=right colspan='.$this->minCommonTD.'>'.$name.'</td>';
        for($i=$this->minCommonTD;$i<=count($this->data['fields']);$i++) {

            $ret .= '<td>';
            if (isset($res[$i])) $ret .= (int) $res[$i];
            else $ret .= '&nbsp;';
            $ret .= '</td>';

        }
        $ret .= '</tr>';

        return $ret;

    }

    function getCommonMaxRow($enable_counter=false) {

        return $this->getCommonRow('max',$enable_counter);

    }

    function getCommonMinRow($enable_counter=false) {

        return $this->getCommonRow('min',$enable_counter);

    }

    function getCommonAvgRow($enable_counter=false) {

        return $this->getCommonRow('avg',$enable_counter);

    }

    function getCommonSumRow($enable_counter=false) {

        return $this->getCommonRow('sum',$enable_counter);

    }

    function getCommonRow($action,$enable_counter=false) {

        switch($action) {
                case 'max':
                    $actionName = _("Максимум:");
                break;
                case 'min':
                    $actionName = _("Минимум:");
                break;
                case 'avg':
                    $actionName = _("Среднее:");
                break;
                case 'sum':
                    $actionName = _("Сумма:");
                break;
        }

        $colspan = (int) count($this->data['fields']) + $enable_counter;
        //$ret .= "<tr><td colspan=$colspan>&nbsp;</td></tr>\n";
        //$ret .= "<tr><th colspan=$colspan>$actionName:</th></tr>\n";
        //$ret .= "<tr>\n";
        //if ($enable_counter) $ret .= "<td></td>";
        $i=0;
        if ($enable_counter) $i++;
        foreach($this->data['fields'] as $v) {

            //$ret .= "<td>";
            if (isset($this->commonCalcData[$v][$action])) {
                if ($action=='avg') {
                    if ($this->commonCalcData[$v]['count'])
                    $ret[$i] = (int) ($this->commonCalcData[$v]['avg'] / $this->commonCalcData[$v]['count']);
                } else
                $ret[$i] = (int) $this->commonCalcData[$v][$action];

                $flag=true;
            }

            if ($flag && ($i<$this->minCommonTD)) $this->minCommonTD = $i;

            //$ret .= "</td>";
            $i++;
        }
        //$ret .= "</tr>\n";

        if (!$flag) $ret = false;

        return $ret;

    }

    /*
    function getCommonRow($action,$enable_counter=false) {

        switch($action) {
                case 'max':
                    $actionName = "Максимум:";
                break;
                case 'min':
                    $actionName = "Минимум:";
                break;
                case 'avg':
                    $actionName = "Среднее:";
                break;
                case 'sum':
                    $actionName = "Сумма:";
                break;
        }

        $colspan = (int) count($this->data['fields']) + $enable_counter;
        $ret .= "<tr><td colspan=$colspan>&nbsp;</td></tr>\n";
        $ret .= "<tr><th colspan=$colspan>$actionName:</th></tr>\n";
        $ret .= "<tr>\n";
        if ($enable_counter) $ret .= "<td></td>";
        foreach($this->data['fields'] as $v) {

            $ret .= "<td>";
            if (isset($this->commonCalcData[$v][$action])) {
                if ($action=='avg') {
                    if ($this->commonCalcData[$v]['count'])
                    $ret.= (int) ($this->commonCalcData[$v]['avg'] / $this->commonCalcData[$v]['count']);
                } else
                $ret.= (int) $this->commonCalcData[$v][$action];

                $flag=true;
            }
            $ret .= "</td>";

        }
        $ret .= "</tr>\n";

        if (!$flag) $ret = '';

        return $ret;

    }
    */

    function getHeadRow($enable_counter=false) {

        global $sitepath, $s;

        $ret .= "<tr>\n";
        if ($enable_counter) $ret .= "<th>#</th>";
        $i=1;
        foreach($this->data['headers'] as $v) {
            $ret .= "<th valign=top>";
            $j=$i;
            if (abs($this->sort) == $i) $j = 0 - $this->sort;
            if (!$this->print) $ret .= "<a href=\"{$sitepath}rep.php?type={$s['reports']['current']['type']}&step=3&sort=".(int) $j."\">";
            $ret .= $v;
            if (!$this->print) $ret .="</a>";
            if (abs($this->sort) == $i) {
                if ($this->sort>0) $ret .= "<img src=\"{$sitepath}images/sort_down.gif\" border=0>";
                else $ret .= "<img src=\"{$sitepath}images/sort_up.gif\" border=0>";
            }
            $ret .= "</th>";
            $i++;
        }
        $ret .= "</tr>\n";

        return $ret;

    }

    function getDataRow($data,$enable_counter=false, $counter_count=1, $fieldsAttributes = array()) {

        $ret .= "<tr>\n";

        if ($enable_counter) $ret .= "<td>".(int) $counter_count."</td>";

        if (is_array($this->data['fields']))
        foreach($this->data['fields'] as $v) {
            if (isset($fieldsAttributes[$v]['hide'])) continue;
            if (empty($data[$v]) || ($data[$v]==' ')) $data[$v] = '&nbsp;';
            if (!strlen(trim($data[$v]))) $data[$v] = '&nbsp;';
            $ret .= "<td";
            if (isset($fieldsAttributes[$v]['rowspan'])) $ret .= " rowspan=".(int) $fieldsAttributes[$v]['rowspan'];
            $ret .= ">";
            if (isset($this->fieldTypes[$v])) {

                switch ($this->fieldTypes[$v]) {

                    case 'integer':
                    $ret .= (int) $data[$v];
                    break;
                    case 'double':
                    $ret .= (double) $data[$v];
                    break;
                    case 'boolean':
                    $ret .= (boolean) $data[$v];
                    break;
                    default:
                    $ret .= $data[$v];
                    break;

                }

            } else $ret .= $data[$v];
            $ret .= "</td>";
        }
        else
        foreach($data as $k=>$v) {
            if (empty($v)) $v = '&nbsp;';
            $ret .= "<td>$v</td>";
        }
        $ret .= "</tr>\n";

        return $ret;

    }

    function getFilterRow($enable_counter=false) {

        $show = '';
        if (!is_array($this->filterData)) $show = "style='display: none;'";
        $ret .= "<tr id='filters_rep' $show>\n";
        if ($enable_counter) $ret .= "<th></th>";
        $i=1;
        foreach($this->data['fields'] as $v) {
            $ret .= "<th><input id='filter_$i' style='width=100%' type=\"text\" name=\"filter_$v\" value='".$this->filterData[$v]."'>&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('report_filter') . "</th>";
            $i++;
        }
        $ret .= "</tr>\n";

        return $ret;
    }

    function getExcelFile($name="elearn.xls",$reportName="") {

        /**
        * IMPORTANT - NEED Spreadsheet_WriteExcel!
        */
        ob_end_clean();

        /**
         * cache hack
         */
        $name = "report_".preg_replace(array("/[\s\.,]/","/[()]/"),array("_",''),to_translit($reportName))."_".date('Y_m_d_H_i_s').".xls";

        $this->getExcelHeader($name);

        // Creating a workbook
        mb_internal_encoding("Windows-1251"); // do not touch. it must be one byte encoding
        $this->workbook = new Spreadsheet_Excel_Writer_Workbook("-");
        $this->workbook->setVersion(8);
        // Creating the first worksheet
        $this->worksheet =& $this->workbook->addWorksheet('Report');
        $this->worksheet->setInputEncoding($GLOBALS['controller']->lang_controller->lang_current->encoding);

        $j=0;

        $j = $this->getExcelReportTitle($reportName,$j); $j++;

        $j = $this->getExcelSubjectArea($j);

        $this->getExcelHeadRow($this->enable_counter,$j++);

        if (is_array($this->data['data']) && count($this->data['data'])) {

            reset($this->data['data']);

            $counter = 1;

            while(list(,$data) = each($this->data['data'])) {

                $this->prepareExcelData($data);

                $ret .= $this->getExcelDataRow($data,$this->enable_counter,$counter++,$j++);

            }

            $j = $this->getExcelCommonMaxRow($this->enable_counter,$j);
            $j = $this->getExcelCommonMinRow($this->enable_counter,$j);
            $j = $this->getExcelCommonAvgRow($this->enable_counter,$j);
            $j = $this->getExcelCommonSumRow($this->enable_counter,$j);

        }


        $this->workbook->close();
    }

    function getExcelHeader($filename) {
/*        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$filename" );
        header("Expires: 0");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");*/

        header("Content-type: application/excel");
        header('Content-Disposition: attachment; filename="' . $filename);
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Pragma: public");
        header("Content-Transfer-Encoding: binary");

    }

    function getExcelBorderCell($style=1,$bold=0) {

        if (isset($GLOBALS[md5($style.$bold)])) {
            return $GLOBALS[md5($style.$bold)];
        }

        $format =& $this->workbook->addFormat();
        $format->setBorder($style);
        $format->setBold($bold);

        $GLOBALS[md5($style.$bold)] = $format;

        return $format;

    }

    function getExcelFormat($size=false,$bold=false,$align=false,$color=false, $pattern=false, $fg_color=false) {

        if (isset($GLOBALS[md5($size.$bold.$align.$color.$pattern.$fg_color)])) {
            return $GLOBALS[md5($size.$bold.$align.$color.$pattern.$fg_color)];
        }

        $format =& $this->workbook->addFormat();
        if ($size) $format->setSize((int) $size);
        if ($bold) $format->setBold((int) $bold);
        if ($align) $format->setAlign($align);
        if ($color) $format->setColor($color);
        if ($pattern) $format->setPattern($pattern);
        if ($fg_color) $format->setFgColor($fg_color);
        
        $GLOBALS[md5($size.$bold.$align.$color.$pattern.$fg_color)] = $format;

        return $format;

    }

    function getExcelReportTitle($reportName,$col) {

        if (!empty($reportName))
        $this->worksheet->writeString($col++, 0, $reportName,$this->getExcelFormat(11,1));

        return $col;

    }

    function getExcelSubjectArea($col) {

        global $s, $reports;

        $subjectArea = getSubjectArea($reports[$s['reports']['current']['name']]['input_fields'],$s['reports']['current']['inputData'],getReportInputForm($s['reports']['current']['name'],$s['reports']['current']['inputData']));
        $i=$col;

        $this->worksheet->writeString($i++, 0, _("Автор отчета:").' '.$s['user']['lname'].' '.$s['user']['fname']);
        $this->worksheet->writeString($i++, 0, _("Дата создания:").' '.date("d.m.Y"));

        if ($subjectArea) {
            foreach($subjectArea as $key=>$value) {
                $this->worksheet->write($i++, 0, $key.': '.$value);
            }

        }

        if ($this->additionalData && is_array($this->additionalData) && count($this->additionalData)) {

            foreach($this->additionalData as $key=>$value) {
                $this->worksheet->write($i++, 0, $key.': '.$value);
            }

        }

        return ($i+1);

    }

    function getExcelHeadRow($enable_counter=false, $col=1) {

        global $sitepath, $s;

        $format = $this->getExcelFormat(false,1,'center');

        $i=1;
        if ($enable_counter) {
            $this->worksheet->writeString($col, 1, "#",$this->getExcelBorderCell(1,1));
            $i=2;
        }

        foreach($this->data['headers'] as $v) {
            $this->worksheet->writeString($col,$i, strip_tags($v),$this->getExcelBorderCell(1,1));
            $i++;
        }

    }

    function prepareExcelData($data) {
        $data = str_replace(array('&nbsp;','<br>'),' ',$data);
        $data = str_replace('&quot;',"\"",$data);
        if (is_array($data) && count($data)) {
            foreach($data as $key => $value) {
                $data[$key] = strip_tags($value);                
            }
        }
        return $data;
    }

    function getExcelDataRow($data,$enable_counter=false, $counter, $col) {
        $i=1;
        if ($enable_counter) {
            $this->worksheet->writeNumber($col, 1, (int) $counter,$this->getExcelBorderCell());
            $i=2;
        }

        if (is_array($this->data['fields']))
        foreach($this->data['fields'] as $v) {
            $this->worksheet->write($col,$i,$this->prepareExcelData($data[$v]),$this->getExcelBorderCell());
            $i++;
        }
        else
        foreach($data as $k=>$v) {
            $this->worksheet->write($col,$i,$this->prepareExcelData($v),$this->getExcelBorderCell());
            $i++;
        }
    }

    function getExcelCommonMaxRow($enable_counter=false,$col) {

        return $this->getExcelCommonRow('max',$enable_counter,$col);

    }

    function getExcelCommonMinRow($enable_counter=false,$col) {

        return $this->getExcelCommonRow('min',$enable_counter,$col);

    }

    function getExcelCommonAvgRow($enable_counter=false,$col) {

        return $this->getExcelCommonRow('avg',$enable_counter,$col);

    }

    function getExcelCommonSumRow($enable_counter=false,$col) {

        return $this->getExcelCommonRow('sum',$enable_counter,$col);

    }

    function getExcelCommonRow($action, $enable_counter=false,$col) {

        $i=1;
        if ($enable_counter) $cell[$i++] = "";

        foreach($this->data['fields'] as $v) {

            if (isset($this->commonCalcData[$v][$action])) {
                if ($action=='avg') {
                    if ($this->commonCalcData[$v]['count'])
                    $cell[$i] = (int) ($this->commonCalcData[$v]['avg'] / $this->commonCalcData[$v]['count']);
                }
                else
                $cell[$i]= (int) $this->commonCalcData[$v][$action];

                $flag = true;
            } else $cell[$i] = "";

            if ($flag && ($i<$this->minCommonTD)) $this->minCommonTD = $i;

            $i++;

        }

        if ($flag) {
            switch($action) {
                case 'max':
                    $actionName = _("Максимум:");
                break;
                case 'min':
                    $actionName = _("Минимум:");
                break;
                case 'avg':
                    $actionName = _("Среднее:");
                break;
                case 'sum':
                    $actionName = _("Сумма:");
                break;
            }

            for($i=1;$i<=(count($this->data['fields'])+1);$i++) {

                $this->worksheet->write($col,$i,(int) $cell[$i], $this->getExcelBorderCell());

            }

            $this->worksheet->writeString($col, (int) ($this->minCommonTD-1), $actionName, $this->getExcelBorderCell());

            return $col+1;

        }
        else return $col;

    }


}
?>