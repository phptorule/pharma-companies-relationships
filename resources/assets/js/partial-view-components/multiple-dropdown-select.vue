<template>
    <ul class="nav nav-tabs" :id="blockId">
        <li class="dropdown">
            <a class="dropdown-toggle" @click="toogleDropdown($event)" data-toggle="dropdown" href="#" :title="selectedValuesNamesString? name +': '+ selectedValuesNamesString : name">

                <span class="caret"></span>
                {{selectedValuesNamesString? selectedValuesNamesString : name}}
            </a>
            <ul class="dropdown-menu" v-if="this.name != 'Used Products'">
                <li v-for="option in options">
                    <div class="grey-checkbox">
                        <label>
                            <input type="checkbox"
                                   @click="checkboxClick(option.value)"
                                   :id="blockId + option.value"
                                   :checked="selectedValues.indexOf(option.value) !== -1"
                            >
                            <span class="borders"></span>
                            <span class="remember_text">{{option.label}}</span>
                        </label>
                    </div>
                </li>
            </ul>

            <ul class="dropdown-menu" v-if="this.name == 'Used Products'">
                <li :class="['dropdown-child-products', 'drp-' + i]" v-for="(parentProduct, i) in options">
                    <div class="parent-product-block">
                        <span class="borders"></span>
                        <input type="checkbox"
                                    @click="checkboxClick(parentProduct.id)"
                                    :id="blockId + parentProduct.id"
                                    :checked="selectedValues.indexOf(parentProduct.id) !== -1"
                                >
                        <a class="dropdown-child-products-link" @click.prevent="toogleChildDropdown($event, i)" href="#">
                            <img class="parent-product-image" :src="parentProduct.image" alt="">
                            <div class="parent-product-info">
                                <span>{{ parentProduct.company }}</span>
                                <span class="caret"></span>
                            </div>
                        </a>
                    </div>
                    <ul class="child-products-list">
                        <li v-for="childProduct in parentProduct.childProducts">
                            <input type="checkbox"
                                   @click="checkboxClick(childProduct.id)"
                                   :id="blockId + childProduct.id"
                                   :checked="selectedValues.indexOf(childProduct.id) !== -1"
                            >
                            {{ childProduct.name }}
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>
</template>

<script>
    export default {

        data: function () {
            return {
                selectedValues: [],
                blockId: '',
                products: []
            }
        },

        computed: {
            selectedValuesNamesString: function () {
                let str = '';
                this.selectedValues.forEach((id, index) => {
                    if (this.name == 'Used Products') {
                        let option = this.options.find(el => el.id == id);
                        str += option.company + (option.name ? ': ' + option.name : '');
                    } else {
                        let option = this.options.find(el => el.value == id);
                        str += option.label
                    }

                    if(++index !== this.selectedValues.length) {
                        str += ', ';
                    }
                });

                return str;
            }
        },

        watch: {
            options: function (newVal, oldVal) {
                if(newVal.length && !oldVal.length) {
                    this.presetSelectedValue();
                }
            }
        },

        methods: {
            toogleDropdown: function ($event) {

                let dropdownContainer = $($event.target).parent();

                if(dropdownContainer.hasClass('open')) {
                    dropdownContainer.removeClass('open');
                }
                else {
                    dropdownContainer.addClass('open');
                }
            },
            toogleChildDropdown: function ($event, i) {
                let cl = '.drp-' + i;

                let dropdownContainer = $(cl);

                if(dropdownContainer.hasClass('show-child-dropdown')) {
                    dropdownContainer.removeClass('show-child-dropdown');
                }
                else {
                    dropdownContainer.addClass('show-child-dropdown');
                }
            },

            checkboxClick: function (selectedValue) {

                let index = this.selectedValues.indexOf(selectedValue);

                if (index === -1) {
                    this.selectedValues.push(selectedValue);
                }
                else {
                    this.selectedValues.splice(index, 1);
                }

                this.notifyParentComponent();
            },

            notifyParentComponent: function () {
                this.$emit('changed', JSON.parse(JSON.stringify(this.selectedValues)));
            },

            setIdForCurrentComponent: function () {
                this.blockId = this.name.replace(/[^A-Za-z0-9]/g,'').toLowerCase() + Math.round(Math.random()*100);
            },
            resetSelectedValues: function () {
                $('#'+this.blockId+' input[type="checkbox"]:checked').prop('checked', false)
                this.selectedValues = [];
            },

            presetSelectedValue: function () {
                if(!this.selected.length || !this.options.length) {
                    return;
                }

                this.selectedValues = this.selected.map(el => parseInt(el));
            }
        },

        mounted: function () {
            $('.dropdown-menu').on('click', function (e) {
                e.stopPropagation();
            });

            this.setIdForCurrentComponent();
        },


        props: ['options', 'selected', 'name', 'relationalProducts']

    }
</script>

<style scoped>
    li.dropdown-child-products {
        display: flex;
        flex-direction: column;
    }

    li.dropdown-child-products ul {
        display: none;
    }

    li.dropdown-child-products.show-child-dropdown ul{
        display: block;
    }

    .parent-product-block {
        display: flex;
        align-items: center;
    }

    .dropdown-child-products-link {
        background-color: transparent;
        display: flex;
        align-items: center;
    }

    .dropdown-child-products-link:hover {
        background-color: transparent;
    }
    
    .dropdown-child-products-link:focus {
        background-color: transparent;
    }

    .parent-product-image {
        height: 70px;
        border-radius: 50%;
        display: inline-block;
    }

    .parent-product-info {
        display: flex;
        justify-content: space-between;
        width: 100%;
        min-width: 200px;
        margin-left: 20px;
    }

    .child-products-list {
        list-style-type: none;
    }
</style>