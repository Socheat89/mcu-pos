<?php
// modules/pos/controllers/ProductController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../models/Product.php';
require_once dirname(__DIR__, 3) . '/core/helpers/url.php';

class ProductController {
    public function index() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (Tenant::getPosLevel() < 1) {
            die('Upgrade to POS Starter or higher to manage products.');
        }

        if (!Auth::hasPermission('pos', 'read')) {
            die('No permission to view products');
        }

        $products = Product::getAll();
        $categories = $this->getCategories();

        include __DIR__ . '/../views/products.php';
    }

    public function create() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'write')) {
            die('No permission to create products');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
        } else {
            $categories = $this->getCategories();
            include __DIR__ . '/../views/product_form.php';
        }
    }

    public function edit($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'write')) {
            die('No permission to edit products');
        }

        $product = Product::getById($id);
        if (!$product) {
            die('Product not found');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
        } else {
            $categories = $this->getCategories();
            include __DIR__ . '/../views/product_form.php';
        }
    }

    public function delete($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'delete')) {
            die('No permission to delete products');
        }

        Product::delete($id);
        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/products');
        exit;
    }

    public function import() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'write')) {
            die('No permission to import products');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToProducts();
        }

        try {
            $source = $_POST['import_source'] ?? 'file';
            if ($source === 'sheet') {
                $sheetUrl = trim($_POST['google_sheet_url'] ?? '');
                if ($sheetUrl === '') {
                    throw new Exception(__('product_import_sheet_required'));
                }
                $rows = $this->loadRowsFromGoogleSheet($sheetUrl);
            } elseif ($source === 'file') {
                if (!isset($_FILES['product_file'])) {
                    throw new Exception(__('product_import_file_required'));
                }
                $rows = $this->loadRowsFromUploadedFile($_FILES['product_file']);
            } else {
                throw new Exception(__('product_import_invalid_source'));
            }

            $result = $this->importProductRows($rows);
            $changed = $result['created'] + $result['updated'];

            $_SESSION['product_import_flash'] = [
                'type' => $changed > 0 ? 'success' : 'warning',
                'title' => $changed > 0 ? __('product_import_complete') : __('product_import_no_changes'),
                'message' => __('product_import_summary', [
                    'created' => $result['created'],
                    'updated' => $result['updated'],
                    'skipped' => $result['skipped']
                ]),
                'details' => $result['errors']
            ];
        } catch (Throwable $e) {
            $_SESSION['product_import_flash'] = [
                'type' => 'danger',
                'title' => __('product_import_failed'),
                'message' => $e->getMessage(),
                'details' => []
            ];
        }

        $this->redirectToProducts();
    }

    public function template() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::hasPermission('pos', 'read')) {
            die('No permission to download product template');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="product-import-template.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['name', 'sku', 'barcode', 'price', 'stock_quantity', 'category', 'status', 'description']);
        fputcsv($out, ['Iced Latte', 'DRINK-001', '8850000000012', '2.50', '25', 'Drinks', 'active', 'Cold coffee drink']);
        fclose($out);
        exit;
    }

    private function store() {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'price' => (float)$_POST['price'],
            'category_id' => $_POST['category_id'] ?: null,
            'stock_quantity' => (int)$_POST['stock_quantity'],
            'sku' => $_POST['sku'] ?? '',
            'barcode' => $_POST['barcode'] ?? '',
            'status' => $_POST['status'] ?? 'active'
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $data['image'] = $this->uploadImage($_FILES['image']);
        }

        Product::create($data);
        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/products');
        exit;
    }

    private function update($id) {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'price' => (float)$_POST['price'],
            'category_id' => $_POST['category_id'] ?: null,
            'stock_quantity' => (int)$_POST['stock_quantity'],
            'sku' => $_POST['sku'] ?? '',
            'barcode' => $_POST['barcode'] ?? '',
            'status' => $_POST['status'] ?? 'active'
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $data['image'] = $this->uploadImage($_FILES['image']);
        }

        Product::update($id, $data);
        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/products');
        exit;
    }

    private function redirectToProducts() {
        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/products');
        exit;
    }

    private function loadRowsFromUploadedFile($file) {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new Exception(__('product_import_file_required'));
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($extension === 'csv' || $extension === 'txt') {
            return $this->readCsvFile($file['tmp_name']);
        }

        if ($extension === 'xlsx') {
            return $this->readXlsxFile($file['tmp_name']);
        }

        throw new Exception(__('product_import_type_error'));
    }

    private function loadRowsFromGoogleSheet($sheetUrl) {
        $csvUrl = $this->buildGoogleSheetCsvUrl($sheetUrl);
        $csv = $this->fetchUrl($csvUrl);
        if (preg_match('/^\s*</', $csv) && stripos($csv, '<html') !== false) {
            throw new Exception(__('product_import_sheet_fetch_error'));
        }
        return $this->readCsvString($csv);
    }

    private function readCsvFile($path) {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new Exception(__('product_import_file_required'));
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function readCsvString($csv) {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $csv);
        rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function readXlsxFile($path) {
        if (!class_exists('ZipArchive')) {
            throw new Exception(__('product_import_xlsx_unavailable'));
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new Exception(__('product_import_xlsx_error'));
        }

        $sharedStrings = $this->readXlsxSharedStrings($zip);
        $sheetPath = $this->getFirstXlsxSheetPath($zip);
        $sheetXml = $zip->getFromName($sheetPath);

        if ($sheetXml === false) {
            $zip->close();
            throw new Exception(__('product_import_xlsx_error'));
        }

        $xml = simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData)) {
            $zip->close();
            throw new Exception(__('product_import_xlsx_error'));
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r'];
                $index = $this->xlsxColumnIndex($ref);
                $cells[$index] = $this->xlsxCellValue($cell, $sharedStrings);
            }

            if (!empty($cells)) {
                $max = max(array_keys($cells));
                $line = [];
                for ($i = 0; $i <= $max; $i++) {
                    $line[] = $cells[$i] ?? '';
                }
                $rows[] = $line;
            }
        }

        $zip->close();
        return $rows;
    }

    private function readXlsxSharedStrings($zip) {
        $contents = $zip->getFromName('xl/sharedStrings.xml');
        if ($contents === false) {
            return [];
        }

        $xml = simplexml_load_string($contents);
        if (!$xml) {
            return [];
        }

        $strings = [];
        foreach ($xml->si as $item) {
            $text = '';
            if (isset($item->t)) {
                $text .= (string)$item->t;
            }
            if (isset($item->r)) {
                foreach ($item->r as $run) {
                    if (isset($run->t)) {
                        $text .= (string)$run->t;
                    }
                }
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private function getFirstXlsxSheetPath($zip) {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml !== false && $relsXml !== false) {
            $workbook = simplexml_load_string($workbookXml);
            $rels = simplexml_load_string($relsXml);

            if ($workbook && $rels && isset($workbook->sheets->sheet[0])) {
                $namespaces = $workbook->getNamespaces(true);
                $relNamespace = $namespaces['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
                $attrs = $workbook->sheets->sheet[0]->attributes($relNamespace);
                $relId = (string)($attrs['id'] ?? '');

                if ($relId !== '') {
                    foreach ($rels->children() as $rel) {
                        if ((string)$rel['Id'] === $relId) {
                            $target = ltrim((string)$rel['Target'], '/');
                            return str_starts_with($target, 'xl/') ? $target : 'xl/' . $target;
                        }
                    }
                }
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function xlsxColumnIndex($cellRef) {
        if (!preg_match('/^([A-Z]+)/i', $cellRef, $matches)) {
            return 0;
        }

        $letters = strtoupper($matches[1]);
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private function xlsxCellValue($cell, $sharedStrings) {
        $type = (string)$cell['t'];

        if ($type === 's') {
            $index = (int)($cell->v ?? 0);
            return $sharedStrings[$index] ?? '';
        }

        if ($type === 'inlineStr') {
            return (string)($cell->is->t ?? '');
        }

        return (string)($cell->v ?? '');
    }

    private function buildGoogleSheetCsvUrl($url) {
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception(__('product_import_sheet_url_error'));
        }

        // Already a CSV output URL — use as-is
        if (preg_match('/[?&](output|format)=csv/i', $url)) {
            return $url;
        }

        // Matches published spreadsheet (d/e/...) — pubhtml or pub path
        // e.g. https://docs.google.com/spreadsheets/d/e/2PACX-.../pubhtml?gid=123
        // e.g. https://docs.google.com/spreadsheets/d/e/2PACX-.../pub?gid=123
        if (preg_match('/docs\.google\.com\/spreadsheets\/d\/e\/([^\/?\#]+)/', $url, $matches)) {
            $docId = $matches[1];
            $gid   = $this->extractGoogleSheetGid($url);
            $csvUrl = 'https://docs.google.com/spreadsheets/d/e/' . $docId . '/pub?output=csv';
            if ($gid !== '0') {
                $csvUrl .= '&gid=' . urlencode($gid);
            }
            return $csvUrl;
        }

        // Regular editable spreadsheet URL
        // e.g. https://docs.google.com/spreadsheets/d/SHEET_ID/edit?gid=123
        if (preg_match('/docs\.google\.com\/spreadsheets\/d\/([^\/?\#]+)/', $url, $matches)) {
            $sheetId = $matches[1];
            $gid     = $this->extractGoogleSheetGid($url);
            $csvUrl  = 'https://docs.google.com/spreadsheets/d/' . $sheetId . '/export?format=csv';
            if ($gid !== '0') {
                $csvUrl .= '&gid=' . urlencode($gid);
            }
            return $csvUrl;
        }

        // Plain CSV URL
        if (preg_match('/\.csv($|[?#])/i', $url)) {
            return $url;
        }

        throw new Exception(__('product_import_sheet_url_error'));
    }

    private function extractGoogleSheetGid($url) {
        $parts = parse_url($url);

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
            if (!empty($query['gid'])) {
                return (string)$query['gid'];
            }
        }

        if (!empty($parts['fragment'])) {
            parse_str($parts['fragment'], $fragment);
            if (!empty($fragment['gid'])) {
                return (string)$fragment['gid'];
            }
        }

        if (preg_match('/gid=([0-9]+)/', $url, $matches)) {
            return $matches[1];
        }

        return '0';
    }

    private function fetchUrl($url) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_USERAGENT => 'MCU POS Product Import/1.0'
            ]);
            $body = curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($body !== false && $status >= 200 && $status < 400) {
                return $body;
            }

            throw new Exception(__('product_import_sheet_fetch_error') . ($error ? ' ' . $error : ''));
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 20,
                'user_agent' => 'MCU POS Product Import/1.0'
            ]
        ]);
        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            throw new Exception(__('product_import_sheet_fetch_error'));
        }

        return $body;
    }

    private function importProductRows($rows) {
        $products = $this->normalizeProductRows($rows);
        $tenantId = Tenant::getId();
        $result = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($products as $item) {
            if (!empty($item['error'])) {
                $result['skipped']++;
                $result['errors'][] = $item['error'];
                continue;
            }

            $data = $item['data'];
            $hasCategory = array_key_exists('_category_name', $data);
            $categoryName = $data['_category_name'] ?? '';
            unset($data['_category_name']);

            if ($hasCategory) {
                $data['category_id'] = $categoryName !== ''
                    ? $this->findOrCreateCategory($categoryName, $tenantId)
                    : null;
            }

            $existing = Product::findBySkuOrBarcode($data['sku'] ?? '', $data['barcode'] ?? '', $tenantId);

            if ($existing) {
                Product::update($existing['id'], $data, $tenantId);
                $result['updated']++;
                continue;
            }

            $data = array_merge([
                'description' => '',
                'price' => 0,
                'stock_quantity' => 0,
                'sku' => '',
                'barcode' => '',
                'status' => 'active'
            ], $data);

            Product::create($data, $tenantId);
            $result['created']++;
        }

        if (count($result['errors']) > 8) {
            $result['errors'] = array_slice($result['errors'], 0, 8);
            $result['errors'][] = __('product_import_more_errors');
        }

        return $result;
    }

    private function normalizeProductRows($rows) {
        $cleanRows = [];
        foreach ($rows as $row) {
            $clean = array_map([$this, 'cleanImportCell'], (array)$row);
            if (!$this->rowIsEmpty($clean)) {
                $cleanRows[] = $clean;
            }
        }

        if (empty($cleanRows)) {
            throw new Exception(__('product_import_empty'));
        }

        $headerMap = $this->mapImportHeaders($cleanRows[0]);
        $hasHeader = !empty($headerMap);

        if ($hasHeader) {
            if (!array_key_exists('name', $headerMap)) {
                throw new Exception(__('product_import_name_header_missing'));
            }
            $dataRows = array_slice($cleanRows, 1);
            $firstRowNumber = 2;
        } else {
            $headerMap = [
                'name' => 0,
                'sku' => 1,
                'barcode' => 2,
                'price' => 3,
                'stock_quantity' => 4,
                'category_name' => 5,
                'status' => 6,
                'description' => 7
            ];
            $dataRows = $cleanRows;
            $firstRowNumber = 1;
        }

        $items = [];
        foreach ($dataRows as $index => $row) {
            $rowNumber = $firstRowNumber + $index;
            $name = $this->importValue($row, $headerMap, 'name');

            if ($name === '') {
                $items[] = [
                    'error' => __('product_import_row_missing_name', ['row' => $rowNumber])
                ];
                continue;
            }

            $data = ['name' => $name];

            foreach (['description', 'sku', 'barcode'] as $field) {
                if (array_key_exists($field, $headerMap)) {
                    $data[$field] = $this->importValue($row, $headerMap, $field);
                }
            }

            if (array_key_exists('price', $headerMap)) {
                $data['price'] = $this->parseImportMoney($this->importValue($row, $headerMap, 'price'));
            }

            if (array_key_exists('stock_quantity', $headerMap)) {
                $data['stock_quantity'] = $this->parseImportInteger($this->importValue($row, $headerMap, 'stock_quantity'));
            }

            if (array_key_exists('status', $headerMap)) {
                $data['status'] = $this->normalizeImportStatus($this->importValue($row, $headerMap, 'status'));
            }

            if (array_key_exists('category_name', $headerMap)) {
                $data['_category_name'] = $this->importValue($row, $headerMap, 'category_name');
            }

            $items[] = ['data' => $data];
        }

        return $items;
    }

    private function mapImportHeaders($row) {
        $aliases = [
            'name' => ['name', 'product', 'product_name', 'item', 'item_name', 'title'],
            'description' => ['description', 'desc', 'detail', 'details', 'note', 'notes'],
            'price' => ['price', 'retail_price', 'unit_price', 'amount', 'sale_price'],
            'stock_quantity' => ['stock', 'stock_quantity', 'stock_qty', 'quantity', 'qty', 'inventory', 'opening_stock'],
            'sku' => ['sku', 'code', 'product_code', 'reference', 'ref'],
            'barcode' => ['barcode', 'bar_code', 'ean', 'upc'],
            'category_name' => ['category', 'category_name', 'type', 'group'],
            'status' => ['status', 'active', 'visibility']
        ];

        $lookup = [];
        foreach ($aliases as $field => $fieldAliases) {
            foreach ($fieldAliases as $alias) {
                $lookup[$alias] = $field;
            }
        }

        $map = [];
        foreach ($row as $index => $heading) {
            $normalized = $this->normalizeImportHeader($heading);
            if (isset($lookup[$normalized]) && !isset($map[$lookup[$normalized]])) {
                $map[$lookup[$normalized]] = $index;
            }
        }

        return $map;
    }

    private function normalizeImportHeader($value) {
        $value = strtolower($this->cleanImportCell($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim($value, '_');
    }

    private function cleanImportCell($value) {
        $value = (string)$value;
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        return trim($value);
    }

    private function rowIsEmpty($row) {
        foreach ($row as $cell) {
            if ($this->cleanImportCell($cell) !== '') {
                return false;
            }
        }
        return true;
    }

    private function importValue($row, $headerMap, $field) {
        if (!array_key_exists($field, $headerMap)) {
            return '';
        }

        $index = $headerMap[$field];
        return $this->cleanImportCell($row[$index] ?? '');
    }

    private function parseImportMoney($value) {
        $value = preg_replace('/[^0-9.\-]/', '', (string)$value);
        return $value === '' ? 0 : (float)$value;
    }

    private function parseImportInteger($value) {
        $value = preg_replace('/[^0-9\-]/', '', (string)$value);
        return $value === '' ? 0 : (int)$value;
    }

    private function normalizeImportStatus($value) {
        $value = strtolower(trim((string)$value));
        if (in_array($value, ['inactive', 'disabled', 'hidden', 'archived', 'no', 'false', '0'], true)) {
            return 'inactive';
        }

        return 'active';
    }

    private function findOrCreateCategory($name, $tenantId) {
        $name = trim((string)$name);
        if ($name === '') {
            return null;
        }

        $db = Database::getInstance();
        $existing = $db->fetchOne(
            "SELECT id FROM categories WHERE tenant_id = ? AND LOWER(name) = LOWER(?) LIMIT 1",
            [$tenantId, $name]
        );

        if ($existing) {
            return (int)$existing['id'];
        }

        return (int)$db->insert('categories', [
            'tenant_id' => $tenantId,
            'name' => $name,
            'description' => ''
        ]);
    }

    private function getCategories() {
        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        return $db->fetchAll("SELECT * FROM categories WHERE tenant_id = ? ORDER BY name", [$tenantId]);
    }

    private function uploadImage($file) {
        $uploadDir = __DIR__ . '/../../../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Increase memory limit for processing large images
        ini_set('memory_limit', '512M');

        // Generate unique filename with .webp extension
        $fileName = uniqid() . '.webp';
        $targetPath = $uploadDir . $fileName;

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            die('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
        }

        // Convert image to WebP
        $image = null;
        switch ($file['type']) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                // Preserve transparency for PNG
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file['tmp_name']);
                break;
            case 'image/webp':
                // If already WebP, just move it
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    return $fileName;
                } else {
                    die('Failed to upload image.');
                }
                break;
        }

        if ($image === null) {
            die('Failed to process image.');
        }

        // Convert and save as WebP with 80% quality
        if (imagewebp($image, $targetPath, 80)) {
            imagedestroy($image);
            return $fileName;
        } else {
            imagedestroy($image);
            die('Failed to save converted image.');
        }
    }
}
?>
