/**
 * Maps the tenant's `layout_mode` setting (surfaced as `branding.layoutMode`)
 * onto the value next-themes expects. `systemmode` follows the viewer's
 * `prefers-color-scheme`; anything unknown falls back to light.
 *
 * @param {{layoutMode?: string}|undefined} branding
 * @returns {'light'|'dark'|'system'}
 */
export function resolveTheme(branding) {
    switch (branding?.layoutMode) {
        case 'darkmode':
            return 'dark';
        case 'systemmode':
            return 'system';
        default:
            return 'light';
    }
}
