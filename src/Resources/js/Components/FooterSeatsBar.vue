<script setup>
import { ref, watch } from "vue";
import { Icon, LinkButton, Button } from "am-ui-package";

const props = defineProps({
  segmentCount: {
    type: String,
  },
  passenger: {
    type: Object,
    default: () => ({}),
  },
  segments: {
    type: Array,
    default: () => [],
  },
  currentSegment: {
    type: Object,
    default: () => ({}),
  },
  initials: {
    type: String,
    default: "",
  },
  currentSegmentIndex: {
    type: Number,
    default: 1,
  },
  trads: {
    type: Object,
    default: () => ({}),
  },
  canSave: {
    type: Boolean,
    default: false,
  },
  isAssigned: {
    type: Boolean,
    default: false,
  },
});
const emit = defineEmits(["save", "selectSegment", "close", "delete"]);
const isOpen = ref(false);
const bgClasses = [
  "bg-amConsistenceBlue",
  "bg-amOrange",
  "bg-fuchsia-500",
  "bg-purple-500",
  "bg-violet-500",
  "bg-indigo-500",
  "bg-blue-500",
  "bg-sky-500",
  "bg-cyan-500",
  "bg-teal-500",
  "bg-emerald-500",
  "bg-green-500",
  "bg-lime-500",
  "bg-yellow-500",
  "bg-amber-500",
  "bg-orange-500",
  "bg-red-500",
];

const handleSelect = (index) => {
  emit("selectSegment", index);
  isOpen.value = !isOpen.value;
};

const handleSave = () => {
  emit("save");
};
</script>
<template>
  <div
    id="desktop"
    class="hidden sm:flex bottom-0 justify-center min-h-20 flex-col z-[100] col-start-1 row-start-2"
  >
    <Transition name="collapse-desktop">
      <div
        v-show="isOpen"
        class="max-w-[626px] min-w-[610px] mx-auto bg-white md:-translate-x-4"
      >
        <header
          class="flex gap-32 text-xs text-amLightGray px-8 py-[10px] border-b"
        >
          <p>{{ trads.label_passenger }}</p>
          <div class="flex gap-7">
            <p>{{ trads.label_seat }}</p>
            <p>{{ trads.label_type }}</p>
          </div>
        </header>
        <button
          v-for="(segment, index) in segments"
          :key="index"
          class="w-full flex items-center justify-between border-b px-8 hover:bg-amTransparentInnovation"
          @click="handleSelect(index)"
        >
          <div class="flex items-center gap-6 py-[10px]">
            <div class="flex flex-col items-start">
              <p class="flex gap-2 items-center text-2xl">
                {{ segment.startLocation }} <Icon name="RightArrow" />{{
                  segment.endLocation
                }}
              </p>
            </div>

            <p
              v-if="
                segment.legCode === currentSegment.legCode &&
                !currentSegment.newSeat &&
                !segment.seats.length
              "
              class="text-amDarkGray text-[10px]"
            >
              {{ trads.label_no_seat }}
            </p>
            <div
              v-else-if="segment.seats.length && !segment.newSeat"
              class="text-amBlueInnovation text-[10px] ml-3 flex gap-14"
            >
              <p>{{ segment.seats[0].seatCode }}</p>
            </div>
            <div
              v-else-if="segment.newSeat"
              class="text-amBlueInnovation text-[10px] ml-3 flex gap-14"
            >
              <p>{{ segment.newSeat?.seatCode }}</p>
              <p>{{ segment.newSeat?.seatCharacteristics[0] }}</p>
            </div>

            <p v-else class="text-amBlueInnovation text-[10px]">
              {{ trads.label_select_segment }}
            </p>
          </div>

          <div class="flex gap-5">
            <Icon
              name="CheckCircle"
              color="#035CF7"
              size="10"
              v-if="segment.success"
            />
            <button
              v-if="segment.legCode === currentSegment.legCode"
              class="border rounded-full p-2 hover:scale-110"
              @click="() => $emit('delete', segment)"
            >
              <Icon name="Close" color="#035CF7" size="10" />
            </button>
          </div>
        </button>
      </div>
    </Transition>

    <div
      class="w-screen h-20 justify-evenly lg:justify-center flex items-center gap-1 lg:gap-8 px-5 xl:px-20 bg-white shadow-[0_0_20px_rgba(0,0,0,0.1)]"
    >
      <div class="hidden md:block lg:text-lg">
        <p>{{ segmentCount }}</p>
      </div>

      <div class="hidden md:block text-base text-amUltraLightGray">|</div>

      <div class="flex items-center gap-[14px]">
        <div
          class="text-white text-xs rounded-full w-[30px] h-[30px] flex justify-center items-center"
          :class="bgClasses[currentSegmentIndex]"
        >
          {{ initials }}
        </div>
        <div>
          <p class="md:hidden text-[10px]">{{ segmentCount }}</p>
          <h2 class="font-GarnettSemibold lg:text-lg line-clamp-1">
            {{ passenger.formatedName }}
          </h2>
        </div>
      </div>

      <div v-if="!currentSegment.newSeat && !currentSegment.seats[0]">
        <p class="text-amDarkGray text-[10px]">
          {{ trads.label_no_seat }}
        </p>
      </div>
      <div
        v-else-if="currentSegment.seats[0] && !currentSegment.newSeat"
        class="flex gap-3"
      >
        <p class="text-amDarkGray text-xs">
          {{ currentSegment.seats[0].seatCode }}
        </p>
      </div>
      <div v-else-if="currentSegment.newSeat" class="flex gap-3">
        <p class="text-amDarkGray text-xs">
          {{ currentSegment.newSeat.seatCode }}
        </p>
        <p class="text-xs lg:min-w-40">
          {{ currentSegment.newSeat.seatCharacteristics[0] }}
        </p>
      </div>

      <div>
        <button
          @click="isOpen = !isOpen"
          class="flex gap-[10px] items-center text-[11px] text-amDarkGray"
        >
          {{ trads.label_segment }}
          <Icon
            name="ToggleArrow"
            color="#E91B2F"
            :class="!isOpen ? '' : 'rotate-180'"
          />
        </button>
      </div>
      <div class="border-l">
        <LinkButton
          variant="primary"
          :class="`${
            !props.canSave ? 'text-gray-600' : 'text-amBlueInnovation'
          } px-5 text-sm`"
          :disabled="!props.canSave"
          @click="handleSave"
          >{{ trads.label_save }}</LinkButton
        >
        <Button
          @click="$emit('selectSegment', currentSegmentIndex + 1)"
          variant="primary"
          :disabled="
            segments.length == 1 ||
            !isAssigned ||
            currentSegmentIndex + 1 == segments.length
          "
        >
          {{ trads.label_next }}
        </Button>
      </div>
    </div>
  </div>

  <div
    id="mobile"
    class="sm:hidden fixed bottom-0 w-screen pr-2 bg-white shadow-[0_0_20px_rgba(0,0,0,0.1)] flex flex-col justify-center z-40"
  >
    <Transition name="collapse">
      <div v-show="isOpen">
        <button
          v-for="(segment, index) in segments"
          :key="index"
          class="w-full flex items-center justify-between border-b px-5 hover:bg-amTransparentInnovation"
          @click="$emit('selectSegment', index)"
        >
          <div class="flex items-center gap-2 py-[10px]">
            <div
              class="text-white text-xs rounded-full w-[30px] h-[30px] flex justify-center items-center"
              :class="bgClasses[index]"
            >
              {{ initials }}
            </div>
            <div class="flex flex-col items-start">
              <h2 class="max-w-52 font-GarnettSemibold line-clamp-1">
                {{ passenger.formatedName }}
              </h2>

              <p
                v-if="
                  segment.legCode === currentSegment.legCode &&
                  !segment.newSeat &&
                  !segment.seats.length
                "
                class="text-amDarkGray text-[10px]"
              >
                {{ trads.label_no_seat }}
              </p>
              <p
                v-else-if="segment.seats.length && !segment.newSeat"
                class="text-[10px]"
              >
                {{ segment.seats[0].seatCode }}
              </p>
              <p v-else-if="segment.newSeat" class="text-[10px]">
                {{ segment.newSeat.seatCode }}
              </p>
              <p v-else class="text-amBlueInnovation text-[10px]">
                {{ trads.label_select_segment }}
              </p>
            </div>
          </div>

          <button
            v-if="segment.legCode === currentSegment.legCode"
            class="border rounded-full p-2 hover:scale-110"
            @click="() => $emit('delete', segment)"
          >
            <Icon name="Close" color="#035CF7" size="10" />
          </button>
        </button>
      </div>
    </Transition>

    <div class="flex items-center justify-between border-b px-5">
      <div class="flex items-center gap-2 py-[10px]">
        <div
          class="text-white text-xs rounded-full w-[30px] h-[30px] flex justify-center items-center"
          :class="bgClasses[currentSegmentIndex]"
        >
          {{ initials }}
        </div>
        <div>
          <p class="text-[10px] text-amLightGray">{{ segmentCount }}</p>
          <h2 class="max-w-52 font-GarnettSemibold line-clamp-1">
            {{ passenger.formatedName }}
          </h2>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <p
          v-if="!currentSegment.newSeat && !currentSegment.seats.length"
          class="text-amDarkGray text-[10px]"
        >
          {{ trads.label_no_seat }}
        </p>
        <p
          v-else-if="currentSegment.newSeat"
          class="text-amDarkGray text-[10px]"
        >
          {{ currentSegment.newSeat?.seatCode }}
        </p>
        <p
          v-else-if="currentSegment.seats.length && !currentSegment.newSeat"
          class="text-amDarkGray text-[10px]"
        >
          {{ currentSegment.seats[0].seatCode }}
        </p>

        <div class="text-base text-amUltraLightGray">|</div>
        <div>
          <button
            @click="isOpen = !isOpen"
            class="flex gap-[10px] items-center text-[11px] text-amDarkGray"
          >
            <Icon
              name="ToggleArrow"
              color="#E91B2F"
              :class="!isOpen ? '' : 'rotate-180'"
            />
          </button>
        </div>
      </div>
    </div>

    <div class="flex px-5 py-2 justify-between">
      <LinkButton
        class="text-sm"
        :disabled="!props.canSave"
        @click="handleSave"
        >{{ trads.label_save }}</LinkButton
      >
      <Button
        @click="$emit('selectSegment', currentSegmentIndex + 1)"
        class="max-w-40"
        :disabled="segments.length == 1 || !isAssigned"
      >
        {{ trads.label_next }}
      </Button>
    </div>
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
