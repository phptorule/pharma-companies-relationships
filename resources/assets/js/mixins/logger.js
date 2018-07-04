import http from './http.js';

const logger = {
    mixins: [http],
    methods: {
        logData: _.debounce(function (componentName, componentAction, payload) {
            let url = '/api/logger/log-data';
            this.httpPost(url, {
                'componentName': componentName,
                'componentAction': componentAction,
                'payload': payload
            });
        }, 400)
    }

}

export default logger;