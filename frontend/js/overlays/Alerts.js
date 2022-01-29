import Overlay from "../Overlay";

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
        console.log(this.alerts);
        // TODO: Implement UI
    }

    async loadAlerts() {
        const response = await fetch('/api/alerts');
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
        // TODO: Implement UI
    }

    removeAlert(alert) {
        // TODO: Implement UI
    }
}
