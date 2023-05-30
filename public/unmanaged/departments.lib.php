<?php

function getColors(){
   $tmp[1][id]="white";                 $tmp[1][title]=_("белый");
   $tmp[2][id]="gray";                         $tmp[2][title]=_("серый");
   $tmp[3][id]="yellow";                 $tmp[3][title]=_("желтый");
   $tmp[4][id]="red";                         $tmp[4][title]=_("красный");
   $tmp[5][id]="lightblue";         $tmp[5][title]=_("голубой");
   $tmp[6][id]="blue";                         $tmp[6][title]=_("синий");
   $tmp[7][id]="cyan";                         $tmp[7][title]=_("фисташковый");
   $tmp[8][id]="magenta";                 $tmp[8][title]=_("фиолетовый");
   $tmp[9][id]="darkgray";                 $tmp[9][title]=_("темносерый");
   $tmp[10][id]="black";                 $tmp[10][title]=_("черный");
   $tmp[11][id]="green";                 $tmp[11][title]=_("зеленый");
   $tmp[12][id]="braun";                 $tmp[12][title]=_("коричневый");
   $tmp[13][id]="Olive";                 $tmp[13][title]=_("оливковый");
   $tmp[14][id]="Navy";                 $tmp[14][title]=_("небесный");
   $tmp[15][id]="Purple";                 $tmp[15][title]=_("пурпурный");
   $tmp[16][id]="Silver";                 $tmp[16][title]=_("серебрянный");
   $tmp[17][id]="Lime";                 $tmp[17][title]=_("лимонный");
   $tmp[18][id]="Fuchsia";                 $tmp[18][title]=_("малиновый");
   $tmp[19][id]="Maroon";                 $tmp[19][title]=_("бордовый");

   return( $tmp );
}

function getPalette( $color="white" ){
   $tmp.="<SELECT name='color'>";
   $tmp.="<option value=0>- "._("укажите")." -</option>";
   $cols = getColors();
   foreach( $cols as $col ){
      if( $color == $col[id] ) $sel="selected"; else $sel="";
      $tmp.="<option value=".$col[id]." $sel>".$col[title]."</option>";
   }
   $tmp.="</SELECT>";
  return( $tmp );
}  

function getDivs( $self_did, $owner_did ){
  $tmp="SELECT * FROM departments";
  $r=sql( $tmp );

 $divs="<SELECT name='owner_did'>";
 $divs.="<option value=0> - "._("укажите")." -</option>";
  while( $res=sqlget( $r ) ){
   if( $res[ did ] != $self_did ){
     if( $res[ did ] == $owner_did ) $sel=" selected "; else $sel="";
     $divs.="<option value=".$res[ did ] ." $sel>".$res[ name ]."</option>";
   }
  }
   $divs.="</SELECT>";// как задать только одного?";
  sqlfree($r);

//      $rq="ALTER TABLE departments ADD owner_did int";
//      $res=sql( $rq,"ERR upgrading $table");


 return( $divs );
}


function show_structure( $divs ){
if (is_array($divs)) {
  foreach( $divs as $div ){
    $sh="";
    for($i=0;$i<$div[ level ];$i++)
      $sh.="--";

    $tmp.=$sh.$div[ name ]."<BR>";
  }
}
  return( $tmp );
}


function show_sublevel( $divs, $did, $sh="" ){
        if (is_array($divs)) {
  foreach( $divs as $r ){
    if( $r[ owner_did ] == $did ){
     if( $did == 0 ){ $b="<B>"; $bb="</b>"; } else{ $b=""; $bb="";}
     if($r[color]>""){ $color=$r[color]; $pic=""; }else{ $color="white"; $pic="-";}
     
     
      $tmp.="<tr>
            <td style='background:$color' width=30 align='center'>$pic</td>
            <td> $sh <a href=$PHP_SELF?c=edit&did=$r[did]$sess>
                 $b $r[name] $bb
            </a></td>
            <td>" . get_people_military_info($r['mid']) . "</td>
            <td>$r[info]</td>
            <td  align='center'><a href=$PHP_SELF?c=delete&did=$r[did]$sess
            onclick=\"if (!confirm('"._("Удалить?")."')) return false;\" >".getIcon("delete")."</a></tr>";

      $tmp.=show_sublevel( $divs, $r[did], $sh.".." )."<P/>";
    }
  }
        }
  return( $tmp );
}

function get_structure( ){

  $tmp="SELECT * FROM departments";
  $res=sql( $tmp );

  while( $r=sqlget( $res ) ){
     $divs[ $r[ did ] ][ did ]= $r[ did ];
     $divs[ $r[ did ] ][ owner_did ]= $r[ owner_did ];
     $divs[ $r[ did ] ][ name ]= $r[ name ];
     $divs[ $r[ did ] ][ color ]= $r[ color ];
     $divs[ $r[ did ] ][ mid ]= $r[ mid ];
     $divs[ $r[ did ] ][ info ]= $r[ info ];
  }
  sqlfree($r);
  return( $divs );
}

function get_structure_level( $divs, $div, $i=0 ){
  // check infinite loop
  $i++;
  if( ( $divs[ $div ][ owner_did ] > 0 ) && ( $i < count ( $divs ) ) ){
     $level=get_structure_level( $divs, $divs[ $div ][ owner_did ], $i ) + 1;
//     echo "level= $level !! ";
  }else
    $level = 0;
  return( $level );
}

function set_structure_levels( &$divs ){
if (is_array($divs)) {
  foreach( $divs as $div ){
//    echo $div[ did ]."; ";
    $level = get_structure_level( $divs, $div[ did ] );
    $divs[ $div[ did ] ] [ level ] = $level;
    $divs[ $div[ did ] ] [ org ] = $i++;
  }
}
}

function org_structure_levels( &$divs ){
if (is_array($divs)) {
  foreach( $divs as $div ){
//    echo $div[ did ]."; ";
    $level = get_structure_level( $divs, $div[ did ] );
    $divs[ $div[ did ] ] [ level ] = $level;
  }
}
}


?>