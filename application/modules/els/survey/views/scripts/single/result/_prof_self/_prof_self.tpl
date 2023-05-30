<br>
<div class="sr-chart-area">
	<canvas id="myChart" width="600" height="500"></canvas>	
	<br>
	<p></p>	
</div>
<?php $this->inlineScript()->captureStart()?>
	var ctx = document.getElementById("myChart").getContext('2d');
	ctx.height = 500;
	var myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: ["<?=implode('", "', $this->prof_self_groups)?>"],
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
