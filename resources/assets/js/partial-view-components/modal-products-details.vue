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
                                <p class="number" v-if="usedYears">
                                    Used for > {{usedYears}} years
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="number" v-if="budgeted_cost">
                                    Tot $ {{budgeted_cost}} spent
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="number" v-if="spending_cost">
                                    {{spending_cost}} % Projected spending {{actual_year+1}}
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

                                <div class="row" v-if="activeTab == 'tender'">
                                    <div class="col-md-12 tender-query">
                                        <div class="col-md-4 tender-search">
                                            <img src="/images/ic-search.png" alt="">
                                            <input
                                                    class="tender-search-input"
                                                    v-model="appliedFilters.tendersSearchInput"
                                                    @keyup="makeTendersSearch()"
                                                    placeholder="Search tenders">
                                        </div>
                                        <div class="col-md-4 filter-cost-tender">
                                            <div class="col-md-6">
                                                <input class="min-value" v-model="tendersCost.value[0]">
                                            </div>
                                            <div class="col-md-6">
                                                <input class="max-value" v-model="tendersCost.value[1]">
                                            </div>
                                            <div class="col-md-12">
                                                <vue-slider ref="sortCost"
                                                            v-bind="tendersCost"
                                                            v-model="tendersCost.value">

                                                </vue-slider>
                                            </div>
                                        </div>
                                        <div class="col-md-6 filter-tag-query-tender" >
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
                                                    <option value="budget-asc">Budget &uarr;</option>
                                                    <option value="budget-desc">Budget &darr;</option>
                                                    <option value="date-asc">Date &uarr;</option>
                                                    <option value="date-desc">Date &darr;</option>
                                                    <option value="tenders-asc">Ascending &uarr;</option>
                                                    <option value="tenders-desc">Descen &darr;</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 tender-data">
                                        <ul class="col-md-12 tenders-list">
                                            <li v-for="(tender, i) in tendersList">
                                                <div class="item">
                                                    <h3 v-if="tender.name">{{i+1}}. {{tender.name}}</h3>

                                                    <p class="tender-winner" v-if="tender.name">Winner of most money {{tender.winner}}</p>

                                                    <p class="tender-reward" v-if="tender.name">Reward type {{tender.reward}}</p>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="col-md-10 pagination-box">
                                            <pagination :records="tendersTotal" ref="paginationDirective" :class="'pagination pagination-sm no-margin pull-right'" :per-page="2" @paginate="pageChanged"></pagination>
                                        </div>
                                        <div class="col-md-2 export-excel">
                                            <button class="btn btn-primary">export-excel</button>
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
                tag_list: [],
                appliedFilters: {
                    tags: this.$route.query['tag-ids[]'] || [],
                    sortCost: this.$route.query['min-max[]'] || [],
                    sortBy: this.$route.query['sort-by'] || '',
                    isOnlySortingChanged: false,
                    tendersSearchInput: this.$route.query['tenders-search'] || '',
                },
                tendersTotal: 0,
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
                spending_cost: null,
                usedYears: null
            }
        },

        computed: {
            tagOptionsForDropDown: function () {
                return this.tag_list.map(tag => {
                    return {
                        label: tag.name,
                        value: tag.id,
                    }
                })
            }

        },

        components: {
            vueSlider
        },

        watch: {
            tendersCost: {
                handler: function() {
                    this.filterCost();
                },
                deep: true
            },
            $route: function (to) {
                this.initFilters();

                this.composeQueryUrl();
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
                    data = data.sort(function (a, b) {
                        return b.delivery_year - a.delivery_year;
                    });
                    this.usedYears = this.actual_year - Math.ceil(data[data.length - 1].delivery_year);
                });
            },

            setTabActive: function (tabName) {
                this.activeTab = tabName;
            },

            getTendersPaginate: function (product_id) {
                let url = '/api/product-by-tenders-paginated/' + product_id +'?page=' + this.pagination.currentPage + this.queryUrl;
                console.log(url);
                this.httpGet(url)
                    .then(data => {
                        this.tendersTotal = data.total;
                        this.tendersList = data.data;
                    });
            },

            applyFilters: function (isOnlySortingChanged) {

                this.appliedFilters.isOnlySortingChanged = !!isOnlySortingChanged;


                this.composeQueryUrl();

                this.$router.push('/address-details/'+this.addressId+'?'+this.queryUrl);
            },

            applyTagsFilter: function (data) {
                this.appliedFilters.tags = data;
                this.applyFilters();
            },

            pageChanged: function (pageNumber) {
                this.pagination.currentPage = pageNumber;
                this.getTendersPaginate(this.productId);
            },

            composeQueryUrl: function () {
                let queryStr = '';

                if (this.appliedFilters.tendersSearchInput) {
                    queryStr += '&tenders-search=' + this.appliedFilters.tendersSearchInput;
                    this.$router.push('/address-details/'+this.addressId+'?tenders-search=' + this.appliedFilters.tendersSearchInput);
                }

                if (this.appliedFilters.tags.length) {
                    this.appliedFilters.tags.forEach(id => {
                        queryStr += '&tag-ids[]=' + id;
                    });
                }

                if (this.appliedFilters.sortBy) {
                    queryStr += '&sort-by=' + this.appliedFilters.sortBy;
                }

                if (this.appliedFilters.sortCost.length) {
                    queryStr += '&min=' + this.appliedFilters.sortCost[0]+'&max=' + this.appliedFilters.sortCost[1];
                }

                this.queryUrl = queryStr;

                return queryStr;
            },

            initFilters: function () {

                this.appliedFilters.tags = this.$route.query['tag-tenders[]'] || [];

                if(typeof this.appliedFilters.tags === 'string') {
                    this.appliedFilters.tags = [this.appliedFilters.tags ];
                }

                this.appliedFilters.sortBy = this.$route.query['sort-by'] || '';
                this.appliedFilters.tendersSearchInput = this.$route.query['tenders-search'] || '';

            },

            makeTendersSearch: function () {
                if(this.timeOutId){
                    clearTimeout(this.timeOutId)
                }

                this.timeOutId = setTimeout(()=>{

                    if(this.$route.path != '/address-details/'+this.addressId) {
                        this.$router.push('/address-details/'+this.addressId+'?tenders-search=' + encodeURIComponent(this.appliedFilters.tendersSearchInput));
                    }
                    else{
                        this.$router.push('/address-details/'+this.addressId+'?global-search=' + encodeURIComponent(this.appliedFilters.tendersSearchInput));
                    }

                    if(this.appliedFilters.tendersSearchInput == '') {
                        this.$router.push('/address-details/'+this.addressId);
                    }

                    this.composeQueryUrl();

                    this.$router.push('/address-details/'+this.addressId+'?'+this.queryUrl);

                },1000)
            },

            filterCost: function () {

                if(this.timeOutId){
                    clearTimeout(this.timeOutId)
                }

                this.timeOutId = setTimeout(()=>{
                    this.appliedFilters.sortCost[0] = this.tendersCost.value[0];
                    this.appliedFilters.sortCost[1] = this.tendersCost.value[1];
                    this.applyFilters(true);
                },1000)

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