import Temperature from "./slides/temperature";
import Tasks from "./slides/tasks";
import Helpers from "./Helpers";
import Photo from "./slides/photo";
import Alerts from "./overlays/Alerts";
import Realtime from "./Realtime";
import Clock from "./overlays/Clock";
import moment from "moment";
import Test from "./slides/test";

export default class App {
    realtime;
    slides = [];
    stage = null;

    constructor() {
        this.createSlides();

        this.init()
    }

    createSlides() {
        this.slides.push(new Temperature('aqara_bad_temperature'));
        this.slides.push(new Temperature('aqara_wohnzimmer_temperature'));
        this.slides.push(new Temperature('aqara_kueche_temperature'));
        this.slides.push(new Temperature('aqara_schlafzimmer_temperature'));
        this.slides.push(new Temperature('aqara_buero_temperature'));
        this.slides.push(new Temperature('tado_ankleideraum_temperature'));
        this.slides.push(new Temperature('aqara_speisekammer_temperature'));
        this.slides.push(new Temperature('aqara_balkon_temperature'));
        this.slides.push(new Tasks());
        this.slides.push(new Tasks());
        this.slides.push(new Test());

        for (let i = 0; i < 10; i++) {
            this.slides.push(new Photo());
        }
    }

    async init() {
        this.realtime = new Realtime();

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
            alerts: new Alerts(this.realtime),
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
            this.stage.appendChild(Helpers.createElementFromHTML(`<div class="slide slide--next slide--type--error" style="background-color: indianred; padding: 20px">An error occured while loading a <em>${slide.constructor.name}</em> slide:<br><br><pre>${e.message}</pre><pre>${e.stack}</pre></div>`));
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
