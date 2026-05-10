<template>
  <router-view v-slot="{ Component }">
    <Transition name="page" mode="out-in">
      <component :is="Component" />
    </Transition>
  </router-view>
</template>

<style>
#app {
  min-height: 100vh;
}

.page-enter-active {
  animation: pageIn 0.28s ease-out;
}

.page-leave-active {
  animation: pageOut 0.16s ease-in;
}

@keyframes pageIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pageOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
    transform: translateY(-4px);
  }
}
</style>
