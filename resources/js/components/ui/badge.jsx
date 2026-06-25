import * as React from "react"
import { cva } from "class-variance-authority";

import { cn } from "@/lib/utils"

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
        // Semantic status variants are tonal: a faint tint + a high-contrast
        // foreground label, with the hue carried by a leading dot (rendered
        // below). Two adjacent columns of solid, fully-saturated badges read as
        // a wall of colour; this keeps states scannable while the data leads.
        // The dot — not coloured text — carries the hue, because coloured text
        // on a 10% tint fails WCAG AA (amber worst, ~2.3:1) whereas foreground
        // text on the tint clears it comfortably (~13:1).
        success: "border-transparent bg-success/10 text-foreground",
        warning: "border-transparent bg-warning/10 text-foreground",
        info: "border-transparent bg-info/10 text-foreground",
        destructive: "border-transparent bg-destructive/10 text-foreground",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

// Dot colour per status variant; absent for non-status variants (default,
// secondary, outline) so counts/labels/codes render without a dot.
const STATUS_DOT = {
  success: "bg-success",
  warning: "bg-warning",
  info: "bg-info",
  destructive: "bg-destructive",
}

function Badge({
  className,
  variant,
  children,
  ...props
}) {
  const dot = STATUS_DOT[variant];
  return (
    <div className={cn(badgeVariants({ variant }), className)} {...props}>
      {dot && <span className={cn("h-1.5 w-1.5 shrink-0 rounded-full", dot)} aria-hidden="true" />}
      {children}
    </div>
  );
}

export { Badge, badgeVariants }
