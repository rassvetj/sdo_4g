<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/metadata.lib.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/teachers/organization.lib.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/teachers/manage_course.lib.php4');
//require_once("..\metadata.lib.php");

function getArrayOfTrackCourses( $trid, $level=-1 ){
  // возвращает массив имен курсов, их ступеней и идентификаторов курсов
  $res=sql("SELECT * FROM Courses, tracks2course
             WHERE tracks2course.trid=$trid
                AND Courses.cid=tracks2course.cid ORDER BY tracks2course.level",
             "errGR179");
  $i=0;
  while ( $r = sqlget($res) ){
//    if ( ( $r['level']==$level ) || ( $level==-1 ) ){
      $tmp[$i][Title]=$r['Title'];
      $tmp[$i][level]=$r['level'];
      $tmp[$i][cid]=$r['cid'];
      $i++;
//    }
//    echo $r['Title'];
   }
   sqlfree ( $res );
  return( $tmp );
}
function getLevels( $courses ){
   if( count( $courses ) >0 ){
    foreach( $courses as $course ){
      $levels[$course[level]]=$course[level];
    }
    $tmp.="<select name=level>";
    $tmp.="<option value=-1>"._("все")."</option>";
    foreach( $levels as $level ){
      $tmp.="<option value=$level>".$level."</option>";
    }
    $tmp.="</select>";
   }
  return( $tmp );
}

function showCourses( $trid, $courses ){
  // возвращает массив имен курсов, их ступеней и идентификаторов курсов

  if( count( $courses ) >0 ){
    $tmp.="<FORM ACTION='xml_exp_imp.php?oper=5&trid=".$trid."' METHOD='post'>";
    $tmp.=_("Укажите ступень").getLevels( $courses ).", "._("для которой генерируем дистрибутив");
    $tmp.="<p><table class=main cellspacing=0>";
    foreach( $courses as $course ){
      $tmp.="<tr>";
//      $tmp.="<td><input type=checkbox name=in[] value=".$course['cid']."></td>";
//      $tmp.="$course['Title']."</td>";
//      $tmp.="<td>".$course['level']."</td>";
//      $tmp.="<td>".$course['cid']."</td>";

      $tmp.="<td>".$course['Title']."</td>";
      $tmp.="<td>".$course['level']."</td>";
      $tmp.="<td>".$course['cid']."</td>";
      $tmp.="</tr>";
    }
    $tmp.="</table><p><INPUT type=submit value='"._("СГЕНЕРИРОВАТЬ")."'></FORM>";
  }
  return( $tmp );
}

function getTrack( $trid ){
  $res=sqlval("SELECT * FROM tracks WHERE trid=$trid","errGR87");
  return( $res );
}

function getCourseDescription( $cid ){
  global $coursestable;
  $res=sqlval("SELECT * FROM ".$coursestable." WHERE cid=$cid","errGR87");
//   $ss="UPDATE ".$coursestable." SET Description ='$meta' WHERE CID=$CID";

  return( $res[Description] );
}

function make_course_organization( $cid, $track_dir ){ // копирует содержание модулей в каталог dir
                                        //и возвращает оглавление курса со  ссылками на материалы

  $dir=$track_dir."/course$cid";

  if(  !is_dir($dir) )  mkdir ($dir, 0700);
  // 1. вывести организацию курса   // 2. создать и вывести модули
  $tmp=show_mod_organization( 0, $cid, 2, $dir );

  $f=fopen( $dir."/index.htm","w+");
  if( $f ){
    if ( ! fputs( $f, $tmp ) ) echo "WRITE $dir ERROR!! ";

    fclose( $f );
  }

}

function makeCoursesDistr( $trid, $courses, $dir="" ){ // формирует дистрибутив курсов по из массива
  // формирует дистрибутив по курсу
//  $track_title=getTrackTitle( $track );
   global $sitepath;
   global $tmpdir;

  $dir=$tmpdir."/track$trid";
  if(  !is_dir($dir) )  mkdir ($dir, 0700);

  $track=getTrack( $trid );

  $tmp.="<H1>".$track[name]."</H1>"._("Учебные материалы по специальности")."<BR>";
  $tmp.=$track[description];

  $tmp.="<Br>"._("версия от")." ".date("d.m.y")."<br>";
  $level=-1;
  if( count ( $courses ) > 0 ){
    foreach( $courses as $k=>$course ){
       if( $level!=$course['level'] ){
         $tmp.="<H2>"._("Семестр")." ".$course['level']."</H2>";
         $level=$course['level'];
       }

//       $dir=make_data_dir( $course[cid] );


       $fcid=1;

       $organization = make_course_organization( $course[cid], $dir ); // копирует содержание модулей в каталог dir
                                        //и возвращает оглавление курса со  ссылками на материалы
       if( $fcid ){
         $xmlFileName="course".$course[cid]."/index.htm";  // xml
         $zipFileName="course".$course[cid].".zip";
         $tmp.=($k+1).". <a href=$xmlFileName>".$course[Title]."</a><br>";
       }else{
         $tmp.=($k+1).". ".$course[Title]."<br>";
       }
       $text=getCourseDescription( $course[cid] );
       $tmp.=view_metadata( read_metadata ( $text ));
       $tmp.=$organization;
   //  modExport($cID, $xmlFileName); //
   //  createModZip($cID, $zipFileName);
   }
  }



  $f=fopen( $dir."/index.htm","w+");

//  $f=fopen( $dir."\\courses\\".$course[cid]."\\".$course[cid].".htm","w");
  if( $f ){
    if ( fputs( $f, $tmp ) )
      echo "<BR>"._("Дистрибутив подготовлен в каталоге")." $dir<BR>"._("Далее скопируйте его на компьютер с устройством для записи CD дисков и запишите диск");
    else
      echo "WRITE $dir ERROR!! ";

    fclose( $f );
  }
  echo "<P><a href='".$sitepath."temp/track$trid/index.htm' target=_browse>"._("Открыть")."</a>";
}

?>