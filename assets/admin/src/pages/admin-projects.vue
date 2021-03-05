<template>
<div class="admin-page">
  <b-navbar toggleable="lg" type="dark" variant="info">
    <b-navbar-brand href="/">Webcrate</b-navbar-brand>

    <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

    <b-collapse id="nav-collapse" is-nav>
      <b-navbar-nav>
        <b-nav-item href="/admin/projects" class="active">Projects</b-nav-item>
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
  <div class="action-menu">
    <div class="">Import projects from users.yml file: </div>
    <b-form @submit="onImport">
      <b-form-group>
        <b-form-file
          v-model="projectsFile"
          :state="Boolean(projectsFile)"
          placeholder="Choose a file or drop it here..."
          drop-placeholder="Drop file here..."
        ></b-form-file>
        <b-button type="submit" variant="primary">Import</b-button>
      </b-form-group>
    </b-form>
  </div>
  <b-table v-if="projects.length" sort-by="uid" striped hover :items="projects" :fields="fields">
      <template v-slot:cell(name)="row">
        {{ row.value }}
      </template>
      <template v-slot:cell(actions)="row">
        <b-button variant="primary" size="sm" :href="'/admin/projects/'+row.item.uid">Edit</b-button>
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

  data: () => ({
    user: user,
    projects: projects,
    projectsFile: null,
    fields: [
      {key: 'uid', label: 'uid', sortable: true },
      {key: 'name', label: 'Name', sortable: true },
      {key: 'https', label: 'Https', sortable: true },
      {key: 'backend', label: 'Backend', sortable: true },
      {key: 'backend_version', label: 'Backend version', sortable: true },
      {key: 'backup', label: 'Backup', sortable: true },
      {key: 'actions', label: 'Actions'}
    ]
  }),

  computed: {

  },

  watch: {

  },

  mounted () {

  },

  methods: {

    onImport: function(e) {
      e.preventDefault();
      let formData = new FormData();
      formData.append('file', this.projectsFile);

      this.axios.post('/admin/import-projects',
          formData,
          {
          headers: {
              'Content-Type': 'multipart/form-data'
          }
        }
      ).then(function(data){
        console.log(data.data);
      })
      .catch(function(){
        console.log('FAILURE!!');
      });

    }

  }


}

</script>
