<?
require_once('1.php');
require_once('courses.lib.php');
require_once('news.lib.php4');
require_once("metadata.lib.php");
require_once("lib/classes/Credits.class.php");
require_once("lib/classes/Chain.class.php");

//$GLOBALS['controller']->page_id=PAGE_INDEX;
$GLOBALS['controller']->setHelpSection('order');

//берём все курсы на которые подписан пользователь (не только активные)
$sql = "SELECT CID
        FROM Students
        WHERE MID = '{$GLOBALS['s']['mid']}'";
$res = sql($sql);
$GLOBALS['s']['allSkurs'] = $GLOBALS['s']['skurs'];
while ($row = sqlget($res)) {
    $GLOBALS['s']['allSkurs'][$row['CID']] = $row['CID'];
}


function show_header() {
   $name=_("Направления, специальности и курсы");
   $static=show_info_block( 0, "[ALL-CONTENT]", "-~courses~-"  );// выводит информацию блоками
   $courses_header=loadtmpl("all-cHeader.html");
   $courses_header=str_replace("[W-PAGESTATIC]",$static,$courses_header);
   $courses_header=str_replace("[W-PAGENAME]",$name,$courses_header);
   return $courses_header;
}

function is_course4order($arr = array()) {
    global $s;
    if ($arr['type']) return false;
    switch ($arr['TypeDes']) {
        case "-1":
            return false;
        break;

        case "0":
            return true;
        break;

        default:
            $mids = array();
            $chain_items = CChainItems::get_as_array($arr['TypeDes']);
            if (is_array($chain_items) && count($chain_items)) {
                foreach($chain_items as $k=>$v) {
                    $mids = CChainItems::get_subject($arr['CID'], $s['mid'], $v);
                    if (!(intval($mids) || (is_array($mids) && count($mids)))) {
                        return false;
                    }
                }
            }
            return true;
        break;
    }
}

function all_tracks_list( $mode=1 ){
//   $tmp=ph("Специальности");
   $res=sql("SELECT * FROM tracks WHERE status>0 ORDER BY trid","errGR73");
   $k=0;
   while ( $r=sqlget($res) ) {
      if( $r['id'] !="" )
        $kod="( ".$r['id']." )";
      else
       $kod="";
      if( $r[status] || $mode ){
         $tmp.=getTrackCourses($r['trid'], $r['name']." $kod ", TRUE, $r['description'], true);
         $tmp.="<P/>";
      }
     $k++;
   }
   sqlfree( $res );
   $ph=ph(_("Специальности"));
   return( $ph.$tmp);
}

function all_course_list( $title="Учебные курсы", $tracks="all", $categories=false) {
      global $coursestable;
      $clist=loadtmpl("courses-lowcourses.html");
      $line=loadtmpl("courses-1allline.html");
      if($tracks!="all"){ // "notracks"
          $tr=getTracksIdList();
      }
      $result=@sql("SELECT *
                    FROM ".$coursestable." c
                    WHERE c.Status>1 AND
                          c.TypeDes>-1 AND
                          c.cEnd  >= NOW()
                          ".((is_array($categories) && count($categories)) ? " AND (c.did LIKE '%;".implode(";%' OR c did LIKE '%;",$categories).";%')":"AND (did LIKE '' OR did = NULL)")."
                          ".((is_array($GLOBALS['s']['allSkurs']) && count($GLOBALS['s']['allSkurs'])) ? "AND c.CID NOT IN ('".join("','", $GLOBALS['s']['allSkurs'])."')" : '')."
                    ORDER BY Title"
                   );                   
      $k=0;
      while ($res=sqlget($result)) {
        if( !isInTrackList( $tr, $res[CID]) ) {
        if (is_course4order($res)) {
            $k++;
            $tmp=$line;

            $str = "";
            switch ($_SESSION['s']['perm']) {
                case 1:
                    $str = "&Action=change&redirect=1&mytypereg=new_student";
                    break;
                case 2:
                    $str = "&Action=change&redirect=1&mytypereg=new_teacher";
                    break;
                default:
                    break;
            }

            $ratings = array();
            $ratings['rating'] = getAvgRating($res['CID']);
            $toolTip = new ToolTip();
            $toolTipStr = $toolTip->display('order_chain');
            $title = (!is_course_free($res['TypeDes'])) ? _('Подать заявку') : _('Зарегистрироваться');
            $ds_str = okbutton($title, "", "", htmlspecialchars("document.location.href=\"reg.php4?Course={$res[CID]}{$str}\"; return false;"));
            $tt = !is_course_free($res['TypeDes']) ? '&nbsp;&nbsp;' . $toolTipStr : '';

            $ds = (defined("LOCAL_FREE_REGISTRATION") && LOCAL_FREE_REGISTRATION) ? $ds_str . $tt  : "<br>";
            $tit=stripslashes($res['Title']);//.$ds;
            $tit= "<a href='#' onClick=\"javascript: wopen('courseinfo.php?cid={$res['CID']}','desc_{$res['CID']}', '800', '600')\" title='" . _('описание курса') . "'>{$tit}</a>";
            //$tit.='<br>'.getProgressBar($ratings['rating']);
            $tmp=str_replace("[RATINGS]", getProgressBar($ratings['rating']), $tmp);
            $tmp=str_replace("[REG-TO-COURSE]", $ds, $tmp);
            $tmp=str_replace("[cName]", $tit, $tmp);
            $bedate=mydate($res['cBegin'])."-<br>".mydate($res['cEnd']);
            $tmp=str_replace("[beDate]",$bedate,$tmp);
            if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM) {
                $res['Fee']=0;
                $tmp=str_replace("[Fee]",$res['credits_student'],$tmp);
                $clist=str_replace("[STOIMOST]",_("кредитов"),$clist);

            } else {
                $tmp=str_replace("[Fee]",$res['Fee'],$tmp);
                $clist=str_replace("[STOIMOST]",_("стоимость"),$clist);
            }
            if ($res['Fee']==0)
               $tmp=str_replace("[VALUTA]","",$tmp);
            else {
               if (isset($GLOBALS['valuta'][$res['valuta']]))
                  $tmp=str_replace("[VALUTA]",$GLOBALS['valuta'][$res['valuta']][2],$tmp);
               else
                  $tmp=str_replace("[VALUTA]",$GLOBALS['valuta'][0][2],$tmp);
            }
            if ($res['TypeDes']<0) $res['TypeDes'] = $res['chain'];
            $tmp=str_replace("[TypeDes]",get_course_type($res['TypeDes']),$tmp);


            $des = '';

/* Здесь описание не нужно, выведем в popup

			$des = read_metadata(stripslashes($res['Description']), COURSES_DESCRIPTION);
            $des = view_metadata_as_text($des, COURSES_DESCRIPTION);
            if (strlen($des)) {
                $des = "<tr>
                        <td colspan=6>
                        <table border=0 width=100% cellspacing=0 cellpadding=0>
                        <tr><td>
                        <div id=\"course_description_{$res['CID']}_hidden\" ><a href=\"javascript:void(0);\" onClick=\"document.getElementById('course_description_{$res['CID']}_hidden').style.display='none'; document.getElementById('course_description_{$res['CID']}_shown').style.display='block';\"><img border=0 src=\"{$GLOBALS['sitepath']}images/ico_plus.gif\" align=absmiddle>"._("описание")."</a></div>
                        <div class=hidden2 id=\"course_description_{$res['CID']}_shown\"><a href=\"javascript:void(0);\" onClick=\"document.getElementById('course_description_{$res['CID']}_shown').style.display='none'; document.getElementById('course_description_{$res['CID']}_hidden').style.display='block';\"><img border=0 src=\"{$GLOBALS['sitepath']}images/ico_minus.gif\" align=absmiddle>"._("описание")." </a>
                       ".$des."&nbsp; </div>
                       </td></tr>
                        </table>
                        </td></tr>";
            } else {
                $des = '';
            }
*/            $tmp=str_replace("[DESCRIPTION]",$des,$tmp); //stripslashes($res['Description'])

            $tmp=str_replace("[Teachers]",get_teachers_list($res['CID'], true),$tmp);
            $tmp=str_replace("[CID]",$res['CID'],$tmp);
            $all.=$tmp;
        }
        }
        else {
        }
      }
      $clist=str_replace("[COURSES]",$all,$clist);
      if (!$GLOBALS['controller']->enabled) {
      $ttt="<table border=0 width=100% id='title_courses'><tr><th><span style='cursor:hand'
             onClick=\"removeElem('title_courses');putElem('courses');\">".getIcon("+").$title."</span></th></tr></table>";
      $clist="<table border='0' cellspacing='0' cellpadding='0' width=100% class=hidden2 id=courses ><tr><th><span style='cursor:hand'
             onClick=\"putElem('title_courses');removeElem('courses');\">".
             getIcon("-").$title."</span></th></tr><tr><td>".$clist."</td></tr></table>";
      }
      if( isset( $tmp )){
        if (!$GLOBALS['controller']->enabled)
        $ph=ph(_("Учебные курсы")." ($k)");
        $ret=$ph.$ttt.$clist;
      }
      else
        $ret=''; // если нет ни одного курса - не выводим таблицу ввобще
      return( $ret );
}

function getAllSubCats ($cat) {
    static $ret;
    $ret = $ret?$ret:array();
    $res = sql("SELECT * FROM courses_groups WHERE owner_did = $cat");
    while ($row = sqlget($res)) {
        $ret[$row['did']] = $row;
        getAllSubCats($row['did']);
    }
    return $ret;
}

function getCategories ($cat=0) {
    //тащим все категории
    $ret = array();
    $res = sql("SELECT * FROM courses_groups");
    while ($row = sqlget($res)) {
        $ret[$row['did']] = $row;
        $ret[$row['did']]['allCourses'] = 0;
    }

    //перебираем все курсы
    $tracks = getTracksIdList();
    $res = sql("SELECT * 
                FROM Courses 
                WHERE Status>1 
                  AND cEnd >= NOW()
                  AND TypeDes >= 0 
                  AND (did LIKE '%;".implode(";%' OR did LIKE '%;",array_keys($ret)).";%')");
    while ($row = sqlget($res)) {
        if (in_array($row['CID'], $GLOBALS['s']['allSkurs'])) {
            continue;
        }
        $dids = explode(';',trim($row['did'],';'));
        foreach ($dids as $did) {
            if (isInTrackList($tracks, $row['CID'])) continue;
            $ret[$did]['allCourses']++;
        }
    }
    
    //добавляем курсы из подкатегорий
    foreach ($ret as $key=>$did) {
        if ($did['owner_did']) {
            continue;    
        }else {
            $ret[$key] = calculateCategoriesCourses($key, $ret);
        }
    }
    
    //собираем хлебные крошки
    $dummy = $cat;
    $bc = $cat ? "<strong>".$ret[$dummy]['name']."</strong>" : "&nbsp;";
    while ($dummy) {    	        
    	$dummy = $ret[$dummy]['owner_did'];
    	$bc = "<a href='{$GLOBALS['sitepath']}order.php?cat=$dummy'>"
    	   . $ret[$dummy]['name']
    	   . "</a>"
    	   . "<span>&nbsp;&#x203A;&nbsp;</span>$bc";
    }
    $bc = "<a href='{$GLOBALS['sitepath']}order.php'>"._("Все рубрики")."</a>$bc";
    
    $owner_cat = $ret[$cat]['owner_did'];
    
    //убираем всё ненужное
    foreach ($ret as $key=>$val) {
        if ($val['owner_did'] != $cat) {
            unset($ret[$key]);
        }
    }

    $smarty = new Smarty_els();
    $smarty->assign('cat',$owner_cat!==false?$owner_cat:-1);
    $smarty->assign('categories',$ret);
    $smarty->assign('sitepath',$GLOBALS['sitepath']);
    $smarty->assign('itemsPerRow',5);
    $smarty->assign('bc', $bc);
    return $smarty->fetch('order_categories.tpl');
}

function calculateCategoriesCourses($id, $info) {    
    foreach ($info as $key=>$did) {
        if ($did['owner_did'] != $id) continue;        
        $info[$key] = calculateCategoriesCourses($key, $info);
        $info[$id]['allCourses']  += $info[$key]['allCourses'];
    }    
    return $info[$id];
}

function getCourseCategories($cid) {
    $ret = array();
    $did = sqlvalue("SELECT did FROM Courses WHERE CID = '$cid'");
    $dids = explode(';', trim($did, ';'));
    $sql = "SELECT * FROM courses_groups WHERE did IN ('".implode("','", $dids)."')";
    $res = sql($sql);
    while ($row = sqlget($res)) {
        $ret[$row['did']] = $row;
    }
    
    return $ret;
}
function getCoursesByName($search_string) {
    $search_string = '%'.str_replace(' ', '%', trim($search_string)).'%';
    $sql = "SELECT * 
            FROM Courses
            WHERE Courses.Status>0 
              AND Courses.TypeDes>-1 
              AND Courses.cEnd > NOW()
              AND LOWER(Courses.Title) LIKE LOWER(".$GLOBALS['adodb']->Quote($search_string).")
            ORDER BY Title";
    $res     = sql($sql);
    $courses = array();
    $tracks  = getTracksIdList();
    while ($row = sqlget($res)) {
        if( !isInTrackList( $tracks, $row['CID']) && is_course4order($row) ) {
            $courses[$row['CID']] = $row;
            $courses[$row['CID']]['rating']   = getProgressBar(getAvgRating($row['CID']));
            $courses[$row['CID']]['Title']    = stripslashes($row['Title']);
            $courses[$row['CID']]['TypeDes']  = get_course_type($row['TypeDes']);
            $courses[$row['CID']]['teachers'] = get_teachers_list($row['CID'], true);            
            $courses[$row['CID']]['dids']     = getCourseCategories($row['CID']);
            $toolTip = new ToolTip();
            $courses[$row['CID']]['tooltip']  = $toolTip->display('order_chain');
            $courses[$row['CID']]['free'] = is_course_free($row['TypeDes']);
        }
    }
    
    $smarty = new Smarty_els();
    $smarty->assign('perm', $_SESSION['s']['perm']);
    $smarty->assign('regFree', defined("LOCAL_FREE_REGISTRATION") && LOCAL_FREE_REGISTRATION);
    $smarty->assign('courses', $courses);
    return $smarty->fetch('course_order_list.tpl');
}


   $courses_header=show_header();

   $course_search_string = $_REQUEST['course_search_string'];
   $GLOBALS['controller']->addFilter(_("Курс"),'course_search_string',false,$course_search_string);
      
   if ($course_search_string) {       
       $course_list = getCoursesByName($course_search_string);
       
   }else {
   $category = $_GET['cat']?$_GET['cat']:0;
   $course_list = getCategories($category)."<br />".all_course_list(_("Перечень курсов"), "notracks",($category)?array($category):false);
   }

   $html=loadtmpl("courses-main.html");

   $tmp=all_tracks_list(1);

   $html=str_replace("[TRACKS]",$tmp,$html);
////////////////////////////////////////////////////////////////

   $html=str_replace("[HEADER]",$courses_header,$html);
   $html=str_replace("[FORM_HEADER]",$form_header,$html);
   $html=str_replace("[REG_FORM]",$reg_form,$html);
   $html=str_replace("[COURSE_LIST]",$course_list,$html);

   $html=str_replace("[ERROR]",$error,$html);
   $html=str_replace("[COOL]",$cool,$html);
   $html=str_replace("[WAIT]",$wait,$html);
   $html=str_replace("[CJAVA]",$cJavaFolder,$html);
   $html=str_replace("[ACTION]","courses.php4",$html);
   $html=showSortImg($html,$s[user][csort]);

   $mhtml=show_tb(1);

   $mhtml=str_replace("[ALL-CONTENT]",$html,$mhtml);

   //$GLOBALS['controller']->captureFromReturn(CONTENT,$tmp.$course_list);
  // $GLOBALS['controller']->setHeader(_('Подать заявку'));
   $GLOBALS['controller']->captureFromReturn('m210101',$tmp);
   $GLOBALS['controller']->captureFromReturn(CONTENT,$course_list);

   printtmpl($mhtml);


?>