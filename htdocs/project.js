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
		type: 'bar',
		data: {
			labels: $labels,
			datasets: [{
				label: 'Duration',
				data: $durations,
                tooltipItems: $tooltips,
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
					'rgba(153, 102, 255, 0.6)',
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
					'rgba(153, 102, 255, 0.6)',
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
					'rgba(153, 102, 255, 0.6)',
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
					'rgba(153, 102, 255, 0.6)',
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
                    barThickness: 60,
                    gridLines: {
                        display: true
                    },
                    beginAtZero: true,
                    ticks: {
                        autoSkip: false
                    }

                }],
                xAxes: [{
                    gridLines: {
                        zeroLineColor: "black",
                        zeroLineWidth: 2,
						display: false
                    },
                    scaleLabel: {
                        display: true,
                        labelString: "type of Request"
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
