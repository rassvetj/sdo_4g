<?php
   include_once("setup.inc.php");

   function getMailname($i) {
      switch ($i) {
         case "0"  : $ret=_("Уведомление о регистрации [sTUDENT_ALIAS-ROD-ONE] на курс."); break;
         case "2"  : $ret=_("Уведомление об отчислении [sTUDENT_ALIAS-ROD-ONE] с курса."); break;
         case "4"  : $ret=_("Уведомление о завершении обучения по курсу."); break;
         case "6"  : $ret=_("Уведомление об удалении [sTUDENT_ALIAS-ROD-ONE] с курса."); break;
         case "8"  : $ret=_("Уведомление о подтверждении принятии заявки на регистрацию в качестве [sTUDENT_ALIAS-ROD-ONE]."); break;
         case "10" : $ret=_("Уведомление о подтверждении принятии заявки на регистрацию в качестве преподавателя."); break;
         case "12" : $ret=_("Уведомление для преподавателя о заявке на регистрацию [sTUDENT_ALIAS-ROD-ONE] на курс."); break;
         case "14" : $ret=_("Уведомление о регистрации преподавателя на курс."); break;
         case "16" : $ret=_("Уведомление об отчислении преподавателя с курса."); break;
         case "18" : $ret=_("Уведомление об удалении преподавателя с курса."); break;
         case "20" : $ret=_("Уведомление для учебной администрации о заявке на регистрацию преподавателем на курс."); break;
         case "22" : $ret=_("Уведомление для учебной администрации о заявке на регистрацию [sTUDENT_ALIAS-ROD-ONE] на курс."); break;
         case "24" : $ret=_("Уведомление для учебной администрации о заявке на регистрацию курса."); break;
         case "26" : $ret=_("Уведомление о подтверждении принятии заявки на регистрацию курса."); break;
         case "28" : $ret=_("Уведомление о регистрации курса."); break;
         case "30" : $ret=_("Уведомление об изменении статуса курса на 'Непубликовано'."); break;
         case "32" : $ret=_("Сообщение от учебной администрации сервера."); break;
         case "34" : $ret=_("Уведомление о добавлении объявления."); break;
         case "36" : $ret=_("Уведомление о добавлении ответа в форуме."); break;
         case "38" : $ret=_("Напоминание пароля."); break;
         case "40" : $ret=_("Учетная запись разблокирована."); break;
         case "42" : $ret=_("Уведомление о регистрации [sTUDENT_ALIAS-ROD-ONE] на специальность."); break;
         case "44" : $ret=_("Уведомление о назначении на курс в качестве преподавателя."); break;
         case "46" : $ret=_("Уведомление о генерации учетных записей."); break;
         case "48" : $ret=_("Уведомление для учебной администрации о регистрации обучаемого на курс."); break;
         case "50" : $ret=_("Уведомление для учебной администрации о регистрации преподавателя на курс."); break;
         case "52" : $ret=_("Уведомление о регистрации преподавателя на курс."); break;
         case "54" : $ret=_("Уведомление обучаемого на курс."); break;
         case "56" : $ret=_("Уведомление о проверки задания, требующего проверки преподавателя"); break;
         case '58' : $ret=_('Уведомление об окончании специальности'); break;

         default  : $ret=$i;
      }
      return $ret;
   }


   if (isset($_POST['str'])) {
   if (is_writable($templdir."all-mail.html")) {
      $fp = fopen ($templdir."all-mail.html", "w");
      if ($fp) {

      $k=str_replace( array("\n","\r"), array("<br />",""), implode($mailSep,$_POST['str']) );
                  fwrite($fp, nl2br( $k ) );
                  fclose($fp);
                  chmod($templdir."all-mail.html",0777);
                  $result=2;
               }
		$GLOBALS['controller']->setMessage(_("Письма успешно отредактированы"));

      }else $result=1;
      refresh("$PHP_SELF?$sess&result=".$result);
   }

   $html=show_tb(1);

   $strT=loadtmpl("all-mail.html");
   $allcont=loadtmpl("adm-mail.html");

   $all=explode($mailSep,$strT);

   if(!isset($_GET['result'])) $result="";
      else {
            if ($_GET['result']==1) $result="Unable to Save";
            if ($_GET['result']==2) $result="Succsessfuly Save";
      }

   $str="";
   $str.="<form action='$PHP_SELF' method='POST'>";
   foreach($all as $v=>$k) {
      $str.=($v%2) ? "<td width='10%'>"._("Тело")."</td>" : "<table width=100% class=main cellspacing=0><tr><th colspan='2' class='intermediate'>".getMailname($v)."</th></tr><tr><td>"._("Заголовок")."</td>";
      $k=str_replace( array("<br>","<br />"), array("\n","\n"), $k );
      $str.="<td><textarea name='str[".$v."]' style='width:100%; height:100px'>".$k."</textarea></td></tr>\n";
      $str.=($v%2) ? "</table>" : "";
      }

   $str.= okbutton(_("Сохранить"))."\n</form>";
   $str = student_alias_parse($str);
   
   $GLOBALS['controller']->captureFromReturn(CONTENT, $str);
   
   $html=str_replace('[ALL-CONTENT]',$allcont,$html);
   $html=str_replace('[RESULT]',$result,$html);
   $html=str_replace('[HEADER]',ph(_("Настройка писем")),$html);
   $html=str_replace('[ALL]',$str,$html);
   printtmpl($html);
?>