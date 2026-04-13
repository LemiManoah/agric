Perfect — below is a Codex-friendly phased implementation blueprint rewritten for your actual stack:

Laravel 12
Laravel Daily Blade starter kit
Livewire for application modules
Alpine.js where helpful
Spatie Laravel Permission
Spatie Activitylog
existing auth stays in place and is extended
map view for farms
MySQL + Redis + S3/MinIO
Google Maps or Leaflet for farm map/polygon support

I’ve also adjusted the plan so Codex does not rebuild auth from scratch, but instead extends what already exists.

The source spec defines four Phase 1 modules—M1 Farmer Registry & Core Profiling, M2 Marketplace, M8 Multi-Channel Communication, and M9 Platform Administration & Reporting—and makes M1 the foundational module that everything else depends on. The same spec also requires role-based access, queue-backed notifications, product/order/payment flows, reporting, and a regional distribution map for farmer locations.

1. Implementation blueprint for Codex
A. Architecture direction

Use the Laravel Daily starter kit as the shell for:

auth
dashboard
profile settings
app layout
navigation
base admin styling

Then build the actual business modules with:

Livewire components for forms, tables, filters, dashboards, and workflows
Blade layouts from the starter kit as page containers
Alpine.js only for small UI interactions
service classes for business logic
Spatie Permission for RBAC
Spatie Activitylog for audit/event logging
Important rule

Do not replace the starter kit.
Instead:

keep its auth and layout structure,
extend the existing users/profile flow,
add new domain tables and Livewire modules around it.

That fits your stack much better than forcing an API-first Vue architecture.

2. Phase 1 system modules in your actual app
M1 — Farmer Registry & Core Profiling

This is the foundation. It stores and manages:

farmers
suppliers
agents
agribusiness/cooperatives
location hierarchy
farm data
farm production/value chains
farm GPS and optional boundaries
verification status and registration source
M2 — Marketplace

This handles:

buyers
products
supplier-linked inventory
cart
checkout
orders
payments
agent-assisted orders
order tracking
receipt generation
M8 — Communications

This handles:

SMS
WhatsApp
Email
templates
queued delivery
callbacks
delivery logging and stats
M9 — Administration & Reporting

This handles:

RBAC
user management
dashboards
maps
activity/audit logs
exports
regional visibility rules
3. Database design you should give Codex

Below is a practical schema for Laravel + Livewire.

Core auth/user tables
users

Use existing starter-kit table. Extend it with only what is needed:

id
name
email nullable/unique
phone nullable/unique
password nullable for phone-first users if needed
status enum: active, suspended, pending
region_id nullable
district_id nullable
last_login_at nullable
created_by nullable
timestamps
profiles

Optional if you want to keep user core clean.

id
user_id
avatar_path
gender
date_of_birth
profession
household_size
number_of_dependants
education_level
languages_spoken json
registration_source
timestamps

You can also merge these into users if you want fewer tables, but for a multi-role system a separate profile table is cleaner.

Geography tables
regions
id
name
code
timestamps
districts
id
region_id
name
code
timestamps
subcounties
id
district_id
name
code
timestamps
parishes
id
subcounty_id
name
timestamps
villages
id
parish_id
name
timestamps

This supports the required hierarchy of region → district → subcounty → parish → village.

Farmer and farm domain
farmers

This is the main farmer registry table.

id
user_id nullable
full_name
phone
national_id_number
passport_photo_path
gender
date_of_birth
education_level
profession
household_size
number_of_dependants
languages_spoken json
registration_source enum: self_registered, field_officer, imported
registered_by_user_id nullable
verification_status enum: submitted, pending_review, verified, suspended, rejected
verified_at nullable
verified_by_user_id nullable
timestamps
soft deletes optional
farmer_locations
id
farmer_id
region_id
district_id
subcounty_id
parish_id nullable
village string
latitude decimal(10,8) nullable
longitude decimal(11,8) nullable
farm_boundary_geojson longText nullable
nearest_trading_centre nullable
distance_to_tarmac_road_km decimal(8,2) nullable
internet_access_level enum: none, 2g, 3g, 4g
timestamps
farmer_business_profiles
id
farmer_id
farm_name
ursb_registration_number
farm_size_acres decimal(10,2)
number_of_plots integer nullable
irrigation_availability enum: none, partial, full
post_harvest_storage_capacity_tonnes decimal(10,2) nullable
has_warehouse_access boolean
cooperative_member boolean
cooperative_name nullable
cooperative_role nullable
average_annual_income_bracket enum nullable
timestamps
farming_activity_types
id
name
farmer_farming_activities

pivot:

farmer_id
farming_activity_type_id
equipment_types
id
name
farmer_equipment

pivot:

farmer_id
equipment_type_id
value_chains
id
name
slug
is_active
farmer_value_chains
id
farmer_id
value_chain_id
production_scale enum: subsistence, local_market, commercial, export
estimated_seasonal_harvest_kg decimal(12,2) nullable
current_market_destination enum: local_market, wholesale, cooperative_aggregation, direct_export
input_access_details json nullable
timestamps

This supports the spec’s farm business and production capture.

Supplier domain
suppliers
id
user_id nullable
farmer_id nullable
business_name
contact_person
phone
email nullable
operating_district_id nullable
typical_supply_volume_kg_per_month decimal(12,2) nullable
supply_frequency enum: daily, weekly, monthly, seasonal
warehouse_linked boolean default false
verification_status enum: pending, verified, suspended
verified_at nullable
verified_by_user_id nullable
timestamps
supplier_value_chains

pivot:

supplier_id
value_chain_id
quality_grades
id
name
supplier_quality_grades

pivot:

supplier_id
quality_grade_id

This matches supplier profiling and warehouse linking requirements.

Agent domain
agents
id
user_id nullable
full_name
agent_code unique
phone
email nullable
primary_district_id
commission_rate decimal(5,2)
total_orders_placed integer default 0
total_commission_earned decimal(14,2) default 0
onboarding_status enum: onboarding, active, suspended
timestamps
agent_regions

pivot:

agent_id
region_id
agent_value_chains

pivot:

agent_id
value_chain_id

This supports agent territories, performance, and commission tracking.

Agribusiness/cooperative domain
agribusiness_profiles
id
user_id nullable
entity_type enum: cooperative, tractor_association, input_dealer, grain_miller, cold_chain_operator, export_company, agro_dealer
organization_name
registration_number nullable
membership_size nullable
fleet_size nullable
service_rates nullable
product_range text nullable
processing_capacity_tonnes_per_day decimal(10,2) nullable
export_markets text nullable
buyer_criteria text nullable
contact_person
contact_phone
timestamps
agribusiness_districts

pivot:

agribusiness_profile_id
district_id
Buyer domain
buyers
id
user_id nullable
company_name
country
business_type
company_registration_number nullable
contact_person_full_name
phone
email
annual_import_volume_usd_range nullable
preferred_payment_method nullable
verification_status enum: pending, verified, suspended
verified_at nullable
verified_by_user_id nullable
timestamps
buyer_value_chain_interests

pivot:

buyer_id
value_chain_id

This supports buyer registration and segmentation.

Product and marketplace domain
product_categories

You may reuse value_chains directly, but if you want a cleaner catalog abstraction:

id
name
slug
linked_value_chain_id nullable
is_active
products
id
name
product_category_id
linked_supplier_id
description longText
quality_grade_id nullable
unit_of_measure
price_per_unit_usd decimal(14,2)
minimum_order_quantity decimal(14,2)
stock_available decimal(14,2)
listing_status enum: draft, active, out_of_stock, archived
warehouse_sku nullable
created_by
timestamps
product_images
id
product_id
path
sort_order
timestamps
product_price_histories
id
product_id
old_price_per_unit_usd
new_price_per_unit_usd
changed_by_user_id
timestamps

This supports listing, pricing, and history.

Cart and order domain
carts
id
user_id
timestamps
cart_items
id
cart_id
product_id
quantity
unit_price_usd
timestamps
orders
id
order_number unique
buyer_id
placed_by_agent_id nullable
status enum: pending, confirmed, processing, dispatched, delivered, cancelled, refunded
subtotal decimal(14,2)
discount_applied decimal(14,2) default 0
order_total decimal(14,2)
payment_method
payment_status enum: unpaid, partial, paid, refunded, failed
payment_reference nullable
delivery_address text
buyer_notes text nullable
ordered_at nullable
confirmed_at nullable
dispatched_at nullable
delivered_at nullable
cancelled_at nullable
timestamps
order_items
id
order_id
product_id
supplier_id nullable
product_name_snapshot
quantity
unit_price_usd
line_total_usd
timestamps
order_status_histories
id
order_id
old_status nullable
new_status
changed_by_user_id
notes nullable
timestamps

This supports the full order lifecycle required by the spec.

Payment domain
payments
id
order_id
method enum: momo, airtel, pal_bank, escrow, wire_transfer
gateway_transaction_reference nullable
gateway_reference_payload json nullable
amount decimal(14,2)
currency default USD
exchange_rate_to_ugx decimal(14,6) nullable
status enum: pending, successful, failed, refunded, partial
paid_at nullable
confirmed_by_user_id nullable
timestamps
payment_callbacks
id
provider
reference
payload json
signature_valid boolean
processed_at nullable
timestamps

This is needed because the spec requires payment gateway references, exchange-rate snapshots, and webhook-based confirmation.

Warehouse integration domain
warehouse_stock_logs
id
product_id
action enum: reserve, release, dispatch, sync
quantity
reference_type
reference_id
payload json nullable
status
timestamps

This lets you build both API-backed and manual-fallback stock management.

Notifications domain
notification_templates
id
key unique
channel enum: sms, whatsapp, email
name
subject nullable
body
is_active
timestamps
notifications
id
notifiable_type
notifiable_id
template_key
channel
recipient
subject nullable
payload json nullable
rendered_message longText nullable
status enum: queued, sent, delivered, failed, read
provider_message_id nullable
sent_at nullable
delivered_at nullable
failed_at nullable
failure_reason nullable
timestamps
notification_logs
id
notification_id
event
details json nullable
timestamps

This supports DB templates, queue-based delivery, and callback tracking.

Reporting/export domain
exports
id
requested_by_user_id
type
filters json nullable
format enum: csv, xlsx, pdf
file_path nullable
status enum: queued, processing, completed, failed
expires_at nullable
timestamps

This aligns with queued exports and downloadable links.

Activity and audit

Use Spatie Activitylog as the primary audit/event ledger.

Recommended:

keep Spatie’s activity_log table
standardize logging conventions
add a small helper service to ensure consistent metadata

Log:

farmer created/updated/verified
supplier verified
buyer verified
product created/updated/archived
order placed/status changed/cancelled
payment success/failure/refund
export requested/completed
user role changed
user suspended/reactivated

This matches the spec’s immutable audit-trail requirement, even though you will implement it through Spatie Activitylog rather than a custom audit table.

4. Module-by-module Laravel structure
app/Models
User
Profile
Region
District
Subcounty
Parish
Village
Farmer
FarmerLocation
FarmerBusinessProfile
FarmingActivityType
EquipmentType
ValueChain
Supplier
Agent
AgribusinessProfile
Buyer
ProductCategory
Product
ProductImage
ProductPriceHistory
Cart
CartItem
Order
OrderItem
OrderStatusHistory
Payment
PaymentCallback
NotificationTemplate
OutboundNotification
NotificationLog
Export
app/Livewire
Admin
Dashboard
Users/Index
Users/Form
Farmers/Index
Farmers/Show
Farmers/Form
Farmers/Map
Suppliers/Index
Suppliers/Form
Agents/Index
Agents/Form
Buyers/Index
Buyers/Form
Products/Index
Products/Form
Orders/Index
Orders/Show
Reports/M1Reports
Reports/Exports
Notifications/Index
ActivityLogs/Index
FarmerPortal
Registration/Wizard
Registration/Drafts
Profile/Show
Profile/Edit
FieldOfficer
FarmerRegistration/Wizard
FarmerRegistration/Drafts
FarmerRegistration/OfflineSync
BuyerPortal
Catalogue/Index
Product/Show
Cart/Show
Checkout/Form
Orders/Index
Orders/Show
AgentPortal
Catalogue/Index
Checkout/BuyerSelector
Orders/Index
Commission/Summary
app/Services
FarmerRegistrationService
SupplierVerificationService
BuyerVerificationService
ProductListingService
CartService
OrderService
StockService
WarehouseService
PaymentService
ExchangeRateService
CommissionService
NotificationService
SmsGatewayService
WhatsAppService
EmailTemplateService
ExportService
FarmMapService
ActivityLogService
app/Policies
FarmerPolicy
SupplierPolicy
AgentPolicy
BuyerPolicy
ProductPolicy
OrderPolicy
ExportPolicy
UserPolicy
app/Enums
UserStatus
RegistrationSource
VerificationStatus
IrrigationAvailability
InternetAccessLevel
ProductionScale
MarketDestination
SupplyFrequency
AgentOnboardingStatus
ListingStatus
OrderStatus
PaymentStatus
PaymentMethod
NotificationChannel
NotificationStatus
ExportStatus
5. Farm map requirement

You asked to include a map showing farms.

Recommended implementation

Create an Admin Farm Map page and optionally a Regional Admin Farm Map page.

Page: Admin > Farmers > Map

Show:

markers for farmers with GPS coordinates
clustering for large datasets
filters by:
region
district
value chain
verification status
click marker to show:
farmer name
supplier status
district
crops/value chains
phone
verification status
optional polygon overlay if farm_boundary_geojson exists
Recommended stack
Leaflet is simpler and cheaper if you do not need advanced Google dependencies
Google Maps if you specifically need polygon drawing and a standardized platform

Since the spec explicitly mentions Google Maps API for farm location and boundary mapping, using Google Maps keeps you closer to the original requirements.

Livewire approach
Livewire component loads filtered farm dataset
JS map component renders markers and polygons
filters update Livewire state
Livewire emits browser event with the filtered data
JS redraws the map

That keeps the server-driven structure while still giving you a rich map UI.

6. RBAC with Spatie Permission

Use:

spatie/laravel-permission
Roles
super_admin
regional_admin
field_officer
farmer
supplier
agent
buyer
Example permissions
User/admin
users.view
users.create
users.update
users.suspend
users.restore
roles.assign
Farmer registry
farmers.view
farmers.view.region
farmers.create
farmers.update
farmers.verify
farmers.export
farmers.view.map
Supplier
suppliers.view
suppliers.create
suppliers.update
suppliers.verify
Agent
agents.view
agents.create
agents.update
agents.view.commission
Buyers
buyers.view
buyers.verify
buyers.update
Products
products.view
products.create
products.update
products.archive
products.manage.stock
products.manage.price
Orders
orders.view.own
orders.view.region
orders.view.all
orders.create
orders.confirm
orders.process
orders.dispatch
orders.deliver
orders.cancel
orders.refund
Payments
payments.view
payments.confirm
payments.refund
Notifications
notifications.view
notifications.send.manual
Reports/exports
reports.view
reports.view.region
exports.create
exports.download
Activity logs
activity.view
activity.view.region

Regional admins should also be query-scoped by region_id, not only permission-scoped.

7. Activity logging with Spatie Activitylog

Use:

spatie/laravel-activitylog
Recommended standard

Every major state change should log:

causer
subject
event
relevant properties
region context if applicable
Example events
farmer.created
farmer.updated
farmer.verified
supplier.verified
buyer.verified
product.created
product.price_changed
order.placed
order.confirmed
order.dispatched
payment.completed
payment.failed
export.requested
user.suspended
role.changed
Recommendation

Create a wrapper like ActivityLogService so you don’t scatter raw activity() calls everywhere.

8. How to adapt the existing auth instead of rebuilding it

You said auth is already existing. Good.

So Codex should:

Keep
login
registration
password reset
email verification
profile settings
starter-kit dashboard layout
Modify
add new columns to users
connect roles/permissions
extend profile page to include phone, region, district, avatar, role summary
allow user-type-specific onboarding after login
add admin-only user creation and role assignment
optionally add phone-based OTP flow for farmer registration/login if needed later
Important

Do not let Codex rip out the starter kit auth and replace it with a totally custom auth module.

9. Recommended phased implementation order
Phase 0 — Foundation and starter kit extension
install starter kit
install Livewire
install Spatie Permission
install Spatie Activitylog
extend existing users schema
seed roles and permissions
protect routes with middleware
update layouts/navigation
Phase 1 — Geography and farmer registry core
region/district/subcounty/parish/village seeders
farmer tables
farmer registration wizard in Livewire
field officer farmer registration flow
photo upload
verification workflow
region scoping
Phase 2 — Farm data and map
farm business profile
value chains
equipment/activity lookups
farm coordinates and polygon support
admin farm map page
regional farm map filtering
Phase 3 — Supplier, agent, agribusiness profiles
supplier management
warehouse-linked verification
agent creation and commissions
agribusiness/cooperative profile support
Phase 4 — Buyer and marketplace foundation
buyer registration and verification
product categories
products and images
public catalogue
buyer dashboard
agent ordering flow entry points
Phase 5 — Orders, cart, checkout
cart
checkout
order creation
stock reservation
order statuses
buyer order history
agent buyer-association workflow
Phase 6 — Payments and receipts
payment providers
callback/webhook handlers
payment status updates
PDF receipts
escrow/manual PAL bank flow
Phase 7 — Notifications
DB templates
SMS, email, WhatsApp service abstractions
queued sending
delivery logs
notification stats
Phase 8 — Admin reporting and exports
KPI dashboard
M1 reports
marketplace/order reports
export jobs
activity log UI
notification stats pages
Phase 9 — Hardening
tests
queue retry checks
concurrency protection on stock
webhook signature validation
authorization audits
performance tuning
10. Codex prompt you can paste directly
You are helping me build a production-ready Laravel 12 application called AgroFresh AgriConnect.

Important stack and constraints:

- Laravel 12
- Existing Laravel Daily Blade Starter Kit is already installed
- Keep the starter kit auth and layout structure
- Use Livewire for application modules and workflows
- Use Blade layouts from the starter kit as shells
- Use Alpine.js only for small UI enhancements
- Use MySQL
- Use Redis queues
- Use S3 or MinIO for file storage
- Use Spatie Laravel Permission for roles/permissions
- Use Spatie Laravel Activitylog for audit/activity logging
- Do NOT rebuild auth from scratch
- Extend existing auth/user/profile flows only as needed
- Build a map page to display farms and optional farm boundaries
- Prefer a maintainable modular Laravel architecture with thin controllers and service classes

Project scope:
This is a Phase 1 agribusiness platform with 4 main modules:

1. M1 Farmer Registry & Core Profiling
2. M2 Marketplace
3. M8 Multi-Channel Communications
4. M9 Platform Administration & Reporting

The app must support these roles:
- super_admin
- regional_admin
- field_officer
- farmer
- supplier
- agent
- buyer

Core implementation rules:
- Use Livewire for forms, tables, dashboards, filtering, and management pages
- Use the starter kit Blade layouts for page chrome/navigation
- Use Spatie roles and permissions to enforce backend authorization
- Use Spatie activity log for major business actions
- Regional admins must only see data in their assigned region
- All business-critical flows must be logged
- All heavy tasks must be queue-friendly
- Keep external integrations behind service classes/interfaces

I want you to build incrementally and production-style.

First, do NOT generate everything blindly.
Start with architecture and schema.

## Existing auth requirement
The starter kit already has:
- login
- register
- password reset
- email verification
- dashboard
- profile settings

Keep those.
Only modify them where needed:
- extend users table with business fields
- connect roles/permissions
- update profile editing where necessary
- allow role-aware onboarding flows

Do not replace the starter-kit auth with a custom one unless I explicitly ask.

## Packages
Use:
- livewire/livewire
- spatie/laravel-permission
- spatie/laravel-activitylog
- maatwebsite/excel
- a PDF package suitable for Laravel
- optional maps package only if necessary, otherwise plain JS integration is fine

## Database blueprint to implement

Create and explain migrations, relationships, indexes, and model structure for these domains:

### Core/user domain
- users (extend existing)
- profiles (optional if cleaner than bloating users)

### Geography domain
- regions
- districts
- subcounties
- parishes
- villages

### Farmer domain
- farmers
- farmer_locations
- farmer_business_profiles
- farming_activity_types
- farmer_farming_activities
- equipment_types
- farmer_equipment
- value_chains
- farmer_value_chains

### Supplier domain
- suppliers
- supplier_value_chains
- quality_grades
- supplier_quality_grades

### Agent domain
- agents
- agent_regions
- agent_value_chains

### Agribusiness domain
- agribusiness_profiles
- agribusiness_districts

### Buyer domain
- buyers
- buyer_value_chain_interests

### Marketplace domain
- product_categories
- products
- product_images
- product_price_histories
- carts
- cart_items
- orders
- order_items
- order_status_histories

### Payment domain
- payments
- payment_callbacks

### Warehouse integration support
- warehouse_stock_logs

### Notification domain
- notification_templates
- notifications
- notification_logs

### Export domain
- exports

Use Spatie activitylog instead of building a separate custom audit table unless a small supporting table is absolutely needed.

## Functional requirements

### M1 Farmer Registry
Build:
- farmer registration wizard with Livewire
- field officer registration wizard with Livewire
- photo upload
- verification workflow
- farm location capture
- region/district/subcounty/parish/village dependent selects
- value chain and farm business capture
- supplier profiling
- agent profiling
- agribusiness/cooperative profiles

### Farm map
Create an admin farm map page and regional admin farm map page:
- show farmer markers by coordinates
- filter by region, district, value chain, verification status
- show popup details
- show polygon overlay where boundary geojson exists

Use Google Maps or Leaflet, but structure it cleanly with Livewire + browser events.

### M2 Marketplace
Build:
- buyer registration and verification
- public catalogue
- product CRUD
- supplier-linked products
- cart
- checkout
- orders
- order status management
- agent ordering on behalf of buyer
- PDF receipt generation

### M8 Communications
Build service-based support for:
- SMS
- WhatsApp
- Email

Requirements:
- templates in DB
- queue-based dispatch
- delivery logs
- callback handling

### M9 Admin
Build:
- dashboard widgets
- user management
- reports
- exports
- activity log viewer
- regional scoping

## Architecture rules
- Controllers thin
- Form Requests for validation where controller endpoints exist
- Livewire components should delegate business logic to services/actions
- Use policies and permissions for authorization
- Use enums for statuses
- Use transactions for order/payment/stock changes
- Use jobs for exports, receipts, and outbound notifications
- Use service classes and adapter classes for integrations

## What I want first
Start by generating:
1. concise architecture overview
2. full ERD in text
3. migration plan in correct build order
4. list of models
5. list of enums
6. list of services
7. list of Livewire components grouped by module
8. RBAC matrix using Spatie permissions
9. route structure
10. phased implementation plan

After that, begin generating actual code in this order:
1. package installation steps
2. roles/permissions seeder
3. users table alterations
4. geography migrations and seeders
5. farmer-related migrations and models
6. base Livewire farmer registry components
7. farm map page
8. continue module by module

When generating code:
- use real Laravel 12 file paths
- produce runnable code
- prefer maintainability over shortcuts
- do not remove the starter kit’s existing auth scaffolding
- explain assumptions briefly before each code batch
11. Recommendations

Start Codex with architecture + migrations only, not full UI and business logic at once. That will reduce bad assumptions.

Use Livewire for all CRUD/workflow screens, but do not force every tiny thing into Livewire. For maps and polygon drawing, Livewire should manage state while JavaScript handles rendering.

Keep Spatie Activitylog as your audit backbone, but define a strict event naming convention early.

Freeze your value chain list before coding products and supplier mappings, because M1 and M2 depend on the same categorization. The spec explicitly says the category structure should be agreed once and kept consistent across modules.

Implement regional scoping from day one. Do not treat it as a later filter. It affects queries, dashboards, reports, and permissions.

For the farm map, start with farmer point markers first, then add polygon boundaries after the base workflow is stable.

For payments, notifications, and warehouse integration, make Codex build service interfaces and fake/mock adapters first so the app can be developed before real external credentials are available.

Finally, ask Codex to generate the project in this order:
schema → roles/permissions → M1 core → farm map → M2 → payments → notifications → reporting.