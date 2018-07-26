<template>
    <div class="box feed-item-simple-text">
        <div class="box-header with-border">

            <div class="news-icon-type pull-right">
                <img src="/images/man.svg" alt="">
            </div>

            <h3 class="box-title">
                Found on <strong>30 Jun 2018</strong>
            </h3>

        </div>

        <div class="box-body">

            <h3>Dr. Michael Feldman</h3>

            <p>
                Published a new article with
                <a href="javascript:void(0)">Patrick de Boer</a>
                and
                <a href="javascript:void(0)">3 others</a>
            </p>

            <p class="image-box">
                <a href="javascript:void(0)">
                    <img src="/images/test-research.jpg" alt="">
                </a>
            </p>

            <p>
                <a href="javascript:void(0)">
                    Detecting d-waves super driller hemo-waves in [...]
                </a>
            </p>

            <div v-if="feed && feed.tags && feed.tags.length"
                 style=" margin-top: 10px;"
            >
                <tag-list :tags="feed.tags"></tag-list>
            </div>

        </div>


        <div class="box-footer">
            <div class="row">
                <div class="col-md-4">
                    <a href="javascript:void(0)">
                        <i class="fa fa-comment"></i>
                        Comment
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="javascript:void(0)">
                        <i class="fa fa-floppy-o"></i>
                        Save
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="javascript:void(0)">
                        <i class="fa fa-share"></i>
                        Share
                    </a>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        props: ['feed']
    }
</script>

<style scoped>

</style>