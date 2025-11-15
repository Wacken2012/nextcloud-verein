<template>
  <div class="deck-container">
    <div class="placeholder">
      <i class="icon icon-deck"></i>
      <h2>Aufgaben</h2>
      <p>Deck/Kanban-Integration kommt bald</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Deck'
}
</script>

<style scoped lang="scss">
.deck-container {
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
