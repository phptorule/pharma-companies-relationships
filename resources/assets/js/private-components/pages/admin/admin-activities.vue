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

                let options = {
                    width: $('#activity-chart').width(),
                    height: 400,
                    hAxis: {
                        direction: '-1'
                    },
                };

                let visualizationData = google.visualization.arrayToDataTable(data, false);

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

