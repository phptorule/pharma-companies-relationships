<template>
    <div>
        <div class="admin-users">
            <div class="au-container">
                <div class="au-breadcrumbs">
                    <ul class="au-breadcrumbs-list">
                        <li>Admin</li>
                        <li><i class="fa fa-angle-right" aria-hidden="true"></i></li>
                        <li class="active">Activity</li>
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
                            <tr v-for="config of configList">
                                <td>{{config.id}}</td>
                                <td>{{config.key}}</td>
                                <td>{{config.value === null ? 'NULL' : config.value}}</td>
                                <td>{{config.updated_at}}</td>
                                <td>
                                    <button class="btn btn-primary"
                                            @click.prevent="edit(config.id)">
                                        <i class="fa fa-pencil"></i>
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
                configList: []
            }
        },

        methods: {
            loadConfigList: function () {
                this.httpGet('/api/admin/configurations')
                    .then(data => {
                        this.configList = data;
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