<?php

namespace App\Orchid\Screens\CivilServant;

use App\Models\CivilServant;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;

use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Color;

use App\Imports\CivilServantImport;
use App\Exports\CivilServantTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class CivilServantListScreen extends Screen
{
    /**
     * @var string
     */
    public $search;

    public function name(): ?string
    {
        return 'Funcionarios';
    }

    public function description(): ?string
    {
        return 'Listado de todos los funcionarios de la institución.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Exportar PDF')
                ->icon('bs.file-pdf')
                ->method('exportPdf')
                ->rawClick(),

            Button::make('Descargar Plantilla')
                ->method('downloadTemplate')
                ->icon('bs.download')
                ->rawClick(),

            \Orchid\Screen\Actions\ModalToggle::make('Importar Excel')
                ->modal('importModal')
                ->method('import')
                ->icon('bs.upload'),

            Link::make('Crear Nuevo')
                ->icon('bs.plus-circle')
                ->route('platform.civil_servant.create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::rows([
                Group::make([
                    Input::make('search')
                        ->type('text')
                        ->value(request('search'))
                        ->title('Buscador de Funcionarios')
                        ->placeholder('Buscar por nombre, NIT o unidad...')
                        ->help('Ingrese un término y pulse Filtrar.'),
                    
                    \Orchid\Screen\Actions\Button::make('Filtrar')
                        ->icon('bs.search')
                        ->method('handleFilter')
                        ->type(Color::PRIMARY),

                    \Orchid\Screen\Actions\Button::make('Limpiar')
                        ->icon('bs.x-circle')
                        ->method('clearFilter')
                        ->type(Color::SECONDARY),
                ])->alignEnd(),
            ]),

            Layout::modal('importModal', Layout::rows([
                Input::make('importFile')
                    ->type('file')
                    ->required()
                    ->title('Archivo Excel')
                    ->help('Seleccione el archivo .xlsx o .csv con el listado de funcionarios.'),
            ]))
                ->title('Importar Funcionarios desde Excel')
                ->applyButton('Empezar Importación'),

            \App\Orchid\Layouts\CivilServant\CivilServantListLayout::class
        ];
    }

    public function query(): iterable
    {
        $search = request('search');

        return [
            'civilServants' => \App\Models\CivilServant::with('position')
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%$search%")
                          ->orWhere('nit', 'like', "%$search%")
                          ->orWhere('unit', 'like', "%$search%");
                })
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate()
        ];
    }

    public function handleFilter(Request $request)
    {
        return redirect()->route('platform.civil_servant.list', array_filter($request->only(['search'])));
    }

    public function clearFilter()
    {
        return redirect()->route('platform.civil_servant.list');
    }

    public function downloadTemplate()
    {
        return Excel::download(new CivilServantTemplateExport, 'plantilla_funcionarios.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new CivilServantImport();
        Excel::import($import, $request->file('importFile'));

        Alert::info(sprintf(
            'Importación finalizada. Nuevos registros: %d. Registros actualizados: %d.',
            $import->imported,
            $import->updated
        ));

        return redirect()->route('platform.civil_servant.list');
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['search']);
        
        $civilServants = CivilServant::with('position')
                ->when($filters['search'] ?? null, function ($query, $search) {
                    $query->where('name', 'like', "%$search%")
                          ->orWhere('nit', 'like', "%$search%")
                          ->orWhere('unit', 'like', "%$search%");
                })
                ->filters()
                ->defaultSort('id', 'desc')
                ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.civil_servant_report', compact('civilServants', 'filters'));
        
        return $pdf->download('reporte_funcionarios.pdf');
    }
}
