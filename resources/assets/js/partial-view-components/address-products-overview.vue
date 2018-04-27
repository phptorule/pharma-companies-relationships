<template>
    <div>
    <ul class="used-products-list" v-if="productsData.product.length">
        <li v-if="i < 3" v-for="(product, i) in productsData.product"
            :title="product.name? product.remark + ': ' + product.name : product.remark">
            <span class="image"></span>
            <span class="prod-name">
                            {{product.name? product.remark + ': ' + product.name : product.remark}}
                        </span>
            <small>
                {{product.total_price}}
            </small>
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
                productsData:{
                    budgeted_cost: '',
                    actual_cost: '',
                    url: '',
                    tender_date: '',
                    cluster: {
                        addresses: []
                    },
                    people: [],
                    products: [],
                    tenders: [],
                    product: []
                },
            }
        },

        methods: {
            loadProductsDetails: function () {
                this.httpGet('/api/products-details/' + this.addressId)
                    .then(data => {
                        this.productsData = data;
                        console.log(data);
                        this.productsData.product = {};
                        this.productsData.tenders.forEach(trend => {
                            let products = trend.purchase.sort(function (a, b) {
                                return b.total_price - a.total_price;
                            });
                            this.productsData.product = products.concat(this.productsData.product);
                        });
                        this.productsData.product = this.productsData.product.sort(function (a, b) {
                            return b.total_price - a.total_price;
                        });

                        console.log(this.productsData);
                    });
            },
        },
        props: ['addressId'],
        mounted: function () {
            if(this.addressId) {
                this.loadProductsDetails();
            }
        },

    }
</script>

<style scoped>

</style>