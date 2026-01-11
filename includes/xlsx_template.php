<?php
/**
 * Minimal XLSX template filler (no external dependencies).
 *
 * Supports writing numeric values into existing/created cells of given sheets,
 * while keeping the rest of the workbook intact (styles/formulas/macros, etc.).
 *
 * This is intentionally minimal: we only need to fill the participant data entry
 * sheets (ogrenci/veli/ogretmen) so that the template's own formulas compute
 * the final result sheets exactly like the official Excel.
 */
class XlsxTemplateFiller {
    private string $templatePath;

    /** @var array<string,string> sheetName => worksheet xml relative path (e.g. xl/worksheets/sheet1.xml) */
    private array $sheetMap = [];

    private ?ZipArchive $zip = null;
    private string $tmpPath = '';
    /** @var array<string,DOMDocument> */
    private array $docCache = [];
    /** @var array<string,bool> */
    private array $dirtySheets = [];

    public function __construct(string $templatePath) {
        $this->templatePath = $templatePath;
    }

    public function __destruct() {
        if ($this->zip instanceof ZipArchive) {
            @$this->zip->close();
        }
        if ($this->tmpPath !== '' && file_exists($this->tmpPath)) {
            @unlink($this->tmpPath);
        }
    }

    public function open(): void {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('PHP ZipArchive extension is required (php-zip).');
        }
        if (!file_exists($this->templatePath)) {
            throw new RuntimeException('XLSX template not found.');
        }

        // Work on a temp copy of the template to avoid rebuilding the zip structure
        $this->tmpPath = sys_get_temp_dir() . '/riba_tpl_' . bin2hex(random_bytes(8)) . '.xlsx';
        if (!copy($this->templatePath, $this->tmpPath)) {
            throw new RuntimeException('Unable to copy XLSX template.');
        }

        $this->zip = new ZipArchive();
        if ($this->zip->open($this->tmpPath) !== true) {
            throw new RuntimeException('Unable to open XLSX temp file.');
        }

        $this->sheetMap = $this->buildSheetMapFromZip();
    }

    public function hasSheet(string $sheetName): bool {
        return isset($this->sheetMap[$sheetName]);
    }

    public function setNumber(string $sheetName, string $cellRef, $value): void {
        if (!$this->hasSheet($sheetName)) {
            throw new InvalidArgumentException('Sheet not found: ' . $sheetName);
        }
        if (!($this->zip instanceof ZipArchive)) {
            throw new RuntimeException('XLSX is not opened.');
        }

        $doc = $this->getSheetDoc($sheetName);
        $ns = $doc->documentElement->namespaceURI;
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('x', $ns);

        [$colLetters, $rowNum] = $this->splitCellRef($cellRef);

        $sheetData = $xpath->query('//x:worksheet/x:sheetData')->item(0);
        if (!$sheetData) {
            throw new RuntimeException('Invalid worksheet structure.');
        }

        // Find/create row
        $row = $xpath->query('//x:worksheet/x:sheetData/x:row[@r="' . $rowNum . '"]')->item(0);
        if (!$row) {
            $row = $doc->createElementNS($ns, 'row');
            $row->setAttribute('r', (string)$rowNum);
            // Insert rows ordered by r to keep Excel happy
            $inserted = false;
            foreach ($xpath->query('//x:worksheet/x:sheetData/x:row') as $existingRow) {
                /** @var DOMElement $existingRow */
                $er = (int)$existingRow->getAttribute('r');
                if ($er > $rowNum) {
                    $sheetData->insertBefore($row, $existingRow);
                    $inserted = true;
                    break;
                }
            }
            if (!$inserted) {
                $sheetData->appendChild($row);
            }
        }

        // Find/create cell
        $cell = $xpath->query('x:c[@r="' . $cellRef . '"]', $row)->item(0);
        if (!$cell) {
            $cell = $doc->createElementNS($ns, 'c');
            $cell->setAttribute('r', $cellRef);
            // Insert in column order for nicer diffs (optional)
            $inserted = false;
            $targetColIndex = $this->colToIndex($colLetters);
            foreach ($xpath->query('x:c', $row) as $existing) {
                /** @var DOMElement $existing */
                $r = $existing->getAttribute('r');
                if ($r) {
                    [$exCol] = $this->splitCellRef($r);
                    if ($this->colToIndex($exCol) > $targetColIndex) {
                        $row->insertBefore($cell, $existing);
                        $inserted = true;
                        break;
                    }
                }
            }
            if (!$inserted) {
                $row->appendChild($cell);
            }
        }

        // Set numeric value
        $cell->setAttribute('t', 'n');
        // Remove existing v/inlineStr nodes
        foreach (iterator_to_array($cell->childNodes) as $child) {
            if ($child instanceof DOMElement && in_array($child->localName, ['v', 'is'], true)) {
                $cell->removeChild($child);
            }
        }
        $v = $doc->createElementNS($ns, 'v', (string)$value);
        $cell->appendChild($v);
        $this->dirtySheets[$sheetName] = true;
    }

    public function saveTo(string $outputPath): void {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('PHP ZipArchive extension is required (php-zip).');
        }
        if (!($this->zip instanceof ZipArchive)) {
            throw new RuntimeException('XLSX is not opened.');
        }

        // Write dirty sheets back into the zip
        foreach (array_keys($this->dirtySheets) as $sheetName) {
            $path = $this->sheetMap[$sheetName];
            $doc = $this->getSheetDoc($sheetName);
            $xml = $doc->saveXML();
            if ($xml === false) {
                throw new RuntimeException('Failed to serialize worksheet xml.');
            }
            $this->zip->setFromString($path, $xml);
        }

        $this->zip->close();
        $this->zip = null;

        if (!copy($this->tmpPath, $outputPath)) {
            throw new RuntimeException('Unable to write XLSX output.');
        }
    }

    private function buildSheetMapFromZip(): array {
        if (!($this->zip instanceof ZipArchive)) {
            throw new RuntimeException('XLSX is not opened.');
        }
        $workbookXml = $this->zip->getFromName('xl/workbook.xml');
        $relsXml = $this->zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $relsXml === false) {
            throw new RuntimeException('Invalid XLSX structure.');
        }

        $wb = new DOMDocument();
        $wb->loadXML($workbookXml);
        $wbNs = $wb->documentElement->namespaceURI;
        $wbXpath = new DOMXPath($wb);
        $wbXpath->registerNamespace('x', $wbNs);

        $rels = new DOMDocument();
        $rels->loadXML($relsXml);
        $relsXpath = new DOMXPath($rels);
        $relsXpath->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');

        // Map rId => target
        $ridToTarget = [];
        foreach ($relsXpath->query('//r:Relationships/r:Relationship') as $rel) {
            /** @var DOMElement $rel */
            $id = $rel->getAttribute('Id');
            $target = $rel->getAttribute('Target');
            if ($id && $target) {
                // Targets are relative to xl/
                $ridToTarget[$id] = 'xl/' . ltrim($target, '/');
            }
        }

        $map = [];
        foreach ($wbXpath->query('//x:sheets/x:sheet') as $sheet) {
            /** @var DOMElement $sheet */
            $name = $sheet->getAttribute('name');
            $rid = $sheet->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
            if ($name && $rid && isset($ridToTarget[$rid])) {
                $map[$name] = $ridToTarget[$rid];
            }
        }
        return $map;
    }

    private function splitCellRef(string $cellRef): array {
        if (!preg_match('/^([A-Z]+)(\\d+)$/', strtoupper($cellRef), $m)) {
            throw new InvalidArgumentException('Invalid cell reference: ' . $cellRef);
        }
        return [$m[1], (int)$m[2]];
    }

    private function colToIndex(string $col): int {
        $col = strtoupper($col);
        $n = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $n = $n * 26 + (ord($col[$i]) - 64);
        }
        return $n;
    }

    private function getSheetDoc(string $sheetName): DOMDocument {
        if (isset($this->docCache[$sheetName])) {
            return $this->docCache[$sheetName];
        }
        if (!($this->zip instanceof ZipArchive)) {
            throw new RuntimeException('XLSX is not opened.');
        }
        $path = $this->sheetMap[$sheetName];
        $xml = $this->zip->getFromName($path);
        if ($xml === false) {
            throw new RuntimeException('Worksheet xml not found in zip: ' . $path);
        }
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;
        $doc->loadXML($xml);
        $this->docCache[$sheetName] = $doc;
        return $doc;
    }
}

