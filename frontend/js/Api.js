import Environment from "./Environment";

export default class Api {
    baseUrl;
    token;

    constructor() {
        this.baseUrl = Environment.apiBaseUrl;
        const currentUrl = new URL(window.location);
        this.token = currentUrl.searchParams.get('token');
    }

    fetch(path, options = {}) {
        const url = new URL(this.baseUrl + path);
        url.searchParams.set('token', this.token);

        return fetch(url.toString(), options);
    }
}
