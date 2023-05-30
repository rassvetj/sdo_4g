<?

   include("1.php");

   echo show_tb();
   echo "<h3>"._("Сейчас на сервере находятся активные пользователи:")."</h3><ul>";

   $res=sql("SELECT * FROM People WHERE last>".(time()-10*60),"errUO69");
   while ($r=sqlget($res)) echo "<li>$r[Login]: $r[FirstName] $r[LastName]";
   sqlfree($res);

   echo "</ul><P>"._("Данные основаны на активности зарегистрированных пользователей за последние 10 минут.");
   echo show_tb();


?>