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
 * Group report.
 *
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/chartjs', 'core_table/dynamic', 'core/ajax', 'core/log'], function(Chart, DynamicTable, Ajax, Log) {
    return {
        init: function(uniqueid, wwwroot, moodlePre4) {

            /**
             * Export as a CSV file.
             *
             * @param {Event} e
             */
            function exportCSV(e) {
                e.preventDefault();
                const users = document.querySelector('#chart').dataset.users;
                const cmid = document.getElementById('export-csv').dataset.cmid;
                location.href = wwwroot + "/mod/otopo/view.php?id=" + cmid + "&action=export&object=group&users=" + users
                    + '&sesskey=' + M.cfg.sesskey;
            }

            /**
             * Load the chart using Ajax.
             *
             * @param {Number} otopo
             * @param {Array<Number>} users
             * @param {Number} session
             * @returns {Promise}
             */
            async function loadAjaxChart(otopo, users, session) {
                var promises = Ajax.call([
                    {
                        methodname: 'mod_otopo_get_group_chart', args: {
                            otopo: otopo,
                            users: users,
                            session: session
                        }
                    }
                ]);
                const chart = await promises[0];

                return chart;
            }

            /**
             * Load the chart.
             *
             * @param {Number} otopo
             * @param {Array<Number>} users
             * @param {Number} session
             */
            function loadChart(otopo, users, session) {
                loadAjaxChart(otopo, users, session).then((chart) => {
                    if (chart) {
                        const config = {
                            data: chart,
                            options: {}
                        };
                        const legend = {
                            position: 'right',
                        };
                        const xScale = {
                            stacked: true,
                        };
                        const yScale = {
                            stacked: true,
                            ticks: {
                                beginAtZero: true,
                                min: 0,
                                max: 100,
                                stepSize: 10
                            }
                        };
                        if (moodlePre4) {
                            config.type = 'horizontalBar';
                            config.options.legend = legend;
                            config.options.scales = {
                                xAxes: [xScale],
                                yAxes: [yScale]
                            };
                            config.options.tooltips = {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        let label = data.datasets[tooltipItem.datasetIndex].label || '';
                                        label += ': ';
                                        label += data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || '0';
                                        label += '%';
                                        return label;
                                    }
                                }
                            };
                        } else {
                            config.type = 'bar';
                            config.options.indexAxis = 'y';
                            config.options.scales = {
                                x: xScale,
                                y: yScale
                            };
                            config.options.plugins = {};
                            config.options.plugins.legend = legend;
                            config.options.plugins.tooltip = {
                                callbacks: {
                                    label: function(context) {
                                        if (context) {
                                            let label = context.dataset.label || '';
                                            label += ': ';
                                            label += context.formattedValue || '0';
                                            label += '%';
                                            return label;
                                        }
                                        return null;
                                    }
                                }
                            };
                        }
                        new Chart(
                            document.getElementById('chart'),
                            config
                        );
                    }

                    return true;
                }).catch(Log.error);
            }

            const root = document.querySelector(`form[data-table-unique-id="${uniqueid}"]`);

            const otopo = root.dataset.otopo;
            const chart = document.getElementById('chart');
            if (chart) {
                const users = document.querySelector('#chart').dataset.users.split(',');
                const session = document.getElementById('session-selector')
                      && parseInt(document.getElementById('session-selector').value) ?
                      parseInt(document.getElementById('session-selector').value) : null;
                loadChart(otopo, users, session);
            }

            root.addEventListener(DynamicTable.Events.tableContentRefreshed, () => {
                const otopo = root.dataset.otopo;
                const chart = document.getElementById('chart');
                if (chart) {
                    const users = document.querySelector('#chart').dataset.users.split(',');
                    const session = document.getElementById('session-selector')
                          && parseInt(document.getElementById('session-selector').value) ?
                          parseInt(document.getElementById('session-selector').value) : null;
                    if (users) {
                        loadChart(otopo, users, session);
                    }
                }
            });

            var lastWidth = 0;
            var lastHeight = 0;
            window.onbeforeprint = () => {
                for (var id in Chart.instances) {
                    lastHeight = Chart.instances[id].canvas.parentNode.style.height;
                    lastWidth = Chart.instances[id].canvas.parentNode.style.width;
                    Chart.instances[id].canvas.parentNode.style.height = '1000px';
                    Chart.instances[id].canvas.parentNode.style.width = '850px';
                    Chart.instances[id].resize();
                }
            };
            window.addEventListener('afterprint', () => {
                for (var id in Chart.instances) {
                    Chart.instances[id].canvas.parentNode.style.height = lastHeight;
                    Chart.instances[id].canvas.parentNode.style.width = lastWidth;
                    Chart.instances[id].resize();
                }
            });

            const exportCSVBtn = document.getElementById('export-csv');
            if (exportCSVBtn) {
                exportCSVBtn.onclick = function(e) {
                    exportCSV(e);
                };
            }
        }
    };
});
