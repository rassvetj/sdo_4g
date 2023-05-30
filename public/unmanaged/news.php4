<?php
   require_once('1.php');
   require_once('news.lib.php4');

   $words=array();
   $GLOBALS['controller']->setHeader(_('Новости'));

   istest();

   if ($dean && $make=="move" && $nID) {
       show_hide_news($nID);
       $GLOBALS['controller']->setMessage(_("Статус новости изменен"), false, 'news.php4');
   }
   if ($dean && $make=="remove" && $nID) {
       remove_news($nID);
       $GLOBALS['controller']->setMessage(_("Новость удалена"),JS_GO_URL,'news.php4');
   }


    $html=show_tb(1);

   $allcontent=$_GET['nid']?show_single_news($_GET['nid']):loadtmpl("news-main-info.html");
   $newsheader=loadtmpl("all-cHeader.html");
   $newsimages=loadtmpl("all-images.html");
   $newswords=loadwords("news-words.html");
   $newssort=loadtmpl("news-sort.html");

   $newsadd=($dean) ? loadtmpl("news-add.html") : "";

   $allnews=show_news_table();

   //Уберём колонку с действиями, если пользователь не авторизован
   if (!isset($s['perm']) || $s['perm']<1) {
       $allnews = str_replace('<!-- -->','<!--',$allnews);
       $allnews = str_replace('<!--/-->','-->',$allnews);
       $allcontent = str_replace('<!-- -->','<!--',$allcontent);
       $allcontent = str_replace('<!--/-->','-->',$allcontent);
   }


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
   $words['PAGESTATIC']=loadtmpl("news-static.html");

   if (isset($_POST['hid_act']) && ($_POST['hid_act'] == 'set_pref')) {
                setOption("news_max_num", intval($_POST['txt_num']));
                setOption("news_max_len", intval($_POST['txt_len']));
   }

   $intNewsNum = ($tmp = getOption("news_max_num")) ? $tmp : "";
   $intNewsLen = ($tmp = getOption("news_max_len")) ? $tmp : "";
   if ($intNewsNum && $intNewsLen) {
                   $strChecked = "checked";
                   $strDisabled = "";
   } else {
                   $strChecked = "";
                   $strDisabled = "disabled";
   }

   $htmlOkButton = okbutton();

$htmlFormPref = (defined("LOCAL_NEWS_ALLOW_PREFERENCES") && LOCAL_NEWS_ALLOW_PREFERENCES && $dean) ? "
<form name='form_preferences' method='post' action=''>
   <table width=100% border=0 cellspacing=1 cellpadding=2 class=addnew>
      <tr>
         <th>"._("Отображение новостей")."</th>
      </tr>
      <tr>
         <td>
           <table border='0' cellspacing='1' cellpadding='5'>
             <tr>
               <td colspan='2' nowrap><input type='checkbox' name='checkbox' value='checkbox' onClick=\"javascript:document.getElementById('txt_num').disabled=!this.checked;document.getElementById('txt_len').disabled=!this.checked;\" {$strChecked}>
      "._("управлять отображением новостей")." </td>
             </tr>
             <tr>
               <td nowrap>"._("на странице:")." </td>
               <td width='100%'><input name='txt_num' type='text' id='txt_num' size='4' maxlength='4' value='{$intNewsNum}' {$strDisabled}></td>
             </tr>
             <tr>
               <td nowrap>"._("длина (в символах):")."</td>
               <td><input name='txt_len' type='text' id='txt_len' size='4' maxlength='4' value='{$intNewsLen}' {$strDisabled}><input name='hid_act' type='hidden' value='set_pref'></td>
             </tr>
           </table>
         </td>
      </tr>
   </table><br>{$htmlOkButton}
</form>
" : "";

$strCreateNews = $GLOBALS['s']['perm'] >= 3 ? "
<div style='padding-bottom: 5px;'>
    <div style='float: left;'><img src='{$GLOBALS['sitepath']}images/icons/small_star.gif'>&nbsp;</div>
    <div><a href='{$GLOBALS['sitepath']}newsmake.php4?make=add' style='text-decoration: none;'>"._("создать новость")."</a></div>
</div>
" : "";

    if ($GLOBALS['controller']->enabled) {
        $newsheader=str_replace('justify','',$words['PAGESTATIC']);
        $newsadd='';
    }

    $allcontent=str_replace("[NEWS-HEADER]",$newsheader,$allcontent);
    $allcontent=str_replace("[NEWS-PREFERENCES]",$htmlFormPref,$allcontent);
    $allcontent=str_replace("[NEWS-FULL]",$allnews,$allcontent);
    $allcontent=str_replace("[OKBUTTON]",okbutton(),$allcontent);
    $allcontent=str_replace("[NEWS-IMAGES]",$newsimages,$allcontent);

    $allcontent=str_replace("[NEWS-ADD]",$newsadd,$allcontent);
    $allcontent=str_replace("[NEWS-SORT]",$newssort,$allcontent);
    $allcontent=str_replace("[CREATE_NEWS]",$strCreateNews,$allcontent);

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