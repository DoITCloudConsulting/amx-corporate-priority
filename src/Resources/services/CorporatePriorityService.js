import axios from "axios";

class CorporatePriorityService {
  reservationSeatMap = {};
  reservation = {};
  currentSegment = {};

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
    const { data } = await axios.post(route("get-seat-map"), payload);

    return data;
  }

  async getAllSeatMaps(reservation) {
    const seatMapsBySegmentId = {};
    const segments = [];

    for (let legIndex = 0; legIndex < reservation.segments.length; legIndex++) {
      const leg = reservation.segments[legIndex];
      console.log(leg);

      if (leg.segments.length) {
        for (let segIndex = 0; segIndex < leg.segments.length; segIndex++) {
          const segment = leg.segments[segIndex];
          const payload = this.prepareSeatMapPayload({
            ...segment,
            isStandBy: reservation.isStandBy,
            legCode: leg.legCode,
          });

          segments.push(segment);
          seatMapsBySegmentId[segment.segmentID] = await this.getSeatMap(
            payload
          );
        }
      }
    }

    return { seatMapsBySegmentId, segments };
  }

  findSeat(segmentID, seatCode, map = null) {
    console.log(map);
    console.log(this.reservationSeatMap);
    return (map ?? this.reservationSeatMap)[segmentID].seatMap.find(
      (seat) => seat.seatCode === seatCode
    );
  }

  prepareSeatAssignmentPayload() {
    console.log(this.currentSegment);
    const isNewAssignment =
      this.currentSegment?.newSeat && !this.currentSegment.seats.length
        ? true
        : false;

    const seatId = isNewAssignment ? "" : this.getCurrentSegmentSeat().id;
    const seatCodeOld = isNewAssignment
      ? ""
      : this.getCurrentSegmentSeat().seatCode;

    return {
      transactionDate: "2025-12-01T15:41:51",
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
          this.currentSegment.remainingSegmentTimeToCheckin
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
    const response = await axios.post(route("assign-seat"), payload);

    return response;
  }

  getCurrentSegmentSeat() {
    return this.currentSegment.seats[0];
  }

  async condonate() {
    const { data } = await axios.post(route("condonate-seats"), {
      pnr: this.reservation.pnr,
    });

    return data;
  }

  downloadPdf(segments) {
    const confirmedSegments = [];
    const pendingSegments = [];

    segments.forEach((segment) => {
      const seats = segment.seats;
      if (seats.length && seats[0].status === "PAID") {
        confirmedSegments.push({
          keyword: seats[0].seatCode,
          value: `${segment.startLocation} / ${segment.endLocation}`,
        });
      } else {
        pendingSegments.push({
          keyword: seats[0].seatCode,
          value: `${segment.startLocation} / ${segment.endLocation}`,
        });
      }
    });

    localStorage.setItem(
      "cp-case-data",
      JSON.stringify({
        reservation: this.reservation.pnr,
        passengerName: `${this.reservation.passenger.lastName} / ${this.reservation.passenger.firstName}`,
        confirmedSegments,
        pendingSegments,
      })
    );

    window.open("/pdf", "_blank");
  }
}

export const corporatePriorityService = new CorporatePriorityService();
