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
    private string $workDir;

    /** @var array<string,string> sheetName => worksheet xml relative path (e.g. xl/worksheets/sheet1.xml) */
    private array $sheetMap = [];

    public function __construct(string $templatePath) {
        $this->templatePath = $templatePath;
        $this->workDir = sys_get_temp_dir() . '/riba_xlsx_' . bin2hex(random_bytes(8));
    }

    public function __destruct() {
        $this->rrmdir($this->workDir);
    }

    public function open(): void {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('PHP ZipArchive extension is required (php-zip).');
        }
        if (!file_exists($this->templatePath)) {
            throw new RuntimeException('XLSX template not found.');
        }
        if (!is_dir($this->workDir) && !mkdir($this->workDir, 0700, true)) {
            throw new RuntimeException('Unable to create temp directory.');
        }

        $zip = new ZipArchive();
        if ($zip->open($this->templatePath) !== true) {
            throw new RuntimeException('Unable to open XLSX template.');
        }
        if (!$zip->extractTo($this->workDir)) {
            $zip->close();
            throw new RuntimeException('Unable to extract XLSX template.');
        }
        $zip->close();

        $this->sheetMap = $this->buildSheetMap();
    }

    public function hasSheet(string $sheetName): bool {
        return isset($this->sheetMap[$sheetName]);
    }

    public function setNumber(string $sheetName, string $cellRef, $value): void {
        if (!$this->hasSheet($sheetName)) {
            throw new InvalidArgumentException('Sheet not found: ' . $sheetName);
        }
        $relPath = $this->sheetMap[$sheetName];
        $xmlPath = $this->workDir . '/' . $relPath;
        if (!file_exists($xmlPath)) {
            throw new RuntimeException('Worksheet xml not found for sheet: ' . $sheetName);
        }

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;
        $doc->load($xmlPath);

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
            $sheetData->appendChild($row);
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

        $doc->save($xmlPath);
    }

    public function saveTo(string $outputPath): void {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('PHP ZipArchive extension is required (php-zip).');
        }
        $zip = new ZipArchive();
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create XLSX output.');
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->workDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            /** @var SplFileInfo $file */
            $filePath = $file->getPathname();
            $rel = substr($filePath, strlen($this->workDir) + 1);
            if ($file->isDir()) {
                $zip->addEmptyDir($rel);
            } else {
                $zip->addFile($filePath, $rel);
            }
        }

        $zip->close();
    }

    private function buildSheetMap(): array {
        $workbookPath = $this->workDir . '/xl/workbook.xml';
        $relsPath = $this->workDir . '/xl/_rels/workbook.xml.rels';
        if (!file_exists($workbookPath) || !file_exists($relsPath)) {
            throw new RuntimeException('Invalid XLSX structure.');
        }

        $wb = new DOMDocument();
        $wb->load($workbookPath);
        $wbNs = $wb->documentElement->namespaceURI;
        $wbXpath = new DOMXPath($wb);
        $wbXpath->registerNamespace('x', $wbNs);

        $rels = new DOMDocument();
        $rels->load($relsPath);
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

    private function rrmdir(string $dir): void {
        if (!is_dir($dir)) return;
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            /** @var SplFileInfo $file */
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }
        @rmdir($dir);
    }
}

