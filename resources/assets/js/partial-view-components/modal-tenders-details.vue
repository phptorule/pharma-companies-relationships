<template>
    <div>
        <div class="modal fade" id="tenders-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <!-- Tenders modal head -->
                        <h4 class="modal-title">
                            Tender
                        </h4>

                    </div>
                    <div class="modal-body">

                        <ul class="nav nav-tabs person-tabs">
                            <li :class="{'active': activeTab == 'chart'}">
                                <a href="javascript:void(0)" @click="setTabActive('chart')" data-toggle="tab"
                                   aria-expanded="false">Chart</a></li>
                            <li :class="{'active': activeTab == 'tender'}">
                                <a href="javascript:void(0)" @click="setTabActive('tender')" data-toggle="tab"
                                   aria-expanded="false">Tender</a></li>
                        </ul>


                        <div class="tab-content" style="width: 100%">

                            <div :class="{hidden: activeTab !== 'chart'}">
                                Chart
                            </div>

                            <div :class="{hidden: activeTab !== 'tender'}">
                                <tender-list-partial
                                        :initalParams="tenderListParams"
                                        :isListVisible="activeTab == 'tender'"
                                ></tender-list-partial>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>


    export default {

        data: function () {
            return {
                tenderListParams: {},
                activeTab: 'chart'
            }
        },

        methods: {
            setTabActive: function (name) {
                this.activeTab = name;
            }
        },

        mounted: function () {
            this.$eventGlobal.$on('showModalTenderDetails', (data) => {

                this.tenderListParams = data;

                $('#tenders-modal').modal('show');

            });

        }
    }
</script>

<style scoped>

</style>