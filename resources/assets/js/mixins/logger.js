import http from './http.js';

const logger = {
    mixins: [http],
    methods: {
        logData: function (componentName, componentAction, userId, payload) {
            let url = '/api/logger/log-data';
            this.httpPost(url, {
                'componentName': componentName,
                'componentAction': componentAction
            });
            console.log('123123');
        }
    }

}

export default logger;