<script setup>
import { defineAsyncComponent } from "vue";

const icons = {
  ErrorSeat: defineAsyncComponent(() => import("./ErrorSeat.vue")),
};

defineProps({ name: String });
</script>

<template>
  <component :is="icons[name]" v-bind="$attrs" />
</template>
