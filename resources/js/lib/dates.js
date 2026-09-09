/**
 * The earliest valid return date for a given pick-up date.
 *
 * storeBooking() validates `end_date` as `after:start_date`, so a same-day
 * rental is refused by the server. All three date pickers on the storefront
 * (the landing search panel, the vehicle detail booking card, and step 2 of
 * the wizard) used `min={startDate}`, which offered a same-day pair the server
 * would then reject — on the landing and the wizard it went through to a
 * validation error four steps later, and on the detail page it silently
 * produced a zero-day total with both dates filled and no error shown.
 */
export function dayAfter(date) {
    if (!date) return '';

    const next = new Date(`${date}T00:00:00`);
    if (Number.isNaN(next.getTime())) return '';

    next.setDate(next.getDate() + 1);

    return next.toISOString().slice(0, 10);
}
