<template>
    <div class="ep-wrapper">
        <div class="ep-container">
            <div class="ep-breadcrumbs">
                <ul class="ep-breadcrumbs-list">
                    <li>User settings</li>
                    <li><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                    <li class="active">Edit Profile</li>
                </ul>
            </div>
            <div class="ep-edit">
                <div class="ep-info">
                    <h3 class="fw-600">Public Avatar</h3>
                    <p class="ep-info-text">You can change your avatar here or remove the current avatar.</p>
                </div>
                <div class="ep-fields">
                    <div class="ep-avatar-image" v-if="isUserLoaded && user.avatar">
                        <div class="ep-avt-cont">
                            <img :src="user.avatar" alt="avatar" @click.prevent="onFileInputClick">
                        </div>
                    </div>
                    <div class="avatar-upl">
                        <p class="fw-600 ep-mb-15">Upload new avatar</p>
                        <div class="ep-mb-15">
                            <button type="button" 
                                class="btn upload-btn fw-600"
                                @click.prevent="onFileInputClick"
                            >
                                Choose file...
                            </button>
                            <span v-if="profileImage" class="nf-chosen fw-600">{{ profileImage.name }}</span>
                            <span v-else class="nf-chosen fw-600">No file chosen</span>
                            <input 
                                type="file" 
                                ref="avatarInput" 
                                accept="image/*"
                                @change="onFileSelect"
                            >
                        </div>
                        <p class="fw-500 ep-mb-15 ep-max-fsize">The maximum file size allowed is 200KB.</p>
                        <button 
                            v-if=" ! isAvatarDefault"
                            type="button" 
                            class="btn remove-avatar-btn"
                            @click="removeAvatar"
                        >
                            Remove avatar
                        </button>
                        <button 
                            v-if="profileImage"
                            type="button" 
                            class="btn change-avatar-btn"
                            @click="updateProfilePicture"
                        >
                            Change avatar
                        </button>
                    </div>
                </div>
            </div>
            <div class="ep-edit">
                <div class="ep-info">
                    <h3 class="fw-600">Main Settings</h3>
                    <p class="ep-info-text">This information will appear on your profile.</p>
                </div>
                <div class="ep-fields">
                    <div class="ep-settings-container">
                        <div class="ep-name-id-container">
                            <div class="ep-name-container">
                                <label for="edit-profile-name">Name</label>
                                <input id="edit-profile-name" 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Name"
                                    v-model="name"
                                    @keydown.enter.prevent="updateProfileSettings"
                                >
                            </div>
                            <div class="ep-id-container">
                                <label for="edit-profile-id">User ID</label>
                                <input id="edit-profile-id" 
                                    type="text" 
                                    class="form-control" 
                                    disabled 
                                    v-model="userId"
                                >
                            </div>
                        </div>
                        <div class="ep-name-id-text">
                            <span class="fw-500">Enter your name, so people you know can recognize you.</span>
                        </div>
                        <div>
                            <label for="edit-profile-email">Email</label>
                            <input id="edit-profile-email" 
                                type="email" 
                                class="form-control" 
                                placeholder="Email"
                                v-model="email"
                                @keydown.enter.prevent="updateProfileSettings"
                            >
                        </div>
                        <div class="ep-email-text">
                            <span class="fw-500">We also use email for avatar detection if no avatar is uploaded.</span>
                        </div>
                        <div>
                            <button 
                                type="button" 
                                class="btn change-avatar-btn" 
                                @click.prevent="updateProfileSettings">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ep-edit">
                <div class="ep-info">
                    <h3 class="fw-600">Password</h3>
                    <p class="ep-info-text">
                        After a successful password update, 
                        you will be redirected to the login page 
                        where you can log in with your new password.
                    </p>
                </div>
                <div class="ep-fields">
                    <div class="ep-password-container">
                        <div class="ep-password-text">
                            <p class="fw-600">Change your password or recover your current one</p>
                        </div>
                        <div class="ep-current-password">
                            <label for="ep-edit-profile-password">Current password</label>
                            <input id="ep-edit-profile-password" 
                                type="password" 
                                class="form-control"
                                placeholder="Current password"
                                v-model="currentPassword"
                            >
                            <span class="fw-500">
                                You must provide your current password in order to change it.
                            </span>
                        </div>
                        <div class="ep-new-password">
                            <label for="ep-edit-profile-new-password">New password</label>
                            <input id="ep-edit-profile-new-password" 
                                type="password" 
                                class="form-control"
                                placeholder="New password (min 6 characters)"
                                v-model="newPassword"
                            >
                        </div>
                        <div class="ep-confirm-password">
                            <label for="ep-edit-profile-confirm-password">Password confirmation</label>
                            <input id="ep-edit-profile-confirm-password" 
                                type="password" 
                                class="form-control"
                                placeholder="Password confirmation"
                                v-model="newPasswordConfirmation"
                            >
                        </div>
                        <div class="ep-save-password">
                            <button 
                                type="button" 
                                class="btn ep-save-password-btn"
                                @click.prevent="changePassword"
                            >
                                Save password
                            </button>
                            <a href="#" class="ep-forgot-password-link" @click.prevent="redirectToForgotPasswordForm">I forgot my password</a>
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
                user: {},
                name: '',
                userId: '',
                email: '',
                currentPassword: '',
                newPassword: '',
                newPasswordConfirmation: '',
                profileImage: null,
                isUserLoaded: false,
                defaultAvatarName: 'profile-pictures/default-avatar.png',
                originalAvatar: '',
                maxFileSize: 200000
            }
        },

        watch: {
            isUserLoaded: function () {
                if (this.isUserLoaded) {
                    this.name = this.user.name;
                    this.userId = this.user.id;
                    this.email = this.user.email;
                }
            }
        },
        
        methods: {
            onFileInputClick: function () {
                this.$root.logData('edit_profile', 'click on image input', JSON.stringify(''));
                this.$refs.avatarInput.click();
            },
            getUser: function () {
                let url = '/api/logged-user';
                
                this.httpGet(url)
                    .then(data => {
                        this.user = data.data;
                        this.isUserLoaded = true;
                        this.originalAvatar = data.originalAvatar;
                    })
                    .catch(error => {
                        console.log(error);
                    })
            },
            onFileSelect: function () {
                this.$root.logData('edit_profile', 'image selected', JSON.stringify(''));
                let file = this.$refs.avatarInput.files[0];

                if (file) {
                    let fileName = file.name;
                    let fileSize = file.size;
                    let idxDot = fileName.lastIndexOf(".") + 1;
                    let extFile = fileName.substr(idxDot, fileName.length).toLowerCase();

                    if (fileSize > this.maxFileSize) {
                        alertify.notify('The maximum file size allowed is 200KB!', 'error', 3);
                        return;
                    }

                    if (extFile == "jpg" || extFile == "jpeg" || extFile == "png") {
                        this.profileImage = file;
                    } else {
                        alertify.notify('Only jpg/jpeg/png files are allowed!', 'error', 3);
                        return;
                    }
                }
            },
            updateProfilePicture: _.debounce(function () {
                this.$root.logData('edit_profile', 'update profile picture', JSON.stringify(''));
                let formData = new FormData();
                formData.append('profile-picture', this.profileImage);
                let url = '/api/user/update-profile-picture';
                this.httpPost(url,
                    formData
                )
                    .then(data => {
                        this.user = data.data;
                        this.profileImage = null;
                        this.originalAvatar = data.originalAvatar;
                        this.$refs.avatarInput.value = "";
                        this.$eventGlobal.$emit('userProfileUpdated', this.user);
                        alertify.notify('Profile picture updated', 'success', 3);
                    })
                    .catch(error => {
                        alertify.notify('Error occured', 'error', 3);
                    })
            }, 400),
            removeAvatar: _.debounce(function () {
                this.$root.logData('edit_profile', 'remove avatar', JSON.stringify(''));
                let url = '/api/user/remove-avatar';
                this.httpPost(url)
                    .then(data => {
                        this.user = data.data;
                        this.profileImage = null;
                        this.originalAvatar = data.originalAvatar;
                        this.$refs.avatarInput.value = "";
                        this.$eventGlobal.$emit('userProfileUpdated', this.user);
                        alertify.notify('Profile picture updated', 'success', 3);
                    })
                    .catch(error => {
                        alertify.notify('Error occured', 'error', 3);
                    })
            }, 400),
            updateProfileSettings: _.debounce(function () {
                this.$root.logData('edit_profile', 'update profile settings', JSON.stringify(''));
                let url = '/api/user/update-profile-settings';
                this.httpPut(url, {
                    name: this.name,
                    email: this.email
                })
                    .then(data => {
                        if (data.success) {
                            this.user = data.data;
                            alertify.notify('Profile settings updated', 'success', 3);
                        } else {
                            alertify.notify(data.message, 'error', 3);
                        }
                    })
                    .catch(error => {
                        alertify.notify('Error occured', 'error', 3);
                    })
            }, 400),
            changePassword: _.debounce(function () {
                this.$root.logData('edit_profile', 'change password', JSON.stringify(''));
                let url = '/api/user/change-password';
                this.httpPost(url, {
                    current_password: this.currentPassword,
                    new_password: this.newPassword,
                    new_password_confirmation: this.newPasswordConfirmation
                })
                    .then(data => {
                        if (data.success) {
                            this.currentPassword = '';
                            this.newPassword = '';
                            this.newPasswordConfirmation = '';
                            alertify.notify('Password successfully changed', 'success', 3);
                        } else {
                            alertify.notify(data.message, 'error', 3);
                        }
                    })
                    .catch(error => {
                        alertify.notify(error.data.message, 'error', 3);
                    })
            }, 400),
            redirectToForgotPasswordForm: function () {
                this.$root.logData('edit_profile', 'click on forgot password', JSON.stringify(''));
                localStorage.removeItem("auth-token");
                localStorage.removeItem("logged-user");
                window.location.assign('/forgot-password');
            }
        },

        mounted: function () {
            this.getUser();
            this.$root.logData('edit_profile', 'open', JSON.stringify(''));
        },

        computed: {
            isAvatarDefault: function () {
                if (this.isUserLoaded) {
                    return this.originalAvatar === this.defaultAvatarName ? true : false;
                } else {
                    return true;
                }
            }
        }
    }
</script>

<style scoped>
    
</style>


