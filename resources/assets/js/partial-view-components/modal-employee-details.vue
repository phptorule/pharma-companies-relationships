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
                                    {{ address.name }}
                                </a><span v-if="i != 0">, </span>
                            </span>
                        </p>

                        <ul class="social-icons">
                            <li>
                                <a href="#" @click.prevent class="without-handler">
                                    <i class="fa fa-linkedin"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click.prevent class="without-handler">
                                    <i class="fa fa-twitter"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click.prevent class="without-handler">
                                    <i class="fa fa-facebook"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click.prevent class="without-handler">
                                    <i class="fa fa-instagram"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" @click.prevent class="without-handler">
                                    <i class="fa fa-telegram"></i>
                                </a>
                            </li>
                        </ul>

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
                                    <div-editable
                                        @updateEdit="updateEmploye"
                                        :content.sync="personData.role"
                                        :placeholder="'Role'"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="view-contacts-chain-container">
                            <a href="javascript:void(0)" 
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
                        <div>
                            <ul class="nav nav-tabs person-tabs">
                                <li :class="{'active': activeTab == 'career'}">
                                    <a href="javascript:void(0)" @click="setTabActive('career')" data-toggle="tab" aria-expanded="true">Career</a></li>
                                <li :class="{'active': activeTab == 'news'}">
                                    <a href="javascript:void(0)" @click="setTabActive('news')" data-toggle="tab" aria-expanded="false">News</a></li>
                                <li :class="{'active': activeTab == 'publications'}">
                                    <a href="javascript:void(0)" @click="setTabActive('publications')" data-toggle="tab" aria-expanded="false">Publications</a></li>
                                <li :class="{'active': activeTab == 'relationships'}">
                                    <a href="javascript:void(0)" @click="setTabActive('relationships')" data-toggle="tab" aria-expanded="false">Relationships</a></li>
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

    export default {
        mixins: [http, getPersonInitials],

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
                    role: ''
                }
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
            "old.role": function () {
                if (this.old.role == null) {
                    this.old.role = '';
                }
            }
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

                        this.httpGet('/api/people/'+personId+'/relationships?page=1')
                            .then(data => {
                                this.relationshipsCollapsedData = data.data;
                            })
                            
                        this.old.name = this.personData.name;
                        this.old.description = this.personData.description;
                        this.old.role = this.personData.role;
                    });

                $('#personal-modal').on('hidden.bs.modal', function (e) {
                    window.location.hash = '';
                });
            },
            setTabActive: function (tabName) {
                this.activeTab = tabName;
            },

            showContactsChain: function() {

                let addressData = JSON.parse(JSON.stringify(this.currentAddress));
                addressData['personId'] = this.personId;
                addressData['isPersonChain'] = true;

                this.$eventGlobal.$emit('showModalContactsChain', addressData);
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
            },
            toggleEditing: function() {
                this.isEditing = !this.isEditing;

                if ( ! this.isEditing) {
                    this.personData.name = this.old.name;
                    this.personData.description = this.old.description;
                    this.personData.role = this.old.role;
                } else {
                    this.checkIfChangesMade();
                }
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
                    this.personData.role.trim() !== this.old.role.trim()
                ) {
                    this.madeChanges = true;
                } else {
                    this.madeChanges = false;
                }
            },
            updateEmploye: function() {
                if (this.madeChanges && ! this.saveBtnDisabled) {
                    this.httpPut('/api/people/' + this.personId + '/update', {
                        name: this.personData.name,
                        description: this.personData.description,
                        role: this.personData.role
                    })
                    .then(data => {
                        this.old.name = data.name;
                        this.old.description = data.description;
                        this.old.role = data.role;
                        this.madeChanges = false;
                        this.saveBtnDisabled = false;
                        this.isEditing = false;
                        this.$eventGlobal.$emit('employeeDetailsUpdated')
                        alertify.notify('Employe has been updated.', 'success', 3);
                    })
                    .catch(err => {
                        alertify.notify('Error occured', 'error', 3);
                    })
                }  
            }
        },

        mounted: function(){
            this.httpGet('/api/connection-types')
                .then(data => {
                    this.connectionTypes = data;
                });

            this.$eventGlobal.$on('showModalEmployeeDetails', (data) => {
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
        /* margin: 0; */
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
        /* display: flex; */
        /* justify-content: center; */
        /* align-items: center; */
        /* margin-top: 18px; */
        /* margin-bottom: 18px; */
    }

    .can-edit div {
        border-bottom: 2px solid #d2d6de;
    }

    .cancel-employe-btn {
        background: transparent;
        font-family: Montserrat;
        font-size: 13px;
        /* margin-right: 10px; */
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

    /* .can-edit div:empty:not(:focus):before { */
        /* font-weight: 100; */
    /* } */

    .role-edit-block {
        display: flex;
        justify-content: center;
    }
</style>