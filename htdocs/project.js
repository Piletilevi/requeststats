var isChartRendered = false;

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
		params.options.animation = {
            onComplete: function() {
                isChartRendered = true
            }
        };

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

})();

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
 function popChartTotalDurationsRequests(bar) {

	 var popCanvas = document.getElementById("popChartTotal");
	 var $names = JSON.parse(popCanvas.getAttribute('data-names'));
	 var $labels = [];
	 var $durations = [];
	 var $durationsSuccess = [];
	 var $durationsFail = [];
	 var $counts = [];
	 var $countsSuccess = [];
	 var $countsFail = [];
	 var $tooltips = [];//Durations, durationsSuccess, Counts, countsSuccess, requestName
	 var rows = 0;
	 var leftSpace = 300;
	 var barheight = 24;
     let space = ' ';
	 Object.keys($names).map(function(key, i) {
		 $tooltips[i] = [];
		 $labels[i] = key;
		 $durations[i] = $names[key].Durations;
		 $durationsSuccess[i] = $names[key].durationsSuccess;
		 $durationsFail[i] = $names[key].Durations - $names[key].durationsSuccess;
         $counts[i] = $names[key].Counts;
         $countsSuccess[i] = $names[key].countsSuccess;
		 $countsFail[i] = $names[key].Counts - $names[key].countsSuccess;
		 //formatter_dec_0.format(dataString);
         if (bar==='duration'){
             $tooltips[i]['total'] = 'Total Requests:  ' + formatter_dec_0.format($names[key].Counts);
             $tooltips[i]['success'] = 'Success Requests:  ' + formatter_dec_0.format($names[key].countsSuccess);
             $tooltips[i]['fail'] = 'Failed Requests:  ' + formatter_dec_0.format((parseInt($names[key].Counts) - parseInt($names[key].countsSuccess)));
         }
         else{
             $tooltips[i]['total'] = 'Total Requests:  ' + formatter_dec_0.format($names[key].Durations);
             $tooltips[i]['success'] = 'Success Requests:  ' + formatter_dec_0.format($names[key].durationsSuccess);
             $tooltips[i]['fail'] = 'Failed Requests:  ' + formatter_dec_0.format((parseInt($names[key].Durations) - parseInt($names[key].durationsSuccess)));
         }
		 rows = i + 1;
	 });
     var durationMax = Math.max.apply(null, $durations);
     var countMax = Math.max.apply(null, $counts);
     var $backgroundColorsRGBA=[];

	 var $opacityBG = [];
	 $opacityBG['total'] = 60;
	 $opacityBG['success'] = 90;
	 $opacityBG['fail'] = 25;

	 Object.keys($opacityBG).map(function(key, i){
		// console.log(key) ;
		// console.log($opacityBG[key]) ;
		 $backgroundColorsRGBA[key] = [];
		 $backgroundColors.forEach(function (bgColor, j) {
			 if(j<=rows){
				 $backgroundColorsRGBA[key][j] = hex2rgba(bgColor, $opacityBG[key]);
			 }
		 });
	 });
     Object.assign(
         Chart.defaults.global, {
             maintainAspectRatio: false,
         //    responsive: true
         }
     );

	 Chart.defaults.global.defaultFontFamily = 'Encode Sans Condensed';
	 Chart.defaults.global.defaultFontSize = 12;

	 Chart.Legend.prototype.afterFit = function() {
		 this.height = this.height + 50;
	 };
	 // Define a plugin to provide data labels
	 Chart.plugins.register({
		 beforeDatasetsDraw: function(chart) {
			 var ctx = chart.ctx;

             var textColor = [];
             textColor['success'] = '#fff';
             textColor['fail'] = '#093342';
             var dataStringTotal =[];
             var fontSize = 12;
             var lineHeight = 1;
             var fontStyle = 'normal';
             var fontFamily = 'Encode Sans Condensed';//font: helpers.fontString(size, style, family)
             var position;
             ctx.textAlign = 'end';
             ctx.textBaseline = 'middle';
             var padding = 10;

			 chart.data.datasets.forEach(function(dataset, i) {
              //   console.log(dataset.category)
				 var meta = chart.getDatasetMeta(i);
                 if (!meta.hidden) {
					 meta.data.forEach(function(element, index) {
						 // Draw the text in black, with the specified font
						 ctx.fillStyle = $backgroundColorsRGBA[dataset.category][index];
                         textColor['total'] = $backgroundColors[index];
						 ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);
						 // 	 ctx.font = Chart.helpers.fontString(fontSize, lineHeight, fontStyle);

						 // Just naively convert to string for now
						 var dataString = dataset.data[index].toString();
						 dataString = formatter_dec_0.format(dataString);

						 // Make sure alignment settings are correct
						 //	ctx.textAlign = 'left';
						 position = element.tooltipPosition();
                         ctx.fillRect(280+i*80, position.y-12, 80, barheight);
                         ctx.fillStyle = textColor[dataset.category];
                         ctx.strokeStyle = textColor[dataset.category];
                      //   ctx.font = 'italic 30px sans-serif';
						 ctx.fillText(dataString, 350+i*80, position.y +  (fontSize*lineHeight-fontSize)/2);
                         ctx.fillStyle = textColor['total'];
                         ctx.strokeStyle = textColor['total'];
                         if (dataStringTotal[index] !== 'is'){
                             if (bar==='duration') {
                                 dataStringTotal[index] = formatter_dec_0.format($durations[index]);
                             }
                             else {
                                 dataStringTotal[index] = formatter_dec_0.format($counts[index]);
                             }
                             ctx.fillText(dataStringTotal[index]+' =', 260+i*80, position.y +  (fontSize*lineHeight-fontSize)/2);
                             dataStringTotal[index] = 'is';
                         }
					 });

                 }
			 });
		 }
	 });

var $dataSuccess;
var $dataFail;
var $labelSuffix;

     if (bar==='duration') {
         $dataSuccess = $durationsSuccess;
         $dataFail = $durationsFail;
         $labelSuffix = 'Durations';
     }
     else {
         $dataSuccess = $countsSuccess;
         $dataFail = $countsFail;
         $labelSuffix = 'Requests';
     }
	 var barChart = new Chart(popCanvas, {
         type: 'horizontalBar',
         data: {
             labels: $labels,
             datasets: [
/*
				 {
				 	category: 'total',
					 label: "total of Durations",
					 data: $durations,
					 backgroundColor: $backgroundColorsRGBA['total'],
					 borderWidth: [0, 0, 0, 0],
					 //	borderWidth: 0.5,
					 hidden: true,
				 },
*/
				 {
					 category: 'success',
					 label: "total of Success "+$labelSuffix,
					 data: $dataSuccess,
					 backgroundColor: $backgroundColorsRGBA['success'],
					 borderWidth: [0, 0, 0, 0],
				 },
				 {
					 category: 'fail',
					 label: "total of Failed "+$labelSuffix,
					 data: $dataFail,
					 backgroundColor: $backgroundColorsRGBA['fail'],
					 borderWidth: [0, 0, 0, 0],
				 },
             ],

         },
         options: {
             //   maintainAspectRatio: false,
             tooltips: {
				 mode: 'index',
                 callbacks: {
                     label: function (t, d) {
                     	let cat = d.datasets[t.datasetIndex].category;
                         if (cat === 'success') {
                             return space+ $tooltips[t.index]['success'];
                         } else if (cat === 'fail') {
                             return space+ $tooltips[t.index]['fail'];
                         } else if (cat === 'total') {
                             return space+ $tooltips[t.index]['total'];
                         }
                     },
					 footer:  function(t, d) {
						 return $tooltips[t[0].index]['total'] // data.labels[tooltipItems.index] ;
					 },
                 },
                 cornerRadius: 10,
                 caretSize: 10,
                 xPadding: 10,
                 yPadding: 12,
                 backgroundColor: 'rgba(0, 0, 0, 0.7)',
                 titleFontSize:14,
                 titleFontStyle: '400',
                 titleMarginBottom: 15,
            //   intersect: false,
			//	 axis: 'x',
				 bodyFontFamily:"'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
			//	 bodyFontSize:12,
				 bodySpacing: 6,
			//	 bodyFontStyle: '600',
				 footerFontSize:14,
				 footerFontStyle: '400',
             },

             legend: {
                 display: true,
                 position: 'top',//

				 labels: {
                     usePointStyle:true,
                     //    boxWidth: 20,
                     fontColor: '#093342',
                     fontSize: 16,
                     padding: 30,
                 },
                 onHover: function(e) {
                     e.target.style.cursor = 'pointer';
                 }
             },
             hover: {
                 onHover: function(e) {
                     var point = this.getElementAtEvent(e);
                     if (point.length) e.target.style.cursor = 'pointer';
                     else e.target.style.cursor = 'default';
                 }
             },
             scales: {
                 yAxes: [{
                     categoryPercentage: 1,
                     barPercentage: 1,
                     barThickness: barheight,
                     //   barThickness: 20,
					 stacked: true,
                     gridLines: {
                         color: "#ffffff",
                         display: true,
                         lineWidth: .5,
                         zeroLineColor: "#5e7287",
                         zeroLineWidth: 0,
                         //    borderDash: [2, 5],
                     },
                     beginAtZero: true,
                     ticks: {
                         autoSkip: false,
                         padding: leftSpace,
						 fontSize: 14,
					 },
                     scaleLabel: {
                         display: true,
                         labelString: "type of Request",
                         fontColor: "#5e7287",
						 fontSize: 16,
                     },
                 }],
                 xAxes: [{
                     categoryPercentage: 1.0,
                     barPercentage: 1.0,
                     barThickness: 'flex',
					 stacked: true,
					 gridLines: {
                         color: hex2rgba("#5e7287",50),
                         display: true,
                         lineWidth: .2,
                         borderDash: [5, 5],
                         zeroLineColor: "#5e7287",
                         zeroLineWidth: .25,
                     },
                     scaleLabel: {
                         display: true,
                         labelString: "total of "+$labelSuffix,
                         fontColor: "#5e7287",
						 fontSize: 16,
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
                     borderSkipped: ['left', 'right', 'top', 'bottom'],
                 },
                 line: {
                     fill: false
                 }
             },
             plugins: {
                 //legend: false,
                 //    title: false
             },
             title: {
                 display: true,
                 text: "total of "+$labelSuffix,
                 fontColor: "#5e7287",
				 fontSize: 16,
             },
             animation: {
                 onComplete: function() {
                     isChartRendered = true
                 }
             }
         }
     });

}

function hex2rgba(hex,opacity=100) {
    hex=hex.slice(1);
    return 'rgba(' +
        parseInt(hex.slice(0,2),16) + ',' +
        parseInt(hex.slice(2,4),16) + ',' +
        parseInt(hex.slice(4),16) + ',' + opacity/100 + ')';
}

var currentLang = document.documentElement.getAttribute('lang');
var formatter_dec_0 = new Intl.NumberFormat(currentLang, {
	style: "decimal",
	useGrouping: true,
	//  currency: "EUR",
	//	currencyDisplay: "€",
	//	minimumFractionDigits: 2,	//ignored if exists one of SignificantDigits
	maximumFractionDigits: 0, //ignored if exists one of SignificantDigits
	//	maximumSignificantDigits: 20, // if in use, FractionDigits will be ignored
});

function download() {
    if (!isChartRendered) return; // return if chart not rendered
    html2canvas(document.getElementById('chart-container'), {
        onrendered: function(canvas) {
            var link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = 'pl-stat.png';
            link.click();
        }
    })
}