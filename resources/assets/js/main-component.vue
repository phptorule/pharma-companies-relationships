<template>
    <div>

        <public-outlet v-if="!isLoggedIn"></public-outlet>

        <private-outlet v-if="isLoggedIn"></private-outlet>

        <loader></loader>

    </div>
</template>

<script>

    import AuthService from './services/auth-service';

    export default {

        data: function () {
            return {
                isLoggedIn: AuthService.isLoggedIn
            }
        },

        methods: {

        },

        created: function () {

        },

        mounted: function () {
            this.$eventGlobal.$on('userLogout', () => {
                this.isLoggedIn = false;
                localStorage.removeItem("auth-token");
                localStorage.removeItem("logged-user");
                window.location = '/login';
            });
        }
    }
</script>

<style scoped>

</style>