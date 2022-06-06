import axios from 'axios'
import swal from 'sweetalert'
import {ApiController} from "./components/ApiController"

require('./init-plugins')
require('chosen-js')
require('croppie')
require('suggestions-jquery')
require('file-saverjs')
require('blobjs')

$(document).ready(function () {
    const API_CONTROLLER = new ApiController(),
        API = API_CONTROLLER.client

    window.API_CONTROLLER = API_CONTROLLER;

    const LIBS = {
        initChosen () {
            $('.js-chosen').chosen({
                width: '100%',
                search_contains: true,
                no_results_text: 'Совпадений не найдено',
                placeholder_text_single: 'Выберите значение',
                placeholder_text_multiple: 'Выберите значения'
            });
        },

        initExports () {
            /*let tableDataExport = TableExport(document.querySelectorAll('#elements-table, #ankets-table'), {
                formats: ['xlsx'],
                position: 'top'
            })*/
        },

        initAll () {
            LIBS.initChosen()
            LIBS.initExports()
        }
    }

    function initCroppies ()
    {
        let Croppies = {}

        $('.croppie-demo').each(function () {
            let id = $(this).data('croppieId')

            Croppies[id] = $(this).croppie({
                enableOrientation: true,
                viewport: {
                    width: 170,
                    height: 170,
                    type: 'circle' // or 'square'
                },
                boundary: {
                    width: 200,
                    height: 200
                }
            })
        })

        $('[id*="croppie-input"]').on('change', function () {
            let reader = new FileReader(), croppId = this.id.replace('croppie-input', '')

            reader.onload = function (e) {
                $('#croppie-block' + croppId).show()

                Croppies[croppId].croppie('bind', {
                    url: e.target.result
                });
            }

            reader.readAsDataURL(this.files[0]);

            this.files = null
            this.value = ''
        });

        $('.croppie-save').click(function () {
            let id = $(this).data('croppieId')

            Croppies[id].croppie('result', {
                type: 'canvas',
                size: 'viewport'
            }).then(image => {
                $('#croppie-block' + id).hide()

                // success message
                swal({
                    title: 'Изображение обрезано',
                    icon: 'info'
                });

                $(`#croppie-result-base64${id}`).val(image)
            })

        })
    }
    initCroppies()

    // Подгрузка в полей в Журналах: CHOSEN
    $.get(location.search ? location.href+'&getFormFilter=1' : '?getFormFilter=1').done(response => {
        if(response) {
            $('#filter-groupsContent').html(response)
            LIBS.initChosen()
        }
    });

    $(window).on('load', function () {
        // Форма поиска по анкетам
        function anketsFormCheckFieldsToShow ()
        {
            const showTableData = el => {
                if(el) {
                    let index = Number(el.attr('data-value')) + 1, prop_checked = el.prop('checked');

                    let anketsTable = $(`.ankets-table thead th:nth-child(${index}), .ankets-table tbody tr td:nth-child(${index})`),
                        displayProp = (!prop_checked ? 'none' : 'table-cell')

                    anketsTable.attr('hidden', !prop_checked).css({'display': displayProp })
                } else {
                    $('.ankets-form input').each(function () {
                        let $t = $(this)

                        if(this.name !== '_token') {
                            showTableData($t)
                        }

                    })
                }
            }

            $('.ankets-form input').change(e => {
                let el = $(e.target)

                showTableData(el)
            })

            showTableData()
        }

        anketsFormCheckFieldsToShow()
    })


    /**
     * Показываем данные в сущностях в карточках Анкеты
     * @param model
     * @param data
     */
    const showAnketsCardDBitemData = async (model, data, fieldsValues) => {
        if(data) {
            let dbItemId = 'CARD_' + model.toUpperCase(), msg = '',
                inputClass = model + '_' + 'input'

            $(`#${dbItemId}`).html('<b class="text-info">Загружаем данные...</b>')

            for(let i in data) {
                let fvItem = fieldsValues[i]

                if(fvItem) {
                   if(i != 'id' && i != 'hash_id') {
                       let field = ''

                       if(fvItem['type'] == 'select') {
                            await API_CONTROLLER.getFieldHTML({ field: i, model, default_value: data[i] }).then(response => {
                                field = response.data
                            })
                        } else {
                            field = `<input data-model="${model}" class="form-control" type="${fvItem['type']}" value='${data[i] ? data[i] : ''}' name="${i}" /> `
                        }

                        msg += `
                            <p class="text-small m-0">${fvItem.label}:
                                <div class="form-group ${inputClass}">
                                    ${field}
                                </div>
                            </p>`

                    }
                }
            }

            $(`#${dbItemId}`).html(msg)

            LIBS.initChosen()

            $(`.${inputClass} input, .${inputClass} select`).change(function () {
                let val = this.value, name = this.name

                if(confirm('Сохранить?')) {
                    API_CONTROLLER.updateModelProperty({
                        item_model: model,
                        item_field: name,
                        item_id: data['id'],
                        new_value: val
                    }).then(response => {
                        console.log(response.data)
                    })
                }
            })


        }
    }

    // Проверка свойства по модели на бэкенда
    window.checkInputProp = (prop = '0', model = '0', val = '0', label, parent) => {
        const PARENT_ELEM = parent;

        $.ajax({
            url: `/api/check-prop/${prop}/${model}/${val}`,
            headers: {'Authorization': 'Bearer ' + API_TOKEN},
            success:  (data) => {
                const PROP_HAS_EXISTS = data.data.exists
                const DATA = data.data.message;

                console.log(data.data)

                showAnketsCardDBitemData(model, DATA, data.data.fieldsValues)

                if(PARENT_ELEM.length) {
                    const APP_CHECKER_PARENT = PARENT_ELEM.find('.app-checker-prop')

                    if(PROP_HAS_EXISTS){
                        APP_CHECKER_PARENT.removeClass('text-danger').addClass('text-success').text(DATA[label])
                    } else {
                        PARENT_ELEM.find('input, textarea, select').val('');
                        APP_CHECKER_PARENT.removeClass('text-success').addClass('text-danger').text(`Не найдено`)
                    }
                }

                if(!!DATA.company_id) {
                    checkInputProp('id', 'Company', DATA.company_id, 'name', [])
                }

                return
            }
        });
    }

    window.addFieldToHistory = (value, field) => {
        API.post('/api/field-history', {
            value, field
        })
    }

    // ЭКСПОРТ таблицы в xlsx
    window.exportTable = function(table) {
        table = document.getElementById(table)
        table = table.cloneNode(true)

        $(table).find('.not-export, .modal').remove()
        $(table).find('.to-export').css({ 'display': 'inline-block' })

        table = table.innerHTML

        let matchZap = table.match(/[0-9]\./g)

        if(matchZap !== null) {
            matchZap.forEach(matchItem => {
                let matchItemOld = matchItem
                matchItem = matchItem.replace('.', ',')

                table = table.replace(matchItemOld, matchItem)
            })
        }

        table = table.replace(/<(\/*)a[^>]*>/g, '<span>').replace('</a>', '</span>')

        var uri = 'data:application/vnd.ms-excel;base64,',
            template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--> <meta charset="utf-8" /></head><body><table>{table}</table></body></html>',
            base64 = function(s) {
                return window.btoa(unescape(encodeURIComponent(s)))
            },
            format = function(s, c) {
                return s.replace(/{(\w+)}/g, function(m, p) {
                    return c[p];
                })
            }
        var toExcel = table;
        var ctx = {
            worksheet: name || '',
            table: toExcel
        };
        var link = document.createElement("a");
        link.download = "export.xls";
        link.href = uri + base64(format(template, ctx))
        link.setAttribute('target', '_blank')
        link.click();
    };

    // Открытие/закртие элементов
    $('[data-toggle-show]').click(function (e) {
        e.preventDefault()

        let $this = $(this), el = $($this.data('toggle-show')), title = $this.find('.toggle-title'),
            hiddenClass = 'toggle-hidden', titleData = 'Показать'

        if(el.length && title.length) {

            titleData = (el.hasClass(hiddenClass)) ? 'Скрыть' : titleData

            title.text(titleData)
            el.toggleClass(hiddenClass);
        }
    })

    $('.field').each(function () {
        let $t = $(this),
            $i = $t.find('> i'),
            $input = $t.find('> input')

        if($t.hasClass('field--password')) {
            $i.click(function () {
                if($(this).hasClass('fa-eye')) {
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash')
                    $input.attr('type', 'password')
                } else {
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye')
                    $input.attr('type', 'text')
                }
            })
        }
    })

    // ЭКШЕНЫ
    $('*[class*="ACTION_"]').click(function (e) {
        e.preventDefault();

        let confirms = {
            'ACTION_DELETE': 'Точно хотите удалить?'
        }

        this.classList.forEach(cls=> {
            if(confirms[cls])
                if(confirm(confirms[cls]))
                    location.href = this.href;
        })
    })

    $('.TRIGGER_CLICK').trigger('click');

    // Отправка данных с форма по CTRL + ENTER
    $('.anketa-fields input, .anketa-fields textarea').keydown(e => {
        if(e.ctrlKey & e.keyCode == 13) {
            $('.anketa-fields form').trigger('submit');
        }
    })


    /**
     * API:
     * ИЗМЕНЕНИЕ ПОЛЕЙ НА BACKEND
     */
    $('.JS_CHANGE_FIELD_MODEL').click(function (e) {
        e.preventDefault()

        let url = this.href, field = $(this).data('field')

        field = $(field)

        if(field.length) {
            let new_value = field.val()

            API.put(url.replace(location.origin, ''), { new_value }).then(response => {
                const data = response.data

                if(data.success)
                    alert('Данные успешно обновлены!')
            })
        }
    })

    // ------------------------------------------------------- //
    // Card Close
    // ------------------------------------------------------ //
    $('.card-close a.remove').on('click', function (e) {
        e.preventDefault();
        $(this).parents('.card').fadeOut();
    });

    // ------------------------------------------------------- //
    // Tooltips init
    // ------------------------------------------------------ //

    $('[data-toggle="tooltip"]').tooltip()

    // ------------------------------------------------------- //
    // Adding fade effect to dropdowns
    // ------------------------------------------------------ //
    $('.dropdown').on('show.bs.dropdown', function () {
        $(this).find('.dropdown-menu').first().stop(true, true).fadeIn();
    });
    $('.dropdown').on('hide.bs.dropdown', function () {
        $(this).find('.dropdown-menu').first().stop(true, true).fadeOut();
    });

    // ------------------------------------------------------- //
    // Sidebar Functionality
    // ------------------------------------------------------ //
    $('#toggle-btn').on('click', function (e) {
        e.preventDefault();
        $(this).toggleClass('active');

        $('.side-navbar').toggleClass('shrinked');
        $('.content-inner').toggleClass('active');
        $(document).trigger('sidebarChanged');

        if ($(window).outerWidth() > 1183) {
            if ($('#toggle-btn').hasClass('active')) {
                $('.navbar-header .brand-small').hide();
                $('.navbar-header .brand-big').show();
            } else {
                $('.navbar-header .brand-small').show();
                $('.navbar-header .brand-big').hide();
            }
        }

        if ($(window).outerWidth() < 1183) {
            $('.navbar-header .brand-small').show();
        }
    })

    // Меню (dropdown)
    $('[data-btn-collapse]').click(function (e) {
        e.preventDefault()

        let t = $(this), menu = $(t.data('btn-collapse')), clsCollapse = 'collapse'

        t.attr('aria-expanded', menu.hasClass(clsCollapse))

        if(menu.length) {
            menu.toggleClass(clsCollapse)
        }
    })

    // Проверяем ссылки в меню
    $('a[data-btn-collapse] + ul a').each(function () {
        let $href = this.href.replace(location.origin, '')

        if(location.pathname.indexOf($href) > -1 || $href.indexOf(location.pathname) > -1) {
            let $parent = $(this).parent().parent()

            if(!$parent.prev().data('clicked')) {
                $parent.prev().attr('data-clicked', 1)
                $parent.prev().trigger('click')
            }
        }
    })

    // ------------------------------------------------------- //
    // Material Inputs
    // ------------------------------------------------------ //

    var materialInputs = $('input.input-material');

    // activate labels for prefilled values
    materialInputs.filter(function() { return $(this).val() !== ""; }).siblings('.label-material').addClass('active');

    // move label on focus
    materialInputs.on('focus', function () {
        $(this).siblings('.label-material').addClass('active');
    });

    // remove/keep label on blur
    materialInputs.on('blur', function () {
        $(this).siblings('.label-material').removeClass('active');

        if ($(this).val() !== '') {
            $(this).siblings('.label-material').addClass('active');
        } else {
            $(this).siblings('.label-material').removeClass('active');
        }
    });

    // ------------------------------------------------------- //
    // Footer
    // ------------------------------------------------------ //

    var contentInner = $('.content-inner');

    $(document).on('sidebarChanged', function () {
        adjustFooter();
    });

    $(window).on('resize', function () {
        adjustFooter();
    })

    function adjustFooter() {
        var footerBlockHeight = $('.main-footer').outerHeight();
        contentInner.css('padding-bottom', footerBlockHeight + 'px');
    }

    // ------------------------------------------------------- //
    // External links to new window
    // ------------------------------------------------------ //
    $('.external').on('click', function (e) {

        e.preventDefault();
        window.open($(this).attr("href"));
    })

    $('*[data-field="Company_name"]').suggestions({
        token: "4de76a04c285fbbad3b2dc7bcaa3ad39233d4300",
        type: "PARTY",
        /* Вызывается, когда пользователь выбирает одну из подсказок */
        onSelect: function(suggestion) {
            if(suggestion.data) {
                //const { inn } = suggestion.data

                //$('#elements-modal-add input[name="inn"]').val(inn)
            }
        }
    });

    function initSmsFormCheck () {
        $('form.SMS_FORM_CHECK').each(function () {
            let sms_code = '',
                $t = $(this),
                $checker = $t.find('.SMS_FORM_CHECKER'),
                inputSms = () => {
                    return $t.find('.SMS_FORM_CHECKER input').val()
                },
                $phone = () => {
                    return this.phone.value
                },
                $btnSubmit = $(this).find('[type="submit"]')

            let generateSms = () => {
                $checker.show()

                sms_code = Math.floor(100000 + Math.random() * 900000);

                console.log(sms_code)

                API_CONTROLLER.sendSmsCode($phone(), sms_code);

                $btnSubmit.attr('disabled', 'disabled')
            }

            $t.find('.SMS_FORM_CHECK_BTN').click(function (e) {
                e.preventDefault()

                if(inputSms() == sms_code) {
                    $checker.hide()
                    $btnSubmit.removeAttr('disabled')
                    $t.data('submit', 1).trigger('submit')
                } else {
                    alert('Код неверный! Попробуйте еще раз')
                }
            })

            $t.submit(function (e) {
                let dataSubmit = $(this).data('submit')

                if(dataSubmit === 0) {
                    e.preventDefault()

                    generateSms()

                    return false
                }
            })
        })
    }

    $('.header #toggle-btn').each(function () {
        let localStatusSidebar = () => {
                return localStorage.getItem('sidebar')
            },
            localStatusSet = status => {
                localStorage.setItem('sidebar', status)
            }

        if(localStatusSidebar() === "0") {
            $(this).trigger('click')
        }

        $(this).click(function () {
            if($(this).hasClass('active')) {
                localStatusSet(1)
            } else {
                localStatusSet(0)
            }
        })
    })

    initSmsFormCheck()

    LIBS.initAll()

});
