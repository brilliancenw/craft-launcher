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

    public function rules(): array
    {
        return [
            [['hotkey'], 'string'],
            [['hotkey'], 'required'],
            [['debounceDelay', 'maxResults', 'maxHistoryItems'], 'number', 'integerOnly' => true],
            [['debounceDelay'], 'default', 'value' => 300],
            [['maxResults'], 'default', 'value' => 10],
            [['maxHistoryItems'], 'default', 'value' => 10],
            [['searchableTypes', 'searchableEntryTypes', 'searchableSections', 'searchableCategoryGroups', 'searchableAssetVolumes'], 'safe'],
            [['searchDrafts', 'searchRevisions', 'searchDisabled', 'searchEntriesByAuthor', 'searchCommerceCustomers', 'searchCommerceProducts', 'searchCommerceOrders', 'enableLaunchHistory'], 'boolean'],
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
        ];
    }
}