<template>
    <span class="sorter">
        <a href="#" @click.prevent="changeAction(-1)">
            <i class="fa fa-angle-up"></i>
        </a>

        <a href="#" @click.prevent="changeAction(1)">
            <i class="fa fa-angle-down"></i>
        </a>
    </span>
</template>

<script>
export default {
    name: "Sorter",
    props: ['methodAction', 'list', 'index'],

    methods: {
        changeAction (action) {
            this.methodAction(this.list, this.index, action)
        }
    }
}
</script>

<style scoped>
    .sorter > a {
        display: block;
        width: 100%;
        font-size: 12px;
    }
    .sorter > a:hover {
        background: #e9e9e9;
    }
    .sorter {
        width: 15px;
        text-align: center;
        display: inline-block;
        font-weight: bold;
    }
</style>
