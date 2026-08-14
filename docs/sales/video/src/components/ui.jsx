import React from 'react';
import { AbsoluteFill, Img, interpolate, spring, staticFile, useCurrentFrame, useVideoConfig } from 'remotion';
import { color, font, gradient } from '../theme.js';

/* ------------------------------------------------------------------ *
 * animation helpers
 * ------------------------------------------------------------------ */

/** Eased 0 → 1 over [from, from+len], clamped at both ends. */
export const useFade = (from, len = 18) => {
  const frame = useCurrentFrame();
  return interpolate(frame, [from, from + len], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });
};

/** Spring 0 → 1 that starts at `delay`. Used for anything that should feel physical. */
export const useEnter = (delay = 0, config = { damping: 200, mass: 0.6 }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  return spring({ frame: frame - delay, fps, config, durationInFrames: 26 });
};

/** Fade out over the last `len` frames of a scene, so cuts never snap to black. */
export const useOutro = (sceneLength, len = 14) => {
  const frame = useCurrentFrame();
  return interpolate(frame, [sceneLength - len, sceneLength], [1, 0], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });
};

/* ------------------------------------------------------------------ *
 * layout
 * ------------------------------------------------------------------ */

/** Scene shell: sets the background, the text direction and the outro fade. */
export const Scene = ({ children, dark = false, dir = 'ltr', length, style }) => {
  const out = useOutro(length);
  return (
    <AbsoluteFill
      style={{
        background: dark ? gradient.dark : color.paper,
        color: dark ? color.paper : color.ink,
        fontFamily: dir === 'rtl' ? font.arabic : font.sans,
        direction: dir,
        opacity: out,
        padding: '86px 108px',
        ...style,
      }}
    >
      {children}
    </AbsoluteFill>
  );
};

/** Small uppercase label above a headline. */
export const Kicker = ({ children, delay = 0, dark = false, dir = 'ltr' }) => {
  const e = useEnter(delay);
  return (
    <div
      style={{
        fontSize: 26,
        fontWeight: 800,
        letterSpacing: dir === 'rtl' ? 0 : '0.24em',
        textTransform: dir === 'rtl' ? 'none' : 'uppercase',
        color: dark ? color.brandLt : color.brand,
        opacity: e,
        transform: `translateY(${(1 - e) * 14}px)`,
        marginBottom: 22,
      }}
    >
      {children}
    </div>
  );
};

/**
 * Headline. Splits on "\n" and staggers each line in, which reads far better
 * at speed than fading a whole paragraph at once.
 */
export const Title = ({ text, delay = 0, size = 92, dark = false, accent, align = 'start' }) => {
  const lines = String(text).split('\n');
  return (
    <div style={{ display: 'flex', flexDirection: 'column', alignItems: align === 'center' ? 'center' : 'flex-start' }}>
      {lines.map((line, i) => (
        <Line
          key={i}
          delay={delay + i * 5}
          size={size}
          dark={dark}
          accent={accent === i}
          align={align}
        >
          {line}
        </Line>
      ))}
    </div>
  );
};

const Line = ({ children, delay, size, dark, accent, align }) => {
  const e = useEnter(delay);
  return (
    <div
      style={{
        fontSize: size,
        lineHeight: 1.08,
        fontWeight: 800,
        letterSpacing: '-0.025em',
        textAlign: align === 'center' ? 'center' : 'inherit',
        color: accent ? color.brand : dark ? color.paper : color.ink,
        opacity: e,
        transform: `translateY(${(1 - e) * 26}px)`,
      }}
    >
      {children}
    </div>
  );
};

/** Secondary paragraph under a headline. */
export const Sub = ({ text, delay = 0, size = 34, dark = false, align = 'start' }) => {
  const e = useEnter(delay);
  return (
    <div
      style={{
        fontSize: size,
        lineHeight: 1.45,
        color: dark ? '#e2cfc5' : color.soft,
        whiteSpace: 'pre-line',
        textAlign: align === 'center' ? 'center' : 'inherit',
        opacity: e,
        transform: `translateY(${(1 - e) * 16}px)`,
        marginTop: 26,
        maxWidth: 1080,
      }}
    >
      {text}
    </div>
  );
};

/* ------------------------------------------------------------------ *
 * screenshot frame
 * ------------------------------------------------------------------ */

/**
 * A screenshot in a browser-ish frame.
 *
 * `crop` trims a fraction off the left of the source image — the captures all
 * include the app sidebar, and for document shots we want it gone.
 * `pan` slowly drifts the image upward so long screenshots feel alive.
 */
export const Shot = ({
  src,
  delay = 0,
  crop = 0,
  cropSide = 'left',
  pan = 0,
  radius = 18,
  style,
  frameStyle,
}) => {
  const frame = useCurrentFrame();
  const e = useEnter(delay, { damping: 200, mass: 0.9 });
  const drift = interpolate(frame - delay, [0, 240], [0, -pan], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  return (
    <div
      style={{
        position: 'relative',
        overflow: 'hidden',
        borderRadius: radius,
        background: color.paper,
        border: `1px solid ${color.line}`,
        boxShadow: '0 40px 90px rgba(0,0,0,.28)',
        opacity: e,
        transform: `translateY(${(1 - e) * 40}px) scale(${0.965 + e * 0.035})`,
        direction: 'ltr',
        ...frameStyle,
      }}
    >
      <Img
        src={staticFile(src)}
        style={{
          display: 'block',
          width: `${100 / (1 - crop)}%`,
          // An Arabic capture has its sidebar on the right, so the crop has to
          // come off the other edge — there we just let overflow clip it.
          marginLeft: cropSide === 'left' ? `${-crop * 100 / (1 - crop)}%` : 0,
          transform: `translateY(${drift}px)`,
          ...style,
        }}
      />
    </div>
  );
};

/* ------------------------------------------------------------------ *
 * accents
 * ------------------------------------------------------------------ */

/** A pill that pops onto a point of interest in a screenshot. */
export const Callout = ({ children, delay, x, y }) => {
  const e = useEnter(delay, { damping: 14, mass: 0.5 });
  return (
    <div
      style={{
        position: 'absolute',
        left: x,
        top: y,
        transform: `translate(-50%, -50%) scale(${0.7 + e * 0.3})`,
        opacity: e,
        background: gradient.brand,
        color: '#fff',
        fontSize: 25,
        fontWeight: 700,
        padding: '11px 22px',
        borderRadius: 999,
        whiteSpace: 'nowrap',
        boxShadow: '0 12px 30px rgba(226,87,31,.42)',
        direction: 'inherit',
      }}
    >
      {children}
    </div>
  );
};

/** Small outlined tag, used for HT / TVA / TTC and the language chips. */
export const Chip = ({ children, delay, dark = false, accent = false }) => {
  const e = useEnter(delay, { damping: 16, mass: 0.5 });
  return (
    <div
      style={{
        fontSize: 34,
        fontWeight: 700,
        padding: '14px 32px',
        borderRadius: 14,
        border: `2px solid ${accent ? color.brand : dark ? 'rgba(255,255,255,.22)' : color.line}`,
        background: accent ? gradient.brand : dark ? 'rgba(255,255,255,.05)' : color.wash,
        color: accent ? '#fff' : dark ? '#fff' : color.brandDeep,
        opacity: e,
        transform: `translateY(${(1 - e) * 18}px)`,
      }}
    >
      {children}
    </div>
  );
};

/** Ticked list item that slides in. */
export const Tick = ({ children, delay, dark = false, dir = 'ltr' }) => {
  const e = useEnter(delay);
  return (
    <div
      style={{
        display: 'flex',
        alignItems: 'center',
        gap: 18,
        fontSize: 38,
        fontWeight: 600,
        marginBottom: 24,
        opacity: e,
        transform: `translate${dir === 'rtl' ? 'X' : 'X'}(${(1 - e) * (dir === 'rtl' ? 26 : -26)}px)`,
        color: dark ? '#f3e6e0' : color.ink,
      }}
    >
      <span style={{ color: color.brand, fontSize: 40, fontWeight: 800, lineHeight: 1 }}>✓</span>
      <span>{children}</span>
    </div>
  );
};

/**
 * A signature that writes itself, drawn as SVG so it stays crisp and needs no
 * asset. Reads far better than a screenshot of the signature list, which is
 * illegible at this size.
 */
export const SignatureCard = ({ delay = 0, label, width = 520 }) => {
  const frame = useCurrentFrame();
  const e = useEnter(delay, { damping: 18, mass: 0.7 });
  const t = frame - delay - 10;

  // len = rough path length (for the dash trick), dur = frames to draw it.
  // The whole signature lands in ~44 frames, about how long a real one takes.
  const strokes = [
    { d: 'M14,78 C40,18 58,16 64,46 C70,76 48,100 40,82 C32,64 66,32 98,38 C130,44 126,88 112,90 C98,92 102,50 136,42 C170,34 190,66 184,82', len: 560, dur: 22 },
    { d: 'M196,74 C214,44 238,36 254,48 C270,60 250,86 236,80 C222,74 244,44 286,52 C316,58 330,72 336,86', len: 330, dur: 14 },
    { d: 'M92,98 L300,44', len: 215, dur: 8 },
  ];

  let start = 0;
  return (
    <div
      style={{
        width,
        background: color.paper,
        borderRadius: 16,
        border: `1px solid ${color.line}`,
        boxShadow: '0 30px 70px rgba(0,0,0,.26)',
        padding: '20px 26px 16px',
        opacity: e,
        transform: `translateY(${(1 - e) * 30}px) scale(${0.94 + e * 0.06})`,
        direction: 'ltr',
      }}
    >
      <div style={{ fontSize: 19, fontWeight: 700, color: color.faint, letterSpacing: '0.06em', textTransform: 'uppercase' }}>
        {label}
      </div>
      <svg viewBox="0 0 360 120" style={{ width: '100%', height: width * 0.3, display: 'block' }}>
        {strokes.map((s, i) => {
          const from = start;
          start += s.dur;
          const drawn = interpolate(t, [from, from + s.dur], [0, s.len], {
            extrapolateLeft: 'clamp',
            extrapolateRight: 'clamp',
          });
          return (
            <path
              key={i}
              d={s.d}
              fill="none"
              stroke={color.ink}
              strokeWidth={5}
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeDasharray={s.len}
              strokeDashoffset={s.len - drawn}
            />
          );
        })}
      </svg>
      <div style={{ height: 2, background: color.line, marginTop: 4 }} />
    </div>
  );
};

/** The DriveDesk wordmark, drawn rather than loaded so it needs no asset. */
export const Wordmark = ({ size = 64, delay = 0 }) => {
  const e = useEnter(delay, { damping: 18, mass: 0.7 });
  return (
    <div
      style={{
        display: 'flex',
        alignItems: 'center',
        gap: size * 0.34,
        opacity: e,
        transform: `translateY(${(1 - e) * 18}px)`,
        direction: 'ltr',
      }}
    >
      <div
        style={{
          width: size * 1.06,
          height: size * 1.06,
          borderRadius: '50%',
          background: gradient.brand,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          boxShadow: '0 10px 30px rgba(226,87,31,.45)',
        }}
      >
        <div
          style={{
            width: size * 0.5,
            height: size * 0.5,
            borderRadius: '50%',
            border: `${Math.max(3, size * 0.07)}px solid #fff`,
            position: 'relative',
          }}
        >
          <div
            style={{
              position: 'absolute',
              left: '50%',
              top: '50%',
              width: size * 0.19,
              height: Math.max(2, size * 0.045),
              background: '#fff',
              borderRadius: 2,
              transformOrigin: 'left center',
              transform: 'translate(0,-50%) rotate(-38deg)',
            }}
          />
        </div>
      </div>
      <div
        style={{
          fontSize: size,
          fontWeight: 800,
          letterSpacing: '-0.03em',
          fontStyle: 'italic',
          color: '#fff',
        }}
      >
        DRIVE<span style={{ color: color.brandLt }}>DESK</span>
      </div>
    </div>
  );
};
