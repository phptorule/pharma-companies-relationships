<template>
    <div class="nav-tabs-custom sidebar-tabs">
        <ul class="nav nav-tabs">
            <li :class="{active: activeTab === 'dashboard' }">
                <router-link to="/dashboard"><i class="fa fa-building"></i> Organisations</router-link>
            </li>
            <li :class="{active: activeTab === 'people-dashboard' }">
                <router-link to="/people-dashboard"><i class="fa fa-group"></i> People</router-link>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        computed: {
            activeTab: function () {
                return this.$route.path.replace('/', '')
            }
        }
    }
</script>

<style scoped>

</style>