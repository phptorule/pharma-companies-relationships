<template>
    <div class="admin-users">
        <div class="au-container">
            <div class="au-breadcrumbs">
                <ul class="au-breadcrumbs-list">
                    <li>Admin</li>
                    <li><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                    <li class="active">Users</li>
                </ul>
            </div>
            <div v-if="formToShow === 'newUser'" class="au-new-user-form">
                <h2>Create New User</h2>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="au-name">Name*: </label>
                            <input type="text" 
                                id="au-name" 
                                class="form-control" 
                                placeholder="Name"
                                v-model="name"
                            >
                        </div>
                        <div class="form-group">
                            <label for="au-email">Email*: </label>
                            <input type="email" 
                                id="au-email" 
                                class="form-control" 
                                placeholder="Email"
                                v-model="email"
                            >
                        </div>
                        <div class="form-group">
                            <label for="au-role">Role*: </label>
                            <input type="text" 
                                id="au-role" 
                                class="form-control" 
                                placeholder="Role (admin, user)"
                                v-model="role"
                            >
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="au-password">Password*: </label>
                            <input type="password" 
                                id="au-password" 
                                class="form-control" 
                                placeholder="Password"
                                v-model="password"
                            >
                        </div>
                        <div class="form-group">
                            <label for="au-confirm-password">Confirm password*: </label>
                            <input type="password" 
                                id="au-confirm-password" 
                                class="form-control" 
                                placeholder="Confirm password"
                                v-model="confirmPassword"
                            >
                        </div>
                        <div class="form-group">
                            <label for="au-link">Link: </label>
                            <input type="text" 
                                id="au-link" 
                                class="form-control" 
                                placeholder="Link"
                                v-model="link"
                            >
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <button class="btn btn-success" @click.prevent="createUser">
                                Create User
                            </button>
                            <button class="btn" @click.prevent="closeForm">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="formToShow === 'editUser'" class="au-new-user-form">
                <h2>Edit User</h2>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="au-name">Name*: </label>
                            <input type="text" 
                                id="au-name" 
                                class="form-control" 
                                placeholder="Name"
                                v-model="name"
                            >
                        </div>
                        <div class="form-group">
                            <label for="au-email">Email*: </label>
                            <input type="email" 
                                id="au-email" 
                                class="form-control" 
                                placeholder="Email"
                                v-model="email"
                            >
                        </div>
                        <div class="form-group">
                            <label for="au-role">Role*: </label>
                            <input type="text" 
                                id="au-role" 
                                class="form-control" 
                                placeholder="Role (admin, user)"
                                v-model="role"
                            >
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group" v-if="changePassword">
                            <label for="au-password">Password*: </label>
                            <input type="password" 
                                id="au-password" 
                                class="form-control" 
                                placeholder="Password"
                                v-model="password"
                            >
                        </div>
                        <div class="form-group" v-if="changePassword">
                            <label for="au-confirm-password">Confirm password*: </label>
                            <input type="password" 
                                id="au-confirm-password" 
                                class="form-control" 
                                placeholder="Confirm password"
                                v-model="confirmPassword"
                            >
                        </div>
                        <div class="form-group">
                            <label for="au-link">Link: </label>
                            <input type="text" 
                                id="au-link" 
                                class="form-control" 
                                placeholder="Link"
                                v-model="link"
                            >
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <button class="btn btn-success" @click.prevent="editUser">
                                Edit User
                            </button>
                            <button class="btn" @click.prevent="closeForm">Close</button>
                            <label for="au-want-change-password">Change password?</label>
                            <input type="checkbox" 
                                id="au-want-change-password" 
                                v-model="changePassword">
                        </div>
                    </div>
                </div>
            </div>
            <div class="au-user-list">
                <h2>User List</h2>
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td>User ID</td>
                                    <td>Name</td>
                                    <td>Email</td>
                                    <td>Role</td>
                                    <td>Link</td>
                                    <td>
                                        <button class="btn btn-warning" @click.prevent="setFormToShow('newUser')">
                                            New User
                                        </button>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users">
                                    <td>{{user.id}}</td>
                                    <td>{{user.name}}</td>
                                    <td>{{user.email}}</td>
                                    <td>{{user.role}}</td>
                                    <td>{{user.link ? user.link : '-'}}</td>
                                    <td>
                                        <button class="btn btn-primary" 
                                            @click.prevent="setFormToShow('editUser', user.id)">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="pagination-box">
                            <pagination 
                                :records="totalUsers" 
                                :class="'pagination pagination-sm no-margin pull-right'" 
                                :per-page="usersPerPage" 
                                :options="{'chunk': 5}"
                                @paginate="usersPageChanged">
                            </pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import http from '../../mixins/http';

    export default {
        mixins: [http],

        data: function () {
            return {
                name: '',
                email: '',
                role: '',
                password: '',
                confirmPassword: '',
                link: '',
                users: [],
                totalUsers: 0,
                usersPerPage: 10,
                formToShow: '',
                editingUserId: '',
                changePassword: false
            }
        },
        watch: {
            formToShow: function (newValue) {
                if (this.formToShow == '' || this.formToShow == 'newUser') {
                    this.clearNewUserForm();
                }
            }
        },
        methods: {
            createUser: _.debounce(function () {
                let url = '/api/admin/create-user';

                this.httpPost(url, {
                    name: this.name,
                    email: this.email,
                    role: this.role,
                    password: this.password,
                    password_confirmation: this.confirmPassword,
                    link: this.link
                })
                .then(data => {
                    if (data.success) {
                        this.clearNewUserForm();
                        this.$eventGlobal.$emit('newUserCreated');
                        alertify.notify('New user successfully created', 'success', 3);
                    } else {
                        alertify.notify(data.message, 'error', 3);
                        return;
                    }
                })
                .catch(error => {
                    alertify.notify(error.data.message, 'error', 3);
                })
            }, 400),
            getUsers: function (pageNumber) {
                let p = pageNumber || 1;
                let url = '/api/get-users' + '/?page=' + p;
                
                this.httpGet(url)
                    .then(data => {
                        this.users = data.data;
                        this.totalUsers = data.total;
                    })
                    .catch(error => {
                        console.log(error);
                    })
            },
            clearNewUserForm: function () {
                this.name = '';
                this.email = '';
                this.role = '';
                this.password = '';
                this.confirmPassword = '';
                this.link = '';
                this.changePassword = false;
            },
            setFormToShow: function (name, userId) {
                this.formToShow = name;

                if (userId) {
                    this.setUserForEdit(userId);
                }
            },
            closeForm: function () {
                this.formToShow = '';
            },
            usersPageChanged: function (pageNumber) {
                this.getUsers(pageNumber);
            },
            setUserForEdit: function (userId) {
                let user = this.users.find(user => user.id == userId);

                if (user) {
                    this.name = user.name;
                    this.email = user.email;
                    this.role = user.role;
                    this.link = user.link;
                    this.editingUserId = user.id;
                }
            },
            editUser: _.debounce(function () {
                let url = '/api/admin/edit-user';

                this.httpPost(url, {
                    name: this.name,
                    email: this.email,
                    role: this.role,
                    password: this.password,
                    password_confirmation: this.confirmPassword,
                    link: this.link,
                    userId: this.editingUserId,
                    changePassword: this.changePassword
                })
                .then(data => {
                    if (data.success) {
                        this.closeForm();
                        this.$eventGlobal.$emit('userEdited');
                        alertify.notify('User successfully edited', 'success', 3);
                    } else {
                        alertify.notify(data.message, 'error', 3);
                        return;
                    }
                })
                .catch(error => {
                    alertify.notify(error.data.message, 'error', 3);
                })
            }, 400)
        },
        mounted: function () {
            this.getUsers();

            this.$eventGlobal.$on('newUserCreated', () => {
                this.getUsers();
            });
           
            this.$eventGlobal.$on('userEdited', () => {
                this.getUsers();
            });
        }
    }
</script>

<style scoped>

</style>

