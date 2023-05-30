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
require_once('../../jpgraph/jpgraph_radar.php');

$plot = (int) $_GET['plot'];

if (isset($_SESSION['s']['report']['current']['plots']['process'][$plot])) {
    $data = $_SESSION['s']['report']['current']['plots']['process'][$plot];
    
    // Create the graph. These two calls are always required
    $graph  = new RadarGraph($data['width'], $data['height'],"auto");
    $graph->img->SetMargin(40,40,20,70);
    $graph->title->SetFont(FF_VERDANA);        
        
    $graph->title->Set($data['title']);

    $lineplots = array();
    if (is_array($data['data']) && count($data['data'])) {
        foreach($data['data'] as $piece) {
            $lineplot =new RadarPlot($piece['x']);

            if (isset($piece['x'])) {            
                $graph->SetTitles($piece['y']);
            }            
            
            $lineplot->SetColor($piece['color']);
            
            if (isset($piece['legend'])) {
                $lineplot->SetLegend($piece['legend']);
            }
            
            $lineplot->SetFill(@$piece['fill']);
            if ($piece['fill']) {
                $lineplot->SetFillColor($piece['color']);                
            }
            
            $graph->Add($lineplot);
            
        }
    }
    
    $graph->legend->Pos(0.5,0.99,"center","bottom");
    $graph->legend->SetLayout(LEGEND_HOR);
    $graph->legend->SetFont(FF_VERDANA);
    
    // Display the graph
    $graph->Stroke();
    exit(); 
}

?>