First 20 files Codex should generate
Batch 1 — foundation and access control
1. database/seeders/RolePermissionSeeder.php

Purpose:

create all roles
create all permissions
assign permissions to roles using Spatie Permission

Must include:

super_admin
regional_admin
field_officer
farmer
supplier
agent
buyer

Why first:
Everything else depends on permission boundaries, and the spec explicitly requires RBAC at backend level.

2. database/migrations/xxxx_xx_xx_xxxxxx_add_business_fields_to_users_table.php

Purpose:

extend existing starter-kit auth user table
do not replace auth
add only missing business fields

Suggested columns:

phone
status
region_id
district_id
last_login_at
created_by

Why second:
You already have auth, so Codex should extend it, not rebuild it.

3. app/Models/User.php

Purpose:

update existing model
add new fillable/casts/relations
add Spatie HasRoles
add activitylog trait if needed
add region/district relationships

Must include:

relation to region
relation to district
helper methods for role checks if useful
4. app/Enums/UserStatus.php

Purpose:

centralize user statuses

Values:

active
pending
suspended

Why early:
Avoid scattered string statuses.

5. config/permission.php review/update note

This may not always be a generated file if package config already exists, but Codex should inspect and adjust if needed.

Purpose:

ensure Spatie Permission is wired correctly
ensure team support is off unless you explicitly want it
confirm cache settings
Batch 2 — geography foundation
6. database/migrations/xxxx_xx_xx_xxxxxx_create_regions_table.php
7. database/migrations/xxxx_xx_xx_xxxxxx_create_districts_table.php
8. database/migrations/xxxx_xx_xx_xxxxxx_create_subcounties_table.php
9. database/migrations/xxxx_xx_xx_xxxxxx_create_parishes_table.php
10. database/migrations/xxxx_xx_xx_xxxxxx_create_villages_table.php

Purpose of batch:

establish the full Uganda location hierarchy required by the spec
support dependent dropdowns
support region-scoped admin access
support reporting and farm mapping

Important instruction for Codex:

use foreign keys
add indexes on all parent IDs
make names searchable
do not store region/district as free text in core registry records if master data exists
Batch 3 — geography models and seeders
11. app/Models/Region.php
12. app/Models/District.php
13. app/Models/Subcounty.php
14. database/seeders/UgandaLocationSeeder.php
15. database/seeders/DatabaseSeeder.php

Purpose:

call RolePermissionSeeder
call UgandaLocationSeeder

Why this batch matters:
The spec says the location structure must be pre-seeded before M1 development begins.

Note:
You do not need all 5 geography models immediately if Codex is generating incrementally, but at least Region, District, and Subcounty should be created now. Parish and Village models can come immediately after if you want a “first 25 files” version.

Batch 4 — farmer registry core schema
16. database/migrations/xxxx_xx_xx_xxxxxx_create_farmers_table.php

Purpose:
Main farmer registry record.

Suggested columns:

user_id
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
languages_spoken
registration_source
registered_by_user_id
verification_status
verified_at
verified_by_user_id
17. database/migrations/xxxx_xx_xx_xxxxxx_create_farmer_locations_table.php

Purpose:
Store location and farm mapping data.

Suggested columns:

farmer_id
region_id
district_id
subcounty_id
parish_id
village
latitude
longitude
farm_boundary_geojson
nearest_trading_centre
distance_to_tarmac_road_km
internet_access_level

This directly supports the spec’s GPS and boundary mapping requirements.

18. app/Models/Farmer.php

Purpose:

central farmer record model
user relation
registeredBy relation
verifiedBy relation
location relation

Must include:

casts for languages_spoken
enum casts if you choose enums now
19. app/Models/FarmerLocation.php

Purpose:

hold GPS/boundary data
relation to geography tables
helper accessor for map coordinates if needed
20. app/Livewire/Admin/Farmers/Index.php

Purpose:
First real Livewire business screen.

This should:

list farmers
support filters
respect permissions
apply regional scoping
be the first proof that the stack is working end to end

Suggested filters:

verification status
region
district
registration source
search by name/phone

Why this as file 20:
It gives you a visible result early without jumping too fast into the full wizard.

What should come immediately after these 20

Once these are stable, the next 10 files should be:

app/Livewire/Admin/Farmers/Map.php
resources/views/livewire/admin/farmers/map.blade.php
resources/views/livewire/admin/farmers/index.blade.php
app/Livewire/FarmerPortal/Registration/Wizard.php
resources/views/livewire/farmer-portal/registration/wizard.blade.php
app/Services/FarmerRegistrationService.php
app/Policies/FarmerPolicy.php
app/Enums/RegistrationSource.php
app/Enums/VerificationStatus.php
routes/web.php updates for admin + farmer module routes

That gives you:

first admin farmer table
first farm map
first registration wizard
first farmer service
first farmer policy
Exact prompt to give Codex for the first 20 files
Generate the first 20 files for my Laravel 12 AgroFresh AgriConnect project using my existing Laravel Daily Blade starter kit.

Rules:
- Keep existing auth; do not rebuild it
- Use Livewire for application modules
- Use Spatie Permission for RBAC
- Use Spatie Activitylog for audit/activity logging
- Extend existing User model/table only as needed
- Use MySQL
- Use proper foreign keys, indexes, casts, and Laravel 12 conventions
- Keep controllers thin
- Prefer enums over raw status strings where appropriate
- Do not generate marketplace, payments, or notifications yet
- Focus only on foundation, geography, and farmer registry core

Generate these files in this exact order:

1. database/seeders/RolePermissionSeeder.php
2. database/migrations/...add_business_fields_to_users_table.php
3. app/Models/User.php updates
4. app/Enums/UserStatus.php
5. any required Spatie permission config adjustment note
6. database/migrations/...create_regions_table.php
7. database/migrations/...create_districts_table.php
8. database/migrations/...create_subcounties_table.php
9. database/migrations/...create_parishes_table.php
10. database/migrations/...create_villages_table.php
11. app/Models/Region.php
12. app/Models/District.php
13. app/Models/Subcounty.php
14. database/seeders/UgandaLocationSeeder.php
15. database/seeders/DatabaseSeeder.php updates
16. database/migrations/...create_farmers_table.php
17. database/migrations/...create_farmer_locations_table.php
18. app/Models/Farmer.php
19. app/Models/FarmerLocation.php
20. app/Livewire/Admin/Farmers/Index.php

For each file:
- show the full path
- provide complete runnable code
- keep code consistent with previous files
- explain assumptions briefly before each file or logical group
- update imports and namespaces correctly
- make User compatible with Spatie roles
- make farmer listing region-aware for regional_admin users

After generating the 20th file, stop and ask for the next batch.
What you should review after Codex generates them

Check these things before moving on:

1. User/auth drift

Make sure Codex did not:

replace starter-kit auth
create duplicate auth tables
introduce a second user system
2. Role design

Make sure:

permissions are granular
regional_admin is not treated like super_admin
farmer/buyer/agent roles are not overprivileged
3. Geography normalization

Make sure Codex did not flatten all locations into strings.

4. Farmer registry integrity

Make sure:

farmer and farmer location are separate tables
GPS fields are decimal and indexed if useful
registration/verification metadata exists
5. Livewire structure

Make sure the first Livewire component is:

properly namespaced
does not contain heavy business logic
uses query scopes or services cleanly