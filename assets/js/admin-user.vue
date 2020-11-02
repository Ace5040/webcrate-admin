<template>
<div class="admin-page">
  <b-navbar toggleable="lg" type="dark" variant="info">
    <b-navbar-brand href="/">Webcrate</b-navbar-brand>

    <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

    <b-collapse id="nav-collapse" is-nav>
      <b-navbar-nav>
        <b-nav-item href="/admin/users" class="active">Users</b-nav-item>
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
  <div>{{ user_data.uid }}, {{ user_data.name }}</div>
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
      user_data: user_data
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
