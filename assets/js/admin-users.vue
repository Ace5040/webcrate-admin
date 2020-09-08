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
  <b-table v-if="users.length" sort-by="uid" striped hover :items="users" :fields="fields">
      <template v-slot:cell(name)="row">
        {{ row.value }}
      </template>
      <template v-slot:cell(actions)="row">
        <b-button variant="primary" size="sm" :href="'/admin/users/'+row.item.uid">Edit</b-button>
      </template>
  </b-table>
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
        users: users,
        fields: [
          {key: 'uid', label: 'uid', sortable: true },
          {key: 'name', label: 'Name', sortable: true },
          {key: 'https', label: 'Https', sortable: true },
          {key: 'backend', label: 'Backend', sortable: true },
          {key: 'backend_version', label: 'Backend version', sortable: true },
          {key: 'backup', label: 'Backup', sortable: true },
          {key: 'actions', label: 'Actions'}
       ]
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
