import Vue from 'vue'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'

Vue.use(BootstrapVue)
Vue.use(IconsPlugin)

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import '../css/login.scss';

import Login from './login.vue';

var app = new (Vue.extend(Login))({
    el: '#app'
});
