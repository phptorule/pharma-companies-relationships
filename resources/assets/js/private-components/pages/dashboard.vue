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
                                    :options="customerTypesForFilter"
                                    :selected="appliedFilters.type"
                                    :isHiddenEmptyOption="true"
                                    :showCircle="true"
                                    @changed="applyTypeFilter"
                                    :name="'Type'"
                                    ref="typeSingleDropdownSelect"
                            ></single-dropdown-select>

                            <multiple-dropdown-select
                                    class="form-control select-filter used-products-filter"
                                    :name="'Used Products'"
                                    :options="usedProductOptionsForDropDown"
                                    :selected="appliedFilters.usedProducts"
                                    :relationalProducts="filterObject.relational_products"
                                    @changed="applyUsedProductsFilter"
                                    ref="productsMultipleDropdownSelect"
                            ></multiple-dropdown-select>

                            <multiple-dropdown-select
                                    class="form-control select-filter tags-filter"
                                    :name="'Tags'"
                                    :options="tagOptionsForDropDown"
                                    :selected="appliedFilters.tags"
                                    @changed="applyTagsFilter"
                                    ref="tagMultipleDropdownSelect"
                            ></multiple-dropdown-select>

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
                    Found {{addressesTotal}} labs. {{totalPointsInCurrentMap}} in current map display
                </div>

                <h3 class="empty-data-h" v-if="!addressList.length">No Found Addresses</h3>

                <ul class="sidebar-list" @mouseleave="setAddressMouseLeaveListener()" v-on:scroll="scrollFunction">
                    <li v-for="(address, i) in addressList" @mouseover="setAddressMouseOverListener([address])" class="sidebar-list-item">
                        <div class="item" :class="{'potential-customers':address.customer_status == 1, 'my-customers': address.customer_status == 2}">

                            <div class="item-image" v-show="address.people_count > 0">
                                <div class="main-image">
                                    <!--<router-link :to="'/address-details/'+address.id+ (address.people_count ? '?all-employees=1' : '')" >-->
                                    <a href="javascript:void(0)" @click="GoToAddressDetails('/address-details/'+address.id+ (address.people_count ? '?all-employees=1' : ''))" >
                                        <div class="box-p">
                                            <span class="people-count" v-if="address.people_count">
                                                See {{address.people_count}} employee{{address.people_count > 1? 's': ''}}
                                            </span>

                                            <img class="addr-img" :src="'/images/mask-'+i+'.png'" alt="">
                                        </div>
                                    </a>
                                    <!--</router-link>-->
                                </div>
                                <div class="circle-1"></div>
                                <div class="circle-2"></div>
                            </div>

                            <h3>
                                <!--<router-link :to="'/address-details/'+address.id">
                                    {{ address.name }}
                                </router-link>-->
                                <a  href="javascript:void(0)" @click="GoToAddressDetails('/address-details/'+address.id)">
                                    {{ address.name }}
                                </a>

                                <span class="oval"></span>
                            </h3>

                            <p class="address">{{ address.address }}</p>

                            <p class="lab-chain-p" v-if="address.cluster">Lab Chain: <strong>{{address.cluster.name}}</strong></p>

                            <ul class="tag-list" v-if="address.tags && address.tags.length">
                                <li v-for="tag in address.tags">
                                    <a href="#" @click.prevent>{{tag.name}}</a>
                                </li>
                            </ul>

                            <div class="info-block" v-if="false"> <!--TODO: remove v-if="false" when staring to work on Lab News feature-->
                                <div class="lightening-icon">
                                    <img src="/images/blue-lightening.png" alt="">
                                </div>

                                <div class="news-label">
                                    New employer <a href="#" @click.prevent class="news-link without-handler">Jina James</a> joined the lab
                                </div>

                                <a href="#" @click.prevent class="news-link more-news-link without-handler">
                                    +3 more news
                                </a>
                            </div>

                        </div>
                    </li>
                </ul>

                <div class="pagination-box">
                    <pagination :records="addressesTotal" ref="paginationDirective" :class="'pagination pagination-sm no-margin pull-right'" :per-page="20" @paginate="pageChanged"></pagination>
                </div>

            </div>

        </section>

    </div>
</template>

<script>

    import http from '../../mixins/http';
    import addressHelpers from '../../mixins/address-helpers';
    import mapNotified from '../../mixins/notify-map-that-filters-updated';
    import mapHoveringNotified from '../../mixins/notify-map-that-hovering-over-list-item';
    var _ = require('lodash');

    export default {

        mixins: [http,addressHelpers, mapNotified, mapHoveringNotified],

        data: function () {
            return {
                isFirstLoad: true,
                user: {},
                addressList: [],
                addressesTotal: 0,
                filterObject: {
                    used_product_list: [],
                    tag_list: [],
                    customer_types: [],
                    relational_products: []
                },
                appliedFilters: {
                    usedProducts: this.$route.query['used-product-ids[]'] || [],
                    tags: this.$route.query['tag-ids[]'] || [],
                    type:  this.$route.query['type-id'] || '',
                    sortBy: this.$route.query['sort-by'] || '',
                    isOnlySortingChanged: false,
                    globalSearch: this.$route.query['iteration[]'] || [],
                    addressIds: this.$route.query['address-ids'] || '',
                },
                pagination: {
                    currentPage: 1
                },
                totalPointsInCurrentMap: 0,
                multipleDropdownSelects: [],
                queryUrl: '',
                oldQueryUrl: '',
                scrollTimoutId: null
            }
        },

        watch: {
            $route: function (to) {

                this.initFilters();

                this.composeQueryUrl();

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
                    {value: 'people-asc', label: 'Employee &uarr;'},
                    {value: 'people-desc', label: 'Employee &darr;'},
                    {value: 'products-asc', label: 'Products &uarr;'},
                    {value: 'products-desc', label: 'Products &darr;'},
                ]
            },

            customerTypesForFilter: function () {
                let arr = this.filterObject.customer_types.map(el => {
                    return {
                        label: `<i class="oval ${el.id == 1? 'potential-customers' : ''} ${el.id == 2? 'my-customers' : ''}"></i>${el.name}`,
                        value: el.id
                    };
                });

                arr.unshift({
                    label: `<i class="oval both"></i> All`,
                    value: ''
                });

                return arr;
            },

            usedProductOptionsForDropDown: function () {
                return this.filterObject.used_product_list;
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

        created: function () {

            this.initFilters();

            this.composeQueryUrl();

            this.loadFilterObject();
        },

        mounted: function () {

            document.title = 'Labscape';

            $('ul.sidebar-list').height(window.innerHeight - 315);

            this.listenToTotalPointsDisplayedOnMapChanged();

            this.loadAddressesPaginated()
                .then((data) => {
                    this.scrollToSidebarListItem();
                });

            this.checkLocalStoragePreviousDashboard();

            this.$root.logData('overview', 'open', JSON.stringify(''));

        },

        methods: {

            initFilters: function () {

                this.appliedFilters.usedProducts = this.$route.query['used-product-ids[]'] || [];

                if(typeof this.appliedFilters.usedProducts === 'string') {
                    this.appliedFilters.usedProducts = [this.appliedFilters.usedProducts ];
                }

                this.appliedFilters.tags = this.$route.query['tag-ids[]'] || [];

                if(typeof this.appliedFilters.tags === 'string') {
                    this.appliedFilters.tags = [this.appliedFilters.tags ];
                }

                this.appliedFilters.globalSearch = this.$route.query['iteration[]'] || [];

                if(typeof this.appliedFilters.globalSearch === 'string') {
                    this.appliedFilters.globalSearch = [this.appliedFilters.globalSearch ];
                }

                this.appliedFilters.type =  this.$route.query['type-id'] || '';
                this.appliedFilters.sortBy = this.$route.query['sort-by'] || '';
                this.appliedFilters.addressIds = this.$route.query['address-ids'] || '';

            },

            applyTypeFilter: function (data) {
                this.appliedFilters.type = data;
                this.applyFilters();
                this.$root.logData('overview', 'apply filter by type', JSON.stringify(data));
            },

            applyUsedProductsFilter: function (data) {
                this.appliedFilters.usedProducts = data;
                this.applyFilters();
                this.$root.logData('overview', 'apply filter by used products', JSON.stringify(data));
            },

            applyTagsFilter: function (data) {
                this.appliedFilters.tags = data;
                this.applyFilters();
                this.$root.logData('overview', 'apply filter by tags', JSON.stringify(data));
            },

            applySortByFilter: function (data) {
                this.appliedFilters.sortBy = data;
                this.applyFilters(true);
                this.$root.logData('overview', 'apply filter by sort', JSON.stringify(data));
            },

            listenToTotalPointsDisplayedOnMapChanged: function () {
                this.$eventGlobal.$on('totalPointsDisplayedOnMapChanged', (totalPoints) => {
                    this.totalPointsInCurrentMap = totalPoints
                });
            },

            composeQueryUrl: function () {
                let queryStr = '';

                if (this.appliedFilters.type) {
                    queryStr += '&type-id=' + this.appliedFilters.type;
                }

                if (this.appliedFilters.globalSearch.length) {
                    this.appliedFilters.globalSearch.forEach(id => {
                        queryStr += '&iteration[]=' + id;
                    });
                }

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

                if(this.$route.query['address-ids']){
                    queryStr += '&address-ids=' + this.$route.query['address-ids'];
                }

                if (this.appliedFilters.sortBy) {
                    queryStr += '&sort-by=' + this.appliedFilters.sortBy;
                }

                this.queryUrl = queryStr;

                return queryStr;
            },

            loadAddressesPaginated: function () {

                let url = '/api/addresses-paginated?page=' + this.pagination.currentPage + this.queryUrl;

                return this.httpGet(url)
                    .then(data => {
                        this.addressesTotal = data.total;
                        this.addressList = data.data;

                        if(!this.appliedFilters.isOnlySortingChanged){
                            this.notifyFiltersHaveBeenApplied(this.queryUrl.replace('&','?'));
                        }

                        this.isFirstLoad = false;

                        this.oldQueryUrl = this.queryUrl;

                        this.unifyAddressesWithDuplicatedNames(this.addressList);

                        return data;
                    })

            },

            pageChanged: function (pageNumber) {
                this.pagination.currentPage = pageNumber;
                this.loadAddressesPaginated();
                this.$root.logData('overview', 'page changed', JSON.stringify(pageNumber));
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

                this.$router.push('/dashboard?'+this.queryUrl);
            },

            resetFilters: function () {

                this.$refs.typeSingleDropdownSelect.resetSelectedValues();
                this.$refs.productsMultipleDropdownSelect.resetSelectedValues();
                this.$refs.tagMultipleDropdownSelect.resetSelectedValues();
                this.$refs.sortBySingleDropdownSelect.resetSelectedValues();

                this.appliedFilters = {
                    usedProducts: [],
                    tags: [],
                    type: '',
                    sortBy: '',
                    isOnlySortingChanged: false,
                    globalSearch: [],
                    addressIds: ''
                };

                this.$eventGlobal.$emit('resetedAllFilters');

                if(this.$route.query['global-search'] || this.$route.query['address-ids']) {
                    this.$router.push('/dashboard');
                }

                this.applyFilters();
                this.$root.logData('overview', 'reset filters', JSON.stringify(''));
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
            }
        }
    }
</script>

<style scoped>

</style>