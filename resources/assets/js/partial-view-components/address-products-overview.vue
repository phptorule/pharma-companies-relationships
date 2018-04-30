<template>
    <div>
        <ul class="staff-list">
            <li v-if="i < 3" v-for="(product, i) in productsData.product"
                :title="product.name? product.remark + ': ' + product.name : product.remark">
                <div class="image">
                    <a href="javascript:void(0)" @click="showProductsDetailsModal()">
                        <span class="person-initials">P{{i+1}}</span>
                        <img :src="'/images/mask-0.png'" alt="">
                    </a>
                </div>
                <div class="prod-info">
                    <p class="name">{{product.name}}</p>
                    <p class="name" v-if="product.total_price">{{Math.ceil(product.total_price)}} |
                        {{Math.ceil(product.total_price)}} |
                        {{Math.ceil(product.total_price)}}</p>
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
                    <li v-if="actual_cost && budgeted_cost" class="tender-list">
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
                                {{Math.ceil(actual_cost)}}
                            </small>
                            <br>
                            <small class="tender-cost">(+%)
                            </small>
                        </div>
                        <div class="tender">
                            Next year:
                            <br>
                            <small class="tender-cost">$
                                {{Math.ceil(budgeted_cost)}}
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
                    product: [],
                    tenders: [],
                    budget: [],
                },
                product: [],
                actual_cost: null,
                budgeted_cost: null,
                graf: '',
                Data: {
                    budget: [],
                    purchase: [],
                    products: [],
                    tenders: [],
                    product: []
                }
            }
        }
        ,

        methods: {
            loadProductsDetails: function () {
                this.httpGet('/api/products-details/' + this.addressId)
                    .then(data => {
                        this.Data = data;
                        this.product = [];
                        this.Data.forEach(tender => {
                            let trends = tender.purchase;
                            this.product = trends.concat(this.product);
                            this.productsData.tenders.push(tender);
                            this.productsData.budget = tender.budget;
                        });
                        this.productsData.product = this.product.sort(function (a, b) {
                            return b.total_price - a.total_price;
                        });
                        for (let i = 0; i < this.productsData.tenders.length; i++) {
                            this.actual_cost += Number(this.productsData.tenders[i].actual_cost);
                            this.budgeted_cost += Number(this.productsData.tenders[i].budgeted_cost);
                        }
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