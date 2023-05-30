<?
//require("phplib.php4");
/*
$fp = fopen("live_up_temp.txt", "w+");
ob_start();
print_r($_SERVER);
print_r($_POST);
fwrite($fp, ob_get_contents(), 1000000);
fclose($fp);
die();*/

require("1.php");

//error_reporting(2047);
//ini_set("display_errors","1");

class elearn_user
{
   var $user_MID=0;
   var $user_pass="";
   var $user_login="";
   var $user_auth=0;

   var $iCID=0;
   var $bTeacher=0;
   var $id="";

   var $error_log="";

   function elearn_user($mid="",$cid="",$login="",$teach="")
   {
//       $this->elearn_user_log("[BEGIN LOGIN]");
         $this->user_login=return_valid_value($login);
         $this->user_MID=$mid;
         $this->bTeacher=$teach;
         $this->iCID=$cid;

         $this->check_user_pass();

      if (!empty($this->user_MID) && !empty($this->iCID) && !empty($this->user_login))
               $this->elearn_user_log("elearn_user - login");
         else
            {
               $this->elearn_forbiden();
               $this->elearn_user_log("elearn_user - Error login with ");
            }
   }

   function check_user_pass()
   {
      // empty login or pass ?
      global $peopletable;

      if (empty($this->user_login) || empty($this->user_MID))
      {
//       $this->elearn_user_log("check_user_pass login or pass empty");
         return 0;
      }

      // sql_query_string

      $sql="SELECT MID FROM ".$peopletable." WHERE login='".$this->user_login."'";

      // sql_select

      if (!$sql_result=sql($sql))
      {
         $this->elearn_user_log("mysql_query_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
         return 0;
      }

      // found records

      if (sqlrows($sql_result)<1)
      {
         $this->elearn_user_log("WARNING: no records found for this user");
         return 0;
      }

      $res=sqlget($sql_result);

      // is this user valid

      if ($res['MID']==$this->user_MID)
      {
         $this->user_auth=1;
//       $this->elearn_user_log("Correct password Acceess Granted");
         return 1;
      }
       $this->user_MID=0;
      $this->elearn_user_log("WARNING: Incorrect password Access Denied!");
      return 0;
   }

   function user_status()
   {
      $str="";
      if (empty($this->user_auth) || empty($this->user_MID))
      {
//       $str="Incorrect user MID or user not Aoutorized";
//          $this->elearn_forbiden();
//       $this->elearn_user_log("Access Forbidden 403");
         return $str;
      }
      $str="[ MID ] => ".$this->user_MID."<br>";
      $str.="[ login ] => ".$this->user_login."<br>";
      $str.="[ pass ] => ".$this->user_pass."<br>";
      $this->elearn_user_log("Show user status");
      return $str;
   }

   function elearn_user_log($str)
   {
      global $cam_up_log;

      $proxy = $_SERVER["REMOTE_ADDR"];
      $user = " - ".$_SERVER["HTTP_X_FORWARDED_FOR"];


      $this->error_log=$proxy.$user.date(" - [d m Y H:i:s] ").$str." : login=".$this->user_login." MID=".$this->user_MID."\n";
      @error_log($this->error_log,3,$cam_up_log);
      @chmod($cam_up_log,0775);
   }

   function generate_id($sheid)
   {
      global $cam_table,$msg;

      if (!$this->can_casting_now($sheid)) return $msg[0];

      if ($this->is_now_casting($sheid))
      {
         $this->get_id();
         $sql="INSERT INTO ".$cam_table."(cam_key,CID,MID,SHEID) VALUES ('".$this->id."','".$this->iCID."','".$this->user_MID."','".$sheid."')";
         if (!$sql_result=sql($sql))
         {
            $this->elearn_user_log("mysql_query_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
            return "---";
         }
      }
      return $this->id;
   }

   function can_casting_now($sheid)
   {
   $ret=false;
   global $scheduletable;

   $time=time();

   $sql="SELECT ".$scheduletable.".CID as CID FROM ".$scheduletable." WHERE ".$scheduletable.".SHEID=".$sheid." AND UNIX_TIMESTAMP(".$scheduletable.".begin)<".$time." AND UNIX_TIMESTAMP(".$scheduletable.".end)>".$time."";

      // sql_select
      if (!$sql_result=sql($sql))
      {
         $this->elearn_user_log("mysql_query_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
         return $ret;
      }

      // found records

      if (sqlrows($sql_result)<1)
      {
//       $this->elearn_user_log("No cam lessons now");
         return $ret;
      }


   return true;
   }

   function is_now_casting($sheid)
   {
      global $cam_table;
      $sql="SELECT cam_key FROM ".$cam_table." WHERE SHEID='".$sheid."'";
      if (!$sql_result=sql($sql))
      {
         $this->elearn_user_log("mysql_query_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
         return "0";
      }
      if (sqlrows($sql_result)>0)
      {
         $res=sqlget($sql_result);
         $this->id=$res['cam_key'];
//       $this->elearn_user_log("Second query for Casting on this lesson SHEID=[".$sheid."]");
         return 0;
      }
      return 1;

   }

   function get_id()
   {
      $this->id=md5(uniqid($this->user_login));
   }

   function elearn_forbiden()
   {
      header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
      header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                                                     // always modified
      header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
      header("Cache-Control: post-check=0, pre-check=0", false);
      header("Pragma: no-cache");                          // HTTP/1.0
      header("HTTP/1.0 403 Forbidden");
      die();
//     $this->elearn_user_log("Send 403 forbidden - ".headers_sent()." - ");
   }

}

class live_file extends elearn_user
{

   function live_file($key)
   {
      global $cam_table;
      global $peopletable;
      global $scheduletable;

      // sql_query_string

      $time=time();

      $sql="SELECT ".$peopletable.".MID as MID, ".$peopletable.".Login as login, ".$cam_table.".CID as CID, ".$scheduletable.".SHEID as SHEID FROM ".$cam_table.",".$peopletable.",".$scheduletable." WHERE ".$cam_table.".cam_key='".$key."' AND ".$cam_table.".MID=".$peopletable.".MID AND ".$scheduletable.".SHEID=".$cam_table.".SHEID AND UNIX_TIMESTAMP(".$scheduletable.".begin)<".$time." AND UNIX_TIMESTAMP(".$scheduletable.".end)>".$time."";

      // sql_select
      if (!$sql_result=sql($sql))
      {
         $this->elearn_user_log("mysql_query_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
         return 0;
      }

      // found records

      if (sqlrows($sql_result)<1)
      {
//       $this->elearn_user_log("No avaible cam casting");
         //$this->elearn_forbiden();
         return 0;
      }

      $res=sqlget($sql_result);

//    $this->elearn_user_log("Key :".$key);
//    $this->elearn_user_log("SHEID :".$res->SHEID);

      elearn_user::elearn_user($res['MID'],$res['CID'],$res['login'],1);
   }

   function  get_str() {
      if ($this->user_auth==1)
      {
         $str=realpath(".");
         $str.="/COURSES/course".$this->iCID."/webcam_room_".$this->iCID."/web.jpg";
//       $this->elearn_user_log("Copy path :".$str);
         return $str;
      }
      return 0;
   }

   function  allow_upload($file_type)
   {
      $allow=0;
      if ($file_type=="image/pjpeg") $allow=1;
      return $allow;
   }

   function show_status($key)
   {

      global $cam_table;
      global $peopletable;
      global $scheduletable;
      global $coursestable;

      // sql_query_string

      $sql="SELECT ".$peopletable.".MID as MID, ".$peopletable.".FirstName as fName, ".$peopletable.".LastName as lName, ".$peopletable.".Login as login, ".$cam_table.".CID as CID, ".$scheduletable.".SHEID as SHEID, ".$scheduletable.".title as lTitle, ".$scheduletable.".begin as begin_time, ".$scheduletable.".end as end_time , ".$coursestable.".Title as cTitle FROM ".$cam_table.",".$peopletable.",".$scheduletable.",".$coursestable." WHERE ".$cam_table.".cam_key='".$key."' AND ".$cam_table.".CID=".$coursestable.".CID AND ".$cam_table.".MID=".$peopletable.".MID AND ".$scheduletable.".SHEID=".$cam_table.".SHEID ";

      // sql_select

      if (!$sql_result=sql($sql))
      {
         $this->elearn_user_log("mysql_query_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
         return 0;
      }

      // found records

      if (sqlrows($sql_result)<1)
      {
//       $this->elearn_user_log("No avaible cam casting");
         //$this->elearn_forbiden();
         return 0;
      }

      $res=sqlget($sql_result);

//    $this->elearn_user_log("Get Status for Key :".$key);

      return $res;

   }


}

// define
   $fp = fopen("live_up_temp.txt", "w+");
   fputs($fp, "running...\n");

   $login=return_valid_value((isset($s['login'])) ? $s['login'] : "");
   $mid=intval((isset($s['mid'])) ? $s['mid'] : "");
   $teacher=$teach;

   $key=return_valid_value((isset($_POST['elearn_key'])) ? $_POST['elearn_key'] : "");

   //$key=return_valid_value((isset($_GET['elearn_key'])) ? $_GET['elearn_key'] : "");

   fputs($fp, "key: $key\n");

   $sheid=intval((isset($_GET['ID'])) ? $_GET['ID'] : "");
   $cid=intval((isset($_GET['CID'])) ? $_GET['CID'] : "");

   $msg=file(realpath(".")."/template/msg.htm",1024);
   $html=implode("",file(realpath(".")."/template/help.htm"));

   if (empty($key)) {
      $user= new elearn_user($mid,$cid,$login,$teacher);
      if ($teacher && !empty($sheid)) {
         $help=implode("",file(realpath(".")."/template/cam_help_teach.htm"));
         $html=str_replace("[PATH]",$sitepath,$html);
         $help=str_replace("[PATH]",$sitepath,$help);
         $html=str_replace("[HELP]",$help,$html);
         $html=str_replace("[ID]",$user->generate_id($sheid),$html);
         echo stripcslashes($html);
      }
      else {
         $help=implode("",file(realpath(".")."/template/cam_help_stud.htm"));
         $html=str_replace("[PATH]",$sitepath,$html);
         $html=str_replace("[HELP]",$help,$html);
         $html=str_replace("[ID]","",$html);
         echo stripcslashes($html);

     }
   }
   else {
         fputs($fp, "key is not empty\n");
         $upload=0;
         $file= new live_file($key);

         if (isset($_FILES['cam_file'])) {
            fputs($fp, "cam_file is set\n");
            if (is_uploaded_file($_FILES['cam_file']['tmp_name'])) {
               if ($str=$file->get_str())
                     fputs($fp, "str: $str\n");
                     if ($file->allow_upload($_FILES['cam_file']['type']) && !empty($str)) {
                        ob_start();
                        print_r($_FILES);
                        fputs($fp, ob_get_contents());
                        $tmp_file_name = $_FILES['cam_file']['tmp_name'];
                        //$str = str_replace("\\", "/", $str);
                        //fputs($fp, "str: $str\n");
                        if(move_uploaded_file($tmp_file_name, $str)) {
                           $upload = 1;
                           //fputs($fp, "upload: succesfull; tmp_name: ".$_FILES['cam_file']['tmp_name']."\n");
                        }
                        else {
                             fputs($fp, "upload not succesfull; tmp_name: ".$tmp_file_name."\n");
                        }
                        //fputs($fp, "Upload File - [".$upload."] - ContentType - [".$_FILES['cam_file']['type']."] FileSize - [".$_FILES['cam_file']['size']."]");

                        //$file->elearn_user_log("Upload File - [".$upload."] - ContentType - [".$_FILES['cam_file']['type']."] FileSize - [".$_FILES['cam_file']['size']."]");
                     }
            }
            fputs($fp, "not uploaded file");
         }
         else {
            fputs($fp, "cam_file is not set\n");
            $info=$file->show_status($key);
            ob_start();
            print_r($info);
            fputs($fp, "info: ".ob_get_contents());
            $status=$msg[2];
            fputs($fp, "status: $status\n");
            setcookie("eLearn Server 3000 Ready",1);
            if ($file->user_auth) {
                  $status=$msg[1];
            }
            if (!empty($info)) {
               $upload=1;
               $html=implode("",file(realpath(".")."/template/cam_status.htm"));

               $html=str_replace("[SERVER]",$sitepath,$html);
               $html=str_replace("[cTitle]",$info['cTitle'],$html);
               $html=str_replace("[fName]",$info['fName'],$html);
               $html=str_replace("[lName]",$info['lName'],$html);
               $html=str_replace("[lTitle]",$info['lTitle'],$html);
               $html=str_replace("[TIME_BEGIN]",$info['begin_time'],$html);
               $html=str_replace("[TIME_END]",$info['end_time'],$html);
               $html=str_replace("[STATUS]",$status,$html);
               echo stripcslashes($html);
            }
            else {
                    echo $msg[3];
            }
            if (!$file->user_auth) {
                 fputs($fp, "user forbiden\n");
                 elearn_user::elearn_forbiden();
            }

         }
         if(!$upload) {
             elearn_user::elearn_forbiden();
         }


      }
      fclose($fp);

//elearn_user::elearn_user_log("[END LOGIN]");
?>
