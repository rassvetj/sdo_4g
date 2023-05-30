<?php
//require_once("1.php");
require_once("lib/jpgraph/jpgraph.php");
require_once("lib/jpgraph/jpgraph_radar.php");

if (isset($_GET['competences'])) {
    $data = unserialize(urldecode($_GET['competences']));
    if (is_array($data) && count($data)) {
        // Create the basic radar graph
        $graph = new RadarGraph(320,240,"auto");
        $graph->img->SetAntiAliasing();
        $graph->SetScale('lin',-2,100);
        // Set background color and shadow
        $graph->SetColor("white");
        $graph->axis->scale->ticks->Set(52);
        //$graph->SetShadow();

        // Position the graph
        $graph->SetCenter(0.4,0.55);

        // Setup the axis formatting
        $graph->axis->SetFont(FF_FONT1);

        // Setup the grid lines
        $graph->grid->SetLineStyle("solid");
        $graph->grid->SetColor("black");
        $graph->grid->Show();
        $graph->HideTickMarks();
                
        // Setup graph titles
        //$graph->title->Set("Competences");
        //$graph->title->SetFont(FF_FONT1,FS_BOLD);
        
        foreach($data as $v) {
            //$titles[] = to_translit($v['name']);
            $plot_data[] = (int) $v['need'];
            $plot_data2[] = (int) $v['current'];
        }                

        //$graph->SetTitles($titles);


        // Create the first radar plot      
        $plot = new RadarPlot($plot_data);
        $plot->SetLegend("Goal");
        $plot->SetColor("red@0.2");
        $plot->SetFill(true);
        $plot->SetLineWeight(1);
        $plot->SetFillColor('red@0.7');
        

        // Create the second radar plot
        $plot2 = new RadarPlot($plot_data2);
        $plot2->SetLegend("Actual");
        $plot2->SetLineWeight(1);
        $plot2->SetColor("blue@0.2");
        $plot2->SetFill(true);
        $plot2->SetFillColor('blue@0.7');
        $plot2->mark->SetType(MARK_FILLEDCIRCLE,'blue@0.2');
                
        // Add the plots to the graph
        $graph->Add($plot);
        $graph->Add($plot2);

        // And output the graph
        $graph->Stroke();
    }
}

?>