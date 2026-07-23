<?php

namespace App\Orchid\Layouts\Dashboard;

use Orchid\Screen\Layouts\Chart;

class CardsChartLayout extends Chart
{
    /**
     * Add a title to the Chart.
     *
     * @var string
     */
    protected $title = 'Tarjetas Emitidas (Últimos 7 días)';

    /**
     * Available options:
     * 'bar', 'line',
     * 'pie', 'percentage'.
     *
     * @var string
     */
    protected $type = 'line';

    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'cardsChart';

    /**
     * Colors: Azul para tarjetas normales, Amarillo para tarjetas de mal estado.
     *
     * @var array
     */
    protected $colors = ['#0d6efd', '#ffc107'];

    /**
     * Determines whether to display the export button.
     *
     * @var bool
     */
    protected $export = true;
}
