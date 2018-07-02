<template>
    <div class="slided-box-content lab-chain-details">

        <a href="javascript:void(0)" class="close-icon-a" @click="closeSlidedBox()">
            <img src="/images/x.png" alt="">
        </a>

        <h3 class="cluster-title" v-if=" ! isClusterEdit">
            <span class="cluster-name">
                {{ addressData.cluster.name }}
            </span>
            <a class="cluster-title-edit" data-v-cd5686be="" href="#" @click.prevent="toggleClusterEdit">
                <i data-v-cd5686be="" class="fa fa-pencil"></i>
            </a>
        </h3>

        <div v-if="isClusterEdit">
            <div class="form-group edit-name-input-block">
                <div-editable 
                    @updateEdit="updateCluster"
                    :content.sync="addressData.cluster.name"
                    :placeholder="'Lab chain name'"
                />
            </div>
            <div class="confirm-edit-block">
                <button
                    type="button"
                    @click="toggleClusterEdit"
                    class="btn cancel-cluster-btn"
                >
                    Cancel Editing
                </button>
                <button
                    type="submit" 
                    v-if=" ! saveBtnDisabled && madeChanges" 
                    @click.prevent="updateCluster" 
                    class="btn save-cluster-btn"
                >
                    Suggest Edits
                </button>
                <button 
                    type="button" 
                    v-if="saveBtnDisabled || ! madeChanges" 
                    disabled 
                    class="btn save-cluster-btn-disabled"
                >
                    Suggest Edits
                </button>
            </div>
        </div>

        <div class="lab-chain-members-overview address-box" v-if="isShowLabChainMembersCollapsed">
            <div class="header">
                <h3>Lab Chain Members <small :title="'Addresses in chain: ' + addressData.cluster.addresses.length">({{addressData.cluster.addresses.length}})</small></h3>
            </div>

            <p v-if="addressData.cluster.addresses.length === 1" class="empty-data-p">Current address is the only member in this chain</p>

            <ul class="lab-chain-member-list">
                <li v-if="c.id != addressData.id && i < 3" v-for="(c,i) in addressData.cluster.addresses">
                    <h4><router-link :to="'/address-details/'+c.id">{{c.name}}</router-link></h4>
                    <p>{{c.address}}</p>
                </li>
            </ul>

            <a href="javascript:void(0)" @click="showLabChainMembersPaginated()" class="address-box-show-more-link">Show all lab chain members</a>
        </div>
        <div class="lab-chain-members-overview address-box" v-if="!isShowLabChainMembersCollapsed">
            <div class="header">
                <h3>Lab Chain Members <small :title="'Addresses in chain: ' + addressData.cluster.addresses.length">({{addressData.cluster.addresses.length}})</small></h3>
            </div>


            <ul class="lab-chain-member-list">
                <li v-for="(addr, i) in clusterAddresses.data">
                    <h4><router-link :to="'/address-details/'+addr.id">{{addr.name}}</router-link></h4>
                    <p>{{addr.address}}</p>
                </li>
            </ul>

            <div class="show-less-btn"><a @click="isShowLabChainMembersCollapsed = true" href="javascript:void(0)">Show Less</a></div>

            <div class="pagination-box">
                <pagination :records="clusterAddresses.total"  :class="'pagination pagination-sm no-margin pull-right'" :per-page="10" @paginate="pageChanged"></pagination>
            </div>
        </div>


        <div class="lab-chain-staff staff-overview address-box">

            <div class="header">
                <h3>Lab Chain Staff 
                    <a href="#" 
                        @click.prevent 
                        class="without-handler"
                    >
                        <i class="fa fa-pencil"></i>
                    </a>
                </h3>
            </div>

            <div class="search-block">
                <i class="fa fa-search icon" aria-hidden="true"></i>
                <input v-model="query" 
                    type="text" 
                    class="people-search-input"
                    @keydown.enter.prevent="handleSearch"
                    @input="handleSearch"
                    placeholder="Employee name"
                    autocomplete="off"
                >

                <v-select 
                    :options="roles"
                    :label="'name'"
                    :class="'roles'"
                    :searchable="false"
                    :placeholder="'Choose role'"
                    v-model="selectedRole"
                />
            </div>

            

            <ul class="staff-list" v-if=" ! canSearch && ! canSearchByRole && isShowLabChainStaffCollapsed">
                <li v-if="i < 3" v-for="(person, i) in clusterStaff.data">
                    <div class="image">
                        <a href="javascript:void(0)" 
                            @click="showEmployeeDetailsModal(person.id, addressData.id, addressData)"
                        >
                            <span class="person-initials">
                                {{ getPersonInitials(person.name) }}
                            </span>
                            <img :src="'/images/mask-'+i+'.png'" alt="">
                        </a>
                    </div>
                    <div class="personal-info">
                        <p class="name">
                            <a href="javascript:void(0)" 
                                @click="showEmployeeDetailsModal(person.id, addressData.id, addressData)"
                            >
                                {{ person.name }}
                            </a>
                        </p>
                        <p class="occupation" v-if="person.addresses.length">
                            at <a class="product-at-link" :href="'/address-details/' + person.addresses[0].id">{{ person.addresses[0].name }}</a> <span class="worked-at-other" 
                                                                    v-tooltip.bottom="{ html: 'tooltipContent' + i }" 
                                                                    v-if="person.addresses.length > 1"
                                                                >and <strong>{{ person.addresses.length - 1 }}</strong> other</span> 
                            <div class="product-tooltip" v-if="person.addresses.length > 1" :id="'tooltipContent' + i">
                                <p v-if="k > 0" v-for="(address, k) in person.addresses">
                                    {{ address.name }}
                                </p>
                            </div>
                        </p>
                        <p class="occupation">{{ person.description }}</p>
                    </div>
                </li>
            </ul>
            
            <ul class="staff-list" v-if=" ! canSearch && ! canSearchByRole && ! isShowLabChainStaffCollapsed ">
                <li v-for="(person, i) in clusterStaff.data">
                    <div class="image">
                        <a href="javascript:void(0)" 
                            @click="showEmployeeDetailsModal(person.id, addressData.id, addressData)"
                        >
                            <span class="person-initials">{{ getPersonInitials(person.name) }}</span>
                            <img :src="'/images/mask-'+i+'.png'" alt="">
                        </a>
                    </div>
                    <div class="personal-info">
                        <p class="name">
                            <a href="javascript:void(0)" 
                                @click="showEmployeeDetailsModal(person.id, addressData.id, addressData)"
                            >
                                {{ person.name }}
                            </a>
                        </p>
                        <p class="occupation" v-if="person.addresses.length">
                            at <a class="product-at-link" :href="'/address-details/' + person.addresses[0].id">{{ person.addresses[0].name }}</a> <span class="worked-at-other" 
                                                                    v-tooltip.bottom="{ html: 'tooltipContent' + i }" 
                                                                    v-if="person.addresses.length > 1"
                                                                >and <strong>{{ person.addresses.length - 1 }}</strong> other</span> 
                            <div class="product-tooltip" v-if="person.addresses.length > 1" :id="'tooltipContent' + i">
                                <p v-if="k > 0" v-for="(address, k) in person.addresses">
                                    {{ address.name }}
                                </p>
                            </div>
                        </p>
                        <p class="occupation">{{person.description}}</p>
                    </div>
                </li>
            </ul>

            <ul class="staff-list" v-if="canSearch || canSearchByRole">
                <li v-for="(person, i) in filtered">
                    <div class="image">
                        <a href="javascript:void(0)" 
                            @click="showEmployeeDetailsModal(person.id, addressData.id, addressData)"
                        >
                            <span class="person-initials">
                                {{ getPersonInitials(person.name) }}
                            </span>
                            <img :src="'/images/mask-'+i+'.png'" alt="">
                        </a>
                    </div>
                    <div class="personal-info">
                        <p class="name">
                            <a href="javascript:void(0)" 
                                @click="showEmployeeDetailsModal(person.id, addressData.id, addressData)"
                            >
                                {{ person.name }}
                            </a>
                        </p>
                        <p class="occupation" v-if="person.addresses.length">
                            at <a class="product-at-link" :href="'/address-details/' + person.addresses[0].id">{{ person.addresses[0].name }}</a> <span class="worked-at-other" 
                                                                    v-tooltip.bottom="{ html: 'tooltipContent' + i }" 
                                                                    v-if="person.addresses.length > 1"
                                                                >and <strong>{{ person.addresses.length - 1 }}</strong> other</span> 
                            <div class="product-tooltip" v-if="person.addresses.length > 1" :id="'tooltipContent' + i">
                                <p v-if="k > 0" v-for="(address, k) in person.addresses">
                                    {{ address.name }}
                                </p>
                            </div>
                        </p>
                        <p class="occupation">{{ person.description }}</p>
                    </div>
                </li>
            </ul>

            <div v-if="(canSearch || canSearchByRole) && filtered.length < 1">No matches</div>

            <div style="clear: both"></div>

            <a v-show="clusterStaff.data.length > 3 && isShowLabChainStaffCollapsed && ! canSearch && ! canSearchByRole" 
                href="javascript:void(0)" @click="showLabChainStaffPaginated()" 
                class="address-box-show-more-link">Show all Employees</a>

            <div class="show-less-btn" v-show=" ! isShowLabChainStaffCollapsed && ! canSearch && ! canSearchByRole">
                <a @click="isShowLabChainStaffCollapsed = true" 
                href="javascript:void(0)">Show Less</a>
            </div>

            <div class="pagination-box" v-show="! canSearch && ! canSearchByRole" v-if=" ! isShowLabChainStaffCollapsed">
                <pagination :records="clusterStaff.total" 
                    :class="'pagination pagination-sm no-margin pull-right'" 
                    :per-page="10" @paginate="staffPageChanged"
                >
                </pagination>
            </div>
        </div>

        <div class="lab-chain-staff staff-overview address-box" v-if="isProductCollapsed">

            <div class="header">
                <h3>Used Products 
                    <a href="#" @click.prevent class="without-handler">
                        <i class="fa fa-pencil"></i>
                    </a>
                </h3>
            </div>

            <p v-if="!clusterProducts.data.length" class="empty-data-p">There are no used products</p>

            <ul class="products-list">
                <li v-if="i < 3" v-for="(product, i) in clusterProducts.data">
                    <img class="product-image" :src="product.image" alt="" v-if="product.image">
                    <img class="product-image" :src="'/images/mask-'+i+'.png'" alt="" v-else>
                    <div class="product-description-block">
                        <span class="product-description">
                            {{ product.name ? product.company + ': ' + product.name : product.company }}
                        </span>

                        <span class="product-also-use">
                            at <a class="product-at-link" :href="'/address-details/' + product.addresses[0].id">
                                {{ product.addresses[0].name }}
                            </a>
                            <span v-if="product.addresses.length > 1" v-tooltip.bottom="{ html: 'tooltipProductContent' + i }">
                                and {{ product.addresses.length - 1 }} other
                            </span>
                            <div class="product-tooltip" v-if="product.addresses.length > 1" :id="'tooltipProductContent' + i">
                                <p v-if="k > 0" v-for="(address, k) in product.addresses">
                                    {{ address.name }}
                                </p>
                            </div>
                        </span>
                    </div>


                </li>
            </ul>

            <div style="clear: both"></div>

            <a v-if="clusterProducts.data.length > 3" 
                href="javascript:void(0)" 
                @click="showProductsPaginated()" 
                class="address-box-show-more-link"
            >
                Show all {{ clusterProducts.data.length }} products
            </a>
        </div>

        <div class="lab-chain-staff staff-overview address-box" v-if="!isProductCollapsed">

            <div class="header">
                <h3>Used Products 
                    <a href="#" @click.prevent class="without-handler">
                        <i class="fa fa-pencil"></i>
                    </a>
                </h3>
            </div>

            <ul class="products-list">
                <li v-for="(product, i) in clusterProducts.data">
                    <img class="product-image" :src="product.image" alt="" v-if="product.image">
                    <img class="product-image" :src="'/images/mask-'+i+'.png'" alt="" v-else>
                    <div class="product-description-block">
                        <span class="product-description">
                            {{ product.name ? product.company + ': ' + product.name : product.company }}
                        </span>

                        <span class="product-also-use">
                            at <a class="product-at-link" :href="'/address-details/' + product.addresses[0].id">{{ product.addresses[0].name }}</a>
                            <span v-if="product.addresses.length > 1" v-tooltip.bottom="{ html: 'tooltipProductContent' + i }">
                                and {{ product.addresses.length - 1 }} other
                            </span>

                            <div class="product-tooltip" v-if="product.addresses.length > 1" :id="'tooltipProductContent' + i">
                                <p v-if="k > 0" v-for="(address, k) in product.addresses">
                                    {{ address.name }}
                                </p>
                            </div>
                        </span>
                    </div>


                </li>
            </ul>

            <div class="show-less-btn"><a @click="isProductCollapsed = true" href="javascript:void(0)">Show Less</a></div>

            <div class="pagination-box">
                <pagination :records="clusterProducts.total"  :class="'pagination pagination-sm no-margin pull-right'" :per-page="10" @paginate="productsPageChanged"></pagination>
            </div>

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
                isShowLabChainMembersCollapsed: true,
                isShowLabChainStaffCollapsed: true,
                isProductCollapsed: true,
                clusterAddresses: {
                    total: 0,
                    data: []
                },
                clusterStaff: {
                    total: 0,
                    data: []
                },
                clusterProducts: {
                    total: 0,
                    data: []
                },
                empList: [],
                filtered: [],
                roles: [],
                selectedRole: {
                    id: -1,
                    name: "All Employees"
                },
                defaultRole: {
                    id: -1,
                    name: "All Employees"
                },
                query: '',
                isClusterEdit: false,
                saveBtnDisabled: true,
                madeChanges: false,
                old: {
                    clusterName: ''
                }
            }
        },

        watch: {
            isActive: function(newVal){
                if(newVal) {
                    this.loadClusterStaffPaginated();
                }
            },
            selectedRole: function () {
                if (this.selectedRole == null) {
                    this.selectedRole = this.defaultRole
                }
                this.handleSearch()
            },
            "addressData.cluster.name": function () {
                this.checkIfInputsEmpty();
                if (this.isClusterEdit) {
                    this.checkIfChangesMade();
                }
            },
        },

        methods: {
            showLabChainMembersPaginated: function() {
                this.isShowLabChainMembersCollapsed = false;
                this.loadClusterDetails();
                this.$root.logData('labchain', 'show labchain members paginated', JSON.stringify(''));
            },

            loadClusterDetails: function (page) {

                let p = page || 1;

                this.httpGet('/api/address-details/'+this.addressData.id+'/get-cluster-members-paginated?page='+p)
                    .then(data => {
                        this.clusterAddresses = data;
                    })
            },

            pageChanged: function(pageNumber) {
                this.loadClusterDetails(pageNumber);
                this.$root.logData('labchain', 'labchain members page changed', JSON.stringify(pageNumber));
            },

            showLabChainStaffPaginated: function() {
                this.isShowLabChainStaffCollapsed = false;
                this.loadClusterStaffPaginated();
                this.$root.logData('labchain', 'show labchain staff paginated', JSON.stringify(''));
            },

            staffPageChanged: function(pageNumber) {
                this.loadClusterStaffPaginated(pageNumber)
                this.$root.logData('labchain', 'labchain staff page changed', JSON.stringify(pageNumber));
            },

            loadClusterStaffPaginated: function (page) {
                let p = page || 1;

                this.httpGet('/api/address-details/'+this.addressData.id+'/get-cluster-staff-paginated?page='+p)
                    .then(data => {
                        this.clusterStaff = data;
                    })
            },

            productsPageChanged: function(pageNumber) {
                this.loadClusterProductsPaginated(pageNumber)
                this.$root.logData('labchain', 'labchain products page changed', JSON.stringify(pageNumber));
            },

            showProductsPaginated: function() {
                this.isProductCollapsed = false;
                this.loadClusterProductsPaginated();
                this.$root.logData('labchain', 'show labchain products paginated', JSON.stringify(this.addressData.cluster.name));
            },

            loadClusterProductsPaginated: function (page) {
                let p = page || 1;

                this.httpGet('/api/address-details/'+this.addressData.id+'/get-cluster-products-paginated?page='+p)
                    .then(data => {
                        this.clusterProducts = data;
                    })
            },

            closeSlidedBox: function () {
                this.$emit('closeSlidedBox')
                this.$root.logData('labchain', 'close slided box', JSON.stringify(''));
            },

            productAlsoUse: function (product) {
                let str = ' at <strong>' + product.addresses[0].name + '</strong>';

                if(product.addresses.length > 1) {
                    str += ' and ' + (product.addresses.length - 1) + ' other';
                }

                return str;
            },
            getRoles: function () {
                this.httpGet('/api/get-roles')
                    .then(data => {
                        this.roles = data
                        this.roles.unshift(this.defaultRole)
                        this.selectedRole = this.defaultRole
                    })
            },
            getAllClusterStuff: function () {
                this.httpGet('/api/address-details/'+this.addressData.id+'/get-all-cluster-staff')
                    .then(data => {
                        this.empList = data;
                        this.filtered = this.empList;
                    })
            },
            handleSearch: _.debounce(function (e) {
                this.$root.logData('labchain', 'search', JSON.stringify({
                    searchQuery: this.query,
                    selectedRole: this.selectedRole
                }));
                this.filtered = this.empList.filter((item) => {
                    if (this.canSearch && this.canSearchByRole) {
                        return item.name.toLowerCase().indexOf(this.query.toLowerCase().trim()) + 1 &&
                            item.type_id == this.selectedRole.id
                    } else if (this.canSearch && ! this.canSearchByRole) {
                        return item.name.toLowerCase().indexOf(this.query.toLowerCase().trim()) + 1
                    } else if ( ! this.canSearch && this.canSearchByRole) {
                        return item.type_id == this.selectedRole.id
                    }
                });
            }, 400),
            toggleClusterEdit: function () {
                this.isClusterEdit = !this.isClusterEdit;
                if ( ! this.isClusterEdit) {
                    this.addressData.cluster.name = this.old.clusterName;
                } else {
                    this.checkIfChangesMade();
                }
                this.$root.logData('labchain', 'toggle cluster edit', JSON.stringify(this.isClusterEdit));
            },
            updateCluster: function () {
                if (this.madeChanges && ! this.saveBtnDisabled) {
                    this.$root.logData('labchain', 'update labchain name', JSON.stringify(this.addressData.cluster.name));
                    this.httpPut('/api/address-details/'+this.addressData.id+'/update-cluster-name', {
                        clusterName: this.addressData.cluster.name.trim()
                    })
                    .then(data => {
                        this.addressData.cluster.name = data.name;
                        this.isClusterEdit = false;
                        this.madeChanges = false;
                        this.old.clusterName = data.name;
                        this.$eventGlobal.$emit('clusterNameUpdated');
                        alertify.notify('Lab Chain name has been updated.', 'success', 3);
                    })
                    .catch(err => {
                        alertify.notify('Error occured', 'error', 3);
                    })
                }
            },
            checkIfInputsEmpty: function () {
                this.saveBtnDisabled = this.addressData.cluster.name.trim() === '' ? true : false;
            },
            checkIfChangesMade: function () {
                this.madeChanges = this.addressData.cluster.name.trim() !== this.old.clusterName.trim() ? true : false;
            },
        },

        mounted: function () {
            this.$root.logData('labchain', 'open', JSON.stringify(''));
            this.loadClusterStaffPaginated();
            this.loadClusterProductsPaginated();

            this.getRoles();
            this.getAllClusterStuff();

            this.$eventGlobal.$on('addressClusterUpdated', () => {
                this.isShowLabChainMembersCollapsed = true;
                this.isShowLabChainStaffCollapsed = true;
                this.isProductCollapsed = true;
                this.loadClusterStaffPaginated();
                this.loadClusterProductsPaginated();
            })

            this.$eventGlobal.$on('employeeDetailsUpdated', () => {
                this.isShowLabChainStaffCollapsed = true;
                this.loadClusterStaffPaginated();
            })
            
            this.$eventGlobal.$on('addressProductsUpdated', () => {
                this.isProductCollapsed = true;
                this.loadClusterProductsPaginated();
            })

            this.old.clusterName = this.addressData.cluster.name;
        },

        props: ['employeeList', 'isActive', 'addressId', 'address', 'addressData'],

        computed: {
            canSearch: function () {
                return this.query.trim() === '' || this.selectedRole == null ? false : true
            },
            canSearchByRole: function () {
                return this.selectedRole && this.selectedRole.id != -1 ? true : false
            }
        }
    }
</script>

<style scoped>
    
</style>