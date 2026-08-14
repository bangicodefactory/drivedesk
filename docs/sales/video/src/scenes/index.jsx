import React from 'react';
import { interpolate, useCurrentFrame } from 'remotion';
import { color, gradient } from '../theme.js';
import { DASHBOARD_SHOT } from '../copy.js';
import {
  Callout,
  Chip,
  Kicker,
  Scene,
  Shot,
  SignatureCard,
  Sub,
  Tick,
  Title,
  Wordmark,
  useEnter,
} from '../components/ui.jsx';

/* ══════════════════════════════════════════════════════════════════ *
 * 1 — Intro
 * ══════════════════════════════════════════════════════════════════ */
export const Intro = ({ c, len }) => (
  <Scene dark dir={c.dir} length={len} style={{ alignItems: 'center', justifyContent: 'center' }}>
    <Wordmark size={72} delay={0} />
    <div style={{ height: 54 }} />
    <Kicker delay={18} dark dir={c.dir}>{c.intro.kicker}</Kicker>
    <Title
      text={c.intro.title.join('\n')}
      delay={26}
      size={86}
      dark
      accent={c.intro.accentLine}
      align="center"
    />
  </Scene>
);

/* ══════════════════════════════════════════════════════════════════ *
 * 2 — The problem
 * Each tool of the old way appears, then gets struck through.
 * ══════════════════════════════════════════════════════════════════ */
const StrikeLine = ({ children, delay }) => {
  const frame = useCurrentFrame();
  const e = useEnter(delay);
  const strike = interpolate(frame, [delay + 22, delay + 40], [0, 100], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });
  const dim = interpolate(frame, [delay + 30, delay + 46], [1, 0.42], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });
  return (
    <div
      style={{
        position: 'relative',
        display: 'inline-block',
        fontSize: 62,
        fontWeight: 700,
        color: '#f0e2db',
        opacity: e * dim,
        transform: `translateY(${(1 - e) * 20}px)`,
        marginBottom: 20,
      }}
    >
      {children}
      <div
        style={{
          position: 'absolute',
          insetInlineStart: 0,
          top: '54%',
          height: 5,
          width: `${strike}%`,
          background: color.brand,
          borderRadius: 3,
        }}
      />
    </div>
  );
};

export const Problem = ({ c, len }) => (
  <Scene dark dir={c.dir} length={len} style={{ justifyContent: 'center' }}>
    <Kicker delay={0} dark dir={c.dir}>{c.problem.kicker}</Kicker>
    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-start' }}>
      {c.problem.lines.map((l, i) => (
        <StrikeLine key={i} delay={10 + i * 15}>{l}</StrikeLine>
      ))}
    </div>
    <div style={{ height: 40 }} />
    <Title text={c.problem.punch.join('\n')} delay={108} size={74} dark accent={1} />
  </Scene>
);

/* ══════════════════════════════════════════════════════════════════ *
 * 3 — Dashboard
 * ══════════════════════════════════════════════════════════════════ */
export const Dashboard = ({ c, len, lang }) => {
  // The Arabic capture is mirrored: sidebar on the right, and the KPI cards run
  // right-to-left — so both the crop and the callout positions have to flip.
  const rtl = c.dir === 'rtl';
  const cardX = (i) => (rtl ? 86 - i * 24.7 : 14 + i * 24.7);

  return (
    <Scene dir={c.dir} length={len}>
      <Kicker delay={0} dir={c.dir}>{c.dashboard.kicker}</Kicker>
      <Title text={c.dashboard.title} delay={6} size={68} />
      <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', marginTop: 26 }}>
        <div style={{ position: 'relative', width: 1300 }}>
          <Shot
            src={DASHBOARD_SHOT[lang]}
            delay={16}
            crop={0.173}
            cropSide={rtl ? 'right' : 'left'}
            frameStyle={{ height: 560 }}
          />
          {/* y sits the pill on the top edge of each KPI card, clear of the page heading */}
          {c.dashboard.callouts.map((t, i) => (
            <Callout key={i} delay={46 + i * 11} x={`${cardX(i)}%`} y={146}>
              {t}
            </Callout>
          ))}
        </div>
      </div>
    </Scene>
  );
};

/* ══════════════════════════════════════════════════════════════════ *
 * 4 — Planning
 * ══════════════════════════════════════════════════════════════════ */
export const Planning = ({ c, len }) => (
  <Scene dir={c.dir} length={len}>
    <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', gap: 60 }}>
      <div>
        <Kicker delay={0} dir={c.dir}>{c.planning.kicker}</Kicker>
        <Title text={c.planning.title} delay={6} size={70} />
      </div>
      <Sub text={c.planning.sub} delay={22} size={28} />
    </div>
    <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center', marginTop: 30 }}>
      <Shot
        src="shots/en-13-planning.png"
        delay={16}
        crop={0.173}
        pan={90}
        frameStyle={{ width: 1560, height: 520 }}
      />
    </div>
  </Scene>
);

/* ══════════════════════════════════════════════════════════════════ *
 * 5 — Fleet & customers
 * ══════════════════════════════════════════════════════════════════ */
const ShotCard = ({ src, caption, delay, dir }) => (
  <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
    <Shot src={src} delay={delay} crop={0.173} frameStyle={{ height: 440 }} />
    <Sub text={caption} delay={delay + 10} size={27} />
  </div>
);

export const Fleet = ({ c, len }) => (
  <Scene dir={c.dir} length={len}>
    <Kicker delay={0} dir={c.dir}>{c.fleet.kicker}</Kicker>
    <Title text={c.fleet.title} delay={6} size={64} />
    <div style={{ flex: 1, display: 'flex', gap: 56, alignItems: 'center', marginTop: 24 }}>
      <ShotCard src="shots/en-11-vehicles.png" caption={c.fleet.left} delay={18} dir={c.dir} />
      <ShotCard src="shots/en-14-drivers.png" caption={c.fleet.right} delay={34} dir={c.dir} />
    </div>
  </Scene>
);

/* ══════════════════════════════════════════════════════════════════ *
 * 6 — Contracts & signature
 * ══════════════════════════════════════════════════════════════════ */
export const Contract = ({ c, len }) => (
  <Scene dir={c.dir} length={len}>
    <div style={{ display: 'flex', gap: 70, height: '100%', alignItems: 'center' }}>
      <div style={{ flex: 1 }}>
        <Kicker delay={0} dir={c.dir}>{c.contract.kicker}</Kicker>
        <Title text={c.contract.title} delay={6} size={62} />
        <div style={{ height: 46 }} />
        {c.contract.bullets.map((b, i) => (
          <Tick key={i} delay={40 + i * 12} dir={c.dir}>{b}</Tick>
        ))}
      </div>
      <div style={{ position: 'relative', width: 720 }}>
        <Shot
          src="shots/en-32-agreement-contract.png"
          delay={16}
          crop={0.173}
          pan={70}
          frameStyle={{ height: 780 }}
        />
        <div style={{ position: 'absolute', insetInlineEnd: -96, bottom: 60 }}>
          <SignatureCard delay={92} label="Signature client" width={470} />
        </div>
      </div>
    </div>
  </Scene>
);

/* ══════════════════════════════════════════════════════════════════ *
 * 7 — Money, VAT, invoices
 * The booking shot hands over to the VAT report halfway through.
 * ══════════════════════════════════════════════════════════════════ */
export const Money = ({ c, len }) => {
  const frame = useCurrentFrame();
  // Two dense screenshots cross-dissolving read as mush, so the outgoing one
  // clears out before the incoming one arrives, with a small push to sell it.
  const swap = interpolate(frame, [108, 128], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });
  const outOp = interpolate(swap, [0, 0.55], [1, 0], { extrapolateRight: 'clamp' });
  const inOp = interpolate(swap, [0.45, 1], [0, 1], { extrapolateLeft: 'clamp' });
  return (
    <Scene dir={c.dir} length={len}>
      <div style={{ display: 'flex', gap: 70, height: '100%', alignItems: 'center' }}>
        <div style={{ flex: 1 }}>
          <Kicker delay={0} dir={c.dir}>{c.money.kicker}</Kicker>
          <Title text={c.money.title} delay={6} size={60} />
          <div style={{ display: 'flex', gap: 18, marginTop: 44, direction: 'inherit' }}>
            {c.money.chips.map((t, i) => (
              <Chip key={i} delay={44 + i * 10} accent={i === 1}>{t}</Chip>
            ))}
          </div>
          <Sub text={c.money.sub} delay={80} size={29} />
        </div>
        <div style={{ position: 'relative', width: 900, height: 720 }}>
          <div style={{ position: 'absolute', inset: 0, opacity: outOp, transform: `translateY(${-swap * 46}px)` }}>
            <Shot
              src="shots/en-26-booking-detail.png"
              delay={16}
              crop={0.173}
              pan={60}
              frameStyle={{ height: 720 }}
            />
          </div>
          <div style={{ position: 'absolute', inset: 0, opacity: inOp, transform: `translateY(${(1 - swap) * 54}px)` }}>
            <Shot
              src="shots/en-25-tva-report.png"
              delay={104}
              crop={0.173}
              pan={110}
              frameStyle={{ height: 720 }}
            />
          </div>
        </div>
      </div>
    </Scene>
  );
};

/* ══════════════════════════════════════════════════════════════════ *
 * 8 — Traffic fines (the hero scene)
 * A plate and a timestamp are typed in; the renter's name resolves.
 * ══════════════════════════════════════════════════════════════════ */
export const Fines = ({ c, len }) => {
  const frame = useCurrentFrame();
  const chars = Math.max(0, Math.round(interpolate(frame, [64, 118], [0, c.fines.typed.length], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  })));
  const caret = frame % 20 < 11 && frame > 60 && frame < 126;
  const revealE = useEnter(140, { damping: 13, mass: 0.6 });
  const scan = interpolate(frame, [120, 142], [0, 1], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  return (
    <Scene dark dir={c.dir} length={len}>
      <div style={{ display: 'flex', gap: 70, height: '100%', alignItems: 'center' }}>
        <div style={{ flex: 1 }}>
      <Kicker delay={0} dark dir={c.dir}>{c.fines.kicker}</Kicker>
      <Title text={c.fines.title} delay={6} size={47} dark />

      {/* the input */}
      <div
        style={{
          marginTop: 54,
          alignSelf: 'flex-start',
          minWidth: 620,
          padding: '24px 32px',
          borderRadius: 16,
          border: `2px solid ${scan > 0 ? color.brand : 'rgba(255,255,255,.2)'}`,
          background: 'rgba(255,255,255,.05)',
          fontSize: 37,
          fontWeight: 700,
          letterSpacing: '0.02em',
          color: '#fff',
          direction: 'ltr',
          textAlign: c.dir === 'rtl' ? 'right' : 'left',
          boxShadow: scan > 0 ? `0 0 ${scan * 40}px rgba(226,87,31,.35)` : 'none',
        }}
      >
        {c.fines.typed.slice(0, chars)}
        <span style={{ opacity: caret ? 1 : 0, color: color.brandLt }}>|</span>
      </div>

      {/* the answer */}
      <div
        style={{
          marginTop: 30,
          alignSelf: 'flex-start',
          display: 'flex',
          alignItems: 'center',
          gap: 22,
          padding: '24px 36px',
          borderRadius: 16,
          background: gradient.brand,
          fontSize: 35,
          fontWeight: 800,
          color: '#fff',
          whiteSpace: 'nowrap',
          opacity: revealE,
          transform: `translateY(${(1 - revealE) * 26}px) scale(${0.92 + revealE * 0.08})`,
          boxShadow: '0 20px 50px rgba(226,87,31,.4)',
        }}
      >
        <span style={{ fontSize: 46 }}>{c.dir === 'rtl' ? '←' : '→'}</span>
        <span>{c.fines.reveal}</span>
      </div>

      <Sub text={c.fines.sub} delay={168} size={29} dark />
        </div>
        <div style={{ width: 780 }}>
          <Shot
            src="shots/en-33-traffic-violation-create.png"
            delay={30}
            crop={0.173}
            pan={60}
            frameStyle={{ height: 640 }}
          />
        </div>
      </div>
    </Scene>
  );
};

/* ══════════════════════════════════════════════════════════════════ *
 * 9 — Languages
 * ══════════════════════════════════════════════════════════════════ */
export const Languages = ({ c, len }) => (
  <Scene dir={c.dir} length={len}>
    <Kicker delay={0} dir={c.dir}>{c.languages.kicker}</Kicker>
    <Title text={c.languages.title} delay={6} size={58} />
    <div style={{ flex: 1, display: 'flex', gap: 48, alignItems: 'center', marginTop: 18 }}>
      <div style={{ flex: 1 }}>
        <Shot src="shots/fr-10-dashboard-viewport.png" delay={20} frameStyle={{ height: 430 }} style={{ width: '100%' }} />
      </div>
      <div style={{ flex: 1 }}>
        <Shot src="shots/ar-10-dashboard-rtl.png" delay={38} frameStyle={{ height: 430 }} style={{ width: '100%' }} />
      </div>
    </div>
    <div style={{ display: 'flex', gap: 18, justifyContent: 'center', direction: 'ltr' }}>
      {c.languages.tags.map((t, i) => (
        <Chip key={i} delay={70 + i * 10} accent={i === 2}>{t}</Chip>
      ))}
    </div>
  </Scene>
);

/* ══════════════════════════════════════════════════════════════════ *
 * 10 — White-label
 * ══════════════════════════════════════════════════════════════════ */
export const WhiteLabel = ({ c, len }) => (
  <Scene dark dir={c.dir} length={len}>
    <div style={{ display: 'flex', gap: 80, height: '100%', alignItems: 'center' }}>
      <div style={{ flex: 1 }}>
        <Kicker delay={0} dark dir={c.dir}>{c.whitelabel.kicker}</Kicker>
        <Title text={c.whitelabel.title} delay={8} size={76} dark accent={2} />
        <Sub text={c.whitelabel.sub} delay={62} size={31} dark />
      </div>
      <div style={{ width: 880 }}>
        <Shot
          src="shots/en-30-settings-company.png"
          delay={22}
          crop={0.173}
          pan={120}
          frameStyle={{ height: 700 }}
        />
      </div>
    </div>
  </Scene>
);

/* ══════════════════════════════════════════════════════════════════ *
 * 11 — Outro
 * ══════════════════════════════════════════════════════════════════ */
export const Outro = ({ c, len }) => {
  const cta = useEnter(48, { damping: 14, mass: 0.6 });
  const foot = useEnter(70);
  return (
    <Scene dark dir={c.dir} length={len} style={{ alignItems: 'center', justifyContent: 'center' }}>
      <Wordmark size={62} delay={0} />
      <div style={{ height: 44 }} />
      <Title text={c.outro.title} delay={14} size={72} dark accent={2} align="center" />
      <div
        style={{
          marginTop: 52,
          fontSize: 56,
          fontWeight: 800,
          letterSpacing: '-0.01em',
          padding: '20px 54px',
          borderRadius: 999,
          background: gradient.brand,
          color: '#fff',
          opacity: cta,
          transform: `scale(${0.9 + cta * 0.1})`,
          boxShadow: '0 22px 60px rgba(226,87,31,.45)',
          direction: 'ltr',
        }}
      >
        {c.outro.cta}
      </div>
      <div style={{ marginTop: 34, fontSize: 27, color: '#c6ada2', opacity: foot }}>
        {c.outro.foot}
      </div>
    </Scene>
  );
};
