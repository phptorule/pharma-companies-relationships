<template>
    <div class="admin-users">
        <div class="au-container">
            <div class="au-breadcrumbs">
                <ul class="au-breadcrumbs-list">
                    <li>Admin</li>
                    <li><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                    <li class="active">Users</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import http from '../../mixins/http';

    export default {
        mixins: [http],

        data: function () {
            return {
                
            }
        }
    }
</script>

<style scoped>

</style>

