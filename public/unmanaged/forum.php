<?php
require_once('1.php');
require_once('positions.lib.php');
require_once('lib/classes/MessageFilter.class.php');
require_once('lib/classes/Forum.class.php');
require_once('lib/classes/Person.class.php');
require_once("lib/FCKeditor/fckeditor.php");

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

//header(sprintf('Content-type: text/html; charset=%s;', $GLOBALS['controller']->lang_controller->lang_current->encoding));

// Обработка сущностей
if (isset($_REQUEST['subject']) && (isset($_REQUEST['subject_id']) || isset($_REQUEST['lesson_id']) )) {
    // получаем id категории по сабжекту
    $id      = (isset($_REQUEST['lesson_id']))? (int) $_REQUEST['lesson_id'] : (int) $_REQUEST['subject_id'];
    $catName = (isset($_REQUEST['lesson_id']))?  'lesson' : $_REQUEST['subject'];
    $_REQUEST['category'] = CForumCategory::getCategoryIdBySubject($catName, $id);
    
} else {
    if (!isset($_REQUEST['category']) && !isset($_REQUEST['thread']))
    $_REQUEST['category'] = CForumCategory::getCategoryIdBySubject('collaboration', 0);
}
$forum = new CForumController();

if ( isset($lessonInfo) ) {
    $forum->setLessonInfo($lessonInfo);
}

$forum->init();
$forum->display();

?>