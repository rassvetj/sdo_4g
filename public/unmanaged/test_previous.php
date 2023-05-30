<?php
require_once("1.php");
if($s['test_position'] === (int) $_REQUEST['test_position']){ //#14345
    require_once("test.inc.php");

    if (!$s[login]) exitmsg("Пожалуйста, авторизуйтесь","/?$sess",0,$constTarget);

    if ($s[me]==2) exitmsg("Сеанс тестирования закончен. Не нажимайте кнопку BACK (Назад) в вашем браузере!","test_end.php?$sess",0,$constTarget);
    if ($s[me]!=1) exitmsg("Сеанс тестирования закончен или не начинался.","./?$sess",0,$constTarget);

    //echo $_SESSION['s']['mode'];
    if (empty($checkkod) && $_SESSION['s']['mode']!=1)
       exitmsg("Неправильный вызов страницы.","test_vopros.php?$sess",0,$constTarget); 


    if ($checkkod!=md5(implode(" | ", $_SESSION['s']['ckod'])) && $_SESSION['s']['mode']!=1)
       exitmsg("Не нажимайте кнопки REFRESH (Обновить) или BACK (Назад), иначе вы можете случайно отключиться от сеанса тестирования. Если вы намеренно хотите прервать тест, воспользуйтесь кнопкой 'ПРЕРВАТЬ ТЕСТИРОВАНИЕ' внизу ////страницы.","test_vopros.php?$sess",0,$constTarget);



    if (($_SESSION['s']['mode']==1) && is_array($_SESSION['s']['adone']) && count($_SESSION['s']['adone'])) {
        // Возвращаемся к предыдущим вопросам
        $_SESSION['s']['ckod'] = array();
        $i=0;
        for($j=$_SESSION['s']['test_position']-1;$j>=0;$j--) {
            if ($i>=$_SESSION['s']['qty']) break;
            $kod = $_SESSION['s']['adone'][$j];
            array_unshift($_SESSION['s']['ckod'],$kod);
            array_unshift($_SESSION['s']['aneed'],$kod);
            $_SESSION['s']['test_position']--;
            $i++;
        }
    }

    $t = "{$sitepath}test_vopros.php?vopros=".md5(microtime()).$sess;
}
refresh("{$sitepath}test_vopros.php?vopros=".md5(microtime()).$sess,0,$constTarget);
?>