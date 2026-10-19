<?php

namespace App\Services;

/**
 * ResumeParserService
 *
 * Extracts Name, Email, Phone, Skills, Education from uploaded resume.
 * Supports PDF and DOCX files.
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
        $extension = strtolower($extension);
        $text = '';
        

        if ($extension === 'pdf') {
            $text = $this->extractFromPdf($path);
        } elseif (in_array($extension, ['doc', 'docx'])) {
            $text = $this->extractFromDocx($path);
        } elseif ($extension === 'txt') {
            $text = file_get_contents($path);
        } else {
            throw new \Exception("Unsupported file type: {$extension}. PDF, DOC, DOCX ya TXT upload karein.");
        }

        if (empty(trim($text))) {
            return '';
        }

        // return $text;

        return [
            'name'      => $this->extractName($text),
            'email'     => $this->extractEmail($text),
            'phone'     => $this->extractPhone($text),
            'skills'    => $this->extractSkills($text),
            'education' => $this->extractEducation($text),
            'raw_text'  => mb_substr($text, 0, 3000), // Store first 3000 chars
        ];
    }

    private function extractFromPdf(string $path): string
    {
        // Try smalot/pdfparser first
        if (class_exists('\Smalot\PdfParser\Parser')) {
            $parser   = new \Smalot\PdfParser\Parser();
            $pdf      = $parser->parseFile($path);
            return $pdf->getText();
        }

        // Fallback: pdftotext system command
        $output = shell_exec("pdftotext " . escapeshellarg($path) . " -");
        if ($output) return $output;

        throw new \Exception('PDF parser library nahi mili. `composer require smalot/pdfparser` run karein.');
    }

    private function extractFromDocx(string $path): string
    {
        if (!class_exists('\PhpOffice\PhpWord\IOFactory')) {
            throw new \Exception('PhpWord library nahi mili. `composer require phpoffice/phpword` run karein.');
        }

        $phpWord  = \PhpOffice\PhpWord\IOFactory::load($path);
        $fullText = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $fullText .= $this->elementToText($element) . "\n";
            }
        }

        return $fullText;
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
