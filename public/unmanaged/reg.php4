<?
require_once('1.php');
require_once('move2.lib.php');
require_once('news.lib.php4');
require_once('courses.lib.php');
require_once('tracks.lib.php');
require_once("metadata.lib.php");
require_once('positions.lib.php');
require_once('lib/classes/Credits.class.php');
require_once('lib/classes/Chain.class.php');

$DEBUG=FALSE;
define("ELDER", 1920);
define("MEAN", 1970);

$GLOBALS['controller']->page_id = "m0102";
$GLOBALS['controller']->view_root->children['link_group']->children = false;
$GLOBALS['controller']->setHeader((isset($Course) || isset($trid)) ? _('Подать заявку') : _('Персональные данные'));

//Разбираем константу REGISTRATION_FORM, которая определяет вид формы регистрации
$reg_form_items = explode(";", REGISTRATION_FORM);
$boolCivil = (defined("LOCAL_REGINFO_CIVIL") && LOCAL_REGINFO_CIVIL);
if (isset($showMID))
   $showMID=intval($showMID);
if (isset($s[mid]) && $s[login])
   $mid=$s['mid'];
unset($regcourse,$regtrack,$teacher);
if (isset($mytypereg)) {
   switch ($mytypereg) {
      case "only_edit":
            if(isset($_GET['Course'])) {
               $regcourse = 1;
            }
            if (isset($_POST['Track'])) {
                $regtrack = 1;
            }
      break;
      case "new_student":
         $regcourse=1;
      break;
      case "new_teacher":
         $teacher=1;
         $regcourse=1;
      break;
   }
}

$allow = true;
if($mid)
    $allow = check_students_permissions(22, $mid);

//echo "mytypereg: ".$mytypereg."<br />";

if (!(defined("LOCAL_FREE_REGISTRATION") && LOCAL_FREE_REGISTRATION))
   $view = 1;

$direct_registration=0;
if( isset( $Course ) ) {
   $GLOBALS['controller']->setCurTab('m010201');
   $direct_action="<input type='hidden' name=Course value=".(int) $Course." /><H3>".cid2title($Course)."</H3> "; //_("регистрация на учебный курс");
   $direct_registration=1;
}

if( isset( $trid ) ) {
   $direct_action="<input type='hidden' name=Track value=".(int) $trid."><H3>".getTrackName($trid)."</H3> "; //_("регистрация на специальность");
   $direct_registration=2;
}
if( isset( $view ) ) {
   $direct_action="<span><H3>"._("Личные данные")."</H3></span>";
   $direct_registration=3;
}
$Password=_("не был изменен")." (not replaced)";

// Обработка фотографий
// НАЧАЛО
//

   // удаление фото
if (isset($deletefoto)) {
   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
      $res=sql("DELETE FROM filefoto WHERE mid=$s[mid]","errRE490");
      exit(refresh("$PHP_SELF?$sess"));
   }

   // показ картинки на экран
if (isset($getimg)) {
   intvals("getimg");
   $r=sqlval("SELECT * FROM filefoto WHERE mid=$getimg","errRE428");
   if (!is_array($r))
      exit(header("Location: images/people/nophoto.gif"));
   else {
        
       
       
       
       exit(header("Location: images/people/nophoto.gif"));
   }
  /* header("Content-type: image/gif");
   header("Cache-control: no-cache");
   /*
   header("Cache-control: public");
   header("Cache-control: max-age=".(60*60*24*7));
   * /
   header("Last-Modified: ".date("r",$r[last]));
   echo $r[foto];
   exit;*/
}

   // форма аплода фотографии
if (isset($upload1)) {
   $GLOBALS['controller']->setHeader(_("Загрузка вашей фотографии на сервер"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHelpSection('upload-photo');
   if (!$s[login])
      exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   $GLOBALS['controller']->captureFromOb(TRASH);
   echo show_tb();
   echo ph(_("Загрузка вашей фотографии на сервер"));
   echo "
      <a href=javascript:history.go(-1)>&lt;&lt; "._("назад")."</a>";
   $GLOBALS['controller']->captureStop(TRASH);
   echo "
      <form action=$PHP_SELF method=post enctype='multipart/form-data'>
      <!--<input type=hidden name=MAX_FILE_SIZE value=102400>-->
      <table><tr><td>
      <input type=hidden name=upload2 value=\"\">
      <input name=upload type=file";

   if(!$allow)
      echo " disabled";

   echo " class=s8></td><td>";
   echo okbutton(_("Отправить"), (!$allow?'disabled':'').'class=s8');

   echo "
      </td></tr></table></form>
      "._("Фото должно быть не больше 100Кб")."
      <!--"._("Рекомендуются фотографии размера")." {$foto_image_maxx}х{$foto_image_maxy} "._("пикселей").".
      "._("Фотографии большего размера будут сжиматься.")."--><P>
      "._("Текущая фотография:")."<P>
      <img src=$PHP_SELF?getimg=$s[mid]&rnd=".time()."$sess><P>";

   if($allow)
      if (!$GLOBALS['controller']->enabled)
      echo "<a href='$PHP_SELF?deletefoto=1$sess' onclick='return confirm(\""._("Действительно удалить?")."\")'>"._("Удалить фото")."</a>";
      $GLOBALS['controller']->setLink('m010206');
      $GLOBALS['controller']->captureStop(CONTENT);
      echo show_tb();
      exit;
}

   // пост от формы аплода
if (isset($upload2)) {
   $error = 0;

   if (!$s[login]) {
      exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   }

   $upload=$_FILES['upload'];

   if ($upload[size]<1 || empty($upload['name']) || empty($upload['tmp_name'])) {
       $error = 1;
       if ($GLOBALS['controller']->enabled) {
           $GLOBALS['controller']->setMessage(_("Фото не было загружено"));
           $GLOBALS['controller']->terminate();
           refresh($sitepath.'reg.php4?upload1=foto');
       }
       else
       exit(_("Фото не было загружено"));
   }

   if ($upload[size]>102400) {
       $error = 2;
       if ($GLOBALS['controller']->enabled) {
           $GLOBALS['controller']->setMessage(_("Фото не должно быть более 102400 байт (100Кб)"));
           $GLOBALS['controller']->terminate();
           refresh($sitepath.'reg.php4?upload1=foto');
       }
       else
       exit(_("Фото не должно быть более 102400 байт (100Кб)."));
   }

   $fn="$tmpdir/foto_".session_id().time();
   $fn="$tmpdir/asd";
   move_uploaded_file($upload[tmp_name],$fn);

   if (!file_exists($fn)) {
       $error = 3;
       if ($GLOBALS['controller']->enabled) {
           $GLOBALS['controller']->setMessage(_("Не удалось скопировать файл, нет прав записи в")." tmpdir");
           $GLOBALS['controller']->terminate();
           refresh($sitepath.'reg.php4?upload1=foto');
       }
       else
       exit(_("Не удалось скопировать файл, нет прав записи в")." $tmpdir");
   }

   $buf=gf($fn);
   $imsize=@getimagesize($fn);

   if (!is_array($imsize) || count($imsize)<4 || $imsize[0]==0 && $imsize[1]==0) {
       $error = 4;
       if ($GLOBALS['controller']->enabled) {
           $GLOBALS['controller']->setMessage(_("Загруженный файл не является картинкой GIF, JPG или PNG"));
           $GLOBALS['controller']->terminate();
           refresh($sitepath.'reg.php4?upload1=foto');
       }
       else
       exit(_("Загруженный файл не является картинкой GIF, JPG или PNG."));
   }

   @unlink($fn);
   $data_buf = unpack("H*hex", $buf);
   $res = sql("SELECT mid FROM filefoto WHERE mid='{$s[mid]}'");
   if (sqlrows($res) && !$error) {
    if(dbdriver == "mssql"){
       $res=sql("UPDATE filefoto SET
                    last=".time().",
                    fx=$imsize[0],
                    fy=$imsize[1],
                    foto=0x".$data_buf['hex']."
                    WHERE mid={$s['mid']}
                    ","errRE457");
    } else {
       $res=sql("UPDATE filefoto SET
                    last=".time().",
                    fx=$imsize[0],
                    fy=$imsize[1],
                    foto='0'
                    WHERE mid={$s['mid']}
                    ","errRE457");
    }
   } else {
    if(dbdriver == "mssql"){
       $res=sql("INSERT INTO filefoto (mid, last, fx, fy, foto) VALUES (
                    {$s['mid']},
                    ".time().",
                    {$imsize[0]},
                    {$imsize[1]},
                    0x".$data_buf['hex'].")
                    ","errRE457");
    } else {
       $res=sql("INSERT INTO filefoto (mid, last, fx, fy, foto) VALUES (
                    {$s['mid']},
                    ".time().",
                    {$imsize[0]},
                    {$imsize[1]},
                    '0')
                    ","errRE457");
    }
   }
   sqlfree($res);

   $table_f =(dbdriver == "mssql") ? "'filefoto'" : "filefoto";
   $buf =(dbdriver == "mssql") ? "0x".$data_buf['hex'] : $buf;
   $adodb->UpdateBlob($table_f, 'foto',$buf,"mid='".$s[mid]."'");

   alert(_("Фото загружено!"));
   refresh("$PHP_SELF?$sess");
   exit;
}

//
// КОНЕЦ обработки фоток
//

echo show_tb();
//echo "<table border=0 cellpadding=0 cellspacing=0 align=center width='525'>
//       <tr><td width=100% class='forum'>";
if (!$login) $GLOBALS['controller']->captureFromOb(CONTENT);
else $GLOBALS['controller']->captureFromOb('m010201');

if (has_info_blocks("-~reg~-") && !isset($GLOBALS['s']['perm'])){
    echo show_info_block( 0, "[ALL-CONTENT]", "-~reg~-"  );// выводит информацию блоками
}

$GLOBALS['controller']->captureFromOb(TRASH);
echo "</td></tr>";
echo "<tr><td width=100%>";
echo "</td></tr><tr><td width=100% class='forum'><span class=msg>";
if (isset($Action)) {
   if(isset($_POST['yearB']) && $_POST['monthB'] && $_POST['dayB'])
      $BirthDate=$_POST['yearB']."-".$_POST['monthB']."-".$_POST['dayB'];
   if(isset($_POST['yearC']) && $_POST['monthC'] && $_POST['dayC'])
      $conferDate=$_POST['yearC']."-".$_POST['monthC']."-".$_POST['dayC'];
   if(isset($_POST['yearP']))
      $PositionDate=$_POST['yearP']."-1-1";
   $Login=return_valid_value($_POST['Login']);
   $LastName=return_valid_value($_POST['LastName']);
   $FirstName=return_valid_value($_POST['FirstName']);
   $Patronymic=(isset($_POST['Patronymic'])) ? return_valid_value($_POST['Patronymic']) : "";
   $Phone=(isset($_POST['Phone'])) ? return_valid_value($_POST['Phone']) : "";
   $Address=(isset($_POST['Address'])) ? return_valid_value($_POST['Address']) : "";
   $Fax=(isset($_POST['Fax'])) ? return_valid_value($_POST['Fax']) : "";
   $rnid=(isset($_POST['rnid'])) ? return_valid_value($_POST['rnid']) : "";
   $Position=(isset($_POST['Position'])) ? return_valid_value($_POST['Position']) : "";
   $PositionPrev=(isset($_POST['PositionPrev'])) ? return_valid_value($_POST['PositionPrev']) : "";
   $CellularNumber=(isset($_POST['CellularNumber'])) ? return_valid_value($_POST['CellularNumber']) : "";
   $ICQNumber=(isset($_POST['ICQNumber'])) ? return_valid_value($_POST['ICQNumber']) : "";
   $BirthDate=(isset($BirthDate)) ? return_valid_value($BirthDate) : "";
   $Information = ($boolCivil) ? ((isset($_POST['Information'])) ? return_valid_value($_POST['Information']) : "") : $conferDate;

   switch ( $Action ) {
      case "reg": //регистрация человека
         //проверка валидности заполнения формы
         $meta_information = "";
         foreach($reg_form_items as $key => $value) {
             $meta_information .= "block=".$value."~";
             $meta_information .= trim(set_metadata($_POST, get_posted_names($_POST), $value),"~");
             $meta_information .= "[~~]";
         }
         $meta_information = trim($meta_information, "[~~]");
         $boolNotValidEmail = (NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL) ? (!validateEmail($_POST['EMail']) || empty($_POST['EMail'])) : 0;
         if(empty($_POST['Login'])||empty($_POST['LastName'])||empty($_POST['FirstName'])||$boolNotValidEmail || (intval($Course) < 0))
            $valid=0;
         else
            $valid=1;

         if(!$valid) {
            if ($GLOBALS['controller']->enabled)
            $GLOBALS['controller']->setMessage(_("Не все необходимые поля заполнены"));
            else
            echo _("Не все необходимые поля заполнены.")."\n";
         }
         else {
            $query = "select * from $peopletable where login='$Login'";
            $result=sql($query,"errREG520");
            if(sqlrows($result)>0) {
                if ($GLOBALS['controller']->enabled)
                $GLOBALS['controller']->setMessage("$Login "._("уже зарегистрирован. Выберите другой логин"));
                else
               echo  "$Login "._("уже зарегистрирован. Выберите другой логин.")."\n";
            }
            else {
               $Password=randString(7);
               $query = "INSERT INTO $peopletable
                             (mid_external,Login,Password,LastName,FirstName,Patronymic,EMail,Information,rnid,preferred_lang)
                          VALUES
                             (".$GLOBALS['adodb']->Quote($mid_external).",".$GLOBALS['adodb']->Quote($Login).", PASSWORD(".$GLOBALS['adodb']->Quote($Password)."),".$GLOBALS['adodb']->Quote($LastName).",".$GLOBALS['adodb']->Quote($FirstName).",".$GLOBALS['adodb']->Quote($Patronymic).",".$GLOBALS['adodb']->Quote($EMail).",".$GLOBALS['adodb']->Quote($meta_information).",".$GLOBALS['adodb']->Quote((int)$rnid).", ".$GLOBALS['adodb']->Quote((int)$_POST['ra_preferred_lang']).")";
               sql($query,"errREG521");
               $mid = sqllast();
               if ( isset($Course) && isset($mid) && isset($regcourse)){

                  if (defined('NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL') && NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL) {
                      $GLOBALS['controller']->setView('Document');
                      $GLOBALS['controller']->setMessage(_("Вы зачисленны на курс. Пароль будет выслан на e-mail"), JS_GO_URL, 'index.php');
                  }else {
                      $GLOBALS['controller']->setMessage(_('Спасибо за регистрацию'), JS_GO_URL, 'index.php');
                  }


                  if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM && !$teacher) {
                      if (CCredits::checkCourseRegistrationPossibility($Course,$mid)) {
                          registration2course($Course, $mid, $teacher);
                          CCredits::payCourse($mid,$Course);
                      } else {
                          $GLOBALS['controller']->setMessage(_("Извините, но у вас недостаточно кредитов"));
                      }
                  } else {
                      //if (defined('NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL') && NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL)
                      //echo  _("Ваша заявка на курс принята к рассмотрению. О результате Вам сообщат по e-mail").".\n";
                      //else
                      //echo  "Спасибо за регистрацию.\n";
                      registration2course($Course, $mid, $teacher, '', &$messsage);
                      $GLOBALS['controller']->setMessage($messsage, JS_GO_URL, 'index.php');                      
                  }
               }
               if ( isset($trid)  ) {
                  if (defined('NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL') && NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL)
                  $GLOBALS['controller']->setMessage(_("Ваша заявка на курс принята к рассмотрению. О результате Вам сообщат по e-mail"), JS_GO_URL, 'index.php');
                  else
                  $GLOBALS['controller']->setMessage(_("Спасибо за регистрацию"), JS_GO_URL, 'index.php');
                  registration2track( $trid, $mid, $teacher);
               }
               $dont_view_form = true;
            }
         }
      break;
      case "change":
     //изменение личных данных
         if (isset($s['mid'])) {
            $boolValidEmail = (NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL) ? validateEmail($EMail) : 1;
            if((strlen($Login) || strlen($LastName) || strlen($FirstName)) && $boolValidEmail) {

               // проверка на присутствие логина
               $sql = "SELECT Login FROM People WHERE Login = ".$GLOBALS['adodb']->Quote($Login)." AND MID <> '".(int) $_SESSION['s']['mid']."'";
               $res = sql($sql);
               if (sqlrows($res)) {
                    $GLOBALS['controller']->setMessage("$Login "._("уже зарегистрирован. Выберите другой логин"),JS_GO_URL,'reg.php4');
                    $GLOBALS['controller']->terminate();
                    exit();
                    $Login = '';
               }

               //загрузка фото
               if (is_array($_FILES['avatar']) && !$_FILES['avatar']['error']) {
                   $avatarPath = $GLOBALS['tmpdir'].'/avatar';
                   move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath);
                   makePreviewImage($avatarPath,$avatarPath,114,152);
                   if (!file_exists($avatarPath)) {                                             
                       $GLOBALS['controller']->setMessage(_("Не удалось скопировать файл, нет прав записи в")." $avatarPath",JS_GO_URL, $GLOBALS['sitepath'].'reg.php4');
                       $GLOBALS['controller']->terminate();
                       exit();
                   }
                   if (filesize($avatarPath) > 12000) {                       
                       $GLOBALS['controller']->setMessage(_("Размер файла превышает 10 Кбайт"),JS_GO_URL, $GLOBALS['sitepath'].'reg.php4');
                       $GLOBALS['controller']->terminate();
                       exit();
                   }
                   
                   $buf=gf($avatarPath);
                   $imsize=@getimagesize($avatarPath);
                   @unlink($avatarPath);
                   $data_buf = unpack("H*hex", $buf);                   
                   if (!is_array($imsize) || count($imsize)<4 || $imsize[0]==0 && $imsize[1]==0) {                                             
                       $GLOBALS['controller']->setMessage(_("Загруженный файл не является картинкой GIF, JPG или PNG."),JS_GO_URL, $GLOBALS['sitepath'].'reg.php4');
                       $GLOBALS['controller']->terminate();
                       exit();
                   }
                                      
                   $res3 = sql("SELECT mid FROM filefoto WHERE mid={$s['mid']}","errFF001");
                   if(sqlrows($res3)){    
                      if(dbdriver == "mssql") {
                         $res_img="
                                UPDATE filefoto SET
                                last=".time().",
                                fx='{$imsize[0]}',
                                fy='{$imsize[1]}',
                                foto=0x".$data_buf['hex']."
                                WHERE mid='{$s['mid']}'";    
                      }else{
                        $res_img="
                               UPDATE filefoto SET
                                last=".time().",
                                fx='{$imsize[0]}',
                                fy='{$imsize[1]}',
                                foto='0'
                                WHERE mid='{$s['mid']}'";
                      }
                    }else{
                      if(dbdriver == "mssql") {
                          $res_img="
                          INSERT INTO filefoto (mid, last, fx, fy, foto)
                              VALUES (
                              '{$s['mid']}',
                              '".time()."',
                               '{$imsize[0]}',
                              '{$imsize[1]}',
                              0x".$data_buf['hex'].")";        
                      }else{
                            $res_img="
                            INSERT INTO filefoto (mid, last, fx, fy, foto)
                            VALUES (
                            '{$s['mid']}',
                            '".time()."',
                            '{$imsize[0]}',
                            '{$imsize[1]}',
                            '0')";
                      }
                   }
                   $res = sql($res_img,"errFF002");
                   sqlfree($res);
                   $table_f =(dbdriver == "mssql") ? "'filefoto'" : "filefoto";
                   $buf =(dbdriver == "mssql") ? "0x".$data_buf['hex'] : $buf;
                   $GLOBALS['adodb']->UpdateBlob($table_f, 'foto',$buf,"mid=".$GLOBALS['adodb']->Quote($s['mid'])."");
                   
               }elseif ($_FILES['avatar']['error']) {
                   switch ($_FILES['avatar']['error']) {
                       case 4:
                           //файл не передан, удаляем фотку
                           sql("DELETE FROM `filefoto` WHERE mid='{$s['mid']}'");                  
                           break;
                       default:                           
                           $GLOBALS['controller']->setMessage(_("При загрузке файла произошла ошибка"),JS_GO_URL, $GLOBALS['sitepath'].'reg.php4');
                           $GLOBALS['controller']->terminate();
                           exit();
                           break;
                   }
               }

               $meta_information = "";
               foreach($reg_form_items as $key => $value) {
                  $meta_information .= "block=".$value."~";
                  $meta_information .= trim(set_metadata($_POST, get_posted_names($_POST), $value),"~");
                  $meta_information .= "[~~]";
               }
               $meta_information = trim($meta_information, "[~~]");
    
               $query = "UPDATE $peopletable SET ";
               if(!empty($mid_external))
               		$query .= "mid_external=".$GLOBALS['adodb']->Quote($mid_external).", ";
               if(!empty($LastName))
               		$query .= "LastName=".$GLOBALS['adodb']->Quote($LastName).", ";
               if(!empty($FirstName))
               		$query .= "FirstName=".$GLOBALS['adodb']->Quote($FirstName).", ";
               if(!empty($Patronymic))
               		$query .= "Patronymic=".$GLOBALS['adodb']->Quote($Patronymic).", ";
               if(!empty($EMail))
               		$query .= "EMail=".$GLOBALS['adodb']->Quote($EMail).", ";
               if(!empty($Phone))
               		$query .= "Phone=".$GLOBALS['adodb']->Quote($Phone).", ";
               if(!empty($Fax))
               		$query .= "Fax=".$GLOBALS['adodb']->Quote($Fax).", ";
               if(!empty($CellularNumber))
               		$query .= "CellularNumber=".$GLOBALS['adodb']->Quote($CellularNumber).", ";
               if(!empty($ICQNumber))
               		$query .= "ICQNumber=".$GLOBALS['adodb']->Quote($ICQNumber).", ";
               if(!empty($BirthDate))
               		$query .= "BirthDate=".$GLOBALS['adodb']->Quote($BirthDate).", ";
               if(!empty($meta_information))
               		$query .= "Information=".$GLOBALS['adodb']->Quote($meta_information).", ";
               if(!empty($rnid))
               		$query .= "rnid=".$GLOBALS['adodb']->Quote($rnid).", ";
               if(!empty($Position))
               		$query .= "Position=".$GLOBALS['adodb']->Quote($Position).", ";
               if(!empty($PositionDate))
               		$query .= "PositionDate=".$GLOBALS['adodb']->Quote($PositionDate).", ";
               if(!empty($PositionPrev))
               		$query .= "PositionPrev=".$GLOBALS['adodb']->Quote($PositionPrev).", ";
               if(!empty($_POST['ra_preferred_lang']))
               		$query .= "preferred_lang=".$GLOBALS['adodb']->Quote((int)$_POST['ra_preferred_lang']).", ";
               $query .= "Login=".$GLOBALS['adodb']->Quote($Login)." WHERE MID='".(int) $s['mid']."'";
               //$query = substr($query,0,-2)." WHERE MID='".(int) $s['mid']."'"; 
               sql($query,"errREG522");
               
                if ($GLOBALS['controller']->enabled)
                $GLOBALS['controller']->setMessage(_("Ваши регистрационные данные были сохранены"));
                else
               echo  _("Ваши регистрационные данные были сохранены.")."<br>";
            }
            else {
                if (!isset($_GET['redirect'])) {
                if ($GLOBALS['controller']->enabled)
                $GLOBALS['controller']->setMessage(_("Неверно заполнена регистрационная форма.")."<br>"._("Провертьте поля:<ul><li>имя;</li><li>фамилия;</li><li>e-mail;</li></ul>."));
                else
               echo _("Неверно заполнена регистрационная форма.")."<br>"._("Провертьте поля \"имя\", \"фамилия\", \"e-mail\".")."<br>";
                }
            }

            if ($regcourse) {
                $redirect_url = $message = "";
                if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM && !$teacher) {
                    if (CCredits::checkCourseRegistrationPossibility($Course,$mid)) {
                        if (registration2course( $Course, $mid, $teacher, &$redirect_url, &$message )) {
                            if (isset($_GET['redirect'])) {
                                $GLOBALS['controller']->setMessage($message, JS_GO_URL, $redirect_url);
                            }
                            else {
                                $GLOBALS['controller']->setMessage($message);
                            }
                            CCredits::payCourse($mid,$Course);
                        }
                        else {
                            $GLOBALS['controller']->setMessage(_("Ошибка регистрации"));
                        }
                    }
                    else {
                        $GLOBALS['controller']->setMessage(_("Извините, но у вас недостаточно кредитов"));
                    }
                }
                else {
                   if (registration2course( $Course, $mid, $teacher, &$redirect_url, &$message )) {
                        if (isset($_GET['redirect'])) {
                            $GLOBALS['controller']->setMessage($message, JS_GO_URL, $redirect_url);
                        }
                        else {
                            $GLOBALS['controller']->setMessage($message);
                        }
                   }
                   else {
                       $GLOBALS['controller']->setMessage(_("Ошибка регистрации"));
                   }
                }
            }

            if ($regtrack) {
                   if (registration2track( $_POST['Track'], $mid, $teacher )) {
                        //if ($GLOBALS['controller']->enabled)
                        $GLOBALS['controller']->setMessage(_("Вы зачислены на специальность")." (".getTrackName($Track).")");
                        //else
                        //echo  "Вы зачислены на специальность (".getTrackName($_POST['Track']).").\n\n";
                   } else {
                        if ($GLOBALS['controller']->enabled)
                        $GLOBALS['controller']->setMessage(_("Ошибка регистрации Возможно Вы уже зачислены на специальность")." (".getTrackName($Track).")");
                        else
                        echo  _("Ошибка регистрации. Возможно Вы уже зачислены на специальность")." (".getTrackName($Track).")";
                   }
            }
            $s['user']['fname'] = !$_GET['redirect']?$FirstName:$s['user']['fname'];
            $s['user']['lname'] = !$_GET['redirect']?$LastName:$s['user']['lname'];
         }
         else {
                if ($GLOBALS['controller']->enabled)
                $GLOBALS['controller']->setMessage(_("Необходимо указать верные логин и пароль чтобы изменить информацию о пользователе."));
                else
            echo  _("Необходимо указать верные логин и пароль чтобы изменить информацию о пользователе.")."\n\n";
         }
      break;
      default:
   }
}


if(!$dont_view_form) {
   echo "</span><br>
   <br>";
   $GLOBALS['controller']->captureStop(TRASH);
  if (!$GLOBALS['controller']->enabled) {
      echo "
             <table width=100% border=0 cellspacing=0 cellpadding=0>";
      echo "<tr><th width=100% class=brdr>"._("Поля регистрации")."</th></tr>";
  }
  echo "
            <form method=post name='reg' action='' onsubmit='javascript: return validateRegForm();' method=post enctype='multipart/form-data'>";
   if(isset($showMID)) {
      $result=sql("SELECT * FROM $peopletable WHERE MID=$showMID","errREG528");
      $row=sqlget($result);
      if (sqlrows($result)>0)
         extract($row);
   }
   elseif(isset($s['mid'])) {
      echo "<input type=hidden name='Action' value='change'>";
      $result=sql("SELECT * FROM $peopletable WHERE MID='".$s['mid']."'","errREG529");
      $row=sqlget($result);
      if (sqlrows($result)>0)
         extract($row);
   }
   else {
      $Information = $meta_information;
      echo "<input type=hidden name='Action' value='reg'>";
   }

   if (!$GLOBALS['controller']->enabled) {
   echo ' <tr class=questt><td colspan=2 class=brdr2>';
   echo '
           <table  width=100%  cellspacing=0 cellpadding=0  border=0>
            <tr>
             <td colspan=4 height=15><img src="images/spacer.gif" width=1 height=1></td>
            </tr>
            <tr>
             <td height=15><img src="images/spacer.gif" width=1 height=1></td>
           <td height=15 colspan=2>';
   }
   if( $direct_registration ) { // т.е. мы не знаем куда именно он решгистрируется - на какой курс или специальность
      echo "<span class=hidden2>";
   }
   if (!isset($showMID)) {
      if(isset($s['mid'])) {
         if ($GLOBALS['controller']->enabled) echo "<input type=hidden name=mytypereg value=only_edit>";
         else
         echo _("Ваш выбор:")."<br>
            <input type=radio name=mytypereg id=my1 value=only_edit checked><label for=my1>"._("Изменение моих регистрационных данных")."</label><br>
            <input type=radio name=mytypereg id=my2 value=new_student><label for=my2>"._("Прошу зачислить меня на новый курс как учащегося (выберите курс)")."</label><br>
            <input type=radio name=mytypereg id=my3 value=new_teacher><label for=my3>"._("Прошу назначить меня преподавателем на новый курс (выберите курс)")."</label><br>";
      }
      else {
         if ($GLOBALS['controller']->enabled) echo "<input type=hidden name=xmytypereg value=''>";
         echo _("Ваш выбор:")."<br>
            <input type=checkbox name=xmytypereg checked disabled>"._("Прошу зарегистрировать меня")."<br>
            <input type=radio name=mytypereg id=my2 value=new_student checked><label for=my2>"._("Прошу зачислить меня на новый курс как учащегося (выберите курс)")."</label><br>
            <input type=radio name=mytypereg id=my3 value=new_teacher><label for=my3>"._("Прошу назначить меня преподавателем на новый курс (выберите курс)")."</label><br>";
      }
   }
   else {
      if (!$GLOBALS['controller']->enabled)
      echo '&nbsp;
         <td/>
         <td height=15><img src="images/spacer.gif" width=1 height=1></td>
         <td height=15><img src="images/spacer.gif" width=1 height=1></td>
        </tr>
        <tr>
           <td width=15 height=15><img src="images/spacer.gif" width=1 height=1></td>
           <td  height=15 colspan=2>';
   }
   if( $direct_registration ) { // т.е. мы не знаем куда именно он решгистрируется - на какой курс или специальность
      echo "</span>";
   }
   if(!isset($showMID)) {
      if(!$direct_registration ) {
         if (!$GLOBALS['controller']->enabled)
         echo selectOpenCourses( )."<br>";
      }
      else {
        if (!$GLOBALS['controller']->enabled)
        echo $direct_action;
      }
   }

   if (!$GLOBALS['controller']->enabled) {
?>
     </td>
    </tr>
    </table>
<?php
   }
?>
    <table class=main cellspacing=0>
    <?php if ($GLOBALS['controller']->enabled && $direct_registration) echo "<tr><td colspan=2>$direct_action</td></tr>";?>
    <?php if ($s['mid']) :?>
    <tr>
     <td rowspan="2"><?=_("Фотография");?></td>
     <td align="left">
        <?=strip_tags(getPhoto($showMID, 1, $foto_image_maxx, $foto_image_maxy), '<img>')?>
     </td>     
    <tr>
        <td>
            <script language="javascript">
                function toggleAvatar(checkbox, avatar) {
                    if (checkbox.is(':checked')) {
                        avatar.removeAttr('disabled');
                    } else {
                        avatar.attr('disabled', 'disabled');
                    }
                }
                $(function () {
                    toggleAvatar($("#editAvatar"), $("#avatar"));
                    $("#editAvatar").click(function() {
                        toggleAvatar($(this), $("#avatar"));
                    });
                    
                });
            </script>
            <input type="file" name="avatar" id='avatar' disabled = "disabled" /><br />
            <input type="checkbox" id="editAvatar" /> <?=_("Изменить фото");?>            
     </td>
    </tr>
    <?php endif;?>
    <tr>
     <td valign="top" align="left">
       <?=_("Учетное имя (логин)")?> <?=getIcon("*")?>
     </td>
     <td valign="top" align="left">
		<input type="text" size="10" name="Login"<? if (isset($Login)) echo " value=\"".htmlspecialchars($Login,ENT_QUOTES)."\""; if (isset($showMID)) echo " disabled"; if (isset($showMID)) echo " disabled"; if (!$allow) echo " disabled";?>></td>
		<? //if (isset($Login)) echo htmlspecialchars($Login,ENT_QUOTES); ?>
     </td>
    </tr>
    <tr>
     <td><?=_("Фамилия")?> <?=getIcon("*")?></td>
     <td>
      <input type="text" size="25" name="LastName"<? if (isset($LastName)) echo " value=\"".htmlspecialchars($LastName,ENT_QUOTES)."\""; if (isset($showMID)) echo " disabled"; if (isset($showMID)) echo " disabled"; if (!$allow) echo " disabled";?>>
     </td>
    </tr>
    <tr>
     <td><?=_("Имя")?> <?=getIcon("*")?></td>
     <td><input type="text" size="25" name="FirstName"
     <?
     if (isset($FirstName))
        echo " value=\"".htmlspecialchars($FirstName,ENT_QUOTES)."\"";
     if (isset($showMID))
        echo " disabled";
     if (!$allow) echo " disabled";?>>
     </td>
    </tr>
    <tr>
     <td><?=_("Отчество")?></td>
     <td>
      <input type="text" size="25" name="Patronymic"
      <?
      if (isset($Patronymic))
         echo " value=\"".htmlspecialchars($Patronymic,ENT_QUOTES)."\"";
      if (isset($showMID))
         echo " disabled";
      if (!$allow) echo " disabled";?>>
     </td>
    </tr>
<?
if (NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL == true) {?>
    <tr>
     <td>E-mail <?=getIcon("*")?></td>
     <td><?
     if (isset($showMID))
        echo "<a href='mailto:".htmlspecialchars($EMail,ENT_QUOTES)."'>";?>
      <input type="text"  size="25" name="EMail"
<?   if (!empty($EMail))
        echo " value=\"".htmlspecialchars($EMail,ENT_QUOTES)."\"";
     if (isset($showMID))
        echo " disabled";
     if (!$allow) echo " disabled";?>>
    </td>
   </tr>
<?
}?>
<?
    if (strlen(REGISTRATION_FORM))
    foreach($reg_form_items as $key => $value) {?>
    <tr>
     <td><?=get_reg_block_title($reg_form_items[$key]);?></td>
     <td><?
     if (isset($showMID))
     {
        echo view_metadata(read_metadata ($Information, $reg_form_items[$key]));
     }
     elseif (!$allow)
     {
        echo view_metadata(read_metadata ($Information, $reg_form_items[$key]));
     }
     else {
        $temp_data = read_metadata($Information, $reg_form_items[$key]);
        echo edit_metadata($temp_data);
        if (is_array($temp_data) && count($temp_data)) {
            if(is_array(current($temp_data))) {
               foreach($temp_data as $td_key => $td_value) {
                 if(array_key_exists("reg_exp", $td_value)) {
                    $reg_exp[$td_value['name']] = $td_value['reg_exp'];
                 }
               }
            }
            else {
                 $element = current($temp_data);
                 if(array_key_exists("reg_exp", $temp_data[$element])) {
                    $reg_exp[$td_value['name']] = $element['reg_exp'];
                 }

            }
        }

     }


     ?>

     </td>
    </tr>
<? }
?>
    <!--
    <tr>
     <td valign="middle" align="left">
       <?=_("Табельный номер")?>
     </td>
     <td valign="top" align="left">
      <input type="text" size="10" name="mid_external"<? if (isset($mid_external)) echo " value='".htmlspecialchars($mid_external,ENT_QUOTES)."'"; if (isset($showMID)) echo " disabled"; if (isset($showMID)) echo " disabled"; if (!$allow) echo " disabled";?>></td>
     </td>
    </tr>
    //-->
<?
if (count($GLOBALS['controller']->lang_controller->langs) > 1) {
?>
     <tr><td colspan="2"><?=_("Язык интерфейса по умолчанию")?>:<br>

<?
	foreach ($GLOBALS['controller']->lang_controller->langs as $lang) {
		$selected = ($lang->id == $preferred_lang) ? 'checked' : '';
		echo "<input type='radio' name='ra_preferred_lang' value='{$lang->id}' {$selected}>&nbsp;<img src='{$sitepath}{$lang->dir}/images/icons/lang.gif' align='absmiddle'>&nbsp;{$lang->title}&nbsp;&nbsp;&nbsp;";
	}
?>
     </td></tr>

<?
}
}
   echo "
   <script language='javascript'>
    function validateRegForm() {
            isValid = true;
  ";

   if((isset($reg_exp))&&(count($reg_exp)>0)) {
       foreach($reg_exp as $key => $value) {
           echo "re_".$key." = /".$value."/;\n";
           echo "
                 if(re_".$key.".test(document.reg.".$key.".value)) {

                 }
                 else  {
                  isValid = false;
                 }";
           //alert('Проверяем форму');

       }
       echo "
       if(isValid) {
        return true;
       }
        else {
         alert('"._("Неправильно заполнены регистрационные данные серии и номера паспорта")."');
         return false;
        }";
   }
   echo "}
   </script>";


/*?>
   <tr>
    <td class=cHilight><?=get_reg_block_title($reg_form_items[2]);?></td>
     <td><?
      if (isset($showMID))
         echo view_metadata(read_metadata ($Information, $reg_form_items[2]));
      else
         echo edit_metadata(read_metadata ($Information, $reg_form_items[2]));?>
     </td>
    </tr>
    <tr>
     <td class=cHilight><?=get_reg_block_title($reg_form_items[3]);?></td>
     <td><?
      if (isset($showMID))
         echo view_metadata(read_metadata ($Information, $reg_form_items[3]));
      else
         echo edit_metadata(read_metadata ($Information, $reg_form_items[3]));?>
     </td>
    </tr>
<?*/?>
<?  if(!isset($showMID)) {
       $strConfirm = (defined("LOCAL_FREE_REGISTRATION") && LOCAL_FREE_REGISTRATION) ? "onClick=\"if (!confirm('"._("ВНИМАНИЕ! В случае не соответствующих действительности регистрационных данных администрация сервера оставляет за собой право ОТКЛОНИТЬ настоящую заявку.")."')) return false;\"" : "";
?>  <tr>
     <td colspan=2 align="right" class="shedaddform">
<?php
if (($GLOBALS['controller']->enabled)&&($allow)) echo okbutton();
else {
?>
     <input type="image" id="add_schedule_send" onMouseOver="this.src='images/send_.gif';"  onMouseOut="this.src='images/send.gif';"
     src="images/send.gif" align="right" alt="ok" border=0 <?=$strConfirm?> <?if (!$allow) echo " disabled";?>>
<?php
}
?>
     </td>
    </tr>
 <?}?>
     </form>
<?php
if (!$GLOBALS['controller']->enabled) {
?>
    </table>
<?php
}
if (!$GLOBALS['controller']->enabled) {
?>
<?=getIcon("*")?> - <?=_("обязательные поля")?>.
<?
}
   if ($GLOBALS['controller']->enabled) echo "</td></tr></table>";
   if (!$login)
   $GLOBALS['controller']->captureStop(CONTENT);
   else
   $GLOBALS['controller']->captureStop('m010201');

if (isset($s['mid']) AND !isset($showMID))
   $showMID=$s['mid'];
if( isset( $showMID ) ){
   echo ph(_("Группы"));
   $GLOBALS['controller']->captureFromOb('m010202');
   echo showGroups( $showMID );
   $GLOBALS['controller']->captureStop('m010202');
}

if(isset($showMID)){
   echo "<br><br><TABLE border=0 cellpadding=0 cellspacing=0 width='100%' class=cHilight><tr>
                        <td valign=top class=shedtitle>"._("Личное дело")."</td>
                        <td width=400 background='images/schedule/back.gif'><img src='images/spacer.gif' width=1 height=1 ></td>
                        <td height=19 valign=bottom nowrap class=wing style='font-size: 14px'><div><b>&#224;</b></div></td>
</tr>
<TR><TD COLSPAN=3 WIDTH=100% HEIGHT=10><img src='images/spacer.gif' width=1 height=1> </TD></TR>
</TABLE>
";
$GLOBALS['controller']->captureFromOb('m010203');
echo "
<!-- "._("справка об истории")." -->
    <table cellspacing=0 border=0    cellpadding=0 width=100%> <tr> <td HEIGHT=100% WIDTH=100% >
   <table width=100% class=main cellspacing=0>
        <tr style='padding:0' >
          <th >&nbsp;"._("Дисциплина")."</th>
          <!--th >&nbsp;"._("начало")."</th>
          <th >&nbsp;"._("окончание")."</th-->
          <th >&nbsp;"._("Статус")."</th>";
if (USE_SPECIALITIES) {
    echo "    <th>&nbsp;"._("стоимость")."</th>";
}
echo "  </tr>";
   $totalCost = 0;
   $query = "select Courses.CID as CID, Title, cBegin, cEnd from $teacherstable, $coursestable where MID=$showMID AND $teacherstable.CID=$coursestable.CID";
   $result1=sql($query,"regERR532");
   if (sqlrows($result1)>0){
      while ($row=sqlget($result1)) {
         //echo "<tr class=schedule  ><td CLASS=CMAINBG><a href='teachers/manage_course.php4?CID={$row['CID']}'>{$row['Title']}</td><!--td CLASS=CMAINBG>".mydate($row['cBegin'])."</td><td CLASS=CMAINBG>".mydate($row['cEnd'])."</td--><td CLASS=CMAINBG>"._("Преподает")."</td>";
         $_dummy = $GLOBALS['s']['perm']>1?'constructor':'structure';
         echo "<tr class=schedule  ><td CLASS=CMAINBG><!--a href='course_$_dummy.php?CID={$row['CID']}'-->{$row['Title']}</td><!--td CLASS=CMAINBG>".mydate($row['cBegin'])."</td><td CLASS=CMAINBG>".mydate($row['cEnd'])."</td--><td CLASS=CMAINBG>"._("Преподает")."</td>";
         if (USE_SPECIALITIES) {
            echo "<td class=CMAINBG></td></tr>";
         }
      }
   }
   $showTotalCost = 1;
   $query = "select Fee, valuta, Title, cBegin, cEnd from $studentstable, $coursestable where MID=$showMID AND $studentstable.CID=$coursestable.CID";
   $result1=sql($query,"errREG534");
   if (sqlrows($result1)>0)
      while ($row=sqlget($result1)) {
         echo "<tr class=schedule  ><td CLASS=CMAINBG>{$row['Title']}</td><!--td CLASS=CMAINBG>".mydate($row['cBegin'])."</td><td CLASS=CMAINBG>".mydate($row['cEnd'])."</td--><td CLASS=CMAINBG>"._("Обучается")."</td>";
         if (USE_SPECIALITIES) {
             echo "<td class=CMAINBG>";
             if ($row['Fee']>0 && $row['valuta']>0) {
                 echo $row['Fee'].' '.$valuta[$row['valuta']][0];
                 $totalCost += $row['Fee'];
                 if (!isset($valutaType)) $valutaType = $row['valuta'];
                 else if ($valutaType!=$row['valuta']) $showTotalCost = 0;
             }
             echo "</td>";
         }
         echo "</tr>";
      }

   $query = "select Fee, valuta, Title, cBegin, cEnd from $fintable, $coursestable where MID=$showMID AND $fintable.CID=$coursestable.CID";
   $result1=sql($query,"errREG536");
   if (sqlrows($result1)>0)
      while ($row=sqlget($result1)) {
         echo "<tr  class=schedule ><td CLASS=CMAINBG>{$row['Title']}</td><!--td CLASS=CMAINBG>".mydate($row['cBegin'])."</td><td CLASS=CMAINBG>".mydate($row['cEnd'])."</td--><td CLASS=CMAINBG>"._("Закончил")."</td>";
         if (USE_SPECIALITIES) {
         echo "<td class=CMAINBG>";
             if ($row['Fee']>0 && $row['valuta']>0) {
                 echo $row['Fee'].' '.$valuta[$row['valuta']][0];
                 $totalCost += $row['Fee'];
                 if (!isset($valutaType)) $valutaType = $row['valuta'];
                 else if ($valutaType!=$row['valuta']) $showTotalCost = 0;
             }
             echo "</td>";
         }
         echo "</tr>";
      }

   $query = "select Title, cBegin, cEnd, Teacher from $claimtable, $coursestable where MID=$showMID AND $claimtable.CID=$coursestable.CID";
   $result1=sql($query,"errREG536");
   if (sqlrows($result1)>0)
      while ($row=sqlget($result1)) {
         echo "<tr class=schedule><td CLASS=CMAINBG>".$row['Title']."</td><!--td CLASS=CMAINBG>".mydate($row['cBegin'])."</td><td CLASS=CMAINBG>".mydate($row['cEnd'])."</td--><td CLASS=CMAINBG nowrap>"._("Заявлен")." ";
         echo ($row['Teacher']==1)? _("преподавателем"):_("учеником");
         echo "</td>";
         if (USE_SPECIALITIES) {
            echo "<td class=CMAINBG></td>";
         }
         echo "</tr>";
      }

   if (($totalCost > 0) && $showTotalCost && USE_SPECIALITIES) echo "<tr class=schedule><td class=CMAINBG colspan=3>&nbsp;</td>
   </tr><tr class=schedule><td class=CMAINBG colspan=2 align=right>"._("Общая стоимость:")." </td><td class=CMAINBG>{$totalCost}</td></tr>";
   echo "</table><br><br>";
   if ($GLOBALS['controller']->enabled) echo "</td></tr></table>";
   $GLOBALS['controller']->captureStop('m010203');
   if (isset($_GET['private'])) {
   	    $GLOBALS['controller']->setCurTab('m010203');
   }
   /**
   * Вывод результатов опросов для конкретного пользователя
   */
   $tmp_polls = get_polls($showMID,true);
   if ($GLOBALS['controller']->enabled) {
        if (!empty($tmp_polls)) $GLOBALS['controller']->captureFromReturn('m010207',$tmp_polls);
   }
   else echo $tmp_polls;

   if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM) {
       $credits =
       "
       <table width=100% class=main cellspacing=0>
       <tr>
           <td>"._("Количество свободных кредитов:")." </td>
           <td>".(int) CCredits::countCreditsByMid($showMID)."</td>
       </tr>
       <tr>
           <td>"._("Количество кредитов обязательной программы:")." </td>
           <td>".($credits_mandatory = (int) CCredits::countMandatoryProgramCreditsByMid($showMID))."</td>
       </tr>
       <tr>
           <td>"._("Количество кредитов программы по выбору:")." </td>
           <td>".($credits_free = (int) CCredits::countFreeProgramCreditsByMid($showMID))."</td>
       </tr>
       <tr>
           <td>"._("Всего доступно кредитов программы по выбору:")." </td>
           <td>".($credits_free_all = (int) CCredits::countFreeProgramCredits($showMID))."</td>
       </tr>
       <tr>
           <td>"._("Остаток кредитов программы по выбору:")." </td>
           <td>".(int) ($credits_free_all - $credits_free)."</td>
       </tr>
       <tr>
           <td>"._("Сумма кредитов программ обучения:")." </td>
           <td>".(int) ($credits_mandatory + $credits_free)."</td>
       </tr>
       </table>
       ";

       $GLOBALS['controller']->captureFromReturn('m010208',$credits);
   }
}
echo "</td> </tr></table></td></tr></table>";
echo "</td><!--center-->";
echo show_tb();

?>