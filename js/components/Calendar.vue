<template>
  <div class="calendar-container">
    <div class="placeholder">
      <i class="icon icon-calendar"></i>
      <h2>Termine</h2>
      <p>Kalenderintegration kommt bald</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Calendar'
}
</script>

<style scoped lang="scss">
.calendar-container {
  background: var(--color-main-background);
  border-radius: 4px;
  padding: 40px 20px;
  text-align: center;
}

.placeholder {
  i {
    font-size: 48px;
    color: var(--color-text-secondary);
    display: block;
    margin-bottom: 16px;
  }

  h2 {
    color: var(--color-text);
    margin-bottom: 8px;
  }

  p {
    color: var(--color-text-secondary);
  }
}
</style>
