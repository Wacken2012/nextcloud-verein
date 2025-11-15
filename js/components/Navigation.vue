<template>
  <nav class="verein-navigation">
    <ul class="nav-menu">
      <li>
        <router-link to="/members" :class="{ active: isActive('members') }">
          <span class="icon">👥</span>
          <span>Mitglieder</span>
        </router-link>
      </li>
      <li>
        <router-link to="/fees" :class="{ active: isActive('fees') }">
          <span class="icon">💰</span>
          <span>Beiträge</span>
        </router-link>
      </li>
      <li>
        <router-link to="/sepa" :class="{ active: isActive('sepa') }">
          <span class="icon">📄</span>
          <span>SEPA-Export</span>
        </router-link>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: 'Navigation',
  methods: {
    isActive(routeName) {
      return this.$route.path.includes(routeName)
    }
  }
}
</script>

<style scoped>
.verein-navigation {
  background-color: #f5f5f5;
  border-bottom: 1px solid #ddd;
  margin-bottom: 20px;
}

.nav-menu {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-menu li {
  margin: 0;
}

.nav-menu a {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  text-decoration: none;
  color: #333;
  transition: background-color 0.2s;
}

.nav-menu a:hover {
  background-color: #e0e0e0;
}

.nav-menu a.active {
  background-color: #0082c9;
  color: white;
}

.icon {
  margin-right: 8px;
  font-size: 1.2em;
}
</style>
