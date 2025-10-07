import axios from "axios";

export async function fetchTicketStatus(data) {
    const response = await axios.get(route('get-reservation', {
            pnr: data.pnr,
            lastname: data.lastname,
            ticketNumber: data.numberTicket,
    }));

    return response.data
}