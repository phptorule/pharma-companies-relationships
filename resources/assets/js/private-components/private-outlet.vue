<template>

    <div v-if="$route.meta && $route.meta.isWithoutMap" class="wrapper profile-wrapper">

        <main-header></main-header>

        <router-view></router-view>

        <main-footer></main-footer>

        <feed-details-pop-up></feed-details-pop-up>

    </div>


    <div class="wrapper" v-else>

        <main-header></main-header>


        <main-sidebar></main-sidebar>


        <div class="content-wrapper">

            <section class="content">

                <modal-employee-details></modal-employee-details>

                <modal-product-details></modal-product-details>

                <modal-tender-details></modal-tender-details>

                <modal-contacts-chain></modal-contacts-chain>

                <notification-block></notification-block>

                <feed-details-pop-up></feed-details-pop-up>

                <map-main></map-main>

            </section>
        </div>


        <main-footer></main-footer>

    </div>
</template>

<script>

    require('../../../../node_modules/admin-lte/dist/js/adminlte.min');

    export default {
        created: function (){
            this.$eventGlobal.$on('update-user-profile', loggedUserData => {
                localStorage.setItem('logged-user', JSON.stringify(loggedUserData))
            });
        },

        mounted: function () {

            this.$eventGlobal.$emit('update-user-profile', JSON.parse(localStorage.getItem('logged-user')));

        }
    }
</script>

<style scoped>

</style>