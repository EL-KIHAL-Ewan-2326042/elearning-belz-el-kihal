<?php

namespace App\Service;

use Smalot\PdfParser\Parser;

class PdfParserService
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    public function parsePdf(string $filePath): string
    {
        try {
            $pdf = $this->parser->parseFile($filePath);
            return $pdf->getText();
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de l'extraction du texte PDF: " . $e->getMessage());
        }
    }
}
