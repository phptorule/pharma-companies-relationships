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
                        <div>
                            <div class="tab-content">
                                <div class="loader-spinner hidden"></div>
                                <div class="activeTab">
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
                                                <i class="fa fa-file-excel-o fa-2x" @click="exportToExcel()"
                                                   title="Export to excel"></i>
                                            </download-excel>
                                            <download-excel
                                                    class="export-to-csv"
                                                    :data="tendersExport.json_data"
                                                    :fields="tendersExport.json_fields"
                                                    type="csv"
                                                    name="tenders.csv"
                                            >
                                                <img @click="exportToExcel()" src="/images/csv.png"
                                                     title="Export to csv">
                                            </download-excel>
                                        </div>
                                    </div>
                                    <div class="col-md-12 tender-data">
                                        <ul class="col-md-12 tenders-list">
                                            <li v-for="(tender, i) in tendersList">
                                                <div class="item">
                                                    <h3 v-if="tender.purchase_name" :title="tender.purchase_name">
                                                        {{tender.tender_date}} -
                                                        {{tender.purchase_name | tendername(55)}}</h3>
                                                    <div class="tender-volume">{{Math.ceil(Number(tender.budget)) |
                                                        currency('Rub') }}
                                                    </div>

                                                    <ul class="tag-list">
                                                        <li v-if="tender.tag_name"><a href="javascript:void(0)"
                                                                                      class="tags">{{tender.tag_name}}</a>
                                                        </li>
                                                    </ul>

                                                    <p class="tender-winner" v-if="tender.budget">
                                                        Winner {{tender.suppliers_name}} of most money
                                                        {{tender.suppliers_amount | currency('Rub') }}
                                                        <a target="_blank" :href="tender.tender_url"><img
                                                                data-v-6d155616="" src="/images/graph/external_link.svg"
                                                                class="tenderUrlIcon"></a>
                                                    </p>
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
    import vueSlider from 'vue-slider-component';


    export default {
        mixins: [http],
        data: function () {
            return {
                tendersCost: {
                    value: [],
                    min: null,
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
                tag_list: [],
                selectedTags: [],
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
                tendersList: [],
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
                },
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

        filters: {
            currency: function (value, currency_type) {
                if (!currency_type) {
                    currency_type = '';
                }
                if (value) {
                    value = String(value);
                    return value.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ') + ' ' + currency_type;
                }
                return '';
            },

            tendername: function (name, size) {

                if (name.length > size) {

                    return name.slice(0, size) + ' ...';

                } else {

                    return name;

                }
            }
        },

        created: function () {

            this.loadTagsFilter();
        },

        components: {
            vueSlider
        },

        watch: {

            tendersCost: {
                handler: function () {

                    this.filterCost();

                },
                deep: true
            },

            $route: function (to) {

                if ($('#tenders-modal').hasClass('in')) {

                    this.initFilters();

                    this.composeQueryUrl();

                    this.$refs.paginationDirective.setPage(1);
                }

            },

        },

        methods: {
            init: function (addressId) {

                $('#tenders-modal').modal('show');

                this.addressId = addressId;

            },

            getTendersPaginate: function (addressId) {

                this.showLoader();

                let url = '/api/tenders-by-address-paginated/' + addressId + '?page=' + this.pagination.currentPage + this.composeQueryUrl();
                this.httpGet(url)
                    .then(data => {
                        this.hideLoader();
                        this.tendersTotal = data.total;
                        this.tendersList = data.data;
                        if(this.tendersCost.max == null && this.tendersCost.min == null){
                            this.tendersCost.max = Math.ceil(this.tendersList[0].max_budgeted / 1000);

                            this.tendersCost.min = Math.ceil(this.tendersList[0].min_budgeted / 1000);

                            this.tendersCost.value = [this.tendersCost.min, this.tendersCost.max];

                        }

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

            applyCostFilter: function (data) {
                this.appliedFilters.sortCost = data;
                this.applyFilters(true);
            },

            applySortFilter: function (data) {
                this.appliedFilters.sortBy = data;
                this.applyFilters(true);
            },

            pageChanged: function (pageNumber) {
                this.pagination.currentPage = pageNumber;
                if ($('#tenders-modal').hasClass('in')) {
                    this.getTendersPaginate(this.addressId);
                }
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
                        });
                    }
                }

                if (this.appliedFilters.sortBy) {
                    queryStr += '&sort-by=' + this.appliedFilters.sortBy;
                }

                if (typeof(this.appliedFilters.sortCost[0]) != 'undefined') {
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

            loadTagsFilter: function () {
                return this.httpGet('/api/product-load-tags')
                    .then(data => {

                        this.tag_list = data;

                    })
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

            exportToExcel: function () {
                this.showLoader();
                let url = '/api/tenders-by-address-to-excel/' + this.addressId + '?' + this.composeQueryUrl();
                this.httpGet(url)
                    .then(data => {
                        this.hideLoader();
                        this.tendersExport.json_data = data;
                    });
            },

            showLoader: function () {
                $('.loader-spinner').removeClass('hidden');
            },

            hideLoader: function () {
                $('.loader-spinner').addClass('hidden');
            },

        },

        mounted: function () {
            this.$eventGlobal.$on('showModalTenderDetails', (data) => {

                this.init(data.addressId);

                this.tendersCost.value = [];
            });

        }
    }
</script>

<style scoped>

</style>