<template>
    <div class="login-box">
        <div class="login-logo">
            <a href="javascript:void(0)"><b>Labscape</b>&beta;</a>
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body">
            <p v-if="!errorText && !isSuccessfulLogin" class="login-box-msg">Sign in to start your session</p>

            <p v-if="errorText" class="login-box-msg text-danger"><i class="fa fa-exclamation-circle"></i> {{errorText}}</p>

            <p v-if="isSuccessfulLogin" class="login-box-msg text-success"><i class="fa fa-check"></i> Logged in successfully. Please wait...</p>

            <!--<form>-->
                <div class="form-group has-feedback">
                    <input @keyup.enter.prevent="doLogin" @keyup="errorText=''" type="email" class="form-control" v-model="email" placeholder="Email">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input @keyup.enter.prevent="doLogin" @keyup="errorText=''" type="password" class="form-control" v-model="password" placeholder="Password">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-8">
                        <div class="grey-checkbox checkbox-margin-top">
                            <label>
                                <input type="checkbox" id="remember-me" v-model="remember_me">
                                <span></span>
                                <span class="remember_text">Remember Me</span>
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-xs-4">
                        <a href="javascript:void(0)" @click="doLogin()" class="btn btn-primary btn-block btn-flat">Sign In</a>
                    </div>
                    <!-- /.col -->
                </div>
            <!--</form>-->

            <!--<a href="#">I forgot my password</a><br>-->
            <router-link to="/forgot-password">I forgot my password</router-link><br>

        </div>
        <!-- /.login-box-body -->
    </div>
</template>

<script>
    import http from '../../mixins/http';
    import AuthService from '../../services/auth-service';

    export default {
        mixins: [http],
        data: function () {
            return {
                email: '',
                password: '',
                remember_me: false,
                errorText: '',
                isSuccessfulLogin: false
            }
        },
        created: function () {

        },
        methods: {
            doLogin: function() {

                let data = {
                    email: this.email,
                    password: this.password
                };

                this.httpPost('/api/login', data)
                    .then(data => {
                        if(!data.success || !data.data) {
                            console.log(data);
                            return;
                        }

                        AuthService.isLoggedIn = true;
                        this.isSuccessfulLogin = true;

                        localStorage.setItem('auth-token', data.data.token);
                        localStorage.setItem('logged-user', JSON.stringify(data.data.user));

                        this.$root.logData('login', 'sign in', JSON.stringify(''));
                        // this.$router.push('/dashboard');
                        window.location.assign('/dashboard')
                    })
                    .catch(err => {
                        console.log('err.data.message', err.data.message);
                        this.errorText = err.data.message;
                    })
            },
        }
    }
</script>

<style scoped>

</style>