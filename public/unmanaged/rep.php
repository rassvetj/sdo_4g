<?php

require_once('1.php');
require_once('lib/rep/report.lib.php');
require_once('lib/rep/report.class.php');
require_once('lib/rep/reportData.class.php');
require_once('metadata.lib.php');

//require_once('lib/excel/Worksheet.php');
//require_once('lib/excel/Workbook.php');
require_once('Spreadsheet/Excel/Writer/Worksheet.php');
require_once('Spreadsheet/Excel/Writer/Workbook.php');

if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM)
require_once('lib/classes/Credits.class.php');

if ($step>1){
    $GLOBALS['controller']->setHelpSection('step'.(int)$step);
}
if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if (!$dean) login_error();

$reportType = isset($_GET['type']) ? (int) $_GET['type'] : 0;

$smarty = new Smarty_els();

$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;

if (($reportType == 5) || (($reports[$s['reports']['current']['name']]['type'] == 5) && !isset($_GET['type']))) {
    $GLOBALS['controller']->setView('DocumentPopup');
    $GLOBALS['controller']->setHeader(_('Результаты'));
    $GLOBALS['controller']->enableNavigation();
    $GLOBALS['controller']->view_root->disableBreadCrumbs();

/*
    $GLOBALS['controller']->setLink('m070602');
    $GLOBALS['controller']->setLink('m070603');
    $GLOBALS['controller']->setLink('m070604');
*/
    $GLOBALS['controller']->setTab('m070610');

    if (isset($_SESSION['s']['orgstructure']['current']) && $_SESSION['s']['orgstructure']['current']) {
        $_id = $_SESSION['s']['orgstructure']['current'];
        $GLOBALS['controller']->setTab('m070611', array('href' => "orgstructure_main.php?id=$_id"));
        $GLOBALS['controller']->setTab('m070612', array('href' => "orgstructure_main.php?id=$_id&type=add"));
    }

    $GLOBALS['controller']->setTab('m070613');
    $GLOBALS['controller']->setTab('m070614');
    $GLOBALS['controller']->setCurTab('m070614');
}

/**
* Визуализация щагов и отправка данных шаблону
*/

switch ($step) {

    /**
    * Страница выбора отчета
    */
    case 1:
        $GLOBALS['controller']->setSubHeader(_('Шаг 1. Выбор типа отчёта'));
        if (!isset($_GET['type'])) refresh("{$sitepath}index.php");

        $s['reports']['current'] = false;
        $s['reports']['current']['type'] = $reportType;

        $smarty->assign('rep_types', array($reportType));
        
        //mggpu'fix
        $reps = array();
        if ($GLOBALS['s']['reports']['current']['type'] == 6){
            $sql = "SELECT report_name, template_name FROM report_templates";
            $res = sql($sql);
            while ($row = sqlget($res)){
                $reps[$row['template_name']] = $reports[$row['report_name']];
                $reps[$row['template_name']]['name'] = $row['template_name'];
                $reps[$row['template_name']]['title'] = $row['template_name'];
            }
        }else{
            $reps = $reports;
        }
        
        $smarty->assign('reports',$reps);
        pr($s['reports']);
    break;

    /**
    * Страница ввода входных данных
    */
    case 2:
        $GLOBALS['controller']->setSubHeader(_('Шаг 2. Ввод входных данных'));
        if (isset($_GET) && is_array($_GET) && count($_GET)) {
            $inputData_GET = parseInputData($_GET,$reports[$s['reports']['current']['name']]['input_fields']);
            if ($inputData_GET) $s['reports']['current']['inputData'] = $inputData_GET;
        }

        //mggpu'fix
        if ($GLOBALS['s']['reports']['current']['type'] == 6 && isset($_POST['reportName'])){
            $sql = "SELECT report_name FROM report_templates WHERE template_name = '".trim($_POST['reportName'])."'";
            $res = sql($sql);
            $row = sqlget($res);
            $s['reports']['current']['template_name'] = $_POST['reportName'];
            if (isset($row['report_name'])) $_POST['reportName'] = $row['report_name'];
        }
            
        $reportName = isset($_POST['reportName']) ? trim($_POST['reportName']) : '';

        if (!isset($s['reports']['current']['name']) && $reportName) $s['reports']['current']['name'] = $reportName;

        if (isset($s['reports'][$reportName])) $s['reports']['current'] = $s['reports'][$reportName];

        $reportInput = isset($reports[$s['reports']['current']['name']]['input']) ? (boolean) $reports[$s['reports']['current']['name']]['input'] : false;

        $commonDataFields = getCommonDataFields($reports[$s['reports']['current']['name']]['fields']);

        if (/*!empty($reportName) &&*/ !$commonDataFields && !$reportInput) refresh("{$sitepath}rep.php?type={$s['reports']['current']['type']}&step=3");

        $smarty->assign('commonCalcFields',$s['reports']['current']['commonCalcFields']);
//        $smarty->assign('header',ph(_("Выберите данные для отчета")));

        /**
        * Для построения формы ввода входных данных
        */
        //$s['reports']['current']['inputDataValues'] = getReportInputForm($s['reports']['current']['name'],$s['reports']['current']['inputData']);
        $inputDataValues = getReportInputForm($s['reports']['current']['name'],$s['reports']['current']['inputData']);

        $sajax_javascript = '';
        $reportFilterFunctions = getReportFilterFunctions($s['reports']['current']['name']);
            if (is_array($reportFilterFunctions) && count($reportFilterFunctions)) {
            require_once('lib/sajax/SajaxWrapper.php');
            $sajax_javascript = CSajaxWrapper::init($reportFilterFunctions);
        }
        $smarty->assign('sajax_javascript',$sajax_javascript);

//        pr($s['reports']['current']['inputData']);
//        die();
        $smarty->assign('inputData',$s['reports']['current']['inputData']);
        $smarty->assign('inputFields',$reports[$s['reports']['current']['name']]['input_fields']);
        $smarty->assign('inputDataForm',$inputDataValues);

        //        $smarty->assign('html',getReportInputForm($s['reports']['current']['name'],$s['reports']['current']['inputData']));
        $smarty->assign('commonDataFields',$commonDataFields);
    break;

    /**
    * Страница вывода результатов (самого отчета)
    */
    case 3:
        $GLOBALS['controller']->setSubHeader(_('Шаг 3. Предварительный просмотр отчёта'));
        if (isset($_GET['sort'])) {

//            if ($s['reports']['current']['sort'] == (int) $_GET['sort'])
//            $s['reports']['current'][''] =
            $s['reports']['current']['sort'] = (int) $_GET['sort'];

        }

        /**
        * Обработка reportInputData
        */

        if (isset($_POST) && is_array($_POST) && count($_POST)) {

            if ($_POST['laststep'] == 2) {

                $s['reports']['current']['inputData'] = parseInputData($_POST,$reports[$s['reports']['current']['name']]['input_fields']);

                /**
                * commonCalcFields
                */
                if (isset($_POST['commonCalc'])) $s['reports']['current']['commonCalcFields'] = $_POST['commonCalc'];
                else $s['reports']['current']['commonCalcFields'] = false;

            }
            else
            $s['reports']['current']['filterData'] = parseFilterData($_POST,$reports[$s['reports']['current']['name']]['fields']);

        }

        $reportName = ph($reports[$s['reports']['current']['name']]['title']);

        if ($report = getReport()) {
            $reportResults     = $report->getTable();
            $additionalResults = $report->getAdditionalData();
            $plots             = $report->getPlots();            
        }

        $reportInput = isset($reports[$s['reports']['current']['name']]['input']) ? (boolean) $reports[$s['reports']['current']['name']]['input'] : false;
        if ($reportInput || getCommonDataFields($reports[$s['reports']['current']['name']]['fields'])) $prev=2; else $prev=1;
        $smarty->assign('prev',$prev);

        $s['reports'][$s['reports']['current']['name']] = $s['reports']['current'];

    break;

    case 4:
        /**
        * Вывод на печать
        */
        //mgppu'fix эта часть третьего шага тут ибо третий шаг мы пропустили
        if (isset($_POST) && is_array($_POST) && count($_POST)) {

            if ($_POST['laststep'] == 2) {

                $s['reports']['current']['inputData'] = parseInputData($_POST,$reports[$s['reports']['current']['name']]['input_fields']);

                /**
                * commonCalcFields
                */
                if (isset($_POST['commonCalc'])) $s['reports']['current']['commonCalcFields'] = $_POST['commonCalc'];
                else $s['reports']['current']['commonCalcFields'] = false;

            }
            else
            $s['reports']['current']['filterData'] = parseFilterData($_POST,$reports[$s['reports']['current']['name']]['fields']);

        }
        
        $reportName = $reports[$s['reports']['current']['name']]['title'];

        if ($report = getReport()) {
            $reportResults     = $report->getTable(true);
            $additionalResults = $report->getAdditionalData();            
        }
        $smarty->assign('step',4);

    break;

    case 5:
        /**
        * Сохранение отчета в excel
        */
        $reportName = $reports[$s['reports']['current']['name']]['title'];
        if ($report = getReport()) {
            $reportResults = $report->getExcelFile('elearn.xls',$reportName);
        }
        exit();

    break;

}

$smarty->assign('subjectArea',getSubjectArea($reports[$s['reports']['current']['name']]['input_fields'],$s['reports']['current']['inputData'],getReportInputForm($s['reports']['current']['name'],$s['reports']['current']['inputData'])));
$smarty->assign('additionalResults',$additionalResults);
$smarty->assign('plots', $plots);
$smarty->assign('reportName',$reportName);
$smarty->assign('reportResults',$reportResults);
$smarty->assign('report',$report);
$smarty->assign('repData',$report->data);
$smarty->assign('submit', okbutton(_('Далее')/*.' &#8594;'*/));
$smarty->assign('cancel', button(/*'&#8592; '.*/_('Назад'), '', 'cancel', "document.location.href=\"{$sitepath}rep.php?type={$reportType}&step=".($step-1)."\"; return false;"));
$smarty->assign('main', button(_('Главная'), '', 'cancel', "document.location.href=\"{$sitepath}rep.php?type={$reportType}&step=1\"; return false;"));
$smarty->assign('print', button(_('Печать'), '', 'print', "wopen(\"{$sitepath}rep.php?type={$reportType}&step=4\")"));
$smarty->assign('save', button(_('Экспорт в Excel'), '', 'save', "document.location.href=\"{$sitepath}rep.php?type={$reportType}&step=5\"; return false;"));

$smarty->assign('sitepath',$sitepath);
$smarty->assign('s',$s);

//выбор произвольного шаблона
$tplName = "rep/rep_".$_SESSION['s']['reports']['current']['name']."_step_".(int) $step.".tpl";
if (!file_exists($smarty->template_dir.'/'.$tplName)){
    $tplName = "rep/rep_type_".(int)$reportType."_step_".(int)$step.".tpl";
    if (!file_exists($smarty->template_dir.'/'.$tplName)) $tplName = "rep/rep_step_".(int) $step.".tpl";
}

//mgppu'fix
if ($step == 4 && $GLOBALS['s']['reports']['current']['type'] == 6){
    $sql = "SELECT rtid, template 
            FROM report_templates 
            WHERE report_name = '".$_SESSION['s']['reports']['current']['name']."'
              AND template_name = '".$_SESSION['s']['reports']['current']['template_name']."'";
    $row = sqlget(sql($sql));
    if ($row['template']) {
        foreach ($report->data['fields'] as $field) {
            $row['template'] = str_replace("[".strtoupper($field)."]",$report->data['data'][0][$field],$row['template']);
        }
        echo "<script language=\"JavaScript\" type=\"text/javascript\">
                <!--
                window.open('$sitepath/reportActionForm.php','_blank','height=15,width=490,left=150,top=150,location=no,menubar=no,scrollbars=no,resizable=no,status=no,titlebar=no');
                // -->
             </script>";
        echo $row['template'] = "<link rel=\"stylesheet\" href=\"{$GLOBALS['strPathRelative']}/styles/report.css\" type=\"text/css\">".$row['template'];
               
        
        //данные для сохранения отчётов
        $_SESSION['reportTmpData']['reportName'] = $reportName;
        $_SESSION['reportTmpData']['template']  = $row['template'];       
        $_SESSION['reportTmpData']['rtid']  = $row['rtid'];                
        
    }else {
$smarty->display($tplName);
    }
}else {
    $smarty->display($tplName);
}

?>