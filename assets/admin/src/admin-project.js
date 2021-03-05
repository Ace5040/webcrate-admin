import Vue from 'vue'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import Sortable from 'sortablejs'
import $ from 'jquery'

Vue.use(BootstrapVue)
Vue.use(IconsPlugin)

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import './styles/common.scss'
import './styles/admin.scss'

import Admin from './pages/admin-project.vue';

var el = document.getElementById('project_domains');

var sortable = Sortable.create(el,{
    handle: '.handle',
    draggable: '.form-group',
    direction: 'vertical',
	onEnd: function () {
        $('#project_domains .form-group').each( (index, item) => {
            $('input', item).attr('name', 'project[domains][' + index +']');
        });
    },

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

var app = new (Vue.extend(Admin))({
    el: '#app'
});
