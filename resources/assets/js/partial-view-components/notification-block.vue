<template>
    <div class="callout-notification-container">
        <div v-for="(notification, i) of notifications"
             class="callout callout-danger callout-notification"
        >
            <p>
                <strong>{{notification.title}}</strong>
                {{notification.body}}
            </p>

        </div>
    </div>
</template>

<script>

    import http from '../mixins/http';

    export default {
        name: "notification-block",

        mixins: [http],

        data: function() {
            return {
                notifications: []
            }
        },


        methods: {

            checkForWebsiteNotifications: function() {
                this.httpGet('/api/check-website-notifications', {doNotShowLoader: true})
                    .then(data => {
                        this.notifications = data;
                        this.$eventGlobal.$emit('first-time-notifications-were-shown', {});
                    })
            },

            loopCheckForWebsiteNotifications: function () {

                let checkNotification;

                setTimeout(checkNotification = () => {

                    this.httpGet('/api/check-website-notifications', {doNotShowLoader: true})
                        .then(data => {
                            this.notifications = data;

                            this.$eventGlobal.$emit('notifications-were-shown', {});
                        });

                    setTimeout(checkNotification, 1000 * 5);
                }, 1000 * 5)

            }
        },


        mounted: function () {
            this.checkForWebsiteNotifications();
            this.loopCheckForWebsiteNotifications()
        }
    }
</script>

<style scoped>

</style>