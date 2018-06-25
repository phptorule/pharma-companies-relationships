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
                    <!-- <li><a href="#" @click.prevent class="without-handler">About</a></li> -->
                    <li><a href="#" @click.prevent class="without-handler">Contact</a></li>
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

    import http from '../../mixins/http.js';
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
        methods: {
            makeGlobalSearch: function () {


                if(this.timeOutId){
                    clearTimeout(this.timeOutId)
                }

                this.timeOutId = setTimeout(()=>{

                    if(this.$route.path != '/dashboard') {
                        this.$router.push('/dashboard?global-search=' + encodeURIComponent(this.globalSearchInput));
                        // this.$eventGlobal.$emit('notifyMapMainGlobalSearchPerformed', encodeURIComponent(this.globalSearchInput));
                    }
                    else{
                        this.$router.push('/dashboard?global-search=' + encodeURIComponent(this.globalSearchInput));
                        // this.$eventGlobal.$emit('globalSearchPerformed', encodeURIComponent(this.globalSearchInput));
                    }

                    if(this.globalSearchInput == '') {
                        this.$router.push('/dashboard');
                    }

                },1000)

                this.$root.logData('global_search', 'search', JSON.stringify(this.globalSearchInput));
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
                console.log(localStorage.getItem('auth-token'));
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
            });

            this.$eventGlobal.$on('userProfileUpdated', (user) => {
                this.user = user;
            })
        }
    }
</script>

<style scoped>

.avatar-image {
    height: 50px;
}

.avatar-link {
    border-radius: 50%;
    background: transparent;
    /* border: 2px solid white; */
    /* min-width: 50px; */
    width: 50px;
    min-width: 50px;
    height: 50px;
}

.avatar-link:hover {
    /* background-color: #2b579c; */
    background-color: cornflowerblue;
}

.avatar-link img {
    object-fit: cover;
    width: 100%;
    height: 100%;
    border-radius: 50%;
}

.avatar-image-block {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    width: 100%;
}

</style>