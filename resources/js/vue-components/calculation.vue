<template>

    <div class="row">
        <div class="col-lg-1"></div>
        <!-- Анкета -->
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body calculation-container">

                    <div class="loader" v-if="anketa_id && !loaded">
                        {{ text_loading }}
                    </div>

                    <div v-if="calcHasSaved" class="text-center">
                        <h2 class="text-success">Калькуляция сохранена в Реестр</h2>

                        <p>
                            <a :href="'/anketa/print/'+calc.anketa_id+'/1?triggerPrint=1'" target="_blank">Скачать PDF-расчета</a>
                        </p>

                        <div>
                            <p>
                                <a href="" class="btn btn-primary">Сделать новую калькуляцию</a>

                                <a
                                   :href="`/anketa/print/${calc.anketa_id}/1?sendMailPdf=1`"
                                   @click.prevent="sendToProtokolPdf('mail')"
                                   class="btn btn-primary">Отправить протокол на почту</a>
                            </p>

                            <p>Отправьте Клиенту на согласование</p>
                            <form @submit.prevent="sendToProtokolPdf('client')" :action="`/anketa/print/${calc.anketa_id}/1`" method="GET">
                                <input type="hidden" name="sendMailPdf" value="1">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <input type="tel" v-model="pdfData.phone" placeholder="Телефон (+7__________)" class="form-control MASK_PHONE" name="phone">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <input type="email" v-model="pdfData.email" placeholder="E-mail" class="form-control" name="email">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <input type="text" v-model="pdfData.fio" placeholder="ФИО Клиента" class="form-control" name="fio">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <input type="text" v-model="pdfData.company" data-field="Company_name" placeholder="Наименование компании" class="form-control" name="company">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-info">Отправить клиенту</button>
                            </form>

                            <hr>

                            <form v-if="!blockedTemplate" @submit.prevent="saveTemplate" method="POST">
                                <div class="form-group">
                                    <input type="text" placeholder="Введите имя шаблона" class="form-control" name="template" />
                                </div>

                                <button type="submit" class="btn btn-info">Сохранить шаблон</button>
                            </form>
                        </div>
                    </div>

                    <h3 v-if="!calcHasSaved" class="text-center">1. {{ title }}</h3>

                    <p class="text-center" v-if="user_name">
                        Создатель: {{ user_name }}
                    </p>

                    <hr>
                    <article v-if="!calcHasSaved" class="anketa anketa-fields">

                        <form @submit.prevent="onSubmit" :action="action" class="calculate">
                            <input type="hidden" name="type_anketa" :value="type_anketa" />

                            <div class="form-group row" v-if="!anketa_id && step >= 0">
                                <label class="col-md-3 form-control-label"><b>Выберите ваш шаблон</b></label>

                                <article class="col-md-9">
                                    <select @change="selectTemplate" class="form-control">
                                        <option value="" selected>Без шаблона</option>
                                        <option v-if="templates.length" v-for="template in templates" :value="template.id">{{ template.name }}</option>
                                    </select>
                                </article>
                            </div>

                            <div class="form-group row" v-if="anketa_id || step >= 0">
                                <label class="col-md-3 form-control-label">ID сделки:</label>
                                <article class="col-md-9">
                                    <input type="number" step="1" v-model="calc.id_deal" name="id_deal" class="form-control">
                                </article>
                            </div>

                            <div class="form-group row" v-if="anketa_id || step >= 0">
                                <label class="col-md-3 form-control-label">Дополнительные опции:</label>
                                <article class="col-md-9">
                                    <label>
                                        <input type="checkbox" v-model="calc.nds" name="nds"> Не учитывать НДС
                                    </label>
                                    <br/>
                                    <label>
                                        <input type="checkbox" v-model="calc.only_total_price_show" name="only_total_price_show"> Отображать разбивку стоимости
                                    </label>
                                </article>
                            </div>

                            <div class="cloning" id="cloning-first">
                                <div v-if="anketa_id || step >= 0" class="form-group row">
                                    <label class="col-md-3 form-control-label">Дата:</label>
                                    <article class="col-md-9">
                                        <input type="date" v-model="calc.date" name="date" class="form-control">
                                    </article>
                                </div>

                                <div v-if="anketa_id || step >= 0" class="form-group row">
                                    <label class="col-md-3 form-control-label">Адрес объекта:</label>
                                    <article class="col-md-9">
                                        <input type="text" v-model="calc.address" name="address" class="form-control">
                                    </article>
                                </div>

                                <div v-if="anketa_id || step >= 0" class="form-group row">
                                    <label class="col-md-3 form-control-label">Площадь объекта, м: <span class="text-red">*</span></label>
                                    <article class="col-md-9">
                                        <input
                                            :id="Math.random()"
                                            class="form-control"
                                            type="number"
                                            step="1"
                                            v-model="calc.squere"
                                            @input="changeFieldValue($event, '')">
                                    </article>
                                </div>

                                <div v-if="anketa_id || step >= 0" class="form-group row">
                                    <label class="col-md-3 form-control-label">Назначение объекта:</label>
                                    <article class="col-md-9">
                                        <calc-select
                                            :data="fields.objects_id.options"
                                            field="objects_id"
                                            :inputTrigger="calculate"
                                            trackBy="name">
                                        </calc-select>
                                    </article>
                                </div>

                                <div v-if="anketa_id || step >= 0" class="form-group row">
                                    <label class="col-md-3 form-control-label">Конструктивная система:</label>
                                    <article class="col-md-9">
                                        <calc-select
                                            :data="fields.construct_systems_id.options"
                                            field="construct_systems_id"
                                            :inputTrigger="calculate"
                                            trackBy="name">
                                        </calc-select>
                                    </article>
                                </div>

                                <div v-if="anketa_id || step >= 0" class="form-group row">
                                    <label class="col-md-3 form-control-label">Усложняющие факторы:</label>
                                    <article class="col-md-9">
                                        <calc-select
                                            :data="fields.lvls_id.options"
                                            field="lvls_id"
                                            :inputTrigger="calculate"
                                            trackBy="name">
                                        </calc-select>
                                    </article>
                                </div>

                                <div v-if="anketa_id || step >= 0" class="form-group row">
                                    <label class="col-md-3 form-control-label">Высота здания, м:</label>
                                    <article class="col-md-9">
                                        <input
                                            :id="Math.random()"
                                            class="form-control"
                                            type="number"
                                            step="0.1"
                                            v-model="calc.height"
                                            @input="changeFieldValue($event, '')">
                                    </article>
                                </div>

                                <div v-if="anketa_id || step >= 0" class="form-group row">
                                    <label class="col-md-3 form-control-label">Число этажей (вкл. подвал):</label>
                                    <article class="col-md-9">
                                        <input
                                            :id="Math.random()"
                                            class="form-control"
                                            type="number"
                                            step="1"
                                            v-model="calc.count_floors"
                                            @input="changeFieldValue($event, '')">
                                    </article>
                                </div>

                                <div v-if="anketa_id || step >= 0" class="form-group row">
                                    <label class="col-md-3 form-control-label">Максимальная высота этажа, м:</label>
                                    <article class="col-md-9">
                                        <input
                                            :id="Math.random()"
                                            class="form-control"
                                            type="number"
                                            step="0.1"
                                            v-model="calc.max_height_floor"
                                            @input="changeFieldValue($event, '')">
                                    </article>
                                </div>

                                <div v-if="anketa_id || step >= 0" class="form-group row">
                                    <label class="col-md-3 form-control-label">Количество зданий:</label>
                                    <article class="col-md-9">
                                        <input
                                            :id="Math.random()"
                                            class="form-control"
                                            type="number"
                                            step="1"
                                            v-model="calc.count_buildings"
                                            @input="changeFieldValue($event, '')">
                                    </article>
                                </div>

                                <hr>
                                <h3 v-if="step >= 1" class="text-center">2. Формирование ведомости объемов работ</h3>

                                <div v-if="anketa_id || step >= 1" class="form-group row">
                                    <label class="col-md-3 form-control-label">Выбор направлений: <span class="text-red">*</span></label>
                                    <article class="col-md-9">

                                        <tav-select
                                            :changeFieldValue="changeFieldValue"
                                            :directions="fields.directions_id"
                                            :type_jobs="fields.type_jobs_id"
                                            :products="fields.products_id"
                                            :calculate="calculate"
                                        ></tav-select>

                                    </article>
                                </div>

                            </div>

                            <div v-if="!showResult" class="form-group">
                                <button v-if="step >= 2" type="submit" class="m-center btn btn-success">Рассчитать</button>
                                <button v-else @click="changeStep" type="button" class="m-center btn btn-success">Далее</button>
                            </div>

                            <div v-if="showResult">
                                <hr>
                                <h3 v-if="step >= 2" class="text-center">3. Настройка калькуляции</h3>

                                <CalculationResult
                                    :sortElement="sortElement"
                                    :squere="calc.squere"
                                    :calc="calc"
                                    :firstCalc="firstCalc"
                                    :updateProductField="updateProductField"
                                    :calculate="calculate"
                                    :print="0"
                                ></CalculationResult>

                                <div class="form-group row">
                                    <button :disabled="savedDoubleCalculate && !anketa_id" @click.prevent="saveReg" type="button" class="m-center btn btn-success">

                                        <template v-if="!anketa_id">
                                            <span v-if="!savedDoubleCalculate">Записать калькуляцию</span>
                                                <span v-else>
                                                <i class="fa fa-check"></i> Успешно записано
                                            </span>
                                        </template>
                                        <template v-else>
                                            Сохранить
                                        </template>

                                    </button>
                                </div>

                            </div>

                        </form>

                    </article>

                </div>
            </div>
        </div>

        <div class="col-lg-1"></div>
    </div>

</template>

<script>
    import Multiselect from 'vue-multiselect'
    import CalcSelect from './calc-select'
    import TavSelect from './tav-select.vue'
    import CalculationResult from './calculation-result'

    export default {
        components: {
            Multiselect, CalculationResult, TavSelect, CalcSelect
        },

        data () {
            return {
                NumIndicators: [],
                templates: [],

                step: 0,
                stepsTitle: [
                    'Ввод данных об объекте',
                    'Формирование ведомости объемов работ',
                    'Настройка калькуляции'
                ],

                pdfData: {
                    fio: '',
                    company: '',
                    phone: '',
                    email: ''
                },

                user_name: null,

                blockedTemplate: 0,
                text_loading: 'Загрузка калькуляции...',
                calcHasSaved: 0,
                loaded: false,
                firstCalc: null,

                USER_ROLE: window.USER_ROLE,

                calc: {
                    only_total_price_show: 0,
                    nds: 0,
                    fixed_round: 2,
                    items: [],
                    price_total: 0,
                    sum_kd: 0,
                    sum_ks: 0,
                    squere: '',
                    anketa_id: 0,
                    height: '',
                    ks: 1, kd: 1,
                    count_floors: '',
                    max_height_floor: '',
                    count_buildings: '',
                    id_deal: '',
                    address: '',
                    date: new Date().getFullYear() + '-' + (new Date().getMonth()+1 < 10 ? '0' + (new Date().getMonth()+1) : new Date().getMonth()+1) + '-' + (new Date().getDate() < 10 ? '0' + new Date().getDate() : new Date().getDate()),
                    name_job: ''
                },

                savedDoubleCalculate: false,
                showResult: 0,
                showProducts: 0,

                fields: {
                    objects_id: { value: [], options: [] },
                    construct_systems_id: { value: [], options: [] },
                    lvls_id: { value: [], options: [] },

                    directions_id: { value: [], options: [] },
                    type_jobs_id: { value: [], options: [] },
                    products_id: { value: [], options: [] },
                },

                customProductsCount: {},
                customRecommendKs: {}
            }
        },

        props: [
            'type_anketa', 'action', 'title', 'anketa_id'
        ],

        methods: {
            changeStep () {
                if(this.step < 2) {
                    this.step = this.step+1
                }
            },

            onSelect(option) {
                console.log('Added');
                option.checked = true;
                console.log(`${option.name}  Clicked!! ${option.checked}`);
            },

            sortElement (list, index, action) {
                let listData = eval('this.calc' + list)

                let currentIndex = index+action
                    currentIndex = currentIndex <= 0 ? 0 : (currentIndex+1 > listData.length ? listData.length-1 : currentIndex)

                eval('this.calc' + list)[index] = listData.splice(currentIndex, 1, listData[index])[0];

                this.calc.items[0] = this.calc.items.splice(0,1, this.calc.items[0])[0]
            },

            onRemove(option) {
                console.log('Removed');
                option.checked = false;
                console.log(`${option.name}  Removed!! ${option.checked}`);
            },

            async changeFieldValue (event, field) {
                switch(field) {
                    case 'directions_id':

                        let type_jobs = this.fields.directions_id.options.map(item => item.id).join(',')
                            type_jobs = await API.getFieldsModel({
                            model: 'TypeJob', search: `?direction_id=${type_jobs}`
                        })

                        if(!this.fields.type_jobs_id.options.length)
                            this.fields.type_jobs_id.options = type_jobs

                        if(!event.checked) {
                            this.fields.type_jobs_id.options.forEach((tj, tj_index) => {
                                if(tj.direction_id == event.id) {
                                    this.fields.type_jobs_id.options[tj_index].checked = false

                                    this.fields.products_id.options.forEach((pr, i) => {
                                        if(pr.type_job_id == tj.id) {
                                            this.fields.products_id.options[i].checked = false
                                        }
                                    })
                                }
                            })
                        }

                        break;

                    case 'type_jobs_id':

                        let products = this.fields.type_jobs_id.options.map(item => item.id).join(',')
                            products = await API.getFieldsModel({
                                model: 'Product', search: `?type_job_id=${products}`
                            })

                        if(products instanceof Array) {
                            if(!this.fields.products_id.options.length)
                                this.fields.products_id.options = products
                        }

                        if(!event.checked) {
                            this.fields.products_id.options.forEach((pr, i) => {
                                if(pr.type_job_id == event.id) {
                                    this.fields.products_id.options[i].checked = false
                                }
                            })
                        }

                        break;
                }

                this.calculate()
            },

            async getFieldsValue () {
                this.fields.directions_id.options = await API.getFieldsModel({ model: 'Direction' });
                this.fields.type_jobs_id.options = await API.getFieldsModel({ model: 'TypeJob' });
                this.fields.products_id.options = await API.getFieldsModel({ model: 'Product' });

                this.fields.lvls_id.options = await API.getFieldsModel({ model: 'Lvl' });
                this.fields.construct_systems_id.options = await API.getFieldsModel({ model: 'ConstructSystem' });
                this.fields.objects_id.options = await API.getFieldsModel({ model: 'Obj' });
            },

            async saveDoubleCalc (isUpdateAnketaId = 0) {
                let dataSelects = this.getDataSelects()

                this.calc.items.forEach(dir => {
                    dir.type_jobs.forEach(tj => {
                        tj.products.forEach(pr => {

                            let data = {
                                model: 'ListKs',
                                anketa_id: this.calc.anketa_id,
                                product_id: pr.id,
                                types: tj.name,
                                product_name: pr.name,
                                count_indicator: pr.count,
                                ks_natur: pr.edin_rascenka_ks(),
                                height: this.calc.height,
                                count_floors: this.calc.count_floors,
                                max_height_floor: this.calc.max_height_floor,
                                count_buildings: this.calc.count_buildings,
                                ...dataSelects
                            }

                            if(isUpdateAnketaId) {
                                API.editListKs(data).then(response => {

                                    if(!response.success) {
                                        API.addElement(data).then(() => {
                                            console.log('Added Listks from EditKs')
                                            this.savedDoubleCalculate = true
                                        })
                                    } else {
                                        console.log('Edited element', response)

                                        this.savedDoubleCalculate = true
                                    }
                                })
                            } else {
                                API.addElement(data).then(response => {
                                    console.log('Added Listks')
                                    this.savedDoubleCalculate = true
                                })
                            }

                        })
                    })
                })
            },

            getDataSelects () {
                let data = {}

                data.directions = this.fields.directions_id.options.filter(item => item.checked).map(item => {
                    return item.name
                }).join(',')

                data.types = this.fields.type_jobs_id.options.filter(item => item.checked).map(item => {
                    return item.name
                }).join(',')

                data.products = this.fields.products_id.options.filter(item => item.checked).map(item => {
                    return item.name
                }).join(',')

                data.name_job = this.fields.objects_id.options.filter(item => item.checked).map(item => {
                    return item.name
                }).join(',')

                data.lvl = this.fields.lvls_id.value.map(item => {
                    return item.name
                }).join(',')

                data.construct_system = this.fields.construct_systems_id.value.map(item => {
                    return item.name
                }).join(',')

                return data
            },

            async sendToProtokolPdf (type) {
                if(type === 'client'){
                    if(!confirm('Отправить калькуляцию клиенту?')) return false;
                }else if(type === 'mail'){
                    if(!confirm('Отправить калькуляцию на почту?')) return false;
                }

                await API.savePdfProtokol('', this.anketa_id, this.pdfData)

                swal({
                    icon: 'info',
                    title: 'Калькуляция отправлена на почту'
                })
            },

            async saveReg () {
                let data = this.calc

                data = Object.assign(data, this.getDataSelects())

                data.kd_final = data.kd
                data.type_anketa = this.type_anketa

                // let full_data = JSON.stringify(this.$data)

                // data.full_data = full_data
                // data.json_calc = JSON.stringify(data)
                data.full_data = 'qwerty'
                data.json_calc = 'qwerty'

                data.sum_ks = (typeof data.sum_kd === "function" ? data.sum_ks() : data.sum_ks)
                data.sum_kd = (typeof data.sum_kd === "function" ? data.sum_kd() : data.sum_kd)

                if(this.anketa_id) {
                    data.anketa_id = this.anketa_id
                }

                if(!this.calc.deadline) {
                    return swal({
                        text: 'Заполните поле Срок выполнения работ',
                        icon: 'warning'
                    })
                }

                let savedData = this.anketa_id ? await API.saveCalc(data) : await API.saveCalc(data)

                if(savedData.success) {
                    let calcHasSaved = this.anketa_id

                    if(calcHasSaved)
                        swal({
                            icon: 'info',
                            title: 'Калькуляция сохранена в реестр'
                        })

                    this.calc.anketa_id = this.anketa_id ? this.anketa_id : savedData.success.id

                    this.calcHasSaved = 1

                    setTimeout(() => {
                        window.updateMasks()
                    }, 1000)
                }

                // TODO: доделать после тго как поправим редактирование Ks
                await this.saveDoubleCalc(this.anketa_id)
            },

            setAllSelected (field, deselect = 0) {
                 if(this.fields[field]) {
                     this.fields[field].value = []

                     if(!deselect) this.fields[field].options.forEach(item => {
                         this.fields[field].value.push(item)
                     })
                 }
            },

            updateProductField ({ product_id, field, newValue }) {
                this.customProductsCount[product_id] = {
                    [field]: newValue
                }

                this.calculate()
            },

            calculate (findRecommendKs = true) {
                if(this.showResult) {
                    // Обнуляем расчет
                    this.calc.items = []
                    this.calc.price_total = 0
                    this.calc.sum_kd = 0
                    this.calc.sum_ks = 0

                    this.calc = Object.assign(this.calc, this.getDataSelects())

                    let dirs = this.fields.directions_id.options.filter(item => item.checked),
                        type_jobs = this.fields.type_jobs_id.options.filter(item => item.checked),
                        products = this.fields.products_id.options.filter(item => item.checked)

                    for(let i = 0; i < dirs.length; i++) {
                        let item = {}

                        item.direction = dirs[i]
                        item.index = this.fields.directions_id.options.indexOf(dirs[i]) + 1

                        item.type_jobs = type_jobs.filter(tj => tj.direction_id.split(',').includes(dirs[i].id + ''))

                        item.type_jobs = item.type_jobs.map((tj, tj_index) => {
                            tj.index = this.fields.type_jobs_id.options.filter(tjj => tjj.direction_id.split(',').includes(dirs[i].id + '')).indexOf(tj) + 1

                            let newProducts = products.slice(0).filter(pr => {
                                return pr.type_job_id.split(',').includes(tj.id + '')
                            })

                            tj.products = newProducts.map((pr, pr_index) => {
                                pr.index = this.fields.products_id.options.filter(prr => prr.type_job_id.split(',').includes(tj.id + '')).indexOf(pr) + 1
                                pr.unique_id = Math.random()

                                let numIndicator = this.getNumIndicator(pr.num_indicator_id, 'name').toUpperCase(),
                                    numIndicatorUnit = this.getNumIndicator(pr.num_indicator_id, 'unit').toUpperCase(),
                                    customCoundId = `product${pr.id}_${tj.id}_${item.direction.id}`

                                // Количество (площадь) - если ранее поле не было даобавлено
                                if(!this.customProductsCount[customCoundId]) {

                                    /**
                                     * Зависящие от ЧП виды
                                     */

                                    if(numIndicatorUnit == 'М2') {
                                        pr.count = this.calc.squere /// pr.norm_numeric_coef_product
                                    } else if(numIndicatorUnit == 'ШТ' && numIndicator.indexOf('МИНИМАЛЬНОЕ КОЛИЧЕСТВО') == -1) {
                                        pr.count = this.calc.squere * pr.norm_numeric_coef_product
                                    } else {
                                        pr.count = pr.norm_numeric_coef_product
                                    }

                                } else {
                                    // Иначе мы просто вставляем то что ранее было введено
                                    pr.count = this.customProductsCount[customCoundId]['count']
                                }

                                pr.count = isFloat(pr.count) ? Number(pr.count.toFixed(this.calc.fixed_round)) : pr.count

                                /**
                                 * Если НЕ площадь
                                 */
                                if((numIndicatorUnit !== 'М2' && numIndicatorUnit !== 'ГА') && pr.count < 1) {
                                    pr.count = 1
                                    pr.min = 1
                                } else {
                                    pr.min = 0
                                }

                                /**
                                 * Проверка МИН.количественног опоказателя ВИДА
                                 */
                                // Если КОЛИЧЕСТВО
                                if(numIndicatorUnit == 'ШТ' && numIndicator.indexOf('МИНИМАЛЬНОЕ КОЛИЧЕСТВО') == -1 && pr.min_norm_numeric_coef_product) {
                                    pr.min = pr.min_norm_numeric_coef_product

                                    if(pr.count < pr.min_norm_numeric_coef_product) {
                                        pr.count = pr.min_norm_numeric_coef_product
                                    }
                                }

                                /**
                                 * Округление
                                 */
                                if(this.getNumIndicator(pr.num_indicator_id, 'round_ceil')) {
                                    pr.count = isFloat(pr.count) ? Math.round(pr.count) : pr.count;
                                }

                                /**
                                 * 1. Нормативный численный показатель, привязан к площади:
                                 Если это м2 - мы его игнорируем — где м2 - там ед.расценку умножаем на площадь, без норм числ пок
                                 1.1. Для всего остального, площадь делим на норм числ пок и будет требуемое количество для данного вида.

                                 pr - объект Вида
                                 count - площадь
                                 edin_rascenka - едииничная расценка
                                 norm_numeric_coef_product - норм.числ.показатель
                                 */

                                if(findRecommendKs && !this.anketa_id) {
                                    this.getRecommendKs(pr, { index_product: pr_index, index_type_job: tj_index, index: i })
                                        .then(_ks => {
                                            let recommendKsKey = `${i}_${tj_index}_${pr_index}`

                                            pr.recommend_ks = this.customRecommendKs[recommendKsKey] ? this.customRecommendKs[recommendKsKey] : ''
                                            pr.edin_rascenka = _ks ? _ks : pr.edin_rascenka

                                            try {
                                                this.calc.items[i].type_jobs[tj_index].products[pr_index].edin_rascenka = _ks ? _ks : pr.edin_rascenka
                                            } catch(e) {}
                                            //this.customRecommendKs[recommendKsKey] ? this.customRecommendKs[recommendKsKey] : pr.edin_rascenka
                                            this.calculate(false)
                                        })
                                }

                                pr.weight = () => {
                                    return Number((pr.price / this.calc.price_total).toFixed(2))
                                }

                                pr.dolya_ks = () => {
                                    return Number(((this.calc.ks - 1) * pr.weight() + 1).toFixed(2))
                                }

                                pr.edin_rascenka_ks = () => {
                                    let _edin_rascenka_ks = Number((pr.edin_rascenka * pr.dolya_ks()))

                                    if(_edin_rascenka_ks < pr.edin_rascenka) {
                                        let razn = _edin_rascenka_ks*100/pr.edin_rascenka,
                                            percentData = (pr.edin_rascenka/10)

                                        if(razn <= 10) {
                                            return percentData
                                        }
                                    }

                                    return _edin_rascenka_ks.toFixed(2)
                                } // един.расценка с кс

                                pr.price = pr.count * pr.edin_rascenka
                                pr.price = isFloat(pr.price) ? Number(pr.price.toFixed(2)) : pr.price

                                pr.price_ks = () => {
                                    let _price = (pr.count * pr.edin_rascenka_ks())

                                    return isFloat(_price) ? Number(_price.toFixed(2)) : _price
                                }

                                pr.price_kd = () => {
                                    let _price = pr.price_ks()*this.calc.kd

                                    return isFloat(_price) ? Number(_price.toFixed(2)) : _price
                                }

                                return Object.assign({}, pr)
                            })

                            // Итого по типу работ
                            tj.price_total = _.sumBy(tj.products, pr => pr.price)

                            tj.price_total_ks = () => {
                                return _.sumBy(tj.products, pr => pr.price_ks())
                            }

                            tj.price_total_kd = () => {
                                return _.sumBy(tj.products, pr => pr.price_kd())
                            }

                            tj.weight_total = () => {
                                return _.sumBy(tj.products, pr => pr.weight())
                            }

                            return tj
                        })

                        // Итого по направлению
                        item.price_total = _.sumBy(item.type_jobs, tj => tj.price_total)

                        item.price_total_ks = () => {
                            return _.sumBy(item.type_jobs, tj => tj.price_total_ks())
                        }

                        item.price_total_kd = () => {
                            return _.sumBy(item.type_jobs, tj => tj.price_total_kd())
                        }

                        item.weight_total = () => {
                            return _.sumBy(item.type_jobs, tj => tj.weight_total())
                        }

                        this.calc.items.push(item)
                    }

                    // Итого по разделам
                    this.calc.price_total = _.sumBy(this.calc.items, item => item.price_total)

                    this.calc.sum_ks = () => {
                        return _.sumBy(this.calc.items, item => item.price_total_ks())
                    }
                    //this.calc.sum_ks = Number((this.calc.price_total * this.calc.ks).toFixed(2))
                    //this.calc.sum_kd = Number((this.calc.sum_ks * this.calc.kd).toFixed(2))
                    this.calc.sum_kd = () => {
                        return _.sumBy(this.calc.items, item => item.price_total_kd())
                    }

                    this.calc.weight_total = () => {
                        return _.sumBy(this.calc.items, item => item.weight_total())
                    }

                    if(!this.firstCalc) {
                        this.firstCalc = this.calc
                    }
                }
            },

            getRecommendKs (pr, { index_product, index_type_job, index }) {
                if(pr.count !== null && pr.id !== null) {
                    return API.getRecommendKs({ count: pr.count, id: pr.id }).then(data => {
                        if(data) {
                            this.customRecommendKs[`${index}_${index_type_job}_${index_product}`] = data

                            return data
                        } else {
                            pr.recommend_ks = ''

                            return ''
                        }
                    })
                }
            },

            onSubmit () {
                if(!this.fields.directions_id.options.filter(item => item.checked).length) {
                    swal({
                        text: 'Заполните поле Направления',
                        icon: 'warning'
                    })
                } else if(!this.calc.squere) {
                    swal({
                        text: 'Заполните поле Площадь',
                        icon: 'warning'
                    })
                } else {
                    this.calculate()

                    this.showResult = 1
                }
            },

            selectTemplate (event) {
                if(this.templates.length) {
                    let val = event.target.value, template = this.templates.filter(item => item.id == val)

                    if(template.length) {
                        template = template[0]
                        template = JSON.parse(template.data)

                        this.fields = template.fields
                        this.calc.items = template.items
                    }
                }
            },

            saveTemplate (event) {
                let template = event.target.template.value, items = this.calc

                items.fields = this.fields

                API.addElement({
                    model: 'Template',
                    name: template,
                    data: JSON.stringify(items)
                }).then(response => {
                    if(response) {
                        swal({
                            icon: 'info',
                            title: 'Шаблон сохранён'
                        })

                        this.blockedTemplate = 1
                    }
                })
            },

            getNumIndicator (id, prop = 'unit') {
                let ni = this.NumIndicators.filter(item => item.id == id)

                if(ni.length) {
                    return ni[0][prop]
                }

                return ''
            }
        },

        async mounted () {
            this.NumIndicators = await API.getFieldsModel({
                model: 'NumIndicator'
            })

            this.templates = await API.getFieldsModel({
                model: 'Template'
            })

            await this.getFieldsValue()

            if(this.anketa_id) {
                let anketa = await API.getForm({ anketa_id: this.anketa_id })

                let user_name = anketa.user_name

                if(! (anketa.full_data.indexOf('calc/') > -1) ) {
                    anketa = JSON.parse(anketa.full_data)

                    for(let i in anketa) {
                        if(this[i] !== null || this[i] !== undefined) {
                            this[i] = anketa[i]
                        }
                    }

                    this.calc.ks = 1;
                    this.ks = 1;

                    setTimeout(() => {
                        this.calculate()
                    }, 1000)

                    this.loaded = 1

                    setTimeout(() => {
                        this.calculate()
                    }, 1000)

                    this.user_name = user_name
                } else {
                    this.text_loading = 'Калькуляция не загружена. Обратитесь к Администратору.'
                }
            }
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped>
    .loader {
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,.75);
        top: 0;
        left: 0;
        color: #000;
        display: flex;
        box-sizing: border-box;
        padding-top: 100px;
        z-index: 9999;
        font-size: 20px;
        justify-content: center;
        align-items: flex-start;
    }

    .calculation-container {
        position: relative;
    }
</style>
