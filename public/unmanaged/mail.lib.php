<?
//   error_reporting(2047);
//   ini_set("display_errors","1");

   $strT=student_alias_parse(loadtmpl("all-mail.html"));

   $str=explode($mailSep,$strT);
//   pr($str);

   $fromdean=getDeansOptions();
   $headers=loadtmpl("all-headers.html");




function getMail( $mid ){
     if ($mid) $pl=getPLE($mid,1);
    // echo "<H1>MAIL</H1>";
     return($pl['Email']);
   }

function mailTostud($go, $mid, $cid, $more, $trid=0, $return = false) {
       // высылает сообщение о смене пралоля и меняет на новый его
       //  если go = tost | remember

       global $str, $fromdean;
       global $Password;
       $from=$fromdean;
       $course="";       
       
       if(isset($cid) && !is_array($cid)) 
       {
        $course=getField("Courses","Title","CID",$cid);
        $descr=getField("Courses","Description","CID",$cid);
        $d_beg=getField("Courses","cBegin","CID",$cid);         
        $d_end=getField("Courses","cEnd","CID",$cid);
       }
       $pl=getPLE( $mid, 0 );    
       
       switch ( $go ) {

         case "tost" :
               if ( $mid )
               $subj=$str[0];
               $body=$str[1];
               $body=str_replace("[LOGIN]",$pl['Login'],$body);
               //$body=str_replace("[PASSWORD]",$pl['Password'],$body);
//               echo $body;
               break;

         case "reg_stud":
               if ( $mid )
               $subj=$str[54];
               $body=$str[55];

               $body=str_replace("[LOGIN]",$pl['Login'],$body);
               //$body=str_replace("[PASSWORD]",$pl['Password'],$body);
               break;
         case "reg_stud_typedes":
               if ( $mid )
               $subj=$str[54];
               $body=$str[55];
               $body=str_replace("[LOGIN]",$pl['Login'],$body);
               break;               
         case "tost_first" :
               if ( $mid )
               $subj=$str[0];
               $body=$str[1];
               $body=str_replace("[LOGIN]",$pl['Login'],$body);
               //$body=str_replace("[PASSWORD]",$pl['Password'],$body);
//               echo $body;
               break;
         case "tostap" :
               $subj=$str[8];
               $body=$str[9];
               break;
         case "toab":
               $subj=$str[2];
               $body=$str[3];
                  break;
         case "togr":
               $subj=$str[4];
               $body=$str[5];
               $body=str_replace("[date1]",$d_beg,$body);
               $body=str_replace("[date2]",$d_end,$body);
                  break;
         case "del" :
               $subj=$str[6];
               $body=$str[7];
               break;
         case "guestmes" :
               $subj=$str[34];
               $body=$str[35];
               $body=str_replace("[MESSAGE]",$more['MESSAGE'],$body);
               $body=str_replace("[LFNAME]",$more['lf'],$body);
               $body=str_replace("[LFNAME2]",$more['lf2'],$body);
               break;
         case "remember" :
               if ($mid) {
                  $pl=getPLE( $mid, 1 );
                  $subj=$str[38];
                  $body=$str[39];
                  $body=str_replace("[LOGIN]",$pl['Login'],$body);
                  $Password = $pl['Password'];
                  //$body=str_replace("[PASSWORD]",$pl['Password'],$body);
                  }
               break;
         case "change" :

               if ($mid) {
                  if($Password == "") {
                     $pl=getPLE( $mid, 1);
                     $Password = $pl['Password'];
                  }
                  else {
                     $pl = getPLE($mid, 0);
                  }
                  $subj=$str[38];
                  $body=$str[39];
                  $body=str_replace("[LOGIN]",$pl['Login'],$body);
                  $body = str_replace("[PASSWORD]", $Password, $body);
                  //$body=str_replace("[PASSWORD]",$pl['Password'],$body);
                  }
               break;
         case "tost_track":
               if ( $mid )
               $arrCourses = array();
               if (is_array($cid)) {
                   foreach ($cid as $key => $val) {
                            $arrCourses[] = "<li>".cid2title($val)."\t ";
                   }
               }
               $strCourses = implode("\n", $arrCourses);
               $subj=$str[42];
               $body=$str[43];
               $body=str_replace("[LOGIN]",$pl['Login'],$body);
               //$body=str_replace("[PASSWORD]",$pl['Password'],$body);
               $body=str_replace("[COURSES]",$strCourses,$body);
               if ($trid) {
                   $track=getField("tracks","name","trid",$trid);
                   $level = getCurLevel($trid, $mid) + 1;
                   $track .= " - ".$level." "._("семестр");
               }
               break;
         case "togr_track":
               if ( $mid )
               $arrCourses = array();
               if (is_array($cid)) {
                   foreach ($cid as $key => $val) {
                            $arrCourses[] = "<li>".cid2title($val)."\t ";
                   }
               }
               $strCourses = implode("\n", $arrCourses);
               $subj=$str[58];
               $body=$str[59];
               $body=str_replace("[LOGIN]",$pl['Login'],$body);
               //$body=str_replace("[PASSWORD]",$pl['Password'],$body);
               $body=str_replace("[COURSES]",$strCourses,$body);
               if ($trid) {
                   $track=getField("tracks","name","trid",$trid);
                   $level = getCurLevel($trid, $mid);
                   $track .= " - ".$level." "._("семестр");
               }
               break;
         case 'free_questions_checked':
             $subj = $str[56];
             $body = $str[57];
             $body=str_replace("[COURSE]",cid2title($cid),$body);
             $body=str_replace("[TEST]",tid2title($more['tid']),$body);
             $body=str_replace("[MARK]",$more['mark'],$body);
             $body=str_replace("[COMMENTS]",$more['comments'],$body);
             $body=str_replace("[TEACHER]",mid2name($more['teacher']),$body);
         break;

       }

       if($Password != "") {
          $body = str_replace("[PASSWORD]", $Password, $body);
       }
       else {
          $body = str_replace("[PASSWORD]", "not updated", $body);
       }


       $body=str_replace("[COURSE]",$course,$body);
       $body=str_replace("[TRACK]",$track,$body);
       $body=str_replace("[DEANNAME]",$from['name'],$body);
       $body=str_replace("[DEANMAIL]",$from['email'],$body);
       $subj=str_replace("[COURSE]",$course,$subj);
       $subj=str_replace("[TRACK]",$track,$subj);
       return ($return) ? (array($pl['Login'],$pl['Password'])) : (sendMail($pl['Email'],$subj,$body,$from['email'],$from['name']));
   }


function mailToteach($go,$mid,$cid,$more="") {
      global $str, $fromdean;
      global $Password;
      $from=$fromdean;
      $course=getField("Courses","Title","CID",$cid);
      switch ($go) {
         case "about_reg_student":

               $pl = getPLE($mid, 0);
               $subj = $str[48];
               $body = $str[49];

               $more['lf'] = getpeoplename($mid);
               $more['email'] = getField("People", "EMail", "MID", $mid);

         break;
         break;
         case "regcourse":

               $pl = getPLE($mid, 0);
               $subj = $str[52];
               $body = $str[53];
         break;
         case "tothap" :
               if ($mid)
                 $pl=getPLE( $mid, 0);
               $subj=$str[10];
               $body=$str[11];
               break;
         case "fromstud" :
               if ($mid) $pl=getPLE($mid,0);
               $subj=$str[12];
               $body=$str[13];
               $body=str_replace("[LFNAME]",$more['lf'],$body);
               $body=str_replace("[MAILTO]",$more['email'],$body);
               break;
         case "toteach" :
               $pl=getPLE( $mid,0 );
               $subj=$str[14];
               $body=$str[15];
               $body=str_replace("[LOGIN]",$pl['Login'].":$pp",$body);
              // $body=str_replace("[PASSWORD]",$more['Password'],$body);
               break;
         case "remteach" :
               if ($mid) $pl=getPLE($mid,0);
               $subj=$str[16];
               $body=$str[17];
               break;
         case "elmes" :
               if ($mid) $pl=getPLE($mid,0);
               $subj=$str[32];
               $body=$str[33];
               $body=str_replace("[MESSAGE]",$more,$body);
               break;
         case "del" :
               if ($mid) $pl=getPLE($mid,0);
               $subj=$str[18];
               $body=$str[19];
               break;
         case "guestmes" :
               if ($mid) $pl=getPLE($mid,0);
               $subj=$str[34];
               $body=$str[35];
               $body=str_replace("[MESSAGE]",$more['MESSAGE'],$body);
               $body=str_replace("[LFNAME]",$more['lf'],$body);
               $body=str_replace("[LFNAME2]",$more['lf2'],$body);
               break;
         case "forced":
               if ($mid) $pl=getPLE($mid,0);
               $subj=$str[44];
               $body=$str[45];
               $body=str_replace("[LOGIN]",$pl['Login'],$body);
               $body=str_replace("[PASSWORD]",$pl['Password'],$body);
             break;
         case "forced_new":
               if ($mid) $pl=getPLE($mid,0);
               $subj=$str[44];
               $body=$str[45];
               $body=str_replace("[LOGIN]",$pl['Login'],$body);
               // no password replace.. will be taken from global scope
             break;
       }

       $body = str_replace("[LFNAME]", $more['lf'], $body);
       $body = str_replace("[MAILTO]", $more['email'], $body);
       $body = str_replace("[LOGIN]", $pl['Login'], $body);
       if($Password != "") {
          $body = str_replace("[PASSWORD]", $Password, $body);
       }
       else {
          $body = str_replace("[PASSWORD]", "not updated", $body);
       }

       $subj=str_replace("[COURSE]",$course,$subj);
       $body=str_replace("[COURSE]",$course,$body);
       $body=str_replace("[DEANNAME]",$from['name'],$body);
       $body=str_replace("[DEANMAIL]",$from['email'],$body);
       return( sendMail($pl['Email'],$subj,$body,$from['email'],$from['name']));
   }

function mailToelearn($go,$mid,$cid,$more="",$to_mid=0) {
      global $str, $fromdean;
      global $Password;
      $from=$fromdean;
      $course=getField("Courses","Title","CID",$cid);

      switch ($go) {
         case "about_reg_student":

               $subj = $str[48];
               $body = $str[49];
               $more['lf'] = getpeoplename($mid);
               $more['email'] = getField("People", "EMail", "MID", $mid);

         break;
         case "about_reg_teacher":

               $subj = $str[50];
               $body = $str[51];
               $more['lf'] = getpeoplename($mid);
               $more['email'] = getField("People", "EMail", "MID", $mid);

         break;
         case "fromteach" :
               $subj=$str[20];
               $body=$str[21];
               break;
         case "fromstud":
               $subj=$str[22];
               $body=$str[23];
               break;
         case "fromcourse":
               $subj=$str[24];
               $body=$str[25];
               $body=str_replace("[DESCRIPTION]",$more['description'],$body);
               break;
         case "import":
               $subj=$str[46];
               $body=$str[47];
               $body=str_replace("[MSG]",$more['msg'],$body);
         break;

      }


      if($Password != "") {
         $body = str_replace("[PASSWORD]", $Password, $body);
      }
      else {
         $body = str_replace("[PASSWORD]", "not updated", $body);
      }

      $body=str_replace("[LFNAME]",$more['lf'],$body);
      $body=str_replace("[MAILTO]",$more['email'],$body);

      $body=str_replace("[COURSE]",$course,$body);
      $body=str_replace("[DEANNAME]",$from['name'],$body);
      $body=str_replace("[DEANMAIL]",$from['email'],$body);

      $to['email'] = $from['email'];
      if ($to_mid) {
          $to['email'] = getField("People", "EMail", "MID", $to_mid);
      }
      
      if ($to['email']) {
        return( sendMail($to['email'],$subj,$body,$from['email'],$from['name']) );
      }
   }

function mailToother($go,$cid,$more) {
   global $str, $fromdean;
   global $Password;
   $from=$fromdean;
   if (!$more['course']) $course=getField("Courses","Title","CID",$cid);
   else $course=$more['course'];

       switch ($go) {
         case "fromcourse":
               $subj=$str[26];
               $body=$str[27];
               break;
         case "regcourse":
               $subj=$str[28];
               $body=$str[29];
               break;
         case "delcourse":
               $subj=$str[30];
               $body=$str[31];
               break;
         case "guestmes" :
               $subj=$str[36];
               $body=$str[37];
               $body=str_replace("[MESSAGE]",$more['MESSAGE'],$body);
               $body=str_replace("[LFNAME]",$more['lf'],$body);
               $body=str_replace("[LFNAME2]",$more['lf2'],$body);
               break;
                case "invalid_login_flush":
               $subj=$str[39];
               $body=$str[40];
       }
       if($Password != "") {
          $body = str_replace("[PASSWORD]", $Password, $body);
       }
       else {
          $body = str_replace("[PASSWORD]", "not updated", $body);
       }


       $body=str_replace("[NEWCOURSENAME]",cid2title($cid),$body);
       $body=str_replace("[DEANNAME]",$from['name'],$body);
       $body=str_replace("[DEANMAIL]",$from['email'],$body);
       $body=str_replace("[COURSE]",$course,$body);

       return ( sendMail($more['email'],$subj,$body,$from['email'],$from['name']) );

   }

   function mailToSimple($go, $mid) {
   global $str, $fromdean;
   $from=$fromdean;
   $r = sql("SELECT * FROM People WHERE `MID`={$mid}");
   $a = sqlget($r);
   switch ($go) {
        case "invalid_login_flush":
           $subj=$str[40];
           $body=$str[41];
   }
   return ( sendMail($a['EMail'],$subj,$body,$from['email'],$from['name']) );

   }

function sendMail($mailto,$subj,$body,$mailfrom,$fromname) {
   global $headers;
   $subj=str_replace(array("<br>","<br />"),array("",""),$subj);
   $headers=str_replace(array("[FROMNAME]","[FROMMAIL]"),
                        array($fromname,$mailfrom),
                        $headers);
   if ($GLOBALS['controller']->enabled){
    
        require_once("lib/phpmailer/class.phpmailer.php");
    
        $mail_controller = new Controller();
        $mail_controller->initialize(CONTROLLER_ON);
        $mail_controller->setView('DocumentMail');
        $mail_controller->setContent($body);
        
        $phpmailer = new PHPMailer();
        $phpmailer->ContentType = "text/html";
        $phpmailer->Subject = $subj;
        $phpmailer->From = $mailfrom;
        $phpmailer->FromName = $fromname;
        $phpmailer->Body = $mail_controller->terminate();
        $phpmailer->AddAddress($mailto);
        $result = $phpmailer->Send();
        
   } else {
        @mail($mailto,$subj,$body, $headers);
   }
  return( $mailto );
}

?>