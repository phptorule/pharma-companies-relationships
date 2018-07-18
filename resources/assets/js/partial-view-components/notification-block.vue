<template>
    <div class="callout-notification-container">
        <div v-for="(notification, i) of notifications"
             class="callout callout-notification"
             :class="'callout-'+notification.type"
        >
            <p>
                <strong>{{notification.title}}</strong>
                {{notification.body}}

                <span v-if="notification.key == 'NEXT_DEPLOYMENT_AT'">{{timerValue}}</span>
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
                notifications: [],
                timerValue: 0
            }
        },


        methods: {

            checkForWebsiteNotifications: function() {
                this.httpGet('/api/check-website-notifications', {doNotShowLoader: true})
                    .then(data => {
                        this.notifications = data;
                        this.$eventGlobal.$emit('first-time-notifications-were-shown', {});

                        this.checkForNotificationDeployment();
                    })
            },

            loopCheckForWebsiteNotifications: function () {

                let checkNotification;

                setTimeout(checkNotification = () => {

                    this.httpGet('/api/check-website-notifications', {doNotShowLoader: true})
                        .then(data => {
                            this.notifications = data;

                            this.$eventGlobal.$emit('notifications-were-shown', {});

                            this.checkForNotificationDeployment();
                        });

                    setTimeout(checkNotification, 1000 * 5);
                }, 1000 * 5)

            },

            checkForNotificationDeployment: function() {
                let nextDeploymentNotification = this.notifications.filter(el => el.key === 'NEXT_DEPLOYMENT_AT');

                if(nextDeploymentNotification) {

                    this.countDownTimer(nextDeploymentNotification[0].expired_at);
                }
            },

            countDownTimer: function (countDownTill) {
                let countDownDate = new Date(countDownTill).getTime();


                let x = setInterval(() => {

                    let now = new Date().getTime();

                    let distance = countDownDate - now;

                    let days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    this.timerValue = days + "d " + hours + "h "
                        + minutes + "m " + seconds + "s ";

                    if (distance < 0) {
                        clearInterval(x);
                        this.timerValue = "EXPIRED";
                    }
                }, 1000);
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