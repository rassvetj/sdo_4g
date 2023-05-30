<?php

   require_once("1.php");
   if (!$stud) login_error();

   istest();
      if (empty($s[skurs]) && empty($s[tkurs])) login_error();

   $html=show_tb(1);
   $allcontent=loadtmpl("results-main.html");
   $cheader=loadtmpl("all-cHeader.html");
   $allwords=loadwords("results-words.html");
   $words['PAGENAME']=$allwords[0];
   $words['moder']=$allwords[1];
   $words['tests']=$allwords[2];
   $words['lesson']=$allwords[3];

   $words['PAGESTATIC']=loadtmpl("results-static.html");

   $html=str_replace("[ALL-CONTENT]",$allcontent,$html);
   $html=str_replace("[results-HEADER]",$cheader,$html);


   printtmpl($html);

?>