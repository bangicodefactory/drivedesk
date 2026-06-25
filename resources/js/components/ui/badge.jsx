import * as React from "react"
import { cva } from "class-variance-authority";

import { cn } from "@/lib/utils"

// Single source of truth for the tonal status variants. Each lists its tint
// (the cva variant class) and its dot colour together, so adding a status is a
// one-line change and the two can't drift apart. Classes are spelled out as
// literals — Tailwind only emits classes it finds verbatim in source, so these
// can't be templated (e.g. `bg-${tone}/10`).
//
// Status variants are tonal: a faint tint + a high-contrast foreground label,
// with the hue carried by a leading dot. Two adjacent columns of solid,
// fully-saturated badges read as a wall of colour; this keeps states scannable
// while the data leads. The dot — not coloured text — carries the hue, because
// coloured text on a 10% tint fails WCAG AA (amber worst, ~2.3:1) whereas
// foreground text on the tint clears it comfortably (~13:1).
const STATUS_VARIANTS = {
  success: { tint: "border-transparent bg-success/10 text-foreground", dot: "bg-success" },
  warning: { tint: "border-transparent bg-warning/10 text-foreground", dot: "bg-warning" },
  info: { tint: "border-transparent bg-info/10 text-foreground", dot: "bg-info" },
  destructive: { tint: "border-transparent bg-destructive/10 text-foreground", dot: "bg-destructive" },
}

const badgeVariants = cva(
  "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2",
  {
    variants: {
      variant: {
        default:
          "border-transparent bg-primary text-primary-foreground hover:bg-primary/80",
        secondary:
          "border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80",
        outline: "text-foreground",
        // Tonal status tints, derived from STATUS_VARIANTS so the dot below
        // always matches. Non-status variants (above) render without a dot.
        ...Object.fromEntries(
          Object.entries(STATUS_VARIANTS).map(([name, s]) => [name, s.tint]),
        ),
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

function Badge({
  className,
  variant,
  children,
  ...props
}) {
  const dot = STATUS_VARIANTS[variant]?.dot;
  return (
    <div className={cn(badgeVariants({ variant }), className)} {...props}>
      {dot && <span className={cn("h-1.5 w-1.5 shrink-0 rounded-full", dot)} aria-hidden="true" />}
      {children}
    </div>
  );
}

export { Badge, badgeVariants }
