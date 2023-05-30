<?php
require_once('1.php');
require_once('lib/PEAR/Archive/Zip.php');
require_once("lib/classes/xml2array.class.php");
require_once('test.inc.php');
require_once('lib/classes/Result.class.php');

$smarty = new Smarty_els();

$smarty->assign('OKBUTTON',okbutton());
$smarty->assign('SITEPATH',$sitepath);

switch($action) {
    case 'import':
        if (isset($_FILES['results']) 
            && !empty($_FILES['results']['name'])) {
            if (move_uploaded_file($_FILES['results']['tmp_name'],$wwf.'/temp/'.$_FILES['results']['name'])) {
                if ($path = CResults::unpack($wwf.'/temp/'.$_FILES['results']['name'],$wwf.'/temp')) {                   
                    // Парсинг xml файлов
                    $errorFileNames = array();
                    $sheids = CResults::parseResults($path, $errorFileNames);
                    @unlink($wwf.'/temp/'.$_FILES['results']['name']);
                    if (!count($errorFileNames)) {                                                               
                        MessageBox(_('Результаты успешно импортированы'));
                    } else {
                        MessageBox(sprintf(_('Произошли ошибки при импорте файлов: %s Остальные данные загружены успешно.'), '<br>'.join('<br>', $errorFileNames).'<br>'));                        
                    }
                    
                    if (is_array($sheids) && count($sheids)) {
                        $sql = "SELECT title, SHEID FROM schedule WHERE SHEID IN ('".join("','",$sheids)."')";
                        $res = sql($sql);
                        while($row = sqlget($res)) {
                            $reslt[$row['SHEID']] = $row['title'];
                        }
                    }
                }
                @unlink($wwf.'/temp/'.$_FILES['results']['name']);
            }
        } else {
            MessageBox('Укажите файл результатов (zip)', 'offline_results_import.php');
        }

        $smarty->assign('results',$reslt);
        $html = $smarty->fetch('offline_results_import_report.tpl');
    break;
    default:
        $html = $smarty->fetch('offline_results_import.tpl');
    break;
}

$GLOBALS['controller']->captureFromReturn(CONTENT,$html);
$GLOBALS['controller']->terminate();

function MessageBox($message,$redirect='') {
    if (!empty($redirect)) {
        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage($message,JS_GO_URL, $redirect);        
        $GLOBALS['controller']->terminate();
        exit();    
    } else {
        $GLOBALS['controller']->setMessage($message);
    }
}

?>