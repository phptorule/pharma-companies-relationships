<template>
    <div>
        TENDER DATA: <br>
        {{tenderData}}
    </div>
</template>

<script>

    import http from '../mixins/http';

    export default {

        mixins: [http],

        data: function() {
            return {
                tenderData: {}
            }
        },

        watch: {
            incomingTenderData:function (newData) {
                this.tenderData = newData;
            }
        },

        methods: {

        },

        props: ['incomingTenderData']
    }
</script>

<style scoped>

</style>