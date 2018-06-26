<template>
    <div>
        <div class="modal fade" id="personal-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <div class="person-profile-picture">
                            <span class="person-initials">{{ getPersonInitials(personData.name) }}</span>
                            <img src="/images/mask-7.png" alt="" class="avatar">

                            <a href="javascript:void(0)" class="close-icon-a" data-dismiss="modal" aria-label="Close">
                                <img src="/images/x.png" alt="">
                            </a>
                        </div>

                        <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->

                        <div v-if=" ! isEditing">
                            <h4 class="modal-title">
                                {{personData.name}}
                                <a href="#" @click.prevent="toggleEditing"><i class="fa fa-pencil"></i></a>
                            </h4>

                            <p class="occupation">{{ personData.description }}</p>
                        </div>

                        <div v-else>
                            <div class="employe-name-block can-edit">
                                <div-editable
                                    @updateEdit="updateEmploye"
                                    :content.sync="personData.name"
                                    :placeholder="'Name'"
                                ></div-editable>
                            </div>
                            <div class="employe-description-block can-edit">
                                <div-editable 
                                    @updateEdit="updateEmploye"
                                    :content.sync="personData.description"
                                    :placeholder="'Position'"
                                ></div-editable>
                            </div>
                        </div>

                        <p class="place-of-work" v-if="personData.careers && personData.careers.length">
                            worked at
                            <span v-for="(address, i) in personData.addresses" :key="address.id">
                                <a :href="'/address-details/' + address.id" >
                                    {{ address.name }}</a><span v-if="++i !== personData.addresses.length">, </span>
                            </span>
                        </p>

                        <ul class="social-icons" v-if=" ! isEditing">
                            <li v-if="personData.linkedin_url">
                                <a :href="personData.linkedin_url" target="_blank">
                                    <i class="fa fa-linkedin"></i>
                                </a>
                            </li>
                            <li v-if="personData.twitter">
                                <a :href="personData.twitter" target="_blank">
                                    <i class="fa fa-twitter"></i>
                                </a>
                            </li>
                            <li v-if="personData.facebook">
                                <a :href="personData.facebook" target="_blank">
                                    <i class="fa fa-facebook"></i>
                                </a>
                            </li>
                            <li v-if="personData.instagram">
                                <a :href="personData.instagram" target="_blank">
                                    <i class="fa fa-instagram"></i>
                                </a>
                            </li>
                            <li v-if="personData.telegram">
                                <a :href="personData.telegram" target="_blank">
                                    <i class="fa fa-telegram"></i>
                                </a>
                            </li>
                        </ul>

                        <ul class="social-icons" v-if="isEditing">
                            <li>
                                <a href="#" @click.prevent="setSocialEdit('linkedin')" 
                                    :class="{'active': personData.linkedin_url, 'editing': isSocialEditing =='linkedin'}">
                                    <i class="fa fa-linkedin"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click.prevent="setSocialEdit('twitter')" 
                                    :class="{'active': personData.twitter, 'editing': isSocialEditing =='twitter'}">
                                    <i class="fa fa-twitter"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click.prevent="setSocialEdit('facebook')" 
                                    :class="{'active': personData.facebook, 'editing': isSocialEditing =='facebook'}">
                                    <i class="fa fa-facebook"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click.prevent="setSocialEdit('instagram')" 
                                    :class="{'active': personData.instagram, 'editing': isSocialEditing =='instagram'}">
                                    <i class="fa fa-instagram"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click.prevent="setSocialEdit('telegram')" 
                                    :class="{'active': personData.telegram, 'editing': isSocialEditing =='telegram'}">
                                    <i class="fa fa-telegram"></i>
                                </a>
                            </li>
                        </ul>

                        <div class="social-edit-block">
                            <input 
                                type="text" 
                                v-model="personData.linkedin_url"
                                v-if="isSocialEditing && isSocialEditing =='linkedin'" 
                                :placeholder="isSocialEditing" 
                                class="social-input"
                                :maxlength="maxSocialLength"
                                @keydown.enter.prevent="updateEmploye"
                            >
                            <input 
                                type="text" 
                                v-model="personData.twitter"
                                v-if="isSocialEditing && isSocialEditing =='twitter'" 
                                :placeholder="isSocialEditing" 
                                class="social-input"
                                :maxlength="maxSocialLength"
                                @keydown.enter.prevent="updateEmploye"
                            >
                            <input 
                                type="text" 
                                v-model="personData.facebook"
                                v-if="isSocialEditing && isSocialEditing =='facebook'" 
                                :placeholder="isSocialEditing" 
                                class="social-input"
                                :maxlength="maxSocialLength"
                                @keydown.enter.prevent="updateEmploye"
                            >
                            <input 
                                type="text" 
                                v-model="personData.instagram"
                                v-if="isSocialEditing && isSocialEditing =='instagram'" 
                                :placeholder="isSocialEditing" 
                                class="social-input"
                                :maxlength="maxSocialLength"
                                @keydown.enter.prevent="updateEmploye"
                            >
                            <input 
                                type="text" 
                                v-model="personData.telegram"
                                v-if="isSocialEditing && isSocialEditing =='telegram'" 
                                :placeholder="isSocialEditing" 
                                class="social-input"
                                :maxlength="maxSocialLength"
                                @keydown.enter.prevent="updateEmploye"
                            >
                        </div>

                        <div class="row person-experience">
                            <div :class="yearsAtThisJob? 'col-md-4': 'col-md-6'">
                                <p class="number">{{experienceYears}}</p>
                                <p class="text">Years Experience</p>
                            </div>

                            <div class="col-md-4" v-if="yearsAtThisJob">
                                <p class="number">
                                    {{ yearsAtThisJob }}
                                </p>
                                <p class="text">Years at this Job</p>
                            </div>

                            <div :class="yearsAtThisJob? 'col-md-4': 'col-md-6'">
                                <p class="number">
                                    <img src="/images/ic-education.png" alt="">
                                </p>
                                <p v-if="!isEditing" class="text">
                                    {{ personData.role }}
                                </p>
                                <div v-if="isEditing" class="role-edit-block can-edit">
                                    <input 
                                        class="edit-input"
                                        type="text" 
                                        v-model="personData.role" 
                                        placeholder="Role"
                                        @keydown.enter.prevent="updateEmploye"
                                        :maxlength="maxRoleLength"
                                        @input="checkRoleLength"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="view-contacts-chain-container">
                            <a 
                                v-show=" ! isEditing"
                                href="javascript:void(0)" 
                                @click="showContactsChain()" 
                                data-dismiss="modal" 
                                aria-label="Close">
                                View Contacts Chain
                            </a>
                            <div v-if="isEditing" class="confirm-employe-edit-block">
                                <button
                                    type="button"
                                    class="btn cancel-employe-btn"
                                    @click.prevent="toggleEditing"
                                >
                                    Cancel Editing
                                </button>
                                <button 
                                    type="button"
                                    class="btn save-employe-btn"
                                    v-if=" ! saveBtnDisabled && madeChanges"
                                    @click.prevent="updateEmploye"
                                >
                                    Suggest Edits
                                </button>
                                <button 
                                    type="button" 
                                    v-if="saveBtnDisabled || ! madeChanges" 
                                    disabled 
                                    class="btn save-employe-btn-disabled"
                                >
                                    Suggest Edits
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="add-new-relation" v-if="showAddRelation && isEditing">
                            <autocomplete
                                :items="peopleItems"
                                :onChange="getPeopleAutocomplete"
                                :onClick="selectConnectionPerson"
                                :itemsTotal="peopleItemsTotal"
                                :itemsType="'People'"
                            />
                            <div v-if="selectedConnectionPerson" class="connect-with-block">
                                <div class="connect-with-title">Connect with:</div>
                                <div class="connect-with-data">
                                    <div class="image">
                                        <a href="javascript:void(0)">
                                            <span class="person-initials">{{getPersonInitials(selectedConnectionPerson.name)}}</span>
                                            <img :src="'/images/mask-'+0+'.png'" alt="">
                                        </a>
                                    </div>
                                    <div class="person-info">
                                        <div>Name: {{ selectedConnectionPerson.name }}</div>
                                        <div>City: {{ selectedConnectionPerson.town }}</div>
                                        <div>Role: {{ selectedConnectionPerson.description }}</div>
                                        <div v-if="selectedConnectionPerson.addresses.length">
                                            Addresses: {{ getAddressesString(selectedConnectionPerson.addresses) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="selectedConnectionPerson" class="relation-fields-block">
                                <div class="remark-block">
                                    <input 
                                        id="edge-comment" 
                                        type="text" 
                                        v-model="edgeComment" 
                                        placeholder="Remark"
                                        class="form-control"
                                    >
                                </div>
                                <div class="connection-type-block">
                                    <v-select 
                                        :options="connectionTypes"
                                        :label="'name'"
                                        :class="'connection-types'"
                                        :searchable="false"
                                        :placeholder="'Choose connection type'"
                                        v-model="selectedConnectionType"
                                    />
                                </div>
                            </div>
                            <div class="confirm-add-relation-block">
                                <button class="btn cancel-add-relation-btn" @click.prevent="closeAddRelation">Cancel</button>
                                <button class="btn add-relation-btn" @click.prevent="createPersonRelation">Add</button>
                            </div>
                        </div>
                        <div>
                            <ul class="nav nav-tabs person-tabs">
                                <li :class="{'active': activeTab == 'career'}">
                                    <a href="javascript:void(0)" @click="setTabActive('career')" data-toggle="tab" aria-expanded="true">Career</a></li>
                                <li :class="{'active': activeTab == 'news'}">
                                    <a href="javascript:void(0)" @click="setTabActive('news')" data-toggle="tab" aria-expanded="false">News</a></li>
                                <li :class="{'active': activeTab == 'publications'}">
                                    <a href="javascript:void(0)" @click="setTabActive('publications')" data-toggle="tab" aria-expanded="false">Publications</a></li>
                                <li :class="{'active': activeTab == 'relationships'}">
                                    <a href="javascript:void(0)" 
                                        @click="setTabActive('relationships')" 
                                        data-toggle="tab" 
                                        aria-expanded="false"
                                    >
                                        Relationships
                                    </a>
                                    <a v-if="isEditing" 
                                        class="add-relation" 
                                        href="#" 
                                        @click.prevent="toggleAddRelation"><i class="fa fa-plus"></i></a>
                                </li>
                            </ul>

                            <div class="tab-content">

                                <div v-if="activeTab == 'career'">
                                    <ul class="career-list">
                                        <li v-for="career in personData.careers">
                                            <p class="occupation">{{workPlace(career)}}</p>
                                            <p class="work-place">{{career.role}}</p>
                                            <p class="date">{{endDate(career.enddate)}}</p>
                                        </li>
                                    </ul>
                                </div>

                                <div v-if="activeTab == 'news'">
                                    <ul class="news-list">
                                        <li>
                                            <p class="date">Feb 21, 2018</p>
                                            <div class="news-box">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis esse facilis ipsa ipsam itaque laborum</p>
                                            </div>
                                        </li>
                                        <li>
                                            <p class="date">Feb 21, 2018</p>
                                            <div class="news-box">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis esse facilis ipsa ipsam itaque laborum</p>
                                            </div>
                                        </li>
                                        <li>
                                            <p class="date">Feb 21, 2018</p>
                                            <div class="news-box">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis esse facilis ipsa ipsam itaque laborum</p>
                                            </div>
                                        </li>
                                        <li>
                                            <p class="date">Feb 21, 2018</p>
                                            <div class="news-box">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Blanditiis esse facilis ipsa ipsam itaque laborum</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div v-if="activeTab == 'publications'">

                                    <p style="text-align: center" v-if="!personData.publications.length">This person doesn't have publications yet.</p>

                                    <ul class="publication-list" v-if="personData.publications.length">
                                        <li v-for="publication in personData.publications">
                                            <p class="title">
                                                <a :href="publication.url" target="_blank">
                                                    {{publication.title}} <i class="fa fa-external-link"></i>
                                                </a>
                                            </p>
                                            <p class="description">
                                                {{publication.journal}}
                                            </p>
                                        </li>
                                    </ul>
                                </div>

                                <div v-if="activeTab == 'relationships'">

                                    <tab-relationships
                                            :personId="personId"
                                            :personData="personData"
                                            :relationshipsCollapsedData="relationshipsCollapsedData"
                                            :connectionTypes="connectionTypes"
                                            @resetTab="activeTab='career'"
                                    ></tab-relationships>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import http from '../mixins/http';
    import getPersonInitials from '../mixins/get-person-initials';
    import addressHelpers from '../mixins/address-helpers';
    import autocomplete from './autocomplete';

    export default {
        mixins: [http, getPersonInitials, addressHelpers],
        components: {
            autocomplete
        },

        data: function () {
            return {
                personId: null,
                currentAddressId: null,
                currentAddress: {},
                personData: {
                    name: '',
                    careers: [],
                    publications: [],
                    relationships: []
                },
                activeTab: 'career',
                connectionTypes: [],
                // relationshipsCollapsed: true,
                relationshipsCollapsedData: [],
                // relationshipsTotal: 0
                isEditing: false,
                madeChanges: false,
                saveBtnDisabled: true,
                old: {
                    name: '',
                    description: '',
                    role: '',
                    linkedin: '',
                    twitter: '',
                    facebook: '',
                    instagram: '',
                    telegram: ''
                },
                maxRoleLength: 1000,
                maxSocialLength: 1000,
                isSocialEditing: false,
                showAddRelation: false,
                peopleItems: [],
                peopleItemsTotal: 0,
                selectedConnectionType: null,
                selectedConnectionPerson: null,
                edgeComment: ''
            }
        },

        computed: {
            experienceYears: function() {

                if( ! this.personData.careers || 
                    ! this.personData.careers.length || 
                    ! this.personData.careers[this.personData.careers.length-1].enddate) {
                    return 0;
                }

                let startDate = moment(this.personData.careers[this.personData.careers.length-1].enddate);
                let now = moment();

                return now.diff(startDate, 'years');
            },

            yearsAtThisJob: function () {
                if ( ! this.personData.careers || !this.personData.careers.length) {
                    return 0;
                }

                let recordInCareer = this.personData.careers.find(el => el.address_id == this.currentAddressId);

                if ( ! recordInCareer || !recordInCareer.enddate) {
                    return 0;
                }

                let startDate = moment(recordInCareer.enddate);

                let now = moment();

                return _.round(now.diff(startDate, 'days') / 365 , 1);
            }
        },

        watch: {
            $route: function (to) {
                if(window.location.hash.indexOf('person-') === -1 && $('#personal-modal').hasClass('in')){
                    $('#personal-modal').modal('hide');
                }
            },
            isEditing: function () {
                if ( ! this.isEditing) {
                    this.showAddRelation = false;
                    this.peopleItems = [];
                    this.selectedConnectionType = null;
                    this.selectedConnectionPerson = null;
                }
            },
            showAddRelation: function () {
                if ( ! this.showAddRelation) {
                    this.showAddRelation = false;
                    this.peopleItems = [];
                    this.selectedConnectionType = null;
                    this.selectedConnectionPerson = null;
                }
            },
            "personData.name": function() {
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "personData.description": function() {
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "personData.role": function () {
                if (this.personData.role == null) {
                    this.personData.role = '';
                }
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "personData.linkedin_url": function () {
                if (this.personData.linkedin_url == null) {
                    this.personData.linkedin_url = '';
                }
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "personData.twitter": function () {
                if (this.personData.twitter == null) {
                    this.personData.twitter = '';
                }
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "personData.facebook": function () {
                if (this.personData.facebook == null) {
                    this.personData.facebook = '';
                }
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "personData.instagram": function () {
                if (this.personData.instagram == null) {
                    this.personData.instagram = '';
                }
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "personData.telegram": function () {
                if (this.personData.telegram == null) {
                    this.personData.telegram = '';
                }
                this.checkIfInputsEmpty();
                if (this.isEditing) {
                    this.checkIfChangesMade();
                }
            },
            "old.role": function () {
                if (this.old.role == null) {
                    this.old.role = '';
                }
            },
            "old.linkedin": function () {
                if (this.old.linkedin == null) {
                    this.old.linkedin = '';
                }
            },
            "old.twitter": function () {
                if (this.old.twitter == null) {
                    this.old.twitter = '';
                }
            },
            "old.facebook": function () {
                if (this.personoldData.facebook == null) {
                    this.old.facebook = '';
                }
            },
            "old.instagram": function () {
                if (this.old.instagram == null) {
                    this.old.instagram = '';
                }
            },
            "old.telegram": function () {
                if (this.old.telegram == null) {
                    this.old.telegram = '';
                }
            },
        },
        methods: {
            endDate: function (date) {
                return moment(date).format('MMM YYYY');
            },
            workPlace: function (career) {
                if (career.address_id) {
                    return (this.personData.addresses.find(addr => addr.id == career.address_id)).name;
                }
                else {
                    return career.address_name_override;
                }
            },
            init: function (personId, addressId, address) {

                window.location.hash = 'person-' + personId;

                this.isEditing = false;

                $('#personal-modal').modal('show');

                this.personId = personId;
                this.currentAddressId = addressId;
                this.currentAddress = address;

                this.httpGet('/api/people/' + personId)
                    .then(data => {
                        this.personData = data;

                        this.unifyAddressesWithDuplicatedNames(this.personData.addresses);

                        this.httpGet('/api/people/'+personId+'/relationships?page=1')
                            .then(data => {
                                this.relationshipsCollapsedData = data.data;
                            })
                            
                        this.old.name = this.personData.name;
                        this.old.description = this.personData.description;
                        this.old.role = this.personData.role;
                        this.old.linkedin = this.personData.linkedin_url ? this.personData.linkedin_url : '';
                        this.old.twitter = this.personData.twitter ? this.personData.twitter : '';
                        this.old.facebook = this.personData.facebook ? this.personData.facebook : '';
                        this.old.instagram = this.personData.instagram ? this.personData.instagram : '';
                        this.old.telegram = this.personData.telegram ? this.personData.telegram : '';
                    });

                $('#personal-modal').on('hidden.bs.modal', function (e) {
                    window.location.hash = '';
                });
            },
            setTabActive: function (tabName) {
                this.activeTab = tabName;
                this.$root.logData('person', 'open', JSON.stringify(tabName + ' tab'));
            },

            showContactsChain: function() {

                let addressData = JSON.parse(JSON.stringify(this.currentAddress));
                addressData['personId'] = this.personId;
                addressData['isPersonChain'] = true;

                this.$eventGlobal.$emit('showModalContactsChain', addressData);

                this.$root.logData('person', 'show contacts chain', JSON.stringify(this.personId));
            },

            openRelationshipTabIfHashDetected: function () {
                if(this.$route.hash.indexOf('#person-') !== -1 && this.$route.hash.indexOf('&relation-person=') !== -1) {
                    setTimeout(() => {
                        this.setTabActive('relationships');
                    }, 1000);
                }
            },
            relationshipsPageChanged: function(page) {
                this.loadPersonRelationshipsPaginated(page);
                this.$root.logData('person_relationship', 'page changed', JSON.stringify(page));
            },
            toggleEditing: function() {
                this.isEditing = !this.isEditing;

                if ( ! this.isEditing) {
                    this.isSocialEditing = false;
                    this.personData.name = this.old.name;
                    this.personData.description = this.old.description;
                    this.personData.role = this.old.role;
                    this.personData.linkedin_url = this.old.linkedin;
                    this.personData.twitter = this.old.twitter;
                    this.personData.facebook = this.old.facebook;
                    this.personData.instagram = this.old.instagram;
                    this.personData.telegram = this.old.telegram;
                } else {
                    this.checkIfChangesMade();
                }

                this.$root.logData('person', 'toggle edit person', JSON.stringify(this.isEditing));
            },
            checkIfInputsEmpty: function() {
                if (
                    this.personData.name.trim() === '' ||
                    this.personData.description.trim() === ''
                ) {
                    this.saveBtnDisabled = true;
                } else {
                    this.saveBtnDisabled = false;
                }
            },
            checkIfChangesMade: function() {
                if (
                    this.personData.name.trim() !== this.old.name.trim() ||
                    this.personData.description.trim() !== this.old.description.trim() ||
                    this.personData.role.trim() !== this.old.role.trim() ||
                    this.personData.linkedin_url.trim() !== this.old.linkedin.trim() ||
                    this.personData.twitter.trim() !== this.old.twitter.trim() ||
                    this.personData.facebook.trim() !== this.old.facebook.trim() ||
                    this.personData.instagram.trim() !== this.old.instagram.trim() ||
                    this.personData.telegram.trim() !== this.old.telegram.trim()
                ) {
                    this.madeChanges = true;
                } else {
                    this.madeChanges = false;
                }
            },
            updateEmploye: function() {
                if (this.madeChanges && ! this.saveBtnDisabled) {
                    this.$root.logData('person', 'update person data', JSON.stringify({
                        name: this.personData.name,
                        description: this.personData.description,
                        role: this.personData.role,
                        linkedin_url: this.personData.linkedin_url,
                        twitter: this.personData.twitter,
                        facebook: this.personData.facebook,
                        instagram: this.personData.instagram,
                        telegram: this.personData.telegram
                    }));
                    this.httpPut('/api/people/' + this.personId + '/update', {
                        name: this.personData.name,
                        description: this.personData.description,
                        role: this.personData.role,
                        linkedin_url: this.personData.linkedin_url,
                        twitter: this.personData.twitter,
                        facebook: this.personData.facebook,
                        instagram: this.personData.instagram,
                        telegram: this.personData.telegram
                    })
                    .then(data => {
                        this.old.name = data.name;
                        this.old.description = data.description;
                        this.old.role = data.role;
                        this.old.linkedin = data.linkedin_url;
                        this.old.twitter = data.twitter;
                        this.old.facebook = data.facebook;
                        this.old.instagram = data.instagram;
                        this.old.telegram = data.telegram;
                        this.madeChanges = false;
                        this.saveBtnDisabled = false;
                        this.isEditing = false;
                        this.isSocialEditing = false;
                        this.$eventGlobal.$emit('employeeDetailsUpdated')
                        alertify.notify('Employe has been updated.', 'success', 3);
                    })
                    .catch(err => {
                        alertify.notify('Error occured', 'error', 3);
                    })
                }  
            },
            checkRoleLength: function (event) {
                if (event.target.value.length > this.maxRoleLength) {
                    this.personData.role = this.personData.role.substr(0, this.maxRoleLength);
                }
            },
            setSocialEdit: function (social) {
                this.isSocialEditing = social;
                this.$root.logData('person', 'set social edit', JSON.stringify(social));
            },
            toggleAddRelation: function () {
                this.showAddRelation = !this.showAddRelation;
            },
            closeAddRelation: function () {
                this.showAddRelation = false;
            },
            getAddressesString: function (addresses) {
                let str = '';
                let names = [];
                if (addresses.length) {
                    names = addresses.map(element => {
                        return element.name;
                    });
                    str = names.join(', ');
                }

                return str;
            },
            getPeopleAutocomplete: _.debounce(function (searchQuery, pageNumber) {
                let p = pageNumber || 1;
                if (searchQuery.length >= 3) {
                    this.httpGet('/api/people/autocomplete/' + searchQuery + '?page=' + p)
                        .then(data => {
                            console.log(data);
                            this.peopleItems = data.data;
                            this.peopleItemsTotal = data.total;
                        })
                        .catch(error => {
                            alertify.notify('Error occured', 'error', 3);
                        })
                } else {
                    this.peopleItems = [];
                }
            }, 400),
            selectConnectionPerson: function (selectedPerson) {
                this.selectedConnectionPerson = selectedPerson;
            },
            createPersonRelation: _.debounce(function () {
                if ( ! this.selectedConnectionPerson) {
                    alertify.notify('Person not selected', 'error', 3);
                } else if ( ! this.selectedConnectionType) {
                    alertify.notify('Connection type not selected', 'error', 3);
                } else {
                    let url = '/api/address-details/create-person-relation';
                    this.httpPost(url, {
                        fromPersonId: this.personId,
                        toPersonId: this.selectedConnectionPerson.id,
                        edgeType: this.selectedConnectionType.id,
                        edgeComment: this.edgeComment
                    })
                        .then(data => {
                            if (data.success) {
                                this.$eventGlobal.$emit('personRelationCreated', {
                                    personId: this.personId,
                                    addressId: this.currentAddressId,
                                    address: this.currentAddress
                                });
                                alertify.notify('Person relation created', 'success', 3);
                            } else {
                                alertify.notify(data.message, 'error', 3);
                            }
                            
                        })
                        .catch(error => {
                            alertify.notify('Error occured', 'error', 3);
                        })
                }

            }, 400)
        },

        mounted: function(){
            this.httpGet('/api/connection-types')
                .then(data => {
                    this.connectionTypes = data;
                });

            this.$eventGlobal.$on('showModalEmployeeDetails', (data) => {
                this.init(data.personId, data.addressId, data.address);
                this.$root.logData('person', 'open', JSON.stringify(data.personId));
            });
            
            this.$eventGlobal.$on('personRelationCreated', (data) => {
                this.init(data.personId, data.addressId, data.address);
            });

            this.openRelationshipTabIfHashDetected('relationships');
        }
    }
</script>

<style scoped>
    .employe-name-block {
        font-family: Montserrat;
        font-size: 28px;
        text-align: center;
        color: #3a444f;
        display: flex;
        margin-top: 18px;
        margin-bottom: 7px;
        justify-content: center;
        align-items: center;
        font-weight: 400;
        line-height: 1.42857143;
        box-sizing: border-box;
    }

    .employe-description-block {
        font-family: Montserrat;
        font-size: 16px;
        font-weight: 400;
        line-height: 1.31;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #3a444f;
    }

    .confirm-employe-edit-block {
        display: inline-block;
    }

    .can-edit div {
        border-bottom: 2px solid #d2d6de;
    }

    .cancel-employe-btn {
        background: transparent;
        font-family: Montserrat;
        font-size: 13px;
    }

    .save-employe-btn {
        width: 170px;
        background: #4a90e3;
        color: #fff;
        padding: 10px 15px;
        border-radius: 5px;
        font-family: Montserrat;
        font-size: 13px;
        font-weight: 600;
        margin: 0;
    }

    .save-employe-btn-disabled {
        width: 170px;
        color: #fff;
        padding: 10px 15px;
        border-radius: 5px;
        font-family: Montserrat;
        font-size: 13px;
        font-weight: 600;
        margin: 0;
        font-size: 13px;
        background-color: #989da3;
        opacity: 1;
    }

    .save-employe-btn:focus,
    .cancel-employe-btn:focus {
        outline: none;
    }

    .save-employe-btn:active,
    .cancel-employe-btn:active {
        box-shadow: none;
    }

    .can-edit div:empty:not(:focus):before {
        color: #b3b3b3;
        content: attr(data-placeholder);
    }

    .role-edit-block {
        display: flex;
        justify-content: center;
    }

    .edit-input {
        border: none;
        border-bottom: 2px solid #d2d6de;
        background-color: transparent;
        font-family: Montserrat;
        font-size: 14px;
        text-align: center;
    }

    .edit-input:focus {
        outline: none;
    }

    #personal-modal ul.social-icons li a.active {
        background-color: #4a90e3;
        color: white;
    }

    #personal-modal ul.social-icons li a {
        transition: none;
        position: relative;
    }

    #personal-modal ul.social-icons li a.editing:after {
        content: '';
        position: absolute;
        width: 100%;
        height: 0;
        left: 0;
        bottom: -6px;
        border-bottom: 2px solid #d2d6de;
    }

    .social-edit-block {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 15px 10px 0 0;
    }

    .social-input {
        border: none;
        border-bottom: 2px solid #d2d6de;
        background-color: transparent;
        font-family: Montserrat;
        font-size: 14px;
    }

    .social-input:focus {
        outline: none;
    }

    #personal-modal ul.person-tabs.nav-tabs > li .add-relation {
        position: absolute;
        cursor: pointer;
        top: 7px;
        right: -20px;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        border: none;
        display: flex;
        justify-content: center;
        background: #bbbec2;
        color: #fff;
        margin: 0;
    }

    #personal-modal ul.person-tabs.nav-tabs > li .add-relation:hover {
        background: #4a90e3;
        border: none;
        cursor: pointer;
    }

    #personal-modal ul.person-tabs.nav-tabs > li .add-relation:focus {
        background: #bbbec2;
        border: none;
        cursor: pointer;
    }

    #personal-modal ul.person-tabs.nav-tabs > li .add-relation .fa {
        cursor: pointer;
        position: absolute;
        top: 5px;
        left: 5px;
    }

    .add-new-relation {
        width: 100%;
        /* min-height: 200px; */
        background: #fff;
        -webkit-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);
        -moz-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);
        box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);
        margin-bottom: 20px;
        padding: 10px 15px;
    }

    .relation-fields-block {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 15px 0;
    }

    .remark-block {
        width: 60%;
    }

    .remark-block input {
        width: 100%;
        border: none;
        border-bottom: 2px solid #d2d6de
    }

    .connection-type-block {
        width: 35%
    }

    .confirm-add-relation-block {
        display: flex;
        justify-content: flex-end;
    }

    .connect-with-title {
        text-align: center;
        width: 100%;
        display: block;
        margin-bottom: 15px;
    }

    .connect-with-data {
        display: flex;
        align-items: center;
        padding: 15px 0;
    }

    .connect-with-data .image {
        margin: 0 15px;
    }

    .connect-with-data .image a {
        position: relative;
    }
    
    .connect-with-data .image a .person-initials {
        position: absolute;
        font-family: Montserrat;
        font-size: 21px;
        line-height: 21px;
        height: 21px;
        font-weight: 600;
        color: #ffffff;
        top: calc(50% - 11px);
        width: 100%;
        text-align: center;
    }

    .cancel-add-relation-btn {
        background: #fff;
        font-family: Montserrat;
        font-size: 13px;
        margin: 0;
    }

    .add-relation-btn {
        background: #4a90e3;
        color: #fff !important;
        padding: 10px 15px;
        border-radius: 5px;
        font-family: Montserrat;
        font-size: 13px;
        text-align: left;
        margin: 0;
    }

    .add-relation-btn:hover {
        background: #5ba3f4;
        transition: background-color 0.1s linear;
    }
</style>