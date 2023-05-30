<?

include("1.php");
include("metadata.lib.php");
require_once('courses.lib.php');
require_once('move2.lib.php');

   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   if ($s[perm]<3) exitmsg(_("Доступ к странице имеют руководство или администраторы"),"/?$sess");



switch ($c) {

case "":
   echo show_tb();
   $controller->setHelpSection('import1c');
   echo ph(_("Импорт курсов из .CSV файла"));
   $GLOBALS['controller']->setHeader(_("Импорт курсов из CSV-файла"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   echo "
   <form enctype='multipart/form-data' action='courses1c.php' method=post>
   $asessf
   <input type=hidden name=c value=\"upload\">
   <input type=hidden name=MAX_FILE_SIZE value=500000>
   <table width=100% class=main cellspacing=0>
   <tr><td>"._("CSV-файл с курсами")." </td><td><input name=userfile type=file class=s8></td></tr>
   <input type=hidden name=razd value=';'id=z1>";
   echo "</table><P>".okbutton()."</form>";
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();

   break;

case "upload":

   $controller->setHelpSection('step2');
   $razd=substr($razd,0,1);
   $upload=$HTTP_POST_FILES[userfile];
   $fn="$tmpdir/tmp_csv_".session_id()."_".mt_rand(0,9999999);
   if ($upload[size]==0 || !is_uploaded_file($upload[tmp_name]))
      exitmsg(_("Вы не загрузили файл! Нажмите кнопку \"Обзор\" и выберите *.CSV файл для импорта"),"$PHP_SELF?$sess");
   move_uploaded_file($upload[tmp_name],$fn);

   echo show_tb();
   //echo backbutton();
   $GLOBALS['controller']->setHeader(_("Импорт курсов из .CSV файла"));
   //$GLOBALS['controller']->captureFromOb(CONTENT);
   $html = "
   <form action=$PHP_SELF method=post>
   $asessf
   <input type=hidden name=c value=\"import\">
   ";

   if (!file_exists($fn))
      err(_("Не удалось скопировать файл, нет прав записи в")." $tmpdir",__FILE__,__LINE__);

   $f=fopen($fn,"rb") or err(_("Не могу открыть файл")." $fn",__FILE__,__LINE__);
   $i=0;

//   $r=trim ( $ss, ";" );
   $html.= "<table width=100% class=main cellspacing=0>";
   while ( $ss=fgets($f,10000) ) {
     if($i == 0) {
     	$i++;
     	$title_array = explode($razd, $ss);

     	continue;
     }

   	 $r=explode ( $razd, $ss );

   	 session_register("title_array");

     $type = ($i) ? "text" : "hidden";
     $tmp=make_import_data($buf, $i, $r, $type);
     if ($i) {
    	$html.= "<tr><td><input checked type=checkbox name='che[$i]'></td><td>";
     }
     $html.= $tmp;
     if ($i) {
        $html.= "</td></tr>";
     }
     $i++;
   }
   $html.= "</table>";

   echo "<P>";
   if ($i==0)
      $html.= _("К сожалению, не найдена информация о курсах в вашем файле.")."
      "._("Либо вы загрузили пустой файл, либо не *.CSV типа.");
  else
      $html.= "<br/>".okbutton()."</form>";
   fclose($f);
   unlink($fn);
   $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
   $GLOBALS['controller']->terminate();
   break;


case "import":

   if (!is_array($che) || count($che)==0)
      exitmsg(_("Ничего не было отмечено для импорта. Выберите хотя бы один курс."),"$PHP_SELF?$sess");

   $max_k=count( $che );
   $buf = $_POST['buf'];

	for($k=1;$k<=$max_k;$k++)
	{
		$reg_form_block = COURSES_DESCRIPTION;
		$information = "";

		$temp = load_metadata($reg_form_block);
		$names = array();
		foreach($temp as $temp_item)
		{
			$names[] = $temp_item['name'];
		}
		$information .= set_metadata($buf[$k], $names ,COURSES_DESCRIPTION);
	  $now = $adodb->DBTimeStamp(time());
      $begin = $adodb->DBTimeStamp(time());
      $end = $adodb->DBTimeStamp(time()+60*60*24*100);
      $res=sql("
      INSERT INTO Courses (Title, Description, TypeDes, Fee, valuta, Status, createdate, cBegin, cEnd, longtime)
      values (
      ".$GLOBALS['adodb']->Quote($buf[$k][name]).",
      ".$GLOBALS['adodb']->Quote($information).",
      1,
      '".doubleval($buf[$k][price])."',
      '".(intval($buf[$k][valuta])%count($valuta))."',
      0,
      {$now},
      {$begin},
      {$end},
      '100'
      )","err1C89");

      if ($cid = sqllast()) {
          sql("INSERT INTO `organizations` (`title`, `cid`, `prev_ref`, `level`) VALUES ('"._("&lt;пустой элемент&gt;")."','".(int) $cid."','-1', '0')");
      }

      sqlfree($res);

   }

   exitmsg(_("Все выбранные курсы")." (".count($che).") "._("были загружены!"), "courses.php4");
   break;

}

function make_import_data($buf, $i, $r, $type) {
	  global $valuta;

      /*if (strval(intval($r[0]))!=strval($r[0]) || $r[0]<1) continue;
      echo "<input $check type=checkbox name='che[$i]'>";
      echo "<input type=text size=60 name='buf1[$i][name]' value=\"".html($r[1])."\" class=s8> ";
      echo "<input type=text size=7 name='buf1[$i][price]' value=\"".doubleval(html($r[2]))."\" class=s8> ";
      echo "<select name='buf1[$i][valuta]' size=1 class=s8>";
      $val=detectvaluta($r[3]);
      foreach ($valuta as $k=>$v) echo "<option value=$k ".($k==$val?"selected":"").">$v[0]";
      echo "</select><br>";
      echo "<input type=text name='buf1[$i][authors]' value=\"".html($r[4])."\" class=s8> ";
      echo "<input type=text size=5 name='buf1[$i][units]' value=\"".doubleval(html($r[5]))."\" class=s8> ";
      echo "<input type=text size=5 name='buf1[$i][units_vol]' value=\"".doubleval(html($r[6]))."\" class=s8> ";
      echo "<input type=text name='buf1[$i][annot]' value=\"".html($r[7])."\" class=s8> ";
      echo "<input type=text size=5 name='buf1[$i][control]' value=\"".html($r[8])."\" class=s8><br> ";
      $i++;	*/

	  $tmp = "<table width=100%><tr><td>";
	  $tmp .= "<input type=text size=60 name='buf[$i][name]' value=\"".html($r[1])."\" class=s8> ";
	  $tmp .= "</td></tr><tr><td>";
      //$tmp .= "<input type=text size=7 name='buf[$i][price]' value=\"".doubleval(html($r[2]))."\" class=s8> ";
      //$tmp .= "<select name='buf[$i][valuta]' size=1 class=s8>";
	  //$val = detectvaluta($r[3]);
      //foreach ($valuta as $k=>$v) $tmp .= "<option value=$k ".($k==$val?"selected":"").">$v[0]";
      //$tmp .= "</select><br>";

      $k = 2;

      $reg_form_block = COURSES_DESCRIPTION;
      $metadata = load_metadata($reg_form_block);
      if(is_array($metadata))
      foreach($metadata as $key => $value) {
      	$metadata[$key]['value'] = html($r[$k]);
      	$metadata[$key]['name'] = "buf[$i][".$metadata[$key]['name']."]";
      	$k++;
      }
      $tmp .= get_reg_block_title($reg_form_block)."".edit_metadata($metadata);

      $tmp.="</td></tr></table>";


      return( $tmp );
}

?>