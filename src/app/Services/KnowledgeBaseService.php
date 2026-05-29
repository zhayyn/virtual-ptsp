<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use App\Models\KnowledgeItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Virtual PTSP - Knowledge Base Service with RAG
 * Built with ❤️ by zhayyn (+6281317361689)
 *
 * Handles:
 * - Text content
 * - File uploads (PDF, TXT, DOCX)
 * - Web scraping
 * - Simple embedding generation
 */
class KnowledgeBaseService
{
    private AiServiceFactory $aiService;

    public function __construct(AiServiceFactory $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Add text content to knowledge base
     */
    public function addText(KnowledgeBase $kb, string $title, string $content, array $metadata = []): KnowledgeItem
    {
        $item = KnowledgeItem::create([
            'knowledge_base_id' => $kb->id,
            'type' => 'text',
            'title' => $title,
            'content' => $content,
            'metadata' => $metadata,
            'is_processed' => true, // Text is immediately available
            'processed_at' => now(),
        ]);

        return $item;
    }

    /**
     * Add file to knowledge base
     */
    public function addFile(KnowledgeBase $kb, $file, array $metadata = []): KnowledgeItem
    {
        // Store file
        $path = $file->store('knowledge', 'public');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Extract content based on type
        $content = $this->extractFileContent($file, $mimeType);

        $item = KnowledgeItem::create([
            'knowledge_base_id' => $kb->id,
            'type' => 'file',
            'title' => $originalName,
            'content' => $content,
            'file_path' => $path,
            'file_name' => $originalName,
            'file_size' => $size,
            'mime_type' => $mimeType,
            'metadata' => $metadata,
            'is_processed' => true,
            'processed_at' => now(),
        ]);

        return $item;
    }

    /**
     * Add URL (web scraping)
     */
    public function addUrl(KnowledgeBase $kb, string $url, array $metadata = []): KnowledgeItem
    {
        $content = $this->scrapeUrl($url);

        $item = KnowledgeItem::create([
            'knowledge_base_id' => $kb->id,
            'type' => 'url',
            'title' => $url,
            'url_source' => $url,
            'url_content' => $content,
            'content' => $content,
            'metadata' => array_merge($metadata, ['scraped_at' => now()->toIso8601String()]),
            'is_processed' => true,
            'processed_at' => now(),
        ]);

        return $item;
    }

    /**
     * Extract content from uploaded file
     */
    private function extractFileContent($file, string $mimeType): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $content = file_get_contents($file->getRealPath());

        // For text files, return as-is
        if (in_array($extension, ['txt', 'md', 'csv'])) {
            return $content;
        }

        // For JSON files
        if ($extension === 'json') {
            $decoded = json_decode($content, true);
            return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        // For other formats, try to extract text
        // Note: In production, use libraries like PhpSpreadsheet, Smalot/PdfParser
        return "File uploaded: {$file->getClientOriginalName()}\n" .
               "Type: {$mimeType}\n" .
               "Size: " . number_format($file->getSize() / 1024, 2) . " KB\n\n" .
               "Content preview (first 2000 chars):\n" .
               substr($content, 0, 2000);
    }

    /**
     * Scrape content from URL
     */
    private function scrapeUrl(string $url): string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Virtual PTSP Knowledge Base Scraper/1.0',
                ])
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();

                // Simple HTML to text extraction
                // In production, use a proper HTML parser like symfony/dom-crawler
                $text = strip_tags($html);

                // Clean up whitespace
                $text = preg_replace('/\s+/', ' ', $text);
                $text = trim($text);

                // Limit to first 10000 chars
                return substr($text, 0, 10000);
            }

            return "Failed to fetch URL: HTTP " . $response->status();

        } catch (\Exception $e) {
            Log::error('URL scraping error: ' . $e->getMessage());
            return "Failed to fetch URL: " . $e->getMessage();
        }
    }

    /**
     * Search knowledge base using RAG
     */
    public function search(KnowledgeBase $kb, string $query, int $limit = 5): array
    {
        // Get all processed items
        $items = KnowledgeItem::where('knowledge_base_id', $kb->id)
            ->where('is_processed', true)
            ->get();

        if ($items->isEmpty()) {
            return [
                'query' => $query,
                'results' => [],
                'context' => '',
            ];
        }

        // Simple relevance scoring (in production, use vector embeddings)
        $scoredItems = $items->map(function ($item) use ($query) {
            $score = $this->calculateRelevance($item->content ?? '', $query);
            return [
                'item' => $item,
                'score' => $score,
            ];
        })->filter(function ($scored) {
            return $scored['score'] > 0.1; // Minimum relevance threshold
        })->sortByDesc('score')->take($limit);

        // Build context from top results
        $contextParts = [];
        foreach ($scoredItems as $scored) {
            $contextParts[] = "--- [Source: {$scored['item']->title}] ---\n" .
                              substr($scored['item']->content ?? '', 0, 2000);
        }

        return [
            'query' => $query,
            'results' => $scoredItems->values()->toArray(),
            'context' => implode("\n\n", $contextParts),
        ];
    }

    /**
     * Simple relevance scoring (TF-IDF-like)
     */
    private function calculateRelevance(string $content, string $query): float
    {
        $content = strtolower($content);
        $queryTerms = array_filter(explode(' ', strtolower($query)));

        if (empty($queryTerms)) {
            return 0;
        }

        $matches = 0;
        foreach ($queryTerms as $term) {
            if (strlen($term) > 2 && str_contains($content, $term)) {
                $matches++;
            }
        }

        return $matches / count($queryTerms);
    }

    /**
     * Generate context-aware response using RAG
     */
    public function generateWithRag(KnowledgeBase $kb, string $question, AiServiceFactory $aiService): array
    {
        // Step 1: Search knowledge base
        $searchResult = $this->search($kb, $question);

        // Step 2: If no relevant results, try direct AI response
        if (empty($searchResult['context'])) {
            return [
                'success' => false,
                'answer' => 'Maaf, saya tidak menemukan informasi yang relevan di knowledge base.',
                'sources' => [],
                'rag_used' => false,
            ];
        }

        // Step 3: Build system prompt with context
        $systemPrompt = "Anda adalah asisten yang membantu menjawab pertanyaan berdasarkan knowledge base yang diberikan.
Gunakan informasi dari context di bawah untuk menjawab pertanyaan. Jika informasi tidak tersedia di context, katakan bahwa Anda tidak tahu.

FORMAT JAWABAN:
1. Langsung jawab pertanyaan
2. Cantumkan sumber jika memungkinkan

--- KNOWLEDGE BASE CONTEXT ---
{$searchResult['context']}
--- END CONTEXT ---";

        // Step 4: Generate response
        $result = $aiService->chat([
            ['role' => 'user', 'content' => $question],
        ], $systemPrompt);

        return [
            'success' => $result['success'],
            'answer' => $result['content'] ?? $result['error'] ?? 'Gagal generate response',
            'sources' => collect($searchResult['results'])->map(fn($r) => $r['item']->title)->toArray(),
            'rag_used' => true,
            'relevant_sources_count' => count($searchResult['results']),
        ];
    }

    /**
     * Delete a knowledge item
     */
    public function deleteItem(KnowledgeItem $item): bool
    {
        // Delete associated file if exists
        if ($item->file_path) {
            Storage::disk('public')->delete($item->file_path);
        }

        return $item->delete();
    }
}