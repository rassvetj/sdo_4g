<?php
require_once('1.php');
require_once('lib/laws/laws.lib.php');
require_once('lib/laws/law.class.php');
require_once('lib/laws/laws.class.php');
require_once('teachers/ziplib.php');

$template   = $_SESSION['reportTmpData']['template'];
$reportName = $_SESSION['reportTmpData']['reportName'];
$rtid       = $_SESSION['reportTmpData']['rtid'];
        
if (isset($_POST['save']) && $template && $reportName) {
    
    //сохранение
    $tmpFileName = $wwf.'/temp/rep'.md5(time()).'.tmp';
    $fp = fopen($tmpFileName,'w');
    if (fwrite($fp,$template) === false) {
        echo _('Нвозможно сохранить отчёт!');
        exit();
    }
    fclose($fp);

    //формируем данные
    $data = $files = array();
    $date = getdate(time());
    $data['title']  = ($_POST['repTitle'])?$_POST['repTitle']:$reportName;
    $data['author'] = getpeoplename($s['mid']);
    $data['create_date']['Date_Year'] = $date['year'];
    $data['create_date']['Date_Month'] = $date['mon'];
    $data['create_date']['Date_Day'] = $date['mday'];
    $data['modify_date']['Date_Year'] = $date['year'];
    $data['modify_date']['Date_Month'] = $date['mon'];
    $data['modify_date']['Date_Day'] =  $date['mday'];
    $data['type'] = $rtid;

    $files['material']['error'] = 0;
    $files['material']['name'] = 'report.html';
    $files['material']['size'] = filesize($tmpFileName);
    $files['material']['tmp_name'] = $tmpFileName;
    $files['material']['type'] = 'text/html';
        
    CLaws::add($data,$files);
    echo '<script language="JavaScript" type="text/javascript">
            self.close();
          </script>';
    exit();
}
?>  
<html>
    <body>
        <script language="JavaScript" type="text/javascript">
            <!--
            function prnt() {
                window.opener.print();
                self.close();
            }
            // -->
        </script>
        <form method="post" action="">
            <div align="center">
                Название : <input type="text" name='repTitle' value = '<?php echo $reportName;?>'/>&nbsp;
                <input type="button" onClick = "prnt();" value='Распечатать'/>
                <input type="submit" name='save' value='Сохранить'/>
            </div>
        </form>
    </body>
</html>

