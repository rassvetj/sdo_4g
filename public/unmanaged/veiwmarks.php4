<?php
// version 2.1 DK. Добвлено
// автоматическое выставление оценок с помощью формулы
//
// define all varibles and settings
   require_once("1.php");
   include("formula_calc.php");

// $courses - course array of current user
// pr($courses)  
   if (!$stud) login_error();
// redirect to the index page if user is not registred
   if (empty($courses)) login_error();
// redirect to the index page if user have no Courses
// get value SHEID - current course id of select first of user courses
   $SHEID=(isset($_GET['SHEID'])) ? intval($_GET['SHEID']) : 0;
   
//   pr($_POST);

   if (isset($_POST['sheid']) && isset($_POST['mid']) && isset($_POST['mark'])){

          updateRes($_POST['sheid'], $_POST['mid'], $_POST['mark']);  
          winclose();
          die();
      }

   if (!$SHEID) {
      winclose();
      die();
      }

   $html="HELOOOO";
   printtmpl($html);
?>