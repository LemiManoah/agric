# Implementation Progress

## Achieved

- Installed and wired `livewire/livewire`, `spatie/laravel-permission`, and `spatie/laravel-activitylog`.
- Published the permission and activitylog package config/migration files.
- Extended the existing auth foundation instead of replacing it:
  - `users` business fields migration
  - `User` model role support, activity logging, and geography relations
  - permission middleware aliases in `bootstrap/app.php`
- Added the first M1 foundation schema:
  - `regions`, `districts`, `subcounties`, `parishes`, `villages`
  - `farmers`
  - `farmer_locations`
- Added core enums for user status, registration source, verification status, and internet access level.
- Added geography and farmer registry models, including regional visibility logic on `Farmer`.
- Added `RolePermissionSeeder` with the Phase 1 role matrix and `UgandaLocationSeeder` with starter Uganda hierarchy data.
- Updated `DatabaseSeeder` to load RBAC and geography seed data first.
- Built the first admin farmer registry surface:
  - Livewire page component at `admin/farmers`
  - searchable/filterable farmer list
  - regional admin query scoping
- Added focused Pest coverage for:
  - role/permission seeding
  - route authorization
  - regional scoping
  - Livewire search filtering
- Added the next M1 workflow batch:
  - `FarmerPolicy` with super admin bypass plus regional and field officer scope rules
  - `FarmerRegistrationService` for create, update, and verify workflows
  - admin farmer map Livewire page with Leaflet rendering hooks and coordinate-only dataset loading
  - public/auth-ready farmer registration wizard with dependent geography selects and mapping inputs
  - route additions for the map page and registration wizard
  - Pest coverage for the map page, registration wizard, and farmer registration service
- Extended the farmer management workflow:
  - farmer detail page with location summary, activity timeline, and verification panel
  - farmer edit flow with normalized geography, coordinates, passport photo support, business profile, and value chain production data
  - field officer registration surface for internal farmer onboarding
  - first farmer overview reporting page with CSV export support
- Added the next M1 ecosystem profiling batch:
  - supplier schema, pivots, model, policy, service, index/form/show pages, verification actions, and CSV export
  - agent schema, pivots, model, policy, service, index/form pages, auto-generated `AGT-XXXXX` codes, and CSV export
  - agribusiness schema, district coverage pivot, model, policy, service, index/form pages, and CSV export
  - reusable `CsvExportService` for the first M1 admin lists and reports
  - M1 profile summary report covering suppliers, agents, and agribusiness entity totals
  - route wiring for suppliers, agents, agribusiness profiles, and the M1 profile summary report
  - focused Pest coverage for supplier, agent, agribusiness, report, and export flows
- Updated the application sidebar navigation so the completed M1 farmer, supplier, agent, agribusiness, and report pages are reachable from the starter-kit layout.
- Updated the auth layout so guest-facing Livewire pages can render without rebuilding the starter-kit scaffolding.
- Added the M2 foundation batch:
  - buyer registration, admin management, verification actions, and buyer portal profile
  - product categories, supplier-linked products, product images, price history, and admin catalogue management
  - public catalogue browsing and product detail pages
  - richer M2 factories and demo seeders for buyers and catalogue data
- Added the M2 cart and order foundation:
  - carts and cart items
  - orders, order items, and order status history
  - `OrderPolicy`, `CartService`, `OrderService`, `StockService`, and `OrderNumberGenerator`
  - buyer cart, checkout, order history, and order detail pages
  - agent-assisted checkout and agent order history
  - admin order list, detail page, status actions, and CSV export support
  - cart/order factories plus `OrderDemoSeeder` for practical local UI review
  - focused Pest coverage for cart, checkout, buyer/agent order flows, admin order management, services, and demo seeding
- Fixed UI regressions introduced during the M2 expansion:
  - restored working `admin/buyers/create` and `admin/products/create` route matching by resolving route-order collisions
  - replaced child sidebar icons with simple bullet markers
  - corrected label/input alignment on the user, buyer, and product create/edit forms that use the shared `x-forms.input` component inside grid layouts

## Next

- Continue with the next approved checklist batch without refactoring completed M1 or current M2 work.

## Notes

- `project.md` asked for normalized geography. I used `village_id` on `farmer_locations` instead of a free-text village field so dependent selects and reporting can stay consistent.
- The starter kit layout now includes Livewire assets, and Alpine is no longer booted separately in `resources/js/bootstrap.js` to avoid double-loading it.
- Internal navigation now reflects the implemented M1 modules instead of the starter-kit example sidebar links.
