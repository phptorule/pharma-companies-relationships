<template>
    <div class="slided-box-content">

        <a href="javascript:void(0)" class="close-icon-a" @click="closeSlidedBox()">
            <img src="/images/x.png" alt="">
        </a>

        <h3 class="">
            All products
            <a data-v-cd5686be="" href="#">
                <i data-v-cd5686be="" class="fa fa-pencil"></i>
            </a>
        </h3>

        <ul class="staff-list">
            <li v-for="(product, i) in products">
                <div class="image">
                    <a href="javascript:void(0)" @click="showProductDetailsModal(addressId, product.id, addressData)">
                        <span class="person-initials">{{getProductName(product.name? product.name : product.company)}}</span>
                        <img :src="product.image? product.image : '/images/mask-'+i+'.png'" alt="">
                    </a>
                </div>
                <div class="personal-info">
                    <p class="name">
                        <a href="javascript:void(0)" @click="showProductDetailsModal(addressId, product.purchases[0].id, addressData)">
                            {{product.name? product.name : product.company}}
                        </a>
                    </p>
                    <p class="occupation">{{product.description}}</p>
                </div>
            </li>
        </ul>

        <div class="pagination-box">
            <pagination :records="productTotal"  :class="'pagination pagination-sm no-margin pull-right'" :per-page="10" @paginate="pageChanged"></pagination>
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
                products: [],
                isDataLoaded: false,
                productTotal: 0
            }
        },

        methods: {
            loadProductsPaginated: function (id, page) {

                let p = page || 1;

                this.httpGet('/api/product-by-address/'+id+'?page='+p)
                    .then(data => {
                        this.products = data.data;
                        this.isDataLoaded = true;
                        this.productTotal = data.total;
                    })
            },

            pageChanged: function (pageNumber) {
                this.loadProductsPaginated(this.addressId, pageNumber);
            },

            closeSlidedBox: function () {
                this.$emit('closeSlidedBox')
            }
        },

        props: ['addressId', 'purchaseId', 'addressData'],

        mounted: function () {

            if(this.addressId && !this.isDataLoaded) {
                this.loadProductsPaginated(this.addressId)
            }
        }
    }
</script>

<style scoped>

</style>