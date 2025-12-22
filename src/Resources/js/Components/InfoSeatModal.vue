<script setup>
import { ref } from "vue";
import { Icon } from "am-ui-package";

defineProps({
  title: {
    type: String,
    required: true,
  },
  topText: {
    type: String,
    default: "",
  },
  characteristics: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close"]);

const isOpen = ref(true);
</script>

<template>
  <div class="flex flex-col z-0 gap-5 max-w-[280px] md:max-w-full md:w-full">
    <article class="h-fit rounded-sm bg-white border">
      <header
        class="w-full p-5 flex justify-center bg-amGreen/10 border-b relative"
      >
        <h2 class="text-base font-GarnettSemibold text-amGreen">
          {{ title }}
        </h2>
        <Icon
          name="Close"
          size="18"
          class="absolute right-5 cursor-pointer"
          color="gray"
          @click="emit('close')"
        />
      </header>
      <slot></slot>
    </article>
  </div>
</template>
