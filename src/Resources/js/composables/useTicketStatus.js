// composables/useTicketStatus.js

import { fetchTicketStatus } from "../../services/TicketStatusService";
// import { getTranslation } from "@/shared/getTranslation";
// import { useEventTracker } from "@/composable/useEventTracker";
// import { useUserSessionStore } from "./useAuthUserStore";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

async function validateResponse({ message, segments, data }, ticketForm) {
  
  // const t = (k) => getTranslation(k);
  const t = (k) => k; // placeholder
console.log('message:', message);

  if (message === "PNR not found, code: 100123, severity: MODERATE") {
    const errorNotification = t("common.tools.error.pnr.invalid-key");
    // await trackError(ticketForm, errorNotification);
    return { success: false, errorNotification };
  }

  if (!segments || !segments.length) {
    const errorNotification = t("common.tools.error.lastname-not-match");
    // await trackError(ticketForm, errorNotification);
    return { success: false, errorNotification };
  }

  // const idAgencyResponse = await getAgencyId();
  // if (!idAgencyResponse?.includes?.(data.stationNumber)) {
  //   const errorNotification = t("common.tools.invalid-iata");
  //   // await trackError(ticketForm, errorNotification);
  //   return { success: false, errorNotification };
  // }

  return { success: true };
}

export async function getTicketStatus(ticketData) {
  try {
    const data = await fetchTicketStatus(ticketData);
    console.log(data)
    const { success, errorNotification } = await validateResponse(
      { message: data.benefits.benefits_error, segments: data.customPassengerSegments.legs, data },
      ticketData
    );

    if (!success) {
      return { validTicket: false, error: errorNotification };
    }

    const ticket = {
      benefits: data.benefits,
      stationNumber: data.customPassengerSegments.stationNumber,
      segments: data.customPassengerSegments.legs,
      passenger: data.customPassengerSegments.passenger,
      isStandBy: data.customPassengerSegments.isStandBy,
      pnr: ticketData.pnr,
      clid: data.clid,
    };

    // await trackEvent({
    //   name: "consult-ticket",
    //   params: { params: { ...ticketData }, result: "success" },
    //   template: "templates.tools.default.success",
    //   msg_type: "success",
    // });

    return ticket;
  } catch (error) {
    console.log(error);

    // const t = (k) => getTranslation(k);
    const t = (k) => k; // placeholder

    const code = error.response?.data?.errorCode;

    if (code === "57111303") {
      const errorNotification = t("common.tools.error.pnr.reservation-out-sync");
      // await trackError(ticketData, errorNotification);
      return { validTicket: false, error: errorNotification };
    }

    if (code === "57111304") {
      const errorNotification = t("tools.baggage.error.no-flight-itinerary");
      // await trackError(ticketData, errorNotification);
      return { validTicket: false, error: errorNotification };
    }

    if (code === "5000") {
      const errorNotification = t("Su clave de reservación no es una clave válida en nuestro sistema.");
      // await trackError(ticketData, errorNotification);
      return { validTicket: false, error: errorNotification };
    }

    // await trackError(ticketData, error.message);
    return { validTicket: false, error: error.message };
  }
}

async function getAgencyId() {
  // const { userSession } = useUserSessionStore();
  // const iata = userSession.iata;
  // const sfGroupId = userSession.salesforce_group_id;
  // (coloca aquí tu lógica real)
  try {
    // Ejemplo de llamada
    const response = await axios.get(route("bundle-iatas", { IATA: "XXXX" }));
    return response.data;
  } catch (e) {
    console.log(e.message);
    return [];
  }
}

async function trackEvent({ name, params, template, msg_type }) {
  // const { track } = useEventTracker();
  const track = async () => {};
  await track({
    type: "tool-ticket", // o construye dinamicamente si tienes currentTool
    name,
    params,
    template,
    msg_type,
  });
}

async function trackError(params, message) {
  await trackEvent({
    name: "consult-ticket",
    params: { params: { ...params }, error: `/api/ticket-status - ${message}` },
    template: "templates.tools.default.error",
    msg_type: "error",
  });
}
