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
                                placeholder="New password"
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
                            <a href="#" class="ep-forgot-password-link" @click.prevent>I forgot my password</a>
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
                let url = '/api/user/update-profile-settings';
                this.httpPut(url, {
                    name: this.name,
                    email: this.email
                })
                    .then(data => {
                        console.log(data);
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
            }, 400)
        },

        mounted: function () {
            this.getUser();
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
    .ep-wrapper {
        display: flex;
        justify-content: center;
        height: calc(100vh - 122px);
        overflow-y: auto;
        font-family: Montserrat;
    }

    .avatar-upl .ep-mb-15 {
        margin-bottom: 15px;
    }

    .ep-wrapper h3 {
        font-family: Montserrat;
        margin-top: 0;
        margin-bottom: 11px;
    }

    .ep-container {
        width: 80%;
    }

    .ep-edit {
        display: flex;
        border-top: 1px solid #d2d6de;
    }

    .ep-info {
        width: 33%;
        padding: 15px 15px 15px 0;
    }

    .ep-info-text {
        font-weight: 600;
    }

    .ep-fields {
        width: 67%;
        padding: 15px 0 15px 15px;
        display: flex;
        align-items: center;
    }

    .ep-breadcrumbs {
        height: 70px;
        display: flex;
        align-items: center;
    }

    .ep-breadcrumbs-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .ep-breadcrumbs-list li {
        display: inline-block;
        margin-right: 7px;
    }

    .ep-breadcrumbs-list li.active {
        font-weight: 600;
    }

    .fw-500 {
        color: #6c6b6a;
        font-weight: 500;
    }

    .fw-600 {
        font-weight: 600;
    }

    .remove-avatar-btn {
        background-color: #df3a0f;
        color: #fff;
        font-family: Montserrat;
        font-weight: 500;
    }

    .remove-avatar-btn:focus {
        outline: none;
    }

    .change-avatar-btn {
        background-color: #00aa4d;
        color: #fff;
        font-family: Montserrat;
        font-weight: 500;
    }
    
    .change-avatar-btn:focus {
        outline: none;
    }

    .ep-avatar-image {
        display: block;
        cursor: pointer;
        width: 150px;
        min-width: 150px;
        height: 150px;
    }

    .ep-avt-cont {
        width: 100%;
        height: 100%;
    }

    .ep-avatar-image img {
        object-fit: cover;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        /* border: 1px solid #d2d6de; */
    }

    .avatar-upl {
        min-height: 120px;
        height: 100%;
        width: 100%;
        display: inline-block;
        margin-left: 20px;
    }

    .avatar-upl p {
        margin: 0;
    }

    .avatar-upl input {
        display: none;
    }

    .upload-btn {
        background-color: #fff;
        border: 1px solid #d2d6de;
    }

    .upload-btn:focus {
        outline: none;
    }

    .nf-chosen {
        margin-left: 15px;
    }

    .ep-settings-container {
        width: 100%;
    }

    .ep-name-id-container {
        width: 100%;
        display: flex;
        justify-content: space-between;
    }

    .ep-name-container {
        width: 75%;
    }

    .ep-id-container {
        width: 20%;
    }

    .ep-name-id-text {
        margin-top: 5px;
        margin-bottom: 10px;
    }

    .ep-email-text {
        margin-top: 5px;
        margin-bottom: 5px;
    }

    .ep-password-container {
        width: 100%;
    }

    #ep-edit-profile-password {
        margin-bottom: 5px;
    }

    .ep-new-password, .ep-confirm-password {
        margin-top: 10px;
    }

    .ep-save-password {
        margin-top: 15px;
    }

    .ep-save-password-btn {
        background-color: #00aa4d;
        color: #fff;
        font-weight: 500;
    }

    .ep-save-password-btn:focus {
        outline: none;
    }

    .ep-forgot-password-link {
        margin-left: 15px;
    }

    .ep-max-fsize {
        border-bottom: 1px solid #d2d6de;
        padding-bottom: 10px;
    }
</style>


