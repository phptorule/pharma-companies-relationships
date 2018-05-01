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

                        <h4 class="modal-title">
                            {{productsData.name}}
                            <a href="#"><i class="fa fa-pencil"></i></a>
                        </h4>

                        <p class="occupation">{{productsData.description}}</p>

                        <p class="place-of-work">
                            at <a href="#">{{currentAddress.name}}</a>
                        </p>

                        <div class="row person-experience">
                            <div class="col-md-4">
                                <p class="number" v-if="tenderOld">
                                    {{tenderOld.tender_date}}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="number" v-if="budgeted_cost">
                                    $ {{budgeted_cost}}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="number" v-if="spending_cost">
                                    {{spending_cost}} %
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div>
                            <ul class="nav nav-tabs person-tabs">
                                <li :class="{'active': activeTab == 'chart'}">
                                    <a href="javascript:void(0)" @click="setTabActive('chart')" data-toggle="tab"
                                       aria-expanded="false">Chart</a></li>
                                <li :class="{'active': activeTab == 'tender'}">
                                    <a href="javascript:void(0)" @click="setTabActive('tender')" data-toggle="tab"
                                       aria-expanded="false">Tender</a></li>
                            </ul>

                            <div class="tab-content">

                                <div v-if="activeTab == 'chart'" @click="viewTendersChart()">
                                    <div id="tender-charts"></div>
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
    import {GoogleCharts} from 'google-charts';

    export default {
        mixins: [http, getPersonInitials, ProductsModal],

        data: function () {
            return {
                addressId: null,
                purchaseId: null,
                currentAddress: {},
                productsData: {},
                tendersData: {},
                activeTab: '',
                connectionTypes: [],
                tenderOld:{
                    tender_date: '',
                },
                actual_cost: null,
                budgeted_cost: null,
                actual_year: (new Date()).getFullYear(),
                actual_year_cost: null,
                old_year_cost: null,
                spending_cost: null
            }
        },

        computed: {

        },

        methods: {
            init: function (addressId, purchaseId, address) {
                $('#product-modal').modal('show');

                this.addressId = addressId;
                this.purchaseId = purchaseId;
                this.currentAddress = address;

                this.httpGet('/api/product-by-purchase/' + purchaseId)
                    .then(data => {
                        data.forEach(product => {
                            this.productsData = product;
                            this.getTendersByProduct(this.productsData.product_id)
                        })
                    });

            },

            getTendersByProduct: function (product_id) {
            this.httpGet('/api/purchase-by-tenders/' + product_id)
                .then(data => {
                    this.tendersData = data;
                    data.forEach(tender => {
                        this.actual_cost += Math.ceil(Number(tender.actual_cost));
                        this.budgeted_cost += Math.ceil(Number(tender.budgeted_cost));
                        if(this.actual_year == Number(tender.delivery_year)){
                            this.actual_year_cost += Math.ceil(Number(tender.delivery_year));
                        }
                        if((this.actual_year-1) == Number(tender.delivery_year)){
                            this.old_year_cost += Math.ceil(Number(tender.delivery_year));
                        }
                    })
                    this.spending_cost = Math.ceil(((this.actual_year_cost-this.old_year_cost)/this.old_year_cost)*100);
                    this.tenderOld = this.tendersData[0];
                });
            },
            setTabActive: function (tabName) {
                this.activeTab = tabName;
            },

            viewTendersChart: function(){
                GoogleCharts.load(drawChart);

                function drawChart() {
                    var data = google.visualization.arrayToDataTable([
                        ['Month', 'Bolivia', 'Ecuador', 'Madagascar', 'Papua New Guinea', 'Rwanda', 'Average'],
                        ['2004/05',  165,      938,         522,             998,           450,      614.6],
                        ['2005/06',  135,      1120,        599,             1268,          288,      682],
                        ['2006/07',  157,      1167,        587,             807,           397,      623],
                        ['2007/08',  139,      1110,        615,             968,           215,      609.4],
                        ['2008/09',  136,      691,         629,             1026,          366,      569.6]
                    ]);

                    var options = {
                        title : 'Monthly Coffee Production by Country',
                        vAxis: {title: 'Cups'},
                        hAxis: {title: 'Month'},
                        seriesType: 'bars',
                        series: {5: {type: 'line'}}
                    };
                        const pie_1_chart = new GoogleCharts.api.visualization.ComboChart(document.getElementById('tender-charts'));
                        pie_1_chart.draw(data);
                }
            }

        },

        mounted: function () {
            this.$eventGlobal.$on('showModalProductsDetails', (data) => {
                this.init(data.addressId, data.purchaseId, data.address);
            });
            this.getTendersByProduct();
        }
    }
</script>

<style scoped>

</style>