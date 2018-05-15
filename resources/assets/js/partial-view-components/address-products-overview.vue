<template>
    <div>
        <ul class="staff-list" v-if="addressData.products">
            <div class="load-spinner-product"></div>
            <li v-if="i < 3" v-for="(product, i) in topProducts">
                <div class="image">
                    <a href="javascript:void(0)" @click="showProductDetailsModal(addressId, product.id, addressData)">
                        <span class="person-initials">{{getProductName(product.name? product.name : product.company)}}</span>
                        <img :src="product.image? product.image : '/images/mask-0.png'" alt="">
                    </a>
                </div>
                <div class="prod-info">
                    <p class="name">
                        <a href="javascript:void(0)"
                           @click="showProductDetailsModal(addressId, product.id, addressData)">
                            {{product.name? product.name : "unspecified "+product.company+"-product"}}
                        </a>
                    </p>
                    <div class="amount">
                        <div class="volume">
                            <span class="volume-head">
                                {{product.volume  | currency('pcs.')}}
                                <span class="volume-title">Est. Test Volume</span>
                            </span>
                        </div>
                        <div class="spending">
                            <span class="spending-head">
                                {{Math.ceil(product.total_spent/1000) | currency('Rub')}} (K)
                                <span class="spending-title">2y Spending</span>
                            </span>
                        </div>
                        <div class="last-tender">
                                <span class="last-tender-head">
                                    {{product.last_tender_date ? product.last_tender_date : ''}}
                                <span class="last-tender-title">Last Tender</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="prod-graf" :id="'graph-container-'+i" style="width: 75px; height: 50px">
                    <div class="load-spinner-charts-product"></div>
                </div>
            </li>
            <li>
                <a href="javascript:void(0)"
                   v-if="addressData.products && addressData.products.length >= 3"
                   @click="showSlidedBox('all-products')"
                   class="address-box-show-more-link show-all-employees-link"
                >
                    Show all products
                </a>
            </li>
        </ul>
        <ul v-else class="empty-data-p hidden">We don't know about any products</ul>
        <div class="header">
            <h3>Tenders</h3>
            <div class="col-md-12 tender-list" v-if="tenderData">
                <div class="col-md-4 tender" v-if="tenderData.amountOldYear">
                        <span class="tender-year">{{tenderData.amountOldYear | currency('Rub (K)')}}
                            <span class="tender-title">Last year</span>
                        </span>
                </div>

                <div class="col-md-4 tender tender-head" v-if="tenderData.amountActualYear">
                    <span class="tender-year">{{tenderData.amountActualYear | currency('Rub (K)')}}
                        <span class="tender-title">This year</span>
                        </span>
                </div>
                <div class="col-md-4 tender tender-head" v-if="tenderData.amountNextYear">
                        <span class="tender-year">{{tenderData.amountNextYear | currency('Rub (K)')}}
                            <span class="tender-title">Next year</span>
                        </span>
                </div>
            </div>
            <p v-else class="empty-data-p hidden">We don't know about any tenders</p>
        </div>
    </div>
</template>

<script>

    import http from '../mixins/http';
    import ProductModal from '../mixins/show-product-details-modal';
    import getProductName from '../mixins/get-product-name';

    export default {
        mixins: [http, ProductModal, getProductName],


        data: function () {
            return {
                tenderData: {
                    amountOldYear: '',
                    amountActualYear: '',
                    amountNextYear: '',
                    rateOldYear: '',
                    rateActualYear: '',
                    rateNextYear: ''
                },
                addressData: {},
                topProducts: [],
                isGoogleChartCoreLoaded: false,
            }
        },

        filters: {
            currency: function (value, currency_type) {
                if (!currency_type) {
                    currency_type = '';
                }
                value = Math.ceil(Number(value) / 1000);
                value = String(value);
                return value.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ') + ' ' + currency_type;
            },
        },

        methods: {
            loadProductsDetails: function () {
                this.httpGet('/api/address-details/' + this.addressId)
                    .then(data => {

                        this.addressData = data;

                        this.getTendersData();

                        this.loadTopProduct()
                            .then(() => {
                                $('.load-spinner-product').addClass('hidden');
                                $('.empty-data-p').removeClass('hidden');
                                this.topProducts.forEach((product, i) => {

                                    this.dataCreateToChart(product.prod_id, i)

                                    if (i >= 3) {
                                        return;
                                    }

                                })
                            });

                    });

            },

            loadTopProduct: function () {

                return this.httpGet('/api/load-top-products/' + this.addressData.id)
                    .then(data => {
                        this.topProducts = data;
                    })
            },

            getTendersData: function () {

                this.httpGet('/api/tenders-by-address/' + this.addressId)
                    .then(data => {

                        this.tenderData = data;

                        $('.empty-data-p').removeClass('hidden');
                    });
            },

            showSlidedBox: function (componentToDisplay) {

                this.$parent.$emit('all-products-view', componentToDisplay);

            },

            dataCreateToChart: function (productId, indexOrder) {
                setTimeout(() => {
                    var url = '/api/tenders-by-product-chart/' + productId + '/' + this.addressData.id;

                    this.httpGet(url)
                        .then(data => {

                            var title = ['Month', 'Total', {type: 'string', role: 'tooltip', 'p': {'html': true}}];

                            var DATA = data.chartsData;

                            DATA.unshift(title);

                            this.viewTendersChart(DATA, 'graph-container-' + indexOrder);

                        });
                }, 1000)
            },

            viewTendersChart: function (data, element_id) {

                $('#' + element_id).html('');

                var data = google.visualization.arrayToDataTable(data);

                var options = {
                    title: '',
                    vAxis: {title: '', gridlines: {color: '#fff', count: 0}},
                    hAxis: {baselineColor: 'none', ticks: []},
                    seriesType: 'bars',
                    legend: 'none',
                    enableInteractivity: false,
                    tooltip: {trigger: 'none'},
                    series: {0: {type: 'line'}}
                };

                var chart = new google.visualization.ComboChart(document.getElementById(element_id));
                chart.draw(data, options);

            },

            loadGoogleChart: function () {
                return google.charts.load('current', {'packages': ['corechart']})
                    .then(() => {
                        this.isGoogleChartCoreLoaded = true;
                    })
            }
        }
        ,
        props: ['addressId'],
        mounted:

            function () {
                if (this.addressId) {
                    this.loadGoogleChart()
                        .then(() => {
                            this.loadProductsDetails();
                        });
                }
            }

    }
</script>

<style scoped>

</style>