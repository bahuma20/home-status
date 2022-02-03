import Overlay from "../Overlay";
import Helpers from "../Helpers";
import moment from "moment";
import Environment from "../Environment";

export default class Alerts extends Overlay {
    realtime;
    eventSource;
    alerts = [];

    constructor(realtime) {
        super();
        this.realtime = realtime;
    }

    async init(parentElement) {
        super.init(parentElement);

        await this.loadAlerts();

        this.renderInitial();

        this.setupRealtime();
    }

    async setupRealtime() {
        this.eventSource = await this.realtime.subscribe(['alerts']);

        this.eventSource.onmessage = event => {
            const newAlerts = JSON.parse(event.data);
            const diff = this.compareAlerts(JSON.parse(JSON.stringify(this.alerts)), JSON.parse(JSON.stringify(newAlerts)));
            this.alerts = newAlerts;

            console.log(diff);

            diff.added.forEach(alert => {
                this.addAlert(alert);
            });

            diff.removed.forEach(alert => this.removeAlert(alert));
        }
    }

    renderInitial() {
        this.alerts.forEach(alert => this.parentElement.appendChild(this.renderAlert(alert)));
    }

    async loadAlerts() {
        const response = await fetch(Environment.apiBaseUrl + 'api/alerts');
        this.alerts = await response.json();
    }

    compareAlerts(orig, modified) {
        const origIds = orig.map(alert => alert.id);
        const modifiedIds = modified.map(alert => alert.id);

        let removedAlerts = origIds.filter(origId => modifiedIds.indexOf(origId) === -1);
        let addedAlerts = modifiedIds.filter(modifiedId => origIds.indexOf(modifiedId) === -1);

        removedAlerts = removedAlerts.map(id => orig.find(item => item.id === id));
        addedAlerts = addedAlerts.map(id => modified.find(item => item.id === id));

        return {
            added: addedAlerts,
            removed: removedAlerts
        }
    }

    addAlert(alert) {
        this.parentElement.append(this.renderAlert(alert));

        // TODO: Stinger animation
        // TODO: different priority
        // TODO: Sorting by priority
        // TODO: Play "pling" sound
        // TODO: Show icon
    }

    removeAlert(alert) {
        const alertElement = this.parentElement.querySelector(`.alert[data-id="${alert.id}"]`);

        alertElement.animate([
            {height: alertElement.offsetHeight + 'px'},
            {height: 0, transform: 'translateY(-10px)'}
        ], {
            duration: 500,
        });

        alertElement.classList.add('is-vanish');

        Promise.all(alertElement.getAnimations().filter(animation => {
            return animation instanceof CSSTransition;
        }).map(animation => animation.finished))
            .then(() => {
                alertElement.remove();
            })
    }

    renderAlert(alert) {
        return Helpers.createElementFromHTML(`
            <div class="alert ${alert.priority >= 3 ? 'alert--important' : ''}"
                data-id="${alert.id}"
                ${alert.url ? `onclick="window.location='${alert.url}'"` : ''}>

                ${alert.icon ? `<div class="alert__icon"><i class="${alert.icon}"></i></div>` : ''}

                <div class="alert__text">
                    <div class="alert__title">${alert.title}</div>
                    ${alert.body ? `<div class="alert__body">${alert.body}</div>` : ''}
                    <div class="alert__time"><span class="moment-from-now" data-date="${alert.created}">${moment(new Date(alert.created)).fromNow()}</span></div>
                </div>
            </div>`);
    }
}
