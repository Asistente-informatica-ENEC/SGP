<?php

namespace App\Orchid\Layouts\Asset;

use Orchid\Screen\Layouts\Selection;
use Orchid\Screen\Fields\Input;

class AssetSelection extends Selection
{
    /**
     * @return iterable
     */
    public function filters(): iterable
    {
        return [
            // Aquí podríamos añadir filtros de clase personalizados si fuera necesario,
            // pero para un buscador de texto directo podemos usar el query.
        ];
    }
}
