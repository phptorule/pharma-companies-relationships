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
                    ></feed-item-simple-text>

                    <feed-item-text-image
                            v-if="feed.type === 'image'"
                            :feed="feed"
                    ></feed-item-text-image>

                </div>


            </div>

        </div>
    </div>
</template>

<script>

    var TEST_ITEMS = [
        {
            type: 'text'
        },
        {
            type: 'text',
            tags: ['medical laboratory']
        },
        {
            type: 'image',
            tags: ['medical SBO', 'CEO']
        },

    ];

    export default {
        data: function () {
            return {
                feeds: TEST_ITEMS
            }
        }
    }
</script>

<style scoped>

</style>