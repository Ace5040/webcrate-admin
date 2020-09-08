<template>
<div class="admin-page">
  <b-navbar toggleable="lg" type="dark" variant="info">
    <b-navbar-brand href="/">Webcrate</b-navbar-brand>

    <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

    <b-collapse id="nav-collapse" is-nav>
      <b-navbar-nav>
        <b-nav-item href="/admin/users">Users</b-nav-item>
      </b-navbar-nav>
      <!-- Right aligned nav items -->
      <b-navbar-nav class="ml-auto">
        <b-nav-item-dropdown right>
          <template v-slot:button-content><em>{{ user }}</em></template>
          <b-dropdown-item href="/logout">Log Out</b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
  <b-list-group>
    <b-list-group-item v-for="item in soft" v-bind:key="item.name" class="d-flex justify-content-between align-items-center">
      {{ item.name }}
      <b-badge variant="primary" pill>{{ item.version ? item.version : 'n/a' }}</b-badge>
    </b-list-group-item>
  </b-list-group>
</div>
</template>

<script>

export default {

  created () {

  },

  components: {
  },

  data () {
    return {
        user: user,
        soft: soft
    }
  },

  computed: {

  },

  watch: {

  },

  mounted () {

  },

  methods: {


  }


}

</script>
