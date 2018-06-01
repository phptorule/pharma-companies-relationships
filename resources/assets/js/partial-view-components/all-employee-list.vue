<template>
    <div class="slided-box-content">

        <a href="javascript:void(0)" class="close-icon-a" @click="closeSlidedBox()">
            <img src="/images/x.png" alt="">
        </a>

        <h3 class="">
            Lab Employees
            <a data-v-cd5686be="" href="#">
                <i data-v-cd5686be="" class="fa fa-pencil"></i>
            </a>
        </h3>

        <div class="search-block">
            <i class="fa fa-search icon" aria-hidden="true"></i>
            <input v-model="query" 
                type="text" 
                class="people-search-input"
                @keydown.enter.prevent="handleSearch"
                placeholder="Employee name"
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
        
        <ul v-if=" ! searchSubmited" class="staff-list">
            <li v-for="(person, i) in people">
                <div class="image">
                    <a href="javascript:void(0)" @click="showEmployeeDetailsModal(person.id, addressId, address)">
                        <span class="person-initials">{{getPersonInitials(person.name)}}</span>
                        <img :src="'/images/mask-'+i+'.png'" alt="">
                    </a>
                </div>
                <div class="personal-info">
                    <p class="name">
                        <a href="javascript:void(0)" 
                            @click="showEmployeeDetailsModal(person.id, addressId, address)">
                            {{ person.name }}
                        </a>
                    </p>
                    <p class="occupation">{{ person.description }}</p>
                </div>
            </li>
        </ul>

        <ul v-else class="staff-list">
            <li v-for="(person, i) in filtered" v-if="filtered.length > 0">
                <div class="image">
                    <a href="javascript:void(0)" @click="showEmployeeDetailsModal(person.id, addressId, address)">
                        <span class="person-initials">{{getPersonInitials(person.name)}}</span>
                        <img :src="'/images/mask-'+i+'.png'" alt="">
                    </a>
                </div>
                <div class="personal-info">
                    <p class="name">
                        <a href="javascript:void(0)" 
                            @click="showEmployeeDetailsModal(person.id, addressId, address)">
                            {{person.name}}
                        </a>
                    </p>
                    <p class="occupation">{{ person.description }}</p>
                </div>
            </li>
            
            <div v-if="filtered.length < 1">No matches</div>
    
        </ul>

        <div v-show=" ! searchSubmited" class="pagination-box">
            <pagination :records="peopleTotal"  
                :class="'pagination pagination-sm no-margin pull-right'" 
                :per-page="10" @paginate="pageChanged"></pagination>
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
                people: [],
                filtered: [],
                roles: [],
                selectedRole: null,
                isDataLoaded: false,
                peopleTotal: 0,
                query: '',
                searchSubmited: false
            }
        },

        watch: {
            query: function () {
                if (this.query.trim() === '') {
                    this.searchSubmited = false;
                }
            }
        },

        methods: {
            loadAddressEmployeesPaginated: function (id, page) {

                let p = page || 1;

                this.httpGet('/api/address-details/'+id+'/people?page='+p)
                    .then(data => {
                        this.people = data.data;
                        this.isDataLoaded = true;
                        this.peopleTotal = data.total;
                    })
            },
            getRoles: function () {
                this.httpGet('/api/get-roles')
                    .then(data => {
                        this.roles = data
                    })
            },

            pageChanged: function (pageNumber) {
                this.loadAddressEmployeesPaginated(this.addressId, pageNumber);
            },

            closeSlidedBox: function () {
                this.$emit('closeSlidedBox')
            },
            handleSearch: function (e) {
                if (this.canSearch) {
                    this.searchSubmited = true;

                    this.filtered = this.employeeList.filter((item) => {
                        if (this.selectedRole) {
                            return item.name.toLowerCase().indexOf(this.query.toLowerCase().trim()) + 1 &&
                                item.type_id == this.selectedRole.id
                        } else {
                            return item.name.toLowerCase().indexOf(this.query.toLowerCase().trim()) + 1
                        }
                    });
                }
            }
        },

        props: ['employeeList', 'isActive', 'addressId', 'address'],

        mounted: function () {
            // if(this.isActive && this.addressId && !this.isDataLoaded) {
                this.loadAddressEmployeesPaginated(this.addressId)
            // }

            this.getRoles()

            this.filtered = this.employeeList;

            this.$eventGlobal.$on('employeeDetailsUpdated', () => {
                this.loadAddressEmployeesPaginated(this.addressId)
                this.filtered = []
                this.query = ''
            })
        },
        computed: {
            canSearch: function () {
                return this.query.trim() === '' ? false : true
            }
        }
    }
</script>

<style scoped>

    .people-search-input {
        width: 200px;
        height: 36px;
        padding: 0 .5em;
        border-bottom: 2px solid #EAEFF4;
        outline: none;
        border-top: none;
        border-left: none;
        border-right: none;
        margin-bottom: 20px;
        border-radius: 4px;
        box-sizing: border-box;
    }

</style>