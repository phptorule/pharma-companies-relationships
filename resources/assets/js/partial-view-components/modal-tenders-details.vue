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

                                <div class="chart-container">
                                    <div id="address-products-chart"></div>
                                </div>

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

    import http from '../mixins/http';

    export default {

        mixins: [http],

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
                isGoogleChartCoreLoaded: false
            }
        },

        computed: {
            usedProductOptionsForDropDown: function () {
                return this.productList.map(product => {
                    return {
                        label: product.company + (product.name? ': ' + product.name : ': unspecified product'),
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
                //ajax query will be here
                console.log('queryUrl', this.queryUrl);

                this.httpGet('/api/tenders/' + this.addressId + '/products-graph' +  this.queryUrl)
                    .then((data) => {

                        this.drawChart(data);
                    });
            },

            loadGoogleChartCore: function () {
                return google.charts.load('current', {'packages': ['corechart']})
                    .then(() => {
                        this.isGoogleChartCoreLoaded = true;
                    })
            },

            drawChart: function (data, delimetrKey, singleChart) {
                // if (this.graphLoadedModal) {
                //     return;
                // }

                $('#address-products-chart').html('');

                let options = {
                    width: 830,
                    height: 200,
                    title: 'Sales',
                    // colors: colorPallette,
                    tooltip: {isHtml: true},
                    vAxis: {title: 'Budget', format: "###,###"},
                    hAxis: {baselineColor: 'none', ticks: []},
                    legend: 'bottom',
                    animation: {startup: true},
                };

                let visualizationData = google.visualization.arrayToDataTable(data);

                let chart = new google.visualization.LineChart(document.getElementById('address-products-chart'));

                chart.draw(visualizationData, options);

                // this.graphLoadedModal = true;
            }
        },

        mounted: function () {
            this.$eventGlobal.$on('showModalTenderDetails', (data) => {

                this.resetFilters();

                this.tenderListParams = data;
                this.addressId = data.addressId;

                $('#tenders-modal').modal('show');

                this.fetchListOfAddressProducts()
                    .then(data => {
                        this.markAllProductItemsAsSelected();

                        this.applyFilters();
                    })
            });

            this.loadGoogleChartCore();
        }
    }
</script>

<style scoped>

</style>