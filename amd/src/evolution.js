// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Evolution.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
                    data.hascharts = data.charts.length > 0;
                    data.currentchart = data.currentchart.id ? data.currentchart : null;
                    data.chartitem = data.chartitem.id ? data.chartitem : null;
                    data.star = document.getElementById('evolution').dataset.star;

                    Templates.renderForPromise('mod_otopo/evol', data).then(({html, js}) => {
                        Templates.replaceNodeContents('#evolution', html, js);
                        let options;
                        if (visual == 'radar') {
                            options = {
                                scale: {
                                    ticks: {
                                        min: 0,
                                        max: data.max,
                                        stepSize: 1
                                    }
                                },
                                scales: {
                                    r: {
                                        beginAtZero: true
                                    }
                                }
                            };
                        } else {
                            const yScale = {
                                ticks: {
                                    beginAtZero: true,
                                    min: 0,
                                    max: data.max,
                                    stepSize: 1,
                                    callbacks: function(value) {
                                        if (value === 0) {
                                            return "";
                                        }
                                        return degreeStr + " " + value;
                                    }
                                }
                            };
                            const legend = {display: false};
                            if (data.moodlePre4) {
                                options = {
                                    scales: {
                                        yAxes: [yScale]
                                    }
                                };
                                options.legend = legend;
                            } else {
                                options = {
                                    scales: {
                                        y: yScale
                                    }
                                };
                                options.plugins = {};
                                options.plugins.legend = legend;
                            }
                        }
                        if (data.moodlePre4) {
                            options.tooltips = {
                                displayColors: false,
                                callbacks: {
                                    title: function(tooltipItems, chart) {
                                        return chart.fullLabels[tooltipItems[0].index];
                                    },
                                    label: function(tooltipItem, data) {
                                        let label = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || '0';
                                        label += data.datasets[tooltipItem.datasetIndex].labels[tooltipItem.index] ?
                                            (' - ' + data.datasets[tooltipItem.datasetIndex].labels[tooltipItem.index]) : '';
                                        return label;
                                    }
                                }
                            };
                        } else {
                            if (!options.plugins) {
                                options.plugins = {};
                            }
                            options.plugins.tooltip = {
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
                            };
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
                            if (data.moodlePre4) {
                                options.tooltips = {
                                    displayColors: false,
                                    callbacks: {
                                        label: function(tooltipItem, data) {
                                            var label = data.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || '0';
                                            label += data.fullLabels[tooltipItem.index] ?
                                                (' - ' + data.fullLabels[tooltipItem.index]) : '';
                                            return label;
                                        }
                                    }
                                };
                            } else {
                                if (!options.plugins) {
                                    options.plugins = {};
                                }
                                options.plugins.tooltip = {
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
                                                label += ': ';
                                                label += context.dataset.label ? context.dataset.label : '';
                                                return label;
                                            }
                                            return null;
                                        }
                                    }
                                };
                            }
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
