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
     * Determines whether to display the export button.
     *
     * @var bool
     */
    protected $export = true;
}
