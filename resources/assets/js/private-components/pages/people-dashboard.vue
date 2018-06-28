<template>
    <div>

        <sidebar-tabs></sidebar-tabs>

        <!-- Main content -->
        <section class="sidebar">

            <div class="sidebar-form">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group filter-panel">

                            <single-dropdown-select
                                    class="form-control select-filter type-filter"
                                    :options="personTypesForFilter"
                                    :selected="appliedFilters.personTypes"
                                    @changed="applyPersonTypeFilter"
                                    :name="'Type'"
                                    ref="personTypeSingleDropdownSelect"
                            ></single-dropdown-select>

                            <div class="person-role-filter-box">
                                <input v-model="appliedFilters.roleInput"
                                       @keyup="applyRoleFilter"
                                       type="text"
                                       placeholder="Person Role..."
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

                            <a href="javascript:void(0)" class="btn btn-default reset-filters" title="Reset Filters" @click="resetFilters()">
                                <i class="fa fa-remove"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.search form -->

            <div class="sidebar-list-box">

                <div class="found-result-statistics">
                    Found {{peopleTotal}}
                    <span v-if="peopleTotal == 1">person</span>
                    <span v-if="peopleTotal != 1">people</span>.
                </div>

                <ul class="sidebar-list people-list" v-on:scroll="scrollFunction">
                    <li v-for="(person, i) in people" class="sidebar-list-item">
                        <div class="item">

                            <div class="item-image">

                                <span class="pull-right person-type-span">
                                    {{ personType(person.type_id) }}
                                </span>

                                <div class="main-image">
                                    <a href="javascript:void(0)">
                                        <div class="box-p">
                                            <span class="people-count person-initials">
                                                {{getPersonInitials(person.name)}}
                                            </span>

                                            <img class="addr-img" :src="'/images/mask-'+i+'.png'" alt="">
                                        </div>
                                    </a>
                                </div>
                                <div class="circle-1"></div>
                                <div class="circle-2"></div>
                            </div>

                            <h3>
                                <a  href="javascript:void(0)"
                                    @click="proceedToEmployeeDetailsModal(person)"
                                >
                                    {{ person.name }}
                                </a>
                            </h3>

                            <!--<p class="address">Type: {{ personType(person.type_id) }}</p>-->

                            <p class="address">Role: <strong>{{ person.role }}</strong></p>

                            <p class="address">

                                <span v-if="person.addresses.length"
                                      class="person-first-address-name"
                                      :title="person.addresses[0].name"
                                >
                                    <a href="javascript:void(0)" @click="goToAddressDetailsPage(person.addresses[0].id)">
                                        {{person.addresses[0].name}}
                                    </a>
                                </span>

                                <span v-if="person.addresses.length > 1">

                                    <a href="javascript:void(0)"
                                       class="person-more-companies"
                                       v-tooltip.bottom="{ html: 'tooltipContent' + person.id }"
                                    >
                                        + {{person.addresses.length - 1}} more
                                        <span v-if="person.addresses.length - 1 === 1">company</span>
                                        <span v-if="person.addresses.length - 1 > 1">companies</span>
                                    </a>

                                    <span class="product-tooltip" :id="'tooltipContent' + person.id" style="display: block">
                                        <ul style="margin: 0">
                                            <li v-for="add of person.addresses">
                                                {{add.name}}
                                            </li>
                                        </ul>
                                    </span>

                                    <br>
                                </span>



                                {{ person.addresses.length ? person.addresses[0].address : '' }}
                            </p>


                        </div>
                    </li>
                </ul>

                <div class="pagination-box">
                    <pagination
                            :records="peopleTotal"
                            ref="paginationDirective"
                            :class="'pagination pagination-sm no-margin pull-right'"
                            :per-page="20"
                            @paginate="pageChanged"></pagination>
                </div>

            </div>

        </section>

    </div>
</template>

<script>

    import http from '../../mixins/http';
    import addressHelpers from '../../mixins/address-helpers';
    import getPersonInitials from '../../mixins/get-person-initials';
    import employeeModal from '../../mixins/show-employee-details-modal';
    var _ = require('lodash');

    export default {

        mixins: [http,addressHelpers, getPersonInitials, employeeModal],

        data: function () {
            return {
                people: [{
                    addresses: [{}]
                }],
                isFirstLoad: true,
                user: {},
                addressList: [],
                addressesTotal: 0,
                peopleTotal: 0,
                filterObject: {
                    used_product_list: [],
                    tag_list: [],
                    customer_types: [],
                    person_types: []
                },
                appliedFilters: {
                    sortBy: this.$route.query['sort-by'] || '',
                    isOnlySortingChanged: false,
                    globalSearch: this.$route.query['global-search'] || '',
                    addressIds: this.$route.query['address-ids'] || '',
                    personTypes: this.$route.query['person-type-id'] || '',
                    roleInput: null
                },
                roleInputTimeoutId: null,
                pagination: {
                    currentPage: 1
                },
                totalPointsInCurrentMap: 0,
                multipleDropdownSelects: [],
                queryUrl: '',
                oldQueryUrl: '',
                hoveredAddress: {},
                mouseOverTimeoutId: null,
                scrollTimoutId: null,
            }
        },

        watch: {
            $route: function (to, from) {

                this.initFilters();

                this.composeQueryUrl();

                let hashToPerson = (to.hash.split('&'))[0];
                let hashFromPerson = (from.hash.split('&'))[0];

                if( ! this.isFirstLoad && hashToPerson !== hashFromPerson) {
                    this.showModalIfPersonHashDetected(null, {});
                }

                if(this.oldQueryUrl == this.queryUrl) {
                    return;
                }

                this.$refs.paginationDirective.setPage(1);
            }
        },

        computed: {

            sortByOptionsForFilter: function () {
                return [
                    {value: 'name-asc', label: 'Name &uarr;'},
                    {value: 'name-desc', label: 'Name &darr;'},
                ]
            },

            customerTypesForFilter: function () {
                return this.filterObject.customer_types.map(el => {
                    return {label: el.name, value: el.id};
                })
            },

            personTypesForFilter: function() {
                return this.filterObject.person_types.map(el => {
                    return {label: el.name, value: el.id};
                })
            },

            usedProductOptionsForDropDown: function () {
                return this.filterObject.used_product_list.map(product => {
                    return {
                        label: product.company + (product.name? ': ' + product.name: ''),
                        value: product.id
                    }
                })
            },
            tagOptionsForDropDown: function () {
                return this.filterObject.tag_list.map(tag => {
                    return {
                        label: tag.name,
                        value: tag.id
                    }
                })
            }
        },


        methods: {

            initFilters: function () {

                this.appliedFilters.sortBy = this.$route.query['sort-by'] || '';
                this.appliedFilters.globalSearch = this.$route.query['global-search'] || '';
                this.appliedFilters.personTypes = this.$route.query['person-type-id'] || '';

            },

            applyPersonTypeFilter: function (data) {
                this.appliedFilters.personTypes = data;
                this.applyFilters();
            },

            applySortByFilter: function (data) {
                this.appliedFilters.sortBy = data;
                this.applyFilters(true);
            },

            applyRoleFilter: function() {

                if(this.roleInputTimeoutId) {
                    clearTimeout(this.roleInputTimeoutId);
                }

                this.roleInputTimeoutId = setTimeout(()=>{
                    this.applyFilters();
                },500)
            },

            composeQueryUrl: function () {
                let queryStr = '';

                if (this.appliedFilters.personTypes) {
                    queryStr += '&person-type-id=' + this.appliedFilters.personTypes;
                }

                if (this.appliedFilters.roleInput) {
                    queryStr += '&role=' + this.appliedFilters.roleInput;
                }

                if (this.appliedFilters.globalSearch) {
                    queryStr += '&global-search=' + this.appliedFilters.globalSearch;
                }

                if (this.appliedFilters.sortBy) {
                    queryStr += '&sort-by=' + this.appliedFilters.sortBy;
                }

                this.queryUrl = queryStr;

                return queryStr;
            },

            loadPersonsPaginated: function() {

                let url = '/api/people-paginated?page=' + this.pagination.currentPage + this.queryUrl;

                return this.httpGet(url)
                    .then(data => {
                        this.people = data.data;
                        this.peopleTotal = data.total;

                        this.oldQueryUrl = this.queryUrl;

                        this.people.forEach(p => this.unifyAddressesWithDuplicatedNames(p.addresses));

                        return data;
                    })
            },

            setAddressMouseOverListener: function(address) {

                if(this.mouseOverTimeoutId) {
                    clearTimeout(this.mouseOverTimeoutId);
                }

                this.mouseOverTimeoutId = setTimeout(()=>{
                    if(this.hoveredAddress.id == address.id) {
                        return;
                    }

                    this.$eventGlobal.$emit('hover-out-from-the-sidebar', {});

                    this.hoveredAddress = address;

                    this.$eventGlobal.$emit('hover-over-address-at-the-sidebar', address);
                }, 100);

            },

            notifyFiltersHaveBeenApplied: function () {
                this.$eventGlobal.$emit('filtersHaveBeenApplied', this.queryUrl.replace('&','?'));
            },

            pageChanged: function (pageNumber) {
                this.pagination.currentPage = pageNumber;
                this.loadPersonsPaginated()
            },

            loadFilterObject: function() {
                return this.httpGet('/api/addresses-load-filters')
                    .then(data => {
                        this.filterObject = data;
                    })
            },

            applyFilters: function (isOnlySortingChanged) {

                this.appliedFilters.isOnlySortingChanged = !!isOnlySortingChanged;

                this.composeQueryUrl();

                this.$router.push('/people-dashboard?'+this.queryUrl);
            },

            resetFilters: function () {

                this.$refs.personTypeSingleDropdownSelect.resetSelectedValues();

                this.appliedFilters = {
                    usedProducts: [],
                    tags: [],
                    type: '',
                    sortBy: '',
                    isOnlySortingChanged: false,
                    globalSearch: '',
                    addressIds: '',
                    personTypes: ''
                };

                this.$eventGlobal.$emit('resetedAllFilters');

                if(this.$route.query['global-search'] || this.$route.query['address-ids']) {
                    this.$router.push('/dashboard');
                }

                this.applyFilters();
            },

            scrollFunction: function() {

                if(this.scrollTimoutId) {
                    clearTimeout(this.scrollTimoutId);
                }

                this.scrollTimoutId = setTimeout(() => {
                    let offsetSidebarListItem = $(".sidebar-list-item:first-child").offset().top - $(".sidebar-list").offset().top;
                    localStorage.setItem('offset-top-sidebar-list-item', offsetSidebarListItem);
                }, 500);

            },

            scrollToSidebarListItem: function() {
                setTimeout(()=>{
                    if(localStorage.hasOwnProperty('offset-top-sidebar-list-item')) {
                        let offsetTop = parseInt(localStorage.getItem('offset-top-sidebar-list-item'));
                        offsetTop = offsetTop > 0 ? offsetTop: -offsetTop;
                        $(".sidebar-list").animate({scrollTop:offsetTop}, 0);
                    }
                },100);
            },

            GoToAddressDetails:function(url) {
                localStorage.setItem('previous-dashboard', this.$route.fullPath);
                this.$router.push(url);
            },

            checkLocalStoragePreviousDashboard: function() {
                if(localStorage.getItem('previous-dashboard')){
                    localStorage.removeItem('previous-dashboard');
                }
            },

            personType: function (typeId) {
                let type = this.filterObject.person_types.find(el => el.id == typeId);

                return type? type.name : '';
            },

            proceedToEmployeeDetailsModal: function (person) {

                if(person.addresses.length) {
                    this.httpGet('/api/address-details/'+person.addresses[0].id)
                        .then(addressData => {
                            this.showEmployeeDetailsModal(person.id, addressData.id, addressData);
                        });
                }
                else {
                    this.showEmployeeDetailsModal(person.id, null, {});
                }
            },

            goToAddressDetailsPage: function (addressId) {
                localStorage.setItem('previous-dashboard', this.$route.fullPath);
                this.$router.push('/address-details/'+addressId);
            }
        },

        mounted: function () {

            document.title = 'Labscape People';

            $('ul.sidebar-list').height(window.innerHeight - 315);

            this.loadPersonsPaginated()
                .then(data =>{
                    this.isFirstLoad = false;

                    this.showModalIfPersonHashDetected(null, {});
                });

            this.checkLocalStoragePreviousDashboard();

        },

        created: function () {

            this.initFilters();

            this.composeQueryUrl();

            this.loadFilterObject();
        },
    }
</script>

<style scoped>

</style>