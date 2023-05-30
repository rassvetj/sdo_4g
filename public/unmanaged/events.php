<?

   include("1.php");
   include("test.inc.php");

   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   if ($s[perm]!=4) exitmsg(_("К этой странице могут обратится только: администратор"),"/?$sess");

/*
   $#%^#%^&#~ !

      Короче, в таблице EventTools колонка с ошибкой написана: Deen

   dima@i.am 26.11.02

*/


   $cols=array(
      "Icon"=>_("Иконка"),
      "Name"=>_("Название"),
      "Инструмент"=>_("Инструмент обучения"),
      "Описание"=>_("Описание"),
   );
   $cols2=array(
/*      "redirectURL"   =>'Redirect',
      "externalURLs"  =>'External Resourses',
      "chatApplet"    =>'Chat applet',
      "liveCam1"      =>'LiveCam1',*/
      "tests"       => _("модуль курса с типом &laquo;задание&raquo; (тест)"),
      "module"      => _("любой элемент из структуры курса"),
      'run'         => _("модуль курса с типом &laquo;внешняя программа&raquo;"),
      "chat"        => _("чат"),
      "video_live"  => _("видео-трансляция"),
      "module,video_live,chat"   => _("комбинированный")." ("._("элемент из структуры курса")." + "._("видео-трансляция")." + "._("чат").")",
      "video_live,chat"   => _("комбинированный")." ("._("видео-трансляция")." + "._("чат").")",   
      "nothing"     => _('нет инструмента обучения'),
      'webinar'     => _('вебинар'),
      'connectpro'  => _('adobe connect pro'),
/*      "books"         =>'Library',
      "kpaint"        =>'DrawBoard',
      "collaborator"  =>'Collaborator'*/
   );
   
   if (USE_WEBINAR) {
       $cols2['webinar'] = _('вебинар');
   }
   
   if (USE_CONNECT_PRO) {
       $cols2['connectpro'] = _('adobe connect pro');      	
   }
   
   //[dimon - 2004-10-20] read icon names from ./images/events
   $icon = array();
   $i=0;
   $dh = opendir('images/events');
   while ($fname = readdir($dh)) {
       if (!is_dir("images/events/$fname")) {
             $icon[$i] = $fname;
             $i++;
       }
   }
   closedir($dh);
   sort($icon);
   $xsl = array();
   $i=0;
   $dh = opendir('xml2/EventXSL');
   while ($fname = readdir($dh)) {
        if (!is_dir("xml2/EventXSL/$fname")) {
              $xsl[$i] = $fname;
              $i++;
        }
   }
   closedir($dh);
   sort($xsl);


   function checkbox1($value) {
      if ($value) return("<font face=webdings color=black>\x61</font>");
      return("<font color=#dddddd>\x95</font>");
   }
   function checkbox2($name,$value) {
      return("<input type=checkbox name=\"form[$name]\" value=1 ".($value?"checked":"").">");
   }

switch ($c) {

case "":

   echo show_tb();
	$GLOBALS['controller']->captureFromOb(CONTENT);
//   <!--input type=submit value='Создать новое занятие &gt;&gt;'-->
//   </form>
   echo "
   <div style='padding-bottom: 5px;'>
    <div style='float: left;'>
        <img src='{$sitepath}images/icons/small_star.gif' />
    </div>
    <div>
        <a style='text-decoration: none;' href='{$sitepath}events.php?c=add'>"._("создать тип занятия")."</a>
    </div>
   </div>
   <table width=100% class=main cellspacing=0><tr>";
   foreach ($cols as $v) echo "<th align=center nowrap>$v</th>";
//   foreach ($cols2 as $k=>$v) {
//       if (count(explode(',',$k))==1)
//           echo "<th align=center>$v</th>";
//   }
   echo "<th align=center>"._('Действия')."</th></tr>";

   $res=sql("SELECT * FROM EventTools ORDER BY typeid","errEV48");
   while ($r=sqlget($res)) {
      echo "<tr class=questt align=center>
      <td><img src={$sitepath}images/events/$r[Icon]></td>
      <td align=left nowrap>$r[TypeName]</td>
      <td align=left nowrap>{$cols2[$r[tools]]}</td>
      <td align=left width=100%>$r[Description]</td>";
//      $check=array();
//      foreach (explode(",",trim($r[tools])) as $v) $check[trim($v)]=1;
//      foreach ($cols2 as $k=>$v){
//         if (count(explode(',',$k))==1) {
//             if (isset($check[$k])) echo "<td>".checkbox1(1)."</td>";
//             else echo "<td>".checkbox1(0)."</td>";
//         }
//      }

      echo "<td nowrap>";

      if (!in_array($r[TypeID], array(3,4))) {
          echo "<a href=$PHP_SELF?c=edit&id=$r[TypeID]>" . getIcon('edit', _('Редактировать тип')) . "</a>&nbsp;
                <a href=$PHP_SELF?c=del&id=$r[TypeID] onClick=\"javascript:return confirm('"._("Вы действительно желаете удалить данный тип занятия и все занятия данного типа?")."')\">" . getIcon('delete', _('Удалить тип')) . "</a>
                ";
      }

      echo "</td>";
      echo "</tr>";
   }
   echo "</table>";

   $GLOBALS['controller']->captureStop(CONTENT);

   echo show_tb();


   break;



case "edit":

   echo show_tb();
   $GLOBALS['controller']->setHelpSection('edit');

   intvals("id");
   $r=sqlval("SELECT * FROM EventTools WHERE typeid=$id","errEV116");
   if (!is_array($r)) exitmsg(_("Нет такого занятия"),"$PHP_SELF?$sess");

//   echo "<h3>Редактирование типа занятия</h3>
   $GLOBALS['controller']->setHeader(_("Редактирование типа занятия"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setLink('m180101',array($id));
   else
   echo "
   <p align=right><a href=$PHP_SELF?c=del&id=$id onClick='return confirm(\""._("Вы действительно желаете удалить?")."\");'>"._("Удалить")."</a></p>";
   echo "
   <form name='frmForm' action='$PHP_SELF' method=post enctype='multipart/form-data'>
   <input type=hidden name=id value=\"$id\">
   <input type=hidden name=c value=\"edit_post\">
   <input type=hidden name=MAX_FILE_SIZE value=100000>
   <table width=100% class=main cellspacing=0>

   <tr class=questt><td>"._("Название")."</td>
   <td><input type=text name=form[TypeName] size=40 value=\"".html($r[TypeName])."\"></td>
   </tr><tr class=questt><td>"._("Иконка")."</td>
   <td>

   <table cellpadding=5 cellspacing=0 border=0>
   <tr>
   <td>
   <select name=form[Icon] size=1 onchange='viewIcon(this.options[selectedIndex].text, this.options[selectedIndex].value);'><option value=0>-- "._("необходимо выбрать иконку")." --</option>";
   foreach ($icon as $v) { echo "<option".($r[Icon]==$v?" selected":"").">$v"; }
   echo "</select>
   </td>
   <td><img src=images/events/{$r[Icon]} name=icon_image /></td>
   <tr>
   <td colspan=2>"._("или загрузить (не более 10Кб)")."</td>
   </tr>
   <tr>
   <td colspan=2><input name=IconUpload type=file><input type=hidden name=form[XSL] value=0></td>
   </tr>
   </table>

   </td>
   </tr>
   <!--tr class=questt>
   <td>Вес</td><td><input type=text name=form[weight] size=40 value=\"".html($r['weight'])."\"></td>
   </tr-->
   <tr class=questt><td>"._("Описание")."</td>
   <td><textarea name=form[Description] cols=30 rows=4>".html($r[Description])."</textarea></td>
   <tr class=questt><td>"._('Инструмент обучения')."&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('event_tools') . "</td><td>";
   $check=array();
   foreach (explode(",",trim($r[tools])) as $v) $check[trim($v)]=1;
   foreach ($cols2 as $k=>$v) {
      if ($k!='chat') {
          $ch = ($r[tools] == $k) ? "checked" : "";
          if (!strlen($r['tools']) && ($k == 'tests')) $ch = 'checked';
          echo "<p><input type=radio name='tools' value='{$k}' {$ch}>&nbsp;&nbsp;$v&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('event_type_' . $k) . '</p>';
      }
   }
   echo "</td></tr></table>";
   echo "<table align='right'><tr><td>".okbutton()."</td><td>".button(_("Отмена"),'','cancel',"window.document.location.href =\"{$sitepath}events.php\"; return true")."</td></tr></table>";

   echo "
   <script>
	function checkForm(frmForm) {
		if ( (frmForm['form[Icon]'].value == '0') && (frmForm['IconUpload'].value.length == 0) ) {
			alert('"._("Необходимо выбрать иконку из списка или указать путь на локальном компьютере!")."');
			return false;
		}
		return true;
	}

	function viewIcon(newimage, valueimage)
	{
		if(valueimage != '0') {
			document.icon_image.src = 'images/events/' + newimage;
		}
	}

   </script>
   ";

   $GLOBALS['controller']->captureStop(CONTENT);

   echo show_tb();

   break;


case "edit_post":

   intvals("id");
   $post=array();
   $post[TypeName]=$form[TypeName];
   $post[Student]=abs(intval($form[Student]))%2;
   $post[Teacher]=abs(intval($form[Teacher]))%2;
   $post[weight] = $form[weight] ? $form[weight] : 0;
   $post[Deen]=abs(intval($form[Dean]))%2;
   if (in_array($form[Icon],$icon)) $post[Icon]=$form[Icon];
   if (in_array($form[XSL],$xsl)) $post[XSL]=$form[XSL];
   $post[Description]=$form[Description];
   $post[tools] = $_POST['tools'];

   $upload = @$_FILES['IconUpload'];
   if ($upload['name']) {
	 if ($upload[size]>10240 || $upload[size] === 0) {       
       $GLOBALS['controller']->setMessage(_("Иконка не должна быть более 10240 байт (10Кб)"), false, $GLOBALS['sitepath']."events.php?c=edit&id={$id}");
           $GLOBALS['controller']->terminate();
       //refresh($sitepath."events.php?c=edit&id={$id}");
       exit();
   	 }
   	 
   	 $info = @getimagesize($upload[tmp_name]);    
     if ($info[0] > 120/*ширина*/ || $info[1] > 100/*высота*/) {
            makePreviewImage($upload[tmp_name], $upload[tmp_name], 120, 100);
     }
     
     $fn="images/events/".to_translit($upload['name']);
     if (move_uploaded_file($upload[tmp_name],$fn)) {
   	   $post[Icon] = to_translit($upload['name']);
   	 }
   }

   //pr($form);
   //pr($post);

   if (empty($post['tools'])) $post['tools'] = 'offline';

   $rq="UPDATE `EventTools` SET ";
   foreach ($post as $k=>$v) $rq.="`$k`=".$GLOBALS['adodb']->Quote($v).",";
   $rq=substr($rq,0,-1)." WHERE `typeid`=$id";
   //exit(pr($rq));
   $res=sql($rq,"errEV151");
   sqlfree($res);
   refresh("$PHP_SELF?$sess");
   break;

case "add":

   echo show_tb();
   $GLOBALS['controller']->setHelpSection('add');

   $GLOBALS['controller']->setHeader(_("Создание типа занятия"));
   $GLOBALS['controller']->captureFromOb(CONTENT);

   echo "
   <form name='frmForm' action='$PHP_SELF' method=post enctype='multipart/form-data'>
   <input type=hidden name=c value=\"add_post\">
   <input type=hidden name=MAX_FILE_SIZE value=100000>
   <table width=100% class=main cellspacing=0>

   <tr class=questt><td>"._("Название")."</td>
   <td><input type=text name=form[TypeName] size=40 value=\"".html($_POST['TypeName'])."\"></td>
   </tr><tr class=questt><td>"._("Иконка")."</td>
   <td>

   <table cellpadding=5 cellspacing=0 border=0>
   <tr>
   <td>
   <select name=form[Icon] size=1 onchange='viewIcon(this.options[selectedIndex].text, this.options[selectedIndex].value);'><option value=0>-- "._("Необходимо выбрать иконку")." --</option>";
   foreach ($icon as $v) { echo "<option>$v</option>"; }
   echo "</select>
   </td>
   <td><img src='' name=icon_image /></td>
   <tr>
   <td colspan=2>"._("или загрузить (не более 10Кб)")."</td>
   </tr>
   <tr>
   <td colspan=2><input name=IconUpload type=file><input type=hidden name=form[XSL] value=0></td>
   </tr>
   </table>

   </td>
   </tr>
   <tr class=questt><td>"._("Описание")."</td>
   <td><textarea name=form[Description] cols=30 rows=4>".html($_POST['Description'])."</textarea></td>
   <tr class=questt><td>"._('Инструмент обучения')."&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('event_tools') . "</td><td>";
   foreach ($cols2 as $k=>$v) {
      if ($k!='chat') {
          echo "<p><input type=radio name='tools' value='{$k}'>&nbsp;&nbsp;$v&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('event_type_' . $k) . '</p>';
      }
   }
   echo "</td></tr></table>";
   echo "<table align='right'><tr><td>".okbutton()."</td><td>".button(_("Отмена"),'','cancel',"this.href =\"{$sitepath}events.php\"")."</td></tr></table>";

   echo "
   <script>
	function checkForm(frmForm) {
		if ( (frmForm['form[Icon]'].value == '0') && (frmForm['IconUpload'].value.length == 0) ) {
			alert('"._("Необходимо выбрать иконку из списка или указать путь на локальном компьютере!")."');
			return false;
		}
		return true;
	}

	function viewIcon(newimage, valueimage)
	{
		if(valueimage != '0') {
			document.icon_image.src = 'images/events/' + newimage;
		}
	}

   </script>
   ";

   $GLOBALS['controller']->captureStop(CONTENT);

   echo show_tb();

   break;


case "add_post":

   intvals("id");
   $post=array();
   $post[TypeName]=$form[TypeName];
   $post[Student]=abs(intval($form[Student]))%2;
   $post[Teacher]=abs(intval($form[Teacher]))%2;
   $post[weight] = $form[weight] ? $form[weight] : 0;
   $post[Deen]=abs(intval($form[Dean]))%2;
   if (in_array($form[Icon],$icon)) $post[Icon]=$form[Icon];
   if (in_array($form[XSL],$xsl)) $post[XSL]=$form[XSL];
   $post[Description]=$form[Description];
   $post[tools] = $_POST['tools'];

   $upload = @$_FILES['IconUpload'];
   if ($upload['name']) {
    if ($upload[size]>10240 || $upload[size] === 0) {       
       $GLOBALS['controller']->setMessage(_("Иконка не должна быть более 10240 байт (10Кб)"), false, $GLOBALS['sitepath']."events.php?c=add");
           $GLOBALS['controller']->terminate();
       exit();
       }
   	 
   	 $info = @getimagesize($upload[tmp_name]);    
     if ($info[0] > 120/*ширина*/ || $info[1] > 100/*высота*/) {
            makePreviewImage($upload[tmp_name], $upload[tmp_name], 120, 100);
   	 }
   	 
     $fn="images/events/".to_translit($upload['name']);
     if (move_uploaded_file($upload[tmp_name],$fn)) {
   	   $post[Icon] = to_translit($upload['name']);
   	 }
   }

   //pr($form);
   //pr($post);

   if (empty($post['tools'])) $post['tools'] = 'offline';

   $rq="INSERT INTO `EventTools` ";
   $fields = array();
   $values = array();
   foreach ($post as $k=>$v) {
       $fields[] = "`$k`";
       $values[] = $GLOBALS['adodb']->Quote($v);
   }
   $rq .= "(".implode(',', $fields).") VALUES(".implode(',', $values).")";
   $res=sql($rq,"errEV151");
   sqlfree($res);

   if (in_array($post['tools'], array('video_live', 'module,video_live,chat', 'video_live,chat', 'webinar', 'connectpro'))) {
        $GLOBALS['controller']->setMessage(_('Внимание! Для корректной работы занятий данного типа необходимо настроить связь с медиа-сервером.'), JS_GO_URL, "$PHP_SELF?$sess");
        $GLOBALS['controller']->terminate();
        exit();
   }
   
   refresh("$PHP_SELF?$sess");
   break;

case "del":

   $sql = "SELECT SHEID FROM schedule WHERE typeID='".(int) $id."'";
   $res = sql($sql);
   while($row = sqlget($res)) $sheids[] = $row['SHEID'];

   if (is_array($sheids) && count($sheids)) {
       sql("DELETE FROM scheduleID WHERE SHEID IN ('".join("','",$sheids)."')");
       sql("DELETE FROM schedulecount WHERE sheid IN ('".join("','",$sheids)."')");
       sql("DELETE FROM schedule WHERE SHEID IN ('".join("','",$sheids)."')");
   }

   intvals("id");
   sql("DELETE FROM eventtools_weight WHERE event='".(int) $id."'");
   $res=sql("DELETE FROM EventTools WHERE typeid=$id","errEV193");
   sqlfree($res);
   refresh("$PHP_SELF?$sess");

   break;


}






?>