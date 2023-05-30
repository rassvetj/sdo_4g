<?

require_once("dir_set.inc.php4");
if(($_GET['make'] != "addMod")&&($_GET['make'] != "delete")) echo show_tb();

require_once("organization.lib.php");
require_once("manage_course.lib.php4");
require_once("../metadata.lib.php");

session_unregister("goto");

$error = new log_elarn_error;
if (!isset($_GET['CID'])) $error->add_error("CID",_("Не указан индетификатор курса"));
if (empty($_GET['CID'])) $error->add_error("CID",_("Индетификатор курса пустой"));
if (!isset($s['mid'])) $error->add_error("userMID",_("Не указан личный индетификатор пользователя"));
if (empty($s['mid'])) $error->add_error("userMID",_("Личный индетификатор пользователя пуст"));
if (!$error->is_errors()) {
   $CID=$_GET['CID'];
   $GLOBALS['is_locked_course'] = is_course_locked($CID);
   $MID=$s['mid'];
   $PID=get_pid($MID,$CID);
   $is_teacher=($s[perm]>= 2)?1:0;
   //if (!empty($PID)) $is_teacher=1;
   $SID=get_sid($MID,$CID);
   if (!$admin && !$dean && (empty($SID) && !$is_teacher))
      $error->add_error("userMID",_("Вы не можете просматривать модули данного курса"));
   if (!$error->is_errors() && isset($_GET['make']) && $is_teacher) {
      if (isset($_GET['ModTitle'])  && $_GET['make']=="addMod") {
         add_new_mod($_GET['ModTitle'],$CID,$PID);

         header("Location: {$sitepath}teachers/manage_course.php4?CID=$CID");
         exit();

         echo show_tb();
         unset($_GET['ModTitle']);
      }
      if (isset($_GET['ModID'])  && ($_GET['make']=="delete")) {
         
         remove_mod((int) $_GET['ModID'],$CID,$PID);
                  
         header("Location: {$sitepath}teachers/manage_course.php4?CID=$CID");
         exit();
         
         echo show_tb();
         unset( $_GET );
      }
   }

   if (!$error->is_errors()  && $is_teacher) {
   		show_all_mod($CID,$MID,$PID, $is_teacher );
   }
   $GLOBALS['controller']->page_id = 'm13'.(int) $CID;
}

if (!$error->is_errors()  && !$is_teacher) {
   show_stud_mod($CID,$MID);
}
if (!$error->is_errors())
   echo show_books($CID);
if ($error->is_errors())
   while (list($err,$num)=each($error->get_error())) {
      if ($GLOBALS['controller']->enabled) {
          if (!$is_teacher) $GLOBALS['controller']->setMessage($num,JS_GO_URL,'course_import.php?CID='.$CID.'&msg='.$_GET['msg']);
          else $GLOBALS['controller']->setMessage($num);
      }
      else echo "<b>".$num."</b>";
   }
echo show_tb();

?>