<template>
    <div class="form-horizontal date-range-picker-container">


        <div class="form-group">
            <label class="col-sm-2 date-picker-label control-label">From</label>
            <div class="col-sm-6 position-relative">
                <i class="fa fa-calendar"></i>
                <input type="text" class="date-range date-start">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 date-picker-label control-label">To</label>
            <div class="col-sm-6 position-relative">
                <i class="fa fa-calendar"></i>
                <input type="text" class="date-range date-end">
            </div>
        </div>

    </div>
</template>

<script>
    export default {

        data: function () {
            return {
                startDate: null,
                endDate: null,
                isStartDateInited: false,
                start_date: '',
                end_date: ''
            }
        },

        methods: {

            initStartDatePicker: function (setDate) {

                if(this.isStartDateInited) {
                    return
                }

                this.startDate.daterangepicker({
                    timePicker: false,
                    locale: {
                        format: 'DD MMM YYYY'
                    },
                    singleDatePicker: true,
                    startDate: setDate? setDate : moment().subtract(1, 'months')
                });

                this.initStartDateWatcher();

                this.isStartDateInited = true;

                this.start_date = setDate? setDate.format('YYYY-MM-DD') : moment().format('YYYY-MM-DD');
            },

            initEndDatePicker: function (dateToSet) {
                this.endDate.daterangepicker({
                    startDate: dateToSet,
                    minDate: dateToSet,
                    timePicker: false,
                    locale: {
                        format: 'DD MMM YYYY'
                    },
                    singleDatePicker: true,
                    ranges: {
                        startDate: dateToSet
                    }
                });

                this.initEndDateWatcher();

                this.end_date = dateToSet ? dateToSet.format('YYYY-MM-DD') : moment().add(1, 'days').format('YYYY-MM-DD');
            },

            initStartDateWatcher: function () {
                this.startDate.on('apply.daterangepicker', (ev, picker) => {

                    let startDate = moment((new Date(ev.target.value).getTime()));
                    this.start_date = startDate.format('YYYY-MM-DD');

                    let endDate = moment(startDate.add(1, 'days'));
                    this.end_date = endDate.format('YYYY-MM-DD');

                    this.initEndDatePicker(endDate);
                    this.initEndDateWatcher();
                })
            },

            initEndDateWatcher: function () {
                this.endDate.on('apply.daterangepicker', (ev, picker) => {
                    let endDate = moment((new Date(ev.target.value).getTime()));
                    this.end_date = endDate.format('YYYY-MM-DD');
                })
            },

        },

        mounted: function () {

            this.startDate = $('.date-range.date-start');
            this.endDate = $('.date-range.date-end');

            this.initStartDatePicker();
            this.initEndDatePicker();

        }
    }
</script>

<style scoped>

</style>