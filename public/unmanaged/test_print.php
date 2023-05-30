<?php
require_once('1.php');
require_once('test.inc.php');

if (!$s['login']) exitmsg("Пожалуйста, авторизуйтесь","/?$sess");
if ($s['perm']<2) exitmsg("У вас нет прав для обращения к данной странице","/?$sess");

$xslt_template = $wwf.'/template/interface/PlainText/Styles/Gray/course.xsl';
$css_template = $sitepath.'template/interface/PlainText/Styles/Gray/template_style.css';

$tid = (int) $_GET['tid'];
$GLOBALS['controller']->setView('DocumentPrint');
if ($tid) {
    $sql = "SELECT data FROM test WHERE tid='".(int) $tid."'";
    $res = sql($sql);
    if (sqlrows($res) && ($row=sqlget($res))) {
        
        // todo: надо придумать как их удалять из temp
        $tmpdir = tempdir($wwf.'/temp/');
        mkdir($tmpdir . '/files', 0777);
        chmod($tmpdir . '/files', 0777);
        chmod($tmpdir, 0777);
        $tmpdir_rel = str_replace($wwf, '.', $tmpdir . '/files/');
        
        $kods = explode($brtag,$row['data']);
        if (is_array($kods) && count($kods)) {
            prepare_files($tmpdir . '/files', $kods);
            $xml = trim(prepare_xml($kods, false, $tmpdir_rel));
            if (file_exists($xslt_template)) {
                $xslt = file_get_contents($xslt_template);
                $xml = str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>","",$xml);
                $xslt = str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>","",$xslt);
                $arguments = array('/_xml'=>$xml,'/_xslt'=>$xslt);
                if ($xsltproc = xslt_create()) {          
                    $html = xslt_process($xsltproc, 'arg:/_xml', 'arg:/_xslt', NULL, $arguments);
                    $html = "<style type=\"text/css\">@import url(\"{$css_template}\");</style>".$html;
                    xslt_free($xsltproc);
                    $GLOBALS['controller']->captureFromReturn(CONTENT,iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$html));
                }
            }
        }
    }
            
} else {
    $GLOBALS['controller']->setMessage('Не выбрано задание',JS_GO_URL,'javascript:window.close();');
}

$GLOBALS['controller']->terminate();

?>