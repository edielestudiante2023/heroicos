<?php

namespace App\Libraries;

use TCPDF;

class PazYSalvoPdfGenerator
{
    /**
     * Generate Paz y Salvo PDF certificate
     *
     * @param array $datos Keys: numero, nombre_acudiente, tipo_documento, numero_documento, nombre_estudiante, fecha_corte
     * @return string Path to generated PDF
     */
    public function generar(array $datos): string
    {
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

        $pdf->SetCreator('Academia Heroicos');
        $pdf->SetAuthor('Academia Heroicos Futbol Club');
        $pdf->SetTitle('Paz y Salvo - ' . $datos['numero']);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins(25, 20, 25);
        $pdf->AddPage();

        // Logo
        $logoPath = FCPATH . 'assets/images/heroicos.png';
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 80, 15, 35, 35, '', '', 'T', false, 300, '', false, false, 0);
        }

        $pdf->SetY(55);

        // Title
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(138, 24, 158); // heroicos-dark
        $pdf->Cell(0, 12, 'CERTIFICADO DE PAZ Y SALVO', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->Cell(0, 8, 'No. ' . $datos['numero'], 0, 1, 'C');

        $pdf->Ln(8);

        // Decorative line
        $pdf->SetDrawColor(183, 32, 210); // heroicos-primary
        $pdf->SetLineWidth(0.8);
        $pdf->Line(25, $pdf->GetY(), 190, $pdf->GetY());

        $pdf->Ln(12);

        // Body text
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(51, 51, 51);

        $fechaCorte = date('d \d\e F \d\e Y', strtotime($datos['fecha_corte']));

        $cuerpo = 'La <b>Academia Heroicos Futbol Club</b> certifica que el(la) senor(a) '
            . '<b>' . $datos['nombre_acudiente'] . '</b>, '
            . 'identificado(a) con <b>' . ($datos['tipo_documento'] ?? 'C.C.') . ' No. ' . ($datos['numero_documento'] ?? 'N/A') . '</b>, '
            . 'se encuentra a <b>PAZ Y SALVO</b> por concepto de todos los compromisos economicos '
            . 'correspondientes al estudiante <b>' . $datos['nombre_estudiante'] . '</b> '
            . 'a la fecha <b>' . $fechaCorte . '</b>.';

        $pdf->writeHTMLCell(0, 0, '', '', '<p style="line-height:1.8; text-align:justify;">' . $cuerpo . '</p>', 0, 1, false, true, 'J');

        $pdf->Ln(10);

        // Additional info
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->writeHTMLCell(0, 0, '', '', '<p style="text-align:justify;">Este documento certifica que a la fecha de emision no existen obligaciones financieras pendientes con la Academia Heroicos Futbol Club por el estudiante mencionado.</p>', 0, 1, false, true, 'J');

        $pdf->Ln(15);

        // Date
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(51, 51, 51);
        $fechaEmision = date('d \d\e F \d\e Y');
        $pdf->Cell(0, 8, 'Fecha de emision: ' . $fechaEmision, 0, 1, 'L');

        $pdf->Ln(20);

        // Signature line
        $pdf->SetDrawColor(51, 51, 51);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(25, $pdf->GetY(), 100, $pdf->GetY());
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(80, 6, 'Academia Heroicos Futbol Club', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->Cell(80, 5, 'Administracion', 0, 1, 'L');

        // Footer
        $pdf->SetY(-30);
        $pdf->SetDrawColor(183, 32, 210);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(25, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->Cell(0, 5, 'Documento generado automaticamente - Academia Heroicos Futbol Club', 0, 1, 'C');

        // Save PDF
        $uploadDir = WRITEPATH . 'uploads/paz_y_salvos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'PYS-' . $datos['numero'] . '.pdf';
        $filepath = $uploadDir . $filename;

        $pdf->Output($filepath, 'F');

        return $filepath;
    }
}
