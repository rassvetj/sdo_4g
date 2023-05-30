<?php

//defines

$include=TRUE ;

// include

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
$GLOBALS['controller']->captureFromOb(CONTENT);
require ("adm_fun.php4");
require ("error.inc.php4");

$connect=get_mysql_base();
debug_yes("array",$HTTP_COOKIE_VARS);

?>

<!-- <center> -->
<?php
$GLOBALS['controller']->captureFromOb(TRASH);
?>
<br><br><br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="100%" class=br  style="font-size:13px">
   <tr>
      <td class=questt>
        <b><a href="people.php"><?=_("Все учетные записи")?></a></b>
          </td>
   </tr>
  </table>
<?

$arr_Status = array("1" => _("претендент"),
					"2" => _("прошедший обучение"),
					"3" => _("слушатель"),
					"4" => _("преподаватель"),
					"5" => _("учебная администрация"),
					"6" => _("администратор"),
					"7" => _('---без роли---')
					/*"7" => _("Неактивные учетные записи")*/);

$Status = isset($_GET['Status']) ? intval($_GET['Status']) : 0;
$Name   = isset($_GET['Name'])   ? addslashes($_GET['Name'])   : '';
$GLOBALS['controller']->addFilter(_("Логин/Фамилия"), 'Name', false, $Name);
$GLOBALS['controller']->addFilter(_("Роль"), 'Status', $arr_Status, $Status);
$GLOBALS['controller']->addFilter(_("Статус"), 'blocked', array('1' => _('активен'), '2' => _('заблокирован')), $blocked);
$GLOBALS['controller']->addFilter(_("Доп. информация"), 'Metadata', false, $Metadata);
$GLOBALS['controller']->captureStop(TRASH);

if (isset($ok) && isset($MID))
   {
      $_SESSION['people_edit'] = $_POST;
      if ($Login) {
      $_sql = "SELECT MID FROM People WHERE Login = ".$GLOBALS['adodb']->Quote($Login)." AND MID <> '".(int) $MID."'";
      $_res = sql($_sql);
      $_row = sqlget($_res);
      $loginMID = $_row['MID'];
      if ($loginMID) {
          if ($loginMID != $MID) {
              $GLOBALS['controller']->setView('DocumentBlank');
              $GLOBALS['controller']->setMessage(sprintf(_('Логин %s уже существует в системе'),$Login),JS_GO_URL,$GLOBALS['sitepath'].'admin/people.php?MID='.$MID.'&edit=1');
              $GLOBALS['controller']->terminate();
              exit();
          }
      }
      }elseif($MID) {
          $GLOBALS['controller']->setView('DocumentBlank');
          $GLOBALS['controller']->setMessage(_("Не заполнено поле 'Логин'"),JS_GO_URL,$GLOBALS['sitepath'].'admin/people.php?MID='.$MID.'&edit=1');
          $GLOBALS['controller']->terminate();
          exit();
      }
      
      if(!strlen(trim($_POST['LastName']))) {
          $GLOBALS['controller']->setView('DocumentBlank');
          $GLOBALS['controller']->setMessage(_("Не заполнено поле 'Фамилия'"),JS_GO_URL,$GLOBALS['sitepath'].'admin/people.php?MID='.$MID.'&edit=1');
          $GLOBALS['controller']->terminate();
          exit();
      }
      
      if(!strlen(trim($_POST['FirstName']))) {
          $GLOBALS['controller']->setView('DocumentBlank');
          $GLOBALS['controller']->setMessage(_("Не заполнено поле 'Имя'"),JS_GO_URL,$GLOBALS['sitepath'].'admin/people.php?MID='.$MID.'&edit=1');
          $GLOBALS['controller']->terminate();
          exit();
      }
      
      switch (checkmail($EMail)) {
          case 1:
              $GLOBALS['controller']->setView('DocumentBlank');
              $GLOBALS['controller']->setMessage(_("Поле 'Email' не заполнено"),JS_GO_URL,$GLOBALS['sitepath'].'admin/people.php?MID='.$MID.'&edit=1');
              $GLOBALS['controller']->terminate(); 
              exit();
              break;
          case -1:
              $GLOBALS['controller']->setView('DocumentBlank');
              $GLOBALS['controller']->setMessage(_("Поле 'Email' заполнено с ошибкой"),JS_GO_URL,$GLOBALS['sitepath'].'admin/people.php?MID='.$MID.'&edit=1');
              $GLOBALS['controller']->terminate(); 
              exit();
              break;
          default:
              break;              
      }
      
      unset($_SESSION['people_edit']);
      
      $work->complete=1;
      $work->ok=1;
      $result=array("MID"=>htmlspecialchars($MID),
                              "LastName"=>htmlspecialchars($LastName),
                              "FirstName"=>htmlspecialchars($FirstName),
                              "EMail"=>htmlspecialchars($EMail),
                              "Patronymic"=>htmlspecialchars($Patronymic),                              
                              "rang"=>(int) $_POST['rang'],
                              "Access_Level"=>(int) $_POST['Access_Level'],
                              "mid_external"=> $_POST['mid_external'],
                              'blocked' => (int) $_POST['blocked']
                               );
        if (!$MID) {
            $result["Password"] = !$Password ? substr(md5(time()*rand(0,100)), 0, 6) : $Password; 
            $result["Login"]    = !$Login ? newLogin() : htmlspecialchars($Login); 
        }else {
            $result["Login"]    = htmlspecialchars($Login); 
            if ($Password) {
                $result["Password"] = $Password; 
            }elseif(isset($Password)) {
                $result["Password"] = substr(md5(time()*rand(0,100)), 0, 6);
            }
        }
        $reg_form_items = explode(";", REGISTRATION_FORM);
        $meta_information = "";
        foreach($reg_form_items as $key => $value) {
          $meta_information .= "block=".$value."~";
          $meta_information .= trim(set_metadata($_POST, get_posted_names($_POST), $value),"~");
          $meta_information .= "[~~]";
        }
        $result['Information'] = trim($meta_information, "[~~]");

        $res=sql_query($MID ? 23 : 30,$result);

        if (!$MID) {
        $MID = $MID ? $MID : sqllast();
            switch ($_POST['Status']) {
                case 1:
                    $sql = "INSERT INTO Students (MID, CID) VALUES ('$MID', 0)";
                    sql($sql);
                    break;
                case 2:
                    $sql = "INSERT INTO Teachers (MID, CID) VALUES ('$MID', 0)";
                    sql($sql);
                    break; 
                case 3:
                    $sql = "INSERT INTO deans (MID) VALUES ('$MID')";
                    sql($sql);
                    break;
                case 4:
                    $sql = "INSERT INTO admins (MID) VALUES ('$MID')";
                    sql($sql);
                    break;
                default:
                    break;
            }
        }        
        $mail_body = _("Добавлена учётная запись. Логин: ").$Login._("  Пароль: ").$Password;
        mailToelearn("import", 0, 0, array("msg" => $mail_body));

        if(count($_FILES) > 0) {
            if (!$_FILES['photo']['error']){
                      $fn="$tmpdir/asd";
                           move_uploaded_file($_FILES['photo'][tmp_name],$fn);
                           //ресайзим картинку
                           makePreviewImage($fn,$fn,114,152);
                           if (!file_exists($fn)) {
                              //exit(_("Не удалось скопировать файл, нет прав записи в")." $tmpdir");
                               $GLOBALS['controller']->setView('DocumentBlank');
                               $GLOBALS['controller']->setMessage(_("Не удалось скопировать файл, нет прав записи в")." $tmpdir",JS_GO_BACK);
                               $GLOBALS['controller']->terminate();
                               exit();
                           }
                           $buf=gf($fn);
                           $imsize=@getimagesize($fn);
                           if (!is_array($imsize) || count($imsize)<4 || $imsize[0]==0 && $imsize[1]==0) {
                              //exit(_("Загруженный файл не является картинкой GIF, JPG или PNG."));
                               $GLOBALS['controller']->setView('DocumentBlank');
                               $GLOBALS['controller']->setMessage(_("Загруженный файл не является картинкой GIF, JPG или PNG."),JS_GO_BACK);
                               $GLOBALS['controller']->terminate();
                               exit();
                           }
                           @unlink($fn);
                             $data_buf = unpack("H*hex", $buf);
                             $res3 = sql("SELECT mid FROM filefoto WHERE mid=$MID","errFF001");
                           if($counter=sqlrows($res3)){

                              if(dbdriver == "mssql")
                                 $res_img="
                                        UPDATE filefoto SET
                                        last=".time().",
                                        fx='$imsize[0]',
                                        fy='$imsize[1]',
                                        foto=0x".$data_buf['hex']."
                                        WHERE mid='$MID'";

                              else
                                $res_img="
                                       UPDATE filefoto SET
                                        last=".time().",
                                        fx='$imsize[0]',
                                        fy='$imsize[1]',
                                        foto='0'
                                        WHERE mid='$MID'";
                                             }
                           else{
                              if(dbdriver == "mssql")
                            $res_img="
                            INSERT INTO filefoto (mid, last, fx, fy, foto)
                                VALUES (
                                '$MID',
                                '".time()."',
                                '$imsize[0]',
                                '$imsize[1]',
                                0x".$data_buf['hex'].")";

                             else
                                $res_img="
                                INSERT INTO filefoto (mid, last, fx, fy, foto)
                                VALUES (
                                '$MID',
                                '".time()."',
                                '$imsize[0]',
                                '$imsize[1]',
                                '0')";
                                     }

         $res = sql($res_img,"errFF002");
         sqlfree($res);
         $table_f =(dbdriver == "mssql") ? "'filefoto'" : "filefoto";
         $buf =(dbdriver == "mssql") ? "0x".$data_buf['hex'] : $buf;
         $adodb->UpdateBlob($table_f, 'foto',$buf,"mid=".$GLOBALS['adodb']->Quote($MID)."");
         
      }else {
          switch($_FILES['photo']['error']) {
              case 4:
                  //файл не передан, удаляем фотку
                  sql("DELETE FROM `filefoto` WHERE mid='$MID'");
                  break;
          }
      }
   }
   }
   
if (isset($action) && isset($_POST['people']))
   {
   		if (is_array($_POST['people']) && count($_POST['people'])) {
            switch ($action) {
                case "delete":
   			      foreach ($_POST['people'] as $people_mid) {
   				     delete_from_people($people_mid);
   			      }
   			    break;
                case "block":
   				     block_people($_POST['people'], isset($_POST['reason']) ? $_POST['reason'] : "");
                break;
                case "unblock":
   				     unblock_people($_POST['people']);
                break;
            }
   		}
   }
if (isset($edit) && isset($HTTP_GET_VARS["MID"]))
   {
   	  $GLOBALS['controller']->setHeader(_('Редактирование учетной записи пользователя'));
      $work->complete=0;
      $work->edit=1;
      $work->complete=edit_table("people.php",(int)$HTTP_GET_VARS["MID"]);
      $GLOBALS['controller']->setHelpSection('edit');
   }
if (isset($del) && isset($HTTP_GET_VARS["MID"]))
   {
      $work->complete=0;
      $work->del=1;
      /*delete_student($HTTP_GET_VARS["MID"]);
      $work->complete=delete_from_students($HTTP_GET_VARS["MID"]);
      $work->complete=delete_from_teachers($HTTP_GET_VARS["MID"]);*/
      $work->complete = delete_from_people($HTTP_GET_VARS["MID"]);
      debug_yes("Num Rows 1",$work->complete);
   }

if ($work->del && $work->complete) {
    if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setMessage(_("Удалено"));
    else
    echo "<h1>"._("Удалено")."</h1>";
}

if (!isset($edit))
   {
      $work->complete=0;
      $work->show=1;

      //if (isset($order)){$res=sql_query(27, array('order' => $order, 'status' => $Status));}
      //else $res=sql_query(27, array('order' => $order, 'status' => $Status));
      /*
      $res=sql_query(27, array('order' => $order, 'status' => $Status));
      $work->complete=generate_table("people.php",$res);

      reset($res);
      while($row = sqlget($res)) {
          $itemData[] = $row;
      }
      */
      /**
      * Вывод по страницам
      */
      require_once($GLOBALS['wwf'].'/lib/classes/Pager.class.php');
      $order = (isset($order) && !empty($order)) ? addslashes($order) : "MID";

      $where = '';
      switch($Status) {
          // Претенденты
          case "1": $join = "INNER JOIN claimants ON claimants.MID = People.MID"; break;
          // Выпускники
          case "2": $join = "INNER JOIN graduated ON graduated.MID = People.MID"; break;
          // Обучаемые
          case "3": $join = "INNER JOIN Students ON Students.MID = People.MID"; break;
          // Преподаватели
          case "4": $join = "INNER JOIN Teachers ON Teachers.MID = People.MID"; break;
          // Учебная администрация
          case "5": $join = "INNER JOIN deans ON deans.MID = People.MID"; break;
          // Администраторы
          case "6": $join = "INNER JOIN admins ON admins.MID = People.MID"; break;
          // Без статуса
          case "7":
              $join = "LEFT OUTER JOIN claimants ON claimants.MID = People.MID LEFT OUTER JOIN graduated ON graduated.MID = People.MID LEFT OUTER JOIN Students ON Students.MID = People.MID LEFT OUTER JOIN Teachers ON Teachers.MID = People.MID LEFT OUTER JOIN deans ON deans.MID = People.MID LEFT OUTER JOIN admins ON admins.MID = People.MID";
              $where = "claimants.MID IS NULL AND graduated.MID IS NULL AND Students.MID IS NULL AND Teachers.MID IS NULL AND deans.MID IS NULL AND admins.MID IS NULL";
              break;
          // Все
          case "0": default: break;
      }

      switch ($_REQUEST['blocked']) {
      	case '1':
      		$blocked = " blocked <> 1";
      		break;
      	case '2':
      		$blocked = " blocked = 1";
      		break;
      	default:
      		$blocked = "";
      		break;
      }
      if (strlen($Name)) {
          if (strlen($where)) {
              $where .= " AND ";
          }
          $where .= " (LOWER(LastName) LIKE LOWER('%".substr($GLOBALS['adodb']->Quote($Name), 1, -1)."%')
                      OR LOWER(Login) LIKE LOWER('%".substr($GLOBALS['adodb']->Quote($Name), 1, -1)."%'))

                    ";
      }

      if (strlen($Metadata)) {
          if (strlen($where)) {
              $where .= " AND ";
          }
          $where .= " LOWER(Information) LIKE LOWER('%".substr($GLOBALS['adodb']->Quote($Metadata), 1, -1)."%')";
      }

      if ($blocked) {
          if (strlen($where)) {
              $where .= ' AND';
          }
      	  $where .= $blocked;
      }

      $pagerOptions = array(
          'CDBPager_table'   => 'People',
          'CDBPager_idName'  => 'People.MID',
          'CDBPager_select'  => '
               People.MID as MID,
               People.mid_external as mid_external,
               People.LastName as LastName,
               People.FirstName as FirstName,
               People.Registered as Registered,
               People.Course as Course,
               People.EMail as EMail,
               People.Phone as Phone,
               People.Information as Information,
               People.Patronymic as Patronymic,
               People.Address as Address,
               People.Fax as Fax,
               People.Password as Password,
               People.javapassword as javapassword,
               People.Login as Login,
               People.BirthDate as BirthDate,
               People.CellularNumber as CellularNumber,
               People.ICQNumber as ICQNumber,
               People.Age as Age,
               People.last as last,
               People.countlogin as countlogin,
               People.rnid as rnid,
               People.Position as Position,
               People.PositionDate as PositionDate,
               People.PositionPrev as PositionPrev,
               People.invalid_login as invalid_login,
               People.isAD as isAD,
               People.polls as polls,
               People.Access_Level as Access_Level,
               People.blocked as blocked',
          'CDBPager_join'    => $join,
          'CDBPager_where'   => $where,
          'CDBPager_group'   => '',
          'CDBPager_order'   => 'People.'.$order,
          'mode'    => 'Sliding',
          'delta'   => 5,
          'perPage' => 50
      );

      $pager = new CDBPager($pagerOptions);
      $page = $pager->getData();
      if ($page['links']) {
          echo "<table class=main cellspacing=0><tr><td>". _('Страница') . ':&nbsp;&nbsp;' . $page['links']."</td></tr></table>";
      }
      $work->complete=generate_table("people.php", $page['result']);
      if ($page['links']) {
          echo "<table class=main cellspacing=0><tr><td>". _('Страница') . ':&nbsp;&nbsp;'.$page['links']."</td></tr></table>";
      }


      require_once('Pager/examples/Pager_Wrapper.php');

/*      $order = (isset($order) && !empty($order)) ? "ORDER BY People.".addslashes($order)." ASC" : "ORDER BY People.MID";
      $sql = "SELECT
                  People.MID as MID
              FROM People ";
      switch($Status) {
          // Претенденты
          case "1": $sql .= "INNER JOIN claimants ON claimants.MID = People.MID"; break;
          // Выпускники
          case "2": $sql .= "INNER JOIN graduated ON graduated.MID = People.MID"; break;
          // Обучаемые
          case "3": $sql .= "INNER JOIN Students ON Students.MID = People.MID"; break;
          // Преподаватели
          case "4": $sql .= "INNER JOIN Teachers ON Teachers.MID = People.MID"; break;
          // Учебная администрация
          case "5": $sql .= "INNER JOIN deans ON deans.MID = People.MID"; break;
          // Администраторы
          case "6": $sql .= "INNER JOIN admins ON admins.MID = People.MID"; break;
          // Без статуса
          case "7": $sql .= "LEFT OUTER JOIN claimants ON claimants.MID = People.MID LEFT OUTER JOIN graduated ON graduated.MID = People.MID LEFT OUTER JOIN Students ON Students.MID = People.MID LEFT OUTER JOIN Teachers ON Teachers.MID = People.MID LEFT OUTER JOIN deans ON deans.MID = People.MID LEFT OUTER JOIN admins ON admins.MID = People.MID WHERE claimants.MID IS NULL AND graduated.MID IS NULL AND Students.MID IS NULL AND Teachers.MID IS NULL AND deans.MID IS NULL AND admins.MID IS NULL"; break;
          // Все
          case "0": default: break;
      }
      $sql .= " {$order}";

      $pagerOptions = array(
          'mode'    => 'Sliding',
          'delta'   => 5,
          'perPage' => 50,
      );

      if ($res = sql($sql)) {
          $i = 0; $j = 0;
          while($row = sqlget($res)) {
              if ($i>=1000) { // fucking oracle IN fix
                  $j++;
                  $i = 0;
              }
              $mids[$j][] = $row['MID'];
              $i++;
          }

          $inArray = array(); $sql_in = "";
          if (is_array($mids) && count($mids)) {
              foreach($mids as $partOfMids) {
                  if (count($partOfMids)) {
                      $inArray[] = "MID = ".join(" OR MID  = ",$partOfMids);
                  }
              }

              if (count($inArray)) {
                  $sql_in = "".join(" OR ", $inArray);
              }
          }

          if (($Name!='') && is_array($mids) && count($mids)) {
              $sql = "
                  SELECT DISTINCT MID FROM People
                  WHERE ($sql_in)
                  AND (LastName LIKE '%".CObject::toLower($Name)."%'
                  OR LastName LIKE '%".CObject::toUpper($Name)."%'
                  OR LastName LIKE '%".$Name."%'
                  OR Login LIKE '%".CObject::toLower($Name)."%'
                  OR Login LIKE '%".$Name."%'
                  OR Login LIKE '%".CObject::toUpper($Name)."%')";
              $res = sql($sql);
              $mids = array();
              $i = 0; $j = 0;
              while($row = sqlget($res)) {
                  if ($i>=1000) { // fucking oracle IN fix
                      $j++;
                      $i = 0;
                  }
                  $mids[$j][] = $row['MID'];
                  $i++;
              }
          }

          if (($Name != '') && is_array($mids) && count($mids)) {
              $inArray = array(); $sql_in = "";
              foreach($mids as $partOfMids) {
                  if (count($partOfMids)) {
                      $inArray[] = "MID = ".join(" OR MID  = ",$partOfMids);
                  }
              }

              if (count($inArray)) {
                  $sql_in = "".join(" OR ", $inArray);
              }
          }

          if (is_array($mids) && count($mids)) {
              $sql = "SELECT
                          People.MID as MID,
                          People.mid_external as mid_external,
                          People.LastName as LastName,
                          People.FirstName as FirstName,
                          People.Registered as Registered,
                          People.Course as Course,
                          People.EMail as EMail,
                          People.Phone as Phone,
                          People.Information as Information,
                          People.Patronymic as Patronymic,
                          People.Address as Address,
                          People.Fax as Fax,
                          People.Password as Password,
                          People.javapassword as javapassword,
                          People.Login as Login,
                          People.BirthDate as BirthDate,
                          People.CellularNumber as CellularNumber,
                          People.ICQNumber as ICQNumber,
                          People.Age as Age,
                          People.last as last,
                          People.countlogin as countlogin,
                          People.rnid as rnid,
                          People.Position as Position,
                          People.PositionDate as PositionDate,
                          People.PositionPrev as PositionPrev,
                          People.invalid_login as invalid_login,
                          People.isAD as isAD,
                          People.polls as polls,
                          People.Access_Level as Access_Level,
                          People.blocked as blocked
                      FROM People
                      WHERE $sql_in
                      {$order}";
          }
          $page = Pager_Wrapper_Adodb($adodb, $sql, $pagerOptions);
          echo "<table class=main cellspacing=0><tr><td>".$page['links']."</td></tr></table>";
          $work->complete=generate_table("people.php", $page['result']);
          echo "<table class=main cellspacing=0><tr><td>".$page['links']."</td></tr></table>";
      }
*/

      // ==============================


   }

if (!$work->complete) show_error(1);

?>
<!-- </center> -->
<?php

$GLOBALS['controller']->captureStop(CONTENT);

//require_once("adm_b.php4");
echo show_tb();

function newLogin() {
    $name   = 'user';
    $row    = sqlval("SELECT Login FROM People WHERE Login REGEXP '{$name}[0-9]*$' ORDER BY Login DESC LIMIT 1");
    $number = (int)str_replace($name, '', $row['Login']) + 1;
    $number = 3-strlen($number) > 0 ? str_repeat(0, 3-strlen($number)).$number : $number;
    return $name.$number;
}

/** Функция проверки e-mail. проверяет мыло и возвращает
  * -1, если не пустое, но с ошибкой
  * строку, если мыло верное
  */
function checkmail($mail) {
    $mail=trim($mail);
    if (strlen($mail)==0) return 1;
    $mail = validateEmail($mail);
    if (!$mail) {
        return -1;
    }
    return $mail;    
}


?>