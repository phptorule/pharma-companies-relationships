<template>
    <div>
        <div class="admin-users">
            <div class="au-container">
                <div class="au-breadcrumbs">
                    <ul class="au-breadcrumbs-list">
                        <li>Admin</li>
                        <li><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                        <li class="active">Website Notifications</li>
                    </ul>
                </div>

                <h2 class="text-center">Website Notifications</h2>

                <div class="row">
                    <div class="col-sm-12">
                        <table class="table admin-notificationurations-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Key</th>
                                <th>Title</th>
                                <th>Body</th>
                                <th>Type</th>
                                <th>Expired at</th>
                                <th>Last Update</th>
                                <th >
                                    <button class="btn btn-success" @click.prevent="addNew()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr v-if="editedNotification.id === 'new-notification'">
                                <td>New</td>

                                <td>
                                    <input v-model="editedNotification.key" class="form-control" :class="{'has-error': editedNotification.key == ''}">
                                </td>

                                <td>
                                    <input v-model="editedNotification.title" class="form-control">
                                </td>

                                <td>
                                    <input v-model="editedNotification.body" class="form-control">
                                </td>

                                <td>
                                    <select v-model="editedNotification.type" class="form-control">
                                        <option :value="type" :selected="type == 'default'" v-for="type of notificationTypes">{{type}}</option>
                                    </select>
                                </td>

                                <td>
                                    <input v-model="editedNotification.expired_at" class="form-control">
                                </td>

                                <td></td>

                                <td>
                                    <button class="btn btn-success"
                                            @click="saveNewConfig()"
                                            :disabled="editedNotification.key === null || editedNotification.key == ''"
                                    >
                                        <i class="fa fa-floppy-o"></i>
                                    </button>

                                    <button class="btn btn-warning"
                                            @click="editedNotification = setEditedNotificationInitValues()"
                                    >
                                        <i class="fa fa-remove"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr v-for="(notification, i)  of notificationList">
                                <td>{{notification.id}}</td>

                                <td>
                                    <span v-if="isHiddenWhileEditing(notification, i)" >
                                        {{notification.key}}
                                    </span>

                                    <input type="text"
                                           v-if="isShownWhileEditing(notification, i)"
                                           :value="editedNotification.key"
                                           @keyup="editKey"
                                           class="form-control"
                                           :class="{'has-error': editedNotification.key == ''}"
                                    >
                                </td>

                                <td>
                                    <span v-if="isHiddenWhileEditing(notification, i)">
                                        {{notification.title === null ? 'NULL' : notification.title}}
                                    </span>

                                    <input type="text"
                                           v-if="isShownWhileEditing(notification, i)"
                                           :value="editedNotification.title"
                                           class="form-control"
                                           @keyup="editTitle"
                                    >
                                </td>

                                <td>
                                    <span v-if="isHiddenWhileEditing(notification, i)">
                                        {{notification.body === null ? 'NULL' : notification.body}}
                                    </span>

                                    <input type="text"
                                           v-if="isShownWhileEditing(notification, i)"
                                           :value="editedNotification.body"
                                           class="form-control"
                                           @keyup="editBody"
                                    >
                                </td>

                                <td>
                                    <span v-if="isHiddenWhileEditing(notification, i)" :class="'text-'+notification.type">
                                        {{notification.type}}
                                    </span>

                                    <select @change="editType"
                                            v-if="isShownWhileEditing(notification, i)"
                                            class="form-control"
                                    >
                                        <option :value="type" :selected="type == notification.type" v-for="type of notificationTypes">{{type}}</option>
                                    </select>
                                </td>


                                <td>
                                    <span v-if="isHiddenWhileEditing(notification, i)">
                                        {{notification.expired_at === null ? 'NULL' : notification.expired_at}}
                                    </span>

                                    <input type="text"
                                           v-if="isShownWhileEditing(notification, i)"
                                           :value="editedNotification.expired_at"
                                           class="form-control"
                                           @keyup="editExpiredAt"
                                    >
                                </td>


                                <td>{{notification.updated_at}}</td>
                                <td>
                                    <button class="btn btn-primary"
                                            v-if="isHiddenWhileEditing(notification, i)"
                                            @click.prevent="startEdit(notification, i)">
                                        <i class="fa fa-pencil"></i>
                                    </button>

                                    <button class="btn btn-success"
                                            v-if="isShownWhileEditing(notification, i)"
                                            :disabled="editedNotification.key === null || editedNotification.key == ''"
                                            @click="updateConfig()"
                                    >
                                        <i class="fa fa-floppy-o"></i>
                                    </button>

                                    <button class="btn btn-danger"
                                            v-if="isShownWhileEditing(notification, i)"
                                            @click="requestDeleteConfirmation()"
                                    >
                                        <i class="fa fa-trash-o"></i>
                                    </button>

                                    <button class="btn btn-warning"
                                            v-if="isShownWhileEditing(notification, i)"
                                            @click="editedNotification = setEditedNotificationInitValues()"
                                    >
                                        <i class="fa fa-remove"></i>
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import http from '../../../mixins/http';

    export default {

        mixins: [http],

        data: function() {
            return {
                notificationList: [],
                editedNotification: this.setEditedNotificationInitValues(),
                notificationTypes: [
                    'default',
                    'success',
                    'warning',
                    'danger'
                ]
            }
        },

        methods: {
            loadNotificationList: function () {
                return this.httpGet('/api/admin/website-notifications')
                    .then(data => this.notificationList = data)
            },

            setEditedNotificationInitValues: function () {
                return {
                    rowIndex: null,
                    id: null,
                    key: null,
                    title: null,
                    body: null,
                    type: 'default',
                    expired_at: null,
                }
            },

            startEdit: function(notificationObj, index) {
                this.editedNotification = notificationObj;
                this.editedNotification.rowIndex = index;
            },

            editKey: function (e) {
                this.editedNotification.key = e.target.value;
            },

            editTitle: function (e) {
                this.editedNotification.title = e.target.value;
            },

            editBody: function (e) {
                this.editedNotification.body = e.target.value;
            },

            editType: function(e) {
                this.editedNotification.type = e.target.value;
            },

            editExpiredAt: function (e) {
                this.editedNotification.expired_at = e.target.value;
            },

            isShownWhileEditing: function(notification, i) {
                return this.editedNotification.id === notification.id && this.editedNotification.rowIndex === i;
            },

            isHiddenWhileEditing: function (notification, i) {
                return this.editedNotification.id !== notification.id && this.editedNotification.rowIndex !== i;
            },

            updateConfig: function () {
                return this.httpPut('/api/admin/website-notifications/'+this.editedNotification.id, this.editedNotification)
                    .then(data => {
                        this.loadNotificationList();

                        this.editedNotification = this.setEditedNotificationInitValues();

                        return data;
                    })
            },

            addNew: function () {
                this.editedNotification = this.setEditedNotificationInitValues();
                this.editedNotification.id = 'new-notification';
            },

            saveNewConfig: function () {
                return this.httpPost('/api/admin/website-notifications', this.editedNotification)
                    .then(data => {
                        this.loadNotificationList();
                        this.editedNotification = this.setEditedNotificationInitValues();
                    })
            },

            requestDeleteConfirmation: function() {
                alertify.confirm('Please confirm deletion.',
                    'Are you sure to delete <strong>' + this.editedNotification.key +'</strong> notification?',
                    () => { this.deleteNotification() },
                    () => { }
                );
            },

            deleteNotification: function () {
                return this.httpDelete('/api/admin/website-notifications/' + this.editedNotification.id)
                    .then(data => {
                        this.loadNotificationList();
                        this.editedNotification = this.setEditedNotificationInitValues();
                    })
            }
        },

        mounted: function () {
            this.loadNotificationList();
        }
    }
</script>

<style scoped>

</style>