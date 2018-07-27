<template>
    <div class="feed-sidebar">

        <div class="search-input-container">

            <div class="search-input-box">
                <i class="fa fa-search"></i>

                <input type="text" class="search-input" placeholder="Free text search">
            </div>

        </div>

        <div class="feed-sidebar-tabs">
            <ul class="ul-feed-sidebar-tabs">
                <li>
                    <a href="javascript:void(0)" class="active">
                        All
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        Saved
                    </a>
                </li>
            </ul>
        </div>

        <div class="filter-container">

            <div class="box box-default">
                <div class="box-header ">
                    <h3 class="box-title">Sort by</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                    </div>
                    <!-- /.box-tools -->
                </div>
                <!-- /.box-header -->
                <div class="box-body">

                    <ul class="ul-sort-by">
                        <li>
                            <a href="javascript:void(0)" :class="{active: true}">
                                <span class="sort-by-icon">
                                    <img src="/images/organisation.svg" class="home">
                                </span>
                                Organisations
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0)">
                                <span class="sort-by-icon">
                                    <img src="/images/man.svg" alt="">
                                </span>
                                People
                            </a>
                        </li>
                    </ul>

                </div>
                <!-- /.box-body -->
            </div>

            <div class="box box-default">
                <div class="box-header ">
                    <h3 class="box-title">Type</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                    </div>
                    <!-- /.box-tools -->
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <ul class="ul-sort-by ul-type">
                        <li>
                            <div class="grey-checkbox checkbox-margin-top">
                                <label>
                                    <input type="checkbox" id="signatories">
                                    <span class="borders"></span>
                                    <span class="remember_text">Signatories</span>
                                </label>
                            </div>
                        </li>

                        <li>
                            <div class="grey-checkbox checkbox-margin-top">
                                <label>
                                    <input type="checkbox" id="platform-activity">
                                    <span class="borders"></span>
                                    <span class="remember_text">Platform activity</span>
                                </label>
                            </div>
                        </li>

                        <li>
                            <div class="grey-checkbox checkbox-margin-top">
                                <label>
                                    <input type="checkbox" id="remember-me">
                                    <span class="borders"></span>
                                    <span class="remember_text">Paper publications</span>
                                </label>
                            </div>
                        </li>

                        <li>
                            <div class="grey-checkbox checkbox-margin-top">
                                <label>
                                    <input type="checkbox" id="address-change">
                                    <span class="borders"></span>
                                    <span class="remember_text">Address Change</span>
                                </label>
                            </div>
                        </li>

                        <li>
                            <div class="grey-checkbox checkbox-margin-top">
                                <label>
                                    <input type="checkbox" id="new-organisations">
                                    <span class="borders"></span>
                                    <span class="remember_text">New organisations</span>
                                </label>
                            </div>
                        </li>

                        <li>
                            <div class="grey-checkbox checkbox-margin-top">
                                <label>
                                    <input type="checkbox" id="new-hires">
                                    <span class="borders"></span>
                                    <span class="remember_text">New hires</span>
                                </label>
                            </div>
                        </li>

                    </ul>
                </div>
                <!-- /.box-body -->
            </div>

            <div class="box box-default">
                <div class="box-header ">
                    <h3 class="box-title">Range</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                    </div>
                    <!-- /.box-tools -->
                </div>
                <!-- /.box-header -->
                <div class="box-body">

                    <date-range-picker></date-range-picker>

                </div>
                <!-- /.box-body -->
            </div>
        </div>

    </div>
</template>

<script>

    import dateRangePicker from './date-range-picker';

    import helpers from '../mixins/helpers'

    export default {

        components : {
            'date-range-picker': dateRangePicker
        },

        mixins: [helpers],

        mounted: function () {

            this.enableBoxCollapsing();
        }

    }
</script>

<style scoped>

</style>