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

            <h2 class="text-center">User Activity</h2>

            <div class="filter-container">
                <div class="row">

                    <form class="form-horizontal">
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-4 col-md-2 control-label">Period: </label>

                                <div class="col-sm-2 col-md-2">
                                    <select v-model="interval" class="form-control" @change="applyFilter()">
                                        <option value="5">5 days</option>
                                        <option value="10">10 days</option>
                                        <option value="20">20 days</option>
                                        <option value="30">30 days</option>
                                        <option value="60">2 month</option>
                                        <option value="182">6 month</option>
                                        <option value="365">1 year</option>
                                    </select>
                                </div>



                                <label class="col-sm-4 col-md-2 control-label">Number of most active users:</label>

                                <div class="col-sm-2 col-md-2">
                                    <select v-model="userNumber" class="form-control" @change="applyFilter()">
                                        <option value="1">1</option>
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="30">30</option>
                                        <option value="60">60</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </form>

                </div>
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
            return {
                interval: 5,
                userNumber: 5,
            }
        },
        watch: {

        },
        methods: {

            applyFilter: function() {
                this.loadTopUserActivities()
                    .then(data => {
                        this.drawChart(data);
                    });
            },

            loadTopUserActivities: function () {
                let url = '/api/admin/get-user-activities?interval='+this.interval+'&user_number='+this.userNumber;

                return this.httpGet(url)
            },

            drawChart: function (data) {

                let options = {
                    width: $('#activity-chart').width(),
                    height: 400,
                    colors: this.GOOGLE_CHART_DEFAULT_COLORS,
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

