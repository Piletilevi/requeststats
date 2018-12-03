(function() {
	var chartElements = document.querySelectorAll('.chart');

	for (var i = chartElements.length; i--;) {
		var chartElement = chartElements[i];
		var params = JSON.parse(chartElement.getAttribute('data-params'));
		params['options'] = {};
		var params = JSON.parse(chartElement.getAttribute('data-params'));
		var canvasElement = document.createElement('canvas');
		canvasElement.width = chartElement.offsetWidth;
		canvasElement.height = chartElement.offsetHeight;
		chartElement.appendChild(canvasElement);
		params.options = params.options || {};
		params.options.tooltips = {mode: 'x-axis'};
		if (params.data.datasets.length <= 1) {
			params.options.legend = {display: false};
		}
		var chart = new Chart(canvasElement, params);
	}
    console.log(params);
	$('.datepicker').daterangepicker({
		singleDatePicker: true,
		locale: {
			format: 'DD.MM.YYYY',
			firstDay: 1
		}
	});
	$('.daterangeinput').daterangepicker({
		dateLimit: {
			days: 90
		},
		showCustomRangeLabel: false,
		alwaysShowCalendars: true,
		applyClass: 'btn-primary',
		ranges: {
			'Today': [moment(), moment()],
			'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		},
		locale: {
			format: 'DD.MM.YYYY',
			firstDay: 1
		}
	});

})();

function popChartCompare() {

	/*
	var popCanvas = $("#popChart");
	var popCanvas = document.getElementById("popChart").getContext("2d");
	*/
	var popCanvas = document.getElementById("popChart");
	var $names = JSON.parse(popCanvas.getAttribute('data-names'));
	//$names['options'] = {};
	var $labels = [];
	var $durations= [];
	/*fonts = Object.keys(fonts).map(i => ({
		family: fonts[i].family,
		weight: fonts[i].weight,
		category: fonts[i].category
	}));*/
	/*
	[].forEach.call($names, function(item,i) {
		$labels[i] = item['Name'];
		console.log(item['Name'])
	});
	$names.forEach(function(element) {
		console.log(element);
	});

	Date: "2018:11:01"
	Name: "sessionrefresh"
	Time: "00:00:01"
	WeekDay: 5
	duration: 25


	var nums = [10, 20, 30, 40];
	var arr;
	var results = nums.map(function(num, index, arr) {

	 //   console.log(arr);
		return arr
	});
	console.log(results);
		arr['Name']: $names[i].Name,
		duration: $names[i].duration,
		WeekDay: $names[i].WeekDay,*/
	var arr =[];
	Object.values($names).map(function(arr, i) {
		 console.log(arr['Name']+', '+arr['duration']);
	   $labels[i]=arr['Name'];
		$durations[i]=arr['duration'];
	});
	//console.log($durations);
	var barChart = new Chart(popCanvas, {
		type: 'bar',
		data: {
			labels: $labels,
			datasets: [{
				label: 'Duration',
				data: $durations,
				backgroundColor: [
					'rgba(255, 99, 132, 0.6)',
					'rgba(54, 162, 235, 0.6)',
					'rgba(255, 206, 86, 0.6)',
					'rgba(75, 192, 192, 0.6)',
					'rgba(153, 102, 255, 0.6)',
					'rgba(255, 159, 64, 0.6)',
					'rgba(255, 99, 132, 0.6)',
					'rgba(54, 162, 235, 0.6)',
					'rgba(255, 206, 86, 0.6)',
					'rgba(75, 192, 192, 0.6)',
					'rgba(153, 102, 255, 0.6)'
				]
			}]
		}
	});
};

 function popChartTotal() {

	/*
	var popCanvas = $("#popChart");
	var popCanvas = document.getElementById("popChart").getContext("2d");
	*/
	var popCanvas = document.getElementById("popChartTotal");
	var $names = JSON.parse(popCanvas.getAttribute('data-names'));
	//$names['options'] = {};
	var $labels = [];
	var $durations= [];
	/*fonts = Object.keys(fonts).map(i => ({
		family: fonts[i].family,
		weight: fonts[i].weight,
		category: fonts[i].category
	}));*/
	/*
	[].forEach.call($names, function(item,i) {
		$labels[i] = item['Name'];
		console.log(item['Name'])
	});
	$names.forEach(function(element) {
		console.log(element);
	});

	Date: "2018:11:01"
	Name: "sessionrefresh"
	Time: "00:00:01"
	WeekDay: 5
	duration: 25


	console.log(results);
		arr['Name']: $names[i].Name,
		duration: $names[i].duration,
		WeekDay: $names[i].WeekDay,*/
	var $tooltips =[];
     Object.keys($names).map(function(key, i) {
         $labels[i] = key;
          $durations[i] = $names[key].Durations;
          $tooltips[i] = 'Success Requests: ' + $names[key].Success + '\r\nTotal Requests: ' + $names[key].Requests;
     //    console.log(key+', '+$names[key].Durations+', '+$tooltips[i]);
     });

/*
     Object.keys(fonts).map(i => ({
         family: fonts[i].family,
         weight: fonts[i].weight,
         category: fonts[i].category
     }));
*/
	//console.log($durations);
	var barChart = new Chart(popCanvas, {
		type: 'horizontalBar',
		data: {
			labels: $labels,
			datasets: [{
				label: 'Duration',
				data: $durations,
                tooltipItems: $tooltips,
				backgroundColor: [
					'#FF6384',
					'#00856A',
					'#D2691E',
					'#9966FF',
					'#6938a4',
					'#FF6384',
					'#6B8E23',
					'#008080',
					'#1FC75A',
					'#E9967A',
					'#9E5700',
					'#CD853F',
					'#4B0082',
					'#20B2AA',
					'#4BC0C0',
					'#6897bb',
					'#FF6384',
					'#2F4F4F',
					'#ffdab9',
					'#8A2BE2',
					'#FF00FF',
					'#EB0000',
					'#9966FF',
					'#9ACD32',
					'#e55120',
					'#CD5C5C',
					'#6938a4',
					'#FF9F40',
					'#556B2F',
					'#F08080',
					'#ffee84',
					'#0015D6',
					'#A0522D',
					'#D6005D',
					'#8B4513',
					'#FFCE56',
					'#483D8B',
					'#F4A460',
					'#36A2EB',
				],
			},
/*
				{
                label: 'tooltipItem',
                data: $tooltips,
                fill: false,
            }
*/
            ],

        },
        options: {
         //   maintainAspectRatio: false,
            tooltips: {
                /*
                                    callbacks: {
                                        label: function(tooltipItem, data) {
                                            console.log(data)
                                            var label = data.datasets[tooltipItem.datasetIndex].label || '';
                console.log(data.datasets)
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += Math.round(tooltipItem.yLabel * 100) / 100;
                                            return label;
                                        }
                                    },
                */
                cornerRadius: 10,
                caretSize: 10,
                xPadding: 10,
                yPadding: 12,
                backgroundColor: 'rgba(0, 0, 0, 0.7)',
                titleFontStyle: 'normal',
                titleMarginBottom: 15
            },
            legend: {
                display: true,
                //     position: 'bottom',
                labels: {
                    //    boxWidth: 20,
                    fontColor: 'rgb(60, 180, 100)',
                    padding:0,
                    boxWidth:80,
                },
            },
            scales: {
                yAxes: [{
                    barPercentage: 1,
                //    barThickness: 60,
                    gridLines: {
						display: true,
						zeroLineColor: "black",
						zeroLineWidth: 1,
                    },
                    beginAtZero: true,
                    ticks: {
                        autoSkip: false
                    },
					scaleLabel: {
						display: true,
						labelString: "type of Request"
					},


				}],
                xAxes: [{
                    gridLines: {
                        zeroLineColor: "black",
                        zeroLineWidth: 1,
						display: true
                    },
                    scaleLabel: {
                        display: true,
                        labelString: "total of Durations"
                    },
                    beginAtZero: true,
                    ticks: {
                        autoSkip: false
                    }
                }]
            },
        }
	});

 };

// Define a plugin to provide data labels
Chart.plugins.register({
	afterDatasetsDraw: function(chart) {
		var ctx = chart.ctx;

		chart.data.datasets.forEach(function(dataset, i) {
			var meta = chart.getDatasetMeta(i);
			if (!meta.hidden) {
				meta.data.forEach(function(element, index) {
					// Draw the text in black, with the specified font
				//	ctx.fillStyle = 'rgb(7, 7, 7)';
					ctx.strokeStyle = dataset.backgroundColor[index];
					ctx.fillStyle = dataset.backgroundColor[index];
				//	console.log(getRgba(ctx.fillStyle));

					var fontSize = 14;
					var lineHeight = 1.2;
					var fontStyle = 'normal';
					var fontFamily = 'inherit';
					ctx.font = Chart.helpers.fontString(fontSize, lineHeight, fontStyle, fontFamily);

					// Just naively convert to string for now
					var dataString = dataset.data[index].toString();

					// Make sure alignment settings are correct
					ctx.textAlign = 'left';
					ctx.textBaseline = 'middle';

					var padding = 10;
					var position = element.tooltipPosition();
					ctx.fillText(dataString, position.x+padding, position.y +  (fontSize*lineHeight-fontSize)/2);
				});
			}
		});
	}
});

function hex2rgba(hex,opacity){
	hex = hex.slice(1,5);
	r = parseInt(hex.substring(0,2), 16);
	g = parseInt(hex.substring(2,4), 16);
	b = parseInt(hex.substring(4,6), 16);

	result = 'rgba('+r+','+g+','+b+','+opacity/100+')';
	return result;
}
