<template>
    <div class="lab-news-overview address-box">
        <div class="header">
            <div class="row">
                <div class="col-md-4">
                    <h3>Lab News
                        <a href="#" @click.prevent class="without-handler">
                            <i class="fa fa-plus"></i>
                        </a>
                    </h3>
                </div>
                <div class="col-md-8">
                    <ul class="news-links">
                        <li><a href="javascript:void(0)" class="active">All news</a></li>
                        <li><a href="javascript:void(0)">Saved</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-7 quick-news-box">

                <feed-item-simple-text
                        v-if="feed1.type === 'text'"
                        :feed="feed1"
                        @onShowFeedDetailsPopUp="showFeedDetailsPopUp"
                ></feed-item-simple-text>

                <feed-item-text-image
                        v-if="feed1.type === 'image'"
                        :feed="feed1"
                        @onShowFeedDetailsPopUp="showFeedDetailsPopUp"
                ></feed-item-text-image>

            </div>

            <div class="col-md-5 quick-news-box">

                <feed-item-simple-text
                        v-if="feed2.type === 'text'"
                        :feed="feed2"
                        @onShowFeedDetailsPopUp="showFeedDetailsPopUp"
                ></feed-item-simple-text>

                <feed-item-text-image
                        v-if="feed2.type === 'image'"
                        :feed="feed2"
                        @onShowFeedDetailsPopUp="showFeedDetailsPopUp"
                ></feed-item-text-image>

            </div>
        </div>

        <div class="text-right">
            <router-link to="/feed"
                         @click.prevent
                         class="address-box-show-more-link">
                Go to Lab News
            </router-link>
        </div>

    </div>
</template>

<script>

    var TEST_ITEMS = [
        {
            id: 2,
            type: 'text',
            tags: ['medical laboratory'],
            icon: 'organisation'
        },
        {
            id: 3,
            type: 'image',
            tags: ['medical SBO', 'CEO'],
            icon: 'people'
        },


    ];



    export default {

        components: {
            'feed-item-simple-text': require('./feed-item-simple-text'),
            'feed-item-text-image': require('./feed-item-text-image')
        },

        name: "quick-feed-box",

        data: function () {
            return {
                feed1: TEST_ITEMS[0],
                feed2: TEST_ITEMS[1],
            }
        },

        methods: {
            showFeedDetailsPopUp: function (feedId) {

                this.$eventGlobal.$emit('show-feed-details-pop-up', feedId);

                this.selectedFeedId = feedId;
            }
        }
    }
</script>

<style scoped>

</style>