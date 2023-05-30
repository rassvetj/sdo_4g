<?php

//require_once("phplib.php4");
require_once("1.php");
require_once("metadata.lib.php");

$CID=(isset($_GET['CID'])) ? $_GET['CID'] : "";
$name=_("Все курсы");
$teachers="";

        function get_courses_list($MID)
        {
                global $coursestable;
                global $teacherstable;
                $html="";

                if ($r=sql("SELECT Title FROM $teacherstable, $coursestable WHERE $teacherstable.CID=$coursestable.CID AND $teacherstable.MID=$MID"))
                 if (sqlrows($r)>0)
                        {
                        while ($res=sqlget($r))
                                {
                                  $html.=$res['Title']."<br>";
                                }
                        }
                return $html;

        }

function create_teachers_list($CID)
        {

                global $coursestable;
                global $teacherstable;
                global $peopletable;
                global $sitepath;

                $html="";
                $line=loadtmpl("viewprof-1line.html");


                if ($r=sql("SELECT CID, LastName, FirstName, EMail, Login, $peopletable.MID as MID, Information FROM $teacherstable, $peopletable WHERE CID=$CID AND $teacherstable.MID=$peopletable.MID"))
                 if (sqlrows($r)>0)
                        {
                        while ($res=sqlget($r))
                                {

                                $photo=$sitepath."images/people/nophoto.gif";
                                $tmp=$line;
                                 $tmp=str_replace("[fName]",stripslashes($res['FirstName']),$tmp);
                                 $tmp=str_replace("[lName]",stripslashes($res['LastName']),$tmp);
                                 $tmp=str_replace("[mail]",stripslashes($res['EMail']),$tmp);
                                 $tmp=str_replace("[classes]",get_courses_list($res['MID']),$tmp);

//                $info=stripslashes($res['Information']);
//                $info=view_metadata( read_metadata ( $info, "" ) );
                                 $tmp=str_replace("[about]", "", $tmp);

//                                if (@is_file("images/people/".$res['MID'].$res['Login'].".gif")) $photo=$sitepath."images/people/".$res['MID'].$res['Login'].".gif";

                                $tmp=str_replace("[PHOTO]", getPhoto( $res['MID'], 0, 100, 140  ), $tmp);
                                $html.=$tmp;

                                }
                        }

                return $html;
        }


$html=loadtmpl("viewprof-main.html");
$static=loadtmpl("viewprof-static.html");

$teachers=create_teachers_list($CID);

if ($CID) $name=getField($coursestable,"Title","CID",$CID);


if ($GLOBALS['controller']->enabled) { $name='';}

$html=str_replace("[PATH]",$sitepath,$html);
$html=str_replace("[STATIC]",$static,$html);
$html=str_replace("[NAME]",$name,$html);
$html=str_replace("[Teachers]",$teachers,$html);

$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->captureFromReturn(CONTENT,$html);
$GLOBALS['controller']->setHeader(_("Информация о преподавателях"));
echo $html;
$GLOBALS['controller']->terminate();


?>