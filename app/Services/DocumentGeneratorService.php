<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\Worker;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Response;

class DocumentGeneratorService
{
    /**
     * Worker variables mapping
     */
    const WORKER_VARIABLES = [
        '{worker_name}' => 'Имя работника',
        '{worker_surname}' => 'Фамилия работника',
        '{worker_full_name}' => 'Полное имя',
        '{worker_phone}' => 'Телефон',
        '{worker_email}' => 'Email',
        '{worker_birth_date}' => 'Дата рождения',
        '{worker_nationality}' => 'Национальность',
        '{worker_gender}' => 'Пол',
    ];

    /**
     * Company variables mapping
     */
    const COMPANY_VARIABLES = [
        '{company_name}' => 'Название компании',
        '{company_address}' => 'Адрес компании',
        '{company_ico}' => 'IČO компании',
        '{company_phone}' => 'Телефон компании',
        '{company_bank_account}' => 'Банковский счёт компании',
        '{current_date}' => 'Текущая дата',
        '{current_date_full}' => 'Дата прописью',
    ];

    /**
     * Assignment variables mapping
     */
    const ASSIGNMENT_VARIABLES = [
        '{hotel_name}' => 'Название отеля',
        '{hotel_address}' => 'Адрес отеля',
        '{room_number}' => 'Номер комнаты',
        '{work_place_name}' => 'Рабочее место',
        '{work_place_address}' => 'Адрес рабочего места',
        '{work_place_position}' => 'Должность',
        '{check_in_date}' => 'Дата заселения',
        '{employment_in_date}' => 'Дата устройства на работу',
    ];
    
    /**
     * Dynamic variables (user input required)
     */
    const DYNAMIC_VARIABLES = [
        '{choose_date}' => 'Выбор даты (пользовательский ввод)',
    ];

    /**
     * Generate PDF document
     */
    public function generatePdf(DocumentTemplate $template, Worker $worker, array $dynamicData = []): Response
    {
        $content = $this->replaceVariables($template->content, $worker, $dynamicData);
        
        // Clean up HTML from TinyMCE
        $content = $this->cleanHtmlForPdf($content);
        
        $html = $this->wrapHtml($content);
        
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        $filename = $this->generateFilename($template, $worker, 'pdf');
        
        return $pdf->download($filename);
    }
    
    /**
     * Clean HTML content for PDF generation
     */
    protected function cleanHtmlForPdf(string $html): string
    {
        // Remove TinyMCE specific classes and styles that might cause issues
        $html = preg_replace('/\s+class="[^"]*mce[^"]*"/i', '', $html);
        
        // Remove empty paragraphs (various formats)
        $html = preg_replace('/<p>\s*(&nbsp;|\xc2\xa0)?\s*<\/p>/i', '', $html);
        $html = preg_replace('/<p><br\s*\/?><\/p>/i', '', $html);
        $html = preg_replace('/<p>\s*<br\s*\/?>\s*<\/p>/i', '', $html);
        
        // Remove trailing empty elements
        $html = preg_replace('/(<p>\s*(&nbsp;|\xc2\xa0|<br\s*\/?>)?\s*<\/p>\s*)+$/i', '', $html);
        $html = preg_replace('/(<br\s*\/?>\s*)+$/i', '', $html);
        
        // Remove page-break at the end (causes empty page)
        $html = preg_replace('/<div[^>]*page-break[^>]*>\s*<\/div>\s*$/i', '', $html);
        
        // Remove excessive whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Fix font-family styles to use PDF-compatible fonts
        $html = preg_replace('/font-family:\s*["\']?times new roman["\']?,?\s*[^;]*/i', 'font-family: "Times New Roman", serif', $html);
        
        // Trim whitespace
        $html = trim($html);
        
        return $html;
    }

    /**
     * Generate DOCX document
     */
    public function generateDocx(DocumentTemplate $template, Worker $worker, array $dynamicData = []): StreamedResponse
    {
        $content = $this->replaceVariables($template->content, $worker, $dynamicData);
        
        // Convert HTML to XHTML for PHPWord compatibility
        $content = $this->convertToXhtml($content);
        
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        Html::addHtml($section, $content, false, false);
        
        $filename = $this->generateFilename($template, $worker, 'docx');
        
        return response()->streamDownload(function () use ($phpWord) {
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
    
    /**
     * Convert HTML to XHTML compatible format for PHPWord
     */
    protected function convertToXhtml(string $html): string
    {
        // Remove colgroup and col tags (not supported by PHPWord)
        $html = preg_replace('/<colgroup[^>]*>.*?<\/colgroup>/is', '', $html);
        $html = preg_replace('/<col[^>]*\/?>/i', '', $html);
        
        // Close self-closing tags
        $html = preg_replace('/<br\s*\/?>/i', '<br/>', $html);
        $html = preg_replace('/<hr\s*\/?>/i', '<hr/>', $html);
        $html = preg_replace('/<img([^>]*[^\/])\s*>/i', '<img$1/>', $html);
        $html = preg_replace('/<input([^>]*[^\/])\s*>/i', '<input$1/>', $html);
        
        // Remove style attributes (can cause issues)
        $html = preg_replace('/\s+style="[^"]*"/i', '', $html);
        
        // Remove empty paragraphs that might cause issues
        $html = preg_replace('/<p>\s*<\/p>/i', '', $html);
        
        // Wrap in proper container if needed
        if (stripos($html, '<body') === false) {
            $html = '<body>' . $html . '</body>';
        }
        
        return $html;
    }

    /**
     * Generate Excel document
     */
    public function generateExcel(DocumentTemplate $template, Worker $worker, array $dynamicData = []): StreamedResponse
    {
        $content = $this->replaceVariables($template->content, $worker, $dynamicData);
        $plainText = strip_tags($content);
        
        $filename = $this->generateFilename($template, $worker, 'xlsx');
        
        return response()->streamDownload(function () use ($plainText, $template, $worker) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', $template->name);
            $sheet->setCellValue('A2', '');
            
            $lines = explode("\n", $plainText);
            $row = 3;
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $sheet->setCellValue('A' . $row, $line);
                    $row++;
                }
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Replace variables in content
     */
    public function replaceVariables(string $content, Worker $worker, array $dynamicData = []): string
    {
        $replacements = $this->buildReplacements($worker);
        
        // Replace standard variables
        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
        
        // Replace dynamic date fields
        $dynamicFields = $this->extractDynamicDateFields($content);
        foreach ($dynamicFields as $field) {
            $value = $dynamicData[$field['field_name']] ?? '';
            if ($value) {
                $value = date('d.m.Y', strtotime($value));
            }
            $content = str_replace($field['full_match'], $value, $content);
        }
        
        return $content;
    }

    /**
     * Get all available variables
     */
    public function getAvailableVariables(): array
    {
        return array_merge(
            self::WORKER_VARIABLES,
            self::COMPANY_VARIABLES,
            self::ASSIGNMENT_VARIABLES,
            self::DYNAMIC_VARIABLES
        );
    }
    
    /**
     * Extract dynamic date fields from template content
     * Format: {choose_date}:"Label text"
     */
    public function extractDynamicDateFields(string $content): array
    {
        $fields = [];
        // Match pattern {choose_date}:"Label text"
        preg_match_all('/\{choose_date\}:"([^"]+)"/', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $index => $match) {
            $fields[] = [
                'full_match' => $match[0],
                'label' => $match[1],
                'field_name' => 'choose_date_' . $index,
            ];
        }
        
        return $fields;
    }

    /**
     * Build replacements array for worker
     */
    protected function buildReplacements(Worker $worker): array
    {
        $user = Auth::user();
        $company = $user ? $user->ownerDetails() : null;
        
        $worker->load(['currentAssignment.room', 'currentAssignment.hotel', 'currentWorkAssignment.workPlace', 'currentWorkAssignment.position']);
        
        $assignment = $worker->currentAssignment;
        $workAssignment = $worker->currentWorkAssignment;

        // Safely get nested values
        $hotelName = '';
        $hotelAddress = '';
        $roomNumber = '';
        $checkInDate = '';
        $workPlaceName = '';
        $workPlaceAddress = '';
        $workPlacePosition = '';
        $employmentInDate = '';
        
        if ($assignment) {
            $hotelName = $assignment->hotel->name ?? '';
            $hotelAddress = $assignment->hotel->address ?? '';
            $roomNumber = $assignment->room->room_number ?? '';
            $checkInDate = $assignment->check_in_date ? date('d.m.Y', strtotime($assignment->check_in_date)) : '';
        }
        
        if ($workAssignment && $workAssignment->workPlace) {
            $workPlaceName = $workAssignment->workPlace->name ?? '';
            $workPlaceAddress = $workAssignment->workPlace->address ?? '';
            $workPlacePosition = $workAssignment->position->name ?? '';
            $employmentInDate = $workAssignment->started_at ? date('d.m.Y', strtotime($workAssignment->started_at)) : '';
        }

        return [
            // Worker variables
            '{worker_name}' => $worker->first_name ?? '',
            '{worker_surname}' => $worker->last_name ?? '',
            '{worker_full_name}' => trim(($worker->first_name ?? '') . ' ' . ($worker->last_name ?? '')),
            '{worker_phone}' => $worker->phone ?? '',
            '{worker_email}' => $worker->email ?? '',
            '{worker_birth_date}' => $worker->dob ? date('d.m.Y', strtotime($worker->dob)) : '',
            '{worker_nationality}' => $worker->nationality ?? '',
            '{worker_gender}' => $worker->gender ?? '',
            
            // Company variables
            '{company_name}' => $company ? (!empty($company->company_name) ? $company->company_name : $company->name) : '',
            '{company_address}' => $company ? ($company->company_address ?? '') : '',
            '{company_ico}' => $company ? ($company->company_ico ?? '') : '',
            '{company_phone}' => $company ? ($company->company_phone ?? '') : '',
            '{company_bank_account}' => $company ? ($company->company_bank_account ?? '') : '',
            '{current_date}' => date('d.m.Y'),
            '{current_date_full}' => $this->dateToWords(date('Y-m-d')),
            
            // Assignment variables
            '{hotel_name}' => $hotelName,
            '{hotel_address}' => $hotelAddress,
            '{room_number}' => $roomNumber,
            '{work_place_name}' => $workPlaceName,
            '{work_place_address}' => $workPlaceAddress,
            '{work_place_position}' => $workPlacePosition,
            '{check_in_date}' => $checkInDate,
            '{employment_in_date}' => $employmentInDate,
        ];
    }

    /**
     * Wrap content in HTML structure
     */
    protected function wrapHtml(string $content): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 15mm 20mm 15mm 20mm;
        }
        * { 
            font-family: "Times New Roman", "DejaVu Serif", serif;
            box-sizing: border-box;
        }
        body { 
            font-size: 12pt; 
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        p { 
            margin: 0 0 8px 0; 
        }
        table { 
            border-collapse: collapse; 
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
            word-wrap: break-word;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        td, th { 
            border: 1px solid #000; 
            padding: 5px 8px;
            vertical-align: top;
            page-break-inside: avoid;
        }
        thead {
            display: table-header-group;
        }
        tfoot {
            display: table-footer-group;
        }
        h1, h2, h3, h4, h5, h6 {
            margin: 0 0 10px 0;
        }
        ul, ol {
            margin: 0 0 10px 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>' . $content . '</body>
</html>';
    }

    /**
     * Generate filename
     * Format: {FullName_Latin}_{DocumentName}_{Date}.{ext}
     */
    protected function generateFilename(DocumentTemplate $template, Worker $worker, string $extension): string
    {
        // Convert worker name to Latin characters
        $workerName = $this->transliterate($worker->first_name . '_' . $worker->last_name);
        $workerName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $workerName);
        $workerName = preg_replace('/_+/', '_', $workerName); // Remove multiple underscores
        $workerName = trim($workerName, '_');
        
        // Keep original document name (just clean special chars for filename)
        $documentName = preg_replace('/[\/\\\\:*?"<>|]/', '_', $template->name);
        $documentName = preg_replace('/_+/', '_', $documentName);
        $documentName = trim($documentName, '_');
        
        return $workerName . '_' . $documentName . '_' . date('Y-m-d') . '.' . $extension;
    }
    
    /**
     * Transliterate Cyrillic to Latin
     */
    protected function transliterate(string $text): string
    {
        $cyr = [
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
            'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я',
            'ě','š','č','ř','ž','ý','á','í','é','ů','ú','ň','ť','ď','ó',
            'Ě','Š','Č','Ř','Ž','Ý','Á','Í','É','Ů','Ú','Ň','Ť','Ď','Ó',
            'ą','ć','ę','ł','ń','ś','ź','ż','ó',
            'Ą','Ć','Ę','Ł','Ń','Ś','Ź','Ż','Ó',
            'ä','ö','ü','ß',
            'Ä','Ö','Ü'
        ];
        
        $lat = [
            'a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','kh','ts','ch','sh','shch','','y','','e','yu','ya',
            'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
            'R','S','T','U','F','Kh','Ts','Ch','Sh','Shch','','Y','','E','Yu','Ya',
            'e','s','c','r','z','y','a','i','e','u','u','n','t','d','o',
            'E','S','C','R','Z','Y','A','I','E','U','U','N','T','D','O',
            'a','c','e','l','n','s','z','z','o',
            'A','C','E','L','N','S','Z','Z','O',
            'ae','oe','ue','ss',
            'Ae','Oe','Ue'
        ];
        
        return str_replace($cyr, $lat, $text);
    }

    /**
     * Convert date to Russian words
     */
    protected function dateToWords(string $date): string
    {
        $months = [
            1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
            5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
            9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
        ];
        
        $d = date('j', strtotime($date));
        $m = (int)date('n', strtotime($date));
        $y = date('Y', strtotime($date));
        
        return $d . ' ' . $months[$m] . ' ' . $y . ' г.';
    }
}
