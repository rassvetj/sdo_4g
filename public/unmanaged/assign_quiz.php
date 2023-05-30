<?php
require_once("1.php");
require_once("positions.lib.php");

$html=create_new_html(0,0);

$mid = get_mid_by_soid($soid);

if(isset($_POST['assign_quiz_go'])) {
   if(is_array($assigned_mids)) {
      foreach($assigned_mids as $key => $value) {
           $query = "SELECT * FROM Students WHERE MID=$value AND CID=$cid";
           $result = sql($query);
           if(sqlrows($result) > 0)
              continue;
           $query = "INSERT INTO Students (MID,CID) VALUES ($mid, $cid)";
           $result = sql($query, "err");
           sqlfree($result);
      }
      $allcontent .= "<center><a href='#' onClick='window.close();'>"._("Опросы удачно назначены")."</a></center>";
   }
}
else {
      $people = get_lastname_and_firstname_by_mid($mid);
      $allcontent = "
      <br /><br />
      <form action='' method='POST'>
      <h3>$people[LastName] $people[FirstName]</h3>";
      $allcontent .= _("Выберите курс:")." ";
      $query = "SELECT * FROM Courses";
      $result = sql($query, "err");
      $allcontent .= "<select name='cid'>";
      while($row = sqlget($result)) {
            $allcontent .= "<option value='".$row['CID']."'>".$row['Title']."</option>";
      }
      $allcontent .= "</select><br /><br />";


      if($head = get_head_by_soid($soid)) {
         $allcontent .= _("Начальнику:")."
                  <ul>
                   <input type='checkbox' name='assigned_mids[]' value='".$head['mid']."'>".$head['lastname']." ".$head['firstname']."
                  </ul>";
      }
      $allcontent .= _("Самому себе:")."
                <ul>
                 <input type='checkbox' name='assigned_mids[]' value='$mid'>".$people['LastName']." ".$people['FirstName']."
                </ul>";
      if($colleagues = get_colleagues_by_soid($soid)) {
         $allcontent .= _("Коллеги:")."
                   <ul>";
         foreach($colleagues as $key => $value) {
           $allcontent .= "<input type='checkbox' name='assigned_mids[]' value='".$key."'>".$value['lastname']." ".$value['firstname']."<br />";
         }
         $allcontent .= "</ul>";
      }

      if($subordinates = get_subordinates_by_soid($soid)) {
         $allcontent .= _("Подчиненные:")."
                   <ul>";
         foreach($subordinates as $key => $value) {
           $allcontent .= "<input type='checkbox' name='assigned_mids[]' value='".$key."'>".$value['lastname']." ".$value['firstname']."<br />";
         }
         $allcontent .= "</ul>";
      }

      $allcontent .="<input type='submit' name='assign_quiz_go' value='"._("Назначить опрос")."'>
              </form>";
}

$html=str_replace("[HEADER]",_("Назначить опрос"),$html);
$html=str_replace("[ALL-CONTENT]", $allcontent, $html);
echo $html;
?>