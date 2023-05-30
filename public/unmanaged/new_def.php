<?php

function getallperiods( ){ // возвращает все пары, времена их начала окончания в массиве
    $rq="SELECT * FROM periods ORDER BY starttime";
    $res=sql( $rq, "err-select shedule 1");

    while( $r=sqlget($res)){ // для всех занятий на курсе
       $periods[$i][name]=$r[name];
       $periods[$i][starttime]=$r[starttime];
       $periods[$i][stoptime]=$r[stoptime];
       $i++;
    }
    sqlfree( $res );
  return( $periods );
}

// пример вызова $num=getlessonperiod( getallperiods(), 640);

function getlessonperiod( $periods, $time ){ // возвращает номер пары
  // time - кол-во минут от начала дня

  foreach( $periods as $i=>$period){
    if( $time<=$period[stoptime] AND $time>=$period[starttime] )
      $num=$i;
  }
  return( $num+1 );
}

function getlessontime( $period, &$starttime, &$stoptime ){ // возвращает время и окончания пары
  $rq="SELECT * FROM periods";
  $res=sql( $rq, "err-select shedule 1");
  while( $r=sqlget($res)){ // для всех занятий на курсе
       $period[name]      = $r[name];
       $period[starttime] = $r[starttime];
       $period[stoptime]  = $r[stoptime];
  }
  return( $time );
}

?>