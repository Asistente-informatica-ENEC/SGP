<?php

declare(strict_types=1);

use App\Orchid\Screens\Asset\AssetEditScreen;
use App\Orchid\Screens\Asset\AssetListScreen;
use App\Orchid\Screens\Position\PositionEditScreen;
use App\Orchid\Screens\Position\PositionListScreen;
use App\Orchid\Screens\CivilServant\CivilServantEditScreen;
use App\Orchid\Screens\CivilServant\CivilServantListScreen;
use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Example...
Route::screen('example', ExampleScreen::class)
    ->name('platform.example')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Example Screen'));

Route::screen('/examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('/examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');

Route::screen('/examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('/examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
Route::screen('/examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('/examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');

// Platform > Funcionarios
Route::screen('civil_servants', CivilServantListScreen::class)
    ->name('platform.civil_servant.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Funcionarios', route('platform.civil_servant.list')));

// Platform > Funcionarios > Editar
Route::screen('civil_servants/{civilServant}/edit', CivilServantEditScreen::class)
    ->name('platform.civil_servant.edit')
    ->breadcrumbs(fn (Trail $trail, $civilServant) => $trail
        ->parent('platform.civil_servant.list')
        ->push('Editar', route('platform.civil_servant.edit', $civilServant)));

// Platform > Funcionarios > Crear
Route::screen('civil_servants/create', CivilServantEditScreen::class)
    ->name('platform.civil_servant.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.civil_servant.list')
        ->push('Crear', route('platform.civil_servant.create')));

// Platform > Bienes
Route::screen('assets', AssetListScreen::class)
    ->name('platform.asset.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Bienes', route('platform.asset.list')));

// Platform > Bienes > Editar
Route::screen('assets/{asset}/edit', AssetEditScreen::class)
    ->name('platform.asset.edit')
    ->breadcrumbs(fn (Trail $trail, $asset) => $trail
        ->parent('platform.asset.list')
        ->push('Editar', route('platform.asset.edit', $asset)));

// Platform > Bienes > Crear
Route::screen('assets/create', AssetEditScreen::class)
    ->name('platform.asset.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.asset.list')
        ->push('Crear', route('platform.asset.create')));

// Platform > Cargos
Route::screen('positions', PositionListScreen::class)
    ->name('platform.position.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Cargos', route('platform.position.list')));

// Platform > Cargos > Editar
Route::screen('positions/{position}/edit', PositionEditScreen::class)
    ->name('platform.position.edit')
    ->breadcrumbs(fn (Trail $trail, $position) => $trail
        ->parent('platform.position.list')
        ->push('Editar', route('platform.position.edit', $position)));

// Platform > Cargos > Crear
Route::screen('positions/create', PositionEditScreen::class)
    ->name('platform.position.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.position.list')
        ->push('Crear', route('platform.position.create')));

// Route::screen('idea', Idea::class, 'platform.screens.idea');
