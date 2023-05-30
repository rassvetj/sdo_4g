
<ul style="list-style-type: none;">
<?php foreach($this->total_point as $group_name => $ball): ?>
	<li><b><?=$ball?></b> - <?=$this->prof_motive_groups[$group_name]?> (<?=$group_name?>)</li>
<?php endforeach; ?>
</ul>