#!/usr/bin/env python3
"""Fix double /opacity and bg-slate-500 mistaken for bg-slate-50."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TARGETS = list(ROOT.glob("resources/views/instructor/**/*.blade.php"))
TARGETS.append(ROOT / "resources/views/dashboard/instructor.blade.php")

FIXES: list[tuple[str, str]] = [
    # Back button that was bg-slate-500
    (r"bg-slate-50 dark:bg-slate-800/400\b", "bg-slate-500 dark:bg-slate-600"),
    # Stacked opacity typos
    (r"dark:bg-slate-800/40/50", "dark:bg-slate-800/50"),
    (r"dark:bg-slate-800/40/80", "dark:bg-slate-800/60"),
    (r"dark:bg-slate-800/40/70", "dark:bg-slate-800/50"),
    (r"dark:bg-sky-900/30/50", "dark:bg-sky-900/40"),
]


def main() -> None:
    n = 0
    for path in TARGETS:
        if not path.is_file():
            continue
        t = path.read_text(encoding="utf-8")
        o = t
        for a, b in FIXES:
            t = re.sub(a, b, t)
        if t != o:
            path.write_text(t, encoding="utf-8")
            n += 1
            print(path.relative_to(ROOT))
    print("files:", n)


if __name__ == "__main__":
    main()
