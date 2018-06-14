let loadersArrCounter = [];
let tiid = null;

const http = {
    methods: {

        showLoader: function () {

            loadersArrCounter.push(1);

            $('.spin-loader').removeClass('hidden');
        },

        hideLoader: function () {

            loadersArrCounter.shift();

            if(tiid) {
                clearTimeout(tiid);
            }

            tiid = setTimeout(()=>{
                if(!loadersArrCounter.length) {
                    $('.spin-loader').addClass('hidden');
                }
            },300)
        },

        httpGet: function (url) {
            this.addAuthHeader();
            this.showLoader();
            return axios.get(url)
                .then(data => {
                    this.hideLoader();
                    return data.data;
                })
                .catch(err => {
                    this.hideLoader();
                    console.log('Handled Error ', err.response);
                    throw err.response;
                })
        },

        httpPost: function (url, data) {
            this.addAuthHeader();
            this.showLoader();
            return axios.post(url, data)
                .then(data => {
                    this.hideLoader();
                    return data.data;
                })
                .catch(err => {
                    this.hideLoader();
                    console.log('Handled Error ', err.response);
                    throw err.response;
                })
        },

        httpPut: function (url, data) {
            this.addAuthHeader();
            this.showLoader();
            return axios.put(url, data)
                .then(data => {
                    this.hideLoader();
                    return data.data;
                })
                .catch(err => {
                    this.hideLoader();
                    console.log('Handled Error ', err.response);
                    throw err.response;
                })
        },

        httpDelete: function (url) {
            this.addAuthHeader();
            this.showLoader();
            return axios.delete(url)
                .then(data => {
                    this.hideLoader();
                    return data.data;
                })
                .catch(err => {
                    this.hideLoader();
                    console.log('Handled Error ', err.response);
                    throw err.response;
                })
        },


        httpPostFile: function(url, fileToUpload, fileKeyName) {
            this.addAuthHeader();

            const formData = new FormData();

            formData.append(fileKeyName || 'file', fileToUpload, fileToUpload.name);

            return axios.post(url, formData)
                .then(data => {
                    this.hideLoader();
                    return data.data;
                })
                .catch(err => {
                    this.hideLoader();
                    console.log('Handled Error ', err.response);
                    throw err.response;
                })
        },

        addAuthHeader: function () {
            if(localStorage.hasOwnProperty('auth-token'))
            {
                axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('auth-token');
            }
            else {
                delete axios.defaults.headers.common['Authorization'];
            }
        }
    }
};


 export default http;