<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);
    }

    public function menu(): array
    {
        return [
            // ── Módulos operativos (visibles para admin Y encargado) ──────────
            Menu::make('Funcionarios')
                ->icon('bs.people')
                ->route('platform.civil_servant.list')
                ->permission('sgp.civil_servants.view')
                ->title('Menú'),

            Menu::make('Bienes')
                ->icon('bs.box-seam')
                ->route('platform.asset.list')
                ->permission('sgp.assets.view'),

            Menu::make('Cargos')
                ->icon('bs.briefcase')
                ->route('platform.position.list')
                ->permission('sgp.positions.view'),

            Menu::make('Tarjetas de Responsabilidad')
                ->icon('bs.card-checklist')
                ->route('platform.responsability_card.list')
                ->permission('sgp.responsability_cards.view'),

            Menu::make('Reportes')
                ->icon('bs.bar-chart')
                ->route('platform.report.list')
                ->permission('sgp.reports.view'),

            Menu::make('Bienes en Mal Estado')
                ->icon('bs.trash')
                ->route('platform.bad_condition_card.list')
                ->permission('sgp.bad_condition.view'),

            // ── Sección solo encargado: su propio perfil ──────────────────────
            Menu::make('Mi Perfil')
                ->icon('bs.person-circle')
                ->route('platform.profile')
                ->permission('sgp.profile.view')
                ->title('Mi Cuenta'),

            // ── Sección solo administrador ────────────────────────────────────
            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Control de perfiles')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),
        ];
    }

    public function permissions(): array
    {
        return [
            ItemPermission::group('SGP')
                ->addPermission('sgp.civil_servants.view', 'Funcionarios')
                ->addPermission('sgp.assets.view', 'Bienes')
                ->addPermission('sgp.positions.view', 'Cargos')
                ->addPermission('sgp.responsability_cards.view', 'Tarjetas de Responsabilidad')
                ->addPermission('sgp.reports.view', 'Reportes')
                ->addPermission('sgp.bad_condition.view', 'Bienes en Mal Estado')
                ->addPermission('sgp.profile.view', 'Mi Perfil'),

            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
