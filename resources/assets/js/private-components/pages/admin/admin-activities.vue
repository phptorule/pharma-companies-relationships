<template>
    <div class="admin-users">
        <div class="au-container">
            <div class="au-breadcrumbs">
                <ul class="au-breadcrumbs-list">
                    <li>Admin</li>
                    <li><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                    <li class="active">Activity</li>
                </ul>
            </div>

            <div id="activity-chart"></div>
        </div>
    </div>
</template>

<script>
    import http from '../../../mixins/http';
    import googleCharts from '../../../mixins/google-charts';

    export default {
        mixins: [http, googleCharts],

        data: function () {
            return {}
        },
        watch: {

        },
        methods: {
            loadTopUserActivities: function () {
                return this.httpGet('/api/admin/get-user-activities')
            },

            drawChart: function (data) {

                let testDATA = [
                    ['Date', 'User 1', 'User 2'],
                    ['02-07-2018', 22500, 20000],
                    ['03-07-2018', 35000, 30500],
                    ['04-07-2018', 44000, 40500],
                    ['05-07-2018', 27000, 20500],
                    ['06-07-2018', 92000, 80000],
                    ['07-07-2018', 18500, 10000]
                ];



                let options = {
                    width: 800,
                    height: 400,
                };

                let visualizationData = google.visualization.arrayToDataTable(testDATA, false);

                let chart = new google.visualization.LineChart(document.getElementById('activity-chart'));

                chart.draw(visualizationData, options);
            }
        },
        mounted: function () {
            this.loadTopUserActivities()
                .then(data => {

                    this.loadGoogleChartCore()
                        .then(()=>{
                            this.drawChart(data);
                        });

                });
        }
    }
</script>

<style scoped>

</style>

