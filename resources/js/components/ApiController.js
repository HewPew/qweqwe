import axios from 'axios'

export class ApiController {
    constructor () {
        this.client = axios.create({
            headers: {
                Authorization: 'Bearer ' + API_TOKEN
            }
        })
    }

    saveCalc (data) {
        return this.client.post('/api/anketa', {
            ...data
        }).then(response => {
            return response.data
        })
    }

    savePdfProtokol (protokolHtml, id, { phone, email, fio, company }) {
        return this.client.post('/anketa/save-pdf/' + id, {
            protokolHtml, phone, email, fio, company
        }).then(response => {
            return response.data
        })
    }

    sendSmsCode (to, code) {
        return this.client.post('/send-sms-code', {
            to, code
        }).then(response => {
            return response.data
        })
    }

    updateCalc (data) {
        return this.client.put('/api/anketa/' + data.anketa_id, {
            ...data
        }).then(response => {
            return response.data
        })
    }

    getForm ({ anketa_id }) {
        return this.client.get('/api/anketa/' + anketa_id).then(response => {
            return response.data
        })
    }

    getRecommendKs ({ count = 0, id }) {
        return this.client.get(`/api/recommend-ks/${count}/${id}`).then(response => {
            return response.data
        })
    }

    addElement (data) {
        return this.client.post('/api/elements/' + data.model, {
            ...data
        }).then(response => {
            return response.data
        })
    }

    editListKs (data) {
        return this.client.put(`/api/edit-listks/${data.anketa_id}/${data.product_id}/${data.types}`, {
            ...data
        }).then(response => {
            return response.data
        })
    }

    getFieldsModel ({ model, search = '' }) {
        return this.client.get('/api/fields/' + model + search).then(response => {
            return response.data
        })
    }

    updateModelProperty ({ item_model, item_id, item_field, new_value }) {
        return this.client.put(`/api/update-ddate/${item_model}/${item_id}/${item_field}`, { new_value }).then(response => {
            const data = response.data

            if(data.success)
                alert('Данные успешно обновлены!')
        })
    }

    getFieldHTML ({ field, model, default_value }) {
        return this.client.get(`/api/getField/${model}/${field}/${default_value}`);
    }
}
