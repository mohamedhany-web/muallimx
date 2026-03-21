#!/usr/bin/env python3
"""Fix corrupted dark mode classes from substring matches (e.g. bg-sky-50 inside bg-sky-500)."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TARGETS = list(ROOT.glob("resources/views/instructor/**/*.blade.php"))
TARGETS.append(ROOT / "resources/views/dashboard/instructor.blade.php")

# (pattern, replacement) — order matters
FIXES: list[tuple[str, str]] = [
    # Room header icon (was bg-rose-500/20)
    (r"bg-rose-50 dark:bg-rose-900/300/20", "bg-rose-500/20 dark:bg-rose-900/40"),
    (r"hover:bg-rose-50 dark:bg-rose-900/300", "hover:bg-rose-700"),
    # Progress bar fills (were *-500)
    (r"h-full bg-sky-50 dark:bg-sky-900/300", "h-full bg-sky-500 dark:bg-sky-400"),
    (r"h-full bg-emerald-50 dark:bg-emerald-900/300", "h-full bg-emerald-500 dark:bg-emerald-400"),
    (r"h-full bg-amber-50 dark:bg-amber-900/300", "h-full bg-amber-500 dark:bg-amber-400"),
    (r"h-full bg-red-50 dark:bg-red-900/300", "h-full bg-red-500 dark:bg-red-400"),
    # Primary solid buttons (were *-500 + hover *-600)
    (r"bg-sky-50 dark:bg-sky-900/300", "bg-sky-500 dark:bg-sky-600"),
    (r"bg-red-50 dark:bg-red-900/300", "bg-red-600 dark:bg-red-700"),
    (r"bg-emerald-50 dark:bg-emerald-900/300", "bg-emerald-600 dark:bg-emerald-700"),
    (r"bg-violet-50 dark:bg-violet-900/300", "bg-violet-600 dark:bg-violet-700"),
    (r"bg-blue-50 dark:bg-blue-900/300", "bg-blue-600 dark:bg-blue-700"),
    # Amber / rose buttons
    (r"bg-amber-50 dark:bg-amber-900/300", "bg-amber-500 dark:bg-amber-600"),
    (r"bg-rose-50 dark:bg-rose-900/300", "bg-rose-600 dark:bg-rose-700"),
]


def main() -> None:
    changed = 0
    for path in TARGETS:
        if not path.is_file():
            continue
        text = path.read_text(encoding="utf-8")
        orig = text
        for pat, repl in FIXES:
            text = re.sub(pat, repl, text)
        if text != orig:
            path.write_text(text, encoding="utf-8")
            changed += 1
            print(f"fixed: {path.relative_to(ROOT)}")
    print(f"Done. Files: {changed}")


if __name__ == "__main__":
    main()
