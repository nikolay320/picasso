jQuery(document).ready(function($) {
	if (!jQuery().remodal) {
		return;
	}

	var radarChart = null,
	    barChart = null;

	var chartModalInstance = $('.picasso-idea-chart-modal').remodal({
		hashTracking: false,
	});

	$(document).on('click', '.picasso-idea-init-chart', function(event) {
	    event.preventDefault();

	    if (radarChart != null) {
	        radarChart.destroy();
	    }

	    if (barChart != null) {
	        barChart.destroy();
	    }
	    
	    var _radar_chart_labels = $(this).attr('data-radar-chart-labels'),
	        radar_chart_labels = _radar_chart_labels.split(','),
	        radar_chart_label = $(this).attr('data-radar-chart-label'),
	        _radar_chart_data = $(this).attr('data-radar-chart-data'),
	        radar_chart_data = _radar_chart_data.split(','),
	        _bar_chart_data = $(this).attr('data-bar-chart-data'),
	        bar_chart_data = _bar_chart_data.split(','),
	        bar_chart_label = $(this).attr('data-bar-chart-label'),
	        bar_chart_star_color = $(this).attr('data-bar-chart-star-color'),
	        chart_data_found = $(this).attr('data-chart-data-found'),
	        radar_chart_canvas_id = 'picasso-idea-radar-chart',
	        bar_chart_canvas_id = 'picasso-idea-horizontal-chart';

	    if (chart_data_found === 'true') {
	    	chartModalInstance.open();
	        picasso_chart(radar_chart_canvas_id, bar_chart_canvas_id, radar_chart_labels, radar_chart_label, radar_chart_data, bar_chart_data, bar_chart_label, bar_chart_star_color);
	    }
	});

	picasso_chart = function(radar_chart_canvas_id, bar_chart_canvas_id, radar_chart_labels, radar_chart_label, radar_chart_data, bar_chart_data, bar_chart_label, bar_chart_star_color) {
	    // radar chart
	    var radarCtx = document.getElementById(radar_chart_canvas_id);

	    var radarData = {
	        labels: radar_chart_labels,
	        datasets: [
	            {
	                label: radar_chart_label,
	                backgroundColor: "rgba(179,181,198,0.2)",
	                borderColor: "rgba(179,181,198,1)",
	                pointBackgroundColor: "rgba(179,181,198,1)",
	                pointBorderColor: "#fff",
	                pointHoverBackgroundColor: "#fff",
	                pointHoverBorderColor: "rgba(179,181,198,1)",
	                data: radar_chart_data
	            }
	        ]
	    };

	    var radarOptions = {
	        responsive : true,
	        legend: {
	            display: false,
	        },
	        scale: {
	            ticks: {
	                max: 5,
	                min: 0,
	                stepSize: 1,
	                fontSize: 12
	            },
	            pointLabels: {
	                fontFamily: "Roboto Condensed",
	                fontSize: 14,
	            },
	        },
	    };

	    // bar chart
	    var barCtx = document.getElementById(bar_chart_canvas_id);

	    var barData = {
	        labels: ["\uf005\uf005\uf005\uf005\uf005", "\uf005\uf005\uf005\uf005\uf123", "\uf005\uf005\uf005\uf005\uf006", "\uf005\uf005\uf005\uf123\uf006", "\uf005\uf005\uf005\uf006\uf006", "\uf005\uf005\uf123\uf006\uf006", "\uf005\uf005\uf006\uf006\uf006", "\uf005\uf123\uf006\uf006\uf006", "\uf005\uf006\uf006\uf006\uf006", "\uf123\uf006\uf006\uf006\uf006", "\uf006\uf006\uf006\uf006\uf006"],
	        datasets: [{
	            label: bar_chart_label,
	            backgroundColor: "rgba(179,181,198,0.2)",
	            borderColor: "rgba(179,181,198,1)",
	            pointBackgroundColor: "rgba(179,181,198,1)",
	            pointBorderColor: "#fff",
	            pointHoverBackgroundColor: "#fff",
	            pointHoverBorderColor: "rgba(179,181,198,1)",
	            data: bar_chart_data,
	        }]
	    };

	    var barOptions = {
	        legend: {
	            display: false,
	        },
	        tooltips: {
	            enabled: false,
	        },
	        scales: {
	            yAxes: [{
	                gridLines: {
	                    display: false,
	                },
	                ticks: {
	                    fontFamily: 'FontAwesome',
	                    fontColor: bar_chart_star_color,
	                    fontSize: 18,
	                },
	            }],
	            xAxes: [{
	                gridLines: {
	                    display: false,
	                    drawBorder: false,
	                },
	                ticks: {
	                    display: false,
	                },
	            }],
	        },
	        hover: {
	            animationDuration: 0
	        },
	        animation: {
	            onComplete: function() {
	                var chartInstance = this.chart;
	                var ctx = chartInstance.ctx;
	                ctx.textBaseline = 'top';
	                ctx.fillStyle = '#333';
	                ctx.font = '14px Roboto Condensed';
	                Chart.helpers.each(this.data.datasets.forEach(function(dataset, i) {
	                    var meta = chartInstance.controller.getDatasetMeta(i);
	                    Chart.helpers.each(meta.data.forEach(function(bar, index) {
	                        if (dataset.data[index] == 0) {
	                            ctx.fillText(dataset.data[index], bar._model.x + 10, bar._model.y - 8);
	                        } else {
	                            ctx.fillText(dataset.data[index], bar._model.x - 25, bar._model.y - 8);
	                        }
	                    }), this)
	                }), this);
	            }
	        }
	    };

	    radarChart = new Chart(radarCtx, {
	        type: 'radar',
	        data: radarData,
	        options: radarOptions
	    });

	    barChart = new Chart(barCtx, {
	        type: 'horizontalBar',
	        data: barData,
	        options: barOptions,
	    });
	}

	// Destroy charts after closing the modal
	$(document).on('closed', '.picasso-idea-chart-modal', function(event) {
		if (radarChart != null) {
		    radarChart.destroy();
		}

		if (barChart != null) {
		    barChart.destroy();
		}
	});
	
});