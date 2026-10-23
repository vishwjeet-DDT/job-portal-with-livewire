<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Candidate;
use App\Services\ResumeParserService;
use App\Services\CandidateResumeImportService;
use App\Services\JobDescriptionMatcherService;

class JobBotController extends Controller
{
    public function __construct(
        private CandidateResumeImportService $resumeImportService,
        private JobDescriptionMatcherService $jdMatcher,
    ) {
    }


    // ─────────────────────────────────────────────
    // INTENT MAP
    // ─────────────────────────────────────────────
    private array $intents = [
        'find_candidates' => [
            'patterns' => [
                'find candidate', 'find position','search candidate', 'candidate dhundo', 'candidate chahiye',
                'skill se candidate', 'candidate by skill', 'who has skill', 'kaun jaanta hai',
                'developer chahiye', 'designer chahiye', 'engineer chahiye', 'candidate list',
                'candidates with', 'candidates for', 'candidates',
            ],
            'handler' => 'handleFindCandidates',
        ],
        'find_jobs' => [
            'patterns' => [
                'find job', 'job dhundo', 'job chahiye', 'jobs for skill', 'skill ke liye job',
                'job list', 'available jobs', 'job search', 'kaunsi job', 'jobs available',
                'job openings', 'vacancies', 'naukri', 'job chahiye', 'jobs for',
            ],
            'handler' => 'handleFindJobs',
        ],
        'parse_resume' => [
            'patterns' => [
                'resume upload', 'upload resume', 'resume parse', 'resume scan',
                'cv upload', 'upload cv', 'resume se nikalo', 'resume details',
                'resume analysis', 'scan resume', 'extract resume',
            ],
            'handler' => 'handleResumeInstruction',
        ],
        // 'add_candidate' => [
        //     'patterns' => [
        //         'add candidate', 'candidate add', 'naya candidate', 'new candidate',
        //         'candidate register', 'register candidate', 'candidate banao', 'create candidate',
        //         'add user', 'candidate jodo',
        //     ],
        //     'handler' => 'handleAddCandidateStart',
        // ],
        'my_profile' => [
            'patterns' => [
                'my profile', 'mera profile', 'my details', 'meri details',
                'who am i', 'profile dikhao', 'my info', 'mera account',
            ],
            'handler' => 'handleMyProfile',
        ],
        'help' => [
            'patterns' => [
                'help', 'kya kar', 'what can you do', 'kya puch', 'commands',
                'options', 'menu', 'features', 'kya pooch',
            ],
            'handler' => 'handleHelp',
        ],
        'save_resume' => [
            'patterns' => [
                'save karo', 'save kar do', 'add karo', 'add kar do', 'candidate add karo',
                'haan save', 'yes save', 'save it', 'save candidate', 'add candidate from resume',
                'jodo', 'add kar', 'save kar', 'confirm save', 'resume save',
            ],
            'handler' => 'handleSaveResumeDraft',
        ],
    ];

    // ─────────────────────────────────────────────
    // MAIN ENTRY
    // ─────────────────────────────────────────────
    public function query(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:8000']);

        $queryText = trim($request->input('query'));
        $lower     = Str::lower($queryText);
        $user      = auth()->user();

        // ── Multi-step: pending intent (e.g. collecting candidate fields) ──
        if (session()->has('pending_intent')) {
            $intentKey = session('pending_intent');
            $step      = session('pending_step', 1);

            return $this->continueFlow($intentKey, $step, $queryText, $lower, $user);
        }

        // ── Job description pasted directly? Match candidates against it ──
        // Checked before intent-pattern matching: intent patterns are short
        // trigger phrases (e.g. "help", "features", "menu") and a long JD
        // paste can accidentally contain one of those words in a sentence
        // (e.g. "...advanced features like pre-aggregations..." matching the
        // help intent) — a real long-form paste is never a legitimate short
        // command, so it must win over an incidental substring match.
        if ($this->looksLikeJobDescription($queryText)) {
            return $this->handleJobDescriptionMatch($queryText);
        }

        // ── Normal intent detection ──
        $intent = $this->detectIntent($lower);

        if ($intent && method_exists($this, $intent['handler'])) {
            return $this->{$intent['handler']}($lower, $queryText, $user);
        }

        // ── Fallback: AI ──
        return $this->handleOpenRouter($queryText);
    }

    // ─────────────────────────────────────────────
    // RESUME UPLOAD (separate endpoint)
    // ─────────────────────────────────────────────
    public function uploadResume(Request $request): JsonResponse
    {
        
        
        $request->validate(['resume' => 'required|file|mimes:pdf,doc,docx|max:5120']);

        $parser = app(ResumeParserService::class);

        try {
            $text = $parser->parse($request->file('resume'));
        } catch (\Throwable $e) {
            \Log::warning('Chatbot resume parse failed', ['error' => $e->getMessage()]);
            $text = '';
        }
        
        

        // dd($text);
        if (!$text) {
            return $this->respond('⚠️ Could not extract text from the resume. Please try a PDF or DOCX file.', null);
        }

        $extracted = $this->extractResumeDataWithAI($text['raw_text']) ?? $text;


        if (!$extracted || (!$extracted['name'] && !$extracted['phone'] && !$extracted['email'])) {
            return $this->respond(
                '🤔 Could not extract details from the resume. Please enter the details manually or try a different format.',
                null
            );
        }

        session(['resume_draft' => $extracted]);
        $skills = $extracted['skills'] ?? [];

        return $this->respond(
            '📄 Resume parsed successfully! Here are the details found:',
            [
                'type'      => 'resume_result',
                'extracted' => $extracted,
                'skills'    => $skills,
            ]
        );
    }

    // ─────────────────────────────────────────────
    // INTENT HANDLERS
    // ─────────────────────────────────────────────

    private function handleFindCandidates(string $lower, string $original, $user): JsonResponse
    {
        $skill = $this->extractSkillName($lower);

        if (!$skill) {
            session(['pending_intent' => 'find_candidates', 'pending_step' => 1]);
            return $this->respond('🔍 Which skill should I search candidates for? (e.g. Laravel, React, PHP)', null);
        }

        return $this->searchCandidatesBySkill($skill);
    }

    private function handleFindJobs(string $lower, string $original, $user): JsonResponse
    {
        $skill = $this->extractSkillName($lower);

        if (!$skill) {
            session(['pending_intent' => 'find_jobs', 'pending_step' => 1]);
            return $this->respond('💼 Which skill should I search jobs for? (e.g. Laravel, React, Python)', null);
        }

        return $this->searchJobsBySkill($skill);
    }

    private function handleResumeInstruction(string $lower, string $original, $user): JsonResponse
    {
        return $this->respond(
            '📎 Upload a resume — I will extract the name, phone, email and skills!',
            ['type' => 'resume_upload_prompt']
        );
    }

    private function handleAddCandidateStart(string $lower, string $original, $user): JsonResponse
    {
        session([
            'pending_intent'    => 'add_candidate',
            'pending_step'      => 1,
            'candidate_draft'   => [],
        ]);

        return $this->respond(
            '➕ Let\'s add a candidate! First, enter the candidate\'s <strong>full name</strong>:',
            null
        );
    }

    private function handleMyProfile(string $lower, string $original, $user): JsonResponse
    {
        if (!$user) {
            return $this->respond('⚠️ You are not logged in.', null);
        }

        $candidate = Candidate::where('user_id', $user->id)->first();

        return $this->respond(
            "👤 Your profile details:",
            [
                'type' => 'profile',
                'rows' => [
                    ['Field' => 'Name',         'Value' => $user->name],
                    ['Field' => 'Email',        'Value' => $user->email],
                    ['Field' => 'Mobile',       'Value' => $user->mobile_number ?? '—'],
                    ['Field' => 'Position',     'Value' => $user->position ?? '—'],
                    ['Field' => 'Experience',   'Value' => $user->experience_type ?? '—'],
                    ['Field' => 'Availability', 'Value' => $candidate?->availability ?? '—'],
                    ['Field' => 'Location',     'Value' => $candidate?->hometown ?? '—'],
                ],
                'columns' => ['Field', 'Value'],
            ]
        );
    }

    private function handleHelp(string $lower, string $original, $user): JsonResponse
    {
        return $this->respond(
            "🤖 Here is what I can do:<br><br>" .
            "🔍 <strong>Candidate Search</strong><br>" .
            "• \"Find Laravel candidates\"<br>" .
            "• \"Candidates with React skill\"<br><br>" .
            "💼 <strong>Job Search</strong><br>" .
            "• \"Jobs for PHP\"<br>" .
            "• \"Python developer jobs available\"<br><br>" .
            "📄 <strong>Resume Parse</strong><br>" .
            "• \"Resume upload\" — extracts name, phone, email & skills<br><br>" .
            "🎯 <strong>JD Match</strong><br>" .
            "• Paste a full job description — matching candidates are shown with a match %<br><br>" .
            "➕ <strong>Candidate Add</strong><br>" .
            "• \"Add candidate\" — step-by-step form<br><br>" .
            "👤 <strong>My Profile</strong><br>" .
            "• \"My profile\" — view your details<br><br>" .
            "🌐 <strong>AI Fallback</strong> — ask anything and the AI will answer!",
            null
        );
    }

    // ─────────────────────────────────────────────
    // MULTI-STEP FLOW HANDLER
    // ─────────────────────────────────────────────
    private function continueFlow(string $intentKey, int $step, string $original, string $lower, $user): JsonResponse
    {
        if ($intentKey === 'find_candidates') {
            session()->forget(['pending_intent', 'pending_step']);
            return $this->searchCandidatesBySkill(trim($original));
        }

        if ($intentKey === 'find_jobs') {
            session()->forget(['pending_intent', 'pending_step']);
            return $this->searchJobsBySkill(trim($original));
        }

        if ($intentKey === 'add_candidate') {
            return $this->handleAddCandidateFlow($step, $original, $lower, $user);
        }

        session()->forget(['pending_intent', 'pending_step']);
        return $this->handleOpenRouter($original);
    }

    private function handleAddCandidateFlow(int $step, string $original, string $lower, $user): JsonResponse
    {
        $draft = session('candidate_draft', []);

        // Step 1: name → Step 2: email → Step 3: mobile → Step 4: confirm
        switch ($step) {
            case 1: // Got name
                $draft['name'] = trim($original);
                session(['candidate_draft' => $draft, 'pending_step' => 2]);
                return $this->respond("✅ Name: <strong>{$draft['name']}</strong><br><br>Now enter the candidate's <strong>email</strong>:", null);

            case 2: // Got email
                if (!filter_var(trim($original), FILTER_VALIDATE_EMAIL)) {
                    return $this->respond('⚠️ Please enter a valid email (e.g. john@gmail.com):', null);
                }
                $draft['email'] = trim($original);
                session(['candidate_draft' => $draft, 'pending_step' => 3]);
                return $this->respond("✅ Email: <strong>{$draft['email']}</strong><br><br>Now enter the <strong>mobile number</strong>:", null);

            case 3: // Got mobile
                $mobile = preg_replace('/\D/', '', trim($original));
                if (strlen($mobile) < 10) {
                    return $this->respond('⚠️ Please enter a valid 10-digit mobile number:', null);
                }
                $draft['mobile'] = $mobile;
                session(['candidate_draft' => $draft, 'pending_step' => 4]);

                return $this->respond(
                    "✅ Almost done! Save these details?<br><br>" .
                    "👤 Name: <strong>{$draft['name']}</strong><br>" .
                    "📧 Email: <strong>{$draft['email']}</strong><br>" .
                    "📱 Mobile: <strong>{$draft['mobile']}</strong><br><br>" .
                    "Type \"<em>yes</em>\" to confirm, or \"<em>cancel</em>\" to cancel.",
                    null
                );

            case 4: // Confirm
                if (in_array($lower, ['yes', 'haan', 'ha', 'y', 'ok', 'confirm', 'save'])) {
                    return $this->createCandidate($draft);
                }
                session()->forget(['pending_intent', 'pending_step', 'candidate_draft']);
                return $this->respond('❌ Adding the candidate was cancelled.', null);
        }

        session()->forget(['pending_intent', 'pending_step', 'candidate_draft']);
        return $this->respond('Something went wrong. Please try again.', null);
    }

    private function createCandidate(array $draft): JsonResponse
    {
        session()->forget(['pending_intent', 'pending_step', 'candidate_draft']);

        if (User::where('email', $draft['email'])->exists()) {
            return $this->respond("⚠️ An account with this email already exists: <strong>{$draft['email']}</strong>", null);
        }

        $nameParts = explode(' ', $draft['name'], 2);

        $user = User::create([
            'name'          => $draft['name'],
            'first_name'    => $nameParts[0],
            'last_name'     => $nameParts[1] ?? '',
            'email'         => $draft['email'],
            'mobile_number' => $draft['mobile'],
            'status'        => 1,
            'password'      => bcrypt(Str::random(12)),
        ]);

        $user->assignRole('Candidate');

        Candidate::create(['user_id' => $user->id]);

        return $this->respond(
            "✅ Candidate added successfully!<br><br>" .
            "👤 Name: <strong>{$user->name}</strong><br>" .
            "📧 Email: <strong>{$user->email}</strong><br>" .
            "🆔 User ID: <strong>{$user->id}</strong>",
            null
        );
    }

    // ─────────────────────────────────────────────
    // DB QUERIES
    // ─────────────────────────────────────────────
    private function searchCandidatesBySkill(string $skill)
    {
        $skill = strtolower(trim($skill));

        $candidates = DB::table('users as u')
            ->join('candidates as c', 'c.user_id', '=', 'u.id')
            ->leftJoin('skillables as sa', function ($join) {
                $join->on('sa.skillable_id', '=', 'u.id')
                    ->where('sa.skillable_type', '=', \App\Models\User::class);
            })
            ->leftJoin('skills as sk', 'sk.id', '=', 'sa.skill_id')
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'u.mobile_number',
                'u.position',
                'u.experience_type',
                DB::raw("GROUP_CONCAT(DISTINCT sk.name ORDER BY sk.name SEPARATOR ', ') as skills")
            )
            ->where(function ($q) use ($skill) {
                $q->whereRaw('LOWER(sk.name) LIKE ?', ["%{$skill}%"])
                ->orWhereRaw('LOWER(u.position) LIKE ?', ["%{$skill}%"]);
            })
            ->groupBy(
                'u.id',
                'u.name',
                'u.email',
                'u.mobile_number',
                'u.position',
                'u.experience_type'
            )
            ->limit(20)
            ->get();

        if ($candidates->isEmpty()) {
            return $this->respond(
                "😔 No candidates found with the \"<strong>{$skill}</strong>\" skill.",
                null
            );
        }

        return $this->respond(
            "🔍 Found <strong>{$candidates->count()}</strong> candidates with the \"<strong>{$skill}</strong>\" skill:",
            [
                'columns' => ['Name', 'Position', 'Experience', 'Skills', 'Contact'],
                'rows' => $candidates->map(fn($c) => [
                    'Name'       => $c->name,
                    'Position'   => $c->position ?? '—',
                    'Experience' => $c->experience_type ?? '—',
                    'Skills'     => $c->skills ?? '—',
                    'Contact'    => $c->mobile_number ?: $c->email,
                ])->toArray(),
            ]
        );
    }

    // ─────────────────────────────────────────────
    // JOB DESCRIPTION PASTE -> CANDIDATE MATCHING
    // ─────────────────────────────────────────────
    private function looksLikeJobDescription(string $text): bool
    {
        if (mb_strlen($text) > 250) {
            return true;
        }

        if (mb_strlen($text) < 80) {
            return false;
        }

        $lower = mb_strtolower($text);
        $keywords = [
            'job description', 'responsibilities', 'required skills', 'requirements',
            'mandatory skill', 'years of experience', 'roles and responsibilities',
            'key responsibilities', 'qualifications', 'job title', 'preferred skills',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function handleJobDescriptionMatch(string $jdText): JsonResponse
    {
        // AI extraction is best-effort (adds role title + a cleaner skills
        // seed) but matching itself does NOT depend on it — it also checks
        // job title, position, experience type, and profile summary against
        // whatever's actually in the database, so it still works if AI is
        // unavailable.
        $requirements = $this->jdMatcher->extractRequirements($jdText);
        $aiRequired   = $requirements['required_skills'] ?? [];
        $aiPreferred  = $requirements['preferred_skills'] ?? [];

        $minYears = $this->jdMatcher->requiredExperienceYears($jdText, $requirements['min_experience_years'] ?? null);

        $matches = $this->jdMatcher->matchCandidates($jdText, $aiRequired, $aiPreferred, 20, $minYears);

        $experienceNote = $minYears
            ? '<br><span style="opacity:.75">JD requires ' . rtrim(rtrim(number_format($minYears, 1), '0'), '.') . '+ years of experience — freshers are excluded.</span>'
            : '';

        if ($matches->isEmpty()) {
            return $this->respond(
                '😔 No candidates matched this JD — nothing matched on skills, job title, position or profile summary.' . $experienceNote,
                null
            );
        }

        $roleLine = !empty($requirements['role_title']) ? " — <strong>" . e($requirements['role_title']) . "</strong>" : '';

        return $this->respond(
            "🎯 JD match{$roleLine}<br>Found <strong>{$matches->count()}</strong> matching candidates, best match first:{$experienceNote}",
            [
                'columns' => ['Name', 'Email', 'Match %', 'Matched', 'Position', 'Experience', 'Contact', 'Resume'],
                'rows' => $matches->map(fn ($m) => [
                    'Name'       => $m['name'],
                    'Email'      => $m['email'] ?? '—',
                    'Match %'    => $m['match_percent'] . '%',
                    'Matched'    => $m['matched_skills'] ?: '—',
                    'Position'   => $m['position'] ?? '—',
                    'Experience' => $m['experience'],
                    'Contact'    => $m['contact'],
                    'Resume'     => $m['resume_url'] ? ['type' => 'link', 'url' => $m['resume_url'], 'label' => 'View Resume'] : '—',
                ])->toArray(),
            ]
        );
    }

    private function searchJobsBySkill(string $skill): JsonResponse
    {
        $skill = strtolower(trim($skill));

        $jobs = DB::table('jobs as j')
            ->join('employers as e', 'e.id', '=', 'j.employer_id')
            ->leftJoin('skillables as sa', function ($join) {
                $join->on('sa.skillable_id', '=', 'j.id')
                    ->where('sa.skillable_type', '=', \App\Models\Job::class);
            })
            ->leftJoin('skills as sk', 'sk.id', '=', 'sa.skill_id')
            ->select(
                'j.id',
                'j.title',
                'j.location',
                'j.job_type',
                'j.experience',
                'j.salary_min',
                'j.salary_max',
                'e.company_name',
                DB::raw("GROUP_CONCAT(DISTINCT sk.name ORDER BY sk.name SEPARATOR ', ') as required_skills")
            )
            ->where('j.status', 1)
            ->where(function ($q) use ($skill) {
                $q->whereRaw('LOWER(sk.name) LIKE ?', ["%{$skill}%"])
                ->orWhereRaw('LOWER(j.title) LIKE ?', ["%{$skill}%"])
                ->orWhereRaw('LOWER(j.description) LIKE ?', ["%{$skill}%"]);
            })
            ->groupBy(
                'j.id',
                'j.title',
                'j.location',
                'j.job_type',
                'j.experience',
                'j.salary_min',
                'j.salary_max',
                'e.company_name'
            )
            ->orderByDesc('j.created_at')
            ->limit(20)
            ->get();

        if ($jobs->isEmpty()) {
            return $this->respond(
                "😔 No jobs found for \"<strong>{$skill}</strong>\".",
                null
            );
        }

        return $this->respond(
            "💼 Found <strong>{$jobs->count()}</strong> jobs for \"<strong>{$skill}</strong>\":",
            [
                'columns' => [
                    'Title',
                    'Company',
                    'Location',
                    'Type',
                    'Experience',
                    'Salary',
                    'Skills Required'
                ],
                'rows' => $jobs->map(fn($j) => [
                    'Title'           => $j->title,
                    'Company'         => $j->company_name,
                    'Location'        => $j->location ?? '—',
                    'Type'            => $j->job_type ?? '—',
                    'Experience'      => $j->experience ?? '—',
                    'Salary'          => ($j->salary_min && $j->salary_max)
                        ? "₹{$j->salary_min} - ₹{$j->salary_max}"
                        : '—',
                    'Skills Required' => $j->required_skills ?? '—',
                ])->toArray(),
            ]
        );
    }

    // ─────────────────────────────────────────────
    // RESUME PARSING
    // ─────────────────────────────────────────────
    private function extractTextFromFile(string $path, string $ext): ?string
    {
        // dd($ext);
        try {
            if ($ext == 'pdf') {
                // Requires pdftotext (poppler-utils) on server
                $output = shell_exec("pdftotext " . escapeshellarg($path) . " -");
                return $output ?: null;
            }

            if (in_array($ext, ['doc', 'docx'])) {
                // Requires antiword / python-docx or similar
                $output = shell_exec("antiword " . escapeshellarg($path));
                return $output ?: null;
            }
        } catch (\Exception $e) {
            \Log::error('Resume text extraction failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function extractResumeDataWithAI(string $text): ?array
    {
        return $this->resumeImportService->extractStructuredData($text);
    }



    // ─────────────────────────────────────────────
    // OPEN ROUTER FALLBACK
    // ─────────────────────────────────────────────
    private function handleOpenRouter(string $userQuery): JsonResponse
    {
        $apiKey = config('services.openrouter.key');

        if (!$apiKey) {
            return $this->respond(
                "🤔 Sorry, I didn't understand \"<em>{$userQuery}</em>\".<br><br>" .
                "Ask about jobs/candidates or type \"<em>help</em>\".",
                null
            );
        }

        $cacheKey = 'jobbot_or_' . md5(Str::lower(trim($userQuery)));

        $reply = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userQuery, $apiKey) {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'HTTP-Referer'  => config('app.url'),
                    'X-Title'       => config('app.name'),
                ])
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model'       => 'nex-agi/nex-n2-pro:free',
                    'temperature' => 0.7,
                    'max_tokens'  => 400,
                    'messages'    => [[
                        'role'    => 'user',
                        'content' => $this->buildPrompt($userQuery),
                    ]],
                ]);

            if (!$response->ok()) {
                \Log::error('JobBot OpenRouter Error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            return $response->json('choices.0.message.content');
        });

        if (!$reply) {
            return $this->respond(
                '⚠️ No response from the AI. Please try again in a moment.',
                null
            );
        }

        return $this->respond($this->markdownToHtml($reply), null);
    }

    private function buildPrompt(string $query): string
    {
        $date = now()->format('d M Y, l');
        return <<<PROMPT
        You are "Job Sphere Assistant" — the AI helper of a job portal for candidates and employers.
        Today's date: {$date} IST.

        User's question: "{$query}"

        Rules:
        - Always reply in English only, even if the question is in Hindi or Hinglish; friendly and concise
        - Maximum 4-5 lines
        - Use a few emojis
        - Answer job search, resume and career advice questions, as well as general questions
        - For candidate/job data from the database, tell the user to ask "find candidates" or "find jobs"
        PROMPT;
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    private function detectIntent(string $query): ?array
    {
        foreach ($this->intents as $intent) {
            foreach ($intent['patterns'] as $pattern) {
                if (str_contains($query, $pattern)) {
                    return $intent;
                }
            }
        }
        return null;
    }

    private function extractSkillName(string $query): ?string
    {
        $stopWords = [
            'find', 'search', 'dhundo', 'chahiye', 'ke liye', 'for', 'with',
            'candidate', 'candidates', 'job', 'jobs', 'skill', 'wale', 'wala',
            'available', 'list', 'by', 'using', 'in', 'the', 'a', 'an','position',
        ];

        $words    = explode(' ', $query);
        $filtered = array_filter($words, fn($w) => !in_array($w, $stopWords) && strlen($w) > 1);

        return count($filtered) ? implode(' ', $filtered) : null;
    }

    private function markdownToHtml(string $text): string
    {
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
        $text = preg_replace('/^- (.+)$/m', '• $1', $text);
        return nl2br(trim($text));
    }

    private function respond(string $message, ?array $data): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data'    => $data,
        ]);
    }

    private function handleSaveResumeDraft(string $lower, string $original, $user): JsonResponse
    {
        try {

            $draft = session('resume_draft');

            if (!$draft) {
                return $this->respond(
                    '⚠️ No resume draft found. Please upload a resume first.',
                    null
                );
            }

            \Log::info('Resume Draft Save Started', [
                'draft' => $draft
            ]);

            $skills = $draft['skills'] ?? [];

            $result = $this->resumeImportService->importCandidate($draft);

            if ($result['status'] !== 'created') {
                \Log::error('Resume Draft Save Failed', [
                    'result' => $result,
                    'draft'  => $draft,
                ]);

                return $this->respond('⚠️ ' . $result['message'] . ' Please upload the resume again.', null);
            }

            $userModel = $result['user'];

            session()->forget('resume_draft');

            \Log::info('Resume Draft Saved Successfully', [
                'user_id' => $userModel->id
            ]);

            return $this->respond(
                "✅ Candidate added successfully!<br><br>" .
                "👤 Name: <strong>{$userModel->name}</strong><br>" .
                "📧 Email: <strong>{$userModel->email}</strong><br>" .
                "📱 Mobile: <strong>" . ($userModel->mobile_number ?: '—') . "</strong><br>" .
                "🏷️ Skills: <strong>" . (empty($skills) ? '—' : implode(', ', $skills)) . "</strong><br>" .
                "🆔 User ID: <strong>{$userModel->id}</strong>",
                null
            );

        } catch (\Throwable $e) {

            \Log::error('Resume Draft Save Failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'draft'   => session('resume_draft'),
            ]);

            return $this->respond(
                '❌ An error occurred while saving the candidate.<br>' .
                'Please check the logs.',
                null
            );
        }
    }
}
