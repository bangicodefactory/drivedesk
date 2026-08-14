import React from 'react';
import { AbsoluteFill, Series } from 'remotion';
import { COPY } from './copy.js';
import { SCENES, color } from './theme.js';
import {
  Contract,
  Dashboard,
  Fines,
  Fleet,
  Intro,
  Languages,
  Money,
  Planning,
  Problem,
  Outro,
  WhiteLabel,
} from './scenes/index.jsx';

const BY_ID = {
  intro: Intro,
  problem: Problem,
  dashboard: Dashboard,
  planning: Planning,
  fleet: Fleet,
  contract: Contract,
  money: Money,
  fines: Fines,
  languages: Languages,
  whitelabel: WhiteLabel,
  outro: Outro,
};

export const Promo = ({ lang = 'en' }) => {
  const c = COPY[lang];

  return (
    <AbsoluteFill style={{ background: color.dark1 }}>
      <Series>
        {SCENES.map(({ id, frames }) => {
          const Comp = BY_ID[id];
          return (
            <Series.Sequence key={id} durationInFrames={frames}>
              <Comp c={c} len={frames} lang={lang} />
            </Series.Sequence>
          );
        })}
      </Series>

      {/*
        Music: drop an mp3 at public/music.mp3 and uncomment. Nothing is
        bundled here — we have no licensed track to ship.

        <Audio src={staticFile('music.mp3')} volume={(f) =>
          interpolate(f, [TOTAL_FRAMES - 60, TOTAL_FRAMES], [0.5, 0], {extrapolateLeft: 'clamp'})
        } />
      */}
    </AbsoluteFill>
  );
};
