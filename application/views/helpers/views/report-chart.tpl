<h3 class="report-chart-title"><?php echo $this->title?></h3>
<div <?php if ($this->showtable == HM_View_Helper_ReportChart::TABLE_DISPLAY_INLINE): ?>class="report-chart-graph"<?php endif;?>>
    <div id="<?php echo $this->chartId;?>"></div>
</div>
<?php if ($this->showtable != HM_View_Helper_ReportChart::TABLE_DISPLAY_NONE): ?>
<div <?php if ($this->showtable == HM_View_Helper_ReportChart::TABLE_DISPLAY_INLINE): ?>class="report-chart-table"<?php endif;?>>
<table>
<?php $i = 0;?>
<?php foreach ($this->data as $num => $row): ?>
<tr>
<?php foreach ($row as $cell): ?>
<?php if (!$i): ?>
<th><?php echo $cell;?></th>
<?php elseif (!$this->multigraph && ($i == (count($this->data) - 1))): ?>
<td style="font-weight: bold;"><?php echo ($cell === false) ? '-' : $cell;?></td>
<?php else: ?>
<td><?php echo ($cell === false) ? '-' : $cell;?></td>
<?php endif;?>
<?php endforeach;?>
</tr>
<?php $i++; ?>
<?php endforeach;?>
</table>
</div>
<?php endif;?>