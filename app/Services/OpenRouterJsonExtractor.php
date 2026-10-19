<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Shared helper: send a system+user prompt to OpenRouter and parse the
 * JSON object out of the reply. Used by resume extraction and job
 * description requirement extraction so the HTTP/parsing boilerplate
 * only lives in one place.
 */
class OpenRouterJsonExtractor
{
    public function extractJson(string $systemPrompt, string $userPrompt, int $maxTokens = 1000): ?array
    {
        $apiKey = config('services.openrouter.key');

        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model'       => 'nex-agi/nex-n2-pro:free',
                    'temperature' => 0,
                    'max_tokens'  => $maxTokens,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('OpenRouter Request Failed', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);

                return null;
            }

            $content = data_get($response->json(), 'choices.0.message.content');

            if (empty($content)) {
                Log::error('No content found in AI response', ['response' => $response->json()]);
                return null;
            }

            $clean = trim(preg_replace('/```json|```/i', '', $content));
            preg_match('/\{.*\}/s', $clean, $matches);

            if (!isset($matches[0])) {
                Log::error('No JSON object found in AI response', ['content' => $clean]);
                return null;
            }

            $data = json_decode(trim($matches[0]), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON Decode Failed', ['error' => json_last_error_msg(), 'json' => $matches[0]]);
                return null;
            }

            return $data;
        } catch (\Throwable $e) {
            Log::error('OpenRouter JSON Extraction Exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return null;
        }
    }
}
