<template>
  <div id="verein-app">
    <h1>Vereinsverwaltung</h1>
    <Navigation />
    <router-view></router-view>
  </div>
</template>

<script>
import Navigation from './components/Navigation.vue'

export default {
  name: 'App',
  components: {
    Navigation
  }
}
</script>

<style scoped>
#verein-app {
  padding: 20px;
}

h1 {
  color: #0082c9;
  margin-bottom: 20px;
}
</style>
