<template>
    <div>
        <div class="modal fade" id="product-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <div class="person-profile-picture">
                            <span class="person-initials">{{getPersonInitials('p')}}</span>
                            <img src="/images/mask-0.png" alt="">
                        </div>

                        <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->

                        <h4 class="modal-title">{{productData.name? productData.company + ': ' + productData.name : productData.company}} <a href="#"><i class="fa fa-pencil"></i></a></h4>

                        <p class="occupation">{{productData.description}}</p>

                            <ul class="social-icons">
                            <li><a href=""><i class="fa fa-linkedin"></i></a></li>
                            <li><a href=""><i class="fa fa-twitter"></i></a></li>
                            <li><a href=""><i class="fa fa-facebook"></i></a></li>
                            <li><a href=""><i class="fa fa-instagram"></i></a></li>
                        </ul>

                        <div class="view-contacts-chain-container">
                            <a href="javascript:void(0)">View Contact Chain</a>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div>
                            <ul class="nav nav-tabs person-tabs">
                                <li :class="{'active': activeTab == 'productInfo'}">
                                    <a href="javascript:void(0)" @click="setTabActive('productInfo')" data-toggle="tab"
                                       aria-expanded="true">Product info</a></li>
                                <li :class="{'active': activeTab == 'chart'}">
                                    <a href="javascript:void(0)" @click="setTabActive('chart')" data-toggle="tab"
                                       aria-expanded="false">Chart</a></li>
                                <li :class="{'active': activeTab == 'tender'}">
                                    <a href="javascript:void(0)" @click="setTabActive('tender')" data-toggle="tab"
                                       aria-expanded="false">Tender</a></li>
                            </ul>

                            <div class="tab-content">

                                <div v-if="activeTab == 'productInfo'">
                                    productInfo
                                </div>

                                <div v-if="activeTab == 'chart'">
                                    chart
                                </div>

                                <div v-if="activeTab == 'tender'">
                                    tender
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
    import ProductsModal from '../mixins/show-products-details-modal';

    export default {
        mixins: [http, getPersonInitials, ProductsModal],

        data: function () {
            return {
                addressId: null,
                purchaseId: null,
                currentAddress: {},
                productData: {},
                activeTab: 'productInfo',
                connectionTypes: []
            }
        },

        computed: {

        },

        methods: {
            init: function (addressId, purchaseId) {
                $('#product-modal').modal('show');

                this.addressId = addressId;
                this.purchaseId = purchaseId;
                // this.currentAddress = address;

                this.httpGet('/api/product-by-purchase/'+purchaseId)
                    .then(data => {
                        this.productData = data;
                        console.log(this.productData);
                    })
            },
            setTabActive: function (tabName) {
                this.activeTab = tabName;
            }
        },

        mounted: function () {
            this.$eventGlobal.$on('showModalProductsDetails', (data) => {
                this.init(data.addressId, data.purchaseId);
            });
        }
    }
</script>

<style scoped>

</style>