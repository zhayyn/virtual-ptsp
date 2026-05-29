<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Virtual PTSP - AI Service Factory
 * Built with ❤️ by zhayyn (+6281317361689)
 *
 * Unified AI service that supports multiple providers:
 * - Google Gemini (default, free tier)
 * - Anthropic Claude
 * - OpenAI GPT
 */
class AiServiceFactory
{
    private ?string $provider = null;
    private ?string $model = null;
    private ?string $apiKey = null;
    private array $settings = [];

    /**
     * Supported providers
     */
    const PROVIDER_GEMINI = 'gemini';
    const PROVIDER_CLAUDE = 'claude';
    const PROVIDER_OPENAI = 'openai';

    /**
     * Set the AI provider
     */
    public function setProvider(string $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Set the model
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set API key
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Set additional settings
     */
    public function setSettings(array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * Send a chat completion request
     */
    public function chat(array $messages, ?string $systemPrompt = null): array
    {
        if (!$this->provider || !$this->apiKey) {
            throw new \Exception('AI provider and API key are required');
        }

        return match($this->provider) {
            self::PROVIDER_GEMINI => $this->chatGemini($messages, $systemPrompt),
            self::PROVIDER_CLAUDE => $this->chatClaude($messages, $systemPrompt),
            self::PROVIDER_OPENAI => $this->chatOpenAI($messages, $systemPrompt),
            default => throw new \Exception("Unsupported provider: {$this->provider}"),
        };
    }

    /**
     * Chat with Google Gemini
     */
    private function chatGemini(array $messages, ?string $systemPrompt = null): array
    {
        $model = $this->model ?? 'gemini-2.0-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        // Format messages for Gemini
        $contents = [];
        foreach ($messages as $msg) {
            if ($msg['role'] === 'user') {
                $contents[] = ['role' => 'user', 'parts' => [['text' => $msg['content']]]];
            } elseif ($msg['role'] === 'assistant') {
                $contents[] = ['role' => 'model', 'parts' => [['text' => $msg['content']]]];
            }
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $this->settings['temperature'] ?? 0.7,
                'maxOutputTokens' => $this->settings['max_tokens'] ?? 2048,
            ],
        ];

        if ($systemPrompt) {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemPrompt]]];
        }

        try {
            $response = Http::timeout(30)
                ->withQueryParameters(['key' => $this->apiKey])
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

                return [
                    'success' => true,
                    'content' => $text,
                    'model' => $model,
                    'usage' => [
                        'tokens' => $data['usageMetadata']['totalTokenCount'] ?? 0,
                    ],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];

        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Chat with Anthropic Claude
     */
    private function chatClaude(array $messages, ?string $systemPrompt = null): array
    {
        $model = $this->model ?? 'claude-sonnet-4-20250514';
        $url = 'https://api.anthropic.com/v1/messages';

        // Convert messages format
        $anthropicMessages = [];
        foreach ($messages as $msg) {
            if ($msg['role'] === 'user') {
                $anthropicMessages[] = ['role' => 'user', 'content' => $msg['content']];
            } elseif ($msg['role'] === 'assistant') {
                $anthropicMessages[] = ['role' => 'assistant', 'content' => $msg['content']];
            }
        }

        $payload = [
            'model' => $model,
            'messages' => $anthropicMessages,
            'max_tokens' => $this->settings['max_tokens'] ?? 2048,
            'temperature' => $this->settings['temperature'] ?? 0.7,
        ];

        if ($systemPrompt) {
            $payload['system'] = $systemPrompt;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'content' => $data['content'][0]['text'] ?? '',
                    'model' => $model,
                    'usage' => [
                        'tokens' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0),
                    ],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];

        } catch (\Exception $e) {
            Log::error('Claude API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Chat with OpenAI
     */
    private function chatOpenAI(array $messages, ?string $systemPrompt = null): array
    {
        $model = $this->model ?? 'gpt-4o-mini';
        $url = 'https://api.openai.com/v1/chat/completions';

        // Convert messages format
        $openaiMessages = [];
        if ($systemPrompt) {
            $openaiMessages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        foreach ($messages as $msg) {
            $openaiMessages[] = [
                'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content'],
            ];
        }

        $payload = [
            'model' => $model,
            'messages' => $openaiMessages,
            'max_tokens' => $this->settings['max_tokens'] ?? 2048,
            'temperature' => $this->settings['temperature'] ?? 0.7,
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'content-type' => 'application/json',
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'model' => $model,
                    'usage' => [
                        'tokens' => $data['usage']['total_tokens'] ?? 0,
                    ],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test the AI connection
     */
    public function test(): array
    {
        $result = $this->chat([
            ['role' => 'user', 'content' => 'Hello, respond with just "OK" if you can hear me.'],
        ]);

        return $result;
    }
}
