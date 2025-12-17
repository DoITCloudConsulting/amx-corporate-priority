<script setup>
import { computed } from "vue";
const props = defineProps({
  size: {
    type: String,
    validator: (prop) => ["xs", "sm", "base", "lg", "xl"].includes(prop),
    default: "base",
  },
  color: {
    type: String,
    validator: (prop) => ["rose", "blue"].includes(prop),
    default: "blue",
  },
});

const classes = computed(() => {
  const base = "rounded-full animate-spin";

  const color = {
    rose: "border-amRoseMexican",
    blue: "border-amBlueInnovation",
  }[props.color];

  const size = {
    xs: "size-[25px] border-[4px] border-t-transparent",
    base: " size-[50px]",
  }[props.size];

  return [base, color, size];
});
</script>
<template>
  <div :class="classes"></div>
</template>
