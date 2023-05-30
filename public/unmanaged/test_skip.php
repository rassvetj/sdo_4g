<?php
require_once("1.php");
require_once("test.inc.php");

if (!$s[login]) exitmsg("Пожалуйста, авторизуйтесь","/?$sess",0,$constTarget);

if ($s[me]==2) exitmsg("Сеанс тестирования закончен. Не нажимайте кнопку BACK (Назад) в вашем браузере!","test_end.php?$sess",0,$constTarget);
if ($s[me]!=1) exitmsg("Сеанс тестирования закончен или не начинался.","./?$sess",0,$constTarget);

/**
 * setting checkkod from REQUEST DATA if not setted earlier
 */
if(!isset($checkkod) && isset($_REQUEST['checkkod'])){
    $checkkod = $_REQUEST['checkkod'];
}

if (!isset($checkkod))
   exitmsg("Неправильный вызов страницы.","test_vopros.php?$sess",0,$constTarget);
if ($checkkod!=md5(implode(" | ", $_SESSION['s']['ckod'])))
   exitmsg("Не нажимайте кнопки REFRESH (Обновить) или BACK (Назад), иначе вы можете случайно отключиться от сеанса тестирования. Если вы намеренно хотите прервать тест, воспользуйтесь кнопкой 'ПРЕРВАТЬ ТЕСТИРОВАНИЕ' внизу страницы.","test_vopros.php?$sess",0,$constTarget);
      
if (($_SESSION['s']['mode']==2) && is_array($_SESSION['s']['ckod']) && count($_SESSION['s']['ckod'])) {
    foreach($_SESSION['s']['ckod'] as $kod) {
        $key = array_search($kod,$_SESSION['s']['aneed']);
        if ($key!==false) {
            unset($_SESSION['s']['aneed'][$key]);
        }
        $_SESSION['s']['aneed'][] = $kod;
    }
}

if ($_SESSION['s']['mode']==2) {
    $_SESSION['s']['ckod'] = array();
    
    test_switch();
}

   
refresh("{$sitepath}test_vopros.php?vopros=".md5(microtime()).$sess,0,$constTarget);
?>