<template>
    <div>
        <ul class="staff-list">
            <li v-if="i < 3" v-for="(purchase, i) in productsData.purchases">
                <div class="image">
                    <a href="javascript:void(0)" @click="showProductsDetailsModal(addressId, purchase.id, addressData)">
                        <span class="person-initials">P{{i+1}}</span>
                        <img :src="'/images/mask-0.png'" alt="">
                    </a>
                </div>
                <div class="prod-info">
                    <p class="name" v-if="purchase.products[0].name">{{purchase.products[0].name}}</p>
                    <p class="name" v-if="purchase.total_price">{{Math.ceil(purchase.total_price)}} |
                        {{Math.ceil(purchase.total_price)}} |
                        {{Math.ceil(purchase.total_price)}}</p>
                </div>
                <div class="prod-graf" v-bind:id="i" style="width: 75px; height: 50px">
                </div>
            </li>
            <li>
                <a href="" class="show-all-link">Show all products</a>
            </li>
        </ul>
        <div class="header">
            <h3>Tenders
                <ul>
                    <li v-if="productsData.actual_cost && productsData.budgeted_cost" class="tender-list">
                        <div class="tender">
                            Last year:
                            <br>
                            <small class="tender-cost">$
                            </small>
                            <br>
                            <small class="tender-cost">(+%)
                            </small>
                        </div>
                        <div class="tender">
                            This year:
                            <br>
                            <small class="tender-cost">$
                                {{productsData.actual_cost}}
                            </small>
                            <br>
                            <small class="tender-cost">(+%)
                            </small>
                        </div>
                        <div class="tender">
                            Next year:
                            <br>
                            <small class="tender-cost">$
                                {{productsData.budgeted_cost}}
                            </small>
                            <br>
                            <small class="tender-cost">(+%)
                            </small>
                        </div>
                    </li>
                </ul>
            </h3>
        </div>
    </div>
</template>

<script>

    import http from '../mixins/http';
    import ProductsModal from '../mixins/show-products-details-modal';
    import getPersonInitials from '../mixins/get-person-initials';
    import {GoogleCharts} from 'google-charts';

    export default {
        mixins: [http, ProductsModal, getPersonInitials],


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
                addressData:{},
                graf: '',
            }
        },

        methods: {
            loadProductsDetails: function () {
                this.httpGet('/api/address-details/' + this.addressId)
                    .then(data => {
                        this.addressData = data;
                        data.tenders.forEach(tender => {
                            this.productsData.actual_cost += Math.ceil(Number(tender.actual_cost));
                            this.productsData.budgeted_cost += Math.ceil(Number(tender.budgeted_cost));
                            this.productsData.tenders.push(tender);
                            this.productsData.budget = tender.budget;
                            tender.purchase.forEach(purchase => {
                                if(purchase.products.length > 0){
                                    this.productsData.purchases.push(purchase);
                                }
                            });
                            this.productsData.purchases = this.productsData.purchases.sort(function (a, b) {
                                return b.total_price - a.total_price;
                            });
                        });
                    });

                GoogleCharts.load(drawChart);

                function drawChart() {
                    const data = google.visualization.arrayToDataTable([
                        ['Year', 'Sales'],
                        ['2017', 1914410],
                        ['2018', 4204305],
                        ['2019', 3144601]
                    ]);

                    let options = {
                        title: 'Company Performance',
                        curveType: 'function',
                    };
                    for(let i = 0; i<3; i++) {
                        const pie_1_chart = new GoogleCharts.api.visualization.LineChart(document.getElementById(i));
                        pie_1_chart.draw(data);
                    }
                }
            },

        }
        ,
        props: ['addressId'],
        mounted:
            function () {
                if (this.addressId) {
                    this.loadProductsDetails();
                }
            }

    }
</script>

<style scoped>

</style>