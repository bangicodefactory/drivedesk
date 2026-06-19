// Shared blacklist confirm-gate for the Booking and Rental-Agreement create forms
// (BAN-252). If any selected driver is blacklisted, ask the owner to confirm; the
// server still enforces and records the override, so this is UX only.
//
// Returns { proceed, acknowledge }:
//   proceed=false  → the owner declined the warning; abort the submit.
//   acknowledge    → send as `acknowledge_blacklist` so the server proceeds + audits.
export async function confirmBlacklist(drivers, selectedIds, confirm, t) {
    const ids = (selectedIds || []).filter(Boolean).map(String);
    const flagged = (drivers || []).filter((d) => d.blacklisted && ids.includes(String(d.id)));

    if (flagged.length === 0) {
        return { proceed: true, acknowledge: false };
    }

    const ok = await confirm({
        title: t('Driver is blacklisted'),
        description: flagged.map((d) => `${d.name}: ${d.blacklist_reason || ''}`.trim()).join('\n'),
        confirmText: t('Proceed anyway'),
        destructive: true,
    });

    return { proceed: !!ok, acknowledge: !!ok };
}
