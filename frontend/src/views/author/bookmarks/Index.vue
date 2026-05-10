<template>
  <SimpleList endpoint="/author/bookmarks" empty-text="Belum ada buku favorit." />
</template>

<script setup>
import SimpleList from '../shared/SimpleList.vue'
</script>
