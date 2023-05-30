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
require_once('../../jpgraph/jpgraph_pie.php');
require_once('../../jpgraph/jpgraph_pie3d.php');

$plot = (int) $_GET['plot'];

if (isset($_SESSION['s']['report']['current']['plots']['process'][$plot])) {
    $data = $_SESSION['s']['report']['current']['plots']['process'][$plot];
    
    // Create the graph. These two calls are always required
    $graph  = new PieGraph($data['width'], $data['height'],"auto");
    $graph->img->SetMargin(40,40,20,70);
    $graph->title->SetFont(FF_VERDANA);        
    $graph->legend->SetFont(FF_VERDANA);        
        
    //$graph->SetScale("textlin");
    
    $graph->title->Set($data['title']);
    //$graph->xaxis->title->Set($data['xtitle']);
    //$graph->yaxis->title->Set($data['ytitle']); 
    //$graph->yaxis->title->SetFont(FF_VERDANA);
    //$graph->xaxis->title->SetFont(FF_VERDANA);
    
    $lineplots = array();
    if (is_array($data['data']) && count($data['data'])) {
        foreach($data['data'] as $piece) {
            $lineplot =new PiePlot3D($piece['x']);            
            $lineplot->value->SetFont(FF_VERDANA);

            if (isset($piece['y'])) {
                $lineplot->SetLegends($piece['y']);                
            }
            
            if (isset($piece['legend']) && is_array($piece['legend'])) {
                $lineplot->SetLegends($piece['legend']);                                
            }
                        
            $graph->Add($lineplot);
            
        }
    }
    
    //$gbplot  = new GroupBarPlot($lineplots);     
    
    //$graph->legend->Pos(0.5,0.95,"center","bottom");
    //$graph->legend->SetLayout(LEGEND_HOR);
    //$graph->legend->SetFont(FF_VERDANA);
    
    // Display the graph
    $graph->Stroke();
    exit(); 
}

?>