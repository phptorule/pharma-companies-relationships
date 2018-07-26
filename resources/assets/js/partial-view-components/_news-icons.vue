<template>
    <div class="news-icon-type pull-right">
        <img v-if="feed && feed.icon === 'people'" src="/images/man.svg" alt="">
        <img v-if="feed && feed.icon === 'organisation'" src="/images/organisation.svg" class="home">
    </div>
</template>

<script>
    export default {
        props: ['feed']
    }
</script>

<style scoped>

</style>