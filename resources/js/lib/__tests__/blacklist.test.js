import { describe, it, expect, vi } from 'vitest';
import { confirmBlacklist } from '../blacklist.js';

const t = (k) => k;
const drivers = [
    { id: 1, name: 'Clean Carla', blacklisted: false },
    { id: 2, name: 'Risky Rachid', blacklisted: true, blacklist_reason: 'No-show' },
    { id: 3, name: 'Banned Bilal', blacklisted: true, blacklist_reason: 'Fraud' },
];

describe('confirmBlacklist', () => {
    it('proceeds without asking when no selected driver is blacklisted', async () => {
        const confirm = vi.fn();
        const res = await confirmBlacklist(drivers, [1], confirm, t);
        expect(confirm).not.toHaveBeenCalled();
        expect(res).toEqual({ proceed: true, acknowledge: false });
    });

    it('acknowledges when the owner confirms a blacklisted driver', async () => {
        const confirm = vi.fn().mockResolvedValue(true);
        const res = await confirmBlacklist(drivers, [2], confirm, t);
        expect(confirm).toHaveBeenCalledOnce();
        expect(res).toEqual({ proceed: true, acknowledge: true });
    });

    it('does not proceed when the owner declines', async () => {
        const confirm = vi.fn().mockResolvedValue(false);
        const res = await confirmBlacklist(drivers, [2], confirm, t);
        expect(res).toEqual({ proceed: false, acknowledge: false });
    });

    it('warns once across both drivers (driver + driver2) and ignores empties', async () => {
        const confirm = vi.fn().mockResolvedValue(true);
        const res = await confirmBlacklist(drivers, [2, 3, ''], confirm, t);
        expect(confirm).toHaveBeenCalledOnce();
        const { description } = confirm.mock.calls[0][0];
        expect(description).toContain('No-show');
        expect(description).toContain('Fraud');
        expect(res.acknowledge).toBe(true);
    });
});
