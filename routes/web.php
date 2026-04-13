<?php

use App\Http\Controllers\Settings;
use App\Livewire\Admin\Agents\Form as AgentForm;
use App\Livewire\Admin\Agents\Index as AgentIndex;
use App\Livewire\Admin\AgribusinessProfiles\Form as AgribusinessProfileForm;
use App\Livewire\Admin\AgribusinessProfiles\Index as AgribusinessProfileIndex;
use App\Livewire\Admin\Buyers\Form as BuyerForm;
use App\Livewire\Admin\Buyers\Index as BuyerIndex;
use App\Livewire\Admin\Buyers\Show as BuyerShow;
use App\Livewire\Admin\Farmers\Edit as FarmerEdit;
use App\Livewire\Admin\Farmers\Index as FarmerIndex;
use App\Livewire\Admin\Farmers\Map as FarmerMap;
use App\Livewire\Admin\Farmers\Show as FarmerShow;
use App\Livewire\Admin\ProductCategories\Index as ProductCategoryIndex;
use App\Livewire\Admin\Products\Form as ProductForm;
use App\Livewire\Admin\Products\Index as ProductIndex;
use App\Livewire\Admin\Products\Show as ProductShow;
use App\Livewire\Admin\Reports\FarmerOverview;
use App\Livewire\Admin\Reports\M1ProfileSummary;
use App\Livewire\Admin\Reports\ProductCatalogueSummary;
use App\Livewire\Admin\Roles\Form as RoleForm;
use App\Livewire\Admin\Roles\Index as RoleIndex;
use App\Livewire\Admin\Suppliers\Form as SupplierForm;
use App\Livewire\Admin\Suppliers\Index as SupplierIndex;
use App\Livewire\Admin\Suppliers\Show as SupplierShow;
use App\Livewire\Admin\Users\Form as UserForm;
use App\Livewire\Admin\Users\Index as UserIndex;
use App\Livewire\BuyerPortal\Profile as BuyerPortalProfile;
use App\Livewire\BuyerPortal\Registration\Create as BuyerRegistrationCreate;
use App\Livewire\FarmerPortal\Registration\Wizard as FarmerRegistrationWizard;
use App\Livewire\FieldOfficer\Farmers\Create as FieldOfficerFarmerCreate;
use App\Livewire\Public\Catalogue\Index as PublicCatalogueIndex;
use App\Livewire\Public\Catalogue\Show as PublicCatalogueShow;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('settings/appearance', [Settings\AppearanceController::class, 'edit'])->name('settings.appearance.edit');
    Route::put('settings/appearance', [Settings\AppearanceController::class, 'update'])->name('settings.appearance.update');
});

Route::middleware(['auth', 'verified', 'permission:farmers.view|farmers.view.region', 'can:viewAny,App\Models\Farmer'])->group(function () {
    Route::get('admin/farmers', FarmerIndex::class)->name('admin.farmers.index');
    Route::get('admin/farmers/map', FarmerMap::class)
        ->middleware(['permission:farmers.view.map', 'can:viewMap,App\Models\Farmer'])
        ->name('admin.farmers.map');
    Route::get('admin/farmers/{farmer}', FarmerShow::class)->name('admin.farmers.show');
    Route::get('admin/farmers/{farmer}/edit', FarmerEdit::class)
        ->middleware(['permission:farmers.update'])
        ->name('admin.farmers.edit');
});

Route::middleware(['auth', 'verified', 'permission:farmers.create'])->group(function () {
    Route::get('field-officer/farmers/create', FieldOfficerFarmerCreate::class)->name('field-officer.farmers.create');
});

Route::middleware(['auth', 'verified', 'permission:reports.view|reports.view.region'])->group(function () {
    Route::get('admin/reports/farmers/overview', FarmerOverview::class)->name('admin.reports.farmers.overview');
    Route::get('admin/reports/m1-profile-summary', M1ProfileSummary::class)->name('admin.reports.m1-profile-summary');
    Route::get('admin/reports/product-catalogue-summary', ProductCatalogueSummary::class)->name('admin.reports.product-catalogue-summary');
});

Route::middleware(['auth', 'verified', 'permission:roles.view'])->group(function () {
    Route::get('admin/roles', RoleIndex::class)->name('admin.roles.index');
});

Route::middleware(['auth', 'verified', 'permission:roles.create'])->group(function () {
    Route::get('admin/roles/create', RoleForm::class)->name('admin.roles.create');
});

Route::middleware(['auth', 'verified', 'permission:roles.update'])->group(function () {
    Route::get('admin/roles/{role}/edit', RoleForm::class)->name('admin.roles.edit');
});

Route::middleware(['auth', 'verified', 'permission:users.view'])->group(function () {
    Route::get('admin/users', UserIndex::class)->name('admin.users.index');
});

Route::middleware(['auth', 'verified', 'permission:users.create'])->group(function () {
    Route::get('admin/users/create', UserForm::class)->name('admin.users.create');
});

Route::middleware(['auth', 'verified', 'permission:users.update'])->group(function () {
    Route::get('admin/users/{user}/edit', UserForm::class)->name('admin.users.edit');
});

Route::get('farmer-portal/register', FarmerRegistrationWizard::class)->name('farmer-portal.registration.create');
Route::get('buyer-portal/register', BuyerRegistrationCreate::class)->name('buyer-portal.registration.create');
Route::get('catalogue', PublicCatalogueIndex::class)->name('catalogue.index');
Route::get('catalogue/{product}', PublicCatalogueShow::class)->name('catalogue.show');

Route::middleware(['auth', 'verified', 'can:viewAny,App\Models\Buyer', 'permission:buyers.view'])->group(function () {
    Route::get('admin/buyers', BuyerIndex::class)->name('admin.buyers.index');
    Route::get('admin/buyers/{buyer}', BuyerShow::class)->name('admin.buyers.show');
});

Route::middleware(['auth', 'verified', 'permission:buyers.create'])->group(function () {
    Route::get('admin/buyers/create', BuyerForm::class)->name('admin.buyers.create');
});

Route::middleware(['auth', 'verified', 'permission:buyers.update'])->group(function () {
    Route::get('admin/buyers/{buyer}/edit', BuyerForm::class)->name('admin.buyers.edit');
});

Route::middleware(['auth', 'verified', 'permission:suppliers.create'])->group(function () {
    Route::get('admin/suppliers/create', SupplierForm::class)->name('admin.suppliers.create');
});

Route::middleware(['auth', 'verified', 'permission:suppliers.update'])->group(function () {
    Route::get('admin/suppliers/{supplier}/edit', SupplierForm::class)->name('admin.suppliers.edit');
});

Route::middleware(['auth', 'verified', 'permission:suppliers.view', 'can:viewAny,App\Models\Supplier'])->group(function () {
    Route::get('admin/suppliers', SupplierIndex::class)->name('admin.suppliers.index');
    Route::get('admin/suppliers/{supplier}', SupplierShow::class)->name('admin.suppliers.show');
});

Route::middleware(['auth', 'verified', 'permission:agents.view', 'can:viewAny,App\Models\Agent'])->group(function () {
    Route::get('admin/agents', AgentIndex::class)->name('admin.agents.index');
});

Route::middleware(['auth', 'verified', 'permission:agents.create'])->group(function () {
    Route::get('admin/agents/create', AgentForm::class)->name('admin.agents.create');
});

Route::middleware(['auth', 'verified', 'permission:agents.update'])->group(function () {
    Route::get('admin/agents/{agent}/edit', AgentForm::class)->name('admin.agents.edit');
});

Route::middleware(['auth', 'verified', 'permission:agribusiness_profiles.view', 'can:viewAny,App\Models\AgribusinessProfile'])->group(function () {
    Route::get('admin/agribusiness-profiles', AgribusinessProfileIndex::class)->name('admin.agribusiness-profiles.index');
});

Route::middleware(['auth', 'verified', 'permission:agribusiness_profiles.create'])->group(function () {
    Route::get('admin/agribusiness-profiles/create', AgribusinessProfileForm::class)->name('admin.agribusiness-profiles.create');
});

Route::middleware(['auth', 'verified', 'permission:agribusiness_profiles.update'])->group(function () {
    Route::get('admin/agribusiness-profiles/{agribusinessProfile}/edit', AgribusinessProfileForm::class)->name('admin.agribusiness-profiles.edit');
});

Route::middleware(['auth', 'verified', 'permission:products.view', 'can:viewAny,App\Models\Product'])->group(function () {
    Route::get('admin/product-categories', ProductCategoryIndex::class)->name('admin.product-categories.index');
    Route::get('admin/products', ProductIndex::class)->name('admin.products.index');
    Route::get('admin/products/{product}', ProductShow::class)->name('admin.products.show');
});

Route::middleware(['auth', 'verified', 'permission:products.create', 'can:create,App\Models\Product'])->group(function () {
    Route::get('admin/products/create', ProductForm::class)->name('admin.products.create');
});

Route::middleware(['auth', 'verified', 'permission:products.update'])->group(function () {
    Route::get('admin/products/{product}/edit', ProductForm::class)->name('admin.products.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('buyer-portal/profile', BuyerPortalProfile::class)->name('buyer-portal.profile');
});

require __DIR__.'/auth.php';
