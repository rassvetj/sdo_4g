<?


   require_once("1.php");

   $GLOBALS['controller']->page_id=PAGE_INDEX;
   $GLOBALS['controller']->setHelpSection('pass');

   $op=(isset($_POST['old_pass'])) ? tosql($_POST['old_pass']) : "";
   $np=(isset($_POST['new_pass'])) ? tosql($_POST['new_pass']) : "";
   $npc=(isset($_POST['new_pass_conf'])) ? tosql($_POST['new_pass_conf']) : "";

   $rLogin=(isset($_POST['login'])) ? tosql($_POST['login']) : "";



   $rp=(isset($_POST['remember_pass'])) ? $_POST['remember_pass'] : "";
   $mes="";

function remember_passwords( ) {
      $html=show_tb(1);
      $all=loadtmpl("pass-remeber.html");
//      $html=str_replace("[HEADER]",ph("‡ Ўл«Ё Ї а®«м?"),$html);
      $html=str_replace("[ALL-CONTENT]",$all,$html);
      return $html;
}

function new_passwords() {
   global $s;
      $html=show_tb(1);
      $all=loadtmpl("pass-new.html");
//      $html=str_replace("[HEADER]",ph("€§¬Ґ­Ёвм Ї а®«м?"),$html);
      $html=str_replace("[ALL-CONTENT]",$all,$html);
      $html=str_replace("[MID]",$s['mid'],$html);
      return $html;
}



   if ($rp && $rLogin) {
         $res=sql("SELECT MID FROM People WHERE `Login`='".$rLogin."'","passEr01");
//     echo "<H1>=$rLogin=$res</H1>";
         if (sqlrows($res)>0) {
               if($row=sqlget($res)) {
//                     echo $row['MID'];
                  $sub_sql = "SELECT * FROM admins WHERE MID = ".$row['MID'];
                  $sub_res = sql($sub_sql,"fn er");
                  if(sqlrows($sub_res)>0) {
                    //$mail = mailTostud("remember",$row['MID'],0, "");
                    $mes=_("Невозможно сменить пароль администратора");
                    if ($GLOBALS['controller']->enabled) {
                        $GLOBALS['controller']->setMessage($mes);
                        $mes='';
                    }
                  }
                  else {
                    $mail = mailTostud("remember",$row['MID'],0, "");
                    $mes=_("Ваш пароль выслан на")." e-mail $mail";
                    if ($GLOBALS['controller']->enabled) {
                        $GLOBALS['controller']->setMessage($mes);
                        $mes='';
                    }
                  }

//                  login_error();
               }
         }

   }

   if ($op && $np && $npc && ($np == $npc)) {     // СМЕНА ПАРОЛЯ
         $sql="SELECT MID FROM People WHERE `Password`=PASSWORD(".$GLOBALS['adodb']->Quote($op).") AND MID='".$s['mid']."'";
         $res=sql($sql,"passEr03");
         if (sqlrows($res)>0) {
               if($row=sqlget($res)) {
                  $tmp= "UPDATE `People` SET Password=PASSWORD(".$GLOBALS['adodb']->Quote($np).") WHERE `MID`='".$s['mid']."'";
//                  $tmp= "UPDATE `People` SET Password=PASSWORD('".$np."') WHERE `MID`='".$s['mid']."'";
                  //echo $tmp;
                  $res=sql( $tmp,"funEr02P");
                  $mes=_("Ваш пароль успешно изменён");
                  if ($GLOBALS['controller']->enabled) {
                      $GLOBALS['controller']->setMessage($mes);
                      $mes='';
                  }
                  $Password = $np;
                  $mail = mailTostud("change",$s['mid'],0, $np);
                  //echo $mail;

               }
         }
         else {
             $mes=_("Вы неверно ввели старый пароль.");
             if ($GLOBALS['controller']->enabled) {
                 $GLOBALS['controller']->setMessage($mes);
                 $mes='';
             }
         }

   }
   elseif ($op || $np || $npc)  {
       $mes=_("Вы не ввели одно из полей либо не правильно ввели пароль повторно.");
       if ($GLOBALS['controller']->enabled) {
            $GLOBALS['controller']->setMessage($mes);
            $mes='';
       }
   }


   if ( !$stud ) {
     $html=remember_passwords();
   }
   else {
     $html=new_passwords();
   }
   $html = str_replace("[OKBUTTON]",okbutton(),$html);

//   echo  "!!!".$mes.$mail;

   $html=str_replace("[Message]",$mes,$html);
    if ($GLOBALS['controller']->enabled) {
        $html=words_parse($html,$words);
        $html=path_sess_parse($html);
        $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
        $GLOBALS['controller']->setHeader(_("Изменить пароль"));
        if ($GLOBALS['controller']->user->profile_current->name == PROFILE_GUEST)
        $GLOBALS['controller']->setHeader(_("Восстановление пароля"));
    }
   printtmpl($html);
?>