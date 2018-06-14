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

                        <div class="tab-content">
                            <div class="activeTab">
                                <tender-list-partial
                                        :initalParams="tenderListParams"
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
                tenderListParams: {}
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