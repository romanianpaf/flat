<?php
/**
 * FPDF - Free PDF generation library
 *
 * This is a vendored copy of FPDF (pure PHP) to avoid adding composer deps
 * on environments where platform extensions block updates.
 *
 * Project: http://www.fpdf.org/
 * License: Freeware (per fpdf.org license)
 */

namespace App\Support\Pdf;

class Fpdf
{
    protected string $buffer = '';
    protected array $pages = [];
    protected int $page = 0;
    protected float $wPt;
    protected float $hPt;
    protected float $k = 72 / 25.4;
    protected float $w;
    protected float $h;
    protected float $x = 10;
    protected float $y = 10;
    protected float $lMargin = 10;
    protected float $tMargin = 10;
    protected float $rMargin = 10;
    protected float $bMargin = 10;
    protected float $fontSizePt = 12;
    protected float $fontSize = 12;
    protected string $fontFamily = 'Helvetica';
    protected string $fontStyle = '';
    protected int $fontUnderline = 0;
    protected array $fonts = [];
    protected string $currentFont = 'Helvetica';
    protected bool $inFooter = false;
    protected bool $compress = true;

    public function __construct(string $orientation = 'P', string $unit = 'mm', string $size = 'A4')
    {
        if ($unit === 'pt') {
            $this->k = 1;
        } elseif ($unit === 'mm') {
            $this->k = 72 / 25.4;
        } elseif ($unit === 'cm') {
            $this->k = 72 / 2.54;
        } elseif ($unit === 'in') {
            $this->k = 72;
        }

        [$w, $h] = match (strtoupper($size)) {
            'A4' => [210, 297],
            'A3' => [297, 420],
            'LETTER' => [216, 279],
            default => [210, 297],
        };

        if (strtoupper($orientation) === 'L') {
            [$w, $h] = [$h, $w];
        }

        $this->w = $w;
        $this->h = $h;
        $this->wPt = $w * $this->k;
        $this->hPt = $h * $this->k;

        // core fonts
        $this->fonts = [
            'Helvetica' => true,
            'Times' => true,
            'Courier' => true,
        ];
    }

    public function SetCompression(bool $compress): void
    {
        $this->compress = $compress;
    }

    public function AddPage(): void
    {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->SetFont($this->fontFamily, $this->fontStyle, $this->fontSizePt);
    }

    public function SetMargins(float $left, float $top, ?float $right = null): void
    {
        $this->lMargin = $left;
        $this->tMargin = $top;
        $this->rMargin = $right ?? $left;
        $this->x = $left;
        $this->y = $top;
    }

    public function SetAutoPageBreak(bool $auto, float $margin = 0): void
    {
        $this->bMargin = $margin;
    }

    public function SetFont(string $family, string $style = '', float $size = 0): void
    {
        $family = ucfirst(strtolower($family));
        if ($family === 'Arial') {
            $family = 'Helvetica';
        }

        $style = strtoupper($style);
        $this->fontUnderline = str_contains($style, 'U') ? 1 : 0;
        $style = str_replace('U', '', $style);

        if ($size <= 0) {
            $size = $this->fontSizePt;
        }

        $this->fontFamily = $family;
        $this->fontStyle = $style;
        $this->fontSizePt = $size;
        $this->fontSize = $size / $this->k;
        $this->currentFont = $family;
    }

    public function Ln(float $h = null): void
    {
        $this->x = $this->lMargin;
        $this->y += $h ?? ($this->fontSize * 1.2);
    }

    public function Cell(float $w, float $h, string $txt = '', int $border = 0, int $ln = 0, string $align = 'L'): void
    {
        $txt = $this->escape($txt);
        $x = $this->x * $this->k;
        $y = ($this->h - $this->y) * $this->k;

        $s = '';
        if ($border) {
            $s .= sprintf('%.2F %.2F %.2F %.2F re S ', $x, $y - $h * $this->k, $w * $this->k, $h * $this->k);
        }

        if ($txt !== '') {
            $dx = 2;
            if ($align === 'C') {
                $dx = ($w - $this->GetStringWidth($txt)) / 2;
            } elseif ($align === 'R') {
                $dx = $w - $this->GetStringWidth($txt) - 2;
            }
            $tx = ($this->x + $dx) * $this->k;
            $ty = ($this->h - ($this->y + 0.75 * $h)) * $this->k;
            $s .= sprintf('BT /F1 %.2F Tf %.2F %.2F Td (%s) Tj ET ', $this->fontSizePt, $tx, $ty, $txt);
        }

        $this->pages[$this->page] .= $s;
        $this->x += $w;
        if ($ln > 0) {
            $this->x = $this->lMargin;
            $this->y += $h;
        }
    }

    public function MultiCell(float $w, float $h, string $txt, int $border = 0): void
    {
        $lines = preg_split("/\r\n|\r|\n/", $txt) ?: [];
        foreach ($lines as $line) {
            $this->Cell($w, $h, $line, $border, 1);
        }
    }

    public function GetStringWidth(string $s): float
    {
        // approximation for core fonts
        return (strlen($s) * $this->fontSize * 0.5);
    }

    public function Output(string $dest = 'S', string $name = 'document.pdf'): string
    {
        $this->buffer = "%PDF-1.3\n";

        $offsets = [];
        $n = 0;

        // 1) catalog
        $offsets[++$n] = strlen($this->buffer);
        $this->buffer .= "$n 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        // 2) pages
        $kids = [];
        $pageObjs = [];
        $contentObjs = [];

        foreach ($this->pages as $p => $_) {
            $pageObjs[$p] = $n + 1 + ($p - 1) * 2;
            $contentObjs[$p] = $pageObjs[$p] + 1;
            $kids[] = $pageObjs[$p] . " 0 R";
        }

        $offsets[++$n] = strlen($this->buffer);
        $this->buffer .= "$n 0 obj\n<< /Type /Pages /Count " . count($this->pages) . " /Kids [" . implode(' ', $kids) . "] >>\nendobj\n";

        // Font object (F1)
        $fontObj = $n + 1 + count($this->pages) * 2;

        // page + contents
        foreach ($this->pages as $p => $content) {
            // page object
            $offsets[++$n] = strlen($this->buffer);
            $this->buffer .= "$n 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$this->wPt} {$this->hPt}]";
            $this->buffer .= " /Resources << /Font << /F1 {$fontObj} 0 R >> >>";
            $this->buffer .= " /Contents " . ($n + 1) . " 0 R >>\nendobj\n";

            // content stream
            $stream = $content;
            $data = $this->compress ? gzcompress($stream) : $stream;
            $filter = $this->compress ? "/Filter /FlateDecode " : "";
            $offsets[++$n] = strlen($this->buffer);
            $this->buffer .= "$n 0 obj\n<< /Length " . strlen($data) . " $filter>>\nstream\n" . $data . "\nendstream\nendobj\n";
        }

        // font object
        $offsets[++$n] = strlen($this->buffer);
        $baseFont = match ($this->currentFont) {
            'Times' => 'Times-Roman',
            'Courier' => 'Courier',
            default => 'Helvetica',
        };
        $this->buffer .= "$n 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /{$baseFont} >>\nendobj\n";

        // xref
        $xref = strlen($this->buffer);
        $this->buffer .= "xref\n0 " . ($n + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= $n; $i++) {
            $this->buffer .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        // trailer
        $this->buffer .= "trailer\n<< /Size " . ($n + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        if ($dest === 'S') {
            return $this->buffer;
        }

        // fallback: output string (controller va seta headers)
        return $this->buffer;
    }

    protected function escape(string $s): string
    {
        // Convertim UTF-8 -> Windows-1252 ca să nu spargem PDF-ul core font
        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT', $s);
        if (is_string($converted)) {
            $s = $converted;
        }
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }
}

