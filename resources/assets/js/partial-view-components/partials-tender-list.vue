<template>
    <div>


        <div v-show="addressDoesNotHaveTenders === 'loading'">
            <h3 class="text-center">Loading ...</h3>
        </div>

        <div class="col-md-12" v-show="addressDoesNotHaveTenders === true">
            <h3 class="text-center">Sorry the current address didn't participate in that tender</h3>
        </div>

        <div v-show="addressDoesNotHaveTenders === false">
            <div class="col-md-12 tender-query">
                <div class="col-md-4 tender-search">
                    <input
                            class="tender-search-input"
                            v-model="appliedFilters.tendersSearchInput"
                            @keyup="makeTendersSearch()"
                            placeholder="Search tenders">
                </div>
                <div class="col-md-4 filter-cost-tender">
                    <div class="col-md-6 min-value">
                        <input class="min-value-input" v-model="tendersCost.value[0]">
                    </div>
                    <div class="slider-dash">-</div>

                    <div class="col-md-6 max-value">
                        <input class="max-value-input" v-model="tendersCost.value[1]">
                    </div>
                    <div class="col-md-12 min-max">
                        <vue-slider ref="sortCost"
                                    v-bind="tendersCost"
                                    v-model="tendersCost.value"
                                    v-if="isSliderVisible"
                        >
                        </vue-slider>
                    </div>
                    <p class="tender-slider-amount-title">Tender Amount</p>
                    <p class="slider-currency-k">(K rubels)</p>
                </div>
                <div class="col-md-4 filter-tag-query-tender">
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
                        <single-dropdown-select
                                class="form-control select-filter tags-filter"
                                :options="sortByOptionsForFilter"
                                :isHiddenEmptyOption="true"
                                @changed="applySortFilter"
                                :name="'Sort By'"
                                ref="sortBySingleDropdownSelect"
                        ></single-dropdown-select>
                    </div>
                </div>
                <div class="col-md-1 export-excel">
                    <download-excel
                            class="export-to-excel"
                            :data="tendersExport.json_data"
                            :fields="tendersExport.json_fields"
                            name="tenders.xls"
                    >
                        <i class="fa fa-file-excel-o fa-2x" @click="exportToExcel(productId)"
                           title="Export to excel"></i>
                    </download-excel>
                    <download-excel
                            class="export-to-csv"
                            :data="tendersExport.json_data"
                            :fields="tendersExport.json_fields"
                            type="csv"
                            name="tenders.csv"
                    >
                        <img @click="exportToExcel(productId)" src="/images/csv.png"
                             title="Export to csv">
                    </download-excel>
                </div>
            </div>
            <div class="col-md-12 tender-data">
                <ul class="col-md-12 tenders-list">
                    <li v-for="(tender, i) in tendersList">
                        <div class="item">
                            <h3 class="pointer"
                                v-if="tender.purchase_name"
                                v-ctk-tooltip="tender.purchase_name"
                                @click="loadTenderDetails(tender)"
                            >
                                {{tender.tender_date ? tender.tender_date : 'Not date'}} -
                                {{tender.purchase_name | tendername(55)}}</h3>
                            <div class="tender-volume">{{Math.ceil(Number(tender.budgeted_cost)) | currency('Rub') }}
                            </div>

                            <ul class="tag-list">

                                <li v-if="!shouldHideProductTags && tender.product_name"
                                    v-for="product in makeTagArray(tender.product_name)"
                                >
                                    <a href="javascript:void(0)" class="tags product-tag">
                                        {{ product }}
                                    </a>
                                </li>

                                <li v-if="tender.tag_name"
                                    v-for="tag in makeTagArray(tender.tag_name)"
                                >
                                    <a href="javascript:void(0)" class="tags">
                                        {{tag}}
                                    </a>
                                </li>
                            </ul>

                            <p class="tender-winner" v-if="tender.suppliers_data[0]">
                                Winner {{tender.suppliers_data[0][0]}}
                                {{tender.suppliers_data[0][1] | currency('Rub') }}
                                <span v-if="tender.suppliers_data.length > 1" class="tender-winner pointer" v-ctk-tooltip="supplier(tender.suppliers_data)">
                                                       + {{(tender.suppliers_data.length - 1)}} more winners
                                                        </span>
                                <a target="_blank" :href="tender.tender_url"><img data-v-6d155616="" src="/images/graph/external_link.svg" class="tenderUrlIcon"></a>
                            </p>
                            <p v-else class="tender-winner">Winner unkown <a target="_blank" :href="tender.tender_url"><img data-v-6d155616="" src="/images/graph/external_link.svg" class="tenderUrlIcon"></a></p>

                            <div class="tender-details-box"
                                 style="display: none"
                                 :class="'tender-box-id-' + tender.tender_id"
                            >
                                <purchase-list-of-tender
                                        :incomingTenderData="selectedTenderData"
                                ></purchase-list-of-tender>

                                <div class="text-center">
                                    <pagination :records="selectedTenderDataTotalPurchases"
                                                :class="'pagination pagination-sm'"
                                                :per-page="10" @paginate="pageChangedOfPurchaseList"
                                    ></pagination>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="col-md-12 pagination-box">
                    <pagination :records="tendersTotal" ref="paginationDirective"
                                :class="'pagination pagination-sm no-margin pull-right'"
                                :per-page="10" @paginate="pageChanged"></pagination>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import http from '../mixins/http';
    import helpers from '../mixins/helpers';
    import getProductName from '../mixins/get-product-name';
    import ProductModal from '../mixins/show-product-details-modal';
    import vueSlider from 'vue-slider-component';
    import labscapeFilters from '../filters/labscape-filters';


    export default {

        mixins: [http, helpers, getProductName, ProductModal, labscapeFilters],

        data: function () {
            return {
                isSliderVisible: false,
                selectedTenderDataTotalPurchases: 0,
                addressDoesNotHaveTenders: 'loading',
                tendersCost: {
                    value: [],
                    min: 0,
                    width: '100%',
                    max: null,
                    disabled: false,
                    show: true,
                    tooltip: 'always',
                    formatter: '',
                    tooltipStyle: {
                        display: 'none',
                    },
                    speed: 0.3,
                },
                addressId: null,
                productId: null,
                purchaseId: null,
                currentAddress: {},
                productsData: {},
                activeTab: 'chart',
                tag_list: [],
                selectedTags: [],
                selectedTenderData: {},
                productTags: [],
                appliedFilters: {
                    tags: this.$route.query['tag-cons[]'] || [],
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
                tenderData: {
                    last_tender_date: null,
                    max_total_spent: null,
                    min_total_spent: null,
                    next_budgeted_cost: null,
                    tag_ids: '',
                    total_budgeted:null,
                },
                tendersList: [],
                actual_year: (new Date()).getFullYear(),
                old_year_cost: null,
                actual_year_cost: null,
                next_year_cost: null,
                spending_cost: null,
                isGoogleChartCoreLoaded: false,
                graphLoadedModal: false,
                showTenderCost: false,
                tendersExport: {
                    json_fields: {
                        'Tender name': 'purchase_name',
                        'Description': 'purchase_remark',
                        'Price': 'budgeted_cost',
                        'Winner': 'winner',
                        'Product': 'product_name',
                        'Consumables': 'tag_name',
                        'Date': 'tender_date',
                    },
                    json_data: [],
                    json_meta: [[{
                        'key': 'charset',
                        'value': 'utf-8'
                    }]],
                },
                chartQueryTag: '',

                users:[
                    {name:'Tom', age:22},
                    {name:'Bob', age:25},
                    {name:'Sam', age:28},
                    {name:'Alice', age:26}
                ],
                selectedUsers:[],
                isHideOthersTag: false
            }
        },

        computed: {

            sortByOptionsForFilter: function () {
                return [
                    {value: 'budget-asc', label: 'Budget ↑'},
                    {value: 'budget-desc', label: 'Budget ↓'},
                    {value: 'date-asc', label: 'Date ↑'},
                    {value: 'date-desc', label: 'Date ↓'},
                    {value: 'tenders-asc', label: 'Ascending ↑'},
                    {value: 'tenders-desc', label: 'Descen ↓'},
                ]
            },

            tagOptionsForDropDown: function () {
                return this.tag_list.map(tag => {
                    return {
                        label: tag.name,
                        value: tag.id,
                        color: tag.color,
                    }
                })
            }

        },

        watch: {

            tagList: function(newVal) {
                this.tag_list = newVal;
            },

            isListVisible: function(isTrue){

                this.isSliderVisible = false;

                if (isTrue) {
                    setTimeout(()=>{
                        this.isSliderVisible = true;
                    },0);
                }
            },

            initalParams: {
                handler: function(data) {
                    this.tendersCost.max = null;
                    this.tendersCost.min = 0;
                    this.init(data.addressId, data.purchaseId, data.address);
                    this.activeTab = 'chart';
                    this.tendersCost.value = [];
                },
                deep: true
            },

            show(val) {
                if (val) {

                    this.$nextTick(() => this.$refs.sortCost.refresh());
                }
            },

            tendersCost: {
                handler: function () {

                    this.filterCost();

                },
                deep: true
            },

            $route: function (to) {

                if ($('#product-modal').hasClass('in') || $('#tenders-modal').hasClass('in')) {

                    this.initFilters();

                    this.composeQueryUrl();

                    this.$refs.paginationDirective.setPage(1);
                }

            },
        },

        methods: {
            init: function (addressId, productId, address) {

                this.addressDoesNotHaveTenders = 'loading';

                this.graphLoadedModal = false;
                this.spending_cost = null;
                this.actual_year_cost = null;
                this.addressId = addressId;
                this.productId = productId;
                this.currentAddress = address;


                if(productId) {

                    this.getTendersByProduct(this.productId);
                }

            },

            supplier: function (value) {

                if(value.length > 1) {

                    var supplier = '';

                    value.forEach((suppliers, i) => {

                        if(i != 0) {

                            supplier += suppliers[0] + ' of most money ' + suppliers[1];

                            if (i >= 3) {

                                return supplier;

                            }
                        }

                    });

                    return supplier;

                }

                return '';
            },

            getTendersByProductPaginate: function (product_id) {

                let url = '/api/tenders-by-product-and-address-paginated/' + product_id + '/' + this.addressId + '?page=' + this.pagination.currentPage + this.composeQueryUrl();

                this.httpGet(url)
                    .then(data => {

                        this.tendersTotal = data.total;
                        this.tendersList = data.data;

                        this.addressDoesNotHaveTenders = false;
                    });

            },

            getTendersPaginate: function (addressId) {

                if(!addressId) {
                    return;
                }

                let url = '/api/tenders-by-address-paginated/' + addressId + '?page=' + this.pagination.currentPage + this.composeQueryUrl();
                this.httpGet(url)
                    .then(data => {
                        this.tendersTotal = data.total;
                        this.tendersList = data.data;

                        this.addressDoesNotHaveTenders = false;

                        if(this.tendersCost.max == null && this.tendersList[0]){
                            this.tendersCost.max = Math.ceil(this.tendersList[0].max_budgeted / 1000)+1;

                            this.tendersCost.value = [this.tendersCost.min, this.tendersCost.max];

                        }

                        this.isSliderVisible = true;

                    });
            },

            getTendersByProduct: function (product_id) {
                
                this.httpGet('/api/product-by-tenders/' + product_id + '/' + this.addressId)
                    .then(data => {
                        this.tenderData = data;
                        this.tenderData.total_budgeted = Math.ceil(this.tenderData.total_budgeted /1000);
                        this.selectedTags = [];
                        this.productTags = [];

                        if(String(this.tenderData.tag_ids) != 'null') {

                            let tenderTag = JSON.parse("[" + this.tenderData.tag_ids + "]");
                            this.tag_list.forEach(tag =>{
                                for (let i = 0; i < tenderTag.length; i++) {
                                    if(tag.id == tenderTag[i]){
                                        this.productTags[i] = this.selectedTags[i] = {
                                            id: tag.id,
                                            color: tag.color,
                                            name: tag.name,
                                        };
                                    }
                                }
                            });

                        }

                        this.tendersCost.max = Math.ceil(this.tenderData.max_total_spent / 1000)+1;

                        this.tendersCost.value = [this.tendersCost.min, this.tendersCost.max];

                    });
            },

            applyFilters: function (isOnlySortingChanged) {

                this.appliedFilters.isOnlySortingChanged = !!isOnlySortingChanged;

                this.composeQueryUrl();

                this.$refs.paginationDirective.setPage(1);
            },

            applyTagsFilter: function (data) {
                this.appliedFilters.tags = data;
                this.applyFilters(true);
            },

            applySortFilter: function (data) {
                this.appliedFilters.sortBy = data;
                this.applyFilters(true);
            },

            pageChanged: function (pageNumber) {

                this.slideUpAllTenderBoxes();

                this.pagination.currentPage = pageNumber;
                if(this.productId) {
                    this.getTendersByProductPaginate(this.productId);
                }
                else {
                    this.getTendersPaginate(this.addressId);
                }

            },

            splitConsumablesFromProductForQueryUrl: function(id) {

                if((''+id).indexOf('product-') === -1) {
                    return'&tag-cons[]=' + id
                }
                else {
                    return'&tag-prod[]=' + id.replace('product-', '');
                }
            },

            composeQueryUrl: function () {
                let queryStr = '';

                if (this.appliedFilters.tendersSearchInput) {
                    queryStr += '&tenders-search=' + this.appliedFilters.tendersSearchInput;

                }

                if (this.appliedFilters.tags.length) {

                    if (typeof this.appliedFilters.tags === 'string') {
                        queryStr += this.splitConsumablesFromProductForQueryUrl(this.appliedFilters.tags);
                    }
                    else {
                        this.appliedFilters.tags.forEach(id => {
                            queryStr += this.splitConsumablesFromProductForQueryUrl(id);
                        });
                    }
                }

                if (this.appliedFilters.sortBy) {
                    queryStr += '&sort-by=' + this.appliedFilters.sortBy;
                }

                if (this.appliedFilters.sortCost.length && this.appliedFilters.sortCost[1]) {
                    queryStr += '&min=' + (this.appliedFilters.sortCost[0] * 1000) + '&max=' + (this.appliedFilters.sortCost[1] * 1000);
                }

                this.queryUrl = queryStr;

                return queryStr;
            },

            initFilters: function () {

                this.appliedFilters.tags = this.$route.query['tag-tenders[]'] || [];

                if (typeof this.appliedFilters.tags === 'string') {
                    this.appliedFilters.tags = [this.appliedFilters.tags];
                }

                this.appliedFilters.sortBy = this.$route.query['sort-by'] || '';
                this.appliedFilters.tendersSearchInput = this.$route.query['tenders-search'] || '';

            },

            makeTendersSearch: function () {
                if (this.timeOutId) {
                    clearTimeout(this.timeOutId)
                }

                this.timeOutId = setTimeout(() => {

                    if (this.$route.path != '/address-details/' + this.addressId) {
                        this.$router.push('/address-details/' + this.addressId + '?tenders-search=' + encodeURIComponent(this.appliedFilters.tendersSearchInput));
                    }
                    else {
                        this.$router.push('/address-details/' + this.addressId + '?global-search=' + encodeURIComponent(this.appliedFilters.tendersSearchInput));
                    }

                    if (this.appliedFilters.tendersSearchInput == '') {
                        this.$router.push('/address-details/' + this.addressId);
                    }

                    this.composeQueryUrl();

                    this.$router.push('/address-details/' + this.addressId + '?' + this.queryUrl);

                }, 1000)
            },

            filterCost: function () {

                if (this.timeOutId) {
                    clearTimeout(this.timeOutId)
                }

                this.timeOutId = setTimeout(() => {
                    this.appliedFilters.sortCost[0] = this.tendersCost.value[0];
                    this.appliedFilters.sortCost[1] = this.tendersCost.value[1];
                    this.applyFilters(true);
                }, 1000)

            },

            exportToExcel: function (product_id) {

                let url = '/api/tenders-by-product-and-address-to-excel/' + product_id + '/' + this.addressId + '?' + this.composeQueryUrl();

                if(!product_id) {
                    url = '/api/tenders-by-address-to-excel/' + this.addressId + '?' + this.composeQueryUrl();
                }

                this.httpGet(url)
                    .then(data => {
                        this.tendersExport.json_data = data;
                    });
            },

            checkIfNoSpentOnOthers: function(chartData)
            {
                let othersSum = 0;

                for(let i=0; i <  chartData.length; ++i) {
                    let row = chartData[i];

                    othersSum += row[row.length-1];
                }

                if(othersSum === 0) {
                    this.isHideOthersTag = true;
                    chartData.forEach((el,i) => {
                        el.splice([el.length-1],1);
                    })
                }
                else {
                    this.isHideOthersTag = false;
                }
            },

            makeTagArray: function (tagsString) {
                return tagsString ? tagsString.split(', ') : [];
            },

            slideUpAllTenderBoxes: function() {
                $('.tender-details-box').slideUp(0, ()=>{
                    $('.tender-details-box').parent().parent().removeClass('open')
                });
            },


            pageChangedOfPurchaseList: function(pageNumber) {

                let url = '/api/tenders/'+this.selectedTenderData.tender.id + '?page=' + pageNumber;

                this.httpGet(url)
                    .then(data => {
                        this.selectedTenderData = data;

                        this.selectedTenderDataTotalPurchases = data.purchases.total;

                        this.selectedTenderData.purchases = data.purchases.data;
                    });
            },

            loadTenderDetails: function (tender) {

                let selector = '.tender-box-id-' + tender.tender_id;

                if (this.checkIfShouldBeSlidedUp(selector, ()=>{$(selector).parent().parent().removeClass('open');})) {
                    return;
                }

                this.slideUpAllTenderBoxes();

                let url = '/api/tenders/'+tender.tender_id + '?page=1';

                this.httpGet(url)
                    .then(data => {

                        this.selectedTenderDataTotalPurchases = data.purchases.total;

                        this.selectedTenderData = data;

                        this.selectedTenderData.purchases = data.purchases.data;

                        setTimeout(()=>{
                            $(selector).slideDown('slow');
                            $(selector).parent().parent().addClass('open');
                        }, 100);

                    });
            }

        },

        props: ['initalParams', 'isListVisible', 'tagList', 'shouldHideProductTags'],

        mounted: function() {

        },

        components: {
            vueSlider
        },
    }
</script>

<style scoped>

</style>