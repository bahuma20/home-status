import Temperature from "./slides/temperature";
import Tasks from "./slides/tasks";
import Helpers from "./Helpers";
import Photo from "./slides/photo";
import Alerts from "./overlays/Alerts";
import Realtime from "./Realtime";
import Clock from "./overlays/Clock";
import moment from "moment";
import Api from "./Api";

export default class App {
    realtime;
    api;
    slides = [];
    stage = null;

    constructor() {
        if (this.checkTokenSet()) {
            this.api = new Api();

            this.createSlides();

            this.init();
        }
    }

    checkTokenSet() {
        const url = new URL(window.location);

        if (!url.searchParams.has('token')) {
            document.querySelector('body').prepend(Helpers.createElementFromHTML('<h1 style="background: red; color: #000; position: absolute; top: 0;left: 0;right: 0;bottom: 0;z-index: 499;">The query parameter "token" is missing. Please provide the API_TOKEN set in the backend.</h1>'))
            return false;
        }

        return true;
    }

    createSlides() {
        this.slides.push(new Temperature(this.api, 'aqara_bad_temperature'));
        this.slides.push(new Temperature(this.api, 'aqara_wohnzimmer_temperature'));
        this.slides.push(new Temperature(this.api, 'aqara_kueche_temperature'));
        this.slides.push(new Temperature(this.api, 'aqara_schlafzimmer_temperature'));
        this.slides.push(new Temperature(this.api, 'aqara_buero_temperature'));
        this.slides.push(new Temperature(this.api, 'tado_ankleideraum_temperature'));
        this.slides.push(new Temperature(this.api, 'aqara_speisekammer_temperature'));
        this.slides.push(new Temperature(this.api, 'aqara_balkon_temperature'));
        this.slides.push(new Tasks(this.api));
        // this.slides.push(new Test(this.api));

        for (let i = 0; i < 10; i++) {
            this.slides.push(new Photo(this.api));
        }
    }

    async init() {
        this.realtime = new Realtime(this.api);

        this.stage = document.querySelector('.stage');
        let scale = document.querySelector('body').offsetWidth / 1024;
        this.stage.setAttribute('style', `transform: scale(${scale})`);

        await this.addOverlays();

        let slide1 = Helpers.randomItem(this.slides);
        await slide1.load();
        this.stage.appendChild(Helpers.createElementFromHTML(`<div class="slide slide--active slide--type--${slide1.type}" style="background-color: ${slide1.getBackgroundColor()}">${slide1.render()}</div>`));
        await this.loadNextSlide();

        setInterval(() => {
            this.nextSlide();
        }, 30000);

        this.setupMomentFromNow();
    }

    async addOverlays() {
        const overlaysContainer = this.stage.appendChild(Helpers.createElementFromHTML(`<div class="overlays"></div>`));

        const overlays = {
            clock: new Clock(),
            alerts: new Alerts(this.realtime, this.api),
        };

        Object.keys(overlays).forEach(key => {
            const overlayParent = overlaysContainer.appendChild(Helpers.createElementFromHTML(`<div class="overlay-${key}">`));
            overlays[key].init(overlayParent);
        });
    }

    async loadNextSlide() {
        let slide = Helpers.randomItem(this.slides);

        try {
            await slide.load();
            this.stage.appendChild(Helpers.createElementFromHTML(`<div class="slide slide--next slide--type--${slide.type}" style="background-color: ${slide.getBackgroundColor()}">${slide.render()}</div>`))
        } catch (e) {
            this.stage.appendChild(Helpers.createElementFromHTML(`<div class="slide slide--next slide--type--error" style="background-color: indianred; padding: 20px">An error occured while loading a <em>${slide.type}</em> slide:<br><br><pre>${e.message}</pre><pre>${e.stack}</pre></div>`));
        }
    }

    nextSlide() {
        let activeSlide = document.querySelector('.slide--active')
        let nextSlide = document.querySelector('.slide--next');

        activeSlide.classList.add('slide--slideout');
        activeSlide.classList.remove('slide--active')
        nextSlide.classList.add('slide--active')
        nextSlide.classList.remove('slide--next');

        setTimeout(() => {
            activeSlide.remove();
        }, 400);


        this.loadNextSlide();
    }

    setupMomentFromNow() {
        setInterval(() => {
            document.querySelectorAll('.moment-from-now').forEach(element => {
                element.textContent = moment(element.dataset.date).fromNow();
            });
        }, 10000);
    }
}
