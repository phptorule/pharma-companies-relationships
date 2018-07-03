<template>
    <div class="main-block multiple-autocomplete-select">
        <div class="wrap-block" v-if="! addNewItemForm">
            <div class="search-block form-group">
                <i class="fa fa-search icon" aria-hidden="true"></i>
                <input @input="handleSearch" 
                    id="product-search-input" 
                    type="text" 
                    class="input" 
                    v-model="query" 
                    placeholder="Product name"
                >
            </div>
            <div class="list-block">
                <label class="check-container" v-for="item in filtered" :key="item.id">
                    {{ item.name ? item.company + ': ' + item.name : item.company }}
                    <input 
                        type="checkbox" 
                        :id="item.id" 
                        :value="item.id"
                        v-model="selected" 
                    >
                    <span class="checkmark"></span>
                </label>
                <div v-if="this.filtered.length < 1">
                    No matches
                </div>
            </div>
            <div class="confirm-block">
                <div class="left-block">
                    <a href="#" class="add-link" @click.prevent="toggleAddNewItemForm">
                        <span class="plus">+</span> New product
                    </a>
                </div>
                <div class="right-block">
                    <button type="button" @click.prevent="closeSelf()" class="btn cancel-btn">
                        Cancel
                    </button>
                    <a href="#" class="button" @click.prevent="addItem">Add</a>
                </div>
            </div>
        </div>
        <div class="wrap-block" v-else>
            <h4 class="new-product-title">New product</h4>
            <div class="form-group">
                <input type="text" 
                    class="input"
                    placeholder="Company*"
                    v-model="newItem.company" 
                    id="new-item-company"
                >
            </div>
            <div class="form-group">
                <input type="text" 
                    class="input" 
                    placeholder="Name"
                    v-model="newItem.name" 
                    id="new-item-name"
                >
            </div>
            <div class="form-group upload-block">
                <input type="text"
                    class="input image-name" 
                    placeholder="Image (jpg, jpeg, png)"
                    v-model="imageName"
                    id="new-item-image-name"
                    readonly
                >
                <a href="#" 
                    class="upload-button"
                    @click.prevent="onUploadClick"
                >
                    Upload image
                </a>
                <input type="file" 
                    style="display: none;" 
                    ref="uploadImageInput" 
                    accept="image/*"
                    @change="onImageUpload"
                >
            </div>
            <div class="form-group">
                <textarea v-model="newItem.description" 
                    class="description-area"
                    placeholder="Description"
                    id="new-item-description"
                ></textarea>
            </div>
            <div class="confirm-block">
                <div class="right-block">
                    <button type="button" @click.prevent="toggleAddNewItemForm" class="btn cancel-btn">
                        Cancel
                    </button>
                    <a href="#" class="button" @click.prevent="addProduct">Add</a>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
import http from '../mixins/http';
    export default {
        name: 'muliple-autocomplete-select',
        props: ['selectedOptions', 'type', 'close', 'update', 'addNewProduct'],
        mixins: [http],
        data: function () {
            return {
                list: [],
                filtered: [],
                selected: [],
                query: '',
                addNewItemForm: false,

                newItem: {
                    company: '',
                    name: '',
                    description: '',
                    image: ''
                },
                imageName: ''
            }
        },
        methods: {
            loadList: function () {
                this.httpGet('/api/' + this.type)
                    .then(data => {
                        this.list = data;
                        this.filtered = this.list;
                    });
            },
            handleSearch: function (e) {
                this.filtered = this.list.filter((item) => {
                    if (this.type === 'products') {
                        return (item.name && item.name.toLowerCase().indexOf(e.target.value.toLowerCase()) + 1) || 
                               item.company.toLowerCase().indexOf(e.target.value.toLowerCase()) + 1
                    } else {
                        return item.name.toLowerCase().indexOf(e.target.value.toLowerCase()) + 1
                    }
                });
            },
            closeSelf: function () {
                this.close();
                this.clearNewProductForm();
            },
            addItem: function () {
                this.update(this.selected);
                this.clearNewProductForm();
            },
            addProduct: function () {
                this.addNewProduct(this.newItem);
            },
            toggleAddNewItemForm: function () {
                this.addNewItemForm = !this.addNewItemForm;
            },
            onUploadClick: function () {
                this.$refs.uploadImageInput.click();
            },
            onImageUpload: function () {
                let fileName = this.$refs.uploadImageInput.files[0].name;
                let idxDot = fileName.lastIndexOf(".") + 1;
                let extFile = fileName.substr(idxDot, fileName.length).toLowerCase();

                if (extFile == "jpg" || extFile == "jpeg" || extFile == "png") {
                    this.newItem.image = this.$refs.uploadImageInput.files[0];
                    this.imageName = this.newItem.image.name;
                } else {
                    alertify.notify('Only jpg/jpeg/png files are allowed!', 'error', 3);
                }   
            },
            clearNewProductForm: function () {
                this.newItem.company = '';
                this.newItem.name = '';
                this.newItem.description = '';
                this.newItem.image = '';
                this.imageName = '';
            }
        },
        mounted: function () {
            this.selectedOptions.forEach(item => {
                this.selected.push(item.id);
            });
            this.loadList();
        }
    }
</script>

<style scoped>
    
</style>
