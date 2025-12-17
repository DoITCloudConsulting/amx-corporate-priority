import axios from "axios";

export async function fetchTicketStatus(data) {
    const response = await axios.get(route('get-reservation', {
            pnr: data.pnr,
            lastname: data.lastname,
            ticketNumber: data.numberTicket,
    }));

    return response.data
}

export async function fetchCorporateValidation(data) {
    const response = await axios.post(route('validate-corporate', {
            pnr: data.pnr
    }));

    return response.data
}