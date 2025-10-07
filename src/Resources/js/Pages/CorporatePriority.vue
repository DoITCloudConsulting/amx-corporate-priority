<script setup>
import { BaseInput } from "am-ui-package/src";
import { usePage } from "@inertiajs/vue3";

const props = defineProps(["user"]);
console.log(props);
</script>

<template>
  <h1>Prueba adiel</h1>
  <BaseInput label="Brandon" />
</template>
