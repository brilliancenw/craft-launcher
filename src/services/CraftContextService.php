<?php

namespace brilliance\launcher\services;

use brilliance\launcher\Launcher;
use Craft;
use craft\base\Component;
use craft\helpers\App;

/**
 * Craft Context Service
 *
 * Builds contextual information about the Craft CMS installation
 * to provide to AI agents as initial context.
 */
class CraftContextService extends Component
{
    /**
     * Build the complete context for AI agents
     * This is sent with every conversation to provide baseline knowledge
     */
    public function buildContext(): array
    {
        $settings = Launcher::$plugin->getSettings();

        return [
            'website' => $this->getWebsiteInfo(),
            'brand' => $this->getBrandInfo(),
            'craft' => $this->getCraftInfo(),
            'capabilities' => $this->getCapabilities(),
            'contentOverview' => $this->getContentOverview(),
            'guidelines' => $this->getContentGuidelines(),
        ];
    }

    /**
     * Get website information
     */
    private function getWebsiteInfo(): array
    {
        $settings = Launcher::$plugin->getSettings();

        return [
            'name' => $settings->websiteName ?? Craft::$app->systemName,
            'url' => Craft::$app->sites->primarySite->baseUrl ?? '',
            'environment' => App::env('CRAFT_ENVIRONMENT') ?? 'production',
            'timezone' => Craft::$app->getTimeZone(),
        ];
    }

    /**
     * Get brand information from environment-specific settings
     */
    private function getBrandInfo(): array
    {
        $brandInfo = Launcher::$plugin->aiSettingsService->getBrandInfo();

        return [
            'owner' => $brandInfo['brandOwner'] ?? '',
            'voice' => $brandInfo['brandVoice'] ?? '',
            'tagline' => $brandInfo['brandTagline'] ?? '',
            'description' => $brandInfo['brandDescription'] ?? '',
            'targetAudience' => $brandInfo['targetAudience'] ?? '',
            'colors' => $brandInfo['brandColors'] ?? [],
            'logoUrl' => $brandInfo['brandLogoUrl'] ?? '',
        ];
    }

    /**
     * Get Craft CMS configuration info
     */
    private function getCraftInfo(): array
    {
        return [
            'version' => Craft::$app->version,
            'edition' => Craft::$app->getEditionName(),
            'isMultiSite' => Craft::$app->getIsMultiSite(),
            'language' => Craft::$app->language,
        ];
    }

    /**
     * Get available capabilities
     */
    private function getCapabilities(): array
    {
        $capabilities = [
            'createEntries' => true,
            'searchContent' => true,
            'accessFields' => true,
            'commerce' => Craft::$app->plugins->isPluginInstalled('commerce'),
        ];

        return $capabilities;
    }

    /**
     * Get content overview (sections summary)
     */
    private function getContentOverview(): array
    {
        $sections = Craft::$app->getEntries()->getAllSections();
        $overview = [];

        foreach ($sections as $section) {
            $overview[] = [
                'name' => $section->name,
                'handle' => $section->handle,
                'type' => $section->type,
                'description' => "Use getSectionDetails('{$section->handle}') for field information",
            ];
        }

        return [
            'sections' => $overview,
            'totalSections' => count($overview),
            'note' => 'This is a summary. Use the getSectionDetails tool to get detailed field information for a specific section.',
        ];
    }

    /**
     * Get content guidelines from environment-specific settings
     */
    private function getContentGuidelines(): array
    {
        $guidelines = Launcher::$plugin->aiSettingsService->getContentGuidelines();

        return [
            'general' => $guidelines['contentGuidelines'] ?? '',
            'tone' => $guidelines['contentTone'] ?? '',
            'writingStyle' => $guidelines['writingStyle'] ?? '',
            'seoGuidelines' => $guidelines['seoGuidelines'] ?? '',
            'customGuidelines' => $guidelines['customGuidelines'] ?? [],
        ];
    }

    /**
     * Build system prompt for AI
     * This is the instruction that tells the AI how to behave
     */
    public function buildSystemPrompt(): string
    {
        $context = $this->buildContext();
        $websiteName = $context['website']['name'] ?? 'this website';

        return <<<PROMPT
You are an AI assistant integrated into the Craft CMS admin panel for {$websiteName}. Your role is to help content creators and administrators work more efficiently with Craft CMS.

# Your Capabilities

You have access to several tools that let you interact with Craft CMS:
- Search for existing content
- Get detailed information about content sections and fields
- Create draft entries that users can review and publish
- Access brand guidelines and content requirements

# Response Formatting

Format your responses using HTML for better readability. Use these tags:
- <strong> for important terms and emphasis
- <h2>, <h3> for headings to organize information
- <ul><li> for bullet lists
- <ol><li> for numbered/sequential lists
- <a href="url"> for links to Craft admin URLs
- <code> for field handles and technical terms
- <p> for paragraphs

Examples:
"<p>I found <strong>3 sections</strong> in your site:</p>
<ul>
<li>Section Alpha</li>
<li>Blog Posts</li>
<li>News Articles</li>
</ul>"

"<p>To create an entry, you'll need these required fields:</p>
<ol>
<li><strong>title</strong> - The entry title</li>
<li><strong>bodyContent</strong> - Main content area</li>
</ol>"

# Brand Voice and Guidelines

{$this->formatBrandInfo($context['brand'])}

# Content Guidelines

{$this->formatGuidelines($context['guidelines'])}

# Available Content Types

{$this->formatContentOverview($context['contentOverview'])}

# How to Work

1. When asked to create content, first understand the requirements by checking section details
2. Reference existing similar content when helpful
3. Always follow the brand voice and content guidelines
4. Create drafts - never publish directly. The user will review your work
5. Be conversational and helpful, but stay focused on the task
6. Format responses with HTML for clarity and readability

# Important Notes

- You are working in {$context['craft']['edition']} Edition of Craft CMS {$context['craft']['version']}
- All content you create will be saved as drafts for review
- When you need detailed field information, use the getSectionDetails tool
- Don't assume field names - always check the section structure first

Ready to help!
PROMPT;
    }

    /**
     * Format brand info for system prompt
     */
    private function formatBrandInfo(array $brand): string
    {
        $parts = [];

        if (!empty($brand['owner'])) {
            $parts[] = "Owner: {$brand['owner']}";
        }

        if (!empty($brand['voice'])) {
            $parts[] = "Brand Voice: {$brand['voice']}";
        }

        if (!empty($brand['tagline'])) {
            $parts[] = "Tagline: {$brand['tagline']}";
        }

        if (!empty($brand['targetAudience'])) {
            $parts[] = "Target Audience: {$brand['targetAudience']}";
        }

        return !empty($parts) ? implode("\n", $parts) : "No specific brand guidelines configured.";
    }

    /**
     * Format guidelines for system prompt
     */
    private function formatGuidelines(array $guidelines): string
    {
        $parts = [];

        if (!empty($guidelines['general'])) {
            $parts[] = $guidelines['general'];
        }

        if (!empty($guidelines['tone'])) {
            $parts[] = "Tone: {$guidelines['tone']}";
        }

        if (!empty($guidelines['writingStyle'])) {
            $parts[] = "Writing Style: {$guidelines['writingStyle']}";
        }

        return !empty($parts) ? implode("\n", $parts) : "Follow general best practices for web content.";
    }

    /**
     * Format content overview for system prompt
     */
    private function formatContentOverview(array $overview): string
    {
        if (empty($overview['sections'])) {
            return "No content sections configured yet.";
        }

        $lines = [];
        foreach ($overview['sections'] as $section) {
            $lines[] = "- {$section['name']} ({$section['handle']}) - {$section['type']}";
        }

        return implode("\n", $lines) . "\n\nNote: {$overview['note']}";
    }
}
