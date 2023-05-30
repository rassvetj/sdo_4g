<?
   if(!isset($sitepath)) {
               header("location: index.php"); 
               exit();
   }

   function pageHeader($pageName,$pageStatic="") {
      $html=loadtmpl("all-cHeader.html");
      $html=str_replace("[W-PAGENAME]",$pageName,$html);
      $html=str_replace("[W-PAGESTATIC]",$pageStatic,$html);
      $html=path_sess_parse($html);
      return $html;
   }

   function ph($pageName,$pageStatic="") {
      return ($GLOBALS['controller']->enabled) ? '' : pageHeader($pageName,$pageStatic);
   }


?>