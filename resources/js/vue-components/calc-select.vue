<template>

    <div v-if="data.length" class="v-calc-select">
<!--        <input type="search" :placeholder="`${data.filter(item => item.checked).length ? 'Выбрано ('+data.filter(item => item.checked).length+')' : 'Выберите значения'}`" class="calc-select__search" />-->

        <div v-if="showed" class="calc-select calc-select__root">

            <div v-for="(item, index) in data" class="calc-select__item">
                <label>
                    <input type="checkbox" @change="inputTrigger" v-model="item.checked" />
                    {{ index+1 }}. {{ item[trackBy] }}
                </label>
            </div>

        </div>

    </div>
    <div v-else class="v-calc-select">
        Загружаем данные..
    </div>

</template>

<script>
     export default {
         data () {
             return {
                 showed: 1,
                 search: ''
             }
         },

         props: [
             'data', 'field', 'trackBy', 'inputTrigger'
         ]
     }
</script>

<style>
    .v-calc-select {
        font-size: 13px;
    }

    .calc-select,
    .calc-select * {
        user-select: none;
    }

    .calc-select__root {
        width: 100%;
        padding-top: 4px;
        border: 1px solid #e9e9e9;
        max-height: 100px;
        overflow-y: auto;
    }

    .calc-select__item {
        margin-bottom: 2.5px;
        padding-left: 15px;
        transition: .5s ease;
    }

    .calc-select__item:hover {
        box-shadow: 0px 2px 10px rgba(0,0,0,.05)
    }

    .calc-select__search {
        width: 100%;
        font-size: 13px;
        border: 1px solid #e9e9e9;
        border-bottom: none;
        cursor: pointer;
        pointer-events: none;
        padding-left: 10px;
    }
</style>
