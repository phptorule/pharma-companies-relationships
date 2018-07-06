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
            <div class="au-new-user-form">
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
                        </div>
                    </div>
                </div>
            </div>
            <div class="au-user-list">
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td>User ID</td>
                                    <td>Name</td>
                                    <td>Email</td>
                                    <td>Role</td>
                                    <td>New user</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users">
                                    <td>{{user.id}}</td>
                                    <td>{{user.name}}</td>
                                    <td>{{user.email}}</td>
                                    <td>{{user.role}}</td>
                                    <td>edit</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- <div v-for="user in users" class="au-user-list-row">
                    <div>{{user.id}}</div>
                    <div>{{user.name}}</div>
                    <div>{{user.email}}</div>
                    <div>{{user.role}}</div>
                </div> -->
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
                users: []
            }
        },
        methods: {
            createUser: function () {
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
                        this.name = '';
                        this.email = '';
                        this.role = '';
                        this.password = '';
                        this.confirmPassword = '';
                        this.link = '';
                        alertify.notify('New user successfully created', 'success', 3);
                    } else {
                        alertify.notify(data.message, 'error', 3);
                    }
                })
                .catch(error => {
                    alertify.notify(error.data.message, 'error', 3);
                })
            },
            getUsers: function () {
                let url = '/api/get-users';
                
                this.httpGet(url)
                    .then(data => {
                        this.users = data.data;
                    })
                    .catch(error => {
                        console.log(error);
                    })
            }
        },
        mounted: function () {
            this.getUsers();
        }
    }
</script>

<style scoped>

</style>

