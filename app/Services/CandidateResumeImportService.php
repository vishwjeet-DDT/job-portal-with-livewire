<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Education;
use App\Models\Employment;
use App\Models\ResumeInboxImport;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Shared resume -> candidate pipeline used by both the recruitment chatbot
 * (JobBotController) and the admin Resume Inbox feature. Keeps the AI
 * extraction prompt and the DB-write logic in one place.
 */
class CandidateResumeImportService
{
    public function __construct(private OpenRouterJsonExtractor $ai)
    {
    }

    /**
     * Call the AI to turn raw resume text into structured candidate data.
     * Returns null on any failure (caller decides on a fallback).
     */
    public function extractStructuredData(string $text): ?array
    {
        $data = $this->ai->extractJson(
            'You are a resume parser. Return ONLY valid JSON. No markdown. No explanation.',
            "Extract candidate information from the resume below.\n\n" .
                "Return ONLY valid JSON in this format:\n" .
                "{\n" .
                '  "name":"",' . "\n" .
                '  "email":"",' . "\n" .
                '  "phone":"",' . "\n" .
                '  "total_experience_years":"",' . "\n" .
                '  "skills":[""],' . "\n" .
                '  "education":[{"course":"","university":"","course_type":"","from_year":"","to_year":""}],' . "\n" .
                '  "experience":[{"company_name":"","job_title":"","joining_date":"","is_current":""}]' . "\n" .
                "}\n\n" .
                "Resume:\n" .
                Str::limit($text, 5000),
            1500
        );

        if (!$data) {
            return null;
        }

        \Log::info('Resume Extraction Success', ['data' => $data]);

        return [
            'name'                    => $data['name'] ?? null,
            'email'                   => $data['email'] ?? null,
            'phone'                   => $data['phone'] ?? null,
            'total_experience_years'  => $data['total_experience_years'] ?? null,
            'skills'                  => is_array($data['skills'] ?? null) ? $data['skills'] : [],
            'education'               => is_array($data['education'] ?? null) ? $data['education'] : [],
            'experience'              => is_array($data['experience'] ?? null) ? $data['experience'] : [],
        ];
    }

    /**
     * Persist extracted resume data as a User + Candidate (+ Skills,
     * Education, Employment). Never throws for expected/validation-type
     * failures — returns a status instead so batch callers can continue.
     *
     * @param array $extracted name/email/phone/total_experience_years/skills[]/education[]/experience[]
     * @param string|null $resumeSourcePath absolute path to the resume file on disk, if it should be stored
     * @param string|null $resumeOriginalName original filename (used for the stored copy's name)
     * @param array|null $dedupe ['message_id' => ?string, 'uid' => ?int, 'attachment_filename' => ?string, 'attachment_hash' => ?string]
     * @return array{status: string, user: ?User, message: string}
     */
    public function importCandidate(array $extracted, ?string $resumeSourcePath = null, ?string $resumeOriginalName = null, ?array $dedupe = null): array
    {
        $name   = trim($extracted['name'] ?? '');
        $email  = trim($extracted['email'] ?? '');
        $phone  = preg_replace('/\D/', '', $extracted['phone'] ?? '');
        $skills = $extracted['skills'] ?? [];

        if (!$name || !$email) {
            return ['status' => 'error', 'user' => null, 'message' => 'Name ya email missing hai resume data me.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'user' => null, 'message' => "Email valid nahi hai: {$email}."];
        }

        if (User::where('email', $email)->exists()) {
            return ['status' => 'skipped', 'user' => null, 'message' => "Is email se pehle se account exist karta hai: {$email}"];
        }

        if (!empty($dedupe['attachment_hash']) && ResumeInboxImport::where('attachment_hash', $dedupe['attachment_hash'])->exists()) {
            return ['status' => 'skipped', 'user' => null, 'message' => 'Yeh resume pehle se import ho chuka hai.'];
        }

        try {
            DB::beginTransaction();

            $nameParts = explode(' ', $name, 2);

            $userModel = User::create([
                'name'          => $name,
                'first_name'    => $nameParts[0],
                'last_name'     => $nameParts[1] ?? '',
                'email'         => $email,
                'mobile_number' => $phone ?: null,
                'password'      => bcrypt($name . '@123'),
            ]);

            $userModel->assignRole('Candidates');

            $candidate = Candidate::create(['user_id' => $userModel->id]);

            foreach ($skills as $skillName) {
                $skillName = trim((string) $skillName);
                if (!$skillName) continue;

                $skill = Skill::firstOrCreate(['name' => $skillName]);

                DB::table('skillables')->insertOrIgnore([
                    'skill_id'       => $skill->id,
                    'skillable_id'   => $userModel->id,
                    'skillable_type' => User::class,
                ]);
            }

            foreach (($extracted['education'] ?? []) as $edu) {
                $course     = trim((string) ($edu['course'] ?? ''));
                $university = trim((string) ($edu['university'] ?? ''));
                $fromYear   = trim((string) ($edu['from_year'] ?? ''));
                $toYear     = trim((string) ($edu['to_year'] ?? ''));

                if (!$course || !$university || !$fromYear || !$toYear) {
                    continue; // education table columns are NOT NULL — skip incomplete rows
                }

                Education::create([
                    'user_id'     => $userModel->id,
                    'course'      => $course,
                    'university'  => $university,
                    'course_type' => trim((string) ($edu['course_type'] ?? '')) ?: 'Full Time',
                    'from_year'   => $fromYear,
                    'to_year'     => $toYear,
                ]);
            }

            foreach (($extracted['experience'] ?? []) as $exp) {
                $companyName = trim((string) ($exp['company_name'] ?? ''));
                $jobTitle    = trim((string) ($exp['job_title'] ?? ''));

                if (!$companyName || !$jobTitle) {
                    continue; // employments.company_name / job_title are NOT NULL
                }

                $joiningDate = trim((string) ($exp['joining_date'] ?? ''));
                $isCurrent   = strtolower(trim((string) ($exp['is_current'] ?? ''))) === 'no' ? 'no' : 'yes';

                Employment::create([
                    'user_id'         => $userModel->id,
                    'company_name'    => $companyName,
                    'job_title'       => $jobTitle,
                    'joining_date'    => $this->parseDateOrNull($joiningDate),
                    'is_current'      => $isCurrent,
                    'expected_notice' => null,
                ]);
            }

            if ($resumeSourcePath && is_file($resumeSourcePath)) {
                $directory = public_path('storage/resumes');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                $filename = time() . '_' . ($resumeOriginalName ?: basename($resumeSourcePath));
                copy($resumeSourcePath, $directory . '/' . $filename);

                $candidate->resume = 'resumes/' . $filename;
                $candidate->save();
            }

            if ($dedupe) {
                ResumeInboxImport::create([
                    'message_id'           => $dedupe['message_id'] ?? null,
                    'uid'                  => $dedupe['uid'] ?? null,
                    'attachment_filename'  => $dedupe['attachment_filename'] ?? ($resumeOriginalName ?? ''),
                    'attachment_hash'      => $dedupe['attachment_hash'] ?? '',
                    'imported_user_id'     => $userModel->id,
                    'imported_at'          => now(),
                ]);
            }

            DB::commit();

            return [
                'status'  => 'created',
                'user'    => $userModel,
                'message' => 'Candidate successfully add ho gaya!',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Candidate Resume Import Failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return ['status' => 'error', 'user' => null, 'message' => 'Candidate save karte waqt error aaya.'];
        }
    }

    private function parseDateOrNull(?string $date): ?string
    {
        if (!$date) return null;

        try {
            return \Carbon\Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
