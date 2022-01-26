import Helpers from "../Helpers";
import Slide from "../Slide";

export default class Tasks extends Slide {
    constructor() {
        super();
        this.type = 'tasks'
        this.data = null;
    }

    async load() {
        const response = await fetch('tasks.php', {
            method: 'GET',
        });
        this.data = await response.json();

    }

    render() {
        let tasks = [];
        this.data.forEach(task => {
            // Filter out child tasks. We focus on the big things here...
            if (task.parent !== null) {
                return;
            }

            tasks.push(`<li class="tasks__item">${task.title}</li>`)
        });

        return `
          <lottie-player 
            src="https://assets7.lottiefiles.com/packages/lf20_AXcpdj.json" 
            background="transparent" 
            speed="0.8" 
            style="width: 300px; height: 300px;" 
            autoplay
            class="tasks__image"></lottie-player>
          <ul class="tasks__list">
            ${tasks.join('')}
          </ul>
        `;
    }
}

