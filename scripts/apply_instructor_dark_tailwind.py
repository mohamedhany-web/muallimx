#!/usr/bin/env python3
"""
Add Tailwind dark: variants to instructor Blade views (idempotent).
Run from project root: python scripts/apply_instructor_dark_tailwind.py
"""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

# (pattern without lookahead, replacement) — applied in order; use (?!\s+dark:) to skip
REPLACEMENTS: list[tuple[str, str]] = [
    # Surfaces & borders
    (r"bg-white(?!\s+dark:)", "bg-white dark:bg-slate-800/95"),
    (r"\bbg-slate-50\b(?!\s+dark:)", "bg-slate-50 dark:bg-slate-800/40"),
    (r"\bbg-slate-100\b(?!\s+dark:)", "bg-slate-100 dark:bg-slate-700/50"),
    (r"border-slate-100(?!\s+dark:)", "border-slate-100 dark:border-slate-700/80"),
    (r"border-slate-200(?!\s+dark:)", "border-slate-200 dark:border-slate-700"),
    (r"divide-slate-100(?!\s+dark:)", "divide-slate-100 dark:divide-slate-700"),
    (r"divide-slate-200(?!\s+dark:)", "divide-slate-200 dark:divide-slate-700"),
    # Text
    (r"text-slate-900(?!\s+dark:)", "text-slate-900 dark:text-slate-50"),
    (r"text-slate-800(?!\s+dark:)", "text-slate-800 dark:text-slate-100"),
    (r"text-slate-700(?!\s+dark:)", "text-slate-700 dark:text-slate-300"),
    (r"text-slate-600(?!\s+dark:)", "text-slate-600 dark:text-slate-400"),
    (r"text-slate-500(?!\s+dark:)", "text-slate-500 dark:text-slate-400"),
    # Colored light backgrounds (icons) — use \\b so bg-sky-50 does not match inside bg-sky-500
    (r"\bbg-sky-50\b(?!\s+dark:)", "bg-sky-50 dark:bg-sky-900/30"),
    (r"\bbg-blue-50\b(?!\s+dark:)", "bg-blue-50 dark:bg-blue-900/30"),
    (r"\bbg-indigo-50\b(?!\s+dark:)", "bg-indigo-50 dark:bg-indigo-900/30"),
    (r"\bbg-violet-50\b(?!\s+dark:)", "bg-violet-50 dark:bg-violet-900/30"),
    (r"\bbg-emerald-50\b(?!\s+dark:)", "bg-emerald-50 dark:bg-emerald-900/30"),
    (r"\bbg-amber-50\b(?!\s+dark:)", "bg-amber-50 dark:bg-amber-900/30"),
    (r"\bbg-rose-50\b(?!\s+dark:)", "bg-rose-50 dark:bg-rose-900/30"),
    (r"\bbg-cyan-50\b(?!\s+dark:)", "bg-cyan-50 dark:bg-cyan-900/30"),
    (r"\bbg-teal-50\b(?!\s+dark:)", "bg-teal-50 dark:bg-teal-900/30"),
    (r"\bbg-red-50\b(?!\s+dark:)", "bg-red-50 dark:bg-red-900/30"),
    # Hovers on slate
    (r"hover:bg-slate-50(?!\s+dark:)", "hover:bg-slate-50 dark:hover:bg-slate-700/40"),
    (r"hover:bg-slate-100(?!\s+dark:)", "hover:bg-slate-100 dark:hover:bg-slate-700/50"),
    (r"hover:bg-gray-50(?!\s+dark:)", "hover:bg-gray-50 dark:hover:bg-slate-700/40"),
    # Status chips
    (r"bg-emerald-100(?!\s+dark:)", "bg-emerald-100 dark:bg-emerald-900/40"),
    (r"bg-rose-100(?!\s+dark:)", "bg-rose-100 dark:bg-rose-900/40"),
    (r"text-emerald-700(?!\s+dark:)", "text-emerald-700 dark:text-emerald-400"),
    (r"text-rose-700(?!\s+dark:)", "text-rose-700 dark:text-rose-400"),
]

GLOBS = [
    "resources/views/instructor/**/*.blade.php",
    "resources/views/dashboard/instructor.blade.php",
]


def process_file(path: Path) -> bool:
    text = path.read_text(encoding="utf-8")
    orig = text
    for pat, repl in REPLACEMENTS:
        text = re.sub(pat, repl, text)
    if text != orig:
        path.write_text(text, encoding="utf-8")
        return True
    return False


def main() -> None:
    changed = 0
    seen: set[Path] = set()
    for pattern in GLOBS:
        for path in ROOT.glob(pattern):
            if path in seen:
                continue
            seen.add(path)
            if process_file(path):
                changed += 1
                print(f"updated: {path.relative_to(ROOT)}")
    print(f"Done. Files changed: {changed}")


if __name__ == "__main__":
    main()
