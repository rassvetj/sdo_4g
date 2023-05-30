<?php
   require_once('1.php');
   require_once('news.lib.php4');
   $words=array();
   istest();
   if ($dean && $make=="move" && $nID)   {
       show_hide_news($nID);
       $GLOBALS['controller']->setMessage(_("Статус инфоблока изменен"),JS_GO_URL,'about.php');
   }
   if ($dean && $make=="remove" && $nID) {
       remove_news($nID);
       $GLOBALS['controller']->setMessage(_("Сообщение удалено"),JS_GO_URL,'about.php');
   }
   $html=show_tb(1);
   $allcontent=loadtmpl("about-main-info.html");
   $newsheader=loadtmpl("all-cHeader.html");
   $newsimages=loadtmpl("all-images.html");
   $newswords=loadwords("about-words.html");
   $newssort=loadtmpl("about-sort.html");
   $sign=getField("OPTIONS","value","name","dekanName");
    $strCreateNews = $GLOBALS['s']['perm'] >= 3 ? "
    <div style='padding-bottom: 5px;'>
        <div style='float: left;'><img src='{$GLOBALS['sitepath']}images/icons/small_star.gif'>&nbsp;</div>
        <div><a href='{$GLOBALS['sitepath']}aboutmake.php4?make=add' style='text-decoration: none;'>"._("создать инфоблок")."</a></div>
    </div>
    " : "";
   $allnews=show_info_table( $dean ); // выводит информацию блоками
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
   if ($GLOBALS['controller']->enabled) {
       $newsheader=str_replace('justify','',$words['PAGESTATIC']);
       $newsadd='';
   }
   $allcontent=str_replace("[NEWS-HEADER]",$newsheader,$allcontent);
   $allcontent=str_replace("[NEWS-FULL]",$allnews,$allcontent);
   $allcontent=str_replace("[OKBUTTON]",okbutton(),$allcontent);
   $allcontent=str_replace("[NEWS-IMAGES]","",$allcontent);
   $allcontent=str_replace("[NEWS-ADD]",$strCreateNews,$allcontent);
   $allcontent=str_replace("[NEWS-SORT]",$newssort,$allcontent);
   $html=str_replace("[ALL-CONTENT]",$allcontent,$html);
   for($i=1;$i<5;$i++) {
      $lnk=getField("OPTIONS","value","name","link$i");
      $html=str_replace('[LINK'.$i.']',$lnk,$html);
   }
    if ($GLOBALS['controller']->enabled) {
        $html=words_parse($html,$words);
        $html=path_sess_parse($html);
        $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
    }
   printtmpl($html);
?>