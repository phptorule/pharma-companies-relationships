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

    // const TEST_DATA = [["Month","Total",{"type":"string","role":"tooltip","p":{"html":true,"role":"tooltip"}},"Test","Reagent","Cartridge","Others"],["01/06/2017",1958.05504,"<span class=\"tooltip-total\">01/06/2017<br>Total: 1,958K</span>",0,0,0,1958],["02/11/2017",542.39516,"<span class=\"tooltip-total\">02/11/2017<br>Total: 542K</span>",542,0,0,0],["03/11/2017",2130,"<span class=\"tooltip-total\">03/11/2017<br>Total: 2,130K</span>",0,0,0,2130],["05/05/2017",8931.99357,"<span class=\"tooltip-total\">05/05/2017<br>Total: 8,932K</span>",0,0,0,8931],["07/04/2017",3442.2048,"<span class=\"tooltip-total\">07/04/2017<br>Total: 3,442K</span>",0,0,0,2977],["10/04/2017",1770.274,"<span class=\"tooltip-total\">10/04/2017<br>Total: 1,770K</span>",0,0,0,1770],["10/05/2017",299.2,"<span class=\"tooltip-total\">10/05/2017<br>Total: 299K</span>",299,0,0,0],["11/04/2017",32387.279,"<span class=\"tooltip-total\">11/04/2017<br>Total: 32,387K</span>",0,0,0,32387],["12/05/2017",9423.2,"<span class=\"tooltip-total\">12/05/2017<br>Total: 9,423K</span>",0,0,0,9423],["13/06/2017",271.02124,"<span class=\"tooltip-total\">13/06/2017<br>Total: 271K</span>",0,0,0,271],["14/04/2017",2733.847,"<span class=\"tooltip-total\">14/04/2017<br>Total: 2,734K</span>",2733,0,0,0],["14/11/2017",662.495,"<span class=\"tooltip-total\">14/11/2017<br>Total: 662K</span>",662,0,0,0],["16/05/2017",1382.15754,"<span class=\"tooltip-total\">16/05/2017<br>Total: 1,382K</span>",0,0,0,2016],["16/06/2017",604.26,"<span class=\"tooltip-total\">16/06/2017<br>Total: 604K</span>",604,0,0,0],["17/04/2017",2711.33268,"<span class=\"tooltip-total\">17/04/2017<br>Total: 2,711K</span>",4767,0,0,4767],["18/08/2017",1555.81958,"<span class=\"tooltip-total\">18/08/2017<br>Total: 1,556K</span>",1555,0,0,0],["20/03/2017",684.0921999999999,"<span class=\"tooltip-total\">20/03/2017<br>Total: 684K</span>",684,0,0,0],["23/11/2017",663.925,"<span class=\"tooltip-total\">23/11/2017<br>Total: 664K</span>",663,0,0,0],["24/04/2017",3213.71896,"<span class=\"tooltip-total\">24/04/2017<br>Total: 3,214K</span>",1771,1771,0,1771],["25/05/2017",2234.6308,"<span class=\"tooltip-total\">25/05/2017<br>Total: 2,235K</span>",2096,0,0,3710],["27/04/2017",2709.4638999999997,"<span class=\"tooltip-total\">27/04/2017<br>Total: 2,709K</span>",2108,0,0,3310],["27/06/2017",765.17174,"<span class=\"tooltip-total\">27/06/2017<br>Total: 765K</span>",0,0,0,156],["27/10/2017",1848.415,"<span class=\"tooltip-total\">27/10/2017<br>Total: 1,848K</span>",0,0,0,1848],["27/11/2017",4814.6388799999995,"<span class=\"tooltip-total\">27/11/2017<br>Total: 4,815K</span>",4814,0,4814,4814],["28/04/2017",2570.4997999999996,"<span class=\"tooltip-total\">28/04/2017<br>Total: 2,570K</span>",598,0,0,4542],["28/09/2017",282.0079,"<span class=\"tooltip-total\">28/09/2017<br>Total: 282K</span>",282,0,0,0],["28/12/2017",2053.1126,"<span class=\"tooltip-total\">28/12/2017<br>Total: 2,053K</span>",2053,0,0,0],["29/05/2017",1981.545,"<span class=\"tooltip-total\">29/05/2017<br>Total: 1,982K</span>",0,0,0,1981],["30/10/2017",714.0624300000001,"<span class=\"tooltip-total\">30/10/2017<br>Total: 714K</span>",714,0,0,0]];
    const TEST_DATA = [["Month","Total","Test","Reagent","Cartridge","Others"],["01/06/2017",1958.05504,0,0,0,1958],["02/11/2017",542.39516,542,0,0,0],["03/11/2017",2130,0,0,0,2130],["05/05/2017",8931.99357,0,0,0,8931],["07/04/2017",3442.2048,0,0,0,2977],["10/04/2017",1770.274,0,0,0,1770],["10/05/2017",299.2,299,0,0,0],["11/04/2017",32387.279,0,0,0,32387],["12/05/2017",9423.2,0,0,0,9423],["13/06/2017",271.02124,0,0,0,271],["14/04/2017",2733.847,2733,0,0,0],["14/11/2017",662.495,662,0,0,0],["16/05/2017",1382.15754,0,0,0,2016],["16/06/2017",604.26,604,0,0,0],["17/04/2017",2711.33268,4767,0,0,4767],["18/08/2017",1555.81958,1555,0,0,0],["20/03/2017",684.0921999999999,684,0,0,0],["23/11/2017",663.925,663,0,0,0],["24/04/2017",3213.71896,1771,1771,0,1771],["25/05/2017",2234.6308,2096,0,0,3710],["27/04/2017",2709.4638999999997,2108,0,0,3310],["27/06/2017",765.17174,0,0,0,156],["27/10/2017",1848.415,0,0,0,1848],["27/11/2017",4814.6388799999995,4814,0,4814,4814],["28/04/2017",2570.4997999999996,598,0,0,4542],["28/09/2017",282.0079,282,0,0,0],["28/12/2017",2053.1126,2053,0,0,0],["29/05/2017",1981.545,0,0,0,1981],["30/10/2017",714.0624300000001,714,0,0,0]];

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
                        console.log('data', data);
                    });

                this.drawChart();
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

                let visualizationData = google.visualization.arrayToDataTable(TEST_DATA);

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