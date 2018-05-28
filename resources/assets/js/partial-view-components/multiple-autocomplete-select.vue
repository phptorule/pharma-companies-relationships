<template>
    <div class="main-block">
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
            <h4>New product</h4>
            <div class="form-group">
                <input type="text" 
                    class="input"
                    placeholder="Company"
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
            <div class="form-group">
                <button class="upload-button">Upload image</button>
                <input type="file" style="display: none;">
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
                    <a href="#" class="button" @click.prevent="addItem">Add</a>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
import http from '../mixins/http';
    export default {
        name: 'muliple-autocomplete-select',
        props: ['selectedOptions', 'type', 'close', 'update'],
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
                    description: ''
                }
            }
        },
        watch: {
            // query: function () {
            //     if (this.list.length > 0 && this.filtered.length < 1) {
            //         this.addNewItem = true;
            //     } else {
            //         this.addNewItem = false;
            //     }
            // }
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
                        return item.name.toLowerCase().indexOf(e.target.value.toLowerCase()) + 1 || 
                               item.company.toLowerCase().indexOf(e.target.value.toLowerCase()) + 1
                    } else {
                        return item.name.toLowerCase().indexOf(e.target.value.toLowerCase()) + 1
                    }
                });
            },
            closeSelf: function () {
                this.close();
            },
            addItem: function () {
                this.update(this.selected);
            },
            toggleAddNewItemForm: function () {
                this.addNewItemForm = !this.addNewItemForm;
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
    .main-block {
        position: relative;
    }
    
    .wrap-block {
        position: absolute;
        z-index: 1;
        background: #fff;
        box-shadow: 0px 0px 20px 2px rgba(0,0,0,0.2);
        padding: 10px;
        border-radius: 5px;
        left: 10px;
        min-width: 200px;
    }

    .input {
        width: 300px;
        border-bottom: 2px solid #EAEFF4;
        outline: none;
        border-top: none;
        border-left: none;
        border-right: none;

   }

   .cancel-btn {
        background: #fff;
        font-family: Montserrat;
        font-size: 13px;
        margin: 0;
   }
   
   .cancel-btn:active {
        box-shadow: none;
   }

   .button {
        background: #4a90e3;
        color: #fff !important;
        padding: 10px 15px;
        border-radius: 5px;
        font-family: Montserrat;
        font-size: 13px;
        text-align: left;
        margin: 0;
        transition: background-color 0.1s linear;
   }
   
   .button:hover {
        background: #5ba3f4;
   }

    .list-block {
        background: #fff;
        max-height: 200px;
        overflow: auto;
        overflow-x: hidden;
        margin-bottom: 15px;
    }

   .check-container {
        display: block;
        position: relative;
        padding-left: 22px;
        margin-bottom: 12px;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        font-weight: normal;
    }
    .check-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .checkmark {
        position: absolute;
        top: 3px;
        left: 0;
        height: 15px;
        width: 15px;
        background-color: #eee;
        border-radius: 50%;
    }
    .check-container input:checked ~ .checkmark {
        background-color: #2196F3;
    }

    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }
    .check-container input:checked ~ .checkmark:after {
        display: block;
    }
    .check-container .checkmark:after {
        top: 5px;
        left: 5px;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: white;
    }
    .list-block::-webkit-scrollbar {
        width: 5px;
        border-radius: 5px;
    }

    .list-block::-webkit-scrollbar-track {
        background: #fff;
    }
 
    .list-block::-webkit-scrollbar-thumb {
        background: #f1f1f1; 
        border-radius: 10px;
    }

    .list-block::-webkit-scrollbar-thumb:hover {
        background: #555; 
    }

    .left-block {
        float: left;
        margin-top: 5px;
    }

    .right-block {
        float: right
    }

    .confirm-block .left-block .add-link {
        color: #72afd2;
        position: relative;
        margin-left: 22px;
    }

    .plus {
        font-size: 35px;
        position: absolute;
        top: -16px;
        left: -23px;
    }

    .description-area {
        max-width: 300px;
        min-width: 300px;
        min-height: 100px;
        max-height: 200px;
        border-bottom: 2px solid #EAEFF4;
        outline: none;
        border-top: none;
        border-left: none;
        border-right: none;
    }

    .upload-button {
        background: #4a90e3;
        color: #fff !important;
        padding: 10px 15px;
        border-radius: 5px;
        font-family: Montserrat;
        font-size: 13px;
        text-align: left;
        margin: 0;
        transition: background-color 0.1s linear;
    }

    .upload-button:hover {
        background: #5ba3f4;
   }
</style>
