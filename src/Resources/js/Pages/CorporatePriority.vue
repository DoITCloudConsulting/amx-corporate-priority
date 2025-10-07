<script setup>
import {
  ToolWrapper,
  ToolPanel,
  Icon,
  PNRForm,
  EmptyStateIllustration,
  NotificationBar,
} from "am-ui-package";
import { ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { getTicketStatus } from "../composables/useTicketStatus";

const step = ref("panel");
const isLoading = ref(false);
const errorNotification = ref(false);

const alert = (message) => {
  window.alert(message);
};

const ticketForm = ref({
  pnr: "",
  numberTicket: "",
  lastname: "",
});

const sendForm = async () => {
  try {
    const response = await getTicketStatus(ticketForm.value);
    console.log(response);
  } catch (error) {
    console.log(error);
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

watch(ticketForm, (val) => {}, { deep: true });
</script>

<template>
  <ToolWrapper
    tool="Corportate Priority"
    description="Aquí podras solicitar el waiver de asientos para tu reserva"
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
      textButton="Continuar"
      title="Información general"
      icon="InputTextIcon"
      @onClick="
        () => {
          step = 'form';
        }
      "
    >
      <div class="hidden sm:grid grid-cols-2 gap-5 text-[10px] md:text-[12px]">
        <div class="flex flex-row items-center gap-2">
          <Icon name="SeatSelection" />
          <p>El beneficiario solo es aplicable para asientos preferenciales.</p>
        </div>
        <div class="flex flex-row items-center gap-2">
          <Icon name="BadSeatSelection" />
          <p>
            En caso de no contar con asientos preferentes disponibles no es
            posible otorgar el beneficio.
          </p>
        </div>
        <div class="flex flex-row items-center gap-2">
          <Icon name="WebCKI" />
          <p>Cualquier uso indebido este sujeto a débito.</p>
        </div>
        <div class="flex items-center gap-2">
          <Icon name="FlexibleFares" />
          <p>
            Si cuentas con un asiento pagado previamente no se realizará ningún
            reembolso por este medio.
          </p>
        </div>
      </div>
      <div
        class="grid sm:hidden grid-cols-1 gap-5 text-[10px] md:text-[12px] my-8"
      >
        <div class="flex flex-row items-center gap-2">
          <Icon name="SeatSelection" />
          <p>El beneficiario solo es aplicable para asientos preferenciales.</p>
        </div>
        <div class="flex flex-row items-center gap-2">
          <Icon name="WebCKI" />
          <p>Cualquier uso indebido este sujeto a débito.</p>
        </div>
        <div class="flex flex-row items-center gap-2">
          <Icon name="BadSeatSelection" />
          <p>
            En caso de no contar con asientos preferentes disponibles no es
            posible otorgar el beneficio.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Icon name="FlexibleFares" />
          <p>
            Si cuentas con un asiento pagado previamente no se realizará ningún
            reembolso por este medio.
          </p>
        </div>
      </div>
    </ToolPanel>
    <div id="ticket-form" v-if="step === 'form'" class="flex flex-col gap-5">
      <PNRForm
        :ticketForm="ticketForm"
        class="mt-[19px] lg:mt-[27px]"
        @update:ticketForm="
          (val) => {
            ticketForm.value = onUpdateTicketForm(val);
          }
        "
        @handleSend="sendForm"
      />
      <NotificationBar
        v-if="errorNotification"
        :description="errorNotification"
        :variant="error"
        @closeWarn="cleanNotificationError"
      />
      <EmptyStateIllustration
        class="mt-[19px] lg:mt-[27px]"
        text="No hay reservaciones registradas."
        data-cy="empty-ticket-card"
        description="No hay reservaciones registradas."
      />
    </div>
  </ToolWrapper>
</template>

<style scoped>
:deep(.uppercase-input input) {
  text-transform: uppercase;
}
</style>
