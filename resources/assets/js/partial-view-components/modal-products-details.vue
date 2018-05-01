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
                                    <div class="tender-query">
                                        <div class="col-md-4 tender-search">
                                            <img src="/images/ic-search.png" alt="">
                                            <input placeholder="Search">
                                        </div>
                                        <div class="col-md-4 filter-cost-tender">
                                            <div class="col-md-6">
                                                <input class="min-value" v-model="tendersCost.value[0]">
                                            </div>
                                            <div class="col-md-6">
                                                <input class="max-value" v-model="tendersCost.value[1]">
                                            </div>
                                            <div class="col-md-12">
                                                <vue-slider v-bind="tendersCost" v-model="tendersCost.value"></vue-slider>
                                            </div>
                                        </div>
                                        <div class="col-md-4 filter-tag-query-tender" >
                                            <div class="col-md-6">
                                                <multiple-dropdown-select
                                                        class="form-control select-filter tags-filter"
                                                        :name="'Tags'"
                                                        :options="tagOptionsForDropDown"
                                                        @changed="applyTagsFilter"
                                                        ref="tagMultipleDropdownSelect"
                                                ></multiple-dropdown-select>
                                            </div>
                                            <div class="col-md-6">
                                                <select v-model="appliedFilters.sortBy" @change="applyFilters(true)" class="form-control select-filter sort-by-filter">
                                                    <option selected class="hidden" value="">Sort By</option>
                                                    <option value="tender-budget">Budget</option>
                                                    <option value="tender-date">Date</option>
                                                    <option value="tender-asc">Ascending &uarr;</option>
                                                    <option value="tender-desc">Descen &darr;</option>
                                                    <option value="tender-ding">Ding</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tender-data">
                                        <ul class="tenders-list">
                                            <li v-for="(tender, i) in tendersList">
                                                <div class="item">
                                                    <h3 v-if="tender.remark">{{i+1}}. {{tender.remark}}</h3>

                                                    <p class="tender-winner" v-if="tender.winner">{{tender.winner}}</p>

                                                    <p class="tender-reward" v-if="tender.reward">{{tender.reward}}</p>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="pagination-box">
                                            <pagination :records="tendersTotal" ref="paginationDirective" :class="'pagination pagination-sm no-margin pull-right'" :per-page="2" @paginate="pageChanged"></pagination>
                                        </div>
                                    </div>
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
    import vueSlider from 'vue-slider-component';

    export default {
        mixins: [http, getPersonInitials, ProductsModal],
        data: function () {

            return {
                tendersCost: {
                    value: [],
                    min: null,
                    max: null,
                    disabled: false,
                    show: true,
                    tooltip: 'always',
                    formatter: '',
                    tooltipStyle: {
                        display: 'none',
                    },
                },
                addressId: null,
                productId: null,
                purchaseId: null,
                currentAddress: {},
                productsData: {},
                tendersData: {},
                activeTab: '',
                connectionTypes: [],
                tendersTotal: 0,
                filterObject: {
                    used_product_list: [],
                    tag_list: []
                },
                appliedFilters: {
                    usedProducts: this.$route.query['used-product-ids[]'] || [],
                    tags: this.$route.query['tag-ids[]'] || [],
                    type:  this.$route.query['type-id'] || '',
                    sortBy: this.$route.query['sort-by'] || '',
                    isOnlySortingChanged: false,
                    globalSearch: this.$route.query['global-search'] || '',
                    addressIds: this.$route.query['address-ids'] || ''
                },
                pagination: {
                    currentPage: 1
                },
                queryUrl: '',
                tenderOld:{
                    tender_date: '',
                },
                tendersList: [],
                actual_cost: null,
                budgeted_cost: null,
                actual_year: (new Date()).getFullYear(),
                actual_year_cost: null,
                old_year_cost: null,
                spending_cost: null
            }
        },

        computed: {
            tagOptionsForDropDown: function () {
                return this.filterObject.tag_list.map(tag => {
                    return {
                        label: 'dsfsd',
                        value: '435'
                    }
                })
            }

        },

        components: {
            vueSlider
        },

        watch: {
            $route: function (to) {
                // this.initFilters();
                //
                // this.composeQueryUrl();

                this.$refs.paginationDirective.setPage(1);

            }
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
                            this.productId = this.productsData.product_id;
                            this.getTendersByProduct(this.productId);
                            this.getTendersPaginate(this.productId);
                        })
                    });

            },

            getTendersByProduct: function (product_id) {
            this.httpGet('/api/product-by-tenders/' + product_id)
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

                    data = data.sort(function (a, b) {
                        return b.budgeted_cost - a.budgeted_cost;
                    });
                    this.tendersCost.min = this.tendersCost.value[0] = Math.ceil(data[data.length - 1].budgeted_cost);
                    this.tendersCost.max = this.tendersCost.value[1] = Math.ceil(data[0].budgeted_cost);
                });
            },

            setTabActive: function (tabName) {
                this.activeTab = tabName;
            },

            getTendersPaginate: function (product_id) {
                let url = '/api/product-by-tenders-paginated/' + product_id +'?page=' + this.pagination.currentPage + this.queryUrl;
                this.httpGet(url)
                    .then(data => {
                        this.tendersTotal = data.total;
                        this.tendersList = data.data;
                        console.log(this.tendersList);
                    });
            },

            applyFilters: function (isOnlySortingChanged) {

                console.log(isOnlySortingChanged);
            },

            applyTagsFilter: function (data) {
                this.appliedFilters.isOnlySortingChanged = !!isOnlySortingChanged;

                console.log(data);
                this.appliedFilters.tags = data;
                this.applyFilters();
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
            },

            pageChanged: function (pageNumber) {
                this.pagination.currentPage = pageNumber;
                this.getTendersPaginate(this.productId);
            },

        },

        mounted: function () {
            this.$eventGlobal.$on('showModalProductsDetails', (data) => {
                this.init(data.addressId, data.purchaseId, data.address);
            });
        }
    }
</script>

<style scoped>

</style>