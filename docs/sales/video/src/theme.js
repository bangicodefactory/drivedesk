// Shared design tokens for the DriveDesk promo.
// Colours are lifted from the app's own brand ramp so the video and the
// product look like the same thing.

export const FPS = 30;
export const WIDTH = 1920;
export const HEIGHT = 1080;

export const color = {
  ink: '#181210',
  dark1: '#150f0e',
  dark2: '#2a130b',
  dark3: '#571f0d',
  brand: '#e2571f',
  brandLt: '#f5893f',
  brandDeep: '#7a2e10',
  paper: '#ffffff',
  wash: '#fdf2ec',
  line: '#e9e2dd',
  soft: '#5f5854',
  faint: '#a2988f',
  ok: '#1f7a4d',
};

// Segoe UI ships on every Windows box and covers Latin + Arabic, so the
// render never depends on a webfont being reachable.
export const font = {
  sans: '"Segoe UI", -apple-system, "Helvetica Neue", Arial, sans-serif',
  arabic: '"Segoe UI", "Tahoma", "Arial", sans-serif',
  mono: '"Cascadia Mono", Consolas, "Courier New", monospace',
};

export const gradient = {
  dark: `linear-gradient(145deg, ${color.dark1} 0%, ${color.dark2} 48%, ${color.dark3} 100%)`,
  brand: `linear-gradient(100deg, ${color.brandLt} 0%, ${color.brand} 100%)`,
};

// Every scene's length in frames, in playback order. Kept in one place so the
// composition duration is always the sum and can never drift.
export const SCENES = [
  { id: 'intro', frames: 100 },
  { id: 'problem', frames: 185 },
  { id: 'dashboard', frames: 195 },
  { id: 'planning', frames: 180 },
  { id: 'fleet', frames: 185 },
  { id: 'contract', frames: 215 },
  { id: 'money', frames: 225 },
  { id: 'fines', frames: 225 },
  { id: 'languages', frames: 175 },
  { id: 'whitelabel', frames: 185 },
  { id: 'outro', frames: 150 },
];

export const TOTAL_FRAMES = SCENES.reduce((n, s) => n + s.frames, 0);
