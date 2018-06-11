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
                    <h2>Public Avatar</h2>
                    <p class="ep-info-text">You can change your avatar here or remove the current avatar.</p>
                </div>
                <div class="ep-fields">
                    <div>
                        <img src="/images/anonimus-person_100x100.png" alt="">
                    </div>
                    <div class="avatar-upl">
                        <p class="fw-600">Upload new avatar</p>
                        <input type="file">
                        <p class="fw-500">The maximum file size allowed is 200KB.</p>
                        <button type="button" class="btn remove-avatar-btn">Remove avatar</button>
                    </div>
                </div>
            </div>
            <div class="ep-edit">
                <div class="ep-info">
                    <h2>Main Settings</h2>
                    <p class="ep-info-text">This information will appear on your profile.</p>
                </div>
                <div class="ep-fields">
                    
                </div>
            </div>
            <div class="ep-edit">
                <div class="ep-info">
                    <h2>Password</h2>
                    <p class="ep-info-text">
                        After a successful password update, 
                        you will be redirected to the login page 
                        where you can log in with your new password.
                    </p>
                </div>
                <div class="ep-fields">
                    
                </div>
            </div>
        </div>
    </div> 
</template>

<script>
    export default {
        
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

    .ep-wrapper h2 {
        font-family: Montserrat;
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
        font-weight: 600;
    }

    .remove-avatar-btn:focus {
        outline: none;
    }

    .avatar-upl {
        min-height: 120px;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-between;
    }

    .avatar-upl p {
        margin: 0;
    }
</style>


