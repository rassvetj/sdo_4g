<?
   require_once('1.php');
   require_once('news.lib.php4');

   istest();
   define("ALLOW_HTML_TAGS", true);

   if (!$dean) login_error();

   $submit=(isset($_POST['formsubmit'])) ? $_POST['formsubmit'] : "";

   if ($submit)
         {
             $valid=0;

            $nID=(isset($_POST['nID'])) ? $_POST['nID'] : "";
            $title=(isset($_POST['Title'])) ? $_POST['Title'] : "";

            $day=(isset($_POST['day'])) ? $_POST['day'] : "";
            $month=(isset($_POST['month'])) ? $_POST['month'] : "";
            $year=(isset($_POST['year'])) ? $_POST['year'] : "";

            $hours=(isset($_POST['hours'])) ? $_POST['hours'] : "";
            $mins=(isset($_POST['min'])) ? $_POST['min'] : "";

            $text=(isset($_POST['text'])) ? $_POST['text'] : "";

            if(!empty($text) && !empty($title)) $valid=1;
      if(empty($text)) {
          $GLOBALS['controller']->setView('DocumentBlank');          
          $GLOBALS['controller']->setMessage(_('Необходимо заполнить поле Содержание'), JS_GO_URL, $sitepath.'newsmake.php4?make='.$make.'&amp;nID='.(int)$nID);
          $GLOBALS['controller']->terminate();    
          exit();      
      }
      if(empty($title)) {
          $GLOBALS['controller']->setView('DocumentBlank');          
          $GLOBALS['controller']->setMessage(_('Необходимо заполнить поле Заголовок'), JS_GO_URL, $sitepath.'newsmake.php4?make='.$make.'&amp;nID='.(int)$nID);
          $GLOBALS['controller']->terminate();
          exit();
      } 
            $text=v_text($text, ALLOW_HTML_TAGS);
            $title=v_text($title);

            $day=v_day($day);
            $month=v_month($month);
            $year=v_year($year);
            $hours=v_hours($hours);
            $mins=v_min($mins);

//            $dat=$year."-".$month."-".$day." ".$hours.":".$mins.":"."00";
      		$dat = mktime($hours, $mins, 0, $month, $day, $year);
            
         }



   $words=array();

   $html=create_new_html(0,0);
   $allnews="";

   $allcontent=loadtmpl("news-main-edit.html");
   $newswords=loadwords("news-words.html");
   $newsheader=loadtmpl("all-cHeader.html");
   $newsimages="";
   $newsadd="";
   $newssort="";


   $words['edit']=$newswords[1];
   $words['delete']=$newswords[2];
   $words['show']=$newswords[3];
   $words['hide']=$newswords[4];

   $words['save']=$newswords[5];
   $words['cancel']=$newswords[6];
   $words['close']=$newswords[7];

   $words['success']=$newswords[14];
   $words['error']=$newswords[15];

   $strSign = (isset($_POST['ch_sign'])) ? $_POST['author'] : "";

   //$GLOBALS['controller']->setView('DocumentPopup');

   if ($valid && empty($nID)) {
       $allnews=add_news($dat,$title,$strSign,$text);
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_('Новость добавлена. Используйте показать/скрыть.'),JS_GO_URL,$GLOBALS['sitepath']."news.php4");
       $GLOBALS['controller']->terminate();
       exit();
   }
   if ($valid && !empty($nID)) {
       $allnews=edit_news($nID,$dat,$title,$strSign,$text);
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_('Новость обновлена. Используйте показать/скрыть.'),JS_GO_URL,$GLOBALS['sitepath']."news.php4");
       $GLOBALS['controller']->terminate();
       exit();
   }
   
   if ($nID && $make=="edit") {
       $GLOBALS['controller']->setHelpSection('edit');
       $GLOBALS['controller']->setHeader(_('Редактирование новости'));
       $words['PAGENAME']=$newswords[8];
       $allnews=sedit_news($nID);
   }elseif ($make=='add') {
       $GLOBALS['controller']->setHelpSection('add');
       $GLOBALS['controller']->setHeader(_('Редактирование новости'));
       $words['PAGENAME']=$newswords[16];
       $allnews=sadd_news();
   }
   
   
   if (empty($allnews)) login_error();

   $pagestatic=loadtmpl("news-estatic.html");

   if ($GLOBALS['controller']->enabled) {
       $newsheader=str_replace('justify','',$pagestatic);
   }

   $allcontent=str_replace("[NEWS-HEADER]",$newsheader,$allcontent);
   $allcontent=str_replace("[NEWS-PREFERENCES]","",$allcontent);
   $allcontent=str_replace("[NEWS-IMAGES]",$newsimages,$allcontent);
   $allcontent=str_replace("[NEWS-FULL]",$allnews,$allcontent);
   $allcontent=str_replace("[W-PAGESTATIC]",$pagestatic,$allcontent);


    $allcontent=str_replace("[NEWS-ADD]",$newsadd,$allcontent);
    $allcontent=str_replace("[NEWS-SORT]",$newssort,$allcontent);
    $allcontent=str_replace("[OKBUTTON]",okbutton(),$allcontent);   
    $allcontent=str_replace("[NEWS-LINK-MAIN]",$GLOBALS['sitepath']."news.php4",$allcontent);   
   $html=str_replace("[ALL-CONTENT]",$allcontent,$html);

   if ($GLOBALS['controller']->enabled) {
        $html=words_parse($html,$words);
        $html=path_sess_parse($html);

        $GLOBALS['controller']->captureFromReturn(CONTENT,$html);        
    }

   //printtmpl($html);
   $GLOBALS['controller']->terminate();
?>