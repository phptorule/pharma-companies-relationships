<template>
    <div>
        <div class="modal fade" id="product-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <div class="person-profile-picture">
                            <span v-if="!productsData.image" class="person-initials">{{productsData.image && productsData.image !== '/storage' ? "":getProductName(productsData.name? productsData.name : productsData.company)}}</span>
                            <img :src="productsData.image && productsData.image !== '/storage' ? productsData.image : '/images/mask-0.png'" alt="">
                        </div>
                        <h4 class="modal-title">
                            {{productsData.name? productsData.name : "unspecified "+productsData.company+"-product"}}
                            <a href="#"><i class="fa fa-pencil"></i></a>
                        </h4>

                        <p class="occupation">{{productsData.description}}</p>

                        <p class="place-of-work">
                            at <a href="#">{{currentAddress.name}}</a>
                        </p>

                        <div class="row person-experience">
                            <div class="col-md-4">
                                <p class="number">
                                    {{tenderData.years_used ? tenderData.years_used : ''}}
                                </p>

                                <p class="text">
                                    <span><i class="fa fa-calendar"></i></span> Used for years
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="number" v-if="tenderData.max_total_spent">
                                    {{Math.ceil(tenderData.max_total_spent/1000) | currency}}(K)&nbsp;<i style="margin-top: 3px;" class="fa fa-ruble"
                                                                           title="Russian rubels"></i>
                                </p>
                                <p class="number" v-else>
                                    0 <span> <i class="fa fa-ruble" title="Russian rubels"></i></span>
                                </p>
                                <p class="text">
                                    Tot spent
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="number" v-if="tenderData.next_budgeted_cost">
                                    {{Math.ceil(tenderData.next_budgeted_cost/1000) | currency}}(K)&nbsp;<i style="margin-top: 3px;" class="fa fa-ruble"
                                                                                                       title="Russian rubels"></i>
                                </p>
                                <p class="number" v-else>
                                    0 <span> <i class="fa fa-ruble" title="Russian rubels"></i></span>
                                </p>
                                <p class="text">
                                    <span><i class="fa fa-arrow-circle-up"></i></span> Projected spending
                                    {{actual_year}}
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
                                <div class="loader-spinner hidden"></div>
                                <div class="tender-chart-container" :class="{hidden: activeTab !== 'chart'}"
                                     @click="viewTendersChart()">
                                    <div class="tag-list">
                                        <div class="item-tag" v-for="tag in productTags" :class="{hidden: isHideOthersTag && tag.id === 'empty'}">
                                            <input type="checkbox" id="checkbox"  v-bind:value="tag" v-model="selectedTags">
                                            <label :style="{color: tag.color}">{{tag.name}}</label>
                                        </div>
                                    </div>
                                    <div class="tender-chart" id="tender-charts"></div>
                                </div>

                                <div :class="{hidden: activeTab !== 'tender'}">
                                    <tender-list-partial
                                            :initalParams="tenderListParams"
                                            :isListVisible="activeTab == 'tender'"
                                            :tagList="tag_list"
                                            :shouldHideProductTags="true"
                                    ></tender-list-partial>
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
    import helpers from '../mixins/helpers';
    import getProductName from '../mixins/get-product-name';
    import ProductModal from '../mixins/show-product-details-modal';
    import vueSlider from 'vue-slider-component';
    import labscapeFilters from '../filters/labscape-filters';


    const othersTag = {
        id: 'empty',
        color: '#666666',
        name: 'Others',
    };

    export default {
        mixins: [http, helpers, getProductName, ProductModal, labscapeFilters],
        data: function () {
            return {

                tenderListParams: {},

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
                productTags: [],

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

        components: {
            vueSlider
        },

        watch: {

            selectedTags(tagVal) {

                this.chartQueryTag = '';

                if (tagVal.length) {

                    tagVal.forEach(tag => {

                        if (tag.name != null) {

                            if(tag.id === 'empty') {
                                return;
                            }

                            this.chartQueryTag += '&tags[]=' + tag.id;
                        }

                    })

                }

                if(tagVal.findIndex(el => el.id === 'empty') !== -1) {
                    this.chartQueryTag += '&tags[]=empty';
                }

                this.graphLoadedModal = false;
                this.filterTagToChart();
            },

            show(val) {
                if (val) {

                    this.$nextTick(() => this.$refs.sortCost.refresh());
                }
            },

            isGoogleChartCoreLoaded: function (newVal) {
                if ($('#product-modal').hasClass('in') && newVal && this.activeTab == 'chart') {
                    this.viewTendersChart(DATA);
                }
            }
        },

        methods: {
            init: function (addressId, productId, address) {
                $('#product-modal').modal('show');

                this.addressId = addressId;
                this.productId = productId;
                this.currentAddress = address;

                let url = '/api/product-by-id/' + productId;
                this.httpGet(url)
                    .then(data => {
                        this.productsData = data;
                    });

                this.getTendersByProduct(this.productId);

                this.loadAvailableTags();
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
                            this.productTags.push(othersTag);
                            this.selectedTags.push(othersTag);
                        }

                        this.tendersCost.max = Math.ceil(this.tenderData.max_total_spent / 1000)+1;

                        this.tendersCost.min = Math.ceil(this.tenderData.min_total_spent / 1000)-1;

                        if(this.tendersCost.min === -1) {
                            this.tendersCost.min = 0;
                        }

                        this.tendersCost.value = [this.tendersCost.min, this.tendersCost.max];

                    });
            },

            setTabActive: function (tabName) {
                this.activeTab = tabName;

                this.showTenderCost = tabName == 'tender';
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

            tempRemoveTotalFromGraph: function(graphData) {
                graphData.forEach(el => {
                    el.splice(1,1);
                })
            },

            filterTagToChart: function () {
                
                var url = '/api/tenders-by-product-chart/' + this.productId + '/' + this.addressId + '?' + this.chartQueryTag;

                this.httpGet(url)
                    .then(data => {

                        var DATA = data.chartsData;

                        this.checkIfNoSpentOnOthers(DATA);

                        var title = ['Month', 'Total',{type: 'string', role: 'tooltip', 'p': {'html': true}}];

                        //todo: temp override title for excluding "Total"
                        title = ['Month', {type: 'string', role: 'tooltip', 'p': {'html': true}}];

                        //todo: temp override DATA for excluding "Total"
                        this.tempRemoveTotalFromGraph(DATA);

                        var colorPallette = [];

                        if (this.selectedTags.length > 0) {

                            this.tag_list.forEach(tag => {

                                this.selectedTags.forEach(selectTag => {

                                    if (tag.id == selectTag.id) {

                                        if(this.isHideOthersTag && selectTag.id == 'empty') {
                                            return;
                                        }

                                        title.push(tag.name);

                                        colorPallette.push(tag.color);
                                    }

                                });

                            });
                        }

                        DATA.unshift(title);

                        if (typeof DATA[1] != "undefined") {
                            this.viewTendersChart(DATA, colorPallette, data.delimetrKey, data.singleChart);
                            
                        } else {
                            DATA[0] = ['Month', 'Total'];
                            DATA[1] = ['Yan-97', 0];
                            this.viewTendersChart(DATA, colorPallette, data.delimetrKey, data.singleChart);
                            
                        }

                    });
            },

            viewTendersChart: function (data, colorPallette, delimetrKey, singleChart) {

                if (this.graphLoadedModal) {
                    return;
                }

                // chear chart
                $('#tender-charts').html('');

                //todo: temp override colorPallette for excluding "Total"
                // colorPallette.unshift('#0099c6');

                var options = {
                    width: 830,
                    height: 200,
                    title: 'Sales',
                    colors: colorPallette,
                    tooltip: {isHtml: true},
                    vAxis: {title: 'Budget', format: "###,###"+delimetrKey,},
                    hAxis: {baselineColor: 'none', ticks: []},
                    legend: 'none',
                    animation: {startup: true},
                };

                var data = google.visualization.arrayToDataTable(data);

                if(singleChart == true)
                {
                    options.legend = 'bottom';
                }

                var chart = new google.visualization.LineChart(document.getElementById('tender-charts'));

                chart.draw(data, options);

                this.graphLoadedModal = true;
            },

            loadGoogleChart: function () {
                return google.charts.load('current', {'packages': ['corechart']})
                    .then(() => {
                        this.isGoogleChartCoreLoaded = true;
                    })
            },

            loadAvailableTags: function () {
                this.httpGet('/api/get-used-consumables-by-address-and-product?address_id='+this.addressId+'&product_id='+this.productId)
                    .then(data => {

                        this.tag_list = data;

                        this.tag_list.push(othersTag);
                    })
            }

        },

        mounted: function () {
            this.$eventGlobal.$on('showModalProductDetails', (data) => {

                this.tenderListParams = data;

                this.init(data.addressId, data.purchaseId, data.address);
                this.activeTab = 'chart';
                this.tendersCost.value = [];
            });

            this.loadGoogleChart();
        },

        beforeDestroy: function () {
            this.$eventGlobal.$off('showModalProductDetails');
        }
    }
</script>