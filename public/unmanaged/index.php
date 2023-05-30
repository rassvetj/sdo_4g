<? //include_once("_dima_timestat.php");
define("INDEX_GB_DAYS", 3);

require_once("1.php");
require_once($wwf."/index.lib.php4");
require_once('news.lib.php4');
require_once("schedule.lib.php");
require_once("positions.lib.php");
require_once('formula_calc.php');

require_once('lib/json/json.lib.php');
$json_callback_function = (json_callback_function_valid($_GET['json_callback'])) ? $_GET['json_callback'] : false;
$json_id = (json_id_valid($_GET['json_id'])) ? $_GET['json_id'] : '';

if ( $json_callback_function ) {
	require_once('lib/json/json.class.php');
}

if (!count($_POST) && !count($_GET)) {
    $GLOBALS['controller']->page_id = PAGE_INDEX;
    $GLOBALS['controller']->persistent_vars->set('page_id', PAGE_INDEX);
}

if ( !$json_callback_function ) {
	$html=create_new_html("top","all");

	$allcontent=(defined("LOCAL_NEWS_TEMPLATE") && LOCAL_NEWS_TEMPLATE) ? loadtmpl("index-main-variant1.html") : loadtmpl("index-main.html");
	$indexstatic=loadtmpl("index-static.html");
	$indexstatic=show_info_block( 0, "[ALL-CONTENT]", "-~home~-"  );// выводит информацию блоками

	$newslow=loadtmpl("index-news.html");
	$indexlogin=loadtmpl("index-login.html");
	$indeximages=loadtmpl("all-images.html");
	if (defined("LOCAL_NEWS_TEMPLATE") && LOCAL_NEWS_TEMPLATE) $indeximages = "<tr>{$indeximages}</tr>";
	$indexwords=loadwords("index-words.html");
}
$lStart=(isset($_POST['start_login'])) ? $_POST['start_login'] : ( (isset($_GET['start_login'])) ? $_GET['start_login'] : "" );
$lExit=(isset($_GET['exit'])) ? $_GET['exit'] : "" ;
$chLevel=(isset($_GET['chLevel'])) ? $_GET['chLevel'] : -1 ;
$json_data['json_id'] = $json_id;

$message = '';
if ($lStart) {
	$_loginData = ( isset($_POST['start_login']) ) ? $_POST : $_GET ;
	$intMaxInvalidLogin = getOption('max_invalid_login');
	$ulogin=(isset($_loginData['login'])) ? tosql($_loginData['login']) : "" ;
	$upass=(isset($_loginData['password'])) ? tosql($_loginData['password']) : "";
	if ($json_callback_function) {
		$ulogin = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$ulogin);
		$upass = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,$upass);
	}

	$message=($intMaxInvalidLogin) ? $indexwords[12] : $indexwords[7];
	$json_data['login_status'] = ($intMaxInvalidLogin) ? 1 : 0;
	/**
	 * Statuses
	 * LOGIN_INVALID				[7 ] = 0
	 * LOGIN_INVALID_TRYES_LIMITED	[12] = 1
	 * LOGIN_INVALID_TRYES_EXCEEDED	[11] = 2
	 * LOGIN_EMPTY_FIELDS			[6 ] = 3
	 * LOGIN_DETAILS_REQUIRED		[  ] = 4
	 * LOGIN_OK						[  ] = 5
	 * LOGIN_LOGGED_OFF				[  ] = -1
	 */
	if ($ulogin && $upass) {
        $s['mid']=user_login($ulogin,$upass);
        if (defined('ENABLE_CHECK_SESSION_EXIST') && ENABLE_CHECK_SESSION_EXIST && is_user_session_exists($s['mid'])) {
            unset($s);
            $dean=0;
            $admin=0;
            $teach=0;
            $stud=0;
            $uinfo = user_fio($s['mid']);
            exitmsg(_("Пользователь")." ".strip_tags($ulogin)." "._("в данный момент уже в системе"),"index.php?exit=1");
            exit();
        }
    }
	else {
		$message=$indexwords[6];
		$json_data['login_status'] = 3;
	}
	if ($s['mid'] == CONST_INVALID_LOGON) {
		$s['mid'] = 0;
		$message=$indexwords[11];
		$json_data['login_status'] = 2;
	} elseif ($s['mid']) {

        /**
        * заносим сессию в бд
        */
        $message = '';

        $sql = "INSERT INTO sessions (mid, start, stop, ip)
                VALUES ('".(int) $s['mid']."',".$adodb->DBTimeStamp(time()).",".$adodb->DBTimeStamp(time()).",'".$_SERVER["REMOTE_ADDR"]."')";
        $res = sql($sql);
        sqlfree($res);
        $s['sessid'] = sqllast();

        $uinfo = user_fio($s['mid']);
        $s['user']['fname']=$uinfo['FirstName'];
        $s['user']['lname']=$uinfo['LastName'];
        $s['user']['patronymic']=$uinfo['Patronymic'];
        $s['user']['email']=$uinfo['Email'];
        $s['user']['meta']['access_level'] = $uinfo['Access_Level'];
        $s['user']['helpAlwaysShow']=$uinfo['Course'];
        $s['blocked'] = $uinfo['blocked'];

	    if ($s['blocked']) {
            unset($s['mid']);
            unset($s);
            $msg = strlen($uinfo['block_message']) ? "<br/><br/>" . _("Администратор указал следующую причину:") . "<br>{$uinfo['block_message']}" : "";
	    	$GLOBALS['controller']->setMessage(_('Ваш аккаунт заблокирован').$msg, JS_GO_URL, $GLOBALS['sitepath']);
	        $GLOBALS['controller']->terminate();
	        exit();
	    }

        //$metadata = read_metadata($uinfo['Information'],'access_level');
        //$s['user']['meta']['access_level'] = $metadata[0]['value'];

        $s['offline_course_path'] = user_offline_courses_paths($s['mid']);
        if (is_array($s['offline_course_path'])) {
            foreach($s['offline_course_path'] as $v) {
                if (!empty($v)) {
                    $s['offline_courses_enabled'] = true;
                    break;
                }
            }
        }
        $s['login']=$ulogin;
        $s['perm']=user_perm($s['mid']);
        if (!$s['perm'] && sqlval("SELECT MID FROM claimants WHERE MID='{$s['mid']}'")) {
            $GLOBALS['controller']->setMessage(_("Ваша заявка ещё не принята."));
            $GLOBALS['controller']->terminate();
            exit();
        } else {
            if (!$s['perm']) {
                $GLOBALS['controller']->setMessage(_("У вас отсутствует роль в системе."));
                $GLOBALS['controller']->terminate();
                exit();
            }
        }
        $json_data['login_status'] = 5;
        if ((!strlen($s['user']['fname'].$s['user']['lname'])) && $s['mid'] && (strpos($_SERVER['PHP_SELF'], "reg.php") === false)) {
            if ( !json_callback_function )
                $controller->setMessage(_("Вам необходимо заполнить форму регистрации на сервере"), JS_GO_URL, $sitepath . 'reg.php4');
            else
				$json_data['login_status'] = 4;
        }

        // Обрабатываем текущие тестирования
        process_online_tests($_SESSION['s']['mid']);
        clean_schedule_locations();

    }
    //      checkForFrames();
    $GLOBALS['controller']->setUser();
	$GLOBALS['controller']->user->alterLang();

    if (isset($GLOBALS['controller']->persistent_vars)) $GLOBALS['controller']->persistent_vars->destroy_all();
	if ( $json_callback_function ) {
		$json = new Services_JSON();
		$json_output = $json->encode($json_data);
		die($json_callback_function.'('.$json_output.')');
	}
}
if ($lExit) {
    user_logout($s['mid'],$s['sessid']);
	unset($s);
	session_unregister('s');
	$dean=0;
	$admin=0;
	$teach=0;
	$stud=0;

	$GLOBALS['controller']->setUser();
	$json_data['login_status'] = -1;
	if ( $json_callback_function ) {
		$json = new Services_JSON();
		$json_output = $json->encode($json_data);
		die($json_callback_function.'('.$json_output.')');
	}
}
$words['news']=$indexwords[0];
$words['fname']=$indexwords[2];
$words['login']=$indexwords[3];
$words['pass']=$indexwords[4];

if ($s['mid'])  {
    if (isset($_GET['lms'])) {
        $s['perm'] = user_perm($s['mid']);
        if (isset($GLOBALS['controller']->persistent_vars)) $GLOBALS['controller']->persistent_vars->destroy_all();
        refresh($GLOBALS['sitepath'].'index.php');
        exit();
    }

    // $s=(isset($_SESSION['s'])) ? $_SESSION['s'] : "";
    // $s - сессия - 3 уровень доступа декана
    $dean=(login_chek($s,$access['d'])) ? 1 : 0;
    // $s - сессия - 4 уровень доступа декана
    $admin=(login_chek($s,$access['a'])) ? 1 : 0;
    // $s - сессия - 2 уровень доступа декана
    $teach=(login_chek($s,$access['t'])) ? 1 : 0;
    // $s - сессия - 1 уровень доступа декана
    $stud=(login_chek($s,$access['s'])) ? 1 : 0;

	if (USE_CMS_INTEGRATION) {
        $metodist  = CMSUser::isMethodologist($s['mid']); //
        $developer = CMSUser::isDeveloper($s['mid']);     //
        $reviewer  = CMSUser::isReviewer($s['mid']);      //

        if (($metodist || $developer || $reviewer) && CMSUser::isOnlyCMSUser($s['mid'])) { //
            refresh($GLOBALS['sitepath'].'cms/index.php?cms'); //
            exit();                                            //
        }                                                      //
    }
    

    if (USE_AT_INTEGRATION) {
        $manager = ATUser::isManager($s['mid']);
        $boss    = ATUser::isBoss($s['mid']);
        $slave   = ATUser::isSlave($s['mid']);
    
        if (($manager || $boss || $slave) && ATUser::isOnlyATUser($s['mid'])) {
            refresh($GLOBALS['sitepath'].'at/index.php?at');
            exit();
        }
    }

    if( ($dean == 0)&&($admin == 0) && ($teach == 0) && ($stud == 0) ) {
        if (USE_SIS_INTEGRATION && !is_structured($s['mid'])) {
        $empty_status = 1;
        $message = _("Вход невозможен. Вам не назначена ни одна роль в системе.");
        unset($s['mid']);
		} elseif (USE_SIS_INTEGRATION) {
			header('Location: sis/index.php?sis' );
	    	exit();
		}
    }
    else {
        $empty_status = 0;
        $words['news']=$indexwords[1];
        $words['uNAME']=$s['user']['fname'];
        $indexlogin=loadtmpl("index-islogin.html");
        $indeximages="";
        $words['fio']=$indexwords[5];
        $words['fname']=$indexwords[8];
        $words['status']=$indexwords[9];
        $words['monitor']=$indexwords[10];
        $maxlevel=user_perm($s['mid']);
        if ($chLevel>-1) {
            $s['perm']=change_level($chLevel,$maxlevel,$s['perm']);
            $GLOBALS['controller']->page_id = PAGE_INDEX;
        }

        $GLOBALS['controller']->setUser();

        // $s - сессия - 3 уровень доступа декана
        $dean=(login_chek($s,$access['d'])) ? 1 : 0;
        // $s - сессия - 4 уровень доступа декана
        $admin=(login_chek($s,$access['a'])) ? 1 : 0;
        // $s - сессия - 2 уровень доступа декана
        $teach=(login_chek($s,$access['t'])) ? 1 : 0;
        // $s - сессия - 1 уровень доступа декана
        $stud=(login_chek($s,$access['s'])) ? 1 : 0;
        $s['tkurs']=array();
        $s['skurs']=array();
        $s['user']['scourse']="";
        if (2==$s['perm']) {
            $s['tkurs']=tCourse_array($s['mid']);
            if (is_array($s['tkurs'])) $s['user']['scourse']=reset($s['tkurs']);
        }
        if (1==$s['perm']) {
            $s['skurs']=sCourse_array($s['mid']);
            if (is_array($s['skurs'])) $s['user']['scourse']=reset($s['skurs']);
        }
        if (3<=$s['perm']) {
            $s['tkurs']=dCourse_array();
            if (is_array($s['tkurs'])) $s['user']['scourse']=reset($s['tkurs']);
        }

        istest();

        if ($s['login'] && $s['mid'] && $s['perm'] == 1) {
            $GLOBALS['controller']->captureFromOb(CONTENT);
            $smarty = new Smarty_els();
            $smarty->assign('sitepath',$sitepath);
            echo $smarty->fetch('informers.tpl');
            $GLOBALS['controller']->captureStop(CONTENT);
            $GLOBALS['controller']->terminate();
            exit();
        }
        
        
        $words['ucLINKS']=cCourse_links($s['perm']);
        $news = show_index_news_table($s['perm']);
        $html=show_cur_html($s['perm']);
        $indexstatic=loadtmpl("index-dinam.html");
        $strChPw = (defined("LOCAL_ALLOW_CHANGE_PW") && LOCAL_ALLOW_CHANGE_PW) ? "<BR><BR><a href='pass.php4'>"._("сменить пароль")." >></a>" : "";
        $ustatus=show_cur_status().$strChPw;
        $indexlogin=$indexstatic;
        if ( $teach && !$admin && is_array($s[tkurs]) )
        $qReag=getInved($s[tkurs]);
        if (!isset($cids) && isset($_SESSION['s'])) {
            if ($teach || $dean || $admin)
            $cids=$s[tkurs];
            else
            $cids=$s[skurs];
        }
        if ( /*!($admin || $dean) && */is_array($cids) ) {// выводит расписание на сегодня
            if ($_SESSION['s']['perm']<=2) {
                $schedule  = schedule(1);
            }
        }
        elseif (!($admin || $dean)) {
            $schedule=_("ВНИМАНИЕ: вы не зарегистрированы ни на одном из курсов!
                        Это значит, что сейчас большинство функций сервера для вас будут недоступны,
                        т.к. они могут выполняться только на каких-либо курсах.<P>
                        Если вы регистрировались на какие-то курсы, но видите данное
                        сообщение, это значит следующее:
                        <ul>
                            <li>вы были отчислены с курсов или обучение было закончено
                            <li>курсы были удалены с сервера
                            <li>курсы были закрыты(*)
                        </ul>
                        <P>
                        Сейчас вы можете связяться с преподавателями курсов (для вашего зачисления),
                        либо дождаться, когда необходимый вам курс будет доступен (возможно сейчас
                        он заблокирован),
                        либо зарегистрироваться на другие курсы.");
        }
        $indexstatic=$schedule;
        $schedule=$qReag;
    }
}
else {

    $news=show_index_news_table( 0 );
    $GLOBALS['controller']->captureFromReturn(NEWS, $news);
    $GLOBALS['controller']->captureFromReturn(CONTENT, $indexstatic);
}

if (strlen($message)){
    $GLOBALS['controller']->setMessage($message);
}

if ($empty_status){
    $GLOBALS['controller']->captureFromReturn(NEWS, $news);
    $GLOBALS['controller']->captureFromReturn(CONTENT, $indexstatic);
}

$GLOBALS['controller']->captureFromReturn("m010101", $indexstatic);

$html=str_replace("[ALL-CONTENT]",$allcontent,$html);
$html=str_replace("[INDEX-LEFT]",$indexstatic,$html);
$html=str_replace("[NEWS-LOW]",$newslow,$html);
$html=str_replace("[INDEX-LOGIN]",$indexlogin,$html);
$html=str_replace("[INDEX-RIGHT]",$indeximages,$html);
$html=str_replace("[NEWS]",$news,$html);
$html=str_replace("[SCHEDULE]",$schedule,$html);

$tmp=writeCurInfo( $s['mid'], time() );
$html=str_replace("[COURSES-info]",$tmp,$html);
$GLOBALS['controller']->captureFromReturn("m010102", $tmp);

$GLOBALS['controller']->captureFromReturn("m010103", $schedule);

if ($GLOBALS['s']['perm']>2) {
	$GLOBALS['controller']->captureFromReturn("m010106", getFlexGraph());
}

$html=str_replace("[MONITORING]",$monitoring,$html);
$html=str_replace("[MDATE]",date("d.m.y"),$html);
$html=str_replace("[L-NAME]",$s['login'],$html);
$html=str_replace("[L-FIO]",$s['user']['fname'],$html);
$html=str_replace("[U-status]",$ustatus,$html);

$welcomeText = _("Добро пожаловать в систему управления обучением");
if (defined('WELCOME_TEXT') && strlen(WELCOME_TEXT)) {
	$welcomeText = WELCOME_TEXT;
}
$GLOBALS['controller']->setHeader($welcomeText);

$html=str_replace("[L-ERROR]",$lError,$html);

$html=str_replace("[PRINT_WEEK_SCHEDULE]","",$html);

if (USE_CMS_INTEGRATION) {
    if ($_SESSION['s']['login'] && $_SESSION['s']['mid']) {         //
        if (!defined('ALLOW_SWITCH_2_CMS')) {                       //
            define('ALLOW_SWITCH_2_CMS', User::allowSwitch2CMS());  //
        }                                                           //
    } 
}

if (USE_SIS_INTEGRATION) {
    if ($_SESSION['s']['login'] && $_SESSION['s']['mid']) {
        if (!defined('ALLOW_SWITCH_2_SIS')) {
            define('ALLOW_SWITCH_2_SIS', User::allowSwitch2SIS());
        }
    }
}

if (USE_AT_INTEGRATION) {
    if ($_SESSION['s']['login'] && $_SESSION['s']['mid']) {

        if (!defined('ALLOW_SWITCH_2_AT')) {
            define('ALLOW_SWITCH_2_AT', User::allowSwitch2AT());
        }
    }
}

/**
* Вывод подчинённых организаций
*/
$subOrganizationList = print_child_organizations_by_mid_no_links($s['mid']);

$html = str_replace("[SUB-ORGANIZATION-LIST]",$subOrganizationList,$html);

if (!empty($subOrganizationList))
$GLOBALS['controller']->captureFromReturn("m010104", $subOrganizationList);

// ===============================================================

/**
 * Объявления
 */
if($s['perm'] == 1) {
    $courses = $s[skurs];
    $arr_posts = array();
    $i = 0;
    $colors = array("#C7D2C2", "#FEFDF5", "#E6DED1");
    $posted = 0;
    /*
    $query = "SELECT DISTINCT posts3.PostID as postid, posts3.posted
              FROM {$guestbook}
              LEFT JOIN posts3_mids ON (posts3_mids.postid=posts3.postid)
              WHERE (";
    $query .= (is_array($courses) && count($courses)) ? "CID IN (" . implode(", ", $courses) . ")" : "CID IN (0)";
    $query .= ") AND (posts3_mids.mid=0 OR posts3_mids.mid='".(int) $_SESSION['s']['mid']."' OR posts3.mid='".(int) $_SESSION['s']['mid']."' OR posts3_mids.postid IS NULL) ";
    $query .= " ORDER BY posted DESC LIMIT {$GuestBookShownRows}";
    $res = sql($query);

    while($row = sqlget($res)) {
    	$ids[] = $row['postid'];
    }

    if (is_array($ids) && count($ids)) {
        $query = "SELECT posts3.PostID as postid, name as name, cid as cid, email as email, text as text, UNIX_TIMESTAMP(posted) as posted, posts3.mid as mid
                  FROM $guestbook
                  WHERE posts3.PostID IN ('".join("','",$ids)."')";
        $query .= "ORDER BY posted DESC LIMIT $GuestBookShownRows";
        $res = sql($query);
    }
    */
$crntCurses = array();
switch ($_SESSION['s']['perm']){
    case 1:
        $crntCurses = (array)$_SESSION['s']['skurs'];
    break;

    case 2:
        $crntCurses = (array)$_SESSION['s']['tkurs'];
    break;

    default:
        $crntCurses = (array)array_merge($_SESSION['s']['skurs'],$_SESSION['s']['tkurs']);
    break;
}

$sql = "SELECT posts.PostID as postid, UNIX_TIMESTAMP(posts.posted) as posted,
               posts.name as name, posts.CID as cid, posts.email as email,
               posts.text as text, posts.mid as mid, posts.startday as startday,
               posts.stopday as stopday
        FROM `posts3` AS posts
        LEFT JOIN `posts3_mids` AS mids ON (mids.postid=posts.PostID)
        LEFT JOIN `Courses` AS courses ON (posts.CID=courses.CID)
        WHERE courses.is_poll = 0
          AND (mids.mid='0'
            OR posts.mid = '".(int) $_SESSION['s']['mid']."' 
            OR mids.mid = '".(int) $_SESSION['s']['mid']."')
          AND (posts.CID IN ('".join("','",$crntCurses)."') )
          OR posts.CID=0
        ORDER BY posted DESC LIMIT $GuestBookShownRows";
$result = sql($sql);

    while($row = sqlget($result)) {
        $posted = (!$posted) ? $row['posted'] : $posted;
        $row['date1'] = date("G:i", $row['posted']);
        $row['date2'] = date("d.m.y", $row['posted']);
	if ($row['cid']) {
        $row['course'] = cid2title($row['cid']);
        $row['course'] = getIcon('note', $row['course']).' '.$row['course'];
	}
        if ($row['posted'] >= time() - INDEX_GB_DAYS*24*60*60) {
          $row['bgcolor'] = "style='background: $colors[2];'";
        }
        $arr_posts[] = $row;
        $i++;
    }

    $tpl = new Smarty_els;
    $tpl->assign("posts", $arr_posts);
    $GLOBALS['controller']->captureFromReturn("m010105", $tpl->fetch("index_gb.tpl"));

    if($posted >= (time()- INDEX_GB_DAYS*24*60*60)) {
        $GLOBALS['controller']->setCurTab('m010105');
    }
}
// =============================================================

/*for($i=1;$i<5;$i++) {
    $lnk=getField("OPTIONS","value","name","link$i");
    $html=str_replace('[LINK'.$i.']',$lnk,$html);
}
*/


checkPermitionToWatch($PHP_SELF);

$html=str_replace_menu( $s['skurs'], $html );
printtmpl($html);
?>