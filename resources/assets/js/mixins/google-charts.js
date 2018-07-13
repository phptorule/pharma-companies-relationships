const googleCharts = {

    methods: {
        loadGoogleChartCore: function () {
            return google.charts.load('current', {'packages': ['corechart']})
        },
    }

};

export default googleCharts;