<script setup>
import { computed, ref } from "vue";
import { CheckInput, Icon } from "am-ui-package";

const LOCALE = "es-MX";
const TZ = "America/Mexico_City";

function formatDate(isoString) {
  if (!isoString) return "";
  const d = new Date(isoString);
  return new Intl.DateTimeFormat(LOCALE, {
    timeZone: TZ,
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  }).format(d);
}

function formatTime(isoString) {
  if (!isoString) return "";
  const d = new Date(isoString);
  return new Intl.DateTimeFormat(LOCALE, {
    timeZone: TZ,
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  }).format(d);
}

const props = defineProps({
  passenger: Object,
  segments: Array,
  clid: { type: String, default: "" },
  checkLabel: String,
  selectAll: { type: Boolean, default: false },
  trads: { type: Object, default: () => ({}) },
  selectedIds: { type: Array, default: () => [] },
});

const emit = defineEmits(["toggle-all", "toggle"]);

const segKey = (segment, index) =>
  segment?.segmentID ?? segment?.segmentCode ?? index;

const typesIndex = {
  "79M": {
    A: props.trads.label_window,
    B: props.trads.label_middle,
    C: props.trads.label_aisle,
    D: props.trads.label_aisle,
    E: props.trads.label_middle,
    F: props.trads.label_window,
  },
  "7M9": {
    A: props.trads.label_window,
    B: props.trads.label_middle,
    C: props.trads.label_aisle,
    D: props.trads.label_aisle,
    E: props.trads.label_middle,
    F: props.trads.label_window,
  },
  "7M8": {
    A: props.trads.label_window,
    B: props.trads.label_middle,
    C: props.trads.label_aisle,
    D: props.trads.label_aisle,
    E: props.trads.label_middle,
    F: props.trads.label_window,
  },
  E90: {
    A: props.trads.label_window,
    B: props.trads.label_aisle,
    C: props.trads.label_aisle,
    D: props.trads.label_window,
  },
  "E-190": {
    A: props.trads.label_window,
    B: props.trads.label_aisle,
    C: props.trads.label_aisle,
    D: props.trads.label_window,
  },
  "7S8": {
    A: props.trads.label_window,
    B: props.trads.label_aisle,
    C: props.trads.label_aisle,
    D: props.trads.label_window,
  },
  789: {
    A: props.trads.label_window,
    B: props.trads.label_middle,
    C: props.trads.label_aisle,
    D: props.trads.label_aisle,
    E: props.trads.label_middle,
    F: props.trads.label_aisle,
    G: props.trads.label_aisle,
    H: props.trads.label_middle,
    J: props.trads.label_window,
  },
};

const openMap = ref({});
const isOpen = (segment, index) => !!openMap.value[segKey(segment, index)];
const toggleOpen = (segment, index) => {
  const key = segKey(segment, index);
  openMap.value[key] = !openMap.value[key];
};
const panelId = (segment, index) => `seg-${segKey(segment, index)}-panel`;

const selectedSet = computed(() => new Set(props.selectedIds));
const total = computed(() => props.segments?.length ?? 0);
const selectedCount = computed(() => props.selectedIds.length);

const allChecked = computed({
  get: () => selectedCount.value > 0 && selectedCount.value === total.value,
  set: (val) => emit("toggle-all", val),
});
const allIndeterminate = computed(
  () => selectedCount.value > 0 && selectedCount.value < total.value
);

const isSelected = (segment, index) =>
  selectedSet.value.has(segKey(segment, index));

console.log(props.segments);

const thereIsOneInvalid = computed(() => {
  return props.segments.some((segment) => !isOk(segment));
});

const isOk = (seg) => {
  if (seg.status === "OK") return true;
  return false;
};
</script>

<template>
  <div id="desktop" class="hidden sm:block">
    <div>
      <header
        v-if="selectAll"
        class="flex justify-between items-center mb-4 text-xs"
      >
        <CheckInput
          :label="checkLabel"
          v-model="allChecked"
          :disabled="thereIsOneInvalid"
          :indeterminate="allIndeterminate"
        />
        <p v-if="segments?.length" class="flex gap-[2px]">
          {{ trads.label_found_first }}
          <span class="text-amBlueInnovation">{{
            segments.length + " " + trads.label_tickets
          }}</span>
          {{ trads.label_found_last }}
        </p>
      </header>
    </div>

    <div class="flex flex-col gap-[17px]">
      <article
        v-for="(segment, index) in segments"
        :key="index"
        class="relative w-full border border-amNewLighterGray bg-white rounded shadow-[0_3px_2px_0px_rgba(0,0,0,0.03)] grid grid-cols-3 px-8 py-5"
        v-bind="$attrs"
      >
        <div class="relative flex">
          <CheckInput
            class="relative self-start p-3"
            :label="''"
            :disabled="!isOk(segment)"
            :model-value="isSelected(segment, index)"
            @update:modelValue="(val) => emit('toggle', segment, val)"
          />
          <div class="flex flex-col gap-11">
            <div>
              <label for="name" class="text-sm">{{ trads.label_name }}</label>
              <p id="name" class="text-xs text-amTextGray">
                {{ passenger.formatedName }}
              </p>
            </div>
            <div>
              <label for="status" class="text-sm">{{
                trads.label_status
              }}</label>
              <p
                id="status"
                :class="`text-xs ${
                  segment.status === 'OK' ? 'text-amGreen' : 'text-amTextGray'
                }`"
              >
                {{ segment.status }}
              </p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 max-w-[181px]">
          <div class="border-b">
            <label for="origin" class="text-sm">{{ trads.label_origin }}</label>
            <p id="origin" class="text-xs text-amTextGray">
              {{ segment.startLocation }}
            </p>
          </div>
          <div class="border-b">
            <label for="destiny" class="text-sm">{{
              trads.label_destiny
            }}</label>
            <p id="destiny" class="text-xs text-amTextGray">
              {{ segment.endLocation }}
            </p>
          </div>
          <div class="relative flex flex-col justify-end">
            <label for="date" class="text-sm">{{ trads.label_date }}</label>
            <p id="date" class="text-xs text-amTextGray">
              {{ formatDate(segment.arrivalDateTime) }}
            </p>
          </div>
          <div class="relative flex flex-col justify-end">
            <label for="time" class="text-sm">{{ trads.label_time }}</label>
            <p id="time" class="text-xs text-amTextGray">
              {{ formatTime(segment.arrivalDateTime) }}
            </p>
          </div>
        </div>

        <div class="grid grid-cols-2 max-w-[181px]">
          <div class="border-b">
            <label for="seat" class="text-sm">{{ trads.label_seat }}</label>
            <p id="seat" class="text-xs text-amTextGray">
              {{ segment.seats?.length ? segment.seats[0].seatCode : "-" }}
            </p>
          </div>
          <div class="border-b">
            <label for="type" class="text-sm">{{ trads.label_type }}</label>
            <p id="type" class="text-xs text-amTextGray">
              {{
                typesIndex[segment.aircraftType] != undefined &&
                segment.seats[0]
                  ? typesIndex[segment.aircraftType][
                      segment.seats[0].seatCode[1].slice(-1)
                    ]
                  : "-"
              }}
            </p>
          </div>
          <div class="relative flex flex-col justify-end">
            <label for="class" class="text-sm">{{ trads.label_class }}</label>
            <p id="class" class="text-xs text-amTextGray">
              {{ segment.bookingClass }}
            </p>
          </div>
          <div class="relative flex flex-col justify-end">
            <label for="corporate" class="text-sm">{{
              trads.label_corporate
            }}</label>
            <p
              v-if="segment.seats[0]?.status === 'PAID'"
              class="text-amGreen text-xs"
            >
              {{ trads.label_confirmated }}
            </p>
            <p v-else class="text-amRed text-xs">{{ trads.label_pending }}</p>
          </div>
        </div>
      </article>
    </div>
  </div>

  <div id="mobile" class="sm:hidden">
    <div>
      <header v-if="selectAll" class="flex flex-col-reverse gap-5 mb-4 text-xs">
        <CheckInput
          :label="checkLabel"
          v-model="allChecked"
          :indeterminate="allIndeterminate"
          :disabled="thereIsOneInvalid"
        />
      </header>
    </div>

    <div class="flex flex-col gap-[17px]">
      <article
        v-for="(segment, index) in segments"
        :key="index"
        class="relative w-full border border-amNewLighterGray bg-white rounded shadow-[0_3px_2px_0px_rgba(0,0,0,0.03)] pt-4 pb-0"
        v-bind="$attrs"
      >
        <div
          class="relative w-full h-full flex items-center px-4 border-b pb-3"
        >
          <CheckInput
            class="relative self-start pt-3"
            :label="''"
            :model-value="isSelected(segment, index)"
            @update:modelValue="(val) => emit('toggle', segment, val)"
            :disabled="!isOk(segment)"
          />

          <div class="flex-1 flex justify-between gap-6">
            <div>
              <label for="name" class="text-sm">{{ trads.label_name }}</label>
              <p id="name" class="text-xs text-amTextGray">
                {{ passenger.formatedName }}
              </p>
            </div>
            <div>
              <label for="status" class="text-sm">{{
                trads.label_status
              }}</label>
              <p
                id="status"
                :class="[
                  'text-xs',
                  segment.status === 'OK' ? 'text-amGreen' : 'text-amTextGray',
                ]"
              >
                {{ segment.status }}
              </p>
            </div>
          </div>

          <button
            type="button"
            class="ml-1 -mr-1 p-2 rounded focus:outline-none focus:ring-2 focus:ring-offset-1 grid place-items-center"
            :aria-expanded="isOpen(segment, index)"
            :aria-controls="panelId(segment, index)"
            @click="toggleOpen(segment, index)"
          >
            <Icon
              name="ToggleArrow"
              class="transition-transform duration-200 text-amTextGray"
              :class="isOpen(segment, index) ? 'rotate-180' : ''"
            />
          </button>
        </div>

        <div
          :id="panelId(segment, index)"
          v-show="isOpen(segment, index)"
          class="mx-2 p-2 overflow-hidden flex flex-col gap-2"
        >
          <div class="grid grid-cols-2 pb-3 gap-x-6 gap-y-3 border-b">
            <div>
              <label class="text-sm">{{ trads.label_origin }}</label>
              <p class="text-xs text-amTextGray">{{ segment.startLocation }}</p>
            </div>
            <div>
              <label class="text-sm">{{ trads.label_destiny }}</label>
              <p class="text-xs text-amTextGray">{{ segment.endLocation }}</p>
            </div>
            <div>
              <label class="text-sm">{{ trads.label_date }}</label>
              <p class="text-xs text-amTextGray">
                {{ formatDate(segment.arrivalDateTime) }}
              </p>
            </div>
            <div>
              <label class="text-sm">{{ trads.label_time }}</label>
              <p class="text-xs text-amTextGray">
                {{ formatTime(segment.arrivalDateTime) }}
              </p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-x-6 gap-y-3">
            <div>
              <label class="text-sm">{{ trads.label_seat }}</label>
              <p class="text-xs text-amTextGray">
                {{ segment.seats?.length ? segment.seats[0].seatCode : "-" }}
              </p>
            </div>
            <div>
              <label class="text-sm">{{ trads.label_type }}</label>
              <p class="text-xs text-amTextGray">
                {{
                  typesIndex[segment.aircraftType] != undefined &&
                  segment.seats[0]
                    ? typesIndex[segment.aircraftType][
                        segment.seats[0].seatCode[1].slice(-1)
                      ]
                    : "-"
                }}
              </p>
            </div>
            <div>
              <label class="text-sm">{{ trads.label_class }}</label>
              <p class="text-xs text-amTextGray">{{ segment.bookingClass }}</p>
            </div>
            <div>
              <label class="text-sm">{{ trads.label_corporate }}</label>
              <p class="text-xs text-amTextGray"></p>
              <p
                v-if="segment.seats[0]?.status === 'PAID'"
                class="text-amGreen text-xs"
              >
                {{ trads.label_confirmated }}
              </p>
              <p v-else class="text-amRed text-xs">{{ trads.label_pending }}</p>
            </div>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>
