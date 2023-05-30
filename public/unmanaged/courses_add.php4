<?  //require_once('phplib.php4');?>
<?  require_once('1.php');
    require_once('courses.lib.php');
  //  require_once('teachers/manage_course.lib.php4');
    require_once('news.lib.php4');

    require_once("metadata.lib.php");
//          require_once('teacher/manage_course.lib.php4');


   if ((isset($delete) || isset($redCID) || isset($chCID))
       && !isset($s[tkurs][$delete]) && $s[perm]<3)
   {
      exit("нет прав на этот курс");
   }

   $redCID=(isset($_GET['redCID'])) ? intval($_GET['redCID']) : "";

   $delete_id=(isset($_GET['delete'])) ? intval($_GET['delete']) : "";
   $Action=(isset($_POST['Action'])) ? $_POST['Action'] : "";
   $Fee=(isset($_POST['Fee'])) ? $_POST['Fee'] : "";



   $b_year=(isset($_POST['year'])) ? $_POST['year'] : date("Y");
   $e_year=(isset($_POST['year2'])) ? $_POST['year2'] : date("Y");

   $b_day=(isset($_POST['day'])) ? $_POST['day'] : date("d");
   $e_day=(isset($_POST['day2'])) ? $_POST['day2'] : date("d");

   $b_month=(isset($_POST['month'])) ? $_POST['month'] : date("m");
   $e_month=(isset($_POST['month2'])) ? $_POST['month2'] : date("m");

   $Title=(isset($_POST['Title'])) ? $_POST['Title'] : "";
   $Description=(isset($_POST['Description'])) ? $_POST['Description'] : "";
   $createby=(isset($_POST['createby'])) ? $_POST['createby'] : "";

   $Status=(isset($_POST['Status'])) ? $_POST['Status'] : 0;
   $TypeDes=(isset($_POST['TypeDes'])) ? $_POST['TypeDes'] : 0;

   $chCID=(isset($_POST['chCID'])) ? $_POST['chCID'] : "";

   $s[user][csort]=(isset($s[user][csort])) ? $s[user][csort] : 1;
   $s[user][corder]=(isset($s[user][corder])) ? $s[user][corder] : 1;

   $csort=(isset($_GET['csort'])) ? intval($_GET['csort']) : "";

   if ($csort==$s[user][csort]) $s[user][corder]=($s[user][corder]==1) ? 2 : 1;
   if ($csort) $s[user][csort]=$csort;

   $createdate=date("Y-m-d");

   $Fee=intval($Fee);
   $Title=return_valid_value($Title);
   $Description=return_valid_value($Description);

   $createby=return_valid_value($createby);


   $cBegin=$b_year."-".$b_month."-".$b_day;
   $cEnd=$e_year."-".$e_month."-".$e_day;


   if (isset($_GET['make'])) $and_complete=$make;

//    include('top.php4');

    function show_header()
    {

      /*$name="Специальности";
      $courses_header1=loadtmpl("courses_tracks.html");
      $courses_header1=str_replace("[W-TRACKS]",$name,$courses_header1);
      */
      $name=_("Направления, специальности и курсы");
   //   $static=loadtmpl("courses-static.html");
      $static=show_info_block( 0, "[ALL-CONTENT]", "-~courses~-"  );// выводит информацию блоками
      $courses_header=loadtmpl("all-cHeader.html");
      $courses_header=str_replace("[W-PAGESTATIC]",$static,$courses_header);
      $courses_header=str_replace("[W-PAGENAME]",$name,$courses_header);
      return $courses_header;

    }


   function getSortType() {
   global $s;

      $ret="CID ";
      if (2==$s[user][csort]) $ret="Title ";
      if (3==$s[user][csort]) $ret="Fee ";
      if (4==$s[user][csort]) $ret="Status ";
      if (5==$s[user][csort]) $ret="TypeDes ";


      if (2==$s[user][corder]) $ret.="DESC";
         else $ret.="ASC";

      $ret=" ORDER by ".$ret;
      return $ret;
   }


/*
   function showSortImg($html) {
   global $s;
      $imgname="[SORTIMG".$s[user][csort]."]";
      $imgpath="<img src='[PATH]images/sort_".((2==$s[user][corder]) ? "up" : "down").".gif' border=0>";
      $html=str_replace($imgname,$imgpath,$html);
      $html=str_replace(array("[SORTIMG1]","[SORTIMG2]","[SORTIMG3]","[SORTIMG4]","[SORTIMG5]"),
      array("","","","",""),$html);
      return $html;
   }
*/

    function show_form_header()
    {
      $addcourse=loadtmpl("courses-addcourse.html");
      return $addcourse;
    }


function course_reg_form()
   {
      $day1="";
      $month1="";
      $year1="";

      $longtime=120;
      for($j="1";$j<32;$j++) $day1.="<option value=".day($j).">".day($j)."</option>\n";
      for($j="1";$j<13;$j++) $month1.="<option value=".day($j).">".month($j)."</option>\n";
      for($j=date("Y")-1;$j-8<date("Y");$j++) $year1.="<option value=".$j.">".$j."</option>\n";

        $form=loadtmpl("courses-regcourse.html");
//        $desc="<textarea class=lineinput style='height:135px; width: 250px; ' name='Description'></textarea-->";
      $desc=edit_metadata( read_metadata ( "", COURSES_DESCRIPTION ));
      $form=str_replace("[DESCRIPTION]",$desc,$form);
      $form=str_replace("[DAY]",$day1,$form);
      $form=str_replace("[MONTH]",$month1,$form);
      $form=str_replace("[YEAR]",$year1,$form);
      $form=str_replace("[LONGTIME]",$longtime,$form);

      return $form;

   }

function create_dirs($id)
   {
      $create=array();
      $ret=0;

      if (!is_dir("COURSES/course".$id)) if (!@mkdir("COURSES/course".$id, 0700)) $ret=1;
      if (!@chmod("COURSES/course".$id,0775)) $ret=2;
      if (!is_dir("COURSES/course".$id."/TESTS")) if (!@mkdir("COURSES/course".$id."/TESTS", 0700)) $ret=1;
      if (!@chmod("COURSES/course".$id."/TESTS",0775)) $ret=2;
      if (!is_dir("COURSES/course".$id."/webcam_room_".$id)) if (!@mkdir("COURSES/course".$id."/webcam_room_".$id, 0700)) $ret=1;
      if (!@chmod("COURSES/course".$id."/webcam_room_".$id,0775)) $ret=2;
      if (!is_dir("COURSES/course".$id."/mods")) if (!@mkdir("COURSES/course".$id."/mods", 0700)) $ret=1;
      if (!@chmod("COURSES/course".$id."/mods",0775)) $ret=2;
      if (!is_dir("COURSES/course".$id."/TESTS_ANW")) if (!@mkdir("COURSES/course".$id."/TESTS_ANW", 0700)) $ret=1;
      if (!@chmod("COURSES/course".$id."/TESTS_ANW",0775)) $ret=2;

   return $ret;
   }

function create_java_folder($id,$d)
   {
      global $servletpath;

      $LoginServlet=$servletpath."fsdCreateDir";

      $html=loadtmpl("courses-java.html");
      $html=str_replace("[LoginServlet]",$LoginServlet,$html);
      $html=str_replace("[ID]",$id,$html);
      $html=str_replace("[DO]",$d,$html);

      return $html;
   }

   if($Action=="reg")
   {

//регистрация

          if (isset($_POST['ch_add_teacher'])) {
                  if (isset($_POST['ch_add_teacher']) && ($strLogin=validateEmail($createby))) {
                                  $r = sql("SELECT * FROM People WHERE Login='{$strLogin}'");
                                  if (sqlrows($r)) {
                                          $strLogin .= "_".randString(3);
                                          $r = sql("SELECT * FROM People WHERE Login='{$strLogin}'");
                                          if (sqlrows($r)) {
                                                  $error .= _("Невозможно создать преподавателя: попробуйте другой")." e-mail<br>\n";
                                          }
                                  }
                  } else {
                                  $error.=_("Неверный")." e-mail.<br>\n";
                  }
          }
      if ( empty($Title)) $error.=_("Вы не ввели название курса.")."<br>\n";
      if (!strlen($error))
      {  $result=sql("select Title from $coursestable where Title='$Title'");
         if (sqlrows($result)>0)
            $error.="$Title "._("уже зарегистрирован. Выберите другое название.")."\n";
         else
         {
                 $arrMeta = load_metadata(COURSES_DESCRIPTION);
                 foreach ($arrMeta as $a) {
                         $arrMetaNames[] = $a['name'];
                 }
                 $strDescMeta = set_metadata($_POST, $arrMetaNames, COURSES_DESCRIPTION);

            $query = "INSERT INTO $coursestable (Title,Description,cBegin,cEnd,TypeDes,Status,Fee,createby,createdate,longtime)
                      VALUES ('$Title','$Description','$cBegin','$cEnd','$TypeDes','$Status','$Fee','$createby','$createdate','$longtime')";
            sql($query,"errCR601");
            $newCID=sqllast();

                        // inserts teacher
            $strPassword = randString(7);
                        $r = sql("INSERT INTO People (Login, Password, EMail) values (
                         '{$strLogin}', PASSWORD('{$strPassword}'), '{$createby}')");
                        $idPeople = sqllast();
                        $r = sql("INSERT INTO Teachers (MID, CID) values ('{$idPeople}', '{$newCID}')");
                        mailToteach("forced", $idPeople, $newCID);

            if ($dean)
            {
               //$newCID=getField($coursestable,"CID","Title",$Title);
               $status['create']=create_dirs($newCID);
               $cJavaFolder=create_java_folder($newCID,"create");
               if (!$status['create']) $cool.=_("Курс")." ".$Title." "._("добавлен успешно.");
               else
               {
                  $error.=_("Произошла ошибка при добавлении курса")." ".$Title."<br>";
                  if(1==$status['create']) $error.=_("Не были созданы необходимые каталоги.");
                  else $error.=_("Не были присвоены необходимые права.");
               }
            }
            else
            {
               $addparam['email']=$createby;
               $addparam['description']=$Description;
               $addparam['title']=$Title;
               $addparam['lf']=$createby;
               mailToelearn("fromcourse","",$newCID,$addparam);
               mailToother("fromcourse",$newCID,$addparam);
               $cool.=_("Спасибо за регистрацию курса")." ".$Title." "._("на сервере. Через некоторое время вам будет выслан ответ.");
            }
         }
      }
   }
//   echo "<H1>$Action</H1>";

   $courses_header=show_header();

//   if( sqlget (sql_query(35)) > 0 )
     $new_course_registration=1;
//   else
//    $new_course_registration=0;

   if( $new_course_registration==1 ){
     $reg_form=course_reg_form();
     $form_header=show_form_header();
   }

   $wait=_("Подождите пока будут созданы/удалены каталоги в сервлетной области");

         $html=loadtmpl("courses-main.html");

    $html=str_replace("[TRACKS]",$tmp,$html);
////////////////////////////////////////////////////////////////

   $html=str_replace("[HEADER]",$courses_header,$html);
   $html=str_replace("[FORM_HEADER]",$form_header,$html);
   $html=str_replace("[REG_FORM]",$reg_form,$html);
   $html=str_replace("[COURSE_LIST]",$course_list,$html);

   $html=str_replace("[ERROR]",$error,$html);
   $html=str_replace("[COOL]",$cool,$html);
   $html=str_replace("[WAIT]",$wait,$html);
   $html=str_replace("[CJAVA]",$cJavaFolder,$html);
   $html=str_replace("[ACTION]","courses_add.php4",$html);
   $html=showSortImg($html,$s[user][csort]);

   if ($s[perm]>=3) $html.="<P><a href=courses1c.php?$sess>"._("Импорт курсов из .CSV файла")."</a>";

   $mhtml=show_tb(1);

   $mhtml=str_replace("[ALL-CONTENT]",$html,$mhtml);


   printtmpl($mhtml);

?>