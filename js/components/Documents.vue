<template>
  <div class="documents-container">
    <div class="placeholder">
      <i class="icon icon-documents"></i>
      <h2>Dokumente</h2>
      <p>Dokumentenverwaltung kommt bald</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Documents'
}
</script>

<style scoped lang="scss">
.documents-container {
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
