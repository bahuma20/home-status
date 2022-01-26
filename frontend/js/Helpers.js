export default class Helpers {
    static createElementFromHTML(htmlString) {
        let div = document.createElement('div');
        div.innerHTML = htmlString.trim();

        return div.firstChild;
    }

    static randomNumber(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min +1)) + min;
    }

    static randomItem(list) {
        if (list.length === 0) {
            return false;
        }

        return list[this.randomNumber(0, list.length - 1)];
    }

    static async getData(url = '') {
        // Default options are marked with *
        const response = await fetch('proxy.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                url: url,
            })
        });
        return response.json();
    }
}
