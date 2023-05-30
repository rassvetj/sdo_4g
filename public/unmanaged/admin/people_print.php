<?
session_start();

if(!session_is_registered("arrStorePasswords")) {
    die_custom();
}
require ("setup.inc.php");
$GLOBALS['controller']->setView('DocumentPrint');
$GLOBALS['controller']->captureFromOb(CONTENT);
?>
<h1><?=_("Сгенерированные учетные записи")?></h1>
<?

if (count($_SESSION['arrStorePasswords'])) {
    $fio = array();
    $logins = array_keys($_SESSION['arrStorePasswords']);
    foreach($logins as $index => $login) {
        $logins[$index] = $GLOBALS['adodb']->Quote($login);
    }
    $sql = "SELECT Login, LastName, FirstName, Patronymic FROM People WHERE Login IN (".join(",", $logins).")";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if (strlen($row['LastName']) || strlen($row['FirstName']) || strlen($row['Patronymic'])) {
            $fio[$row['Login']] = sprintf('%s %s %s', $row['LastName'], $row['FirstName'], $row['Patronymic']);
        }
    }
    $count = count($fio);
?>
<table border="0" cellpadding="10" cellspacing="1" bgcolor="#CCCCCC">
  <tr bgcolor="#FFFFFF">
    <td><b><?=_("Логин")?></b></td>
  <?php
  if ($count) {
     echo "<td><b>"._('ФИО')."</b></td>";    
  }
  ?>
    <td><b><?=_("Пароль")?></b></td>
  </tr>
<?
        foreach ($_SESSION['arrStorePasswords'] as $key => $val) {
?>
  <tr bgcolor="#FFFFFF">
    <td><?=$key?></td>
<?php
   if ($count) {
       echo "<td>".htmlspecialchars($fio[$key])."</td>"; 
   }
?>
    <td><?=$val?></td>
  </tr>
<?
        }
        //session_unregister("arrStorePasswords");
?>
</table>
<script>
window.print();
</script>
<?
} else {
        die_custom();
}

function die_custom() {
         echo "<script>window.close()</script>";
         exit();
}
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>