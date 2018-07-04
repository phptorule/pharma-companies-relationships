<template>
    <div>
        <div class="form-group filter-panel">

            <div class="person-role-filter-box">
                <input v-model="appliedFilters.nameInput"
                       @keyup="applyNameFilter"
                       type="text"
                       placeholder="Company Name"
                >
            </div>

            <single-dropdown-select
                    class="form-control select-filter type-filter"
                    :options="sortByOptionsForFilter"
                    :selected="appliedFilters.sortBy"
                    :isHiddenEmptyOption="true"
                    @changed="applySortByFilter"
                    :name="'Sort By'"
                    ref="sortBySingleDropdownSelect"
            ></single-dropdown-select>

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
                    nameInput: null,
                    sortBy: '',
                },
                queryUrl: '',
                pagination: {
                    currentPage: 1
                },
                nameInputTimeoutId: null
            }
        },

        watch: {
            personId: function () {
                this.init();
            }
        },

        computed: {

            sortByOptionsForFilter: function () {
                return [
                    {value: 'name-asc', label: 'Name &uarr;'},
                    {value: 'name-desc', label: 'Name &darr;'}
                ]
            },

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

                if (this.appliedFilters.nameInput) {
                    queryStr += '&name=' + this.appliedFilters.nameInput;
                }

                if (this.appliedFilters.sortBy) {
                    queryStr += '&sort-by=' + this.appliedFilters.sortBy;
                }

                this.queryUrl = queryStr;

                return queryStr;
            },

            applyNameFilter: function(){
                if(this.nameInputTimeoutId) {
                    clearTimeout(this.nameInputTimeoutId);
                }

                this.nameInputTimeoutId = setTimeout(()=>{
                    this.applyFilters();
                },500)  
            },

            applySortByFilter: function (data) {
                this.appliedFilters.sortBy = data;
                this.applyFilters();
            },

            applyFilters: function() {
                this.composeQueryUrl();
                this.$refs.paginationDirective.setPage(1);
            },

            resetFilters: function () {

                this.$refs.sortBySingleDropdownSelect.resetSelectedValues();

                this.appliedFilters = {
                    nameInput: null,
                    sortBy: '',
                };

                this.applyFilters();
            },
        },

        props: ['personId'],

        mounted: function () {
            this.loadAvailableAddressesPaginated();
        }
    }
</script>

<style scoped>

</style>