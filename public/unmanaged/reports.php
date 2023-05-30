<?
        require_once('1.php');
        echo show_tb();
        echo ph(_("Отчеты"));
        echo("<b><a href='reports/form_output.php'>"._("Результаты тестирования")."</a></b><br>");
        echo "<br />";
        echo("<b><a href='reports/teachers_load.php'>"._("Учебная нагрузка")."</a></b><br>");
//        echo "<br />";
//        echo("<b><a href='reports/plan_graph_period.php'>План график</a></b><br>");                
        echo "<br />";        
        echo("<b><a href='reports/studying_period.php'>"._("Статистика обучения")."</a></b><br>");        
        echo "<br />";
        echo("<b><a href='reports/schedule_groups.php'>"._("Расписание групп")."</a></b><br>");                
        echo show_tb();

?>