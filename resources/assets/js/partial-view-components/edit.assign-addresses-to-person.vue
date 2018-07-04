<template>
    <div>
        <div class="filters">
            Filters will be here
        </div>

        <div>
            <ul>
                <li v-for="address of addressList">
                    {{address.name}}
                </li>
            </ul>

            <div class="pagination-box">
                <pagination
                        :records="addressesTotal"
                        ref="paginationDirective"
                        :class="'pagination pagination-sm no-margin pull-right'"
                        :per-page="20"
                        @paginate="pageChanged"
                ></pagination>
            </div>
        </div>
    </div>
</template>

<script>

    import http from '../mixins/http';
    import addressHelpers from '../mixins/address-helpers';

    export default {

        mixins: [http, addressHelpers],

        data: function () {
            return {
                addressesTotal: 0,
                addressList: [],
                appliedFilters: {
                    usedProducts: [],
                    tags: [],
                    sortBy: this.$route.query['sort-by'] || '',
                },
                queryUrl: '',
                pagination: {
                    currentPage: 1
                },
            }
        },

        watch: {
            personId: function () {
                this.init();
            }
        },

        methods: {
            init: function () {
                this.loadAvailableAddressesPaginated();
            },

            loadAvailableAddressesPaginated: function () {

                console.log('here');

                let url = '/api/addresses-paginated?page=' + this.pagination.currentPage + this.queryUrl;

                return this.httpGet(url)
                    .then(data => {
                        this.addressesTotal = data.total;
                        this.addressList = data.data;

                        this.unifyAddressesWithDuplicatedNames(this.addressList);

                        return data;
                    })
            },

            pageChanged: function (pageNumber) {
                this.pagination.currentPage = pageNumber;
                this.loadAvailableAddressesPaginated();
            },

            composeQueryUrl: function () {
                let queryStr = '';

                if (this.appliedFilters.usedProducts.length) {
                    this.appliedFilters.usedProducts.forEach(id => {
                        queryStr += '&used-product-ids[]=' + id;
                    });
                }

                if (this.appliedFilters.tags.length) {
                    this.appliedFilters.tags.forEach(id => {
                        queryStr += '&tag-ids[]=' + id;
                    });
                }

                if (this.appliedFilters.sortBy) {
                    queryStr += '&sort-by=' + this.appliedFilters.sortBy;
                }

                this.queryUrl = queryStr;

                return queryStr;
            },

            applyUsedProductsFilter: function (data) {
                this.appliedFilters.usedProducts = data;
                this.applyFilters();
            },

            applyTagsFilter: function (data) {
                this.appliedFilters.tags = data;
                this.applyFilters();
            },

            applySortByFilter: function (data) {
                this.appliedFilters.sortBy = data;
                this.applyFilters();
            },

            applyFilters: function() {
                this.$refs.paginationDirective.setPage(1);
            }
        },

        props: ['personId'],

        mounted: function () {
            this.loadAvailableAddressesPaginated();
        }
    }
</script>

<style scoped>

</style>