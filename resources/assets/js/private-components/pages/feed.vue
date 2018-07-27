<template>
    <div>
        <feed-sidebar></feed-sidebar>

        <div class="feed-content">

            <div class="row date-separated-feed-item">

                <div class="col-md-12">
                    <div class="date-title">
                        <h2>Jun 2018 - Aug 2018</h2>
                    </div>
                </div>

                <div class="col-md-4" v-for="feed of feeds">

                    <feed-item-simple-text
                            v-if="feed.type === 'text'"
                            :feed="feed"
                            @onShowFeedDetailsPopUp="showFeedDetailsPopUp"
                    ></feed-item-simple-text>

                    <feed-item-text-image
                            v-if="feed.type === 'image'"
                            :feed="feed"
                            @onShowFeedDetailsPopUp="showFeedDetailsPopUp"
                    ></feed-item-text-image>

                </div>


            </div>

        </div>

        <feed-details-pop-up
                :feedId="selectedFeedId"
        ></feed-details-pop-up>
    </div>
</template>

<script>

    var TEST_ITEMS = [
        {
            id: 1,
            type: 'text',
            icon: 'people'
        },
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
        {
            id: 4,
            type: 'image',
            tags: ['medical SBO', 'CEO'],
            icon: 'people'
        },
        {
            id: 5,
            type: 'image',
            tags: ['medical SBO', 'CEO'],
            icon: 'people'
        },
        {
            id: 6,
            type: 'image',
            tags: ['medical SBO', 'CEO'],
            icon: 'people'
        },
        {
            id: 7,
            type: 'text',
            tags: ['medical laboratory'],
            icon: 'organisation'
        },

    ];

    export default {

        components: {
            'feed-item-simple-text': require('../../partial-view-components/feed-item-simple-text'),
            'feed-item-text-image': require('../../partial-view-components/feed-item-text-image'),
            'feed-details-pop-up': require('../../partial-view-components/feed-details-pop-up')
        },

        data: function () {
            return {
                feeds: TEST_ITEMS,
                selectedFeedId: null,
            }
        },

        methods: {
            showFeedDetailsPopUp: function (feedId) {
                this.selectedFeedId = feedId;

                $('#feed-details-pop-up').modal('show');
            }
        }
    }
</script>

<style scoped>

</style>