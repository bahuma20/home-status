export default class Realtime {
    baseUrl = null;
    api;

    constructor(api) {
        this.api = api;
    }

    async subscribe(topics) {
        if (!this.baseUrl) {
            this.baseUrl = await this.getSubscriptionBaseUrl();
        }

        const url = new URL(this.baseUrl);

        topics.forEach(topic => {
            url.searchParams.append('topic', 'alerts');
        })

        return new EventSource(url);
    }

    async getSubscriptionBaseUrl() {
        const response = await this.api.fetch('api/realtime/subscription-url');
        const data = await response.json();
        return data.url;
    }
}
