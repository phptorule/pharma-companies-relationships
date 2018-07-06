<template>
    <div class="assign-addresses-to-person-container" style="display: none">

        <div class="row preselected-addresses-container">
            <div class="col-md-12">

                <p class="empty-data-p"
                   style="text-align: center"
                   v-if="!pretenderAddressList.length && !personAddresses.length"
                >
                    Current person address list is empty
                </p>

                <span v-for="address of personAddresses"
                      class="label label-primary"
                      :title="address.name"
                      :class="{'to-be-removed': !isAddressInList(address.id)}"
                      @click="addAddressToSelectedList(address.id)"
                >
                    <i class="fa fa-remove"></i>
                    {{address.name}}
                </span>

                <span v-for="address of pretenderAddressList"
                      class="label label-success"
                      :title="address.name"
                      :class="{'to-be-removed': !isAddressInList(address.id)}"
                      @click="removeAddressFromSelectedList(address.id)"
                >
                    <i class="fa fa-remove"></i>
                    {{address.name}}
                </span>

            </div>

            <!--<div class="col-md-1">
                <button class="btn btn-success" @click="assignAddresses()" title="Update person's address list">
                    <i class="fa fa-floppy-o"></i>
                </button>
            </div>-->
        </div>

        <div class="row">

            <div class="form-group filter-panel">

                <div class="col-md-6">
                    <div class="person-role-filter-box">
                        <input v-model="appliedFilters.nameInput"
                               @keyup="applyNameFilter"
                               type="text"
                               placeholder="Company Name"
                        >
                    </div>
                </div>


                <div class="col-md-5">

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


                <div class="col-md-1">

                    <a href="javascript:void(0)" class="btn btn-default reset-filters" title="Reset Filters" @click="resetFilters()">
                        <i class="fa fa-refresh"></i>
                    </a>

                </div>


            </div>

        </div>

        <div class="addresses-to-person-box">
            <ul class="ul-addresses-to-person">
                <li v-for="address of addressList">
                    <div>
                        <div class="row">
                            <div class="col-md-1 text-center">
                                <div class="grey-checkbox only-people-with-addresses-checkbox">
                                    <label>
                                        <input type="checkbox"
                                               :checked="isAddressInList(address.id)"
                                               @click="addAddressToSelectedList(address.id)">
                                        <span class="borders"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-11">
                                <a href="javascript:void(0)"
                                   :class="{'address-in-list': isAddressInList(address.id)}"
                                   @click="addAddressToSelectedList(address.id)">
                                    {{address.name}}
                                    <br>
                                    <small>{{address.address}}</small>
                                </a>
                            </div>
                        </div>
                    </div>
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
                nameInputTimeoutId: null,
                selectedAddressIdList: [],
                pretenderAddressList: []
            }
        },

        watch: {
            personId: function () {
                this.init();
            },

            // personAddresses: function (newVal) {
            //
            //     this.addPersonAddressesToSelected();
            // }
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

            addAddressToSelectedList: function(addressId) {

                let index = this.selectedAddressIdList.indexOf(addressId);

                if(index === -1) {
                    this.selectedAddressIdList.push(addressId);
                    this.fillPretenderAddressList(addressId);
                }
                else {
                    // this.selectedAddressIdList.splice(index,1);
                    this.removeAddressFromSelectedList(addressId)
                }
            },

            removeAddressFromSelectedList: function (addressId) {
                let index = this.selectedAddressIdList.indexOf(addressId);
                this.selectedAddressIdList.splice(index,1);

                let i = this.pretenderAddressList.findIndex(a => a.id == addressId);

                if(i !== -1) {
                    this.pretenderAddressList.splice(i, 1);
                }
            },

            fillPretenderAddressList: function(addressId) {

                let indexInPersonAddresses = this.personAddresses.findIndex(a => a.id == addressId);

                let index = this.pretenderAddressList.findIndex(a => a.id == addressId);

                if(index === -1 && indexInPersonAddresses === -1) {
                    let address = this.addressList.find(address => addressId == address.id);

                    if(address) {
                        this.pretenderAddressList.push(address);
                    }
                }
                else {
                    this.pretenderAddressList.splice(index, 1);
                }

                console.log('pretenderAddressList', this.pretenderAddressList);
            },

            isAddressInList: function(addressId) {
                return this.selectedAddressIdList.indexOf(addressId) !== -1;
            },

            init: function () {
                this.loadAvailableAddressesPaginated();

                this.addPersonAddressesToSelected();
            },

            addPersonAddressesToSelected: function() {

                this.selectedAddressIdList = [];
                this.pretenderAddressList = [];

                if(!this.personAddresses || !this.personAddresses.length) {
                    return;
                }

                this.personAddresses.forEach(ad => this.selectedAddressIdList.push(ad.id))
            },

            loadAvailableAddressesPaginated: function () {

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

            notifyPersonAddressListUpdated: function() {
                this.$emit('onPersonAddressListUpdated', {});
                this.$eventGlobal.$emit('update-people-list',{});
            },

            assignAddresses: function () {

                let url = '/api/assign-addresses-to-person/' + this.personId;

                return this.httpPost(url, this.selectedAddressIdList)
                    .then(data => {

                        alertify.notify('Person address list has been successfully updated', 'success', 3);

                        this.notifyPersonAddressListUpdated();
                        this.pretenderAddressList = [];

                        return data;
                    })
                    .catch(err => {
                        alertify.notify('Sorry, error occurred', 'danger', 3);
                    })
            }
        },

        props: ['personId', 'personAddresses'],

        mounted: function () {

            this.loadAvailableAddressesPaginated()
                .then(()=>{
                    $('.assign-addresses-to-person-container').slideDown('fast');
                });

            this.addPersonAddressesToSelected();

            this.$eventGlobal.$on('onSubmitAssigningAddressesToPerson', () => {
                this.assignAddresses();
            })
        },

        beforeDestroy: function () {
            this.$eventGlobal.$off('onSubmitAssigningAddressesToPerson');
        }
    }
</script>

<style scoped>

</style>