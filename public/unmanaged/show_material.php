<?php
require_once('1.php');
require_once($GLOBALS['wwf'].'/lib/classes/CourseContent.class.php');

if (!$_SESSION['s']['mid'] || !$_SESSION['s']['login']) {
    exitmsg(_("Пожалуйста, авторизуйтесь"),$GLOBALS['sitepath']);
}

$id     = (int) $_GET['id'];
$cid    = (int) $_GET['cid'];
$run_id = (int) $_GET['run_id'];
$bid    = (int) $_GET['bid'];

$item = false;
if ($id) {
    $item = CCourseItemIrkut::get($id);
}

if ($run_id) {
    $item = array(
        'module' => 0,
        'cid'    => $cid,
        'oid'    => 0,
        'vol2'   => $run_id
    );
}

if ($bid) {
    $item = array(
        'module' => $bid,
        'cid'    => $cid,
        'oid'    => 0,
        'vol2'   => 0
    );
}

if ($item) {
    
    $smarty = new Smarty_els();

    if (defined('ENABLE_EAUTHOR_COURSE_NAVIGATION') && ENABLE_EAUTHOR_COURSE_NAVIGATION) {
        $use_external_navigation = 'true';
    }
    else {
        $use_external_navigation = 'false';
    }
    $block_content_copy      = 'false';
    $condition = ($_SESSION['s']['perm']==1) && DISABLE_COPY_MATERIAL;
    if ($condition){
        $block_content_copy = 'true';
    }

    $smarty->assign('item', $item);
    $smarty->assign('condition', $condition);
    $smarty->assign('use_external_navigation', $use_external_navigation);
    $smarty->assign('block_content_copy', $block_content_copy);
    $smarty->assign('sitepath', $sitepath);
    echo $smarty->fetch('show_material.tpl');
    exit();
}

exitmsg(_('Модуль не существует'), $GLOBALS['sitepath']);

?>