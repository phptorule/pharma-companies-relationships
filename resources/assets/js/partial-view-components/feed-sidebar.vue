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
                            <a href="javascript:void(0)">
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
                    The body of the box
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
                    The body of the box
                </div>
                <!-- /.box-body -->
            </div>
        </div>

    </div>
</template>

<script>
    export default {

    }
</script>

<style scoped>

</style>