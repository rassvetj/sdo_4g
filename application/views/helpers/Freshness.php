<?php
class HM_View_Helper_Freshness extends HM_View_Helper_Abstract
{

    public function freshness($freshness, $freshnessTitle = null)
    {
		if (empty($freshnessTitle)) {
			$freshnessTitle = _('Обновляемость');
		}    	
		$freshnessTitle .= ': ';
		if ($freshness >= 75) {
			$freshnessTitle .= _('часто');
		} elseif (($freshness >= 50) && ($freshness < 75)) {
			$freshnessTitle .= _('относительно часто');
		} elseif (($freshness >= 25) && ($freshness < 50)) {
			$freshnessTitle .= _('относительно редко');
		} else {
			$freshnessTitle .= _('редко');
		}
    	return "<span class=\"bullet cc{$freshness}\" title=\"{$freshnessTitle}\"></span>";
    }
}