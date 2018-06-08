<template>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li :class="{active: activeTab === 'dashboard' }">
                <router-link to="/dashboard">Organisations</router-link>
            </li>
            <li :class="{active: activeTab === 'people-dashboard' }">
                <router-link to="/people-dashboard">People</router-link>
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