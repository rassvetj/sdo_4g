<?php

require_once("dir_set.inc.php4");

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
//       $this->elearn_user_log("elearn_user - begin login");
         $this->user_login=return_valid_value($login);
         $this->user_MID=$mid;
         $this->bTeacher=$teach;
         $this->iCID=$cid;

         $this->check_user_pass();

      if (!empty($this->user_MID) && !empty($this->iCID) && !empty($this->user_login))
            {
//             $this->elearn_user_log("elearn_user - login");
            }
         else
            {
               $this->elearn_forbiden();
//             $this->elearn_user_log("elearn_user - Error login with ");
            }
   }

   function check_user_pass()
   {
      // empty login or pass ?
      global $peopletable;

      if (empty($this->user_login) || empty($this->user_MID))
      {
         $this->elearn_user_log("check_user_pass login or pass empty");
         return 0;
      }

      // sql_query_string

      $sql="SELECT MID FROM ".$peopletable." WHERE login='".$this->user_login."'";

      // sql_select

      if (!$sql_result=sql($sql))
      {
         $this->elearn_user_log("sql_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
         return 0;
      }

      // found records

      if (sqlrows($sql_result)<1)
      {
         $this->elearn_user_log("no records found for this user");
         return 0;
      }

      $res=mysql_fetch_object($sql_result);

      // is this user valid

      if ($res->MID==$this->user_MID)
      {
         $this->user_auth=1;
//       $this->elearn_user_log("Correct password Acceess Granted");
         return 1;
      }
       $this->user_MID=0;
      $this->elearn_user_log("Incorrect password Access Denied!");
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
      global $file_up_log;

      $proxy = $_SERVER["REMOTE_ADDR"];
      $user = " - ".$_SERVER["HTTP_X_FORWARDED_FOR"];
      $this->error_log=$proxy.$user.date(" - [d m Y H:i:s] ").$str." : login=".$this->user_login." MID=".$this->user_MID."\n";
      @error_log($this->error_log,3,$file_up_log);
      @chmod($file_up_log,0775);

   }

   function generate_id($mod)
   {
      global $file_transfer_table,$msg;

      $error_msg="Error";

      if (isset($msg[4])) $error_msg=$msg[4];

      if (!$this->can_casting_now($mod)) return $msg[4];

      if ($this->is_now_casting($mod))
      {
         $this->get_id();
         $sql="INSERT INTO ".$file_transfer_table."(ft_key,ModID,t_date,MID) VALUES ('".$this->id."','".$mod."','".date("Y-m-d G:i:s")."','".$this->user_MID."')";
         if (!$sql_result=sql($sql))
         {
            $this->elearn_user_log("sql_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
            return "---";
         }

      }
      return $this->id;
   }

   function can_casting_now($mod)
   {
   $ret=false;
   global $teacherstable,$mid,$cid;


   $sql="SELECT PID FROM ".$teacherstable." WHERE CID='".$cid."' AND MID='".$mid."'";

      // sql_select
      if (!$sql_result=sql($sql))
      {
         $this->elearn_user_log("sql_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
         return $ret;
      }

      // found records

      if (sqlrows($sql_result)<1)
      {
//       $this->elearn_user_log("Not a teacher !");
         return $ret;
      }

   $ret=true;

   return $ret;
   }

   function is_now_casting($mod)
   {
      global $file_transfer_table;

      $time=time();

      $sql="SELECT ft_key FROM ".$file_transfer_table." WHERE ModID='".$mod."' AND UNIX_TIMESTAMP(t_date)>".mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));
      if (!$sql_result=sql($sql))
      {
         $this->elearn_user_log("sql_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
         return "0";
      }
      if (sqlrows($sql_result)>0)
      {
         $res=mysql_fetch_object($sql_result);
         $this->id=$res->ft_key;
//       $this->elearn_user_log("Second query for transfering on this course MOD=[".$mod."]");
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

   var $mod_id=0;

   function live_file($key)
   {
      global $peopletable;
      global $mod_list_table;

      global $file_transfer_table;

      $time=time();

      $sql="SELECT ".$file_transfer_table.".ModID as ModID, ".$mod_list_table.".CID as CID, ".$peopletable.".Login as login, ".$peopletable.".MID as MID FROM ".$file_transfer_table.",".$peopletable.",".$mod_list_table." WHERE ft_key='".$key."' AND ".$mod_list_table.".ModID=".$file_transfer_table.".ModID AND ".$file_transfer_table.".MID=".$peopletable.".MID AND UNIX_TIMESTAMP(t_date)>".mktime(0,0,0,date("m",$time),date("d",$time),date("Y",$time));


      // sql_select
      if (!$sql_result=sql($sql))
      {
         $this->elearn_user_log("sql_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
         return 0;
      }

      // found records

      if (sqlrows($sql_result)<1)
      {
//       $this->elearn_user_log("No avaible cam casting");
         //$this->elearn_forbiden();
         return 0;
      }

      $res=mysql_fetch_object($sql_result);

//    $this->elearn_user_log("Key :".$key);
//    $this->elearn_user_log("ModID :".$res->ModID);

      $this->mod_id=$res->ModID;

      elearn_user::elearn_user($res->MID,$res->CID,$res->login,1);
   }

   function  get_str()
   {
      if ($this->user_auth==1)
      {
         $str=realpath(".");
         $str.="/../COURSES/course".$this->iCID."/mods/".$this->mod_id."/";
//       $this->elearn_user_log("Copy path :".$str);
         return $str;
      }
      return 0;
   }

   function  allow_upload($file_type)
   {
      $allow=1;
//    if ($file_type=="image/pjpeg") $allow=1;
      return $allow;
   }

   function  validate_dir($dir)
   {
      $dirs=array();
      $dir_num=0;

      $dir=str_replace("\\","/",$dir);

      if ($dir[strlen($dir)-1]!="/") $dir.="/";

      $dirs=explode("/",$dir);

      $dir_num=count($dirs);
      $cur_dir=dirname(realpath("./"));
      $cur_dir.="/COURSES/course".$this->iCID."/mods/".$this->mod_id."/";

      for($i=0;$i<$dir_num;$i++)
      {
         if (!@is_dir($cur_dir.$dirs[$i]))
            {
            @mkdir($cur_dir.$dirs[$i], 0700);
            @chmod($cur_dir.$dirs[$i],0777);
            }
         $cur_dir.=$dirs[$i];
         $cur_dir.="/";
      }

//    $this->elearn_user_log("Include dir:".$dir);
      return $dir;
   }


   function show_status($key) {
      global $peopletable;
      global $coursestable;
      global $mod_list_table;
      global $file_transfer_table;
      $sql="SELECT ".$file_transfer_table.".ModID as ModID, ".$file_transfer_table.".t_date as date,".$mod_list_table.".CID as CID, ".$peopletable.".Login as login, ".$peopletable.".MID as MID, ".$peopletable.".FirstName as fName, ".$peopletable.".LastName as lName,".$mod_list_table.".Title as ModTitle, ".$coursestable.".Title as cTitle FROM ".$file_transfer_table.",".$peopletable.",".$mod_list_table.", ".$coursestable." WHERE ft_key='".$key."' AND ".$mod_list_table.".ModID=".$file_transfer_table.".ModID AND ".$file_transfer_table.".MID=".$peopletable.".MID AND ".$coursestable.".CID=".$mod_list_table.".CID ";
      if (!$sql_result=sql($sql)) {
         $this->elearn_user_log("sql_error SQL=".$sql." [".mysql_errno()."] ".mysql_error());
         return 0;
      }
      if (sqlrows($sql_result)<1) {
         return 0;
      }
      $res=sqlget($sql_result);
      return $res;
   }


}

// define
   $login=return_valid_value((isset($s['login'])) ? $s['login'] : "");
   $mid=intval((isset($s['mid'])) ? $s['mid'] : "");
   $teacher=$teach;
   $key=return_valid_value((isset($_POST['elearn_key'])) ? $_POST['elearn_key'] : "");

// $key=return_valid_value((isset($_GET['elearn_key'])) ? $_GET['elearn_key'] : "");
// $sheid=intval((isset($_GET['ID'])) ? $_GET['ID'] : "");

   $cid=intval((isset($_GET['CID'])) ? $_GET['CID'] : "");
   $ModID=intval((isset($_GET['ModID'])) ? $_GET['ModID'] : "");

   $file_dir=(isset($_POST['elearn_dir'])) ? $_POST['elearn_dir'] : "";

   $msg=array();

   $msg=file(realpath(".")."/../template/msg.htm",1024);
   $html=implode("",file(realpath(".")."/../template/help.htm"));

   if (empty($key))
   {
      $user= new elearn_user($mid,$cid,$login,$teacher);
      if ($teacher && !empty($ModID))
      {
         $help=implode("",file(realpath(".")."/../template/ft_help_teach.htm"));
         $html=str_replace("[PATH]",$sitepath,$html);
         $help=str_replace("[PATH]",$sitepath,$help);
         $html=str_replace("[HELP]",$help,$html);
         $html=str_replace("[ID]",$user->generate_id($ModID),$html);
         echo stripcslashes($html);
      }else
      {
         $user->elearn_forbiden();
      }
   }
      else
      {
         $upload=0;
         $file= new live_file($key);

         if (isset($_FILES['cam_file']))
         {
            if (is_uploaded_file($_FILES['cam_file']['tmp_name']))
            {
               $file_dir=$file->validate_dir($file_dir);
               if ($str=$file->get_str())
                     if ($file->allow_upload($_FILES['cam_file']['type']) && !empty($str))
                     {
                        $upload=move_uploaded_file($_FILES['cam_file']['tmp_name'],$str.$file_dir.$_FILES['cam_file']['name']);
                        $file->elearn_user_log("Upload File - [".$upload."] - ContentType - [".$_FILES['cam_file']['type']."] FileSize - [".$_FILES['cam_file']['size']."]");
                     }
            }

         }else
         {

            $info=$file->show_status($key);
            $status=$msg[2];
            setcookie("eLearn Server 3000 Ready",1);
            if ($file->user_auth)
                  $status=$msg[1];

            if (!empty($info))
            {
               $upload=1;
               $html=implode("",file(realpath(".")."/../template/ft_status.htm"));

               $html=str_replace("[SERVER]",$sitepath,$html);
               $html=str_replace("[cTitle]",$info['cTitle'],$html);
               $html=str_replace("[fName]",$info['fName'],$html);
               $html=str_replace("[lName]",$info['lName'],$html);
               $html=str_replace("[lTitle]",$info['ModTitle'],$html);
               $html=str_replace("[TIME_BEGIN]",$info['date'],$html);
//             $html=str_replace("[TIME_END]",$info->end_time,$html);
               $html=str_replace("[STATUS]",$status,$html);
               echo stripcslashes($html);
            }else echo $msg[3];
            if (!$file->user_auth) elearn_user::elearn_forbiden();

         }
         if(!$upload) elearn_user::elearn_forbiden();

      }

?>