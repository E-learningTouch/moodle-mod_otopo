// This file is part of Moodle - http://moodle.org/
// ... [Licensing and documentation comments]

define([
    'core/notification',
    'core/templates',
    'core/ajax',
    'core/chartjs',
    'core/log'
], function(
    Notification,
    Templates,
    Ajax,
    Chart,
    Log
) {
    return {
        init: function(otopo, degreeStr) {

            /**
             * Load the evolution using Ajax.
             *
             * @param {Number} otopo
             * @param {String} visual
             * @param {Number} item
             * @returns {Promise}
             */
            async function loadAjaxEvolution(otopo, visual, item) {
                var promises = Ajax.call([
                    {
                        methodname: 'mod_otopo_get_my_evolution',
                        args: {
                            otopo: otopo,
                            visual: visual,
                            item: item
                        }
                    }
                ]);
                const chart = await promises[0];

                return chart;
            }

            /**
             * Reload the page.
             *
             * @param {Number} otopo
             */
            function reloadPage(otopo) {
                const visual = document.getElementById('id_visual').value;
                const item = parseInt(document.getElementById('id_item').value);

                loadAjaxEvolution(parseInt(otopo), visual, item).then((data) => {
                    // Vérifiez et modifiez data.max si nécessaire
                    // data.max = 5; // Décommentez si vous souhaitez définir manuellement

                    // Limiter les données pour qu'elles ne dépassent pas data.max
                    const limitData = (dataset) => {
                        dataset.data = dataset.data.map(value => Math.min(value, data.max));
                        return dataset;
                    };

                    if (data.currentchart && data.currentchart.datasets) {
                        data.currentchart.datasets = data.currentchart.datasets.map(limitData);
                    }

                    if (data.charts && data.charts.length > 0) {
                        data.charts.forEach(chart => {
                            if (chart.datasets) {
                                chart.datasets = chart.datasets.map(limitData);
                            }
                        });
                    }

                    if (data.chartitem && data.chartitem.datasets) {
                        data.chartitem.datasets = data.chartitem.datasets.map(limitData);
                    }

                    data.hascharts = data.charts.length > 0;
                    data.currentchart = data.currentchart.id ? data.currentchart : null;
                    data.chartitem = data.chartitem.id ? data.chartitem : null;
                    data.star = document.getElementById('evolution').dataset.star;

                    Templates.renderForPromise('mod_otopo/evol', data).then(({html, js}) => {
                        Templates.replaceNodeContents('#evolution', html, js);
                        let options;
                        if (visual === 'radar') {
                            options = {
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: data.max, // Définir max directement sur l'axe
                                        ticks: {
                                            stepSize: 1,
                                            callback: function(value) {
                                                if (value === 0) {
                                                    return "";
                                                }
                                                return degreeStr + " " + value;
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        displayColors: false,
                                        callbacks: {
                                            title: function(context) {
                                                if (context.length > 0 && context[0].label) {
                                                    return context[0].label;
                                                }
                                                return null;
                                            },
                                            label: function(context) {
                                                if (context) {
                                                    let label = context.formattedValue || '0';
                                                    label += context.dataset.labels[context.dataIndex] ?
                                                        (' - ' + context.dataset.labels[context.dataIndex]) : '';
                                                    return label;
                                                }
                                                return null;
                                            }
                                        }
                                    }
                                }
                            };
                        } else {
                            const yScale = {
                                beginAtZero: true,
                                min: 0,
                                max: data.max, // Définir max directement sur l'axe
                                ticks: {
                                    stepSize: 1,
                                    callback: function(value) {
                                        if (value === 0) {
                                            return "";
                                        }
                                        return degreeStr + " " + value;
                                    }
                                }
                            };
                            const legend = { display: false };
                            if (data.moodlePre4) {
                                options = {
                                    scales: {
                                        yAxes: [{
                                            ...yScale
                                        }]
                                    },
                                    legend: legend,
                                    plugins: {
                                        tooltip: {
                                            displayColors: false,
                                            callbacks: {
                                                title: function(tooltipItems, chart) {
                                                    return chart.fullLabels[tooltipItems[0].index];
                                                },
                                                label: function(tooltipItem, data) {
                                                    let tooltipItemFromdata = data.datasets[tooltipItem.datasetIndex];
                                                    let label = tooltipItemFromdata.data[tooltipItem.index] || '0';
                                                    label += tooltipItemFromdata.labels[tooltipItem.index]
                                                        ? (' - ' + tooltipItemFromdata.labels[tooltipItem.index])
                                                        : '';
                                                    return label;
                                                }
                                            }
                                        }
                                    }
                                };
                            } else {
                                options = {
                                    scales: {
                                        y: {
                                            ...yScale
                                        }
                                    },
                                    plugins: {
                                        legend: legend,
                                        tooltip: {
                                            displayColors: false,
                                            callbacks: {
                                                title: function(context) {
                                                    if (context.length > 0 && context[0].label) {
                                                        return context[0].label;
                                                    }
                                                    return null;
                                                },
                                                label: function(context) {
                                                    if (context) {
                                                        let label = context.formattedValue || '0';
                                                        label += context.dataset.labels[context.dataIndex] ?
                                                            (' - ' + context.dataset.labels[context.dataIndex]) : '';
                                                        return label;
                                                    }
                                                    return null;
                                                }
                                            }
                                        }
                                    }
                                };
                            }
                        }

                        const currentChartElement = document.getElementById('currentChart');
                        if (currentChartElement && data.currentchart) {
                            const config = {
                                type: visual,
                                data: data.currentchart,
                                options: options
                            };
                            new Chart(
                                currentChartElement,
                                config
                            );
                        }

                        data.charts.forEach(function(chart) {
                            const chartElement = document.getElementById('chart' + chart.id);
                            if (chartElement) {
                                const config = {
                                    type: visual,
                                    data: chart,
                                    options: options
                                };
                                new Chart(
                                    chartElement,
                                    config
                                );
                            }
                        });

                        const chartItemElement = document.getElementById('chartItem');
                        if (chartItemElement && data.chartitem) {
                            const config = {
                                type: visual,
                                data: data.chartitem,
                                options: options
                            };
                            new Chart(
                                chartItemElement,
                                config
                            );
                        }
                        return true;
                    }).catch(Notification.exception);
                    return true;
                }).catch(Log.error);
            }

            document.getElementById('id_visual').onchange = function() {
                reloadPage(otopo);
            };
            document.getElementById('id_item').onchange = function() {
                reloadPage(otopo);
            };

            reloadPage(otopo);

            var lastWidth = 0;
            var lastHeight = 0;
            window.onbeforeprint = function() {
                for (var id in Chart.instances) {
                    lastHeight = Chart.instances[id].canvas.parentNode.style.height;
                    lastWidth = Chart.instances[id].canvas.parentNode.style.width;
                    Chart.instances[id].canvas.parentNode.style.height = '850px';
                    Chart.instances[id].canvas.parentNode.style.width = '850px';
                    Chart.instances[id].resize();
                }
            };
            window.addEventListener('afterprint', function() {
                for (var id in Chart.instances) {
                    Chart.instances[id].canvas.parentNode.style.height = lastHeight;
                    Chart.instances[id].canvas.parentNode.style.width = lastWidth;
                    Chart.instances[id].resize();
                }
            });
        }
    };
});
