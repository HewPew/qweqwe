<template>
    <div class="calculation-result">
        <p><b>Назначение объекта - {{ calc.name_job }}</b></p>
        <p v-if="calc.id_deal">ID сделки: {{ calc.id_deal }}</p>
        <p>Дата: {{ calc.date }}</p>
        <p v-if="calc.address">Адрес объекта: {{ calc.address }}</p>
        <p>Площадь объекта (м2): {{ calc.squere }}</p>

        <p v-if="calc.height">Высота здания, м: {{ calc.height }}</p>
        <p v-if="calc.count_floors">Число этажей, включая подвал: {{ calc.count_floors }}</p>
        <p v-if="calc.max_height_floor">Максимальная высота этажа, м: {{ calc.max_height_floor }}</p>
        <p v-if="calc.count_buildings">Количество зданий: {{ calc.count_buildings }}</p>

        <!--<p v-if="!is_print">
            <span><i>Количество чисел после запятой (округление)</i></span>
            <br>
            <input min="0" type="number" @input="calculate" v-model="calc.fixed_round" />
        </p>-->

        <br/><br/>

        <h3>Состав работ</h3>
        <div class="calculation-result__table">
            <table :style="{ 'min-width': is_print ? 'auto' : '1450px' }" class="table table-bordered">
                <thead>
                <tr>
                    <th>Направление деятельности</th>
                    <th v-if="!is_print">Ед. измерения</th>
                    <th v-if="!is_print">Кол-во</th>
                    <th v-if="!is_print">Ед.расценка</th>

                    <template v-if="!is_print">
                        <th style="min-width: 200px;">Стоимость, руб.<br/>(без учета НДС)</th>
                        <th v-if="USER_ROLE >= 777 && !not_show_double_calc">Мин. стоимость<br/>(без учета НДС)</th>
                    </template>

<!--                    <th v-if="USER_ROLE >= 777 && (!not_show_double_calc || is_print)">
                        <span v-if="is_print"></span>&lt;!&ndash; Стоимость, руб.<br/>(без учета НДС) &ndash;&gt;
                        <span v-else>Стоимость для КП<br/>(без учета НДС)</span>
                    </th>-->

                    <!-- expert, admin -->
                    <template v-if="USER_ROLE >= 777 && !not_show_double_calc">
                        <th>Вес работы</th>
                        <th>Доля ks</th>
                        <th>Ед.расц с Ks</th>
                    </template>
                </tr>
                </thead>

                <tbody>

                <template v-if="calc.items.length">

                    <template v-for="(item, direction_index) in calc.items" v-if="checkProductsInTypeJobs(item.type_jobs)">
                        <tr>
                            <td :colspan="is_print ? (send_mail_pdf ? 1 : 2) : 10">
                                <sorter
                                    v-if="!is_print"
                                    :methodAction="sortElement"
                                    list="['items']"
                                    :index="direction_index"
                                ></sorter>

                                <b class="mb-0">{{ direction_index+1 }}. {{ item.direction.name }}</b>
                            </td>
                        </tr>

                        <template v-for="(type_job, type_job_index) in item.type_jobs" v-if="type_job.products.length">
                            <tr>
                                <td :colspan="is_print ? (send_mail_pdf ? 1 : 2) : 10">
                                    &nbsp;&nbsp;

                                    <sorter
                                        v-if="!is_print"
                                        :methodAction="sortElement"
                                        :list="`['items'][${direction_index}]['type_jobs']`"
                                        :index="type_job_index"
                                    ></sorter>

                                    <b>{{ direction_index+1 }}.{{ type_job_index+1 }}. {{ type_job.name }}</b>
                                </td>
                            </tr>

                            <tr v-for="(product, product_index) in type_job.products">
                                <td>
                                    &nbsp;&nbsp;&nbsp;&nbsp;

                                    <sorter
                                        v-if="!is_print"
                                        :methodAction="sortElement"
                                        :list="`['items'][${direction_index}]['type_jobs'][${type_job_index}]['products']`"
                                        :index="product_index"
                                    ></sorter>

                                    {{ direction_index+1 }}.{{ type_job_index+1 }}.{{ product_index+1 }}. {{ product.name }}
                                </td>
                                <td v-if="is_print">{{ product.price }}</td>
                                <template v-if="!is_print">
                                    <td>{{ getNumIndicator(product.num_indicator_id) }}</td>
                                    <td>
                                        <span v-if="is_print">
                                            {{ showCop(product.count) }}
                                        </span>
                                        <span v-else>
                                            <vue-numeric-input :id="Math.random()" @input="updateProductField({
                                                product_id: `product${product.id}_${type_job.id}_${item.direction.id}`,
                                                field: 'count', newValue: $event
                                            })" :min="product.min" :value="product.count" :step="getNumIndicator(product.num_indicator_id, 'round_ceil') ? 1 : 0.1"></vue-numeric-input>
                                        </span>
                                    </td>

                                    <td>{{ product.edin_rascenka }}</td>
                                    <td>{{ showCop(product.price) }}</td>
                                    <td>{{ showCop(showDataFunction(product, 'price_ks')) }}</td>
                                </template>

<!--                                <td>{{ (!is_print || calc.only_total_price_show) ? showCop(showDataFunction(product, 'price_kd')) : '' }}</td>-->

                                <template v-if="USER_ROLE >= 777 && !not_show_double_calc">
                                    <td>{{ showDataFunction(product, 'weight') }}</td>
                                    <td>{{ showDataFunction(product, 'dolya_ks') }}</td>
                                    <td>{{ showDataFunction(product, 'edin_rascenka_ks') }}</td>
                                </template>

                            </tr>

                            <tr v-if="calc.only_total_price_show">
                                <td :colspan="!is_print ? 4 : 1" class="text-right">Итого по типу:</td>

                                <template v-if="!is_print">
                                    <td>{{ showCop(type_job.price_total) }}</td>
                                    <td>{{ showCop(showDataFunction(type_job, 'price_total_ks')) }}</td>
                                </template>

<!--                                <td>{{ showCop(showDataFunction(type_job, 'price_total_kd')) }}</td>-->

                                <td v-if="is_print" colspan="3">{{ item.price_total }}</td>
                            </tr>
                        </template>

                        <tr v-if="calc.only_total_price_show">
                            <td :colspan="!is_print ? 4 : 1" class="text-right">Итого по направлению:</td>

                            <template v-if="!is_print">
                                <td>{{ showCop(item.price_total) }}</td>
                                <td>{{ showCop(showDataFunction(item, 'price_total_ks')) }}</td>
                            </template>

<!--                            <td>{{ showCop(showDataFunction(item, 'price_total_kd')) }}</td>-->
                            <td v-if="is_print" colspan="3">{{ item.price_total }}</td>
                        </tr>
                    </template>

                    <tr style="background-color: #f6fff1;">
                        <td :colspan="!is_print ? 4 : 1" class="text-right"><b>Общая стоимость работ (руб, без НДС):</b></td>

                        <td v-if="!is_print">
                            {{ this.calc.price_total }}
                        </td>

                        <td v-if="!is_print">
                            {{ showCop(showDataFunction(calc, 'sum_ks')) }}

                            <div class="calculation-result__ks" v-if="!not_show_double_calc">
                                <b>Мин.стоимость</b>
                                <vue-numeric-input :id="Math.random()" v-if="!is_print" @input="calculate" v-model="calc.ks" :step="0.01"></vue-numeric-input>
                                <span v-if="is_print">{{ calc.ks }}</span>
                            </div>
                        </td>

                        <td v-if="is_print">
                            {{ this.calc.price_total }}
                        </td>

                        <td v-if="!is_print" colspan="3"></td>

                    </tr>

                    <tr v-if="is_print && !calc.nds" style="background-color: #f6fff1;">
                        <td :colspan="!is_print ? 4 : 1" class="text-right"><b>Сумма НДС (20%, руб):</b></td>
                        <td>{{ Math.round(this.calc.sum_kd * 0.2 * 100) / 100 }}</td>
                    </tr>

                    <tr v-if="is_print && !calc.nds" style="background-color: #f6fff1;">
                        <td :colspan="!is_print ? 4 : 1" class="text-right"><b>Итого с НДС (руб):</b></td>
                        <td>{{ Math.round(this.calc.sum_kd * 1.2 * 100) / 100  }}</td>
                    </tr>

                    <tr style="background-color: #f6fff1;">
                        <td :colspan="!is_print ? 4 : 1" class="text-right"><b>Срок выполнения работ (раб.дн):</b></td>
                        <td>
                            <input v-if="!is_print" required v-model="calc.deadline" type="number" name="deadline" />
                            <span v-else>{{ calc.deadline }}</span>
                        </td>
                    </tr>

                    <!-- expert, admin -->
                    <!--                    <template v-if="USER_ROLE >= 777 && !not_show_double_calc">
                                            <tr style="background-color: #daf7de;">
                                                <td colspan="4" class="text-right">
                                                    Сумма с Ks
                                                </td>
                                                <td>{{ showCop(calc.sum_ks) }}</td>
                                                <td>1</td> &lt;!&ndash; {{ showDataFunction(calc, 'weight_total') }} &ndash;&gt;
                                                <td colspan="3">
                                                </td>
                                            </tr>
                                        </template>-->

                </template>

                </tbody>
            </table>
        </div>

    </div>
</template>

<script>
    import sorter from './Sorter'

    export default {
        name: "calculation-result.vue",

        components: {
            sorter
        },

        props: [
            'directions', 'type_jobs', 'products', 'squere', 'calc',
            'updateProductField', 'is_print', 'calculate',
            'not_show_double_calc', 'getRecommendKs', 'send_mail_pdf', 'anketa_id',
            'sortElement',
            'phone', 'email', 'fio', 'company'
        ],

        data () {
            return {
                NumIndicators: [],
                USER_ROLE: window.USER_ROLE
            }
        },

        methods: {
            getNumIndicator (id, field = 'unit') {
                let ni = this.NumIndicators.filter(item => item.id == id)

                if(ni.length) {
                    return ni[0][field]
                }

                return ''
            },

            checkProductsInTypeJobs (tj_array) {
                let checkdata = tj_array.filter(tj => {
                    return tj.products.length
                })

                return checkdata.length
            },

            numberWithSpaces (x) {
                return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
            },

            showCop (number) {
                number = (typeof number == "string" ? Number(number.replace(/ /g, '')) : number)

                if(!isFloat(number)) {
                    number = this.numberWithSpaces(number)

                    return number + '.00';
                } else {
                    number = this.numberWithSpaces(number.toFixed(2))

                    return number
                }
            },

            showDataFunction (data, prop) {
                if(data[prop] && !this.is_print) {
                    if(typeof data[prop] == "function") {
                        data[prop + '_print'] = data[prop]()

                        return data[prop]()
                    } else {
                        data[prop + '_print'] = data[prop]

                        return data[prop]
                    }
                } else if(this.is_print) {
                    return data[prop + '_print']
                }

                return ''
            }
        },

        async mounted () {
            if(this.calculate) {
                this.calculate()
            }

            this.NumIndicators = await API.getFieldsModel({
                model: 'NumIndicator'
            })

            if(this.send_mail_pdf && this.anketa_id) {
                let savePdfRequest = await API.savePdfProtokol($('.ANKETA_PRINT').html(), this.anketa_id, {
                    phone: this.phone,
                    email: this.email,
                    fio: this.fio,
                    company: this.company
                })

                if(savePdfRequest) {
                    window.open(savePdfRequest, '_blank').focus()
                }
            }
        }
    }
</script>

<style scoped>
    .search-rks {
        display: block;
    }

    .calculation-result {
        font-size: 13px;
    }

    .calculation-result__table {
        max-height: 650px;
        overflow-y: auto;
    }

    .calculation-result__table table thead {
        position: sticky;
        top: 0;
        z-index: 999;
        background: white;
        box-shadow: 0px 0px 2px 1px #e9e9e9;
    }
</style>
