import '@testing-library/jest-dom';

// Polyfills for jsdom: Radix UI components use ResizeObserver + matchMedia.
class ResizeObserverMock {
    observe() {}
    unobserve() {}
    disconnect() {}
}
globalThis.ResizeObserver = globalThis.ResizeObserver ?? ResizeObserverMock;

globalThis.matchMedia = globalThis.matchMedia ?? ((query) => ({
    matches: false,
    media: query,
    onchange: null,
    addEventListener() {},
    removeEventListener() {},
    addListener() {},
    removeListener() {},
    dispatchEvent() { return false; },
}));
