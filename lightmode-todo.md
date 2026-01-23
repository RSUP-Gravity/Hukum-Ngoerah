# Light Mode Improvement Todo

Scope: light mode only. Keep dark mode unchanged.

## Theme Tokens
- [x] Refine light-only tokens for background/surface/shadow in `resources/css/app.css`.
- [x] Increase light text contrast via `--text-secondary` and `--text-tertiary` in `resources/css/app.css`.
- [x] Add any required light-only overrides without touching `.dark` tokens in `resources/css/app.css`.
- [x] Sync light background/colors in `resources/views/components/layouts/app.blade.php`.
- [x] Sync light background/colors in `resources/views/components/layouts/auth.blade.php`.
- [x] Adjust light fallback surfaces in `resources/css/browser-compatibility.css`.

## Components
- [x] Tune light glass surfaces for cards/navbar/sidebar in `resources/css/app.css`.
- [x] Ensure light secondary/ghost buttons stay visible in `resources/css/app.css` and `resources/views/components/button.blade.php`.
- [x] Improve light inputs (border, placeholder, focus) in `resources/css/app.css` and:
  - `resources/views/components/input.blade.php`
  - `resources/views/components/select.blade.php`
  - `resources/views/components/textarea.blade.php`
- [x] Improve light table contrast in `resources/css/app.css` and `resources/views/components/table.blade.php`.
- [x] Soften light alert/toast backgrounds while keeping contrast in:
  - `resources/views/components/alert.blade.php`
  - `resources/views/layouts/partials/toast.blade.php`
- [x] Adjust light skeleton/lazy gradients in `resources/css/lazy-loading.css`.

## Pages
- [x] Tidy light Quick Actions palette in `resources/views/dashboard.blade.php`.
- [x] Improve light stats cards (numbers, labels, progress) in `resources/views/dashboard.blade.php`.
- [x] Tune light chart palette (grid, labels, tooltip) in `resources/views/dashboard.blade.php`.
- [x] Improve light navbar search chip contrast in `resources/views/layouts/partials/navbar.blade.php`.
- [x] Ensure light sidebar hover/active readability in `resources/views/layouts/partials/sidebar.blade.php`.
- [x] Audit light filters/toggles/highlights in `resources/views/documents/index.blade.php`.

## QA
- [ ] Verify theme toggle does not affect dark mode visuals.
- [ ] Check dashboard, documents list/detail, and auth pages in light mode.

Note: QA checks need manual verification in the browser.
