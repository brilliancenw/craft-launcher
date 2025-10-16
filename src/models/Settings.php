<?php
namespace brilliance\launcher\models;

use craft\base\Model;

class Settings extends Model
{
    public string $hotkey = 'cmd+k';
    public int $debounceDelay = 300;
    public int $maxResults = 10;
    public array $searchableTypes = [
        'entries' => true,
        'categories' => true,
        'assets' => true,
        'users' => true,
        'globals' => true,
        'sections' => true,
        'entryTypes' => true,
        'categoryGroups' => true,
        'assetVolumes' => true,
        'fields' => true,
        'fieldGroups' => true,
        'userGroups' => true,
        'plugins' => true,
        'routes' => true,
        'utilities' => true,
        'settings' => true,
    ];
    
    public array $searchableEntryTypes = [];
    public array $searchableSections = [];
    public array $searchableCategoryGroups = [];
    public array $searchableAssetVolumes = [];
    
    public bool $searchDrafts = false;
    public bool $searchRevisions = false;
    public bool $searchDisabled = false;
    public bool $searchEntriesByAuthor = true;
    
    // Commerce settings (only used if Commerce is installed)
    public bool $searchCommerceCustomers = true;
    public bool $searchCommerceProducts = true;
    public bool $searchCommerceOrders = true;
    
    // Launch history settings
    public bool $enableLaunchHistory = true;
    public int $maxHistoryItems = 10;

    // Integration settings
    public array $enabledIntegrations = [];

    // AI Assistant settings
    public bool $enableAIAssistant = false;
    public string $aiHotkey = 'cmd+j';
    public string $aiProvider = 'claude';
    public string $claudeApiKey = '';
    public string $openaiApiKey = '';
    public string $geminiApiKey = '';
    public int $maxAIConversationHistory = 50;
    public bool $enableAIStreaming = true;

    // Website/Brand information
    public string $websiteName = '';
    public string $brandOwner = '';
    public string $brandTagline = '';
    public string $brandDescription = '';
    public string $brandVoice = '';
    public string $targetAudience = '';
    public array $brandColors = [];
    public string $brandLogoUrl = '';

    // Content Guidelines
    public string $contentGuidelines = '';
    public string $contentTone = '';
    public string $writingStyle = '';
    public string $seoGuidelines = '';
    public array $customGuidelines = [];

    // Result navigation shortcuts
    public string $selectResultModifier = 'cmd';
    public array $resultShortcuts = [
        'first' => 'return',
        1 => 'cmd+1',
        2 => 'cmd+2', 
        3 => 'cmd+3',
        4 => 'cmd+4',
        5 => 'cmd+5',
        6 => 'cmd+6',
        7 => 'cmd+7',
        8 => 'cmd+8',
        9 => 'cmd+9',
    ];

    public function rules(): array
    {
        return [
            [['hotkey', 'selectResultModifier', 'aiHotkey', 'aiProvider'], 'string'],
            [['hotkey'], 'required'],
            [['debounceDelay', 'maxResults', 'maxHistoryItems', 'maxAIConversationHistory'], 'number', 'integerOnly' => true],
            [['debounceDelay'], 'default', 'value' => 300],
            [['maxResults'], 'default', 'value' => 10],
            [['maxHistoryItems'], 'default', 'value' => 10],
            [['maxAIConversationHistory'], 'default', 'value' => 50],
            [['selectResultModifier'], 'default', 'value' => 'cmd'],
            [['aiHotkey'], 'default', 'value' => 'cmd+j'],
            [['aiProvider'], 'default', 'value' => 'claude'],
            [['aiProvider'], 'in', 'range' => ['claude', 'openai', 'gemini']],
            [['claudeApiKey', 'openaiApiKey', 'geminiApiKey'], 'string'],
            [['websiteName', 'brandOwner', 'brandTagline', 'brandDescription', 'brandVoice', 'targetAudience', 'brandLogoUrl'], 'string'],
            [['contentGuidelines', 'contentTone', 'writingStyle', 'seoGuidelines'], 'string'],
            [['searchableTypes', 'searchableEntryTypes', 'searchableSections', 'searchableCategoryGroups', 'searchableAssetVolumes', 'resultShortcuts', 'enabledIntegrations', 'brandColors', 'customGuidelines'], 'safe'],
            [['searchDrafts', 'searchRevisions', 'searchDisabled', 'searchEntriesByAuthor', 'searchCommerceCustomers', 'searchCommerceProducts', 'searchCommerceOrders', 'enableLaunchHistory', 'enableAIAssistant', 'enableAIStreaming'], 'boolean'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'hotkey' => 'Hotkey Combination',
            'debounceDelay' => 'Search Delay (milliseconds)',
            'maxResults' => 'Maximum Results Per Type',
            'searchableTypes' => 'Searchable Content Types',
            'searchableEntryTypes' => 'Entry Types to Search',
            'searchableSections' => 'Sections to Search',
            'searchableCategoryGroups' => 'Category Groups to Search',
            'searchableAssetVolumes' => 'Asset Volumes to Search',
            'searchDrafts' => 'Include Drafts in Search',
            'searchRevisions' => 'Include Revisions in Search',
            'searchDisabled' => 'Include Disabled Elements in Search',
            'searchEntriesByAuthor' => 'Search Entries by Author Name',
            'searchCommerceCustomers' => 'Search Commerce Customers',
            'searchCommerceProducts' => 'Search Commerce Products and Variants',
            'searchCommerceOrders' => 'Search Commerce Orders',
            'enableLaunchHistory' => 'Track Launch History',
            'maxHistoryItems' => 'Max Popular Items to Show',
            'selectResultModifier' => 'Result Selection Modifier Key',
            'resultShortcuts' => 'Result Navigation Shortcuts',
            'enabledIntegrations' => 'Enabled Integrations',

            // AI Assistant
            'enableAIAssistant' => 'Enable AI Assistant',
            'aiHotkey' => 'AI Assistant Hotkey',
            'aiProvider' => 'AI Provider',
            'claudeApiKey' => 'Claude API Key',
            'openaiApiKey' => 'OpenAI API Key',
            'geminiApiKey' => 'Gemini API Key',
            'maxAIConversationHistory' => 'Max Messages Per Conversation',
            'enableAIStreaming' => 'Enable Streaming Responses',

            // Brand Information
            'websiteName' => 'Website Name',
            'brandOwner' => 'Brand Owner',
            'brandTagline' => 'Brand Tagline',
            'brandDescription' => 'Brand Description',
            'brandVoice' => 'Brand Voice',
            'targetAudience' => 'Target Audience',
            'brandColors' => 'Brand Colors',
            'brandLogoUrl' => 'Brand Logo URL',

            // Content Guidelines
            'contentGuidelines' => 'General Content Guidelines',
            'contentTone' => 'Content Tone',
            'writingStyle' => 'Writing Style',
            'seoGuidelines' => 'SEO Guidelines',
            'customGuidelines' => 'Custom Guidelines',
        ];
    }
}