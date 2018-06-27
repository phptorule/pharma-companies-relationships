<template>
    <header class="main-header">
        <!-- Logo -->



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

        methods: {
            makeGlobalSearch: function () {

                if(this.timeOutId){
                    clearTimeout(this.timeOutId)
                }

                this.timeOutId = setTimeout(()=>{

                    // if(this.$route.path != '/dashboard') {
                    //     this.$router.push('/dashboard?global-search=' + encodeURIComponent(this.globalSearchInput));
                    // }
                    // else{
                    //     this.$router.push('/dashboard?global-search=' + encodeURIComponent(this.globalSearchInput));
                    // }
                    //

                    if(this.globalSearchInput == '') {

                        this.notifyGlobalSearchResults({count_addresses: null, count_people: null});

                        return;
                    }

                    this.preProcessGlobalSearchQuery();

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

                if(data.count_addresses === null && data.count_people === null ) {
                    localStorage.removeItem('global-search-result-counter');
                }
                else {
                    localStorage.setItem('global-search-result-counter', JSON.stringify(data));
                }

                this.$eventGlobal.$emit('notifyGlobalSearchCountResults', data);
            }
        },

        created: function () {

        },
        mounted: function () {
            this.$eventGlobal.$on('resetedAllFilters', () => {
                this.globalSearchInput = '';
            })
        }
    }
</script>

<style scoped>

</style>