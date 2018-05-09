<template>
    <div>
        <div class="modal fade" id="product-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <div class="person-profile-picture">
                            <span class="person-initials">{{getPersonInitials('p')}}</span>
                            <img :src="productsData.image? productsData.image : '/images/mask-0.png'" alt="">
                        </div>

                        <h4 class="modal-title">
                            {{productsData.name? productsData.name : 'Product name'}}
                            <a href="#"><i class="fa fa-pencil"></i></a>
                        </h4>

                        <p class="occupation">{{productsData.description}}</p>

                        <p class="place-of-work">
                            at <a href="#">{{currentAddress.name}}</a>
                        </p>

                        <div class="row person-experience">
                            <div class="col-md-4">
                                <p class="number" v-if="usedYears">
                                    {{usedYears}}
                                </p>

                                <p class="text">
                                    <span><i class="fa fa-calendar"></i></span> Used for years
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="number" v-if="budgeted_cost">
                                    {{budgeted_cost | currency }}
                                </p>
                                <p class="text">
                                    <span> <i class="fa fa-ruble"></i></span> Tot spent
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="number" v-if="spending_cost != 'Infinity'">
                                    {{spending_cost}} %
                                </p>
                                <p class="number" v-else>
                                    0 %
                                </p>
                                <p class="text">
                                    <span><i class="fa fa-arrow-circle-up"></i></span> Projected spending
                                    {{actual_year+1}}
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

                                <div class="tender-chart-container" :class="{hidden: activeTab !== 'chart'}"
                                     @click="viewTendersChart()">
                                    <div class="tender-chart" id="tender-charts"></div>
                                    <div class="tag-list">
                                        <div v-for="tag in tag_list">
                                            <input type="checkbox" v-if="" v-bind:value="tag" v-model="selectedTags">
                                            <label>{{tag.name}}</label><br>
                                        </div>
                                    </div>
                                </div>

                                <div :class="{hidden: activeTab !== 'tender'}">
                                    <div class="col-md-12 tender-query">
                                        <div class="col-md-4 tender-search">
                                            <input
                                                    class="tender-search-input"
                                                    v-model="appliedFilters.tendersSearchInput"
                                                    @keyup="makeTendersSearch()"
                                                    placeholder="Search tenders">
                                        </div>
                                        <div class="col-md-4 filter-cost-tender">
                                            <i class="fa fa-ruble slider-currency-i"></i>
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
                                                            :show="showTenderCost"
                                                >
                                                </vue-slider>
                                            </div>
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
                                                    @click="exportToExcel(productId)"
                                            >
                                                <i class="fa fa-file-excel-o fa-2x" title="Export to excel"></i>
                                            </download-excel>
                                            <download-excel
                                                    class="export-to-csv"
                                                    :data="tendersExport.json_data"
                                                    :fields="tendersExport.json_fields"
                                                    type="csv"
                                                    name="tenders.csv"
                                                    @click="exportToExcel(productId)"
                                            >
                                                <i class="fa fa-file-excel-o fa-2x" title="Export to csv"></i>
                                            </download-excel>
                                        </div>
                                    </div>
                                    <div class="col-md-12 tender-data">
                                        <ul class="col-md-12 tenders-list">
                                            <li v-for="(tender, i) in tendersList">
                                                <div class="item">
                                                    <h3 v-if="tender.purchase_name">{{i+1}}.
                                                        {{tender.purchase_name}}</h3>

                                                    <p class="tender-winner" v-if="tender.budget">Winner of most money
                                                        {{Math.ceil(Number(tender.budget)) | currency }}</p>

                                                    <p class="tender-reward" v-if="tender.product_name">Reward type
                                                        {{tender.product_name}}</p>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="col-md-12 pagination-box">
                                            <pagination :records="tendersTotal" ref="paginationDirective"
                                                        :class="'pagination pagination-sm no-margin pull-right'"
                                                        :per-page="5" @paginate="pageChanged"></pagination>
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
    import ProductModal from '../mixins/show-product-details-modal';
    import vueSlider from 'vue-slider-component';


    export default {
        mixins: [http, getPersonInitials, ProductModal],
        data: function () {
            return {
                tendersCost: {
                    value: [],
                    min: 0,
                    width: '100%',
                    max: 100,
                    disabled: false,
                    show: true,
                    tooltip: 'always',
                    formatter: '',
                    tooltipStyle: {
                        display: 'none',
                    },
                    interval: 1000,
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
                tenderOld: {
                    tender_date: '',
                },
                tendersList: [],
                actual_cost: null,
                budgeted_cost: null,
                actual_year: (new Date()).getFullYear(),
                old_year_cost: null,
                actual_year_cost: null,
                next_year_cost: null,
                spending_cost: null,
                usedYears: null,
                isGoogleChartCoreLoaded: false,
                graphLoadedModal: false,
                showTenderCost: false,
                tendersExport: {
                    json_fields: {
                        'Tender name': 'purchase_name',
                        'Description': 'purchase_remark',
                        'Price': 'purchase_total_price',
                        'Winner': 'purchase_quantity',
                        'Product': 'product_name',
                        'Consumables': 'tag_name',
                        'Date': 'tender_date',
                    },
                    json_data: [],
                    json_meta: [[{
                        'key': 'charset',
                        'value': 'utf-8'
                    }]],
                }
            }
        },

        computed: {

            sortByOptionsForFilter: function () {
                return [
                    {value: 'budget-asc', label: 'Budget &uarr;'},
                    {value: 'budget-desc', label: 'Budget &darr;'},
                    {value: 'date-asc', label: 'Date &uarr;'},
                    {value: 'date-desc', label: 'Date &darr;'},
                    {value: 'tenders-asc', label: 'Ascending &uarr;'},
                    {value: 'tenders-desc', label: 'Descen &darr;'},
                ]
            },

            tagOptionsForDropDown: function () {
                return this.tag_list.map(tag => {
                    return {
                        label: tag.name,
                        value: tag.id,
                    }
                })
            }

        },

        filters: {
            currency: function (value) {
                value = String(value);
                return value.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
            },
        },

        created: function () {

            this.loadTagsFilter();
        },

        components: {
            vueSlider
        },

        watch: {

            selectedTags(tagVal) {
                this.graphLoadedModal = false;
                this.filterTagToChart();
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

                if ($('#product-modal').hasClass('in') && this.activeTab == 'tender') {


                    this.initFilters();

                    this.composeQueryUrl();

                    this.$refs.paginationDirective.setPage(1);
                }

            },

            isGoogleChartCoreLoaded: function (newVal) {
                if ($('#product-modal').hasClass('in') && newVal && this.activeTab == 'chart') {
                    this.viewTendersChart(DATA);
                }
            }
        },

        methods: {
            init: function (addressId, purchaseId, address) {
                $('#product-modal').modal('show');
                this.graphLoadedModal = false;
                this.actual_cost = null;
                this.budgeted_cost = null;
                this.spending_cost = null;
                this.actual_year_cost = null;
                this.old_year_cost = null;
                this.next_year_cost = null;
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
                            this.filterTagToChart()
                        })
                    });

            },

            getTendersByProduct: function (product_id) {
                this.httpGet('/api/product-by-tenders/' + product_id)
                    .then(data => {
                        let dataproduct = data;
                        this.tenderOld = data[0];
                        this.tendersCost.min = 0;
                        this.selectedTags = []

                        data.forEach((tender, i) => {

                            let tag_load_checker = true;
                            if (this.selectedTags.length != 0) {
                                for (let j = 0; j < this.selectedTags.length; j++) {

                                    if (this.selectedTags[j].id == tender.tag_id) {

                                        tag_load_checker = false;
                                        break;
                                    }
                                }
                            } else {
                                this.selectedTags[this.selectedTags.length] = {

                                    id: tender.tag_id,

                                    name: tender.tag_name

                                };
                                tag_load_checker = false;
                            }

                            if (tag_load_checker) {

                                this.selectedTags[this.selectedTags.length] = {

                                    id: tender.tag_id,

                                    name: tender.tag_name

                                };
                            }

                            this.actual_cost += Math.ceil(Number(tender.actual_cost));

                            this.budgeted_cost += Math.ceil(Number(tender.budgeted_cost));

                            if (this.actual_year == Number(tender.delivery_year)) {

                                this.actual_year_cost += Math.ceil(Number(tender.budgeted_cost));

                            }

                            if ((this.actual_year - 1) == Number(tender.delivery_year)) {

                                this.old_year_cost += Math.ceil(Number(tender.budgeted_cost));

                            }

                            if ((this.actual_year + 1) == Number(tender.delivery_year)) {

                                this.next_year_cost += Math.ceil(Number(tender.budgeted_cost));

                            }

                        })



                        this.spending_cost = Math.ceil(((this.next_year_cost/this.old_year_cost)-1) * 100);

                        console.log('((',this.next_year_cost,'/',this.old_year_cost,')-1)*100 = ', this.spending_cost);

                        data = data.sort(function (a, b) {
                            return b.budgeted_cost - a.budgeted_cost;
                        });

                        this.tendersCost.max = Math.ceil(data[0].budgeted_cost/1000);
                        this.tendersCost.min = Math.ceil(data[data.length - 1].budgeted_cost/1000);;
                        this.tendersCost.value = [this.tendersCost.min, this.tendersCost.max];

                        data = data.sort(function (a, b) {

                            return b.delivery_year - a.delivery_year;

                        });
                        let delivery_year = Math.ceil(data[data.length - 1].delivery_year);

                        if (this.actual_year > delivery_year && delivery_year != 0) {

                            this.usedYears = this.actual_year - delivery_year + 1;

                        } else {

                            this.usedYears = 1;

                        }

                        this.filterTagToChart();
                    });
            },

            setTabActive: function (tabName) {
                this.activeTab = tabName;
                if (tabName == 'tender') {
                    this.showTenderCost = true;
                } else {
                    this.showTenderCost = false;
                }

            },

            getTendersPaginate: function (product_id) {

                let url = '/api/product-by-tenders-paginated/' + product_id + '?page=' + this.pagination.currentPage + this.composeQueryUrl();

                this.httpGet(url)
                    .then(data => {
                        this.tendersTotal = data.total;
                        this.tendersList = data.data;
                    });
                this.exportToExcel(product_id);
            },

            applyFilters: function (isOnlySortingChanged) {

                this.appliedFilters.isOnlySortingChanged = !!isOnlySortingChanged;


                this.composeQueryUrl();

                // this.$router.push('/address-details/'+this.addressId+'?'+this.queryUrl);
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
                this.pagination.currentPage = pageNumber;
                this.getTendersPaginate(this.productId);
            },

            composeQueryUrl: function () {
                let queryStr = '';

                if (this.appliedFilters.tendersSearchInput) {
                    queryStr += '&tenders-search=' + this.appliedFilters.tendersSearchInput;

                }

                if (this.appliedFilters.tags.length) {

                    if (typeof this.appliedFilters.tags === 'string') {
                        queryStr += '&tag-cons[]=' + this.appliedFilters.tags;
                    }
                    else {
                        this.appliedFilters.tags.forEach(id => {
                            queryStr += '&tag-cons[]=' + id;
                            console.log(queryStr);
                        });
                    }
                }

                if (this.appliedFilters.sortBy) {
                    queryStr += '&sort-by=' + this.appliedFilters.sortBy;
                }

                if (this.appliedFilters.sortCost.length) {
                    queryStr += '&min=' + (this.appliedFilters.sortCost[0]*1000) + '&max=' + (this.appliedFilters.sortCost[1]*1000);
                }

                this.queryUrl = queryStr;
                // console.log(' this.queryUrl' , this.queryUrl);
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

            loadTagsFilter: function () {
                return this.httpGet('/api/product-load-tags')
                    .then(data => {
                        this.tag_list = data;
                    })
            },

            exportToExcel: function (product_id) {

                let url = '/api/product-by-tenders-to-excel/' + product_id + '?' + this.composeQueryUrl();
                this.httpGet(url)
                    .then(data => {
                        this.tendersExport.json_data = data;
                    });
            },

            filterTagToChart: function () {
                this.httpGet('/api/product-by-tenders/' + this.productId)
                    .then(data => {

                        var DATA = [[]];
                        if (data.length != 0) {

                            let graf_data = {
                                title: []
                            };
                            graf_data.title.push('Month')
                            graf_data.title.push('Total')

                            for (let j = 0; j < this.selectedTags.length; j++) {

                                if (String(this.selectedTags[j].name) != 'null') {

                                    let tags = String(this.selectedTags[j].name);

                                    graf_data.title.push(tags);

                                }
                            }

                            let k = 0;

                            data.forEach((tender, i) => {

                                let isTagId = false;

                                let date_tender = moment(new Date(tender.tender_date)).format('MMM-YY');

                                let tag = String(tender.tag_name);

                                if (typeof graf_data[date_tender] == "undefined") {
                                    graf_data[date_tender] = [];
                                }

                                for (let j = 0; j < this.selectedTags.length; j++) {

                                    k = 0;

                                    if (String(this.selectedTags[j].name) != 'null') {

                                        if (this.selectedTags[j].id == tender.tag_id) {

                                            isTagId = true;

                                            graf_data[date_tender].push({[tag]: tender.budgeted_cost});

                                            k++;
                                        }
                                    }
                                }

                            });

                            for (let i = 0; i < graf_data.title.length; i++) {

                                DATA[0].push(graf_data.title[i]);
                            }


                            let key_graf = 1;
                            let tagsum = [];
                            let total = 0;
                            for (var key in graf_data) {

                                if (key != 'title' && graf_data[key].length != 0) {

                                    DATA.push([key]);

                                    for (let i = 2; i < graf_data.title.length; i++) {

                                        let tagsum_tmp = 0;

                                        for (let j = 0; j < graf_data[key].length; j++) {

                                            if (typeof graf_data[key][j][graf_data.title[i]] != "undefined") {

                                                total += Math.ceil(Number(graf_data[key][j][graf_data.title[i]]));

                                                tagsum_tmp += Math.ceil(Number(graf_data[key][j][graf_data.title[i]]));

                                            } else {

                                                tagsum_tmp += 0;

                                            }

                                        }

                                        tagsum.push(tagsum_tmp);
                                        tagsum_tmp = 0;

                                    }
                                    DATA[key_graf].push(total);
                                    for (let s = 0; s < tagsum.length; s++) {
                                        DATA[key_graf].push(tagsum[s]);
                                    }
                                    tagsum = [];
                                    total = 0;
                                    key_graf++;
                                }

                            }
                            setTimeout(() => {

                                if (typeof DATA[1] != "undefined") {
                                    setTimeout(() => {
                                        this.viewTendersChart(DATA);
                                    }, 100)
                                } else {
                                    DATA[0] = ['Month', 'Total'];
                                    DATA[1] = ['Yan-97', 0];
                                    setTimeout(() => {
                                        this.viewTendersChart(DATA);
                                    }, 100)
                                }
                            }, 300)
                        }
                    });
            },

            viewTendersChart: function (data) {

                if (this.graphLoadedModal) {
                    return;
                }
                $('#tender-charts').html('');

                var data = google.visualization.arrayToDataTable(data);

                var options = {
                    title: 'Sales',
                    vAxis: {title: 'Budget'},
                    hAxis: {baselineColor: 'none', ticks: []},
                    seriesType: 'bars',
                    legend: 'none',
                    series: {0: {type: 'line'}}
                };

                var chart = new google.visualization.ComboChart(document.getElementById('tender-charts'));
                chart.draw(data, options);
                this.graphLoadedModal = true;

            },

            loadGoogleChart: function () {
                return google.charts.load('current', {'packages': ['corechart']})
                    .then(() => {
                        this.isGoogleChartCoreLoaded = true;
                    })
            }

        },

        mounted: function () {
            this.$eventGlobal.$on('showModalProductDetails', (data) => {
                this.init(data.addressId, data.purchaseId, data.address);
                this.activeTab = 'chart';
                this.tendersCost.value = [];
            });

            this.loadGoogleChart();
        }
    }
</script>

<style scoped>

</style>