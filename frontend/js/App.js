import Temperature from "./slides/temperature";
import Tasks from "./slides/tasks";
import Helpers from "./Helpers";
import Photo from "./slides/photo";
import moment from "moment";

export default class App {
    constructor() {
        this.slides = [];
        this.stage = null;

        this.createSlides();

        this.init()
    } 

    createSlides() {
        this.slides.push(new Temperature('xiaomi_arbeitsbereich_temperature', 'Arbeitsbereich'));
        this.slides.push(new Temperature('xiaomi_schlafzimmer_temperature', 'Schlafzimmer'));
        this.slides.push(new Temperature('xiaomi_wohnzimmer_temperature', 'Wohnbereich'));
        this.slides.push(new Temperature('bewegungsmelder_bad_temperature', 'Bad'));
        this.slides.push(new Temperature('weather_home_temperature', 'Außen'));
        this.slides.push(new Tasks());

        for (let i = 0; i < 10; i++) {
            this.slides.push(new Photo());
        }
    }

    async init() {
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
    }

    async addOverlays() {
        this.stage.appendChild(Helpers.createElementFromHTML(`<div class="overlays">
          <div class="overlay-clock"></div>
        </div>`));

        let updateClock = () => {
            this.stage.querySelector('.overlay-clock').textContent = moment().format('HH:mm');
        }

        updateClock();
        setInterval(updateClock, 60000)
    }

    async loadNextSlide() {
        let slide = Helpers.randomItem(this.slides);

        await slide.load();

        this.stage.appendChild(Helpers.createElementFromHTML(`<div class="slide slide--next slide--type--${slide.type}" style="background-color: ${slide.getBackgroundColor()}">${slide.render()}</div>`))
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
}
