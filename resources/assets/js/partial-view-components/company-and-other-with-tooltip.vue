<template>
    <span>
        <span v-if="entity.addresses.length"
              class="person-first-address-name"
              :title="entity.addresses[0].name"
        >
            <a href="javascript:void(0)" @click="goToAddressDetailsPage(entity.addresses[0].id)">
                {{entity.addresses[0].name}}
            </a>
        </span>

        <span v-if="entity.addresses.length > 1">

            <a href="javascript:void(0)"
               v-if="!moreTextWithAND"
               class="person-more-companies"
               v-tooltip.bottom="{ html: 'tooltipContent' + entity.id }"
            >
                + {{entity.addresses.length - 1}} more

                <span v-if="entity.addresses.length - 1 === 1">company</span>
                <span v-if="entity.addresses.length - 1 > 1">companies</span>
            </a>

            <span v-if="moreTextWithAND">

                and

                <a href="javascript:void(0)"
                   class="person-more-companies"
                   v-tooltip.bottom="{ html: 'tooltipContent' + entity.id }"
                >
                    {{entity.addresses.length - 1}} other

                    <span v-if="entity.addresses.length - 1 === 1">company</span>
                    <span v-if="entity.addresses.length - 1 > 1">companies</span>
                </a>
            </span>

            <span class="product-tooltip" :id="'tooltipContent' + entity.id" style="display: block">
                <ul style="margin: 0">
                    <li v-for="add of entity.addresses">
                        {{add.name}}
                    </li>
                </ul>
            </span>

            <br>
        </span>
    </span>
</template>

<script>
    export default {
        name: "company-and-other-with-tooltip",
        
        data: function () {
            return {}
        },

        methods: {
            goToAddressDetailsPage: function (addressId) {
                if (this.isRememberPreviousPage) {
                    localStorage.setItem('previous-dashboard', this.$route.fullPath);
                }

                this.$router.push('/address-details/'+addressId);
            }
        },
        
        props: ['entity', 'moreTextWithAND', 'isRememberPreviousPage']
    }
</script>

<style scoped>

</style>