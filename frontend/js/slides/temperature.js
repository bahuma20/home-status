import Slide from "../Slide";

export default class Temperature extends Slide {
    constructor(entity_id) {
        super();
        this.type = 'temperature'
        this.data = null;
        this.entity_id = entity_id
    }

    async load() {
        let response = await fetch(`/api/ha/temperature/${this.entity_id}`);
        this.data = await response.json();
    }

    render() {
        return `
          <lottie-player
            src="https://assets8.lottiefiles.com/private_files/lf30_wBcF24.json"
            background="transparent"
            speed="0.5"
            style="width: 300px; height: 300px;"
            loop
            autoplay
            class="temperature__image"></lottie-player>
          <div class="temperature__name">${this.data.name}</div>
          <div class="temperature__value">${Math.round(parseFloat(this.data.temperature))} °C</div>
        `;
    }
}

