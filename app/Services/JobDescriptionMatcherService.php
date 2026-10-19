<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns a pasted job description into structured requirements (via AI, best
 * effort) and ranks existing candidates against the JD by overlap across
 * skills, job title, position, experience type, and profile summary.
 */
class JobDescriptionMatcherService
{
    public function __construct(private OpenRouterJsonExtractor $ai)
    {
    }

    /**
     * @return array{role_title: ?string, required_skills: array, preferred_skills: array, min_experience_years: mixed, max_experience_years: mixed}|null
     */
    public function extractRequirements(string $text): ?array
    {
        $data = $this->ai->extractJson(
            'You are a job description parser. Return ONLY valid JSON. No markdown. No explanation.',
            "Extract structured requirements from the job description below.\n\n" .
                "Return ONLY valid JSON in this format:\n" .
                "{\n" .
                '  "role_title":"",' . "\n" .
                '  "required_skills":[""],' . "\n" .
                '  "preferred_skills":[""],' . "\n" .
                '  "min_experience_years":null,' . "\n" .
                '  "max_experience_years":null' . "\n" .
                "}\n\n" .
                "Rules:\n" .
                "- required_skills = skills explicitly called mandatory/required/must-have, or clearly core technologies of the role.\n" .
                "- preferred_skills = skills called nice-to-have/preferred/bonus/good-to-have.\n" .
                "- Keep each skill short (e.g. \"Apache Pinot\", \"Kafka\", \"PostgreSQL\"), not full sentences.\n" .
                "- Do not duplicate a skill in both lists.\n\n" .
                "Job Description:\n" .
                Str::limit($text, 6000),
            800
        );

        if (!$data) {
            return null;
        }

        return [
            'role_title'            => $data['role_title'] ?? null,
            'required_skills'       => $this->cleanSkillList($data['required_skills'] ?? []),
            'preferred_skills'      => $this->cleanSkillList($data['preferred_skills'] ?? []),
            'min_experience_years'  => $data['min_experience_years'] ?? null,
            'max_experience_years'  => $data['max_experience_years'] ?? null,
        ];
    }

    /**
     * Rank candidates against a job description by overlap across skills,
     * job title, position, experience type, and profile summary — not just
     * AI-extracted skills. This works even when AI extraction fails/is
     * unavailable: the "vocabulary" of matchable terms comes straight from
     * what's already recorded in the database (skill names, job titles,
     * positions), checked for whether they actually appear in the JD text.
     * AI-extracted skills (if available) are merged in as extra vocabulary.
     *
     * Returns a collection of plain arrays, highest match first.
     */
    public function matchCandidates(string $jdText, array $aiRequiredSkills = [], array $aiPreferredSkills = [], int $limit = 20): Collection
    {
        $jdLower = mb_strtolower($jdText);

        $dbSkillNames = collect(DB::table('skills')->pluck('name'));
        $dbJobTitles  = collect(DB::table('employments')->pluck('job_title'));
        $dbPositions  = collect(DB::table('users')->whereNotNull('position')->pluck('position'));
        $aiSkills     = collect(array_merge($aiRequiredSkills, $aiPreferredSkills));

        $vocabulary = $this->cleanSkillList(
            $dbSkillNames->merge($dbJobTitles)->merge($dbPositions)->merge($aiSkills)->all()
        );

        $matchedTerms = collect($vocabulary)->filter(fn ($term) => $this->termFoundInText($term, $jdLower))->values();

        if ($matchedTerms->isEmpty()) {
            return collect();
        }

        $candidates = DB::table('users as u')
            ->join('candidates as c', 'c.user_id', '=', 'u.id')
            ->leftJoin('skillables as sa', function ($join) {
                $join->on('sa.skillable_id', '=', 'u.id')
                    ->where('sa.skillable_type', '=', User::class);
            })
            ->leftJoin('skills as sk', 'sk.id', '=', 'sa.skill_id')
            ->leftJoin('employments as e', 'e.user_id', '=', 'u.id')
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'u.mobile_number',
                'u.position',
                'u.experience_type',
                'c.summary',
                DB::raw("GROUP_CONCAT(DISTINCT sk.name SEPARATOR '||') as skills"),
                DB::raw("GROUP_CONCAT(DISTINCT e.job_title SEPARATOR '||') as job_titles")
            )
            ->groupBy('u.id', 'u.name', 'u.email', 'u.mobile_number', 'u.position', 'u.experience_type', 'c.summary')
            ->get();

        $results = [];

        foreach ($candidates as $candidate) {
            $profileText = mb_strtolower(implode(' ', array_filter([
                str_replace('||', ' ', $candidate->skills ?? ''),
                str_replace('||', ' ', $candidate->job_titles ?? ''),
                $candidate->position,
                $candidate->experience_type,
                $candidate->summary,
            ])));

            if ($profileText === '') {
                continue;
            }

            $hits = $matchedTerms->filter(fn ($term) => $this->termFoundInText($term, $profileText));

            if ($hits->isEmpty()) {
                continue;
            }

            $results[] = [
                'name'            => $candidate->name,
                'email'           => $candidate->email,
                'position'        => $candidate->position,
                'experience_type' => $candidate->experience_type,
                'contact'         => $candidate->mobile_number ?: $candidate->email,
                'match_percent'   => (int) round(min(100, ($hits->count() / $matchedTerms->count()) * 100)),
                'matched_skills'  => implode(', ', $hits->map('ucfirst')->all()),
                'sort_weight'     => $hits->count(),
            ];
        }

        return collect($results)
            ->sortByDesc('sort_weight')
            ->values()
            ->take($limit);
    }

    /**
     * Whether $term appears in $text. Substring containment is only allowed
     * for longer tokens (>=4 chars) to avoid false positives like "Go"
     * matching inside "Django".
     */
    private function termFoundInText(string $term, string $text): bool
    {
        if ($term === '') {
            return false;
        }

        if (mb_strlen($term) < 4) {
            return preg_match('/\b' . preg_quote($term, '/') . '\b/u', $text) === 1;
        }

        return str_contains($text, $term);
    }

    private function cleanSkillList(array $skills): array
    {
        return collect($skills)
            ->map(fn ($s) => strtolower(trim((string) $s)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
