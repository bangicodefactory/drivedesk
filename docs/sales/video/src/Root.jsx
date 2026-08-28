import React from 'react';
import { Composition } from 'remotion';
import { Promo } from './Promo.jsx';
import { FPS, HEIGHT, TOTAL_FRAMES, WIDTH } from './theme.js';

const base = {
  component: Promo,
  durationInFrames: TOTAL_FRAMES,
  fps: FPS,
  width: WIDTH,
  height: HEIGHT,
};

export const RemotionRoot = () => (
  <>
    <Composition id="PromoEN" {...base} defaultProps={{ lang: 'en' }} />
    <Composition id="PromoFR" {...base} defaultProps={{ lang: 'fr' }} />
    <Composition id="PromoAR" {...base} defaultProps={{ lang: 'ar' }} />
  </>
);
