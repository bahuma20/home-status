import 'regenerator-runtime/runtime';
import App from './App';

import '../scss/app.scss';

import 'moment/locale/de';
import moment from "moment";

moment.locale('de');

window.homeApp = new App();

