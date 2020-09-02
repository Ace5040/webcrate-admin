<template>
<div class="admin-page">
  <b-navbar toggleable="lg" type="dark" variant="info">
    <b-navbar-brand href="/">Webcrate</b-navbar-brand>

    <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

    <b-collapse id="nav-collapse" is-nav>
      <!-- Right aligned nav items -->
      <b-navbar-nav class="ml-auto">
        <b-nav-item-dropdown right>
          <!-- Using 'button-content' slot -->
          <template v-slot:button-content>
            <em>{{ user }}</em>
          </template>
          <b-dropdown-item href="/logout">Log Out</b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
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
        user: user
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
