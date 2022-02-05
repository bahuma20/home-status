import Slide from "../Slide";

export default class Test extends Slide {


    constructor(api) {
        super(api);

        this.type = 'TEST';
    }

    async load() {
        super.load();

        await fetch('http://asljdflsakjdflaskdjflksdfjalsödkfjalsdf.org')
    }
}
