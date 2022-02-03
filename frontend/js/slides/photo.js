import Slide from "../Slide";
import Environment from "../Environment";

export default class Photo extends Slide {
    constructor() {
        super();
        this.type = 'photo'
        this.data = null;
    }

    async load() {
        const response = await fetch(Environment.apiBaseUrl + 'api/photos', {
            method: 'GET',
        });
        this.data = await response.json();
    }

    render() {
        let imageUrl = this.data.baseUrl + '=w1920-h1125-c';

        let date = new Date(this.data.mediaMetadata.creationTime);

        let now = new Date();

        let difference = (now - date) / 1000;

        let value;
        let unit;

        if (difference < 60) {
            value = difference;
            unit = 'second';
        } else if (difference < 60 * 60) {
            value = Math.round(difference/60);
            unit = 'minute';
        } else if (difference < 60 * 60 * 24) {
            value = Math.round(difference/60/60);
            unit = 'hour';
        } else if (difference < 60 * 60 * 24 * 7) {
            value = Math.round(difference/60/60/24);
            unit = 'day';
        } else if (difference < 60 * 60 * 24 * 30) {
            value = Math.round(difference/60/60/24/7);
            unit = 'week';
        } else if (difference < 60 * 60 * 24 * 365) {
            value = Math.round(difference/60/60/24/30);
            unit = 'month';
        } else {
            value = Math.round(difference/60/60/24/365);
            unit = 'year';
        }

        const rtf = new Intl.RelativeTimeFormat('de', {
            numeric: 'auto',
        });

        let dateText = this.data.homeAppType === 'people' ? rtf.format(0 - value, unit) : '';

        return `
            <img class="photo__image" src="${imageUrl}">
            <div class="photo__shadow"></div>
            <div class="photo__date">${dateText}</div>
        `;
    }
}

