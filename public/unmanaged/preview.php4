<?php
//require_once("phplib.php4");
require_once("1.php");
$out="<?xml version='1.0' encoding='{$GLOBALS['controller']->lang_controller->lang_current->encoding}'?>\n";
$out.="<?xml-stylesheet type='text/xsl' href='".$sitepath."xml2/TESTs/preview.xsl'?>\n";

if (empty($TID) || empty($QID) || empty($HTTP_COOKIE_VARS)) { header("location:".$sitepath."start.php4"); exit(); }


 $sql="SELECT xmlQ,attachFileName,attachExt,type,CID FROM TestContent,TestTitle WHERE TestContent.TID=TestTitle.TID AND TestContent.TID=".$TID." AND QID=".$QID;

 $res=sql($sql);

 $out3="";

 while ($temp=sqlget($res))
        {
                $out.=stripslashes($temp['xmlQ']);
                $aFN=$temp['attachFileName'];
                $aE=$temp['attachExt'];
                $type=$temp['type'];
                $CID=$temp['CID'];
        }


        switch ($type)
                {
                case 1 : $sType="<multiple_choice>"; break;
                case 2 : $sType="<match>"; break;
                case 3 : $sType="<filling_form>"; break;
                case 4 : $sType="<free/>"; break;
                case 5 : $sType="<attach>"; break;
                }



        $n=strpos($out,$sType);
        $out1=substr($out,0,$n);
        $out2=substr($out,$n,strlen($out));
        if ($n!=FALSE && !empty($aFN))
                {
                 $put="application";
                     if (!empty($aE))
                        if (strtolower($aE)=="swf")
                                $put="flash";
                        if ((strtolower($aE)=="gif") || (strtolower($aE)=="jpg") || (strtolower($aE)=="png") || (strtolower($aE)=="bmp"))
                                $put="image";
                $out3="<object src='".$sitepath."COURSES/course".$CID."/TESTS/".$aFN."' type='".$put."'/>"        ;

                }

header ("Content-type: text/xml");
print $out1.$out3.$out2;
?>