<template>
    <div class="slided-box-content all-employee-list">

        <a href="javascript:void(0)" class="close-icon-a" @click="closeSlidedBox()">
            <img src="/images/x.png" alt="">
        </a>

        <h3 class="">
            Lab Employees
            <a data-v-cd5686be="" href="#" @click.prevent class="without-handler">
                <i data-v-cd5686be="" class="fa fa-pencil"></i>
            </a>
        </h3>

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
        
        <ul v-if=" ! canSearch && ! canSearchByRole" class="staff-list">
            <li v-for="(person, i) in people">
                <div class="image" style="cursor: pointer">
                    <a href="javascript:void(0)" style="cursor: default;" @click="showEmployeeDetailsModal(person.id, addressId, address)">
                        <span class="person-initials">{{getPersonInitials(person.name)}}</span>
                        <img :src="'/images/mask-'+i+'.png'" alt="">
                    </a>
                </div>
                <div class="personal-info">
                    <a href="javascript:void(0)" class="name" @click="showEmployeeDetailsModal(person.id, addressId, address)">
                        {{ person.name }}
                    </a>
                    <p class="occupation">{{ person.email }}</p>
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

        <div v-show=" ! canSearch && ! canSearchByRole" class="pagination-box">
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
                selectedRole: {
                    id: -1,
                    name: "All Employees"
                },
                defaultRole: {
                    id: -1,
                    name: "All Employees"
                },
                isDataLoaded: false,
                peopleTotal: 0,
                query: '',
            }
        },

        watch: {
            selectedRole: function () {
                if (this.selectedRole == null) {
                    this.selectedRole = this.defaultRole
                }
                this.handleSearch()
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
                        this.roles.unshift(this.defaultRole)
                        this.selectedRole = this.defaultRole
                    })
            },

            pageChanged: function (pageNumber) {
                this.loadAddressEmployeesPaginated(this.addressId, pageNumber);
                this.$root.logData('staff', 'employee page changed', JSON.stringify(pageNumber));
            },

            closeSlidedBox: function () {
                this.$emit('closeSlidedBox')
                this.$root.logData('staff', 'close slided box', JSON.stringify(''));
            },
            handleSearch: _.debounce(function (e) {
                this.$root.logData('staff', 'search', JSON.stringify({
                    searchQuery: this.query,
                    selectedRole: this.selectedRole
                }));
                this.filtered = this.employeeList.filter((item) => {
                    if (this.canSearch && this.canSearchByRole) {
                        return item.name.toLowerCase().indexOf(this.query.toLowerCase().trim()) + 1 &&
                            item.type_id == this.selectedRole.id
                    } else if (this.canSearch && ! this.canSearchByRole) {
                        return item.name.toLowerCase().indexOf(this.query.toLowerCase().trim()) + 1
                    } else if ( ! this.canSearch && this.canSearchByRole) {
                        return item.type_id == this.selectedRole.id
                    }
                });
            }, 400)
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

            this.$root.logData('staff', 'open', JSON.stringify(''));
        },
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