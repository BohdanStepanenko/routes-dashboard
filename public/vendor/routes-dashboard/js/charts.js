const totalChart = document.getElementById('totalChart');
const apiChart = document.getElementById('apiChart');
const otherChart = document.getElementById('otherChart');
const diffChart = document.getElementById('diffChart');
const missedChart = document.getElementById('missedChart');

var dataContainer = document.getElementById('total-chart-data');
var data = JSON.parse(dataContainer.getAttribute('data-php-variable'));

new Chart(totalChart, {
    type: 'pie',
    data: {
        labels: [
            'Api routes',
            'Missed routes',
            'Other routes',
            'Diff routes'
        ],
        datasets: [{
            label: 'Count',
            data: [data.api, data.missed, data.other, data.diff],
            backgroundColor: [
                '#6A6A6A',
                '#ED164E80',
                '#BDC3C7',
                '#ED164E80'
            ],
            hoverOffset: 4
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                titleFont: {
                    size: 20
                },
                bodyFont: {
                    size: 16
                },
                footerFont: {
                    size: 16
                }
            }
        }
    }
});

new Chart(apiChart, {
    type: 'pie',
    data: {
        datasets: [{
            data: [data.total - data.api, data.api],
            backgroundColor: [
                '#6A6A6A',
                '#BDC3C7'
            ],
            hoverOffset: 4
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false,
            },
            tooltip: false,
        }
    }
});

new Chart(otherChart, {
    type: 'pie',
    data: {
        datasets: [{
            data: [data.total - data.other, data.other],
            backgroundColor: [
                '#6A6A6A',
                '#BDC3C7'
            ],
            hoverOffset: 4
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false,
            },
            tooltip: false,
        }
    }
});

new Chart(diffChart, {
    type: 'pie',
    data: {
        datasets: [{
            data: [data.total - data.diff, data.diff],
            backgroundColor: [
                '#6A6A6A',
                '#ED164E80'
            ],
            hoverOffset: 4
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false,
            },
            tooltip: false,
        }
    }
});

new Chart(missedChart, {
    type: 'pie',
    data: {
        datasets: [{
            data: [data.total - data.missed, data.missed],
            backgroundColor: [
                '#6A6A6A',
                '#ED164E80'
            ],
            hoverOffset: 4
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false,
            },
            tooltip: false,
        }
    }
});
