<template>
    <div>
        <div class="admin-users">
            <div class="au-container">
                <div class="au-breadcrumbs">
                    <ul class="au-breadcrumbs-list">
                        <li>Admin</li>
                        <li><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                        <li class="active">Configurations</li>
                    </ul>
                </div>

                <h2 class="text-center">Configurations</h2>

                <div class="row">
                    <div class="col-sm-12">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Key</th>
                                <th>Value</th>
                                <th>Last Update</th>
                                <th >
                                    <button class="btn btn-success" @click.prevent="addNew()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(config, i)  of configList">
                                <td>{{config.id}}</td>

                                <td>
                                    <span v-if="isHiddenWhileEditing(config, i)" >
                                        {{config.key}}
                                    </span>

                                    <input type="text"
                                           v-if="isShownWhileEditing(config, i)"
                                           :value="config.key"
                                           @keyup="editKey"
                                    >
                                </td>

                                <td>
                                    <span v-if="isHiddenWhileEditing(config, i)">
                                        {{config.value === null ? 'NULL' : config.value}}
                                    </span>

                                    <input type="text"
                                           v-if="isShownWhileEditing(config, i)"
                                           :value="config.value"
                                           @keyup="editValue"
                                    >
                                </td>


                                <td>{{config.updated_at}}</td>
                                <td>
                                    <button class="btn btn-primary"
                                            v-if="isHiddenWhileEditing(config, i)"
                                            @click.prevent="startEdit(config, i)">
                                        <i class="fa fa-pencil"></i>
                                    </button>

                                    <button class="btn btn-success"
                                            v-if="isShownWhileEditing(config, i)"
                                            @click="updateConfig()"
                                    >
                                        <i class="fa fa-floppy-o"></i>
                                    </button>

                                    <button class="btn btn-warning"
                                            v-if="isShownWhileEditing(config, i)"
                                            @click="editedConfig = setEditedConfigInitValues()"
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
                configList: [],
                editedConfig: this.setEditedConfigInitValues()
            }
        },

        methods: {
            loadConfigList: function () {
                return this.httpGet('/api/admin/configurations')
                    .then(data => this.configList = data)
            },

            setEditedConfigInitValues: function () {
                return {
                    rowIndex: null,
                    id: null,
                    key: null,
                    value: null
                }
            },

            startEdit: function(configObj, index) {
                this.editedConfig.id = configObj.id;
                this.editedConfig.key = configObj.key;
                this.editedConfig.value = configObj.value;
                this.editedConfig.rowIndex = index;
            },

            editKey: function (e) {
                this.editedConfig.key = e.target.value;
            },

            editValue: function (e) {
                this.editedConfig.value = e.target.value;
            },

            isShownWhileEditing: function(config, i) {
                return this.editedConfig.id === config.id && this.editedConfig.rowIndex === i;
            },

            isHiddenWhileEditing: function (config, i) {
                return this.editedConfig.id !== config.id && this.editedConfig.rowIndex !== i;
            },

            updateConfig: function () {
                return this.httpPut('/api/admin/configurations/'+this.editedConfig.id, this.editedConfig)
                    .then(data => {
                        this.loadConfigList();

                        this.editedConfig = this.setEditedConfigInitValues();

                        return data;
                    })
            }
        },

        mounted: function () {
            this.loadConfigList();
        }
    }
</script>

<style scoped>

</style>