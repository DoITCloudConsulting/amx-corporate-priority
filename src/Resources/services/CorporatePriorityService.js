import axios from "axios";
import { usePage } from "@inertiajs/vue3";

class CorporatePriorityService {
  reservationSeatMap = {};
  reservation = {};
  currentSegment = {};
  trackerActivity;
  case = {
    number: "",
  };

  async getIatas(IATA) {
    try {
      const { iata, salesforce_group_id } = usePage().props.auth.user;

      let securityType = "";
      let groupId = "";

      if (salesforce_group_id == null || salesforce_group_id == "") {
        securityType = "IATA";
        groupId = iata;
      } else {
        securityType = "securityGroups";
        groupId = salesforce_group_id;
      }
      let routeStr = "";

      if (securityType == "securityGroups") {
        routeStr = "/api/IATAS/" + groupId;
      } else {
        routeStr = "/api/groupiatas/" + groupId;
      }

      const response = await axios.get(routeStr);

      await this.trackerActivity.track({
        name: "corporate-priority-get-iatas",
        type: "tool.corporate-priority.get-iatas",
        params: {
          iata: groupId,
          result: "success",
        },
        template: "templates.tools.corporate-priority.get-iatas.success",
        msg_type: "success",
        lang: "en",
      });

      return response.data;
    } catch (error) {
      await this.trackerActivity.track({
        name: "corporate-priority-get-iatas",
        type: "tool.corporate-priority.get-iatas",
        params: {
          result: "error",
        },
        template: "templates.tools.corporate-priority.get-iatas.error",
        msg_type: "error",
        lang: "en",
      });

      throw error;
    }
  }

  isAnySeatAvailable(map) {
    return (map?.seatMap || map).some(
      (seat) => seat.status === "AVAILABLE" && seat.type === "PREFERRED",
    );
  }

  prepareSeatMapPayload() {
    return {
      reservationCode: this.reservation.pnr,
      isStandBy: this.reservation.isStandBy,
      transactionDate: new Date().toISOString().slice(0, 19),
      legCode: this.currentSegment.legCode,
      legRegion: this.currentSegment.segmentRegion ?? null,
      windowLegStatus: "MYB",
      segmentCode: this.currentSegment.segmentCode,
      aircraftType: this.currentSegment.aircraftType,
      origin: this.currentSegment.startLocation,
      destination: this.currentSegment.endLocation,
      marketingCarrier: this.currentSegment.marketingCarrier,
      operatingCarrier: this.currentSegment.operatingCarrier,
      operatingFlightCode: this.currentSegment.flightNumber,
      departureDate: this.currentSegment.departureDateTime,
      arrivalDate: this.currentSegment.arrivalDateTime,
      farebasis: (this.currentSegment.farebasis || "").split("/")[0] || null,
      fareFamily: this.currentSegment.fareFamily,
      bookingClass: this.currentSegment.bookingClass,
      bookingCabin: this.currentSegment.bookingCabin,
      segmentRegion: this.currentSegment.segmentRegion ?? null,
    };
  }
  async getSeatMap(payload) {
    try {
      const { data } = await axios.post(route("get-seat-map"), payload);

      // fire tracker success
      await this.trackerActivity.track({
        name: "corporate-priority-seat-map",
        type: "tool.corporate-priority.seat-map",
        params: {
          pnr: this.reservation.pnr,
          legCode: payload?.legCode,
          segmentCode: payload?.segmentCode,
          origin: payload?.origin,
          destination: payload?.destination,
          result: "success",
        },
        template: "templates.tools.corporate-priority.seat-map.success",
        msg_type: "success",
        lang: "en",
      });

      return data;
    } catch (error) {
      // track error
      await this.trackerActivity.track({
        name: "corporate-priority-seat-map",
        type: "tool.corporate-priority.seat-map",
        params: {
          pnr: this.reservation.pnr,
          legCode: payload?.legCode,
          segmentCode: payload?.segmentCode,
          origin: payload?.origin,
          destination: payload?.destination,
          result: "error",
        },
        template: "templates.tools.corporate-priority.seat-map.error",
        msg_type: "error",
        lang: "en",
      });

      throw error;
    }
  }

  async getAllSeatMaps(reservation) {
    const seatMapsBySegmentId = {};
    const segments = [];

    for (let legIndex = 0; legIndex < reservation.segments.length; legIndex++) {
      const leg = reservation.segments[legIndex];

      if (leg.segments.length) {
        for (let segIndex = 0; segIndex < leg.segments.length; segIndex++) {
          const segment = leg.segments[segIndex];
          const payload = this.prepareSeatMapPayload({
            ...segment,
            isStandBy: reservation.isStandBy,
            legCode: leg.legCode,
          });

          segments.push(segment);
          try {
            seatMapsBySegmentId[segment.segmentID] =
              await this.getSeatMap(payload);
          } catch (e) {
            // track error for all-seat-maps
            await this.trackerActivity.track({
              name: "corporate-priority-all-seat-maps",
              type: "tool.corporate-priority.all-seat-maps",
              params: {
                pnr: reservation.pnr,
                segmentId: segment.segmentID,
                result: "error",
              },
              template:
                "templates.tools.corporate-priority.all-seat-maps.error",
              msg_type: "error",
              lang: "en",
            });
            throw e;
          }
        }
      }
    }

    // if everything ok, fire success tracker
    await this.trackerActivity.track({
      name: "corporate-priority-all-seat-maps",
      type: "tool.corporate-priority.all-seat-maps",
      params: { pnr: reservation.pnr, result: "success" },
      template: "templates.tools.corporate-priority.all-seat-maps.success",
      msg_type: "success",
      lang: "en",
    });

    return { seatMapsBySegmentId, segments };
  }

  findSeat(segmentID, seatCode, map = null) {
    const seatsMap = map ?? this.reservationSeatMap;

    console.log(seatsMap);
    return (seatsMap[segmentID]?.seatMap || seatsMap).find(
      (seat) => seat.seatCode === seatCode,
    );
  }

  prepareSeatAssignmentPayload() {
    const isNewAssignment =
      this.currentSegment?.newSeat && !this.currentSegment.seats.length
        ? true
        : false;

    const seatId = isNewAssignment ? "" : this.getCurrentSegmentSeat().id;
    const seatCodeOld = isNewAssignment
      ? ""
      : this.getCurrentSegmentSeat().seatCode;

    return {
      transactionDate: new Date().toISOString().slice(0, 19),
      isStandBy: this.reservation.isStandBy,
      reservationCode: this.reservation.pnr,
      ticketNumber: this.reservation.numberTicket,
      passenger: {
        lastName: this.reservation.passenger.lastName,
        firstName: this.reservation.passenger.firstName,
        nameNumber: this.reservation.passenger.nameId,
        type: this.reservation.passenger.type,
      },
      seat: {
        id: seatId,
        seatCode: this.currentSegment?.newSeat?.seatCode,
        isChangeSeat: isNewAssignment ? false : true,
        seatCodeOld,
        segmentCode: this.currentSegment.segmentCode,
        emd: "",
        status: "",
        currencyCode: this.currentSegment?.newSeat?.currency?.currencyCode,
        base: this.currentSegment?.newSeat?.currency?.base,
        taxes: this.currentSegment?.newSeat?.currency?.taxes,
        total: this.currentSegment?.newSeat?.currency?.total,
      },
      segment: {
        entity: this.currentSegment.legEntity,
        remainingTimeToCheckIn: Number(
          this.currentSegment.remainingSegmentTimeToCheckin,
        ),
        segmentCode: this.currentSegment.segmentCode,
        coupon: Number(this.currentSegment.coupon),
        status: this.currentSegment.status,
        legCode: this.currentSegment.legCode,
        aircraftType: this.currentSegment.aircraftType,
        origin: this.currentSegment.startLocation,
        destination: this.currentSegment.endLocation,
        marketingFlightCode: this.currentSegment.flightNumber,
        marketingCarrier: this.currentSegment.operatingCarrier,
        operatingCarrier: this.currentSegment.operatingCarrier,
        operatingFlightCode: this.currentSegment.flightNumber,
        departureDate: this.currentSegment.departureDateTime,
        arrivalDate: this.currentSegment.arrivalDateTime,
        farebasis: this.currentSegment.farebasis,
        fareFamily: this.currentSegment.fareFamily,
        bookingClass: this.currentSegment.bookingClass,
        cabinClass: this.currentSegment.cabinClass,
        bookingCabin: this.currentSegment.bookingCabin,
        segmentNumber: this.currentSegment.segmentNumber.toString(),
      },
      rollback: false,
    };
  }

  async assignSeat(payload) {
    try {
      const response = await axios.post(route("assign-seat"), payload);

      await this.trackerActivity.track({
        name: "corporate-priority-assign-seat",
        type: "tool.corporate-priority.assign-seat",
        params: {
          pnr: payload?.reservationCode,
          ticket: payload?.ticketNumber || this.reservation.numberTicket,
          lastname:
            payload?.passenger?.lastName || this.reservation.passenger.lastName,
          seatCode: payload?.seat?.seatCode,
          result: "success",
        },
        template: "templates.tools.corporate-priority.assign-seat.success",
        msg_type: "success",
        lang: "en",
      });

      return response;
    } catch (error) {
      await this.trackerActivity.track({
        name: "corporate-priority-assign-seat",
        type: "tool.corporate-priority.assign-seat",
        params: {
          pnr: payload?.reservationCode,
          ticket: payload?.ticketNumber || this.reservation.numberTicket,
          lastname:
            payload?.passenger?.lastName || this.reservation.passenger.lastName,
          seatCode: payload?.seat?.seatCode,
          result: "error",
        },
        template: "templates.tools.corporate-priority.assign-seat.error",
        msg_type: "error",
        lang: "en",
      });

      throw error;
    }
  }

  getCurrentSegmentSeat() {
    return this.currentSegment.seats[0];
  }

  async condonate() {
    try {
      const { data } = await axios.post(route("condonate-seats"), {
        pnr: this.reservation.pnr,
      });

      await this.trackerActivity.track({
        name: "corporate-priority-condonate",
        type: "tool.corporate-priority.condonate",
        params: {
          pnr: this.reservation.pnr,
          ticket: this.reservation.numberTicket,
          lastname: this.reservation.passenger.lastName,
          result: "success",
        },
        template: "templates.tools.corporate-priority.condonate.success",
        msg_type: "success",
        lang: "en",
      });

      return data;
    } catch (error) {
      await this.trackerActivity.track({
        name: "corporate-priority-condonate",
        type: "tool.corporate-priority.condonate",
        params: {
          pnr: this.reservation.pnr,
          ticket: this.reservation.numberTicket,
          lastname: this.reservation.passenger.lastName,
          result: "error",
        },
        template: "templates.tools.corporate-priority.condonate.error",
        msg_type: "error",
        lang: "en",
      });

      throw error;
    }
  }

  async preparePdfDownloadPayload(segments) {
    const userName = usePage().props?.auth?.user?.name;
    const passenger = this.reservation.passenger;

    const confirmedSegments = [];
    const pendingSegments = [];

    segments.forEach((segment) => {
      const seats = segment.seats;
      if (seats.length && seats[0].status === "PAID") {
        confirmedSegments.push({
          keyword: seats[0]?.seatCode,
          value: `${segment.startLocation} / ${segment.endLocation}`,
        });
      }
      if (seats.length && seats[0].status !== "PAID") {
        pendingSegments.push({
          keyword: seats[0]?.seatCode,
          value: `${segment.startLocation} / ${segment.endLocation}`,
        });
      }
    });

    const issuerAccount = await this.getAccountByIata(
      this.reservation.stationNumber,
    );

    this.pdfDownloadPayload = {
      fileName: "Corporate_Priority",
      reservation: this.reservation.pnr,
      userName,
      agency: issuerAccount.Name || "Agencia de prueba",
      corporate: this.reservation.corporate || "Corporativo de prueba",
      passengerName: `${passenger.lastName} / ${passenger.firstName}`,
      confirmedSegments,
      pendingSegments,
    };

    return { ...this.pdfDownloadPayload };
  }

  async getAccountByIata(iata) {
    try {
      const response = await axios.get(route("account.getByIata", { iata }));

      await this.trackerActivity.track({
        name: "corporate-priority-get-account-by-iata",
        type: "tool.corporate-priority.get-account-by-iata",
        params: {
          iata: iata,
          result: "success",
        },
        template:
          "templates.tools.corporate-priority.get-account-by-iata.success",
        msg_type: "success",
        lang: "en",
      });

      return response.data;
    } catch (error) {
      await this.trackerActivity.track({
        name: "corporate-priority-get-account-by-iata",
        type: "tool.corporate-priority.get-account-by-iata",
        params: {
          iata: iata,
          result: "error",
        },
        template:
          "templates.tools.corporate-priority.get-account-by-iata.error",
        msg_type: "error",
        lang: "en",
      });

      throw error;
    }
  }

  async downloadPDF() {
    try {
      const response = await axios.post(
        route("pdf"),
        { ...this.pdfDownloadPayload },
        {
          responseType: "blob",
        },
      );
      const url = window.URL.createObjectURL(response.data);
      const a = document.createElement("a");
      a.href = url;
      a.download = this.pdfDownloadPayload.fileName + ".pdf";
      a.click();
      window.URL.revokeObjectURL(url);

      await this.trackerActivity.track({
        name: "corporate-priority-download-pdf",
        type: "tool.corporate-priority.download-pdf",
        params: {
          pnr: this.pdfDownloadPayload.reservation,
          fileName: this.pdfDownloadPayload.fileName,
          result: "success",
        },
        template: "templates.tools.corporate-priority.download-pdf.success",
        msg_type: "success",
        lang: "en",
      });
    } catch (error) {
      await this.trackerActivity.track({
        name: "corporate-priority-download-pdf",
        type: "tool.corporate-priority.download-pdf",
        params: {
          pnr: this.pdfDownloadPayload?.reservation,
          fileName: this.pdfDownloadPayload?.fileName,
          result: "error",
        },
        template: "templates.tools.corporate-priority.download-pdf.error",
        msg_type: "error",
        lang: "en",
      });

      throw error;
    }
  }

  prepareCasePayload(data = {}) {
    const { lastName, firstName, nameId } = this.reservation.passenger;

    const user = usePage().props?.auth?.user;
    const segment = this.currentSegment;

    const caseData = {
      concept: "ASIENTOS",
      subconcept: "CORPORATE PRIORITY",
      typeGSS: "Servicios",
      description: "",
      priority: "Medium",
      status: "Confirmado",
      firstNameRequest: user.first_name,
      lastNameRequest: user.last_name,
      iata: this.reservation.stationNumber,
      recordId: "",
      iataSolicitante: user.iata,
      waiver: null,
      ...data?.case,
    };

    return {
      booking: {
        flight: this.currentSegment.flightNumber,
        route: `${segment.startLocation} - ${segment.endLocation}`,
        bookinIata: this.reservation.stationNumber,
        flightDate: segment.departureDateTime,
        name: this.reservation.pnr,
        recordId: "",
      },
      passenger: {
        firstName: firstName,
        lastName: lastName,
        NumberPassengers: nameId,
        name: `${firstName} ${lastName}`,
        totalPassengers: 1,
        recordId: "",
      },
      case: caseData,
    };
  }

  async createCase(payload) {
    try {
      const response = await axios.post(route("createCase"), payload);

      await this.trackerActivity.track({
        name: "corporate-priority-create-case",
        type: "tool.corporate-priority.create-case",
        params: {
          pnr: this.reservation.pnr,
          result: "success",
        },
        template: "templates.tools.corporate-priority.create-case.success",
        msg_type: "success",
        lang: "en",
      });

      console.log(response.data);
      return response.data.data.records[0];
    } catch (error) {
      await this.trackerActivity.track({
        name: "corporate-priority-create-case",
        type: "tool.corporate-priority.create-case",
        params: {
          pnr: this.reservation.pnr,
          result: "error",
        },
        template: "templates.tools.corporate-priority.create-case.error",
        msg_type: "error",
        lang: "en",
      });

      throw error;
    }
  }
  async storeTrakerActivity() {
    const user = usePage().props?.auth?.user;
    await axios
      .post(
        route("ta-creation"),
        {
          id_user: user.id,
          from: "heroku",
          description: `Page: /corporate-priority > GET: /dev/preferred/process/booking > [${this.reservation.pnr}, ${this.reservation.numberTicket}, ${this.reservation.passenger.lastName}]`,
          tipo: "Tools",
          subtipo: "Corporate Priority",
          PNR: this.reservation.pnr,
          Ticket: this.reservation.numberTicket,
          Apellido: this.reservation.passenger.lastName,
          noVuelo: this.currentSegment.flightNumber,
          resgreso: this.currentSegment.endLocation,
          iata: this.reservation.stationNumber,
          dDate: this.currentSegment.arrivalDateTime
            .slice(0, -3)
            .replace("T", " ")
            .replaceAll("-", "/"),
          CCI_PAXNAME: this.reservation.passenger.lastName,
          ticketNumber: this.reservation.numberTicket,
          coupon: this.currentSegment.coupon,
          first_name: this.reservation.passenger.firstName,
          last_name: this.reservation.passenger.lastName,
          id_agency: user.id_agency,
          statusCase: "Confirmado",
          agencia_emisora: this.reservation.stationNumber,
        },
        {
          headers: {
            "Content-Type": "application/json",
          },
        },
      )
      .then((res) => {})
      .catch((err) => {
        console.log(err);
      });
  }
}

export const corporatePriorityService = new CorporatePriorityService();
