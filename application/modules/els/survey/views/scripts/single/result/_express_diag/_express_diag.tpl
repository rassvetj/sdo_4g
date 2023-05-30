<br>
<div class="sr-chart-area">
	<canvas id="myChart" width="600" height="500"></canvas>	
	<br>
	<p>1 - сфера способностей к искусству.</p>
	<p>2 - сфера технических способностей.</p>
	<p>3 - сфера работы с людьми.</p>
	<p>4 – сфера способностей к научной деятельности.</p>
	<p>5 – сфера физического труда.</p>
	<p>6 – сфера деятельности в области предпринимательства, экономики и финансов.</p>
	<br>	
</div>
<?php $this->inlineScript()->captureStart()?>
	var ctx = document.getElementById("myChart").getContext('2d');
	ctx.height = 500;
	var myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: ["<?=implode('", "', array_keys($this->total_point))?>"],
			datasets: [{
				label: '',
				data: [<?=implode(', ', $this->total_point)?>],
				backgroundColor: [
					'rgba(255, 99, 132, 0.2)',
					'rgba(54, 162, 235, 0.2)',
					'rgba(255, 206, 86, 0.2)',
					'rgba(75, 192, 192, 0.2)',
					'rgba(160, 6, 186, 0.2)',
					'rgba(12, 255, 1, 0.2)',					
				],
				borderColor: [
					'rgba(255,99,132,1)',
					'rgba(54, 162, 235, 1)',
					'rgba(255, 206, 86, 1)',
					'rgba(75, 192, 192, 1)',
					'rgba(195, 20, 186, 1)',
					'rgba(75, 255, 50, 1)',
				],
				borderWidth: 1
			}]
		},
		options: {
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero:true
					}
				}]
			},
			 legend: {
				display: false
			},
			tooltips: {
				callbacks: {
				   label: function(tooltipItem) {
						  return tooltipItem.yLabel;
				   }
				}
			}
			
			
		}
	});
<?php $this->inlineScript()->captureEnd()?>
