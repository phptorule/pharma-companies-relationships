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


            <div class="nav-search">
                <ul class="nav navbar-nav">
                    <li>
                        <img src="/images/ic-search.png" alt="">
                        <input
                            v-model="globalSearchInput"
                            @keyup="makeGlobalSearch()"
                            placeholder="Search by laboratory, people or location"
                        >

                        <i v-if="globalSearchInput"
                           @click="resetGlobalSearch()"
                           class="fa fa-remove clear-global-search-input"></i>
                    </li>
                </ul>
            </div>

            <div class="nav-links">
                <ul>
                    <li><a href="#" @click.prevent class="active without-handler">Lab Map</a></li>
                    <!--<li><a href="#">Feed</a></li>-->
                    <li><a href="#" @click.prevent class="without-handler">About</a></li>
                    <li><a href="#" @click.prevent class="without-handler">Contact</a></li>
                </ul>
            </div>

        </nav>
    </header>
</template>

<script>

    import GlobalSearch from '../../services/global-search';
    import http from '../../mixins/http';

    export default {

        mixins: [http],

        data: function () {
            return {
                user: {},
                globalSearchInput: this.$route.query['global-search'],
                timeOutId: null
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
                            if(+data.count_addresses === 0 && +data.count_people > 0) {
                                this.$router.push('/people-dashboard?global-search=' + encodeURIComponent(this.globalSearchInput))
                            }
                            else {
                                this.$router.push('/dashboard?global-search=' + encodeURIComponent(this.globalSearchInput))
                            }
                        })

                },1000)
            },

            preProcessGlobalSearchQuery: function () {
                return this.httpGet('/api/addresses/pre-process-global-search?global-search='+ encodeURIComponent(this.globalSearchInput))
                    .then(data => {

                        this.notifyGlobalSearchPerformed(data);

                        return data;
                    })
            },

            notifyGlobalSearchPerformed: function (data) {

                GlobalSearch.resultCounter = data;

                this.$eventGlobal.$emit('notifyGlobalSearchCountResults', data);
            },

            resetGlobalSearch: function () {
                this.globalSearchInput = '';
                this.makeGlobalSearch();
            },
        },

        created: function () {

        },
        mounted: function () {
            this.$eventGlobal.$on('resetedAllFilters', () => {
                this.globalSearchInput = '';
                this.notifyGlobalSearchPerformed({count_addresses: null, count_people: null});
            });

            if(this.globalSearchInput) {
                this.makeGlobalSearch();
            }
        },

        beforeDestroy: function () {
            this.$eventGlobal.$off('resetedAllFilters');
        }
    }
</script>

<style scoped>

</style>