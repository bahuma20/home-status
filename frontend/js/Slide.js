import Helpers from "./Helpers";

export default class Slide {

    constructor() {
        this.type = null;
        this.backgroundColor = null; // If null, it will get a random one.

        this.backgroundColors = [
            '#FFE082',
            '#dcedc8',
            '#b2dfdb',
            '#bcaaa4',
            '#bbdefb',
            '#c5cae9',
            '#ffcdd2',
            '#e1bee7',
            '#ffccbc',
            '#cfd8dc',
            '#e0f7fa',
        ]
    }

    render() {

    }

    getBackgroundColor() {
        if (this.backgroundColor) {
            return this.backgroundColor;
        }

        return Helpers.randomItem(this.backgroundColors);
    }

    async load() {

    }
}

