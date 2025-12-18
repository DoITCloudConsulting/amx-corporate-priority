<script setup>
import { ref, reactive, computed, watch, watchEffect, onMounted } from "vue";
import { CheckInput, Button, LinkButton } from "am-ui-package";
import SeatsMap from "./SeatsMap.vue";
import { useSeatModalStage } from "../composables/useSeatModalStage";
import { corporatePriorityService } from "../../services/CorporatePriorityService";
import CircleLoader from "./CircleLoader.vue";
import { getTicketStatus } from "../composables/useTicketStatus";
import FooterSeatsBar from "./FooterSeatsBar.vue";
import SeatModal from "./SeatModal.vue";
import InfoSeatModal from "./InfoSeatModal.vue";

const props = defineProps({
  seatsMapInfo: { type: Array, default: () => [] },
  segments: { type: Array, default: () => [] },
  passenger: { type: Object, default: () => ({}) },
  trads: { type: Object, default: () => ({}) },
  allSeatsAssigned: { type: Boolean, default: true },
  seatMapPayload: { type: Object, default: {} },
  isStandBy: { type: Boolean, required: true },
  formPayload: { type: Object, default: {}, required: true },
  assignSeat: {
    type: Function,
    required: true,
  },
});

const aircraftType = {
  "79M": ["A", "B", "C", "|", "D", "E", "F"],
  "7M9": ["A", "B", "C", "|", "D", "E", "F"],
  "7M8": ["A", "B", "C", "|", "D", "E", "F"],
  E90: ["A", "B", "|", "C", "D"],
  "E-190": ["A", "B", "|", "C", "D"],
  "7S8": ["A", "B", "|", "C", "D"],
  789: ["A", "B", "C", "|", "D", "E", "F", "|", "G", "H", "J"],
};

const emit = defineEmits([
  "close",
  "addSeat",
  "delete",
  "update-agree-terms",
  "updateSeatMap",
  "updateReservation",
]);

const isAssigningSeat = ref(false);
const isProcessingCondonation = ref(false);
const currentSegment = ref(0);
const modalOpenBySegment = reactive({});
const initialHasAnySeatByIndex = reactive({});
const canContinueWithNextSegment = ref(false);

const modalLabel = ref();

const currentSegmentInfo = computed(() => {
  console.log(props.segments);
  return props.segments[currentSegment.value] || {};
});
const currentSeatMapRaw = computed(() => {
  return props.seatsMapInfo[currentSegmentInfo.value.segmentID] || null;
});

onMounted(async () => {
  const currentSegment = { ...currentSegmentInfo.value };

  corporatePriorityService.reservationSeatMap = { ...props.seatsMapInfo };
  corporatePriorityService.currentSegment = { ...currentSegmentInfo.value };
  corporatePriorityService.reservation = {
    ...props.formPayload,
    passenger: props.passenger,
    isStandBy: props.isStandBy,
  };

  setStageNameBySeatTypeAndStatus(currentSegment);
});

watch(currentSegmentInfo, (newValue) => {
  setStageNameBySeatTypeAndStatus(newValue);

  corporatePriorityService.currentSegment = { ...newValue };
});

const setStageNameBySeatTypeAndStatus = (segment) => {
  // If the segment has a seat already assigned
  if (segment.seats.length) {
    const currentSeatAssigned = corporatePriorityService.findSeat(
      segment.segmentID,
      segment.seats[0].seatCode
    );

    if (currentSeatAssigned.type === "PREFERRED") {
      if (segment.seats[0].status === "PAID" || segment.newSeat) {
        // If seat is type preferred and already paid
        setStageName("preferent");
      } else if (segment.seats[0].status !== "PAID") {
        // If seat is type preference but is still unpaid
        setStageName("condonate");
      }
    } else {
      // If seat is not type preferent
      setStageName("noPreferent");
    }
  } else if (segment?.newSeat?.seatCode !== undefined) {
    // If the segment is found with an unassigned seat
    setStageName("condonate");
  } else {
    // If the segment is found with an unassigned seat
    setStageName("");
  }
};

const currentSeatMap = computed(() => {
  const raw = currentSeatMapRaw.value;

  if (raw?.seatMap || raw.length) {
    const map = (raw?.seatMap || raw).filter(
      (seat) => seat.type === "PREFERRED"
    );
    console.log(map);
    return map;
  }

  return [];
});

const agreeTerms = computed({
  get: () => !!currentSegmentInfo.value?.agreeTerms,
  set: (val) => {
    emit("update-agree-terms", { index: currentSegment.value, value: val });
  },
});

const corporateSeatCode = computed(
  () => currentSegmentInfo.value?.seats?.[0]?.seatCode || null
);

const hasCorporateSeatInMap = computed(() => {
  const code = corporateSeatCode.value;
  const map = currentSeatMap.value;
  if (!code || !map?.length) return false;
  return map.some((s) => s?.seatCode === code);
});

const initModalStates = (segments = []) => {
  segments.forEach((seg, idx) => {
    const key = idx;
    if (modalOpenBySegment[key] === undefined) {
      modalOpenBySegment[key] = !!(seg?.seats?.length || seg?.newSeat);
    }
    // guardamos si originalmente tenía asientos
    if (initialHasAnySeatByIndex[key] === undefined) {
      initialHasAnySeatByIndex[key] = !!seg?.seats?.length;
    }
  });
};

const newSeatExists = computed(() => !!currentSegmentInfo.value?.newSeat);

const assignSeat = async () => {
  try {
    isAssigningSeat.value = true;

    const payload = corporatePriorityService.prepareSeatAssignmentPayload();

    const response = await corporatePriorityService.assignSeat(payload);
    const seatAssigned = currentSegmentInfo.value.seats[0] ?? [];

    if (response.status === 200 && response.data.length) {
      const seatMapPayload = corporatePriorityService.prepareSeatMapPayload();

      const newMap = await corporatePriorityService.getSeatMap(seatMapPayload);

      emit("updateSeatMap", currentSegmentInfo.value, newMap);

      setStageName("success");
    }
  } catch (error) {
    console.log(error);
  } finally {
    isAssigningSeat.value = false;
  }
};

const modalIsOpenForCurrent = computed(() => {
  const key = currentSegment.value;
  console.log(modalOpenBySegment[key]);
  return !!modalOpenBySegment[key];
});

watchEffect(() => {
  modalLabel.value = hasCorporateSeatInMap.value
    ? props.trads.label_seat_is_preferent
    : props.trads.label_seat_no_preferent;
});

const seatType = ref(props.trads.label_am_preferred);
const segmentCount = computed(
  () =>
    `${currentSegment.value + 1} ${props.trads.label_of} ${
      props.segments.length
    }`
);
const initials = computed(() => {
  const p = props.passenger || {};
  return p.firstName && p.lastName ? p.firstName[0] + p.lastName[0] : "";
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
  console.log(seat);
  if (!seat) return;
  emit("addSeat", seat, currentSegment.value);
};

const handleAsignSeat = () => {
  const anyChange = props.segments.some((seg) => isSeatChange(seg));

  if (!anyChange || !currentSegmentInfo.value?.agreeTerms) {
    emit("close");
    return;
  }
  emit("asignSeat");
};

const handleDelete = (s) => {
  console.log(s);
  emit("delete", s);
};

const initialHadAnySeat = computed(
  () => initialHasAnySeatByIndex[currentSegment.value] ?? false
);

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

  const originalFirst =
    Array.isArray(seg.seats) && seg.seats.length
      ? seg.seats[0]?.seatCode
      : null;
  if (!originalFirst) return true;

  if (originalFirst !== newCode) return true;

  return false;
};

watch(props.segments, () => {
  console.log(props.segments);
});

watch(
  () => props.segments,
  (list) => {
    initModalStates(list || []);
  },
  { immediate: true }
);

const condonateSeats = async () => {
  try {
    isProcessingCondonation.value = true;

    const response = await corporatePriorityService.condonate();

    console.log(response);

    if (response.message === "Booking processed successfully") {
      const { pnr, passenger, numberTicket } =
        corporatePriorityService.reservation;

      const reservation = await getTicketStatus({
        pnr,
        lastname: passenger.lastName,
        numberTicket,
      });

      console.log(reservation);

      const { seatMapsBySegmentId, segments } =
        await corporatePriorityService.getAllSeatMaps(reservation);

      segments.forEach((segment) => {
        if (segment.seats.length) {
          const segmentId = segment.segmentID;
          // Seat from segment
          const seat = segment.seats[0];
          // Seat from seat map
          const mapSeat = corporatePriorityService.findSeat(
            segmentId,
            seat.seatCode,
            seatMapsBySegmentId
          );

          if (mapSeat.type === "PREFERRED" && seat.status !== "PAID") {
            console.log(
              "No se pudieron condonar todos los asientos disponibles"
            );
            return;
          } else {
            console.log("Todos los asientos fueron condonados correctamente");
          }
        }
      });
      corporatePriorityService.downloadPdf(segments);

      emit("updateReservation", reservation);
      emit("close");
    }
  } catch (error) {
    console.log(error);
  } finally {
    isProcessingCondonation.value = false;
  }
};

const {
  current: currentModalStage,
  setStageName,
  stageName,
} = useSeatModalStage({
  trads: props.trads,
});
</script>

<template>
  <section
    class="w-screen bg-[#F2F8FC] items-center grid md:grid-cols-[400px_1fr] lg:grid-cols-2 grid-rows-[1fr_auto]"
  >
    <div
      class="relative w-full h-full flex justify-center md:justify-end gap-5 px-5 row-start-1 row-end-3 col-start-1"
    >
      <SeatsMap
        :initials="initials"
        :data="currentSeatMap"
        @select="handleSelectSeat"
        :colsPattern="aircraftType[currentSegmentInfo.aircraftType]"
        :currentSegment="currentSegmentInfo"
        :characteristics="seatsCharacteristics"
      />
    </div>
    <div
      class="pl-5 md:pl-0 pr-5 md:pr-0 w-full h-full lg:h-auto lg:w-auto md:max-w-md flex flex-col justify-center"
    >
      <div class="hidden md:flex flex-col w-[280px] md:w-full mb-5">
        <p class="mb-3 font-GarnettSemibold text-sm text-amDarkGray">
          Vuelo AM
        </p>
        <SeatModal
          class="hidden sm:flex"
          :seatType="seatType"
          :characteristics="seatsCharacteristics"
        />
      </div>

      <div
        v-if="stageName"
        class="fixed md:relative bg-black bg-opacity-50 md:bg-transparent left-0 top-0 w-full h-screen md:h-auto z-[100] flex flex-col items-center justify-center"
      >
        <InfoSeatModal
          v-if="stageName === 'condonate'"
          @close="setStageName('')"
          class="z-50 flex flex-col gap-[15px]"
          :title="currentModalStage.title"
        >
          <div class="p-6 md:p-8 flex flex-col gap-6">
            <p class="text-center text-sm">{{ currentModalStage.mainText }}</p>

            <div class="flex flex-col lg:flex-row gap-[25px] justify-center">
              <Button
                @click="$emit('close', false, currentSegmentInfo)"
                size="lg"
                variant="secondary"
                width="full"
                :disabled="isAssigningSeat"
                class="w-full px-5 text-sm border rounded font-GarnettSemibold border-amBlueInnovation"
              >
                {{ currentModalStage.secondaryButton.text }}
              </Button>
              <Button
                @click="assignSeat"
                size="lg"
                width="full"
                :disabled="isAssigningSeat"
              >
                <div
                  v-if="isAssigningSeat"
                  class="w-full h-full flex items-center justify-center"
                >
                  <CircleLoader size="xs" />
                </div>

                <span v-else>
                  {{ currentModalStage.primaryButton.text }}
                </span>
              </Button>
            </div>
          </div>
        </InfoSeatModal>

        <InfoSeatModal
          v-if="stageName === 'preferent' || stageName === 'noPreferent'"
          @close="setStageName('')"
          class="z-50 flex flex-col gap-[15px] w-full"
          :title="currentModalStage.title"
        >
          <div class="p-6 md:p-8 flex flex-col gap-6">
            <p class="text-center text-sm">{{ currentModalStage.mainText }}</p>
            <CheckInput
              class="text-xs"
              :label="currentModalStage.checkbox.text"
              v-model="currentModalStage.checkbox.agreeTerms"
            />
            <div class="flex flex-col gap-4">
              <LinkButton
                class="w-full"
                @click="$emit('close', false, currentSegmentInfo)"
                >{{ trads.label_back }}</LinkButton
              >
              <Button
                @click="assignSeat"
                width="full"
                :disabled="
                  !currentModalStage.checkbox.agreeTerms || isAssigningSeat
                "
              >
                <div
                  v-if="isAssigningSeat"
                  class="w-full h-full flex items-center justify-center"
                >
                  <CircleLoader size="xs" />
                </div>

                <span v-else>
                  {{ trads.label_continue }}
                </span>
              </Button>
            </div>
          </div>
        </InfoSeatModal>

        <InfoSeatModal
          v-if="stageName === 'success'"
          @close="setStageName('')"
          class="z-50 flex flex-col gap-[15px] w-full"
          :title="trads.label_seats"
        >
          <div class="p-6 md:p-8 flex flex-col gap-6">
            <p class="text-center text-sm">{{ currentModalStage.mainText }}</p>
          </div>
        </InfoSeatModal>
      </div>
    </div>
    <FooterSeatsBar
      :passenger="passenger"
      :segmentCount="trads.label_segment + ' ' + segmentCount"
      :segments="segments"
      :currentSegment="currentSegmentInfo"
      :currentSegmentIndex="currentSegment"
      :initials="initials"
      @selectSegment="handleSelect"
      @save="condonateSeats"
      @close="() => $emit('close')"
      @delete="handleDelete"
      :trads="trads"
      :can-save="canSave"
    />
  </section>
</template>
