<script setup>
import {
  ToolWrapper,
  ToolPanel,
  Icon,
  PNRForm,
  EmptyStateIllustration,
  NotificationBar,
  Button,
  CheckTicket,
  SeatsMapLayout,
  GeneralToast
} from "am-ui-package";
import { ref, reactive, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { getTicketStatus } from "../composables/useTicketStatus";
import { getTranslation } from '@shared/getTranslation'

const page = usePage();
console.log(getTranslation('enums.view-more'));


const step = ref("panel");
const isLoading = ref(false);
const passenger = ref({});
const legs = ref([]);
const segments = ref([]);
const stationNumber = ref("");
const clid = ref("");
const notificationError = ref(false);
const isToastOpen = ref(false);
const isStandBy = ref()
const ticketForm = ref({
  pnr: "",
  numberTicket: "",
  lastname: "",
});

const formatPassengerName = (p = {}) =>
  p.firstName && p.lastName
    ? `${capitalize(p.firstName)} ${capitalize(p.lastName)}`
    : '';


const trads = {
  label_tool_name: "Corportate Priority",
  label_tool_description: "Aquí podras solicitar el waiver de asientos para tu reserva",
  label_need_help: "¿Necesitas ayuda",
  label_name: "Nombre",
  label_status: "Estatus",
  label_origin: "Origen",
  label_destiny: "Destino",
  label_date: "Fecha",
  label_time: "Hora",
  label_seat: "Asiento",
  label_class: "Clase",
  label_type: "Tipo",
  label_pnr: "AM PNR",
  label_ticket: "Ticket",
  label_lastname: "Apellido (s)",
  label_corporate: "Corporativo",
  label_continue: "Continuar",
  label_consult: "Consultar",
  label_consulting: "Consultando",
  label_panel_title: "Información general",
  label_1_panel: "El beneficiario solo es aplicable para asientos preferenciales.",
  label_2_panel: "En caso de no contar con asientos preferentes disponibles no es posible otorgar el beneficio.",
  label_3_panel: "Cualquier uso indebido este sujeto a débito.",
  label_4_panel: "Si cuentas con un asiento pagado previamente no se realizará ningún reembolso por este medio.",
  label_found_first: "Se encuentran ",
  label_found_last: " en esta reservación.",
  label_empty_top: "Aquí podrá visualizar los boletos relacionados a su reservación",
  label_no_reservations: "No hay reservaciones registradas.",
  label_check_all: "Seleccionar todos",
  label_tickets: "boletos",
  label_footer_text: "Continuar con la asignación del asiento",
  label_am_flight: "Vuelo AM",
  label_am_preferred: "Preferred",
  label_of: "de",
  label_standard_seat: "Asiento estándar",
  label_priority_landing: "Desembarque prioritario",
  label_priority_ubication: "Ubicación prioritaria",
  label_passenger: "Asiento estándar",
  label_seat: "Asiento",
  label_next: "Siguiente",
  label_select_seat: "Selecciona asiento para este pasajero",
  label_segment: "Segmento",
  label_save: "Guardar y salir",
  label_no_seat: "Sin asiento",
  label_select_segment: "Selecciona asiento para este pasajero",

};


const alert = (message) => {
  window.alert(message);
};

const sendForm = async () => {
  isLoading.value = true;
  notificationError.value = null;
  try {
    const res = await getTicketStatus(ticketForm.value);
    if (!res?.validTicket) {
      notificationError.value = res?.error || 'No fue posible validar el ticket.';
      return;
    }
    console.log(res);
    
    passenger.value = {
      ...res.passenger,
      formatedName: formatPassengerName(res.passenger),
    };
    legs.value = res.segments ?? [];
    isStandBy.value = !!res.isStandBy;
    stationNumber.value = res.stationNumber;
    clid.value = res.clid;

    segments.value = legs.value.flatMap((leg, i) =>
      (leg.segments ?? []).map(seg => ({ ...seg, legCode: leg.legCode, legIndex: i }))
    );

    segments.value.forEach(ensureSeatMap);

  } catch (err) {
    notificationError.value = err?.response?.data?.message || err?.message || 'Error inesperado.';
  } finally {
    isLoading.value = false;
  }
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
  selectedIds.value = val ? new Set(segments.value.map(s => segKey(s))) : new Set();
  console.log(legsToMap.value);
  
};

const seatMapCache = reactive({});
const seatMapStatus = reactive({});

const buildSeatMapPayload = (seg) => ({
  reservationCode: ticketForm.value.pnr,
  isStandBy: !!isStandBy.value,
  transactionDate: new Date().toISOString().slice(0,19),
  legCode: seg.legCode,
  legRegion: seg.segmentRegion ?? null,
  windowLegStatus: 'MYB',
  segmentCode: seg.segmentCode,
  aircraftType: seg.aircraftType,
  origin: seg.startLocation,
  destination: seg.endLocation,
  marketingCarrier: seg.marketingCarrier,
  operatingCarrier: seg.operatingCarrier,
  operatingFlightCode: seg.flightNumber,
  departureDate: seg.departureDateTime,
  arrivalDate: seg.arrivalDateTime,
  farebasis: (seg.farebasis || '').split('/')[0] || null,
  fareFamily: seg.fareFamily,
  bookingClass: seg.bookingClass,
  bookingCabin: seg.bookingCabin,
  segmentRegion: seg.segmentRegion ?? null,
});

const ensureSeatMap = async (seg) => {
  const key = segKey(seg);
  if (seatMapStatus[key] === 'loading' || seatMapStatus[key] === 'ready') return;

  seatMapStatus[key] = 'loading';
  try {
    const payload = buildSeatMapPayload(seg);
    const { data } = await axios.post(route('get-seat-map'), payload);
    seatMapCache[key] = data;
    seatMapStatus[key] = 'ready';
  } catch (e) {
    seatMapStatus[key] = 'error';
  }
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
  const segView = legsToMap.value[currentIndexInLegsToMap]
  if (!segView) return

  const key = segKey(segView)
  const i = segments.value.findIndex(s => segKey(s) === key)
  if (i === -1) return

  const curr = segments.value[i]

  const shouldUnset =
    curr.newSeat && curr.newSeat.seatCode === seat.seatCode

  if (shouldUnset) {
    const { newSeat, ...rest } = curr
    segments.value[i] = rest
  } else {
    segments.value[i] = { ...curr, newSeat: seat }
  }
}


const handleCloseMap = (showToast) => {
  step.value = "form";
  if (showToast) {
    isToastOpen.value = true;
  }
}

const readyCount = computed(() =>
  legsToMap.value.filter(s => seatMapStatus[segKey(s)] === 'ready').length
);

const canContinue = computed(() =>
  legsToMap.value.length > 0 &&
  readyCount.value === legsToMap.value.length
);

const continueLabel = computed(() => {
  if (!legsToMap.value.length) return trads.label_continue;
  if (canContinue.value) return trads.label_continue;
  return `Preparando mapas… ${readyCount.value}/${legsToMap.value.length}`;
});

const goToSeats = () => {
  if (!canContinue.value) return;
  step.value = 'seatsMap';
};

const deleteSelection = (payload) => {
  let key;

  if (typeof payload === 'number') {
    const segView = legsToMap.value[payload];
    if (!segView) return;
    key = segKey(segView);
  } else if (typeof payload === 'string') {
    key = payload;
  } else if (payload && typeof payload === 'object') {
    key = segKey(payload);
  }

  if (!key) return;
  const next = new Set(selectedIds.value);
  next.delete(key);
  selectedIds.value = next;

  const i = segments.value.findIndex(s => segKey(s) === key);
  if (i !== -1) {
    const { newSeat, ...rest } = segments.value[i];
    segments.value[i] = { ...rest }; 
  }
  console.log(selectedIds.value.size);
  
  if (selectedIds.value.size == 0) {
    handleCloseMap()
  }
  
};

watch(legsToMap, (list) => {
  list.forEach(ensureSeatMap);
}, { immediate: true });
</script>

<template>
<section v-if="step !== 'seatsMap'" class="text-[#0B2343]">
    <ToolWrapper
      class="mb-40"
      :tool="trads.label_tool_name"
      :description="trads.label_tool_description"
      @goBack="
        () => {
          step = 'panel';
        }
      "
      @handleNeedHelp="
        () => {
          alert('Help is on the way!');
        }
      "
    >
      <ToolPanel
        v-if="step === 'panel'"
        class="mt-[42px] lg:mt-[56px]"
        :textButton="trads.label_continue"
        :title="trads.label_panel_title"
        icon="InputTextIcon"
        @onClick="
          () => {
            step = 'form';
          }
        "
      >
        <div class="hidden sm:grid grid-cols-2 gap-5 text-[10px] md:text-xs">
          <div class="flex flex-row items-center gap-2">
            <Icon name="SeatSelection" />
            <p>
              {{trads.label_1_panel}}
            </p>
          </div>
          <div class="flex flex-row items-center gap-2">
            <Icon name="BadSeatSelection" />
            <p>
              {{trads.label_2_panel}}
            </p>
          </div>
          <div class="flex flex-row items-center gap-2">
            <Icon name="WebCKI" />
            <p>{{trads.label_3_panel}}</p>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="FlexibleFares" />
            <p>
              {{trads.label_4_panel}}
            </p>
          </div>
        </div>
        <div
          class="grid sm:hidden grid-cols-1 gap-5 text-[10px] md:text-xs my-8"
        >
          <div class="flex flex-row items-center gap-2">
            <Icon name="SeatSelection" />
            <p>
              {{trads.label_1_panel}}
            </p>
          </div>
          <div class="flex flex-row items-center gap-2">
            <Icon name="WebCKI" />
            <p>{{trads.label_3_panel}}</p>
          </div>
          <div class="flex flex-row items-center gap-2">
            <Icon name="BadSeatSelection" />
            <p>
              {{trads.label_2_panel}}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <Icon name="FlexibleFares" />
            <p>
              {{trads.label_4_panel}}
            </p>
          </div>
        </div>
      </ToolPanel>
      <div id="ticket-form" v-else-if="step === 'form'" class="flex flex-col gap-5 mt-10">
        <PNRForm
          :ticketForm="ticketForm"
          :trads="trads"
          @update:ticketForm="
            (val) => {
              ticketForm.value = onUpdateTicketForm(val);
            }
          "
          @handleSend="sendForm"
        />

        <NotificationBar
          v-if="notificationError"
          variant="error"
          @close="() => (notificationError = false)"
          >
          {{ notificationError }}
        </NotificationBar>

        <EmptyStateIllustration
          v-if="!segments.length"
          data-cy="empty-ticket-card"
          :description="trads.label_no_reservations"
          :topText="trads.label_empty_top"
          :isLoading="isLoading"
        />
        <CheckTicket
          v-if="segments.length"
          :passenger="passenger"
          :segments="segments"
          :selected-ids="selectedIdsArr"
          :checkLabel="trads.label_check_all"
          :selectAll="true"
          :trads="trads"
          :clid="clid"
          @toggle-all="handleToggleAll"
          @toggle="handleToggle"
        />
      </div>
    </ToolWrapper>
    <footer
      v-if="step == 'form' && segments.length"
      class="fixed bottom-0 w-full flex justify-center px-16 py-[10px] border-t-2 bg-white"
    >
      <div
        class="w-full max-w-[736px] flex flex-col sm:flex-row justify-between items-center"
      >
        <p class="text-xs sm:text-base">
          {{trads.label_footer_text}}
        </p>
        <Button
          class="m-5"
          variant="secondary"
          size="lg"
          @click="goToSeats"
          :disabled="!canContinue"
          >{{ continueLabel}}</Button
        >
      </div>
    </footer>
  </section>

  <Transition
    enter-active-class="transform transition ease-out duration-500"
    enter-from-class="translate-y-full opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transform transition ease-in duration-500"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-full opacity-0"
  >
    <SeatsMapLayout
      v-if="step === 'seatsMap'"
      @close="handleCloseMap"
      @addSeat="handleSeat"
      @delete="deleteSelection"
      :trads="trads"
      :passenger="passenger"
      :seatsMapInfo="seatMapCache"
      :segments="legsToMap"
    />
  </Transition>
  <GeneralToast v-if="isToastOpen" text="El beneficio de Corporate Priority se otorgó de forma exitosa" @close="isToastOpen = false"/>
</template>

<style scoped>
:deep(.uppercase-input input) {
  text-transform: uppercase;
}
</style>
