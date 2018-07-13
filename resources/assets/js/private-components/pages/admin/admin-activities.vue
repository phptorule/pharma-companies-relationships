<template>
    <div class="admin-users">
        <div class="au-container">
            <div class="au-breadcrumbs">
                <ul class="au-breadcrumbs-list">
                    <li>Admin</li>
                    <li><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                    <li class="active">Activity</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import http from '../../../mixins/http';

    export default {
        mixins: [http],

        data: function () {
            return {}
        },
        watch: {

        },
        methods: {
            loadTopUserActivities: function () {
                this.httpGet('/api/admin/get-top-user-activities')
            }
        },
        mounted: function () {
            this.loadTopUserActivities()
        }
    }
</script>

<style scoped>

</style>

