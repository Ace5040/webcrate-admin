import Vue from 'vue'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'

Vue.use(BootstrapVue)
Vue.use(IconsPlugin)

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import './styles/common.scss'
import './styles/admin.scss'

import Admin from './pages/admin.vue';

var app = new (Vue.extend(Admin))({
    el: '#app'
});
