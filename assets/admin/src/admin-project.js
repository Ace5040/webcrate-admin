import 'es6-promise/auto'
import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import VueAxios from 'vue-axios'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import Sortable from 'sortablejs'
import $ from 'jquery'
Vue.use(BootstrapVue)
Vue.use(IconsPlugin)
Vue.use(Vuex)
Vue.use(VueAxios, axios)
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import './styles/common.scss'
import './styles/admin.scss'

const store = new Vuex.Store({
    state: {
        user: user
    }
})

import header from './components/header.vue';
    new (Vue.extend(header))({
    el: '#header',
    store
});

import page from './pages/admin-project.vue';
new (Vue.extend(page))({
    el: '#app'
});

var el = document.getElementById('project_domains');
var sortable = Sortable.create(el,{
    handle: '.handle',
    draggable: '.form-group',
    direction: 'vertical',
	onEnd: function () {
        $('#project_domains .form-group').each( (index, item) => {
            $('input', item).attr('name', 'project[domains][' + index +']');
        });
    }
});

$('body').on('click', '.add-domain-button', e => {
    var list = $('#project_domains');
    var counter = list.children().length;
    var newWidget = list.attr('data-prototype');
    newWidget = newWidget.replace(/__name__/g, counter);
    $(newWidget).appendTo(list);
});

$('body').on('click', '.remove', e => {
    var list = $('#project_domains');
    var counter = list.children().length;
    if ( counter > 1 ) {
        let item = e.currentTarget;
        $(item).parent().remove();
        $('#project_domains .form-group').each( (index, item) => {
            $('input', item).attr('name', 'project[domains][' + index +']');
        });
    }
});

var el = document.getElementById('project_nginx_options');
var sortable = Sortable.create(el,{
    handle: '.handle',
    draggable: '.form-group',
    direction: 'vertical',
	onEnd: function () {
        $('#project_nginx_options .form-group').each( (index, item) => {
            $('input', item).attr('name', 'project[nginx_options][' + index +']');
        });
    }
});

$('body').on('click', '.add-nginx-option-button', e => {
    var list = $('#project_nginx_options');
    var counter = list.children().length;
    var newWidget = list.attr('data-prototype');
    newWidget = newWidget.replace(/__name__/g, counter);
    $(newWidget).appendTo(list);
});

$('body').on('click', '.remove', e => {
    var list = $('#project_nginx_options');
    var counter = list.children().length;
    if ( counter > 1 ) {
        let item = e.currentTarget;
        $(item).parent().remove();
        $('#project_nginx_options .form-group').each( (index, item) => {
            $('input', item).attr('name', 'project[nginx_options][' + index +']');
        });
    }
});
