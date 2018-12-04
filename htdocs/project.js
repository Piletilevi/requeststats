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
var $backgroundColors=[
    '#ff2f65',
    '#00856A',
    '#f04e00',
    '#9966FF',
    '#1FC75A',
    '#1abadc',
    '#ff3647',
    '#008080',
    '#FF00FF',
    '#e9721b',
    '#cd7a00',
    '#6900ac',
    '#20B2AA',
    '#2666e7',
    '#FF6384',
    '#00b940',
    '#ff862f',
    '#8A2BE2',
    '#CD853F',
    '#EB0000',
    '#9966FF',
    '#9ACD32',
    '#e55120',
    '#CD5C5C',
    '#6938a4',
    '#85b32e',
    '#FF9F40',
    '#F08080',
    '#afa423',
    '#0015D6',
    '#A0522D',
    '#4BC0C0',
    '#D6005D',
    '#ffb948',
    '#483D8B',
    '#529fff',
    '#556B2F',
    '#00ebab',
    '#b73f16',
];

//console.log($bgColors)
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
	var count =0;
     Object.keys($names).map(function(key, i) {
         $labels[i] = key;
          $durations[i] = $names[key].Durations;
          $tooltips[i] = 'Success Requests: ' + $names[key].Success + '\r\nTotal Requests: ' + $names[key].Requests;
     //    console.log(key+', '+$names[key].Durations+', '+$tooltips[i]);
         count =i;
     });
     var durationMax = Math.max.apply(null, $durations);
     var $backgroundColorsRGBA=[];
     $backgroundColors.forEach(function (bgColor, i) {
         if(i<=count){
             $backgroundColorsRGBA[i] = hex2rgba(bgColor, 70);
         }
     });
     var leftSpace=100;
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
title:"total of Durations",
                data: $durations,
                tooltipItems: $tooltips,
                backgroundColor: $backgroundColorsRGBA,
                /*
                               {
                                label: 'tooltipItem',
                                data: $tooltips,
                                fill: false,
                            }
                */
            }],

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
                position: 'top',//
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
                        color: "#ffffff",
                        display: true,
						zeroLineColor: "#000000",
						zeroLineWidth: 1,
                        borderDash: [2, 5],
                    },
                    beginAtZero: true,
                    ticks: {
                        autoSkip: false,
                        padding: leftSpace,
                    },
					scaleLabel: {
						display: true,
						labelString: "type of Request"
					},


				}],
                xAxes: [{
                    barPercentage: 0.6,
                    gridLines: {
                        color: "#e2e2e2",
                        lineWidth:1,
                        zeroLineColor: "#000000",
                        zeroLineWidth: 1,
                        display: true,
                        borderDash: [3, 8],
                    },
                    scaleLabel: {
                        display: true,
                        labelString: "total of Durations",
                    },
                    beginAtZero: true,
                    ticks: {
                        autoSkip: false,
                        min: 0,
                        max: durationMax,
                    //    stepSize: durationMax/count,
                    },

                }]
            },
            elements: {
                rectangle: {
                    borderSkipped: 'left',
                },
                line: {
                    fill: false
                }
            },
            plugins: {
                legend: false,
                //    title: false
            },
            title: {
                display: true,
                text: 'total of Durations'
            },
/*
*/
        }
	});

 };

// Define a plugin to provide data labels
Chart.plugins.register({
	beforeDatasetsDraw: function(chart) {
		var ctx = chart.ctx;

		chart.data.datasets.forEach(function(dataset, i) {
			var meta = chart.getDatasetMeta(i);
			if (!meta.hidden) {
				meta.data.forEach(function(element, index) {
					// Draw the text in black, with the specified font
					ctx.strokeStyle = $backgroundColors[index];
					ctx.fillStyle = $backgroundColors[index];

					var fontSize = 14;
					var lineHeight = 1.2;
					var fontStyle = 'normal';
					var fontFamily = 'inherit';
					ctx.font = Chart.helpers.fontString(fontSize, lineHeight, fontStyle, fontFamily);

					// Just naively convert to string for now
					var dataString = dataset.data[index].toString();

					// Make sure alignment settings are correct
				//	ctx.textAlign = 'left';
					ctx.textAlign = 'end';
					ctx.textBaseline = 'middle';

					var padding = 10;
					var position = element.tooltipPosition();
					ctx.fillText(dataString, 200, position.y +  (fontSize*lineHeight-fontSize)/2);
				});
			}
		});
	}
});

function hex2rgba(hex,opacity=100) {
    hex=hex.slice(1);
    return 'rgba(' +
        parseInt(hex.slice(0,2),16) + ',' +
        parseInt(hex.slice(2,4),16) + ',' +
        parseInt(hex.slice(4),16) + ',' + opacity/100 + ')';
}