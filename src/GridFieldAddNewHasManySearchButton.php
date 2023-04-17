<?php

namespace Fromholdio\GridFieldAddNewHasMany;

use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;

class GridFieldAddNewHasManySearchButton implements GridField_HTMLProvider, GridField_URLHandler
{
    protected string $buttonClass;
    protected ?string $buttonName;
    protected ?array $extraData;
    protected bool $doAllowDuplicate;
    protected string $fragment;
    protected string $joinKey;
    protected string $title;
    protected string $resultFormat;
    protected array $searchFields;
    protected SS_List $searchList;
    protected ?string $modelClass;
    protected ?array $gridFieldReloadList;
    protected bool $redirectToEdit = false;

    private static $allowed_actions = [
        'handleSearch'
    ];

    public function __construct(
        string $joinKey,
        SS_List $searchList,
        array $searchFields = ['Title'],
        string $resultFormat = 'Title',
        ?array $extraData = null,
        bool $doAllowDuplicate = false,
        $fragment = 'buttons-before-left',
        string $buttonClass = 'btn-outline-primary',
        string $buttonName = null
    ) {
        $this->setButtonClass($buttonClass);
        $this->setButtonName($buttonName);
        $this->setExtraData($extraData);
        $this->setDoAllowDuplicate($doAllowDuplicate);
        $this->setFragment($fragment);
        $this->setJoinKey($joinKey);
        $this->setResultFormat($resultFormat);
        $this->setSearchFields($searchFields);
        $this->setSearchList($searchList);
        $this->setTitle(_t('GridFieldExtensions.ADDEXISTING', 'Add Existing'));
        $this->setModelClass(null);
        $this->setGridFieldReloadList(null);
    }

    public function setButtonName(?string $buttonName): self
    {
        $this->buttonName = $buttonName;
        return $this;
    }

    public function getButtonName(): ?string
    {
        return $this->buttonName;
    }

    public function setExtraData(?array $extraData): self
    {
        $this->extraData = $extraData;
        return $this;
    }

    public function getExtraData(): ?array
    {
        return $this->extraData;
    }

    public function setGridFieldReloadList(?array $list): self
    {
        $this->gridFieldReloadList = $list;
        return $this;
    }

    public function getGridFieldReloadList(): ?array
    {
        return $this->gridFieldReloadList;
    }

    public function getGridFieldReloadAttribute(): ?string
    {
        $attr = null;
        $list = $this->getGridFieldReloadList();
        if (!empty($list)) {
            $attrData = array_values($list);
            $attr = json_encode($attrData, JSON_FORCE_OBJECT);
        }
        return $attr;
    }

    public function setRedirectToEdit(bool $value): self
    {
        $this->redirectToEdit = $value;
        return $this;
    }

    public function getRedirectToEdit(): bool
    {
        return $this->redirectToEdit;
    }

    public function setDoAllowDuplicate(bool $doAllowDuplicate): self
    {
        $this->doAllowDuplicate = $doAllowDuplicate;
        return $this;
    }

    public function getDoAllowDuplicate(): bool
    {
        return $this->doAllowDuplicate;
    }

    public function setFragment(string $fragment): self
    {
        $this->fragment = $fragment;
        return $this;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function setJoinKey(string $joinKey): self
    {
        $this->joinKey = $joinKey;
        return $this;
    }

    public function getJoinKey(): string
    {
        return $this->joinKey;
    }

    public function setResultFormat(string $resultFormat): self
    {
        $this->resultFormat = $resultFormat;
        return $this;
    }

    public function getResultFormat(): string
    {
        return $this->resultFormat;
    }

    public function setSearchFields(array $fields): self
    {
        $this->searchFields = $fields;
        return $this;
    }

    public function getSearchFields()
    {
        return $this->searchFields;
    }

    public function setSearchList(SS_List $searchList): self
    {
        $this->searchList = $searchList;
        return $this;
    }

    public function getSearchList(): SS_List
    {
        return $this->searchList;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setModelClass(?string $class = null): self
    {
        $this->modelClass = $class;
        return $this;
    }

    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    public function setButtonClass(string $class) :self
    {
        $this->buttonClass = $class;
        return $this;
    }

    public function getButtonClass() :string
    {
        return $this->buttonClass;
    }

    public function getHTMLFragments($gridField)
    {
        Requirements::css('fromholdio/silverstripe-gridfield-addnew-hasmany: client/css/gridfieldaddnewhasmanysearch.css');
        Requirements::javascript('fromholdio/silverstripe-gridfield-addnew-hasmany: client/js/gridfieldaddnewhasmanysearch.js');

        $data = ArrayData::create([
            'Title' => $this->getTitle(),
            'Classes' => 'action btn font-icon-search add-new-hasmany-search ' . $this->getButtonClass(),
            'Link' => $gridField->Link($this->getURLSegment()),
            'GridFieldReloadAttribute' => $this->getGridFieldReloadAttribute()
        ]);
        return [$this->fragment => $data->renderWith(__CLASS__)];
    }

    public function getURLHandlers($gridField)
    {
        return [$this->getURLSegment() => 'handleSearch'];
    }

    public function handleSearch($gridField, $request)
    {
        return new GridFieldAddNewHasManySearchHandler($gridField, $this);
    }

    public function getURLSegment()
    {
        $urlSegment = 'add-new-hasmany-search';
        $buttonName = $this->getButtonName();
        if (!is_null($buttonName)) {
            $urlSegment .= '-' . $buttonName;
        }
        return $urlSegment;
    }
}
