<template>
    <div>
        <ul class="staff-list">
            <li v-if="i < 3" v-for="(product, i) in productsData.product"
                :title="product.name? product.remark + ': ' + product.name : product.remark">
                <div class="image">
                    <a href="javascript:void(0)">
                        <span class="person-initials">P{{i+1}}</span>
                        <img :src="'/images/mask-0.png'" alt="">
                    </a>
                </div>
                <div class="prod-info">
                    <p class="name">{{product.name? product.remark + ': ' + product.name : product.remark}}</p>
                </div>
                <div class="prod-graf">
                    Graf
                </div>
                <div class="prod-info-tab">
                    <div class="prod-cost">
                        5456456
                    </div>
                    <div class="prod-cost">
                        45645
                    </div>
                    <div class="prod-cost">
                        {{product.total_price}}
                    </div>
                </div>
            </li>
            <li>
                <a href="" class="show-all-link">Show all products</a>
            </li>
        </ul>
        <div class="header">
            <h3>Tenders
                <ul v-if="productsData.tenders.length">
                    <li v-if="i < productsData.tenders.length" v-for="(tender, i) in productsData.tenders">
                        <div class="trends">
                            Last year:
                            <small>
                            </small>
                        </div>
                        <div class="trends">
                            This year:
                            <small>
                                {{tender.actual_cost}}
                            </small>
                        </div>
                        <div class="trends">
                            Next year:
                            <small>
                                {{tender.budgeted_cost}}
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
    import employeeModal from '../mixins/show-employee-details-modal';
    import getPersonInitials from '../mixins/get-person-initials';

    export default {
        mixins: [http, employeeModal, getPersonInitials],

        data: function () {
            return {
                productsData: {
                    product: [],
                    tenders: [],
                    budget: [],
                },
                product: [],
                Data: {
                    budget: [],
                    purchase: [],
                    products: [],
                    tenders: [],
                    product: []
                }
            }
        },

        methods: {
            loadProductsDetails: function () {
                this.httpGet('/api/products-details/' + this.addressId)
                    .then(data => {
                        // console.log(data);
                        this.Data = data;
                        this.product = [];
                        this.Data.forEach(tender => {
                            let trends = tender.purchase;
                            this.product = trends.concat(this.product);
                            this.product.push(data => tender.tender_date);
                            this.productsData.budget = tender.budget;
                            console.log(this.product);
                        });
                        this.productsData.product = this.product.sort(function (a, b) {
                            return b.total_price - a.total_price;
                        });
                        console.log(this.productsData);
                    });
            },

            //     this.productsData = data;
            //     // console.log(data);
            //     this.product = {};
            //     this.productsData.forEach(products => {
            //         this.product = products.purchase.concat(this.product);
            //         console.log(this.product);
            //     });
            //     // this.productsData.product = this.productsData.product.sort(function (a, b) {
            //     //     return b.total_price - a.total_price;
            //     // });
            //     // console.log(this.productsData);
            // });
        },
        props: ['addressId'],
        mounted: function () {
            if (this.addressId) {
                this.loadProductsDetails();
            }
        },

    }
</script>

<style scoped>

</style>