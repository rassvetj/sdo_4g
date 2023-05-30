<?php
class HM_Test_Result_ResultService extends HM_Service_Abstract
{

	public function getEveragePercent($balmax, $balmin, $bal, $balmax2, $balmin2){
		
		$bal = str_replace(',', '.', $bal) - 0;
        $percent = 0;
		if($wtf = $balmax2 - $balmin){
			$percent =  round( intval( (($bal - $balmin)*100)/$wtf ), 2 );
            $percent = ($percent < 0) ? 0 : $percent;
		}
		return $percent;
		
	}
	
	public function getEverageMark($balmax, $balmin, $bal, $balmax2, $balmin2){
		
		$bal = str_replace(',', '.', $bal) - 0;
        $bal = ($bal < 0) ? 0 : $bal;
		return round($bal, 2);
		
	}
	
}