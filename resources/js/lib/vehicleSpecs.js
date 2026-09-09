/**
 * Human-readable, translated labels for a vehicle's stored spec values.
 *
 * `vehicles.gearbox` and `vehicles.fuel_type` hold lowercase English slugs
 * (App\Models\Vehicle::$gearbox / ::$fuelType) — 'automatic', 'petrol'. The
 * admin screens run them through Vehicle::$gearbox[...] server-side; the
 * storefront had been printing the raw slug, so a French visitor read
 * "automatic / petrol" next to otherwise translated copy.
 *
 * Keys are mirrored from those two static maps. An unknown value (a row
 * written before a slug was added, or by hand) falls through to itself rather
 * than to an empty cell — showing 'gpl' is better than showing nothing.
 */

const GEARBOX_KEYS = {
    automatic: ['spec_gearbox_automatic', 'Automatique'],
    manual: ['spec_gearbox_manual', 'Manuelle'],
};

const FUEL_KEYS = {
    essence: ['spec_fuel_essence', 'Essence'],
    petrol: ['spec_fuel_petrol', 'Essence'],
    diesel: ['spec_fuel_diesel', 'Diesel'],
    hybrid: ['spec_fuel_hybrid', 'Hybride'],
    electric: ['spec_fuel_electric', 'Électrique'],
    gas: ['spec_fuel_gas', 'GPL'],
};

function label(map, value, t) {
    if (!value) return '—';
    const entry = map[String(value).toLowerCase()];
    return entry ? t(entry[0], entry[1]) : value;
}

export function gearboxLabel(value, t) {
    return label(GEARBOX_KEYS, value, t);
}

export function fuelLabel(value, t) {
    return label(FUEL_KEYS, value, t);
}

/** Both labels for a vehicle row, since callers almost always want the pair. */
export function specLabels(vehicle, t) {
    return {
        gearbox: gearboxLabel(vehicle?.gearbox, t),
        fuel: fuelLabel(vehicle?.fuel_type, t),
    };
}
