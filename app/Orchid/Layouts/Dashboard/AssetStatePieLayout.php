<?php

namespace App\Orchid\Layouts\Dashboard;

use Orchid\Screen\Layouts\Chart;

class AssetStatePieLayout extends Chart
{
    /**
     * Add a title to the Chart.
     *
     * @var string
     */
    protected $title = 'Estado General de los Bienes';

    /**
     * Available options:
     * 'bar', 'line',
     * 'pie', 'percentage'.
     *
     * @var string
     */
    protected $type = 'pie';

    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'assetStateChart';

    /**
     * Colors: Azul (Asignados), Verde (Disponibles), Amarillo (En Mal Estado), Gris (Otros).
     *
     * @var array
     */
    protected $colors = ['#0d6efd', '#198754', '#ffc107', '#6c757d'];

    /**
     * Determines whether to display the export button.
     *
     * @var bool
     */
    protected $export = true;
}
