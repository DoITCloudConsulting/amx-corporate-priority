<script setup>
import {
  ToolWrapper,
  ToolPanel,
  Icon,
  PNRForm,
  EmptyStateIllustration,
  NotificationBar,
  Button,
  SeatsTicket,
  GeneralToast,
} from "am-ui-package";
import { ref, reactive, computed, watch, onMounted } from "vue";
import { getTicketStatus } from "../composables/useTicketStatus";
import axios from "axios";
import { getTranslation } from '@shared/getTranslation'
import SeatsMapLayout from "../Components/SeatsMapLayout.vue";
import { corporatePriorityService } from "../../services/CorporatePriorityService";

const step = ref("panel");
const isLoading = ref(false);
const isFormLoading = ref(false);
const isChangedSeat = ref(false);
const errorModal = ref(false);
const passenger = ref({});
const selected = ref([]);
const legs = ref([]);
const segments = ref([]);
const stationNumber = ref("");
const clid = ref("");
const notificationError = ref(false);
const isToastOpen = ref(true);
const allSeatsAssigned = ref(false);
const isStandBy = ref();
const ticketForm = ref({
  pnr: "",
  numberTicket: "",
  lastname: "",
});

const formatPassengerName = (p = {}) =>
  p.firstName && p.lastName
    ? `${capitalize(p.firstName)} ${capitalize(p.lastName)}`
    : "";

const trads = {
  label_tool_name: getTranslation('tools.corporate-priority.title'),
  label_tool_description: getTranslation('tools.corporate-priority.description'),
  label_need_help: getTranslation('navbar.common.need-help'),
  label_back: getTranslation('common.tools.back'),

  label_name: getTranslation('common.tools.ticket.name'),
  label_status: getTranslation('common.tools.ticket.status'),
  label_origin: getTranslation('common.tools.ticket.origin'),
  label_destiny: getTranslation('common.tools.ticket.destination'),
  label_date: getTranslation('common.tools.ticket.date'),
  label_time: getTranslation('common.tools.ticket.time'),
  label_seat: getTranslation('common.tools.ticket.seat'),
  label_class: getTranslation('common.tools.ticket.class'),
  label_type: getTranslation('common.tools.ticket.type'),

  label_pnr: getTranslation('common.tools.form.AMPNR'),
  label_ticket: getTranslation('common.tools.form.ticket'),
  label_lastname: getTranslation('common.tools.form.lastname'),
  label_corporate: getTranslation('common.tools.ticket.status-seat'),

  label_continue: getTranslation('common.tools.continue'),
  label_confirmated: getTranslation('common.confirmed'),
  label_pending: getTranslation('common.pending'),
  label_confirm: getTranslation('common.confirm'),

  label_consult: getTranslation('common.tools.form.button-consult'),
  label_consulting: getTranslation('common.tools.form.button-consulting'),

  label_panel_title: getTranslation('common.tools.general-information'),
  label_error: getTranslation('common.tools.error'),
  label_no_benefit: getTranslation('common.tools.error.not-corporate'),

  label_no_seats_available: getTranslation('tools.corporate-priority.no-seats-available'),

  label_1_panel: getTranslation('tools.corporate-priority.panel-text-1'),
  label_2_panel: getTranslation('tools.corporate-priority.panel-text-2'),
  label_3_panel: getTranslation('tools.corporate-priority.panel-text-3'),
  label_4_panel: getTranslation('tools.corporate-priority.panel-text-4'),

  label_found_first: getTranslation('tools.corporate-priority.found-first'),
  label_found_last: getTranslation('tools.corporate-priority.found-last'),

  label_empty_top: getTranslation('common.tools.here-related'),
  label_no_reservations: getTranslation('common.tools.no-reservations-registered'),
  label_check_all: getTranslation('common.tools.select-all'),
  label_tickets: getTranslation('common.tools.tickets'),

  label_footer_text: getTranslation('tools.corporate-priority.footer-text'),

  label_am_flight: getTranslation('tools.corporate-priority.am-flight'),
  label_am_preferred: getTranslation('tools.corporate-priority.am-prefered'),
  label_of: getTranslation('common.tools.of'),

  label_standard_seat: getTranslation('tools.corporate-priority.standard-seat'),
  label_priority_landing: getTranslation('tools.corporate-priority.priority-landing'),
  label_priority_ubication: getTranslation('tools.corporate-priority.priority-ubication'),

  label_passenger: getTranslation('common.tools.passenger'),
  label_seats: getTranslation('common.tools.seats'),

  label_next: getTranslation('common.next'),

  // Esta label esta repetida revisar
  label_select_seat: getTranslation('tools.corporate-priority.select-segment'),

  label_segment: getTranslation('common.tools.segment'),
  label_save: getTranslation('tools.corporate-priority.save-leave'),

  label_no_seat: getTranslation('tools.corporate-priority.no-seat'),
  label_select_segment: getTranslation('tools.corporate-priority.select-segment'),

  label_success_toast: getTranslation('tools.corporate-priority.success-corporate'),

  label_invalid_pnr: getTranslation('common.tools.form-errors.invalid-pnr'),
  label_invalid_ticket_number: getTranslation('common.tools.form-errors.invalid-ticket-number'),
  label_invalid_value: getTranslation('common.tools.form-errors.invalid-value'),

  label_condonate: getTranslation('tools.corporate-priority.condonate'),
  label_seat_condonate: getTranslation('tools.corporate-priority.seat-condonate'),
  label_seat_yes_condonate: getTranslation('tools.corporate-priority.seat-yes-condonate'),
  label_seat_no_condonate: getTranslation('tools.corporate-priority.seat-no-condonate'),

  label_seat_no_preferent: getTranslation('tools.corporate-priority.seat-no-preferent'),

  //estaba mal escrito 'labl', ubicar label y corregir
  label_current_preferred: getTranslation('tools.corporate-priority.current-preferred'),

  label_seat_is_preferent: getTranslation('tools.corporate-priority.seat-is-preferent'),

  label_price: getTranslation('tools.corporate-priority.price'),
  label_window: getTranslation('tools.corporate-priority.window'),
  label_aisle: getTranslation('tools.corporate-priority.aisle'),
  label_middle: getTranslation('tools.corporate-priority.middle'),
  label_agree_terms: getTranslation('tools.corporate-priority.agree-terms'),
  label_change_success: getTranslation('tools.corporate-priority.change-success'),
};

const alert = (message) => {
  window.alert(message);
};

console.log(window.location);

const sendForm = async () => {
  isLoading.value = true;
  isFormLoading.value = true;
  segments.value = [];
  notificationError.value = null;
  try {
    const res = await getTicketStatus(ticketForm.value);
    if (!res?.validTicket) {
      notificationError.value =
        res?.error || "No fue posible validar el ticket.";
      return;
    }

    updateReservation(res);
  } catch (err) {
    notificationError.value =
      err?.response?.data?.message || err?.message || "Error inesperado.";
  } finally {
    isLoading.value = false;
    isFormLoading.value = false;
  }
};

const updateReservation = (reservation) => {
  passenger.value = {
    ...reservation.passenger,
    formatedName: formatPassengerName(reservation.passenger),
  };
  legs.value = reservation.segments ?? [];
  isStandBy.value = reservation.isStandBy;
  stationNumber.value = reservation.stationNumber;
  clid.value = reservation.clid;

  segments.value = legs.value.flatMap((leg, i) =>
    (leg.segments ?? []).map((seg) => ({
      ...seg,
      legCode: leg.legCode,
      legIndex: i,
      legEntity: leg.legEntity,
    }))
  );
};

const handleAssignSeats = async () => {
  const toAssign = segments.value
    .map((s, idx) => ({ seg: s, idx }))
    .filter(({ seg }) => !!seg.newSeat);

  if (!toAssign.length) {
    console.log("No hay segmentos con newSeat para asignar.");
    return;
  }
  notificationError.value = false;

  const now = new Date();
  const formatted =
    now.getFullYear() +
    "-" +
    String(now.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(now.getDate()).padStart(2, "0") +
    "T" +
    String(now.getHours()).padStart(2, "0") +
    ":" +
    String(now.getMinutes()).padStart(2, "0") +
    ":" +
    String(now.getSeconds()).padStart(2, "0");

  let anySuccess = false;
  console.log(toAssign);

  const seatAssignPayload =
    corporatePriorityService.prepareSeatAssignmentPayload();

  const response = await corporatePriorityService.assignSeat(seatAssignPayload);
};

const selectedIds = ref(new Set());
const segKey = (s) => s?.segmentID ?? s?.segmentCode;

const legsToMap = computed(() =>
  segments.value.filter((s) => selectedIds.value.has(segKey(s)))
);

const selectedIdsArr = computed(() => Array.from(selectedIds.value));

const handleToggle = (segment, checked) => {
  const key = segKey(segment);
  const next = new Set(selectedIds.value);
  checked ? next.add(key) : next.delete(key);
  selectedIds.value = next;
};

const handleToggleAll = (val) => {
  selectedIds.value = val
    ? new Set(segments.value.map((s) => segKey(s)))
    : new Set();
  console.log(legsToMap.value);
};

const seatMapCache = reactive({});
const seatMapStatus = reactive({});
let seatMapPayload = reactive({});

const buildSeatMapPayload = (seg) => ({
  reservationCode: ticketForm.value.pnr,
  isStandBy: !!isStandBy.value,
  transactionDate: new Date().toISOString().slice(0, 19),
  legCode: seg.legCode,
  legRegion: seg.segmentRegion ?? null,
  windowLegStatus: "MYB",
  segmentCode: seg.segmentCode,
  aircraftType: seg.aircraftType,
  origin: seg.startLocation,
  destination: seg.endLocation,
  marketingCarrier: seg.marketingCarrier,
  operatingCarrier: seg.operatingCarrier,
  operatingFlightCode: seg.flightNumber,
  departureDate: seg.departureDateTime,
  arrivalDate: seg.arrivalDateTime,
  farebasis: (seg.farebasis || "").split("/")[0] || null,
  fareFamily: seg.fareFamily,
  bookingClass: seg.bookingClass,
  bookingCabin: seg.bookingCabin,
  segmentRegion: seg.segmentRegion ?? null,
});

let count = 0;
const ensureSeatMap = async (seg) => {
  const key = segKey(seg);
  if (seatMapStatus[key] === "loading" || seatMapStatus[key] === "ready")
    return;

  seatMapStatus[key] = "loading";
  try {
    const payload = buildSeatMapPayload(seg);
    console.log(payload);

    seatMapPayload = payload;

    console.log(seatMapPayload);
    const { data } = await axios.post(route("get-seat-map"), seatMapPayload);
    console.log(data);

    seatMapCache[key] = data;
    seatMapStatus[key] = "ready";
  } catch (e) {
    console.log(e.message);
    seatMapStatus[key] = "error";
  }
};

const updateSeatMap = (seg, newMap) => {
  const key = segKey(seg);

  seatMapCache[key] = newMap;

  console.log(seatMapStatus);
};

const toUpper = (s) => (s ?? "").toString().toUpperCase();
const sanitizeUpper = (obj) => ({
  ...obj,
  pnr: toUpper(obj.pnr),
  lastname: toUpper(obj.lastname),
  numberTicket: obj.numberTicket,
});

const onUpdateTicketForm = (val) => {
  ticketForm.value = sanitizeUpper(val);
};

function capitalize(word = "") {
  return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
}

const handleSeat = (seat, currentIndexInLegsToMap) => {
  const segView = legsToMap.value[currentIndexInLegsToMap];
  if (!segView) return;

  const key = segKey(segView);
  const i = segments.value.findIndex((s) => segKey(s) === key);

  if (i === -1) return;

  const curr = segments.value[i];

  const shouldUnset = curr.newSeat && curr.newSeat.seatCode === seat.seatCode;

  if (shouldUnset) {
    const { newSeat, ...rest } = curr;
    segments.value[i] = rest;
    isChangedSeat.value = false;
  } else {
    segments.value[i] = { ...curr, newSeat: seat };
    isChangedSeat.value = true;
  }
};

const handleCloseMap = (showToast, segment) => {
  step.value = "form";
  if (showToast) {
    isToastOpen.value = true;
  }

  if (segment) {
    handleToggle(segment, false);
  }
};

onMounted(() => { });

const readyCount = computed(
  () =>
    legsToMap.value.filter((s) => seatMapStatus[segKey(s)] === "ready").length
);

const canContinue = computed(
  () =>
    legsToMap.value.length > 0 && readyCount.value === legsToMap.value.length
);

const continueLabel = computed(() => {
  if (!legsToMap.value.length) return trads.label_continue;
  if (canContinue.value) return trads.label_continue;
  return `Preparando mapas… ${readyCount.value}/${legsToMap.value.length}`;
});

const goToSeats = () => {
  if (!canContinue.value) return;
  if (!clid.value) {
    noCorp.value = true;
    openModal.value = true;
    modalLabel.value = trads.label_no_seats_available;
  } else {
    step.value = "seatsMap";
  }
};

const deleteSelection = (payload) => {
  let key;

  if (typeof payload === "number") {
    const segView = legsToMap.value[payload];
    if (!segView) return;
    key = segKey(segView);
  } else if (typeof payload === "string") {
    key = payload;
  } else if (payload && typeof payload === "object") {
    key = segKey(payload);
  }

  if (!key) return;
  const next = new Set(selectedIds.value);
  next.delete(key);
  selectedIds.value = next;

  const i = segments.value.findIndex((s) => segKey(s) === key);
  if (i !== -1) {
    const { newSeat, ...rest } = segments.value[i];
    segments.value[i] = { ...rest };
  }
  console.log(selectedIds.value.size);

  if (selectedIds.value.size == 0) {
    handleCloseMap();
  }
};

function onUpdateAgreeTerms({ index, value }) {
  const segView = legsToMap.value[index];
  if (!segView) return;

  const key = segKey(segView);
  const i = segments.value.findIndex((s) => segKey(s) === key);
  if (i === -1) return;

  const curr = segments.value[i];
  segments.value[i] = { ...curr, agreeTerms: value };
}

watch(legsToMap, (list) => {
  list.forEach(ensureSeatMap);
});
</script>

<template>
  <section v-if="step !== 'seatsMap'" class="text-[#0B2343]">
    <GeneralToast :is-open="isToastOpen" @close="isToastOpen = false">
      <p class="text-white">
        El beneficio de Corporate Priority se otorgó de forma exitosa.
        <button type="button" class="text-white underline">
          Descargar el PDF
        </button>
      </p>
    </GeneralToast>
    <ToolWrapper class="mb-40" :tool="trads.label_tool_name" :description="trads.label_tool_description" @goBack="
      () => {
        step = 'panel';
      }
    " @handleNeedHelp="
      () => {
        alert('Help is on the way!');
      }
    ">
      <ToolPanel v-if="step === 'panel'" class="mt-[42px] lg:mt-[56px]" :textButton="trads.label_continue"
        :title="trads.label_panel_title" icon="InputTextIcon" @onClick="
          () => {
            step = 'form';
          }
        ">
        <div class="hidden sm:grid grid-cols-2 gap-5 text-[10px] md:text-xs">
          <div class="flex flex-row items-center gap-2">
            <Icon name="SeatSelection" />
            <p>
              {{ trads.label_1_panel }}
            </p>
          </div>
          <div class="flex flex-row items-center gap-2">
            <Icon name="BadSeatSelection" />
            <p>
              {{ trads.label_2_panel }}
            </p>
          </div>
          <div class="flex flex-row items-center gap-2">
            <Icon name="WebCKI" />
            <p>{{ trads.label_3_panel }}</p>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="FlexibleFares" />
            <p>
              {{ trads.label_4_panel }}
            </p>
          </div>
        </div>
        <div class="grid sm:hidden grid-cols-1 gap-5 text-[10px] md:text-xs my-8">
          <div class="flex flex-row items-center gap-2">
            <Icon name="SeatSelection" />
            <p>
              {{ trads.label_1_panel }}
            </p>
          </div>
          <div class="flex flex-row items-center gap-2">
            <Icon name="WebCKI" />
            <p>{{ trads.label_3_panel }}</p>
          </div>
          <div class="flex flex-row items-center gap-2">
            <Icon name="BadSeatSelection" />
            <p>
              {{ trads.label_2_panel }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="FlexibleFares" />
            <p>
              {{ trads.label_4_panel }}
            </p>
          </div>
        </div>
      </ToolPanel>
      <div id="ticket-form" v-else-if="step === 'form'" class="flex flex-col gap-5 mt-10">
        <PNRForm :ticketForm="ticketForm" :trads="trads" @update:ticketForm="
          (val) => {
            ticketForm.value = onUpdateTicketForm(val);
          }
        " @handleSend="sendForm" :isLoading="isFormLoading" />

        <NotificationBar v-if="notificationError" variant="error" @close="() => (notificationError = false)">
          {{ notificationError }}
        </NotificationBar>

        <EmptyStateIllustration v-if="!segments.length" data-cy="empty-ticket-card"
          :description="trads.label_no_reservations" :topText="trads.label_empty_top" :isLoading="isLoading" />
        <SeatsTicket v-if="segments.length" :passenger="passenger" :segments="segments" :selected-ids="selectedIdsArr"
          :checkLabel="trads.label_check_all" :selectAll="true" :trads="trads" :clid="clid"
          @toggle-all="handleToggleAll" @toggle="handleToggle" />
      </div>
    </ToolWrapper>
    <footer
      v-if="step == 'form' && segments.length"
      class="fixed bottom-0 w-full flex justify-center px-16 py-[10px] border-t-2 z-[100] bg-white"
    >
      <div
        class="w-full max-w-[736px] flex flex-col sm:flex-row justify-between items-center"
      >
        <p class="text-xs sm:text-base">
          {{ trads.label_footer_text }}
        </p>
        <Button class="m-5" variant="secondary" size="lg" @click="goToSeats" :disabled="!canContinue">{{ continueLabel
        }}</Button>
      </div>
    </footer>
  </section>

  <Transition enter-active-class="transform transition ease-out duration-500"
    enter-from-class="translate-y-full opacity-0" enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transform transition ease-in duration-500" leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-full opacity-0">
    <SeatsMapLayout v-if="step === 'seatsMap'" :isStandBy="isStandBy" :formPayload="ticketForm"
      :seatMapPayload="seatMapPayload" @updateSeatMap="updateSeatMap" @close="handleCloseMap" @addSeat="handleSeat"
      @delete="deleteSelection" @update-agree-terms="onUpdateAgreeTerms" :assignSeat="handleAssignSeats" :trads="trads"
      :passenger="passenger" :seatsMapInfo="seatMapCache" :segments="legsToMap" :allSeatsAssigned="allSeatsAssigned"
      @updateReservation="updateReservation" :isChangedSeat="isChangedSeat" />
  </Transition>
</template>

<style scoped>
:deep(.uppercase-input input) {
  text-transform: uppercase;
}
</style>
