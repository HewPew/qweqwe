<template>

    <div class="v-tav-select">
<!--        <input type="search" :placeholder="`${directions.options.filter(item => item.checked).length ? 'Выбрано направлений ('+directions.options.filter(item => item.checked).length+')' : 'Выберите значения'}`" class="tav-select__search" />-->

        <div v-if="showed" class="tav-select tav-select__root">

            <div v-for="(direction, index) in directions.options" :key="direction.id" class="tav-select__item">
                <PlusMinus v-if="!is_mobile" :item="direction" />

                <label v-if="!is_mobile">
                    <input type="checkbox" @change="changeFieldValue(direction, 'directions_id')" v-model="direction.checked" />
<!--                            {{ index+1 }}. -->
                    {{ direction.name }}
                </label>
                <div v-else class="tav-select__label">
                    <input type="checkbox" @change="changeFieldValue(direction, 'directions_id')" v-model="direction.checked" />

                    <PlusMinus :item="direction" :label="index+1 + '. ' + direction.name" />
                </div>

                <div class="tav-select" v-if="type_jobs.options.length > 0 && direction.opened">

                    <div v-for="(type_job, index_type_job) in type_jobs.options.filter(tj => tj.direction_id.split(',').includes(direction.id + ''))" :key="type_job.id" class="tav-select__item">
                        <PlusMinus v-if="!is_mobile" :item="type_job" />

                        <label v-if="!is_mobile">
                            <input type="checkbox" @change="changeFieldValue(type_job, 'type_jobs_id')" v-model="type_job.checked" />
<!--                                            {{ index+1 }}.{{ index_type_job+1 }}. -->
                            {{ type_job.name }}
                        </label>
                        <div v-else class="tav-select__label">
                            <input type="checkbox" @change="changeFieldValue(type_job, 'type_jobs_id')" v-model="type_job.checked" />

                            <PlusMinus :item="type_job" :label="(index+1) + '.' + (index_type_job+1) + '.' + type_job.name" />
                        </div>

                        <div v-if="products.options.length > 0 && type_job.opened" class="tav-select">
                            <div v-for="(product, index_product) in products.options.filter(pr => pr.type_job_id.split(',').includes(type_job.id + ''))" :key="product.id" class="tav-select__item tav-select__item--notpm">
                                <PlusMinus v-if="!is_mobile" :item="type_job" hidden="1" />
                                <label>
                                    <input type="checkbox" @change="changeProductField(direction, type_job)" v-model="product.checked" />
<!--                                                            {{ index+1 }}.{{ index_type_job+1 }}.{{ index_product+1 }} -->
                                    {{ product.name }}
                                </label>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

</template>

<script>
    import PlusMinus from '../vue-snippets/PlusMinus'

     export default {
         components: {
             PlusMinus
         },

         data () {
             return {
                 showed: 1,
                 search: '',
                 is_mobile: window.isMobile()
             }
         },

         methods: {
             changeProductField (direction, type_job) {
                 if(!direction.checked) {
                     direction.checked = 1
                 }

                 if(!type_job.checked) {
                     type_job.checked = 1
                 }

                 this.calculate()
             }
         },

         props: [
             'directions', 'products', 'type_jobs', 'changeFieldValue', 'calculate'
         ]
     }
</script>

<style>
    .tav-select,
    .tav-select * {
        user-select: none;
    }

    .tav-select__root {
        width: 100%;
        padding-top: 4px;
        border: 1px solid #e9e9e9;
        max-height: 400px;
        overflow-y: auto;
    }

    .tav-select__item {
        margin-bottom: 5px;
        padding-left: 15px;
        transition: .5s ease;
    }

    .tav-select__item--notpm {
        padding-left: 25px;
    }

    .tav-select__item:hover {
        box-shadow: 0px 2px 10px rgba(0,0,0,.05)
    }

    .tav-select__label {
        margin-bottom: 0.2rem;
        font-size: 13px;
    }

    .tav-select__notpm {

    }

    .tav-select__search {
        width: 100%;
        font-size: 14px;
        border: 1px solid #e9e9e9;
        border-bottom: none;
        cursor: pointer;
        pointer-events: none;
        padding-left: 10px;
    }
</style>
