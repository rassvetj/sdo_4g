<?php
define('LENGHT_STANDALONE_ANNOUNCE', 500);
require_once("lib/FCKeditor/fckeditor.php");


   $array_pages = array(
                                     "-~home~-" => _("стартовая страница"),
                                     "-~reg~-" => _("страница регистрации")
                                     /*"-~lib~-" => _("страницы библиотеки"),*/
                                     );


   $info_block="-~-"; // признак того,что новость никакая не новость а информационный блок

        $make=(isset($_GET['make'])) ? $_GET['make'] : "";
        $nID=(isset($_GET['nID'])) ? $_GET['nID'] : "";
        $all=(isset($_GET['all'])) ? $_GET['all'] : "";
        if (!isset($s['user']['nsort'])) $s['user']['nsort']=0;
        $s['user']['nsort']=(isset($_GET['sort'])) ? $_GET['sort'] : $s['user']['nsort'];

function show_news_table( $num=5, $where="" ) {
   $ret="";
   $n=array();
   global $dean;
   global $all;
   global $info_block;
   if ($dean) $num="";
   $res=get_news( $num, $where );
   $line=loadtmpl("news-1lnews.html");
   $n['dean']=($dean) ? loadtmpl("news-edit.html") : "";
   $news_cnt = ($value = (integer)$num) ? $value : sqlrows($res);
   while (($row=@sqlget($res))&& $news_cnt--) {
      $n['date']=date("d ", $row['date_timestamp']);
      $dummyMonth=month(date("m", $row['date_timestamp']));
      $n['date'].=strpos($dummyMonth, 'ь')||strpos($dummyMonth, 'й')?str_replace(array('ь','й'),'я',$dummyMonth):$dummyMonth.'а';
      $n['date'].=date(" Y H:i", $row['date_timestamp']);

      //echo $n['date'];
      //die();

      $n['PATH']=$GLOBALS['sitepath'];
      $n['text']=$row['message'];
      $n['name']=$row['author'];
      $n['title']=$row['Title'];
      $n['ID']=$row['nID'];
      $n['show']=($row['show']) ? "[W-hide]" : "[W-show]";
      $n['icon']=($row['show']) ? "hide.gif" : "show.gif";
      $n['iconTitle']=($row['show']) ? _("Скрыть новость") : _("Открыть новость");
      if( intBlockType( $n['name'] ) == 0 ){
         if ( $dean || $row['show'])
            $ret.=words_parse( $line, $n, "N-");
      }
   }

   return $ret;
}

function show_single_news($id) {
    global $newstable;
    $sql  = "SELECT * FROM `$newstable` WHERE nID = '{$_GET['nid']}'";
    $res  = sql($sql);
    $news = sqlget($res);
    $news['author'] = str_replace(array("-~","~-"), array('<!--', '-->'), $news['author']);

    $smarty = new Smarty_els();
    $smarty->assign('news', $news);
    $smarty->assign('perm', $GLOBALS['s']['perm']);
    return $smarty->fetch('single_news.tpl');
}

function sedit_news( $where )
        {

        $ret="";
        $n=array();
        $num=1;

        $res=get_news( $num, $where );
        $line=loadtmpl("news-eline.html");
        $news_cnt = ($value = (integer)$num) ? $value : sqlrows($res);
        while (($row=@sqlget($res)) && $news_cnt--)
        {
                $n['day']=select_day(date("d", $row['date_timestamp']));
                $n['month']=select_month(date("m", $row['date_timestamp']));
                $n['year']=select_year(date("Y", $row['date_timestamp']));
                $n['hours']=date("H", $row['date_timestamp']);
                $n['min']=date("i", $row['date_timestamp']);
                $n['text']=$row['message'];
                $n['name']=$row['author'];
                $n['title']=htmlspecialchars($row['Title'],ENT_QUOTES);
                $n['ID']=$row['nID'];
                $ret.=words_parse($line,$n,"N-");
        }
        return $ret;
}

function sadd_news() {
   global $s;

   $ret="";
   $n=array();
   $line=loadtmpl("news-eline.html");
   $n['day']=select_day(date("d"));
   $n['month']=select_month(date("m"));
   $n['year']=select_year(date("Y"));
   $n['hours']=date("H");
   $n['min']=date("i");
   $n['text']="";
   $n['name']=$s['user']['fname'];
   $n['title']=trim(strip_tags($_POST['title']));
   $n['ID']="";
   $ret.=words_parse($line,$n,"N-");
   return $ret;
}

function add_news( $dat, $title, $author, $message) {

   //fn $dat = date("Y.m.d H:i:s", $dat);
   global $adodb;
   global $newstable;
   global $lang;

   $ret=loadtmpl("news-addrez.html");
   global $adodb;
//   $sql="INSERT INTO `".$newstable."` (`date`, `Title`, `author`, `message`, `lang`, `show`)
//         VALUES (".$adodb->DBTimeStamp($dat).", ".$adodb->Quote($title).", ".$adodb->Quote($author).", ".$adodb->Quote($message).", ".$adodb->Quote($lang).", '0')";
   $sql="INSERT INTO `".$newstable."` (`date`, `Title`, `author`, `message`, `lang`, `show`)
         VALUES (".$adodb->DBTimeStamp($dat).", ".$adodb->Quote(stripslashes($title)).", ".$adodb->Quote($author).", ".$adodb->Quote(stripslashes($message)).", ".$adodb->Quote($lang).", '0')";
   if ($res=sql($sql))
      $ret=str_replace("[W-error]","",$ret);
   else
      $ret=str_replace("[W-success]","",$ret);
   return $ret;
}

function edit_news($nid,$dat,$title,$author,$message) {
   global $adodb;
   global $newstable;
   $ret=loadtmpl("news-addrez.html");
   global $adodb;
//   $sql="UPDATE `".$newstable."` SET `date`=".$adodb->DBTimeStamp($dat).", `Title`=".$adodb->Quote($title).", `author`=".$adodb->Quote($author).", `message`=".$adodb->Quote($message).", `lang`=".$adodb->Quote($lang).", `show`='0' WHERE `nID`='".$nid."'";
   $sql="UPDATE `".$newstable."` SET `date`=".$adodb->DBTimeStamp($dat).", `Title`=".$adodb->Quote(stripslashes($title)).", `author`=".$adodb->Quote($author).", `message`=".$adodb->Quote(stripslashes($message)).", `lang`=".$adodb->Quote($lang).", `show`='0' WHERE `nID`='".$nid."'";
   if ($res=sql($sql)) $ret=str_replace("[W-error]","",$ret);
   else $ret=str_replace("[W-success]","",$ret);
   return $ret;
}



function show_hide_news($nid) {
   global $newstable;
   $sql = "SELECT `show` FROM $newstable WHERE nID = $nid";
   $res = sql($sql);
   $row = sqlget($res);
   if($row['show'] == 0)
      $sql = "UPDATE `".$newstable."` SET `show` = 1 WHERE `nID` = '".$nid."'";
   else
      $sql = "UPDATE `".$newstable."` SET `show` = 0 WHERE `nID` = '".$nid."'";
   $res=sql($sql);
   return true;
}

function remove_news($nid)
        {
                global $newstable;

                $sql="DELETE FROM `".$newstable."` WHERE `nID` = '".$nid."'";
                $res=sql($sql);
                return true;
        }

/////////////////////////////// ABOUT INFO MANEGEMENT
function sedit_info( $where, $info_type ) {
         global $array_pages;
         $ret="";
         $n=array();
         $num=1;
         $res=get_news( $num, $where );
         $line=loadtmpl("about-eline.html");
         $news_cnt = ($value = (integer)$num) ? $value : sqlrows($res);

         while (($row=@sqlget($res)) && $news_cnt--) {

				ob_start();
				$oFCKeditor = new FCKeditor('text') ;
		 		$oFCKeditor->BasePath	= "{$GLOBALS['sitepath']}lib/FCKeditor/";
				$oFCKeditor->Value		= $row['message'] ;
				$oFCKeditor->Create() ;
				$fck_code = ob_get_contents();
				ob_clean();

                $n['day']=select_day(date("d", $row['date_timestamp']));
                $n['month']=select_month(date("m", $row['date_timestamp']));
                $n['year']=select_year(date("Y", $row['date_timestamp']));
                $n['hours']=date("H", $row['date_timestamp']);
                $n['min']=date("i", $row['date_timestamp']);
                $n['text']=$fck_code;
                $n['checked_standalone'] = ($row['standalone']) ? 'checked' : '';
//                $n['text']=$row['message'];
                $n['name']=$info_type;
                $n['title']=htmlspecialchars($row['Title']);
                $n['ID']=$row['nID'];
                $n['author']=select_author($row['author']);
                //$n['show']=select_show($row['show']);
                $n['show']=$row['show'];

                $n['author'] = '';
                foreach($array_pages as $key => $value) {
                        if($row['author'] == $key) {
                           $sel = "selected";
                        }
                        else {
                           $sel = "";
                        }
                        $n['author'].="<option value='$key' $sel>$value</option>\n";
                }

                $ret.=words_parse($line,$n,"N-");
        }
        return $ret;
}

function sadd_info( $info_type )
        {
        global $s;
        global $array_pages;

    $ret="";
        $n=array();

        $line=loadtmpl("about-eline.html");

				ob_start();
				$oFCKeditor = new FCKeditor('text') ;
		 		$oFCKeditor->BasePath	= "{$GLOBALS['sitepath']}lib/FCKeditor/";
				$oFCKeditor->Value		= '' ;
				$oFCKeditor->Create() ;
				$fck_code = ob_get_contents();
				ob_clean();

                $n['day']=select_day(date("d"));
                $n['month']=select_month(date("m"));
                $n['year']=select_year(date("Y"));
                $n['hours']=date("H");
                $n['min']=date("i");
                $n['text']=$fck_code;
                $n['name']= $info_type; ///!!!!!!!!!
                $n['title']=htmlspecialchars($_POST['title']);
                $n['ID']="";

                $n['author'] = '';

                foreach($array_pages as $key => $value) {
                        $n['author'].="<option value='$key'>$value</option>\n";
                }


                /*
                $n['author'] = "<option value=\"\">какой-то страницы</option>
                                <option value=\"-~home~-\">стартовой страницы</option>
                                <option value=\"-~reg~-\">страницы регистрации</option>
                                <option value=\"-~courses~-\">страницы курсов</option>
                                <option value=\"-~lib~-\">страницы библиотеки</option>
                                <option value=\"-~about~-\">страницы о сервере</option>
                                <option value=\"-~help~-\">страницы помощи</option>
                                <option value=\"-~faq~-\">FAQ</option>";
                */
                $ret.=words_parse($line,$n,"N-");


        return $ret;
}

function add_info( $dat, $title, $type, $message, $standalone=0 ) {
   global $newstable;
   global $lang;
   global $adodb;
   $ret=loadtmpl("about-addrez.html");
   $intTimestamp = mktime();
   $sql="INSERT INTO `".$newstable."` (`date`, `Title`, `author`, `lang`, `show`, `standalone`, `message`)
         VALUES (".$adodb->DBTimeStamp($dat).", ".$adodb->Quote(stripslashes($title)).", '".$type."', '".$lang."', '1', '".(int) $standalone."', ' ')";

   if ($res=sql($sql)){
      $ret=str_replace("[W-error]","",$ret);
   }
   else {
      $ret=str_replace("[W-success]","",$ret);
   }

   if($message)
    $adodb->UpdateClob($newstable,'message',$message,"nID='". sqllast()."'");


   return $ret;
}

function edit_info($nid,$dat,$title,$info_type,$message, $show = 0, $standalone = 0) {
   global $adodb;
   global $newstable;
   $ret=loadtmpl("about-addrez.html");
   $sql="UPDATE `".$newstable."` SET `date`=".$adodb->DBTimeStamp($dat).", `Title`=".$adodb->Quote(stripslashes($title)).", `author`=".$adodb->Quote($info_type).", `lang`=".$adodb->Quote($lang).", `show`='$show', standalone = '".intval($standalone)."', message = ' ' WHERE `nID`='".$nid."'";
//   $sql="UPDATE `".$newstable."` SET `date`=".$adodb->DBTimeStamp($dat).", `Title`='".$title."', `author`='".$info_type."', `message`=".$message.", `lang`='".$lang."', `show`='$show', standalone = '{$standalone}' WHERE `nID`='".$nid."'";

   if ($res=sql($sql))
      $ret=str_replace("[W-error]","",$ret);
   else
      $ret=str_replace("[W-success]","",$ret);

   if(strlen($message)) {
      $adodb->UpdateClob($newstable,'message',$message,"nID=". (int) $nid);
   }

   return $ret;
}

function show_info_table( $dean, $info_block="", $num=5, $where="" ) {
   $ret=array();
   $n=array();
   global $all;
   if ($dean) $num="";
   $res=get_news( $num, $where );
   //$row = sqlget($res);
   $line= ( $dean) ? loadtmpl("about-1labout.html") : loadtmpl("about-1labout-main.html");
   $n['dean']=( $dean ) ? loadtmpl("about-edit.html") : "";
   $contents="";
   $i=0;
   $news_cnt = ($value = (integer)$num) ? $value : sqlrows($res);
   while (($row=sqlget($res)) && $news_cnt--) {
      if ( $dean ) {
         $n['date']=date("d ", $row['date_timestamp']);
         $n['date'].=month(date("m", $row['date_timestamp']));
         $n['date'].=date(" Y H:i", $row['date_timestamp']);
         $n['name']=textBlockType( $row['author'] );
         $HR="";
      }
      else {
         $n['date']="";
         $n['name']="";
         $HR="";
      }
      if ($row['standalone'] && (strlen($row['message']) > LENGHT_STANDALONE_ANNOUNCE)){
          $row['message'] = substr($row['message'], 0, LENGHT_STANDALONE_ANNOUNCE) . "... <a href='info_block.php?id={$row['nID']}' target='_blank'>"._("Подробнее")."</a>";
//      if (!$dean && $row['standalone'] && (strlen($row['message']) > LENGHT_STANDALONE_ANNOUNCE) && ($dot = strpos($row['message'], ".", LENGHT_STANDALONE_ANNOUNCE))){
//      		if ($dot != strlen($row['message'])){
//      			$row['message'] = substr($row['message'], 0, LENGHT_STANDALONE_ANNOUNCE) . ". <a href='info_block.php?id={$row['nID']}'>Подробнее...</a>";
//      		}
      }

      $n['name']=$row['message'];

      $n['title']=str_replace('"', '&quot;', $row['Title']);
      $n['ID']=$row['nID'];
      $n['show']=($row['show']) ? "[W-hide]" : "[W-show]";
      $n['icon']=($row['show']) ? "hide.gif" : "show.gif";
      $n['iconTitle']=($row['show']) ? _("Скрыть инфоблок") : _("Открыть инфоблок");
      if( ( $row['author']==$info_block && $info_block!="") || ( intBlockType( $row['author'] ) != 0 && $info_block=="" ) ) {
         if ( $dean || $row['show']) {
            $ret[]=words_parse($line, $n, "N-").$HR;
            $i++;
         }
      }
   }
   if($dean && ($i > 1))
      $r=implode("", $ret);
   else
      $r=implode("", $ret);
   return $r;
}

function has_info_blocks($type){
	$res = sql("select * from news2 where author='{$type}'");
	return sqlrows($res);
}

function show_info_block( $dean, $html , $type ){
   $words=array();
   //global $info_block; /// Ожно сделать чтоб на любой стриице был инфо блок

   //$newsimages=loadtmpl("all-images.html");
   $newswords=loadwords("about-words.html");
   $allcontent=loadtmpl("about-main.html");
   if( $dean ){
     $newsheader=loadtmpl("all-cHeader.html");
     $newssort=loadtmpl("about-sort.html");
   }else{
//     $allcontent="";
     $newsheader="";
     $newssort="";
   }
   $newsadd=( $dean ) ? loadtmpl("about-add.html") : "";



   $allnews=show_info_table( $dean, $type, "" ); // выводит информацию блоками



   $words['PAGENAME']=$newswords[0];
   $words['edit']=$newswords[1];
   $words['delete']=$newswords[2];
   $words['show']=$newswords[3];
   $words['hide']=$newswords[4];
   $words['sort']=$newswords[9];
   $words['byID']=$newswords[10];
   $words['byDate']=$newswords[11];
   $words['addn']=$newswords[12];
   $words['nall']=$newswords[13];
   $words['PAGESTATIC']=loadtmpl("about-static.html");


   $allcontent=str_replace("[NEWS-HEADER]",$newsheader,$allcontent);
   $allcontent=str_replace("[NEWS-FULL]",$allnews,$allcontent);
   $allcontent=str_replace("[NEWS-IMAGES]","",$allcontent);
   $allcontent=str_replace("[NEWS-ADD]",$newsadd,$allcontent);
   $allcontent=str_replace("[NEWS-SORT]",$newssort,$allcontent);

   $html=str_replace("[ALL-CONTENT]",$allcontent,$html);

   for($i=1;$i<5;$i++) {
      $lnk=getField("OPTIONS","value","name","link$i");
      $html=str_replace('[LINK'.$i.']',$lnk,$html);
   }
  return( $html );
}

?>