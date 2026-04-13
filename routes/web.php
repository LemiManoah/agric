<?php

use App\Http\Controllers\Settings;
use App\Livewire\Admin\Agents\Form as AgentForm;
use App\Livewire\Admin\Agents\Index as AgentIndex;
use App\Livewire\Admin\AgribusinessProfiles\Form as AgribusinessProfileForm;
use App\Livewire\Admin\AgribusinessProfiles\Index as AgribusinessProfileIndex;
use App\Livewire\Admin\Farmers\Edit as FarmerEdit;
use App\Livewire\Admin\Farmers\Index as FarmerIndex;
use App\Livewire\Admin\Farmers\Map as FarmerMap;
use App\Livewire\Admin\Farmers\Show as FarmerShow;
use App\Livewire\Admin\Reports\FarmerOverview;
use App\Livewire\Admin\Reports\M1ProfileSummary;
use App\Livewire\Admin\Roles\Form as RoleForm;
use App\Livewire\Admin\Roles\Index as RoleIndex;
use App\Livewire\Admin\Suppliers\Form as SupplierForm;
use App\Livewire\Admin\Suppliers\Index as SupplierIndex;
use App\Livewire\Admin\Suppliers\Show as SupplierShow;
use App\Livewire\FarmerPortal\Registration\Wizard as FarmerRegistrationWizard;
use App\Livewire\FieldOfficer\Farmers\Create as FieldOfficerFarmerCreate;
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

Route::get('farmer-portal/register', FarmerRegistrationWizard::class)->name('farmer-portal.registration.create');

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

require __DIR__.'/auth.php';
