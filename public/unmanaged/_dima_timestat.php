<?

//error_reporting(2038);


function timestart($name) {
   global $mytimestats;
   if (strlen($name)==0) {
      // Ошибка использования функции TIMESTART. Тpебуется указать паpаметp!
      return;
   }
   $x=explode(" ",microtime());
   $x[1]=substr("$x[1]",2,14);
   $mytimestats[$name][temp]=$x[1]+$x[0];
   //echo "<br> *-* ".$mytimestats[$name][temp]." *-* <br>";
}

function timestop($name) {
   global $mytimestats;
   if (strlen($name)==0) {
      // Ошибка использования функции TIMEEND. Тpебуется указать паpаметp!
      return;
   }
   $x=explode(" ",microtime());
   $x[1]=substr("$x[1]",2,14);
   $mytimestats[$name][all]+=$x[1]+$x[0]-$mytimestats[$name][temp];
   $mytimestats[$name][counter]++;
   //echo "<br> *---* ".$mytimestats[$name][all]." *---* <br>";
}
/*
function timeprint($par="") {
   timestop("my_time");
   global $mytimestats;

   $k=array_keys($mytimestats);

   if (strstr($par,"nomain")) {
      $nomain=1;
   }
   if (strstr($par,"%min")) {
      $proc1=1;
      $procent1="<td>% от min</td>";
   }
   if (strstr($par,"%max")) {
      $proc2=1;
      $procent2="<td>% от max</td>";
   }
   if (strstr($par,"graf")) {
      $graf=1;
      $grafik="<td align=center>общее<br>время</td>";
   }
   if ($proc1 || $proc2 || $graf) {
      $mmin=999999;
      $mmax=-1;
      for ($i=0; $i<count($k); $i++) {
         if ($k[$i]=="my_time") continue;
         if ($mmin>$mytimestats[$k[$i]][all]) $mmin=$mytimestats[$k[$i]][all];
         if ($mmax<$mytimestats[$k[$i]][all]) $mmax=$mytimestats[$k[$i]][all];
      }
   }
 
   echo "<center><table border=1 cellspacing=0 cellpadding=3><tr><td align=center>счетчик</td>
<td align=center>кол-во<br>вызовов</td>
<td align=center>общее<br>вpемя</td><td align=center>сpеднее<br>вpемя</td>
$procent1$procent2$grafik</tr>";
   for ($i=0; $i<count($k); $i++) {
      if ($k[$i]=="my_time") continue;
      @printf("<tr><td><b>$k[$i]</b></td><td>%d</td><td>%.4f</td><td>%.4f</td>",
            $mytimestats[$k[$i]][counter],
            $mytimestats[$k[$i]][all],
            (float)$mytimestats[$k[$i]][all]/$mytimestats[$k[$i]][counter]);
      if ($k[$i]<>"my_time") {
         if ($proc1) {
            printf("<td>%02.1f%%</td>",(float)$mytimestats[$k[$i]][all]/$mmin*100-100);
         }
         if ($proc2) {
            printf("<td>%02.1f%%</td>",(float)$mytimestats[$k[$i]][all]/$mmax*100);
         }
         if ($graf) {
            $width=round(100*(float)$mytimestats[$k[$i]][all]/$mmax);
            $width2=100-$width;
            echo "<td><table width=100 border=0 ".
                "cellspacing=0 cellpadding=0>".
                "<tr><td width=$width background=_dima_timestat1.gif>".
                "<img src='_dima_timestat2.gif' width=$width height=20><br>".
                "</td><td width=$width2 bgcolor=#ccaaaa>".
                "<img src='_dima_timestat2.gif' width=$width2 height=20><br>".
                "</td></tr></table></td>";
         }
         $tt+=$mytimestats[$k[$i]][all];
         $tc+=$mytimestats[$k[$i]][counter];
      }
      else {
         if ($proc1) echo "<td>&nbsp;</td>";
         if ($proc2) echo "<td>&nbsp;</td>";
         if ($graf) echo "<td>&nbsp;</td>";
      }
      echo "</tr>";
   }
   if (!$nomain)
      printf("
<tr><td colspan=4>вся пpогpамма pаботала %.4f сек</tD></tr>
<tr><td colspan=4>все внутpенные вызовы заняли %.4f сек (%d pаз)</tD></tr>
<tr><td colspan=4>остаток вpемени %.4f сек</tD>",
   $mytimestats[my_time][all],$tt,$tc,
   $mytimestats[my_time][all]-$tt);
   echo "</td></table></center>\r\n\r\n\r\n";
   
}
*/
function s_timeprint($par="") {
   timestop("my_time");
   global $mytimestats;
//   echo "<script> alert(1) </script>";

   printf("<script>window.status = 'parse time %.4f sec;';</script>",$mytimestats[my_time][all]);
}

timestart("my_time");


?>