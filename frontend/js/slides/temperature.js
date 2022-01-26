import Helpers from "../Helpers";
import Slide from "../Slide";

export default class Temperature extends Slide {
    constructor(entity_id, name) {
        super();
        this.type = 'temperature'
        this.data = null;
        this.entity_id = entity_id
        this.name = name;
    }

    async load() {
        this.data = await Helpers.getData(`states/sensor.${this.entity_id}`);
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
          <div class="temperature__name">${this.name}</div>
          <div class="temperature__value">${Math.round(parseFloat(this.data.state))} °C</div>
        `;
    }
}

