<template>
    <div 
        @keydown.enter.prevent="updateEdit"
        contenteditable="true"
        :data-placeholder="placeholder"
        @input="$emit('update:content', $event.target.innerText)" 
        class="address-name"
    ></div>
</template>

<script>
    export default {
        name: "div-editable",
        props: ['content', 'placeholder'],
        mounted: function () {
            this.$el.innerText = this.content;
        },
        methods: {
            updateEdit: function () {
                this.$emit('updateEdit');
            }
        }
    }
</script>

<style scoped>
    .address-name {
        cursor: text;
    }
    .address-name:focus {
        outline: none;
        cursor: text;
    }
</style>