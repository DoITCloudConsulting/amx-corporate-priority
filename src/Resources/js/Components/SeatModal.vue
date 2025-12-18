<script setup>
import { ref } from "vue";
import { Icon } from "am-ui-package";
import seat from "../../../assets/seat.png";

defineProps({
  seatType: {
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

const isOpen = ref(true);

const icons = {
  "Asiento estándar": "StandardSeat",
  "Desembarque prioritario": "PriorityLanding",
  "Ubicación prioritaria": "Diamond",
};
</script>

<template>
  <div class="flex flex-col gap-5 w-full">
    <article class="rounded-sm bg-white border">
      <header class="w-full p-5 flex justify-between bg-amGreen/10 border-b">
        <h2 class="text-base font-GarnettSemibold text-amGreen">
          {{ seatType }}
        </h2>
        <button
          class="w-[30px] h-[30px] rounded bg-white border flex justify-center items-center"
          :aria-expanded="isOpen"
          @click="() => (isOpen = !isOpen)"
        >
          <Icon name="ToggleArrow" :class="isOpen ? '' : 'rotate-180'" />
        </button>
      </header>

      <Transition name="collapse">
        <section v-show="isOpen" class="p-5 md:py-[30px] flex justify-between">
          <div class="flex flex-col gap-4">
            <div
              class="flex text-xs items-center gap-[7px]"
              v-for="(item, index) in characteristics"
              :key="index"
            >
              <Icon :name="icons[item]" />
              <p>{{ item }}</p>
            </div>
          </div>
          <img :src="seat" alt="seat" width="91" class="hidden md:block" />
        </section>
      </Transition>
    </article>
  </div>
</template>

<style scoped>
.collapse-enter-active,
.collapse-leave-active {
  transition: max-height 250ms ease, opacity 200ms ease, transform 250ms ease;
  overflow: hidden;
}
.collapse-enter-from,
.collapse-leave-to {
  max-height: 0;
  opacity: 0;
  transform: translateY(-8px);
}
.collapse-enter-to,
.collapse-leave-from {
  max-height: 500px;
  opacity: 1;
  transform: translateY(0);
}
</style>
