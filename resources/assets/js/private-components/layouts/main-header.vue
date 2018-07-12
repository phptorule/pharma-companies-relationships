<template>
    <header class="main-header">

        <nav class="navbar navbar-static-top">

            <div class="box-for-logo">
                <div class="header-logo">
                    <span class="logo-lg">
                        <router-link to="/dashboard">
                            <img src="/images/labscape.png" alt="">
                        </router-link>
                    </span>
                </div>
            </div>

            <global-search></global-search>

            <div class="nav-links">
                <ul>
                    <li><a href="#" @click.prevent class="active without-handler">Lab Map</a></li>
                    <!--<li><a href="#">Feed</a></li>-->
                    <!-- <li><a href="#" @click.prevent class="without-handler">About</a></li> -->
                    <!--<li><a href="#" @click.prevent class="without-handler">Contact</a></li>-->
                    <li v-if="user && user.role === 'admin'">
                        <router-link to="/admin/users">
                            Admin
                        </router-link>
                    </li>
                    <li v-if="user.link"><a :href="user.link" target="_blank"  style="font-size: 2.5em; margin-right: 0"><i class="fa fa-lightbulb-o"></i></a></li>
                </ul>
            </div>

            <div class="profile-avatar-block">
                <div class="avatar-image-block" v-if="isUserLoaded && user.avatar">
                    <router-link to="/user/edit-profile" class="avatar-link">
                        <img class="avatar-image"
                            :src="user.avatar" alt=""
                        >
                    </router-link>
                </div>
            </div>

            <!-- <div>
                <a href="#" @click.prevent="logout">Logout</a>
            </div> -->

        </nav>
    </header>
</template>

<script>

    import GlobalSearch from '../../services/global-search';
    import http from '../../mixins/http';
    import AuthService from '../../services/auth-service.js';

    export default {

        mixins: [http],

        data: function () {
            return {
                user: {},
                globalSearchInput: this.$route.query['global-search'],
                timeOutId: null,
                isUserLoaded: false
            }
        },

        watch: {

            globalSearchInput: function (newVal) {
                GlobalSearch.globalSearchInput = newVal;
            },

            $route: function (to, from) {
                if (!to.query['global-search']) {
                    this.globalSearchInput = '';
                    this.notifyGlobalSearchPerformed({count_addresses: null, count_people: null});
                }
            }

        },

        methods: {
            makeGlobalSearch: function () {

                if(this.timeOutId){
                    clearTimeout(this.timeOutId)
                }

                this.timeOutId = setTimeout(()=>{

                    if(this.globalSearchInput == '') {

                        this.notifyGlobalSearchPerformed({count_addresses: null, count_people: null});
                        this.$router.push(this.$route.path);

                        return;
                    }

                    this.preProcessGlobalSearchQuery()
                        .then(data => {

                            let hash = this.$route.hash;

                            if(+data.count_addresses === 0 && +data.count_people > 0) {
                                this.$router.push('/people-dashboard?global-search=' + encodeURIComponent(this.globalSearchInput) + hash)
                            }
                            else {
                                this.$router.push('/dashboard?global-search=' + encodeURIComponent(this.globalSearchInput) + hash)
                            }
                        });

                    this.$root.logData('global_search', 'search', JSON.stringify(this.globalSearchInput));

                },1000);
            },

            preProcessGlobalSearchQuery: function () {
                return this.httpGet('/api/addresses/pre-process-global-search?global-search='+ encodeURIComponent(this.globalSearchInput))
                    .then(data => {

                        this.notifyGlobalSearchPerformed(data);

                        return data;
                    })
            },

            notifyGlobalSearchPerformed: function (data) {

                // GlobalSearch.resultCounter = data;

                // this.$eventGlobal.$emit('notifyGlobalSearchCountResults', data);
            },

            getUser: function () {
                let url = '/api/logged-user';

                this.httpGet(url)
                    .then(data => {
                        this.user = data.data;
                        this.isUserLoaded = true;
                    })
                    .catch(error => {
                        console.log(error);
                    })
            },
            logout: function () {
                let url = '/api/user/logout';

                this.httpPost(url, {
                    token: localStorage.getItem('auth-token')
                })
                    .then(data => {
                        console.log(data);
                        this.$eventGlobal.$emit('userLogout');
                    })
                    .catch(error => {
                        console.log(error);
                    })
            }
        },

        created: function () {

        },
        mounted: function () {
            this.getUser();
            this.$eventGlobal.$on('resetedAllFilters', () => {
                this.globalSearchInput = '';
                this.notifyGlobalSearchPerformed({count_addresses: null, count_people: null});
            });

            if(this.globalSearchInput) {
                this.makeGlobalSearch();
            }

            this.$eventGlobal.$on('userProfileUpdated', (user) => {
                this.user = user;
            })
        },

        beforeDestroy: function () {
            this.$eventGlobal.$off('resetedAllFilters');
            this.$eventGlobal.$off('userProfileUpdated');
        }
    }
</script>

<style scoped>

</style>