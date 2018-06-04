<template>
    <section class="sidebar">
        <div class="address-details-container slider-container" :class="{expanded: isExpanded}">

            <div class="slided-box">

                <div v-if="sideComponentToDisplay == 'all-employee'">
                    <all-employee-list
                        :addressId="addressId"
                        :isActive="isExpanded && sideComponentToDisplay == 'all-employee'"
                        :employeeList="addressData.people"
                        :address="addressData"
                        @closeSlidedBox="onCloseSlidedBox"
                    ></all-employee-list>
                </div>

                <div v-if="sideComponentToDisplay == 'lab-chain-details'">
                    <lab-chain-details
                        :isActive="isExpanded && sideComponentToDisplay == 'lab-chain-details'"
                        :addressId="addressId"
                        :addressData="addressData"
                        @closeSlidedBox="onCloseSlidedBox"
                    ></lab-chain-details>
                </div>

            </div>

            <div class="address-details-fixed-height">

                <router-link to="/dashboard" title="Back to dashboard" class="link-back arrow-left">
                    <i class="fa fa-angle-left"></i>
                </router-link>

                <div class="address-overview">

                    <customer-status-select
                        :options="customerStatusList"
                        :selected="addressData.customer_status"
                        :addressId="addressId"
                        @customerStatusUpdated="updateCustomerStatus"
                    ></customer-status-select>

                    <div v-if=" ! isEditing">
                        <h2>
                            <span style="vertical-align: middle">{{ addressData.name }}</span>

                            <a href="javascript:void(0)" @click="toggleEditing" :class="{'active': isEditing}">
                                <i class="fa fa-pencil"></i>
                            </a>

                            <a href="javascript:void(0)" title="Show on Map" @click="showOnMap()"><i class="fa fa-map-marker"></i></a>
                        </h2>

                        <div style="clear: both"></div>

                        <p class="lab-chain">
                            <span class="current-chain-name">{{addressData.cluster.name}}</span>

                            <br />

                            <span class="lab-chain-text">Lab Chain:</span>
                            <autocompleteSelect 
                                v-if="chainSelect" 
                                :type="'clusters'" 
                                :selected="addressData.cluster" 
                                :close="closeChain" 
                                :choose="addChain" 
                            />
                            <a href="#" @click.prevent="toggleChain" class="add-to-chain-link">Add to Chain</a>
                        </p>

                        <ul class="tag-list">
                            <li v-for="tag in addressData.tags" :key="tag.id">
                                <a href="#" @click.prevent>
                                    {{ tag.name }}
                                </a>
                            </li>
                        </ul>

                        <p class="address-line">
                            {{ addressData.address }}
                        </p>

                        <p class="link-and-phone">
                            <a :href="addressData.url" target="_blank">
                                {{ addressData.url.replace('https://', '').replace('http://', '') }}
                            </a>
                            <span class="pone-number">{{ addressData.phone }}</span>
                        </p>
                    </div>

                    <div v-else>
                        <div @click="toggleEditingInput('name')">
                            <div class="name-block can-edit">
                                <div-editable 
                                    @updateEdit="updateAddress" 
                                    :content.sync="addressData.name" 
                                    :placeholder="'Name'"
                                ></div-editable>
                            </div>
                        </div>

                        <div style="clear: both"></div>

                        <p class="lab-chain">
                            <span class="current-chain-name">{{addressData.cluster.name}}</span>

                            <br />

                            <span class="lab-chain-text">Lab Chain:</span>
                            <autocompleteSelect 
                                v-if="chainSelect" 
                                :type="'clusters'" 
                                :selected="addressData.cluster" 
                                :close="closeChain" 
                                :choose="addChain"
                            />
                            <a href="#" @click.prevent="toggleChain" class="add-to-chain-link">Add to Chain</a>
                        </p>

                        <ul v-if="editingInput !== 'tags'" class="tag-list tags-edit">
                            <li v-for="tag in addressData.tags" :key="tag.id">
                                <a href="#" @click.prevent>
                                    {{ tag.name }}
                                    <button class="delete-tag" @click="removeSelectedTag(tag.name)">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click.prevent="toggleEditingInput('tags')" class="add-tag">
                                    Add Tag
                                </a>
                            </li>
                        </ul>

                        <v-select 
                            v-show="editingInput === 'tags'"
                            v-model="addressData.tags"
                            :options="allTags"
                            :label="'name'"
                            :class="'tags-select'"
                            multiple
                            taggable
                            push-tags
                            :placeholder="'Select tags'"
                        />

                        <p class="address-line can-edit" @click="toggleEditingInput('address')">
                            <div-editable 
                                @updateEdit="updateAddress" 
                                :content.sync="addressData.address" 
                                :placeholder="'Address'"
                            ></div-editable>
                        </p>

                        <p class="address-line can-edit" @click="toggleEditingInput('url')">
                            <div-editable 
                                @updateEdit="updateAddress" 
                                :content.sync="addressData.url" 
                                :placeholder="'URL'"
                            ></div-editable>
                        </p>

                        <p class="address-line can-edit" @click="toggleEditingInput('phone')">
                            <div-editable 
                                @updateEdit="updateAddress" 
                                :content.sync="addressData.phone" 
                                :placeholder="'Phone Number'"
                            ></div-editable>
                        </p>

                        <div class="confirm-edit-block">
                            <button
                                type="button"
                                @click="toggleEditing"
                                class="btn cancel-address-btn"
                            >
                                Cancel Editing
                            </button>
                            <button
                                type="submit" 
                                v-if=" ! saveBtnDisabled && madeChanges" 
                                @click.prevent="updateAddress" 
                                class="btn save-address-btn"
                            >
                                Suggest Edits
                            </button>
                            <button 
                                type="button" 
                                v-if="saveBtnDisabled || ! madeChanges" 
                                disabled 
                                class="btn save-address-btn-disabled"
                            >
                                Suggest Edits
                            </button>

                        </div>
                    </div>
                </div>

                <div class="staff-overview address-box">
                    <div class="header">
                        <h3>Staff 
                            <a href="#" @click.prevent class="without-handler">
                                <i class="fa fa-pencil"></i>
                            </a>
                        </h3>
                        <a href="javascript:void(0)" 
                            @click="showContactsChain(addressData)" 
                            class="view-contacts-chain"
                        >
                            View Relationship Graph
                        </a>
                    </div>

                    <p v-if="!addressData.people.length" class="empty-data-p">There are no employees yet.</p>

                    <ul class="staff-list">
                        <li v-if="i < 3" v-for="(person, i) in addressData.people" :key="person.id">
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
                                <p class="occupation">{{ person.description }}</p>
                            </div>
                        </li>
                    </ul>

                    <div style="clear: both"></div>

                    <a href="#"
                       v-if="addressData.people && addressData.people.length > 3"
                       @click.prevent="showSlidedBox('all-employee')"
                       class="address-box-show-more-link show-all-employees-link"
                    >
                        Show all Employees
                    </a>
                </div>

                <div class="used-products-overview address-box">
                    <div class="header">
                        <h3>
                            Used Products 
                            <a href="#" @click.prevent="toggleProducts">
                                <i class="fa fa-pencil"></i>
                            </a>
                        </h3>
                    </div>

                    <p v-if=" ! addressData.products.length" class="empty-data-p">There are no used products</p>

                    <ul class="used-products-list" v-if="addressData.products.length">
                        <li v-if=" ! showAllProducts && i < 3" v-for="(product, i) in addressData.products" 
                            :title="productName(product.company, product.name)"
                            :key="product.id"
                        >
                            <img class="image" :src="product.image" v-if="product.image">
                            <div class="image" v-else></div>
                            <span class="prod-name">
                                {{ productName(product.company, product.name) }}
                            </span>
                        </li>

                        <li v-if="showAllProducts" 
                            v-for="product in addressData.products" 
                            :title="productName(product.company, product.name)"
                            :key="product.id"
                        >
                            <img class="image" :src="product.image" v-if="product.image">
                            <div class="image" v-else></div>
                            <span class="prod-name">
                                {{ productName(product.company, product.name) }}
                            </span>
                        </li>
                        
                        <li v-if="addressData.products.length > 3">
                            <a href="#" @click.prevent="toggleShowAllProducts" class="show-all-link prod-name">
                                {{ showHideProducts }}
                            </a>
                        </li>
                    </ul>
                    <multiple-autocomplete-select 
                        v-if="isProductsEditing"
                        :selectedOptions="addressData.products"
                        :type="'products'"
                        :close="closeProducts"
                        :update="updateProducts"
                        :addNewProduct="addNewProduct"
                    ></multiple-autocomplete-select>
                </div>

                <div class="lab-chain-members-overview address-box">
                    <div class="header">
                        <h3>Lab Chain Members 
                            <small :title="'Addresses in chain: ' + addressData.cluster.addresses.length">
                                ({{addressData.cluster.addresses.length}})
                            </small>
                        </h3>
                    </div>

                    <p v-if="addressData.cluster.addresses.length === 1" 
                        class="empty-data-p">
                        Current address is the only member in this chain
                    </p>

                    <ul class="lab-chain-member-list">
                        <li v-if="c.id != addressData.id && i < 3" 
                            v-for="(c, i) in addressData.cluster.addresses"
                            :key="c.id"
                        >
                            <h4><router-link :to="'/address-details/'+c.id">{{c.name}}</router-link></h4>
                            <p>{{c.address}}</p>
                        </li>
                    </ul>

                    <a href="javascript:void(0)" @click="showSlidedBox('lab-chain-details')" v-if="addressData.cluster.addresses.length > 1" class="address-box-show-more-link">Lab Chain Details</a>
                </div>

                <div class="lab-news-overview address-box">
                    <div class="header">
                        <h3>Lab News 
                            <a href="#" @click.prevent class="without-handler">
                                <i class="fa fa-plus"></i>
                            </a>
                        </h3>
                    </div>

                    <ul class="lab-news-list">
                        <li>
                            <p class="date">Feb 21, 2018</p>
                            <h4>New employee <a href="#" @click.prevent class="without-handler">
                                Jina James</a> joined the lab</h4>
                        </li>
                        <li>
                            <p class="date">Feb 21, 2018</p>
                            <h4>High-precision analysis of DNA is now provided by lab</h4>
                        </li>
                    </ul>

                    <a href="#" @click.prevent class="address-box-show-more-link without-handler">Go to Lab News</a>
                </div>
            </div>

        </div>
    </section>
</template>

<script>

    import http from '../../mixins/http';
    import employeeModal from '../../mixins/show-employee-details-modal';
    import getPersonInitials from '../../mixins/get-person-initials';
    import autocompleteSelect from '../../partial-view-components/autocomplete-select';
    import multipleAutocompleteSelect from '../../partial-view-components/multiple-autocomplete-select';

    export default {
        mixins: [http, employeeModal, getPersonInitials],
        components: {
            autocompleteSelect,
            multipleAutocompleteSelect
        },
        data: function () {
            return {
                addressId: null,
                addressData: {
                    tags: [],
                    url: '',
                    cluster: {
                        addresses: []
                    },
                    people: [],
                    products: []
                },
                customerStatusList: [],
                chainList: [],
                isExpanded: false,
                sideComponentToDisplay: '',
                isFirstLoad: true,
                chainSelect: false,
                isEditing: false,
                editingInput: null,
                saveBtnDisabled: false,
                madeChanges: false,
                old: {
                    name: '',
                    address: '',
                    url: '',
                    phone: '',
                    tags: []
                },
                allTags: [],

                isProductsEditing: false,
                showAllProducts: false,
            }
        },

        watch:{
            $route: function(to){
                this.addressId = this.$route.params['id'];
                this.loadAddressDetails();

                if( ! this.isFirstLoad) {
                    this.showModalIfPersonHashDetected();
                }
            },
            isEditing: function () {
                if ( ! this.isEditing) {
                    this.editingInput = null;
                }
            },
            "addressData.name": function () {
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "addressData.address": function () {
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "addressData.url": function () {
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "addressData.phone": function () {
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "addressData.tags": function () {
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            }
        },

        methods: {
            checkIfInputsEmpty: function () {
                this.saveBtnDisabled = this.addressData.name === '' ||
                    this.addressData.address === '' ||
                    this.addressData.url === '' ||
                    this.addressData.phone === '' ||
                    this.addressData.tags.length < 1 
                    ? true : false;
            },
            checkIfChangesMade: function () {
                this.madeChanges = this.addressData.name !== this.old.name ||
                    this.addressData.phone !== this.old.phone ||
                    this.addressData.address !== this.old.address ||
                    this.addressData.url !== this.old.url ||
                    this.compareTags() 
                    ? true : false;
            },
            compareTags: function() {
                let _this = this,
                    sortedTags = this.addressData.tags.slice(),
                    sortedOldTags = this.old.tags.slice();
                // sortedTags.sort(function(a, b) {
                //     let c = a.name,
                //         d = b.name;
                //     if (c > d) return 1;
                //     if (c < d) return -1;
                // });
                // sortedOldTags.sort(function(a, b) {
                //     let c = a.name,
                //         d = b.name;
                //     if (c > d) return 1;
                //     if (c < d) return -1;
                // });
                sortedTags.sort((tagA, tagB) => {
                    return tagA.name > tagB.name;
                });
                
                sortedOldTags.sort((tagA, tagB) => {
                    return tagA.name > tagB.name;
                });
                
                if (sortedTags.length === sortedOldTags.length) {
                    for (let i = 0; i < sortedTags.length; i++) {
                        if (sortedTags[i].name !== sortedOldTags[i].name) {
                            _this.madeChanges = true;
                            break;
                        } else {
                            _this.madeChanges = false;
                        }
                    }
                } else {
                    _this.madeChanges = true;
                }

                return _this.madeChanges ? true : false ;
            },
            loadAddressDetails: function () {
                return this.httpGet('/api/address-details/' + this.addressId)
                    .then(data => {
                        this.addressData = data;
                        document.title = this.addressData.name;
                    })
            },
            loadCustomerStatusList: function () {
                this.httpGet('/api/customer-statuses')
                    .then(data => {
                        this.customerStatusList = data;
                    })
            },
            loadChainList: function () {
                this.httpGet('/api/clusters')
                    .then(data => {
                        this.chainList = data;
                    })
            },
            addChain: function (id) {
                this.httpPut('/api/clusters/' + this.addressId, {
                        cluster_id: id
                    })
                    .then(data => {
                        this.addressData.cluster.id = data.cluster.id
                        this.addressData.cluster.name = data.cluster.name
                        this.addressData.cluster.addresses = data.cluster.addresses
                        alertify.notify('Chain has been updated.', 'success', 3);
                        this.chainSelect = false;
                        this.$eventGlobal.$emit('addressClusterUpdated');
                    })
                    .catch(err => {
                        alertify.notify('Error occured', 'error', 3);
                    })
            },
            updateCustomerStatus: function (status) {
                this.httpPut('/api/address-details/' + this.addressId + '/update-status', {
                        status: status
                    })
                    .then(data => {
                        this.addressData.customer_status = data.customer_status;
                        alertify.notify('Status has been updated.', 'success', 3);
                    })
                    .catch(err => {
                        alertify.notify('Error occured', 'error', 3);
                    })
            },
            showSlidedBox: function (componentToDisplay) {
                if(this.sideComponentToDisplay == componentToDisplay) {
                    this.isExpanded = !this.isExpanded;
                    componentToDisplay = '';
                } else {
                    this.isExpanded = true;
                }
                this.sideComponentToDisplay = componentToDisplay;
            },
            showContactsChain: function (addressData) {
                this.$eventGlobal.$emit('showModalContactsChain', addressData)
            },
            showOnMap: function () {
                this.$eventGlobal.$emit('showSpecificItem', [this.addressData])
            },
            showModalIfPersonHashDetected: function () {
                if(this.$route.hash.indexOf('#person-') !== -1) {
                    let personId = this.$route.hash.replace('#person-', '');
                    this.showEmployeeDetailsModal(personId, this.addressId, this.addressData);
                }
            },
            loadAllTags: function () {
                this.httpGet('/api/address-details/' + this.addressId + '/get-all-tags')
                    .then(data => {
                        this.allTags = data;
                    })
            },
            loadSelectedTags: function () {
                this.httpGet('/api/address-details/' + this.addressId + '/load-selected-tags')
                    .then(data => {
                        this.old.tags = data;
                    })
            },
            toggleEditing: function () {
                this.isEditing = !this.isEditing;
                if ( ! this.isEditing) {
                    this.addressData.name = this.old.name;
                    this.addressData.address = this.old.address;
                    this.addressData.url = this.old.url;
                    this.addressData.phone = this.old.phone;
                    this.addressData.tags = this.old.tags;
                } else {
                    this.loadSelectedTags();
                    this.checkIfChangesMade();
                }
            },
            toggleEditingInput: function (input) {
                this.editingInput = input;
            },
            updateAddress: function () {
                if (this.madeChanges && ! this.saveBtnDisabled) {
                    this.httpPut('/api/address-details/' + this.addressData.id + '/update-details', {
                        name: this.addressData.name,
                        address: this.addressData.address,
                        url: this.addressData.url,
                        phone: this.addressData.phone,
                        tags: this.addressData.tags
                    })
                    .then(data => {
                        this.old.name = this.addressData.name;
                        this.old.address = this.addressData.address;
                        this.old.url = this.addressData.url;
                        this.old.phone = this.addressData.phone;
                        this.loadAllTags();
                        this.loadSelectedTags();
                        this.madeChanges = false;
                        this.saveBtnDisabled = false;
                        this.editingInput = null;
                        this.isEditing = false;
                        alertify.notify('Address has been updated.', 'success', 3);
                    })
                    .catch(err => {
                        alertify.notify('Error occured', 'error', 3);
                    })
                }  
            },
            removeSelectedTag: function (name) {
                let tags = this.addressData.tags.filter(function(item) {
                    return item.name != name;
                });

                this.addressData.tags = tags;
            },
            closeChain: function () {
                this.chainSelect = false;
            },
            toggleChain: function () {
                this.chainSelect = !this.chainSelect
            },
            closeProducts: function () {
                this.isProductsEditing = false;
            },
            toggleProducts: function () {
                this.isProductsEditing = !this.isProductsEditing;
            },
            updateProducts: function (selectedProducts) {
                this.httpPut('/api/products/' + this.addressData.id, {
                        selectedProducts: selectedProducts
                    })
                    .then(data => {
                        this.addressData.products = [];
                        this.addressData.products = data.products;
                        alertify.notify('Used products has been updated.', 'success', 3);
                        this.closeProducts();
                        this.$eventGlobal.$emit('addressProductsUpdated')
                    })
                    .catch(err => {
                        alertify.notify('Error occured', 'error', 3);
                    })
            },
            addNewProduct: function (newItem) {
                let formData = new FormData();
                formData.append('company', newItem.company);
                formData.append('name', newItem.name);
                formData.append('description', newItem.description);
                formData.append('image', newItem.image);
                this.httpPost('/api/products/create',
                    formData
                )
                .then(data => {
                    if (data.status) {
                        alertify.notify(data.message, 'error', 3);
                        return;
                    }
                    this.closeProducts();
                    alertify.notify('New product has been added.', 'success', 3);
                })
                .catch(error => {
                    console.log(error);
                    alertify.notify('Error occured', 'error', 3);
                });
            },
            toggleShowAllProducts: function () {
                this.showAllProducts = !this.showAllProducts;
            },
            productName: function (company, name) {
                return name ? company + ': ' + name : company;
            },
            onCloseSlidedBox: function () {
                this.isExpanded = false;
                this.sideComponentToDisplay = '';
            }
        },
        computed: {
            showHideProducts: function () {
                return this.showAllProducts ? 'Hide' : 'Show all';
            }
        },

        mounted: function () {
            $('.address-details-fixed-height').height(window.innerHeight - 70 - 51);
            $('.slided-box').height(window.innerHeight - 70 - 51);

            this.addressId = this.$route.params.id;

            this.loadAllTags();

            this.loadAddressDetails()
                .then(() => {
                    this.showModalIfPersonHashDetected();
                    this.isFirstLoad = false;
                    this.old.name = this.addressData.name;
                    this.old.address = this.addressData.address;
                    this.old.url = this.addressData.url;
                    this.old.phone = this.addressData.phone;
                    this.madeChanges = false;
                    this.saveBtnDisabled = true;
                });

            this.loadCustomerStatusList();

            if(this.$route.query['all-employees']) {
                setTimeout(() => {
                    this.showSlidedBox('all-employee');
                }, 0)
            }

            this.showModalIfPersonHashDetected();
        }
    }
</script>

<style scoped>
    .used-products-list li .image {
        position: absolute;
        top: 0px;
        left: 0px;
        width: 25px;
        height: 25px;
        background-color: #eaeff4;
        border-radius: 50%;
        margin-right: 10px;
    }

    ul.used-products-list li span.prod-name {
        margin-left: 30px;
    }

    ul.used-products-list li {
        position: relative;
    }
</style>