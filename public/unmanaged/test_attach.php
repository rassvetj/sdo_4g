<?

   require_once("1.php");

    if (isset($_GET['mode'])) {
        $mode = $_GET['mode'];
    }

    if (isset($_GET['num'])) {
        $num = $_GET['num'];
    }

    if (isset($_GET['cryptkod'])) {
        $cryptkod = $_GET['cryptkod'];
    }

    if (isset($_GET['checkkod'])) {
        $checkkod = $_GET['checkkod'];
    }

    if (isset($_GET['checkrnd'])) {
        $checkrnd = $_GET['checkrnd'];
    }

   include("test.inc.php");

   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

/*
   $checkrnd=mt_rand(100000,999999);
   $cryptkod=urlencode(encrypt($kod,$checkrnd.$GLOBALS[cryptpass]));
   $checkkod=md5("$kod $rnd $GLOBALS[cryptpass]");
   $add="";
   if ($mode=="linknew") $add=" target=_blank ";
   return "<a$add href='test_attach.php?mode=$mode&cryptkod=$cryptkod&num=$num&checkkod=$checkkod&checkrnd=$checkrnd'>$text</a>";
*/

/*
   echo "
   pass=$GLOBALS[cryptpass] <P>
   cryptkod=$cryptkod <P>
   checkkod=$checkkod<P>
   checkrnd=$checkrnd<P>
   num=$num<P>
   
   ";
*/

   $checkrnd=intval($checkrnd);
   $num=intval($num);
   $kod=decrypt($cryptkod,$checkrnd.$GLOBALS[cryptpass]);
   //echo "kod=$kod<P>";
   if ($checkkod!=md5("$kod $checkrnd $GLOBALS[cryptpass]")) exit("HackDetect: "._("ошибочная ссылка"));

/*
mode=linknew
num=1
cryptkod=%F3%F2J%40%13D%8C%996%3F%0F%FB%88%DC0%C0F
checkkod=8777985d5f2bd4c7005b3ceb0278db45
checkrnd=886295

mode=download
num=1
cryptkod=
checkkod=b8156648ff8eb9f8daa420ed04051900
checkrnd=173184
*/
   //
   // условия: когда можно пускать на скачивание аттачей
   //

   $ok=0;

   if (isset($s[tkurs][kodintval($kod)]) || ($_SESSION['s']['perm'] > 1)) $ok=1;

   if (!$ok && $s[me]!=1) exit(_("В данный момент вы не проходите тестирование и не можете использовать файлы тестов."));
   
   if (!$ok && !in_array($kod,$s[ckod])) exit(_("Этот вопрос не находится в списке вопросов, которые вы должны пройти за сеанс работы"));


   $res=sql("SELECT kod, fnum, ftype, fname, fdata, fdate, fx, fy FROM file WHERE kod=".$GLOBALS['adodb']->Quote($kod)." AND fnum='$num'","err1");
   if (sqlrows($res)==0) exit(_("Этот файл не найден"));
   $r=sqlget($res);
   sqlfree($res);

   if (isset($attachtype[$r[ftype]])) $type=$attachtype[$r[ftype]][1];
   else $type="application/octet-stream";

/*
   switch ($r[ftype]) {
      case 0: $type="text/plain"; break;
      case 1: $type="text/html"; break;
      case 2: $type="image/gif"; break;
      case 3: $type="image/jpeg"; break;
      case 4: $type="image/png"; break;
      case 5: $type="application/octet-stream"; break;
      case 6: $type="application/x-shockwave-flash"; break;
      case 7: $type="application/msword"; break;
      case 8: $type="image/bmp"; break;
   }
*/
   if ($r[fname]=="") $r[fname]="no_name.tmp";

switch ($mode) {

case "linkopen":
case "linknew":
case "flash":
case "img":

   header("Content-type: $type");
   echo $r['fdata'];
   exit;

case "download":

   $PHP_SELF = $GLOBALS['sitepath'].'test_attach.php';
//   echo '!!';
   header("Location: $PHP_SELF?mode=download_go&cryptkod=".ue($cryptkod).
      "&checkkod=$checkkod&checkrnd=$checkrnd&num=$num$sess");
   refresh("$PHP_SELF?mode=download_go&cryptkod=".ue($cryptkod).
      "&checkkod=$checkkod&checkrnd=$checkrnd&num=$num$sess");
//   header("Location: $PHP_SELF/".ue($r[fname])."?mode=download_go&cryptkod=".ue($cryptkod).
//      "&checkkod=$checkkod&checkrnd=$checkrnd&num=$num$sess");
//   refresh("$PHP_SELF/".ue($r[fname])."?mode=download_go&cryptkod=".ue($cryptkod).
//      "&checkkod=$checkkod&checkrnd=$checkrnd&num=$num$sess");

/*
   echo "$PHP_SELF/".ue($r[fname])."?mode=download_go&cryptkod=".ue($cryptkod).
      "&checkkod=$checkkod&checkrnd=$checkrnd&num=$num$sess";
*/
   exit;

case "download_go":

   header("Content-type: application/unknown"); 
   header("Content-disposition: attachment; filename=\"$r[fname]\";"); 
   echo $r[fdata];
   exit;

}


?>