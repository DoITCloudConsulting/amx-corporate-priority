<script setup>
import { BaseInput } from "am-ui-package/src";
import { usePage } from "@inertiajs/vue3";
const page = usePage();
console.log(page.props);
</script>

<template>
  <BaseInput label="Brandobn" />
</template>
