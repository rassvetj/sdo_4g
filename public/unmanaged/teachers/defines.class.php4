<?
   istest();
   if (!$stud) login_error();

class userSet {
// public

   var $userMID=0;
   var $userFirstName=0;
   var $userLastName=0;
   var $userEmail=0;
   var $userName=0;
   var $b_userTeacher=0;
   var $userCoursesImploded=0;
   var $selectedcourse=0;

   function userSet()
   {
     global $s,$teach;
      $this->userMID=$s['mid'];
      $this->userName=$s['login'];
      $this->b_userTeacher=$teach;
      $this->selectedcourse="";
   }

   function show_all_vars()
   {
      $str="";
      while (list($key,$value)=each($this))
         $str.= "[ ".$key." ] = > ".$value."<br>\n";
      return $str;
   }

   function valid_num($val)
   {
      if (ereg("^[0-9]{1,9}$",$val))
         return $val;
      return 0;
   }

   function valid_string($val)
   {
      $val=htmlspecialchars($val);
      $val=str_replace("#"," ",$val);
      $val=addslashes($val);
      return $val;
   }
}

class eLdef extends userSet {
// public

   var $CID=0;
   var $PID=0;
   var $ModID=0;
   var $make=0;
   var $showfull=0;

   function eLdef($arg=0)
   {
      $this->userSet();
      $this->CID=(isset($arg['CID'])) ? $this->valid_num($arg['CID']) : 0;
      $this->PID=(isset($arg['PID'])) ? $this->valid_num($arg['PID']) : 0;
      $this->ModID=(isset($arg['ModID'])) ? $this->valid_num($arg['ModID']) : 0;
      $this->make=(isset($arg['make'])) ? $arg['make'] : 0;
      $this->showfull=(isset($arg['showfull'])) ? $this->valid_num($arg['showfull']) : 0;
      $this->is_teacher();
   }

   function is_teacher()
   {
      global $teacherstable;
      $sql="SELECT PID FROM ".$teacherstable." WHERE MID='".$this->userMID."' AND CID='".$this->CID."'";
      $sql_result=sql($sql);
      if (sqlrows($sql_result)>0)
      {
         $res=sqlget($sql_result);
         if ($res['PID']==$this->PID)
            return ;
      }
      $this->PID=0;
      return ;
   }

   function return_path_to_mod($mod=0)
   {
      $mod=($mod > 0) ? $mod : $this->ModID;
      if ($this->valid_num($this->CID) && $this->valid_num($mod))
         return "../COURSES/course".$this->CID."/mods/".$mod."/";
         // return "../COURSES/course".$this->CID."/mods/";
      return "";
   }

   function return_http_path_allmod()
   {
      global $sitepath;
      if ($this->valid_num($this->CID))
         return $sitepath."COURSES/course".$this->CID."/mods/";
      return "";
   }

   function get_path_to_mod()
   {
      if ($this->valid_num($this->CID))
         return "COURSES/course".$this->CID."/mods/";
      return "";
   }


   function return_http_path_mod($mod=0)
   {
      global $sitepath;
      $mod=(empty($mod)) ? $this->ModID : $mod ;
      if (!empty($sitepath) && $this->valid_num($mod) && $this->valid_num($this->CID))
         return $sitepath."COURSES/course".$this->CID."/mods/".$mod."/";
         //return $sitepath."COURSES/course".$this->CID."/mods/";
      return "";
   }

   function return_mod_name($mod=0)
   {
      $mod=(empty($mod)) ? $this->ModID : $mod ;
      global $mod_list_table;
      if ($this->valid_num($mod))
      {
         $sql="SELECT Title FROM ".$mod_list_table." WHERE ModID='".$mod."'";
         $sql_result=sql($sql);
         if (@sqlrows($sql_result)>0)
         {
            $res=sqlget($sql_result);
            return $res['Title'];
         }
         return ;
      }
      return ;
   }

   function return_mod_list($cid=0)
   {
      $cid=(empty($cid)) ? $this->CID : $cid ;
      $list=array();
      if ($this->valid_num($cid))
      {
               global $mod_list_table;
               $sql="SELECT ModID FROM ".$mod_list_table." WHERE CID='".$cid."' ORDER by ModID ASC";
               //echo $sql;
               $sql_result=sql($sql);
               if (sqlrows($sql_result)>0) {

                  while ($res=sqlget($sql_result))
                     $list[]=$res['ModID'];
//                     return $list;
               }
//               return 0;
      }
      return $list;
   }


}
?>