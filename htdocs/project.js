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

})()