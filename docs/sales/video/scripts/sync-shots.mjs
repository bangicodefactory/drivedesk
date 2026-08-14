// Remotion can only serve assets out of public/, but the screenshots are shared
// with the decks and the handbook and live one level up. Copying them in at
// build time keeps a single source of truth instead of committing them twice.

import { cpSync, existsSync, mkdirSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const src = resolve(here, '../../screenshots');
const dest = resolve(here, '../public/shots');

if (!existsSync(src)) {
  console.error(`sync-shots: no screenshots at ${src}`);
  process.exit(1);
}

mkdirSync(dest, { recursive: true });
cpSync(src, dest, { recursive: true });
console.log(`sync-shots: ${src} → ${dest}`);
