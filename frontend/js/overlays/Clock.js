import Overlay from "../Overlay";
import moment from "moment";

export default class Clock extends Overlay {
    interval;

    init(parentElement) {
        super.init(parentElement);

        this.updateClock();

        this.interval = setInterval(() => {
            this.updateClock();
        }, 60000);
    }

    updateClock() {
        this.parentElement.textContent = moment().format('HH:mm');
    }
}
