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

                                <div class="filters">

                                    <div class="row">

                                        <div class="col-md-12">

                                            <div class="filter-container">

                                                <div class="pull-left filter-label">
                                                    Used Products:
                                                </div>

                                                <multiple-dropdown-select
                                                        class="form-control select-filter pull-left"
                                                        :name="'Used Products'"
                                                        :options="usedProductOptionsForDropDown"
                                                        :selected="appliedFilters.usedProducts"
                                                        @changed="applyUsedProductsFilter"
                                                        ref="productsMultipleDropdownSelect"
                                                        style="max-width: 200px"
                                                ></multiple-dropdown-select>

                                                <div class="pull-left filter-label include-others-label-box">
                                                    <div class="grey-checkbox">
                                                        <label>
                                                            <input type="checkbox"
                                                                   @click="includeOthersProduct()"
                                                                   :checked="isOthersIncluded"
                                                            >
                                                            <span class="borders"></span>
                                                            <span class="remember_text">Include other (non-specified) products</span>
                                                        </label>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>

                                    </div>
                                </div>

                                <div class="chart-container">
                                    <div v-if="isGraphLoading" class="loading-header">
                                        <h3>Loading...</h3>
                                    </div>
                                    <div v-if="isFilterEmpty" class="loading-header">
                                        <h3>Please apply any filter</h3>
                                    </div>
                                    <div id="address-products-chart"></div>
                                </div>

                            </div>

                            <div :class="{hidden: activeTab !== 'tender'}">
                                <tender-list-partial
                                        :initalParams="tenderListParams"
                                        :isListVisible="activeTab == 'tender'"
                                        :tagList="tag_list"
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

    import http from '../mixins/http';
    import helpers from '../mixins/helpers';

    export default {

        mixins: [http, helpers],

        data: function () {
            return {
                tenderListParams: {},
                addressId: null,
                activeTab: 'chart',
                productList: [],

                appliedFilters: {
                    usedProducts: []
                },

                queryUrl: '',
                isOthersIncluded: true,
                isGoogleChartCoreLoaded: false,
                isGraphLoading: true,
                isFilterEmpty: false,
                chartWidth: null,
                tag_list: []
            }
        },

        computed: {
            usedProductOptionsForDropDown: function () {

                let colors = JSON.parse(JSON.stringify(this.GOOGLE_CHART_DEFAULT_COLORS));

                colors.shift();

                return this.productList.map((product, i) => {

                    let icon = `<i class="oval" style="background-color: ${colors[i]}"></i> `;

                    return {
                        label: icon + product.company + (product.name? ': ' + product.name : ': unspecified product'),
                        value: product.id
                    }
                })
            }
        },

        methods: {
            setTabActive: function (name) {
                this.activeTab = name;
            },

            fetchListOfAddressProducts: function () {
                return this.httpGet('/api/get-product-list-by-address/'+this.addressId)
                    .then(data =>{

                        this.productList = JSON.parse(JSON.stringify(data));

                        return data;
                    })
            },

            markAllProductItemsAsSelected: function () {
                this.productList.forEach(el => {
                    this.appliedFilters.usedProducts.push(el.id);
                })
            },

            resetFilters: function () {
                this.appliedFilters = {
                    usedProducts: []
                };
            },

            applyUsedProductsFilter: function (data) {
                this.appliedFilters.usedProducts = data;

                this.applyFilters();
            },

            includeOthersProduct: function() {
                this.isOthersIncluded = !this.isOthersIncluded;

                this.applyFilters();
            },

            applyFilters: function () {
                this.composeQueryUrl();

                this.loadGraphData();
            },

            composeQueryUrl: function () {
                this.queryUrl = '?';

                this.queryUrl += 'include-others=' + (+this.isOthersIncluded);

                if(this.appliedFilters.usedProducts.length) {
                    this.queryUrl += '&used-products=';
                    this.appliedFilters.usedProducts.forEach((id,i) => {
                        this.queryUrl += id;
                        if(i+1 < this.appliedFilters.usedProducts.length) {
                            this.queryUrl += ',';
                        }
                    })
                }

            },

            loadGraphData: function () {

                this.httpGet('/api/tenders/' + this.addressId + '/products-graph' +  this.queryUrl)
                    .then((data) => {

                        this.isGraphLoading = true;

                        if(this.chartWidth) {
                            this.drawChart(data.chart_data, data.delimiter_key);
                        }
                        else {
                            setTimeout(()=>{
                                this.drawChart(data.chart_data, data.delimiter_key);
                            },200);
                        }

                    });
            },

            loadGoogleChartCore: function () {
                return google.charts.load('current', {'packages': ['corechart']})
                    .then(() => {
                        this.isGoogleChartCoreLoaded = true;
                    })
            },

            defineChartColors: function(chartData) {
                let allColors = JSON.parse(JSON.stringify(this.GOOGLE_CHART_DEFAULT_COLORS));

                let usedColors = [allColors[0]];

                this.appliedFilters.usedProducts.forEach(id => {

                    let i = this.productList.findIndex(el => el.id == id);

                    usedColors.push(allColors[i+1]);
                });

                if(chartData[0].indexOf('Other') === -1) {
                    usedColors.shift();
                }

                return usedColors;
            },

            drawChart: function (data, delimiterKey) {

                $('#address-products-chart').html('');

                if(data[0].length === 1) {
                    this.isFilterEmpty = true;
                    this.isGraphLoading = false;
                    return;
                }
                else {
                    this.isFilterEmpty = false;
                }

                let colors = this.defineChartColors(data);

                let options = {
                    width: this.chartWidth,
                    height: 400,
                    title: 'Sales',
                    colors: colors,
                    tooltip: {isHtml: true},
                    vAxis: {title: 'Budget', format: "###,###"+delimiterKey},
                    hAxis: {baselineColor: 'none', ticks: []},
                    legend: 'none',
                    animation: {startup: true},
                    chartArea:{
                        right:40,
                        width:"85%",
                    }
                };

                let visualizationData = google.visualization.arrayToDataTable(data);

                setTimeout(()=>{
                    this.isGraphLoading = false;

                    let chart = new google.visualization.LineChart(document.getElementById('address-products-chart'));

                    chart.draw(visualizationData, options);
                }, 0);
            },

            defineChartWidth: function () {
                this.chartWidth = $('#tenders-modal .modal-body').width();
            },

            loadAvailableTags: function () {
                this.httpGet('/api/get-used-consumables-by-address/'+this.addressId)
                    .then(data => {

                        this.tag_list = data;
                    })
            }
        },

        mounted: function () {
            this.$eventGlobal.$on('showModalTenderDetails', (data) => {

                this.resetFilters();

                this.tenderListParams = data;
                this.addressId = data.addressId;

                this.loadAvailableTags();

                $('#tenders-modal').modal('show');

                this.fetchListOfAddressProducts()
                    .then(data => {
                        this.markAllProductItemsAsSelected();

                        this.applyFilters();
                    });

                setTimeout(()=>{
                    this.defineChartWidth();
                },200)
            });

            this.loadGoogleChartCore();
        }
    }
</script>

<style scoped>

</style>