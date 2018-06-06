<template>
    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> {{versionNumber}}
        </div>
        <strong>Copyright &copy; {{currentYear}} PeakData GmbH.</strong> All rights reserved.
    </footer>
</template>

<script>

    import http from '../../mixins/http';

    export default {
        mixins: [http],

        data: function() {
            return {
                versionNumber: '1.0.0'
            }
        },

        computed: {
            currentYear: function () {
                return moment().format('YYYY');
            }
        },

        created:function () {
            this.httpGet('/api/app-version')
                .then(data => {
                    this.versionNumber = data;
                })
        }
    }
</script>

<style scoped>

</style>