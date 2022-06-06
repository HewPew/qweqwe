<template>
    <label :class="`v-plusminus ${hidden ? 'v-plusminus__hidden' : ''}`">
        {{ label ? label : item.opened ? '-' : '+' }}

        <input v-if="!hidden" type="checkbox" class="v-plusminus__checkbox" v-model="item.opened">
    </label>
</template>

<script>
    export default {
        props: [
            'item', 'hidden', 'label'
        ]
    }
</script>

<style>
    .v-plusminus {
        cursor: pointer;
    }

    .v-plusminus__hidden {
        color: transparent;
        display: none;
    }

    .v-plusminus__checkbox {
        display: none;
        visibility: hidden;
    }
</style>
