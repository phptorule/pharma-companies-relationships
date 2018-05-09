<template>
    <div>
        <ul class="staff-list" v-if="productsData.purchases.length">
            <li v-if="i < 3" v-for="(purchase, i) in productsData.purchases">
                <div class="image">
                    <a href="javascript:void(0)" @click="showProductDetailsModal(addressId, purchase.id, addressData)">
                        <span class="person-initials">P{{i+1}}</span>
                        <img :src="purchase.products[0].image? purchase.products[0].image : '/images/mask-0.png'"
                             alt="">
                    </a>
                </div>
                <div class="prod-info">
                    <p class="name">{{purchase.products[0].name? purchase.products[0].name : 'Product name' + i}}</p>
                    <p class="amount" v-if="purchase.total_price">{{Math.ceil(purchase.total_price) | currency}} |
                        {{Math.ceil(purchase.total_price) | currency}} |
                        {{Math.ceil(purchase.total_price) | currency}}</p>
                </div>
                <div class="prod-graf" :id="'graph-container-'+purchase.products[0].id" style="width: 75px; height: 50px"></div>
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
        <p v-else class="empty-data-p">We don't know about any products</p>
        <div class="header">
            <h3>Tenders</h3>
            <div class="col-md-12 tender-list" v-if="amount_old_year || amount_actual_year || amount_next_year">
                <div class="col-md-4 tender" v-if="amount_old_year">
                    <p class="tender-year">Last year:</p>
                    <a class="tender-amount-btn" href="javascript:void(0)">{{amount_old_year |
                        currency}}</a>
                    <p class="tender-percent">({{rate_old_year}} %)</p>
                </div>
                <div class="col-md-4 tender" v-if="amount_actual_year">
                    <p class="tender-year-center">This year:</p>
                    <a class="tender-amount-btn" href="javascript:void(0)">{{amount_actual_year |
                        currency}}</a>
                    <p class="tender-percent">({{rate_actual_year}}%)</p>
                </div>
                <div class="col-md-4 tender" v-if="amount_next_year">
                    <p class="tender-year">Next year:</p>
                    <a class="tender-amount-btn" href="javascript:void(0)">{{amount_next_year |
                        currency}}</a>
                    <p class="tender-percent">({{rate_next_year}}%)</p>
                </div>
            </div>
            <p v-else class="empty-data-p">We don't know about any tenders</p>
        </div>
    </div>
</template>

<script>

    import http from '../mixins/http';
    import ProductModal from '../mixins/show-product-details-modal';
    import getPersonInitials from '../mixins/get-person-initials';

    export default {
        mixins: [http, ProductModal, getPersonInitials],


        data: function () {
            return {
                productsData: {
                    purchases: [],
                    tenders: [],
                    budget: [],
                    actual_cost: null,
                    budgeted_cost: null,
                    tender_date: null,
                    uri: null
                },
                actual_year: (new Date()).getFullYear(),
                amount_old_year: null,
                amount_actual_year: null,
                amount_next_year: null,
                rate_old_year: null,
                rate_actual_year: null,
                rate_next_year: null,
                addressData: {},
                isGoogleChartCoreLoaded: false,
            }
        },

        filters: {
            currency: function (value) {
                value = String(value);
                return value.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ') + ' Rub';
            },
        },

        methods: {
            loadProductsDetails: function () {
                this.httpGet('/api/address-details/' + this.addressId)
                    .then(data => {

                        this.addressData = data;

                        data.tenders.forEach((tender, i) => {

                            this.productsData.actual_cost += Math.ceil(Number(tender.actual_cost));

                            this.productsData.budgeted_cost += Math.ceil(Number(tender.budgeted_cost));

                            this.productsData.tenders.push(tender);

                            this.productsData.budget = tender.budget;

                            tender.purchase.forEach(purchase => {

                                if (purchase.products.length > 0) {

                                    this.productsData.purchases.push(purchase);

                                }

                            });

                            this.productsData.purchases = this.productsData.purchases.sort(function (a, b) {

                                return b.total_price - a.total_price;

                            });

                        });

                        this.getTendersData();

                        this.productsData.purchases.forEach((purchase, i) => {

                            this.dataCreateToChart(purchase.products[0].id)

                            if (i >= 3) {
                                return;
                            }

                        })
                    });

            },

            getTendersData: function () {
                this.httpGet('/api/tenders-by-address/' + this.addressId)
                    .then(data => {
                        data.forEach(tender => {

                            if (tender.budget != null) {
                                if (this.actual_year > Math.ceil(tender.budget.delivery_year)) {

                                    this.amount_old_year += Math.ceil(Number(tender.budgeted_cost));

                                } else if (this.actual_year == Math.ceil(tender.budget.delivery_year)) {

                                    this.amount_actual_year += Math.ceil(Number(tender.budgeted_cost));

                                } else if (this.actual_year < Math.ceil(tender.budget.delivery_year)) {

                                    this.amount_next_year += Math.ceil(Number(tender.budgeted_cost));

                                }
                            }

                        });

                        if (this.amount_actual_year < this.amount_old_year) {

                            this.rate_old_year = Math.ceil((this.amount_actual_year / this.amount_old_year) * 100);

                        } else if (this.amount_actual_year > this.amount_old_year) {

                            this.rate_old_year = Math.ceil((this.amount_old_year / this.amount_actual_year) * 100);

                        }

                        if (this.amount_actual_year <= this.amount_next_year) {

                            this.rate_actual_year = Math.ceil((this.amount_actual_year / this.amount_next_year) * 100);

                        } else if (this.amount_actual_year >= this.amount_next_year) {

                            this.rate_actual_year = Math.ceil((this.amount_next_year / this.amount_actual_year) * 100);

                        }

                        if (this.amount_old_year < this.amount_next_year) {

                            this.rate_next_year = Math.ceil((this.amount_actual_year / this.amount_next_year) * 100);

                        } else if (this.amount_old_year > this.amount_next_year) {

                            this.rate_next_year = Math.ceil((this.amount_next_year / this.amount_actual_year) * 100);

                        }

                    });
            },

            showSlidedBox: function (componentToDisplay) {

                this.$parent.$emit('all-products-view', componentToDisplay);

            },

            dataCreateToChart: function (productId) {
                setTimeout(() => {
                this.httpGet('/api/product-by-tenders/' + productId)
                    .then(data => {

                            var DATA = [[]];
                            if (data.length != 0) {

                                let graf_data = {
                                    title: []
                                };
                                graf_data.title.push('Month')
                                graf_data.title.push('Total')

                                data.forEach(tender => {

                                    graf_data.productId = tender.product_id

                                    let date_tender = moment(new Date(tender.tender_date)).format('MMM-YY');

                                    if (typeof graf_data[date_tender] == "undefined") {

                                        graf_data[date_tender] = Math.ceil(Number(tender.budgeted_cost));
                                    }

                                });

                                for (let i = 0; i < graf_data.title.length; i++) {

                                    DATA[0].push(graf_data.title[i]);
                                }

                                for (var key in graf_data) {
                                    if (key != 'title' && key != 'productId') {
                                        DATA.push([key, graf_data[key]]);
                                    }
                                }

                                setTimeout(() => {
                                    if (typeof DATA[1] != "undefined") {
                                        setTimeout(() => {
                                            this.viewTendersChart(DATA, 'graph-container-' + graf_data.productId);
                                        }, 100)
                                    } else {
                                        DATA[0] = ['Month', 'Total'];
                                        DATA[1] = ['Yan-97', 0];
                                        setTimeout(() => {
                                            this.viewTendersChart(DATA, 'graph-container-' + graf_data.productId);
                                        }, 100)
                                    }
                                }, 300)
                            }

                        }
                    );
                }, 500)
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