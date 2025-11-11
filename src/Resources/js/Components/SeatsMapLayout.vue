<script setup>
import { ref, reactive, computed, watch, watchEffect } from "vue";
import {
  SeatModal,
  HeadSeatsBar,
  FooterSeatsBar,
  SeatsMap,
  InfoSeatModal,
  CheckInput,
  Button,
  LinkButton,
} from "am-ui-package";

const props = defineProps({
  seatsMapInfo: { type: Array, default: () => [] },
  segments: { type: Array, default: () => [] },
  passenger: { type: Object, default: () => ({}) },
  trads: { type: Object, default: () => ({}) },
  allSeatsAssigned: { type: Boolean, default: true }
});

const aircraftType = {
  "79M": ['A','B','C','|','D','E','F'],
  "7M9": ['A','B','C','|','D','E','F'],
  "7M8": ['A','B','C','|','D','E','F'],
  "E90": ['A','B','|','C','D'],
  "E-190": ['A','B','|','C','D'],
  "7S8": ['A','B','|','C','D'],
  "789": ['A','B','C','|','D','E','F','|','G','H','J'],
}

const emit = defineEmits(["close", "addSeat", "delete", "assignSeat", "update-agree-terms"]);

const currentSegment = ref(0);
const modalOpenBySegment = reactive({});
const initialHasAnySeatByIndex = reactive({});

const modalLabel = ref();

const currentSegmentInfo = computed(() => props.segments[currentSegment.value] || {});
const currentSeatMapRaw = computed(() => props.seatsMapInfo[currentSegmentInfo.value.segmentID] || null);


const currentSeatMap = computed(() => {
  const raw = currentSeatMapRaw.value;
  if (Array.isArray(raw)) return raw;
  if (raw && Array.isArray(raw.seatMap)) return raw.seatMap;
  return [];
});

const agreeTerms = computed({
  get: () => !!currentSegmentInfo.value?.agreeTerms,
  set: (val) => {
    emit('update-agree-terms', { index: currentSegment.value, value: val });
  }
});

const corporateSeatCode = computed(() => currentSegmentInfo.value?.seats?.[0]?.seatCode || null);

const hasCorporateSeatInMap = computed(() => {
  const code = corporateSeatCode.value;
  const map = currentSeatMap.value;
  if (!code || !map?.length) return false;
  return map.some(s => s?.seatCode === code);
});

const initModalStates = (segments = []) => {
  segments.forEach((seg, idx) => {
    const key = idx;
    if (modalOpenBySegment[key] === undefined) {
      modalOpenBySegment[key] = !!(seg?.seats?.length || seg?.newSeat);
    }
    // guardamos si originalmente tenía asientos
    if (initialHasAnySeatByIndex[key] === undefined) {
      initialHasAnySeatByIndex[key] = !!(seg?.seats?.length);
    }
  });
};

const newSeatExists = computed(() => !!currentSegmentInfo.value?.newSeat);

const assign = () => {
  emit('assignSeat', currentSegmentInfo.value);
};

const modalIsOpenForCurrent = computed(() => {
  const key = currentSegment.value;
  return !!modalOpenBySegment[key];
});

watchEffect(() => {
  modalLabel.value = hasCorporateSeatInMap.value
    ? props.trads.label_seat_is_preferent
    : props.trads.label_seat_no_preferent;
});

const seatType = ref(props.trads.label_am_preferred);
const segmentCount = computed(
  () => `${currentSegment.value + 1} ${props.trads.label_of} ${props.segments.length}`
);
const initials = computed(() => {
  const p = props.passenger || {};
  return p.firstName && p.lastName ? p.firstName[0] + p.lastName[0] : '';
});


const seatsCharacteristics = ref([
  props.trads.label_standard_seat,
  props.trads.label_priority_landing,
  props.trads.label_priority_ubication,
]);

const handleSelect = (index) => {
  
  if (index === props.segments.length) {
    currentSegment.value = 0;
  } else {
    currentSegment.value = index;
  }
};

const handleSelectSeat = (seat) => {
  if (!seat) return;
  emit("addSeat", seat, currentSegment.value);
};

const handleAsignSeat = () => {
  const anyChange = props.segments.some(seg => isSeatChange(seg));

  if (!anyChange || !currentSegmentInfo.value?.agreeTerms) {
    emit('close');
    return;
  }
  emit('asignSeat');
};

const handleDelete = (s) => {
console.log(s);
  emit('delete', s)
}

const initialHadAnySeat = computed(() => initialHasAnySeatByIndex[currentSegment.value] ?? false);

const isCorporateSegment = computed(() => {
  const s = currentSegmentInfo.value ?? {};
  return Boolean(s.clid || s.isCorporate || s.corporate);
});

const canSave = computed(() => {
  if (isCorporateSegment.value && !initialHadAnySeat.value) {
    return true;
  }
  if (newSeatExists.value) {
    return true;
  }
  if (newSeatExists.value && initialHadAnySeat.value) {
    return true;
  }

  return !!currentSegmentInfo.value?.agreeTerms;
});

const isSeatChange = (seg) => {
  if (!seg?.newSeat) return false;

  const newCode = seg.newSeat?.seatCode ?? null;
  if (!newCode) return false;

  const originalFirst = Array.isArray(seg.seats) && seg.seats.length ? seg.seats[0]?.seatCode : null;
  if (!originalFirst) return true;

  if (originalFirst !== newCode) return true;

  return false;
};


watch(props.segments, () => {
  console.log(props.segments);
  
})

watch(
  () => props.segments,
  (list) => {
    initModalStates(list || []);
  },
  { immediate: true }
);
</script>

<template>
  <section class="w-screen bg-[#F2F8FC] flex flex-col items-center">
    <main class="relative w-full h-full flex justify-evenly gap-5 md:pl-[46px] sm:pr-16">
      <SeatsMap
        :initials="initials"
        :data="currentSeatMap"
        @select="handleSelectSeat"
        :colsPattern="aircraftType[currentSegmentInfo.aircraftType]"
        :currentSegment="currentSegmentInfo"
        :characteristics="seatsCharacteristics"
      />

      <div id="desktop" class="hidden sm:flex flex-col gap-10 pt-20">
        <div>
          <p class="mb-3">Vuelo AM</p>
          <SeatModal
            class="hidden sm:flex"
            :seatType="seatType"
            :characteristics="seatsCharacteristics"
          />
        </div>
        <InfoSeatModal v-if="(currentSegmentInfo.seats?.length || currentSegmentInfo.newSeat) && modalIsOpenForCurrent && !currentSegmentInfo.success" class="z-50flex flex-col gap-[15px]" :title="trads.label_seats">
          <div class="p-6 md:p-8 flex flex-col gap-6">
            <p class=" text-center text-sm">{{ modalLabel }}</p>
              <CheckInput
                class="text-xs"
                :label="trads.label_agree_terms"
                v-model="agreeTerms"
              />
            <div class="flex flex-col gap-4">
              <LinkButton class="w-full" @click="$emit('close', false, currentSegmentInfo)">{{ trads.label_back }}</LinkButton>
              <Button @click="assign" width="full" :disabled="!currentSegmentInfo.agreeTerms ">
                {{ trads.label_continue }}
              </Button>
            </div>
          </div>
        </InfoSeatModal>

        <InfoSeatModal v-if="currentSegmentInfo.success" class="z-50flex flex-col gap-[15px]" :title="trads.label_seats">
          <div class="p-6 md:p-8 flex flex-col gap-6">
            <p class=" text-center text-sm">{{ trads.label_change_success }}</p>
          </div>
        </InfoSeatModal>
      </div>

      <div id="mobile" v-if="(currentSegmentInfo.seats?.length || currentSegmentInfo.newSeat) && modalIsOpenForCurrent "  class="absolute sm:hidden w-screen h-screen flex justify-center pt-60 bg-black bg-opacity-30 z-50">
        <InfoSeatModal class="z-50 flex flex-col gap-[15px]" :title="trads.label_seats">
          <div class="p-5 flex flex-col gap-6">
            <p class=" text-center text-xs">{{ modalLabel }}</p>
              <CheckInput
                class="text-[11px]"
                :label="trads.label_agree_terms"
                v-model="agreeTerms"
              />
            <div class="flex flex-col gap-4">
              <LinkButton class="w-full" @click="$emit('close', false, currentSegmentInfo)">{{ trads.label_back }}</LinkButton>
              <Button @click="() => (modalOpenBySegment[currentSegment] = false)" width="full" :disabled="!currentSegmentInfo.agreeTerms ">
                {{ trads.label_continue }}
              </Button>
            </div>
          </div>
        </InfoSeatModal>
      </div>
    </main>
    <FooterSeatsBar
      :passenger="passenger"
      :segmentCount="trads.label_segment + ' ' + segmentCount"
      :segments="segments"
      :currentSegment="currentSegmentInfo"
      :currentSegmentIndex="currentSegment"
      :initials="initials"
      @selectSegment="handleSelect"
      @save="handleAssignSeat"
      @close="() => $emit('close')"
      @delete="handleDelete"
      :trads="trads"
      :can-save="canSave"
    />
  </section>
</template>
