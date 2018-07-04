<template>
    <div>
        <paginated-address-list></paginated-address-list>
    </div>
</template>

<script>

    import http from '../mixins/http';

    export default {

        mixins: [http],

        data: function () {
            return {

            }
        },

        watch: {
            personId: function () {
                this.init();
            }
        },

        methods: {
            init: function () {

            }
        },

        props: ['personId'],

        mounted: function () {
            this.loadAvailableAddressesPaginated();
        }
    }
</script>

<style scoped>

</style>