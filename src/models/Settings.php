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

    public function rules(): array
    {
        return [
            [['hotkey'], 'string'],
            [['hotkey'], 'required'],
            [['debounceDelay', 'maxResults'], 'number', 'integerOnly' => true],
            [['debounceDelay'], 'default', 'value' => 300],
            [['maxResults'], 'default', 'value' => 10],
            [['searchableTypes', 'searchableEntryTypes', 'searchableSections', 'searchableCategoryGroups', 'searchableAssetVolumes'], 'safe'],
            [['searchDrafts', 'searchRevisions', 'searchDisabled'], 'boolean'],
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
        ];
    }
}