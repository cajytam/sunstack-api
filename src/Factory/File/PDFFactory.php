<?php

namespace App\Factory\File;

use Mpdf\Output\Destination;

class PDFFactory
{
    private \Mpdf\Mpdf $pdf;
    private string $nbPages;
    private array $generatedPages = [];
    private string $currentFontFamily;
    private int $currentFontSize;
    private string $currentFontWeight;
    private string $currentFontColor;
    private bool $currentIsFormatNumber;
    private bool $currentIsFormatCurrency;
    private int $_colorR;
    private int $_colorG;
    private int $_colorB;
    private int $_currentPage = 0;

    public function __construct(private $pdf_source = null, private $globalFontConfig = [], private $pagesToNotGenerate = [])
    {
        $fontDirs = (new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'];
        $fontData = (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'];

        $this->pdf = new \Mpdf\Mpdf([
            'tempDir' => dirname(__DIR__) . '/../../var/cache',
            'mode' => 'utf-8',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'fontDir' => array_merge($fontDirs, [dirname(__DIR__) . '/../../assets/fonts/']),
            'fontdata' => $fontData +
                [
                    'poppins' => [
                        'R' => 'Poppins-Regular.ttf',
                        'B' => 'Poppins-Bold.ttf',
                        'I' => 'Poppins-Italic.ttf',
                        'useOTL' => 0xFF,
                    ],
                ],
        ]);

        $this->pdf->showImageErrors = true;

        $this->nbPages = $this->pdf_source ? $this->pdf->SetSourceFile($this->pdf_source) : 0;

        $this->_initializeFontFormat();

        $this->pdf->SetFont(
            $this->currentFontFamily,
            $this->currentFontWeight,
            $this->currentFontSize,
        );

        $this->setFontColor($this->currentFontColor);
    }

    public function page(int $page)
    {
        if ($this->nbPages < $page) {
            throw new \ErrorException("Page incohérente : page {$page} invoquée alors que le PDF comporte {$this->nbPages} page" . ($page > 1 ? 's' : ''));
        }

        if ($this->_currentPage > $page) {
            throw new \ErrorException("Ordre de page incorrecte : la page {$page} a déjà été traitée. Page actuelle : {$this->_currentPage}");
        }

        if (!in_array($page, $this->generatedPages)) {
            $this->_generatePages($page);
        }
        $this->_currentPage = $page;
        return $this;
    }

    public function addText(
        string|null $texte = '',
        float       $posX = null,
        float       $posY = null,
        array       $params = [],
        bool|null   $formatNumber = null,
        bool|null   $formatCurrency = null,
        bool|null   $formatNumberWithoutDecimal = null
    ): self
    {
        if (null === $texte) {
            $texte = '';
        }
        if (!empty($params)) {
            $this->paramsHandler($params);
            if (is_bool($formatNumber)) {
                $this->currentIsFormatNumber = $formatNumber;
            }
            if (is_bool($formatCurrency)) {
                $this->currentIsFormatCurrency = $formatCurrency;
            }
        }

        $this->pdf->SetFont(
            $this->currentFontFamily,
            $this->currentFontWeight,
            $this->currentFontSize
        );
        $this->setFontColor($this->currentFontColor);

        if ($formatNumber) {
            if (is_numeric($texte)) {
                $texte = $this->_format_number($texte);
            }
        }
        if ($formatCurrency) {
            if (is_numeric($texte)) {
                $texte = $this->_format_number($texte) . '€';
            }
        }
        if ($formatNumberWithoutDecimal) {
            if (is_numeric($texte)) {
                $texte = $this->_format_number($texte, 0);
            }
        }
        if (null === $posX) {
            $posX = $this->pdf->x;
        }
        if (null === $posY) {
            $posY = $this->pdf->y;
        }

        // TODO: Ajouter un auto-alignement si le nombre de caractères change
        $this->pdf->WriteText($posX, $posY, $texte);
        $this->_initializeFontFormat();
        $this->pdf->SetXY($posX, $posY);

        return $this;
    }

    public function addImageBase64(
        string $contentBase64,
        float  $width,
        float  $height,
        float  $posX,
        float  $posY,
    )
    {
        $this->pdf->WriteFixedPosHTML(
            "<img style='position: fixed' src='data:image/png;base64," . $contentBase64 . "' alt='image base64' />",
            $posX,
            $posY,
            $width,
            $height,
        );
    }

    public function addVerticalText(
        string|null $text,
        float       $width,
        float       $height,
        float       $posX,
        float       $posY,
        array       $params = [],
    ): self
    {
        if (null === $text) {
            $texte = '';
        }
        if (!empty($params)) {
            $this->paramsHandler($params);
        }

        $this->pdf->SetFont(
            $this->currentFontFamily,
            $this->currentFontWeight,
            $this->currentFontSize
        );
        $this->setFontColor($this->currentFontColor);

        $this->pdf->WriteFixedPosHTML(
            '<div style="position: absolute; width: '.$width.'mm ;height: '.$height.'mm ; top: ' . $posY . 'mm ;left: ' . $posX . 'mm ;rotate: -90; font-family: ' . $this->currentFontFamily . '; font-size: '.$this->currentFontSize.';font-weight: '. $this->currentFontWeight.'; color: ' . $this->currentFontColor . '; ">' . $text . '</div>',
            $posX,
            $posY,
            $width,
            $height,
        );

        return $this;
    }

    public function currentX(): float
    {
        return $this->pdf->x;
    }

    public function currentY(): float
    {
        return $this->pdf->y;
    }

    public function setFontColor(string $htmlColor): void
    {
        list($this->_colorR, $this->_colorG, $this->_colorB) = sscanf($htmlColor, "#%02x%02x%02x");
        $this->pdf->SetTextColor($this->_colorR, $this->_colorG, $this->_colorB);
    }

    public function setFont(
        string $fontFamily = '',
        string $fontWeight = '',
        int    $fontSize = 0,
        string $fontColor = ''
    ): void
    {
        if (empty($fontFamily)) {
            $fontFamily = $this->globalFontConfig['font-family'];
        }
        if (empty($fontWeight)) {
            $fontWeight = $this->globalFontConfig['font-weight'];
        }
        if (empty($fontSize) || $fontSize <= 0) {
            $fontSize = $this->globalFontConfig['font-size'];
        }
        if (empty($fontColor)) {
            $fontColor = $this->globalFontConfig['color'];
        }
        $this->pdf->SetFont($fontFamily, $fontWeight, $fontSize);
        $this->setFontColor($fontColor);
    }

    public function drawRectagle(float $x, float $y, float $w, float $h, array $fillColor = [255, 255, 255]): static
    {
        list($r, $g, $b) = $fillColor;
        $this->pdf->SetFillColor($r, $g, $b);
        $this->pdf->RoundedRect(
            $x,
            $y,
            $w,
            $h,
            0,
            'F'
        );

        return $this;
    }

    public function generatePDF(string $nomPDF = '')
    {
        $this->_generateRestOfPages();
        if (ob_get_contents()) ob_end_clean();
        $this->pdf->OutputHttpDownload($nomPDF);
    }

    public function displayPDF(string $nomPDF = '')
    {
        $this->_generateRestOfPages();
        if (ob_get_contents()) ob_end_clean();
        $this->pdf->Output($nomPDF, Destination::INLINE);
    }

    public function savePDFOnServer(string $path, string $nomPDF): void
    {
        $this->_generateRestOfPages();
        if (ob_get_contents()) ob_end_clean();
        $this->pdf->Output($path . $nomPDF, Destination::FILE);
    }

    public function createCell(float $x, float $y, float $width, float $height, string $texte, string $align = 'C'): static
    {
        $this->pdf->SetX($x);
        $this->pdf->SetY($y);
        $this->pdf->WriteCell($width, $height, $texte, 1, 0, $align);

        return $this;
    }

    public function mergePDF(array $files): void
    {
        foreach ($files as $file) {
            $pageCount = $this->pdf->setSourceFile($file);
            for ($i = 0; $i < $pageCount; $i++) {
                $tpl = $this->pdf->importPage($i + 1);
                $this->pdf->addPage();
                $this->pdf->useTemplate($tpl);
            }
        }
    }

    private function _generatePages(int $page)
    {
        $this->pdf->AddPage();
        $this->pdf->useTemplate($this->pdf->ImportPage($page));
        array_push($this->generatedPages, $page);
    }

    private function _format_number(float|string $number, int $nb_decimals = 2): string
    {
        return strval(number_format($number, $nb_decimals, ',', ' '));
    }

    public function paramsHandler(array $params): void
    {
        if (isset($params['font-family'])) {
            $this->currentFontFamily = $params['font-family'];
        }
        if (isset($params['font-size'])) {
            $this->currentFontSize = $params['font-size'];
        }
        if (isset($params['font-weight'])) {
            $this->currentFontWeight = $params['font-weight'];
        }
        if (isset($params['color'])) {
            $this->currentFontColor = $params['color'];
        }
    }

    private function _convertColorDecimalHTML(string $r, string $g, string $b): string
    {
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    private function _initializeFontFormat(): void
    {
        if (!isset($this->globalFontConfig['font-family'])) {
            $this->currentFontFamily = 'Arial';
        } else {
            $this->currentFontFamily = $this->globalFontConfig['font-family'];
        }

        if (!isset($this->globalFontConfig['font-size'])) {
            $this->currentFontSize = 12;
        } else {
            $this->currentFontSize = $this->globalFontConfig['font-size'];
        }

        if (!isset($this->globalFontConfig['font-weight'])) {
            $this->currentFontWeight = 'R';
        } else {
            $this->currentFontWeight = $this->globalFontConfig['font-weight'];
        }

        if (!isset($this->globalFontConfig['color'])) {
            $this->currentFontColor = '#000000';
        } else {
            $this->currentFontColor = $this->globalFontConfig['color'];
        }

        if (!isset($this->globalFontConfig['is-format-number'])) {
            $this->currentIsFormatNumber = false;
        } else {
            $this->currentIsFormatNumber = $this->globalFontConfig['is-format-number'];
        }
    }

    private function _generateRestOfPages(): void
    {
        for ($i = 1; $i <= $this->nbPages; $i++) {
            if (!in_array($i, $this->pagesToNotGenerate) && !in_array($i, $this->generatedPages)) {
                $this->page($i);
            }
        }
    }

    public function getNbPages(): int
    {
        return $this->nbPages;
    }
}
