/**
 * Format an `<input type="datetime-local">` value (e.g. "2026-06-20T09:00")
 * into the `Y/m/d H:i` string the backend expects — see
 * VehicleController@getAvailableVehicle / @getVehicleRateCalculation, which
 * parse with Carbon::createFromFormat('Y/m/d H:i', ...).
 *
 * This reproduces the format the old Blade xdsoft datetimepicker emitted.
 * The previous implementation only swapped "T" for a space, leaving dashes,
 * which Carbon rejected with InvalidFormatException → 500 (JAVASCRIPT-4).
 *
 * @param {string} val datetime-local value, or '' when unset
 * @returns {string} "Y/m/d H:i" (e.g. "2026/06/20 09:00"), or '' when empty
 */
export function formatDt(val) {
    if (!val) return '';
    // "2026-06-20T09:00[:ss]" → "2026/06/20 09:00" (drop any seconds component)
    return val.replace('T', ' ').replace(/-/g, '/').slice(0, 16);
}
