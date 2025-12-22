<script setup>
import { ref, reactive, computed, watch, watchEffect, onMounted } from "vue";
import {
  CheckInput,
  Button,
  LinkButton,
  Overlay,
  LoaderSpinner,
} from "am-ui-package";

import SeatsMap from "./SeatsMap.vue";
import FooterSeatsBar from "./FooterSeatsBar.vue";
import SeatModal from "./SeatModal.vue";
import InfoSeatModal from "./InfoSeatModal.vue";

import { useSeatModalStage } from "../composables/useSeatModalStage";
import { useSegmentSeatState } from "../composables/useSegmentSeatState";
import { getTicketStatus } from "../composables/useTicketStatus";

import { corporatePriorityService } from "../../services/CorporatePriorityService";

const props = defineProps({
  seatsMapInfo: { type: Object, default: {} },
  segments: { type: Array, default: () => [] },
  passenger: { type: Object, default: () => ({}) },
  trads: { type: Object, default: () => ({}) },
  allSeatsAssigned: { type: Boolean, default: true },
  seatMapPayload: { type: Object, default: {} },
  isStandBy: { type: Boolean, required: true },
  formPayload: { type: Object, default: {}, required: true },
  isChangedSeat: { type: Boolean },
});

const emit = defineEmits([
  "close",
  "addSeat",
  "delete",
  "update-agree-terms",
  "updateSeatMap",
  "updateReservation",
  "openToast",
]);

const aircraftType = {
  "79M": ["A", "B", "C", "|", "D", "E", "F"],
  "7M9": ["A", "B", "C", "|", "D", "E", "F"],
  "7M8": ["A", "B", "C", "|", "D", "E", "F"],
  E90: ["A", "B", "|", "C", "D"],
  "E-190": ["A", "B", "|", "C", "D"],
  "7S8": ["A", "B", "|", "C", "D"],
  789: ["A", "B", "C", "|", "D", "E", "F", "|", "G", "H", "J"],
};

/* ======================================================
 * 4️⃣ Estado local
 * ==================================================== */
const currentSegment = ref(0);

const isAssigningSeat = ref(false);
const isProcessingCondonation = ref(false);
const saveLoader = ref(false);

const modalOpenBySegment = reactive({});
const initialHasAnySeatByIndex = reactive({});
const modalLabel = ref();

const seatType = ref(props.trads.label_am_preferred);

const seatsCharacteristics = ref([
  props.trads.label_standard_seat,
  props.trads.label_priority_landing,
  props.trads.label_priority_ubication,
]);

const assignedSegments = ref(new Set());

const currentSegmentInfo = computed(
  () => props.segments[currentSegment.value] || {}
);
const hasSelectedSeat = ref(false);

/* ======================================================
 * 5️⃣ Composables
 * ==================================================== */
const {
  agreeTermsBySegment,
  seatAssignedBySegment,
  initAgreeTerms,
  initSeatAssigned,
  agreeTermsForCurrentSegment,
  assignedSeatForCurrentSegment,
} = useSegmentSeatState(currentSegment);

const {
  current: currentModalStage,
  setStageName,
  stageName,
} = useSeatModalStage({
  trads: props.trads,
  close: () => emit("close", false, currentSegmentInfo.value),
});

/* ======================================================
 * 6️⃣ Computed
 * ==================================================== */

const currentSeatMapRaw = computed(
  () => props.seatsMapInfo[currentSegmentInfo.value.segmentID] || null
);

const currentSeatMap = computed(() => {
  const raw = currentSeatMapRaw.value;
  if (!raw) return [];

  return (raw.seatMap || raw).filter((seat) => seat.type === "PREFERRED");
});

const isFooterDisabled = computed(() => {
  return isAssigningSeat.value;
});

const corporateSeatCode = computed(
  () => currentSegmentInfo.value?.seats?.[0]?.seatCode || null
);

const hasCorporateSeatInMap = computed(() => {
  if (!corporateSeatCode.value || !currentSeatMap.value.length) return false;
  return currentSeatMap.value.some(
    (s) => s.seatCode === corporateSeatCode.value
  );
});

const newSeatExists = computed(() => !!currentSegmentInfo.value?.newSeat);

const initialHadAnySeat = computed(
  () => initialHasAnySeatByIndex[currentSegment.value] ?? false
);

const isCorporateSegment = computed(() => {
  const s = currentSegmentInfo.value ?? {};
  return Boolean(s.clid || s.isCorporate || s.corporate);
});

const isCurrentSegmentAssigned = computed(() => {
  return assignedSegments.value.has(currentSegmentInfo.value.segmentID);
});

const isMobile = computed(() => window.innerWidth < 768);

/* ======================================================
 * 9️⃣ Handlers
 * ==================================================== */
const setStageNameBySeatTypeAndStatus = (segment) => {
  if (segment?.seats?.length) {
    const seat = corporatePriorityService.findSeat(
      segment.segmentID,
      segment.seats[0].seatCode
    );

    if (seat.type === "PREFERRED") {
      setStageName(
        segment.seats[0].status === "PAID" || segment.newSeat
          ? "preferent"
          : "condonate"
      );
    } else {
      setStageName("noPreferent");
    }
  } else if (segment?.newSeat?.seatCode !== undefined) {
    setStageName("condonate");
  } else {
    setStageName("");
  }

  console.log(stageName);
  console.log(currentModalStage);
};

const initModalStates = (segments = []) => {
  segments.forEach((seg, idx) => {
    modalOpenBySegment[idx] ??= !!(seg?.seats?.length || seg?.newSeat);
    initialHasAnySeatByIndex[idx] ??= !!seg?.seats?.length;
  });
};

const handleSelect = (index) => {
  currentSegment.value = index === props.segments.length ? 0 : index;
};

const handleSelectSeat = (seat) => {
  if (!seat) return;
  hasSelectedSeat.value = true;
  emit("addSeat", seat, currentSegment.value);
};

const handleDelete = (seat) => emit("delete", seat);

const assignSeat = async () => {
  try {
    isAssigningSeat.value = true;

    const payload = corporatePriorityService.prepareSeatAssignmentPayload();
    const response = await corporatePriorityService.assignSeat(payload);

    if (response.status === 200 && response.data.length) {
      const seatMapPayload = corporatePriorityService.prepareSeatMapPayload();
      const newMap = await corporatePriorityService.getSeatMap(seatMapPayload);

      props.segments.forEach((seg) => {
        if (seg.newSeat) {
          assignedSegments.value.add(seg.segmentID);
        }
      });
      assignedSeatForCurrentSegment.value = true;

      console.log("ASsign", corporatePriorityService.reservation);
      const { pnr, passenger, numberTicket } =
        corporatePriorityService.reservation;

      const reservation = await getTicketStatus({
        pnr,
        lastname: passenger.lastName,
        numberTicket,
      });

      console.log("ASsign 2", corporatePriorityService.reservation);

      emit("updateSeatMap", currentSegmentInfo.value, newMap);
      emit("updateReservation", reservation);
      setStageName("success");
    }
  } finally {
    isAssigningSeat.value = false;
  }
};

watchEffect(() => {
  modalLabel.value = hasCorporateSeatInMap.value
    ? props.trads.label_seat_is_preferent
    : props.trads.label_seat_no_preferent;
});

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

const canSave = computed(() => {
  if (isCorporateSegment.value && !initialHadAnySeat.value) {
    return agreeTermsForCurrentSegment.value;
  }
  if (newSeatExists.value) {
    return agreeTermsForCurrentSegment.value;
  }
  if (newSeatExists.value && initialHadAnySeat.value) {
    return true;
  }

  return !!currentSegmentInfo.value?.agreeTerms;
});

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
  saveLoader.value = true;
  try {
    isProcessingCondonation.value = true;

    const response = await corporatePriorityService.condonate();
    if (response.message !== "Booking processed successfully") return;

    console.log("Condonation", corporatePriorityService.reservation);

    const { pnr, passenger, numberTicket } =
      corporatePriorityService.reservation;

    console.log("Condonation 2", corporatePriorityService.reservation);

    const reservation = await getTicketStatus({
      pnr,
      lastname: passenger.lastName,
      numberTicket,
    });

    const { segments } = await corporatePriorityService.getAllSeatMaps(
      reservation
    );

    const casePayload = corporatePriorityService.prepareCasePayload();

    await corporatePriorityService.createCase(casePayload);

    corporatePriorityService.preparePdfDownloadPayload(segments);
    emit("updateReservation", reservation);
    emit("close");
    emit("openToast", true, { variant: "success" });
  } catch (err) {
    const payload = corporatePriorityService.prepareCasePayload({
      case: {
        status: "Escalado",
      },
    });
    const caseRegistered = await corporatePriorityService.createCase(payload);
    console.log(caseRegistered);

    const caseNumber = caseRegistered.CaseNumber;

    emit("openToast", true, {
      variant: "error",
      stage: "BOOKING_CONDONATION_UNSUCCESFULLY",
      text: `No es posible otorgar de momento el beneficio, contacta a tu centro de atención telefónica con el número de caso ${caseNumber}.`,
    });
  } finally {
    isProcessingCondonation.value = false;
    saveLoader.value = false;
  }
};

/* ======================================================
 * 7️⃣ Watchers
 * ==================================================== */
watch(() => props.segments, initAgreeTerms, { immediate: true });
watch(() => props.segments, initSeatAssigned, { immediate: true });

watch(
  agreeTermsBySegment,
  () => {
    props.segments.forEach((s, i) => (s.agreeTerms = agreeTermsBySegment[i]));
  },
  { deep: true }
);

watch(
  seatAssignedBySegment,
  () => {
    props.segments.forEach(
      (s, i) => (s.seatAsigned = seatAssignedBySegment[i])
    );
  },
  { deep: true }
);

watch(currentSegmentInfo, (segment) => {
  setStageNameBySeatTypeAndStatus(segment);
  corporatePriorityService.currentSegment = { ...segment };

  const { isAnySeatAvailable } = corporatePriorityService;

  if (!isAnySeatAvailable(currentSeatMap.value)) {
    emit("openToast", true, {
      variant: "error",
      stage: "NO_SEATS_AVAILABLES",
    });
    const payload = corporatePriorityService.prepareCasePayload();
    corporatePriorityService.createCase(payload);
    return;
  }
});

watch(() => props.segments, initModalStates, { immediate: true });

watch(currentSegment, () => {
  hasSelectedSeat.value = false;
});

watchEffect(() => {
  modalLabel.value = hasCorporateSeatInMap.value
    ? props.trads.label_seat_is_preferent
    : props.trads.label_seat_no_preferent;
});

/* ======================================================
 * 8️⃣ Lifecycle
 * ==================================================== */
onMounted(async () => {
  const currentSegment = { ...currentSegmentInfo.value };

  const { isAnySeatAvailable } = corporatePriorityService;

  if (!isAnySeatAvailable(currentSeatMap.value)) {
    emit("openToast", true, {
      variant: "error",
      stage: "NO_SEATS_AVAILABLES",
    });
    const payload = corporatePriorityService.prepareCasePayload();

    corporatePriorityService.createCase(payload);
    return;
  }

  corporatePriorityService.reservationSeatMap = { ...props.seatsMapInfo };
  corporatePriorityService.currentSegment = { ...currentSegmentInfo.value };

  console.log(corporatePriorityService.reservation);

  const corporate = corporatePriorityService.reservation.corporate;
  const stationNumber = corporatePriorityService.reservation.stationNumber;
  corporatePriorityService.reservation = {
    ...props.formPayload,
    passenger: props.passenger,
    isStandBy: props.isStandBy,
    corporate,
    stationNumber,
  };

  console.log(corporatePriorityService.reservation);

  setStageNameBySeatTypeAndStatus(currentSegment);
});
</script>

<template>
  <section
    class="w-screen bg-[#F2F8FC] items-center grid md:grid-cols-[400px_1fr] lg:grid-cols-2 grid-rows-[1fr_auto]"
  >
    <Overlay :isOpen="saveLoader" class="fixed z-[500]">
      <LoaderSpinner size="150" />
    </Overlay>
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
        v-if="stageName && (!isMobile || hasSelectedSeat)"
        class="fixed md:relative bg-black bg-opacity-50 md:bg-transparent left-0 top-0 w-full h-screen md:h-auto z-[90] flex flex-col items-center justify-center"
      >
        <InfoSeatModal
          v-if="!assignedSeatForCurrentSegment && stageName === 'condonate'"
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
                  <LoaderSpinner size="30" />
                </div>

                <span v-else>
                  {{ currentModalStage.primaryButton.text }}
                </span>
              </Button>
            </div>
          </div>
        </InfoSeatModal>

        <InfoSeatModal
          v-if="
            !assignedSeatForCurrentSegment &&
            (stageName === 'preferent' || stageName === 'noPreferent')
          "
          @close="setStageName('')"
          class="z-50 flex flex-col gap-[15px] w-full"
          :title="currentModalStage.title"
        >
          <div class="p-6 md:p-8 flex flex-col gap-6">
            <p class="text-center text-sm">{{ currentModalStage.mainText }}</p>
            <CheckInput
              class="text-xs"
              :label="currentModalStage.checkbox.text"
              v-model="agreeTermsForCurrentSegment"
            />
            <div class="flex flex-col gap-4">
              <LinkButton
                class="w-full"
                @click="currentModalStage.backButton.action"
                >{{ trads.label_back }}</LinkButton
              >
              <Button
                @click="assignSeat"
                width="full"
                :disabled="
                  !agreeTermsForCurrentSegment ||
                  !isChangedSeat ||
                  isAssigningSeat
                "
              >
                <div
                  v-if="isAssigningSeat"
                  class="w-full h-full flex items-center justify-center"
                >
                  <LoaderSpinner size="30" />
                </div>

                <span v-else>
                  {{ trads.label_continue }}
                </span>
              </Button>
            </div>
          </div>
        </InfoSeatModal>

        <InfoSeatModal
          v-if="stageName === 'success' || assignedSeatForCurrentSegment"
          @close="setStageName('')"
          class="z-50 flex flex-col gap-[15px] w-full"
          :title="trads.label_seats"
        >
          <div class="p-6 md:p-8 flex flex-col gap-6">
            <p class="text-center text-sm">{{ trads.label_change_success }}</p>
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
      :can-save="canSave && !isFooterDisabled"
      :isAssigned="isCurrentSegmentAssigned"
    />
  </section>
</template>
