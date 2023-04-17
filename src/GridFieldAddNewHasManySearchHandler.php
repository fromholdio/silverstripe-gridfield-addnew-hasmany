<?php

namespace Fromholdio\GridFieldAddNewHasMany;

use SilverStripe\Control\Controller;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\View\ArrayData;

class GridFieldAddNewHasManySearchHandler extends RequestHandler
{
    protected $button;
    protected $gridField;
    protected $items;

    private static $allowed_actions = [
        'index',
        'add',
        'edit',
        'SearchForm'
    ];

    public function __construct(GridField $gridField, GridFieldAddNewHasManySearchButton $button)
    {
        $this->setGridField($gridField);
        $this->setButton($button);
        parent::__construct();
    }

    public function index()
    {
        return $this->renderWith(__CLASS__);
    }

    public function add($request)
    {
        $id = $request->postVar('id');
        if (!$id) {
            $this->httpError(400);
        }

        $searchList = $this->getSearchList();
        $searchItem = $searchList->find('ID', $id);
        if (!$searchItem || !$searchItem->exists()) {
            $this->httpError(400);
        }

        $gridList = $this->getGridList();
        $joinKey = $this->getJoinKey();
        $gridDataClass = $this->getGridModelClass();
        $extraData = $this->getButton()->getExtraData();

        if (isset($extraData['ClassName'])) {
            $extraClass = $extraData['ClassName'];
            if (!is_a($extraClass, $gridDataClass, true)) {
                throw new \LogicException(
                    'Your extra data is setting a class "' . $extraClass .'" '
                    . 'that is incompatible with the grid field model class of "'
                    . $gridDataClass . '"'
                );
            }
            $gridDataClass = $extraClass;
            unset($extraData['ClassName']);
        }

        $newItem = $gridDataClass::create();
        $newItem->{$joinKey} = $id;

        if (is_array($extraData) && !empty($extraData)) {
            foreach ($extraData as $key => $value) {
                $newItem->{$key} = $value;
            }
        }

        $newItem->write();
        $gridList->add($newItem);

        $data = null;
        if ($this->getRedirectToEdit())
        {
            $editLink = $this->getGridField()->Link('item/' . $newItem->ID . '/edit');
            $data = ['edit' => $editLink];
        }
        return json_encode($data);
    }

    public function edit($request)
    {
        $id = $request->getVar('id');
        if (!$id) {
            $this->httpError(400);
        }

        $gridList = $this->getGridList();
        $joinKey = $this->getJoinKey();
        $item = $gridList->find($joinKey, $id);
        if (!$item || !$item->exists()) {
            $this->httpError(400);
        }

        /** @var GridField $gridField */
        $gridField = $this->getGridField();
        $editLink = $gridField->Link('item/' . $item->ID . '/edit');
        var_dump($editLink);
        die();
        $this->redirect($editLink);
    }

    public function SearchForm()
    {
        $searchFields = $this->getButton()->getSearchFields();
        if (empty($searchFields)) return null;

        $form = Form::create(
            $this,
            'SearchForm',
            FieldList::create(
                TextField::create('SearchTerm', 'Search')
            ),
            FieldList::create(
                FormAction::create('doSearch', 'Search')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn btn-primary font-icon-search')
            )
        );
        $form->addExtraClass('stacked add-new-hasmany-search-form form--no-dividers');
        $form->setFormMethod('GET');
        return $form;
    }

    public function doSearch($data, $form)
    {
        $searchList = $this->getSearchList();
        $searchFields = $this->getButton()->getSearchFields();

        if (isset($data['SearchTerm']) && count($searchFields) > 0) {
            $searchTerm = $data['SearchTerm'];
            $filterAny = [];
            foreach ($searchFields as $searchField) {
                $filterName = $searchField;
                if (strpos($searchField, ':') === false) {
                    $filterName .= ':PartialMatch';
                }
                $filterAny[$filterName] = $searchTerm;
            }
            $results = $searchList->filterAny($filterAny);
        } else {
            $results = $searchList;
        }

        $this->items = $results;
        $data = $this->customise(['SearchForm' => $form]);
        return $data->index();
    }

    public function Items()
    {
        $searchList = $this->items;
        if (is_null($searchList)) {
            $searchList = $this->getSearchList();
        }
        $format = $this->getItemFormat();
        $items = ArrayList::create();
        foreach ($searchList as $searchItem) {
            $items->push(ArrayData::create(['Result' => $searchItem, 'Title' => $searchItem->{$format}]));
        }
        return PaginatedList::create($items, $this->getRequest());
    }

    public function Link($action = null)
    {
        return Controller::join_links(
            $this->getGridField()->Link(),
            $this->getButton()->getURLSegment(),
            $action
        );
    }

    public function getRedirectToEdit(): bool
    {
        return $this->getButton()->getRedirectToEdit();
    }

    protected function getJoinKey()
    {
        return $this->getButton()->getJoinKey();
    }

    protected function getGridList()
    {
        return $this->getGridField()->getList();
    }

    protected function getGridModelClass(): string
    {
        $class = $this->getButton()->getModelClass();
        if (is_null($class)) {
            $gridList = $this->getGridList();
            $class = $gridList->dataClass();
        }
        return $class;
    }

    protected function getItemFormat()
    {
        return $this->getButton()->getResultFormat();
    }

    protected function getSearchList()
    {
        $excludeGridList = !$this->getButton()->getDoAllowDuplicate();
        $searchList = $this->getButton()->getSearchList();
        $gridList = $this->getGridList();
        if ($excludeGridList) {
            $joinKey = $this->getJoinKey();
            $gridListIDs = $gridList->columnUnique($joinKey);
            if (count($gridListIDs) > 0) {
                $searchList = $searchList->exclude('ID', $gridListIDs);
            }
        }
        return $searchList;
    }

    public function setGridField($gridField)
    {
        $this->gridField = $gridField;
        return $this;
    }

    public function getGridField()
    {
        return $this->gridField;
    }

    public function setButton($button)
    {
        $this->button = $button;
        return $this;
    }

    public function getButton() :GridFieldAddNewHasManySearchButton
    {
        return $this->button;
    }
}
