<?php
ini_set("session.cookie_lifetime",0);         // кука умирает при закрытии браузера
ini_set("session.auto_start",0);              // автостарт сессий не нужен
ini_set("session.cookie_path","/");           // путь для кук
ini_set("session.cookie_domain",$cookiehost); // путь для кук

@session_start();
@session_register("s");
@setcookie(session_name(),session_id(),0,"/");
@setcookie($mysessid,session_id(),0,"/");

require_once('../../jpgraph/jpgraph.php');
require_once('../../jpgraph/jpgraph_line.php');

$plot = (int) $_GET['plot'];

if (isset($_SESSION['s']['report']['current']['plots']['process'][$plot])) {
    $data = $_SESSION['s']['report']['current']['plots']['process'][$plot];
    
    // Create the graph. These two calls are always required
    $graph  = new Graph($data['width'], $data['height'],"auto");
    $graph->img->SetMargin(40,40,20,70);
    $graph->title->SetFont(FF_VERDANA);
            
    $graph->SetScale("textlin");
    
    $graph->title->Set($data['title']);
    $graph->xaxis->title->Set($data['xtitle']);
    $graph->yaxis->title->Set($data['ytitle']); 
    $graph->yaxis->title->SetFont(FF_VERDANA);
    $graph->xaxis->title->SetFont(FF_VERDANA);
    $graph->yaxis->SetFont(FF_VERDANA);
    $graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,7);    
    $graph->xaxis->SetTickSide(SIDE_LEFT);
    $graph->xaxis->SetLabelAngle(90);
    $graph->xaxis->SetLabelMargin(10);
                
       
    if (is_array($data['data']) && count($data['data'])) {
        
        $coordinate = array('x','y');
                
        foreach($data['data'] as $piece) {
            //Обрежем строки до заданной длинны
            foreach ($coordinate as $coord){
                if (isset($piece[$coord])){
                    foreach ($piece[$coord] as $key=>$val){
                        if (strlen($val)>$data[$coord.'TextMaxLen']){
                            $piece[$coord][$key] = substr($val,0,$data[$coord.'TextMaxLen']).'...';
                        }
                    }
                }
            }
            // Create the linear plot
            if (isset($piece['x'])) {            
                $graph->xaxis->SetTickLabels($piece['x']);
            }
            $lineplot =new LinePlot($piece['y']);                
            
            $lineplot->SetColor($piece['color']);
            
            if (isset($piece['legend'])) {
                $lineplot->SetLegend($piece['legend']);
            }
            
            // Add the plot to the graph
            $graph->Add($lineplot);
        }
    }
    
    //$graph->yaxis->scale->SetAutoMax(100);
    $graph->yaxis->scale->SetAutoMin(0);
        
    $graph->legend->Pos(0.5,0.95,"center","bottom");
    $graph->legend->SetLayout(LEGEND_HOR);
    $graph->legend->SetFont(FF_VERDANA);
    
    
    // Display the graph
    $graph->Stroke();
    exit(); 
}

?>