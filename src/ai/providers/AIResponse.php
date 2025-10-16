<?php

namespace brilliance\launcher\ai\providers;

/**
 * AI Response
 *
 * Standardized response object from AI providers
 */
class AIResponse
{
    public string $content;
    public array $toolCalls;
    public string $role;
    public array $metadata;
    public bool $stopReason;
    public ?string $error;

    public function __construct(
        string $content = '',
        array $toolCalls = [],
        string $role = 'assistant',
        array $metadata = [],
        bool $stopReason = false,
        ?string $error = null
    ) {
        $this->content = $content;
        $this->toolCalls = $toolCalls;
        $this->role = $role;
        $this->metadata = $metadata;
        $this->stopReason = $stopReason;
        $this->error = $error;
    }

    /**
     * Check if the response contains tool calls
     */
    public function hasToolCalls(): bool
    {
        return !empty($this->toolCalls);
    }

    /**
     * Check if there was an error
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Convert to array for storage
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'toolCalls' => $this->toolCalls,
            'role' => $this->role,
            'metadata' => $this->metadata,
            'stopReason' => $this->stopReason,
            'error' => $this->error,
        ];
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['content'] ?? '',
            $data['toolCalls'] ?? [],
            $data['role'] ?? 'assistant',
            $data['metadata'] ?? [],
            $data['stopReason'] ?? false,
            $data['error'] ?? null
        );
    }
}
