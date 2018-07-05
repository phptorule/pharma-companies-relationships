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
                            <button class="btn btn-success">Create User</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="au-user-list">

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
                link: ''
            }
        }
    }
</script>

<style scoped>

</style>

