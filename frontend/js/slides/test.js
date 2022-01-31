import Slide from "../Slide";

export default class Test extends Slide {

    async load() {
        super.load();

        await fetch('http://asljdflsakjdflaskdjflksdfjalsödkfjalsdf.org')
    }
}
