<?php

namespace App\Exports;

use App\Models\ResponsabilityCard;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponsabilityCardExport
{
    protected $card;

    public function __construct(ResponsabilityCard $card)
    {
        $this->card = $card;
    }

    public function download()
    {
        $templatePath = public_path('formatos/Formato TR nvas..xlsx');
        
        if (!file_exists($templatePath)) {
            throw new \Exception("El formato base no se encuentra en: " . $templatePath);
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Tamaño Oficio (Folio / 8.5 x 13 pulg. / 216 x 330 mm) en horizontal
        $sheet->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_FOLIO)
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(1);
 
        // Eliminar filas para que ajuste mejor (Petición de usuario)
        $sheet->removeRow(19, 1);
        $sheet->removeRow(22, 1);
        
        // Ajustar tamaño de fuente en fila 20 (+0.5 según pedido)
        $row20Style = $sheet->getStyle('A20:H20');
        $currentSize = $row20Style->getFont()->getSize() ?: 10;
        $row20Style->getFont()->setSize($currentSize + 0.5);
 

        // --- INSERTAR LOGO ---
        if (file_exists(public_path('images/logost.png'))) {
            $drawing = new Drawing();
            $drawing->setName('Logo ST');
            $drawing->setDescription('Ministerio de Salud y Escuela de Enfermería');
            $drawing->setPath(public_path('images/logost.png'));
            $drawing->setHeight(93); // Aumentado en 3px según pedido
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }

        // --- MApEO DE DATOS CORREGIDO (Según imagen institucional) ---
        
        // Encabezado
        $sheet->setCellValue('H5', 'No. ' . $this->card->assignment_code);
        $sheet->setCellValue('C7', $this->card->civilServant?->name);
        $sheet->setCellValue('G7', $this->card->civilServant?->nit);
        $sheet->setCellValue('C9', $this->card->civilServant?->unit);
        $sheet->setCellValue('G9', $this->card->civilServant?->position?->name);

        // Tabla de Bienes (Inicia en fila 12)
        $startRow = 12;
        $currentRow = $startRow;

        foreach ($this->card->assignments as $assignment) {
            $asset = $assignment->asset;
            if ($asset) {
                $sheet->setCellValue('A' . $currentRow, $assignment->date?->format('d/m/Y'));
                $sheet->setCellValue('B' . $currentRow, $asset->sicoin);
                $sheet->setCellValue('C' . $currentRow, '1'); // Cantidad
                $sheet->setCellValue('D' . $currentRow, $asset->description);
                $sheet->setCellValue('E' . $currentRow, $asset->value); // DEBE
                // F (HABER) queda vacío
                $sheet->setCellValue('G' . $currentRow, $asset->value); // SALDO
                $sheet->setCellValue('H' . $currentRow, $asset->state); // OBSERVACIÓN
                $currentRow++;
            }
        }

        // --- AJUSTES FINALES DE IMPRESIÓN ---
        // Aseguramos que el área de impresión empiece desde la columna A y termine exacto en la H
        $sheet->getPageSetup()->setPrintArea('A1:H' . ($currentRow + 10)); // Regresamos a la columna H
        
        // Quitamos el centrado horizontal para forzar el inicio a la izquierda controlado
        $sheet->getPageSetup()->setHorizontalCentered(false);

        // Márgenes ajustados: Izquierda exacto 1.1cm (0.433 pulg), Derecha mínimo.
        $sheet->getPageMargins()
            ->setLeft(0.433)
            ->setRight(0.15)
            ->setTop(0.3)
            ->setBottom(0.3);

        // --- FIN MApEO ---

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="Tarjeta_Responsabilidad_' . $this->card->assignment_code . '.xlsx"',
        ]);
    }
}
