<?
   require_once('1.php');
   require_once('news.lib.php4');

   $GLOBALS['controller']->setHelpSection($make);

   istest();
   if (!$dean) login_error();

   $submit=(isset($_POST['formsubmit'])) ? $_POST['formsubmit'] : "";

   if ($submit) {
      $valid=0;
      $nID=(isset($_POST['nID'])) ? $_POST['nID'] : "";
      $title=(isset($_POST['Title'])) ? $_POST['Title'] : "";
      $day=(isset($_POST['day'])) ? $_POST['day'] : "";
      $month=(isset($_POST['month'])) ? $_POST['month'] : "";
      $year=(isset($_POST['year'])) ? $_POST['year'] : "";
      $hours=(isset($_POST['hours'])) ? $_POST['hours'] : "";
      $mins=(isset($_POST['min'])) ? $_POST['min'] : "";
      $text=(isset($_POST['text'])) ? $_POST['text'] : "";
      $type=(isset($_POST['block_type'])) ? $_POST['block_type'] : "";
      $show=(isset($_POST['show'])) ? $_POST['show'] : 0;
      if(!empty($text) && !empty($title)) $valid=1;
      if(empty($text)) {
          $GLOBALS['controller']->setMessage(_("Необходимо заполнить поле Содержание"), JS_GO_URL, $sitepath.'aboutmake.php4?make='.$make.'&amp;nID='.(int)$nID);
          $GLOBALS['controller']->terminate();
          exit();
      }
      if(empty($title)) {
          $GLOBALS['controller']->setMessage(_("Необходимо заполнить поле Заголовок"), JS_GO_URL, $sitepath.'aboutmake.php4?make='.$make.'&amp;nID='.(int)$nID);
          $GLOBALS['controller']->terminate();
          exit();
      }
      $title = v_text($title);
      $day   = v_day($day);
      //$text = addslashes($text);
      $month = v_month($month);
      $year  = v_year($year);
      $hours = v_hours($hours);
      $mins  = v_min($mins);
      $standalone=($_POST['standalone']);
      $dat = mktime($hours, $mins, 0, $month, $day, $year);
//      $dat=$year."-".$month."-".$day." ".$hours.":".$mins.":00";

   }
   $words=array();
   $html=create_new_html(0,0);
   $allnews="";
   $allcontent=loadtmpl("about-main-edit.html");
   $newswords=loadwords("about-words.html");
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


   if ($nID && $make=="edit") {
      $words['PAGENAME']=$newswords[8];
      $allnews=sedit_info ($nID, $type );
   }

   if ($make=="add") {
      $words['PAGENAME']=$newswords[16];
      $allnews=sadd_info( $type );
   }

   if ($valid && empty($nID)) {
      $allnews=add_info( $dat, $title, $type, $text , $standalone);
      $GLOBALS['controller']->setMessage(_("Инфоблок успешно добавлен"),JS_GO_URL,'about.php');
      $GLOBALS['controller']->terminate();
      exit();
   }
   if ($valid && !empty($nID)) {
      $allnews=edit_info($nID, $dat, $title, $type, $text, $show, $standalone);
      $GLOBALS['controller']->setMessage(_("Инфоблок успешно отредактирован"),JS_GO_URL,'about.php');
      $GLOBALS['controller']->terminate();
      exit();
   }

   if (empty($allnews)) login_error();

   $pagestatic=loadtmpl("about-estatic.html");
   if ($GLOBALS['controller']->enabled) {
       $newsheader=str_replace('justify','',$pagestatic);
   }
   $allcontent=str_replace("[NEWS-HEADER]",$newsheader,$allcontent);
   $allcontent=str_replace("[NEWS-IMAGES]",$newsimages,$allcontent);
   $allcontent=str_replace("[NEWS-FULL]",$allnews,$allcontent);
   $allcontent=str_replace("[W-PAGESTATIC]",$pagestatic,$allcontent);
   $allcontent=str_replace("[NEWS-ADD]",$newsadd,$allcontent);
   $allcontent=str_replace("[NEWS-SORT]",$newssort,$allcontent);
   $allcontent=str_replace("[OKBUTTON]",okbutton(),$allcontent);
//   $allcontent=str_replace("[CANSELBUTTON]","<div style='float: right; margin-left:4px' class='button'><a href='".$sitepath."about.php'>"._("Отмена")."</a></div>",$allcontent);
   $allcontent=str_replace("[CANSELBUTTON]", button(_('Отмена'), '', 'cancel', '', "{$sitepath}about.php"), $allcontent);

   $html=str_replace("[ALL-CONTENT]",$allcontent,$html);
    if ($GLOBALS['controller']->enabled) {
        $html=words_parse($html,$words);
        $html=path_sess_parse($html);

        if ($make=='add') {
            $GLOBALS['controller']->setHeader(_("Добавить информационный блок"));
        }elseif ($make=='edit') {
            $GLOBALS['controller']->setHeader(_("Редактирование информационного блока"));
        }

        $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
    }
   printtmpl($html);

?>