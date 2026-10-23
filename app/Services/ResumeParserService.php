<?php

namespace App\Services;

/**
 * ResumeParserService
 *
 * Extracts Name, Email, Phone, Skills, Education from uploaded resume.
 * Supports PDF, DOC, DOCX, RTF and TXT files.
 *
 * Requirements (add to composer.json):
 *   "smalot/pdfparser": "^2.0"
 *   "phpoffice/phpword": "^1.2"
 */
class ResumeParserService
{
    // Common tech skills to scan for
    private array $knownSkills = [
        'php', 'laravel', 'mysql', 'postgresql', 'javascript', 'typescript',
        'vue', 'react', 'angular', 'node', 'python', 'django', 'flask',
        'java', 'spring', 'c#', '.net', 'c++', 'ruby', 'rails',
        'html', 'css', 'bootstrap', 'tailwind', 'sass', 'scss',
        'git', 'docker', 'kubernetes', 'aws', 'azure', 'gcp',
        'mongodb', 'redis', 'elasticsearch', 'graphql', 'rest', 'api',
        'linux', 'nginx', 'apache', 'jenkins', 'ci/cd',
        'excel', 'powerpoint', 'word', 'photoshop', 'figma',
        'livewire', 'jquery', 'wordpress', 'magento', 'shopify',
        'android', 'ios', 'flutter', 'swift', 'kotlin', 'react native',
        'machine learning', 'deep learning', 'tensorflow', 'pytorch',
        'data analysis', 'tableau', 'power bi',
    ];

    // Education keywords
    private array $educationKeywords = [
        'b.tech', 'b.e.', 'b.sc', 'b.com', 'b.a.', 'bca', 'bba',
        'm.tech', 'm.e.', 'm.sc', 'm.com', 'mca', 'mba', 'm.a.',
        'phd', 'ph.d', 'diploma', '10th', '12th', 'ssc', 'hsc',
        'bachelor', 'master', 'graduate', 'post graduate',
        'university', 'college', 'institute', 'school',
    ];

    public function parse($file)
    {
        return $this->parseFromPath($file->getRealPath(), strtolower($file->getClientOriginalExtension()));
    }

    /**
     * Same as parse(), but works off a raw file path + extension instead of
     * an UploadedFile — used when the source is not an HTTP upload (e.g. an
     * IMAP attachment written to a temp file).
     */
    public function parseFromPath(string $path, string $extension): array|string
    {
        // Trust the file's real content over its extension: inboxes regularly
        // contain old binary .doc files, .docx files renamed to .doc, or PDFs
        // with the wrong extension — feeding those to the wrong reader is what
        // produced "The archive failed to load with the following error code: 19".
        $format = $this->detectFormat($path) ?? strtolower($extension);

        $text = match ($format) {
            'pdf'  => $this->extractFromPdf($path),
            'docx' => $this->extractFromDocx($path),
            'doc'  => $this->extractFromLegacyDoc($path),
            'rtf'  => $this->extractWithPhpWord($path, 'RTF'),
            'txt'  => (string) file_get_contents($path),
            default => throw new \Exception("Unsupported file type: {$extension}. Please upload a PDF, DOC, DOCX, RTF or TXT file."),
        };

        $text = $this->normalizeText($text);

        if (empty(trim($text))) {
            return '';
        }

        return [
            'name'      => $this->extractName($text),
            'email'     => $this->extractEmail($text),
            'phone'     => $this->extractPhone($text),
            'skills'    => $this->extractSkills($text),
            'education' => $this->extractEducation($text),
            'raw_text'  => mb_substr($text, 0, 3000), // Store first 3000 chars
        ];
    }

    /**
     * Sniff the real document format from the file's magic bytes.
     */
    private function detectFormat(string $path): ?string
    {
        $head = (string) @file_get_contents($path, false, null, 0, 8);

        return match (true) {
            str_starts_with($head, '%PDF')             => 'pdf',
            str_starts_with($head, "PK\x03\x04")       => 'docx',
            str_starts_with($head, "\xD0\xCF\x11\xE0") => 'doc',
            str_starts_with($head, '{\\rtf')           => 'rtf',
            default                                    => null,
        };
    }

    private function extractFromPdf(string $path): string
    {
        // pdftotext (poppler-utils) first: it is much faster and keeps words
        // intact, while smalot/pdfparser often splits them apart
        // ("singh .a m an @ gm ail .c o m"), which broke email/name extraction.
        $output = $this->runCommand('pdftotext', '-layout ' . escapeshellarg($path) . ' -');
        if ($output && trim($output) !== '') {
            return $output;
        }

        if (class_exists('\Smalot\PdfParser\Parser')) {
            try {
                $text = (new \Smalot\PdfParser\Parser())->parseFile($path)->getText();
                if (trim($text) !== '') {
                    return $text;
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        throw new \Exception('Could not read text from this PDF (it may be a scanned image or password protected).');
    }

    /**
     * Old binary Word (.doc, Word 97-2003). PhpWord's MSDoc reader handles
     * many files; antiword/catdoc (if installed on the server) are more
     * reliable, and as a last resort the readable text runs are pulled out
     * of the binary directly.
     */
    private function extractFromLegacyDoc(string $path): string
    {
        foreach (['antiword' => '', 'catdoc' => '-w '] as $binary => $flags) {
            $output = $this->runCommand($binary, $flags . escapeshellarg($path));
            if ($output && trim($output) !== '') {
                return $output;
            }
        }

        // PhpWord's MSDoc reader decodes 8-bit .doc text as UTF-16, which
        // yields CJK-looking garbage, so its output is only used when it
        // actually reads as text; otherwise the raw text runs win.
        $candidates = [$this->extractRawDocText($path)];

        try {
            $candidates[] = $this->extractWithPhpWord($path, 'MSDoc');
        } catch (\Throwable $e) {
            // ignore — raw extraction is still available
        }

        usort($candidates, fn ($a, $b) => $this->readableScore($b) <=> $this->readableScore($a));

        return $candidates[0];
    }

    /**
     * Rough "is this real text" score: count of Latin letters/digits/spaces.
     */
    private function readableScore(string $text): int
    {
        return (int) preg_match_all('/[A-Za-z0-9@.,\s]/', $text);
    }

    /**
     * Read the DOCX XML directly: unlike PhpWord this also picks up text
     * boxes, shapes and headers/footers, where many resume templates keep
     * the name, email and phone. PhpWord is the fallback.
     */
    private function extractFromDocx(string $path): string
    {
        $zip = new \ZipArchive();

        if ($zip->open($path) === true) {
            $parts = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('#^word/(header\d*|document|footer\d*)\.xml$#', $name)) {
                    $parts[$name] = $zip->getFromIndex($i);
                }
            }
            $zip->close();

            // header(s) first, then body, then footer(s)
            uksort($parts, fn ($a, $b) => (str_contains($a, 'header') ? 0 : (str_contains($a, 'document') ? 1 : 2))
                <=> (str_contains($b, 'header') ? 0 : (str_contains($b, 'document') ? 1 : 2)));

            $text = '';
            foreach ($parts as $xml) {
                $xml = preg_replace('#<mc:Fallback>.*?</mc:Fallback>#s', '', (string) $xml); // text boxes are stored twice
                $xml = preg_replace(['#</w:p>#', '#<w:tab/>#', '#<w:br\s*/>#', '#</w:tc>#'], ["\n", "\t", "\n", ' '], $xml);
                $text .= html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8') . "\n";
            }

            if (trim($text) !== '') {
                return $text;
            }
        }

        return $this->extractWithPhpWord($path, 'Word2007');
    }

    private function extractWithPhpWord(string $path, string $reader): string
    {
        if (!class_exists('\PhpOffice\PhpWord\IOFactory')) {
            throw new \Exception('PhpWord library is missing. Run `composer require phpoffice/phpword`.');
        }

        $phpWord  = \PhpOffice\PhpWord\IOFactory::load($path, $reader);
        $fullText = '';

        foreach ($phpWord->getSections() as $section) {
            // Many resumes keep name/email/phone in the page header.
            foreach ($section->getHeaders() as $header) {
                $fullText .= $this->elementToText($header) . "\n";
            }

            foreach ($section->getElements() as $element) {
                $fullText .= $this->elementToText($element) . "\n";
            }

            foreach ($section->getFooters() as $footer) {
                $fullText .= $this->elementToText($footer) . "\n";
            }
        }

        return $fullText;
    }

    /**
     * Word 97 stores body text either as 8-bit or UTF-16LE runs inside the
     * binary; keep runs of readable characters and drop the rest.
     */
    private function extractRawDocText(string $path): string
    {
        $binary = (string) file_get_contents($path);

        // UTF-16LE text: printable ASCII byte followed by a NUL byte.
        $utf16 = preg_match_all('/(?:[\x20-\x7E\t\r\n]\x00){4,}/', $binary, $m)
            ? array_map(fn ($run) => str_replace("\x00", '', $run), $m[0])
            : [];

        // 8-bit text.
        $ascii = preg_match_all('/[\x20-\x7E\t\r\n]{6,}/', $binary, $m) ? $m[0] : [];

        // Drop binary noise: keep runs that contain words, not symbol soup.
        $clean = fn (array $runs) => array_values(array_filter(
            array_map('trim', $runs),
            fn ($run) => $run !== '' && preg_match_all('/[A-Za-z]/', $run) >= strlen($run) * 0.5
        ));
        $utf16 = $clean($utf16);
        $ascii = $clean($ascii);

        // The body is whichever encoding holds most of the text; the other one
        // is mostly style/font tables, so it goes last (raw_text is truncated).
        $groups = strlen(implode('', $ascii)) >= strlen(implode('', $utf16)) ? [$ascii, $utf16] : [$utf16, $ascii];

        return implode("\n", array_unique(array_merge(...$groups)));
    }

    private function runCommand(string $binary, string $args): ?string
    {
        if (!function_exists('shell_exec') || !trim((string) @shell_exec('command -v ' . escapeshellarg($binary) . ' 2>/dev/null'))) {
            return null;
        }

        return @shell_exec($binary . ' ' . $args . ' 2>/dev/null') ?: null;
    }

    /**
     * Make sure the text is valid UTF-8 — invalid bytes break json_encode,
     * the AI request and MySQL inserts.
     */
    private function normalizeText(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        }

        return (string) preg_replace('/[^\P{C}\n\t]/u', ' ', $text);
    }

    /**
     * Recursively flatten a PhpWord element (Text, TextRun, ListItemRun,
     * Cell, etc.) into plain text. getText() on some elements (e.g. Text
     * elements produced from Word "structured document tags"/content
     * controls) can itself return a nested TextRun object instead of a
     * string, so every level is checked before concatenation.
     */
    private function elementToText(mixed $element): string
    {
        if (is_string($element)) {
            return $element;
        }

        if (!is_object($element)) {
            return '';
        }

        if (method_exists($element, 'getRows')) { // Table
            $text = '';
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    $text .= $this->elementToText($cell) . ' ';
                }
                $text .= "\n";
            }
            return $text;
        }

        if (method_exists($element, 'getElements')) {
            $text = '';
            foreach ($element->getElements() as $child) {
                $text .= $this->elementToText($child) . ' ';
            }
            return $text;
        }

        if (method_exists($element, 'getText')) {
            return $this->elementToText($element->getText());
        }

        return '';
    }

    // ─── Extractors ──────────────────────────────────────────────────────────

    private function extractEmail(string $text): string
    {
        if (preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/i', $text, $m)) {
            return strtolower(trim($m[0]));
        }
        return '';
    }

    private function extractPhone(string $text): string
    {
        // Indian mobile: 10 digits, optionally +91 or 0
        $patterns = [
            '/(?:\+91[\s\-]?|0)?[6-9]\d{9}/',
            '/\b\d{3}[\s\-]\d{3}[\s\-]\d{4}\b/',
            '/\(\d{3}\)[\s\-]?\d{3}[\s\-]\d{4}/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return preg_replace('/[\s\-]/', '', trim($m[0]));
            }
        }
        return '';
    }

    private function extractName(string $text): string
    {
        $lines = array_filter(
            array_map('trim', explode("\n", $text)),
            fn($l) => strlen($l) > 1
        );
        $lines = array_values($lines);

        // First non-empty line that looks like a name (2-4 words, no special chars, no email/phone)
        foreach (array_slice($lines, 0, 10) as $line) {
            // Skip if looks like email, phone, address, URL
            if (preg_match('/[@\d:\/\\\\]/', $line)) continue;
            if (strlen($line) > 60) continue;

            $wordCount = str_word_count($line);
            if ($wordCount >= 2 && $wordCount <= 4) {
                // Remove non-name characters
                $clean = preg_replace('/[^a-zA-Z\s\.]/', '', $line);
                $clean = trim($clean);
                if (strlen($clean) >= 4 && str_word_count($clean) >= 2) {
                    return ucwords(strtolower($clean));
                }
            }
        }

        // Fallback: look for "Name: X" pattern
        if (preg_match('/(?:name|naam)\s*[:\-]?\s*([A-Za-z][A-Za-z\s]{2,40})/i', $text, $m)) {
            return ucwords(strtolower(trim($m[1])));
        }

        return '';
    }

    private function extractSkills(string $text): array
    {
        $lowerText = mb_strtolower($text);
        $found = [];

        foreach ($this->knownSkills as $skill) {
            // Match whole word or bounded
            $pattern = '/\b' . preg_quote($skill, '/') . '\b/i';
            if (preg_match($pattern, $lowerText)) {
                $found[] = ucfirst($skill);
            }
        }

        // Also extract skills listed after "Skills:" section
        if (preg_match('/skills?\s*[:\-]\s*(.+?)(?:\n\n|\z)/is', $text, $m)) {
            $skillSection = $m[1];
            $items = preg_split('/[,;|•\n]+/', $skillSection);
            foreach ($items as $item) {
                $item = trim(preg_replace('/\s+/', ' ', $item));
                if (strlen($item) >= 2 && strlen($item) <= 40 && !in_array(strtolower($item), array_map('strtolower', $found))) {
                    $found[] = ucfirst(strtolower($item));
                }
            }
        }

        return array_unique(array_values($found));
    }

    private function extractEducation(string $text): array
    {
        $education = [];
        $lines     = array_map('trim', explode("\n", $text));

        foreach ($lines as $line) {
            $lower = mb_strtolower($line);
            foreach ($this->educationKeywords as $kw) {
                if (str_contains($lower, $kw) && strlen($line) >= 5 && strlen($line) <= 200) {
                    $education[] = trim($line);
                    break;
                }
            }
        }

        // Deduplicate and limit to 5
        return array_slice(array_unique($education), 0, 5);
    }
}
