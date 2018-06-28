<template>
    <div class="login-box">
        <div class="login-logo">
            <a href="javascript:void(0)"><b>Labscape</b>&beta;</a>
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body">
            <p  class="login-box-msg">Set New Password</p>

            <div v-if="isPasswordSet">
                <h1 style="margin-top: 0" class="text-success text-center"><i class="fa fa-2x fa-check-circle-o"></i></h1>
                <p class="text-center">New password has been successfully set. <br /> Please login using it.</p>
            </div>

            <div v-if="errorMessage !== ''" style="margin-bottom: 15px">
                <h1 class="text-danger text-center" style="margin-top: 0"><i class="fa fa-2x fa-exclamation-circle"></i></h1>
                <p class="text-danger text-center" >{{errorMessage}}</p>
            </div>

            <form name="loginForm" v-if="!isPasswordSet">
                <div class="form-group has-feedback"  >
                    <input
                        v-model="email"
                        class="form-control"
                        placeholder="Email"
                        type="email"
                        name="email"
                        required
                    >
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    <div v-if="false">
                        <span class="help-block" >Email is required.</span>
                        <span class="help-block" >Must be a valid email.</span>
                    </div>
                </div>
                <div class="form-group has-feedback" >
                    <input
                        v-model="password"
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Password (min 6 characters)"
                        required
                        minlength="6"
                    >
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    <div v-if="false">
                        <span class="help-block" >Password is required.</span>
                        <span class="help-block" >Password must be at least 6 characters long</span>
                    </div>
                </div>


                <div class="form-group has-feedback" :class="{'has-error': password !== password_confirmation && password != '' && password_confirmation != ''}">
                    <input
                        v-model="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        placeholder="Password confirmation"
                        required
                    >
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    <div v-if="false">
                        <span class="help-block" >Password confirmation is required.</span>
                    </div>
                    <div v-show="password !== password_confirmation && password != '' && password_confirmation != ''">
                        <span class="help-block">New Password and its confirmation must match.</span>
                    </div>
                </div>

                <div class="row">
                    <!-- /.col -->
                    <div class="col-xs-12 reset-btn-block">
                        <a href="javascript:void(0)"
                            @click.prevent="doResetPassword()"
                            :disabled=" ! isDataValid"
                            class="btn btn-primary btn-block btn-flat"
                        >Reset Password</a>
                    </div>
                    <!-- /.col -->
                </div>
            </form>

            <router-link to="/login">Back to login</router-link>


            <!-- /.social-auth-links -->


        </div>
        <!-- /.login-box-body -->
    </div>
</template>

<script>
    import http from '../../mixins/http';

    export default {
        mixins: [http],

        data: function () {
            return {
                email: '',
                password: '',
                password_confirmation: '',
                isPasswordSet: false,
                errorMessage: '',
                token: this.$route.params['token']
            }
        },

        methods: {
            doResetPassword: function() {

                let data = {
                    email: this.email,
                    password: this.password,
                    password_confirmation: this.password_confirmation,
                    token: this.token
                };
                if (this.isDataValid) {
                    this.httpPost('/api/password/reset/'+this.token, data)
                        .then(data => {
                            this.errorMessage = '';
                            this.isPasswordSet = true;
                        })
                        .catch(err => {
                            this.errorMessage = err.data.message;
                        })
                }
            }
        },
        computed: {
            isDataValid: function () {
                return (this.password !== this.password_confirmation) || this.password == '' || this.password_confirmation == '' ? false : true;
            }
        }
    }
</script>

<style scoped>
    .reset-btn-block {
        margin-bottom: 10px;
    }
</style>