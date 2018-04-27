<template>
    <div>
        <div class="modal fade" id="personal-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <div class="person-profile-picture">
                            <span class="person-initials">P1</span>
                            <img src="/images/mask-0.png" alt="">
                        </div>
                        <h4 class="modal-title">personData<a href="#"><i class="fa fa-pencil"></i></a></h4>

                        <p class="occupation">personData</p>

                    </div>
                    <div class="modal-body">
                        <div>
                            <ul class="nav nav-tabs person-tabs">
                                <li :class="{'active': activeTab == 'career'}">
                                    <a href="javascript:void(0)" @click="setTabActive('career')" data-toggle="tab"
                                       aria-expanded="true">Career</a></li>
                                <li :class="{'active': activeTab == 'news'}">
                                    <a href="javascript:void(0)" @click="setTabActive('news')" data-toggle="tab"
                                       aria-expanded="false">News</a></li>
                            </ul>

                            <div class="tab-content">

                                <div v-if="activeTab == 'career'">
                                    <ul class="career-list">

                                    </ul>
                                </div>

                                <div v-if="activeTab == 'news'">
                                    <ul class="news-list">
                                        <li>
                                            <p class="date">Feb 21, 2018</p>
                                            <div class="news-box">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis
                                                    esse facilis ipsa ipsam itaque laborum</p>
                                            </div>
                                        </li>
                                        <li>
                                            <p class="date">Feb 21, 2018</p>
                                            <div class="news-box">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis
                                                    esse facilis ipsa ipsam itaque laborum</p>
                                            </div>
                                        </li>
                                        <li>
                                            <p class="date">Feb 21, 2018</p>
                                            <div class="news-box">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis
                                                    esse facilis ipsa ipsam itaque laborum</p>
                                            </div>
                                        </li>
                                        <li>
                                            <p class="date">Feb 21, 2018</p>
                                            <div class="news-box">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis
                                                    esse facilis ipsa ipsam itaque laborum</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import http from '../mixins/http';
    import getPersonInitials from '../mixins/get-person-initials';

    export default {
        mixins: [http, getPersonInitials],

        data: function () {
            return {
                personId: null,
                currentAddressId: null,
                currentAddress: {},
                personData: {
                    name: '',
                    careers: [],
                    publications: []
                },
                activeTab: 'career',
                connectionTypes: []
            }
        },

        computed: {

        },

        methods: {

        },

        mounted: function () {

        }
    }
</script>

<style scoped>

</style>