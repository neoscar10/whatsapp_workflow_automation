<?php

namespace App\Livewire\Contacts;

use App\Models\Contact\ContactTag;
use App\Models\Contact\ContactGroup;
use App\Services\Contact\ContactTagService;
use App\Services\Contact\ContactGroupService;
use App\Services\Contact\ContactSegmentRuleService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class AudienceManagerPage extends Component
{
    use WithPagination;

    public $activeTab = 'tags'; // tags, static_groups, dynamic_segments
    public $search = '';

    // Modal State
    public $showTagModal = false;
    public $showStaticGroupModal = false;
    public $showDynamicSegmentModal = false;
    public $showPreviewModal = false;

    // Form Data
    public $selectedId = null;
    public $name = '';
    public $description = '';
    public $color = '#3b82f6';
    
    // Dynamic Segment Rules
    public $rules = [
        'match' => 'all',
        'conditions' => [
            ['field' => '', 'operator' => '', 'value' => '']
        ]
    ];

    // Preview
    public $previewResults = null;

    protected $queryString = [
        'activeTab' => ['except' => 'tags'],
        'search' => ['except' => ''],
    ];

    public function updatedActiveTab()
    {
        $this->resetPage();
        $this->reset(['search', 'selectedId']);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Tag Methods
    public function openTagModal($id = null)
    {
        $this->resetForm();
        if ($id) {
            $tag = ContactTag::where('company_id', Auth::user()->company_id)->findOrFail($id);
            $this->selectedId = $tag->id;
            $this->name = $tag->name;
            $this->description = $tag->description;
            $this->color = $tag->color ?? '#3b82f6';
        }
        $this->showTagModal = true;
    }

    public function saveTag()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $service = app(ContactTagService::class);
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
        ];

        if ($this->selectedId) {
            $tag = ContactTag::where('company_id', Auth::user()->company_id)->findOrFail($this->selectedId);
            $service->update(Auth::user(), $tag, $data);
            $msg = 'Tag updated successfully.';
        } else {
            $service->create(Auth::user(), $data);
            $msg = 'Tag created successfully.';
        }

        $this->showTagModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function deleteTag($id)
    {
        $tag = ContactTag::where('company_id', Auth::user()->company_id)->findOrFail($id);
        app(ContactTagService::class)->delete(Auth::user(), $tag);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Tag deleted.']);
    }

    // Static Group Methods
    public function openStaticGroupModal($id = null)
    {
        $this->resetForm();
        if ($id) {
            $group = ContactGroup::where('company_id', Auth::user()->company_id)->findOrFail($id);
            $this->selectedId = $group->id;
            $this->name = $group->name;
            $this->description = $group->description;
        }
        $this->showStaticGroupModal = true;
    }

    public function saveStaticGroup()
    {
        $this->validate(['name' => 'required|string|max:255']);
        
        $service = app(ContactGroupService::class);
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'type' => 'static',
        ];

        if ($this->selectedId) {
            $group = ContactGroup::where('company_id', Auth::user()->company_id)->findOrFail($this->selectedId);
            $service->update(Auth::user(), $group, $data);
            $msg = 'Static group updated.';
        } else {
            $service->create(Auth::user(), $data);
            $msg = 'Static group created.';
        }

        $this->showStaticGroupModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    // Dynamic Segment Methods
    public function openDynamicSegmentModal($id = null)
    {
        $this->resetForm();
        if ($id) {
            $group = ContactGroup::where('company_id', Auth::user()->company_id)->findOrFail($id);
            $this->selectedId = $group->id;
            $this->name = $group->name;
            $this->description = $group->description;
            $this->rules = $group->rules ?? $this->rules;
        }
        $this->showDynamicSegmentModal = true;
    }

    public function addCondition()
    {
        $this->rules['conditions'][] = ['field' => '', 'operator' => '', 'value' => ''];
    }

    public function removeCondition($index)
    {
        unset($this->rules['conditions'][$index]);
        $this->rules['conditions'] = array_values($this->rules['conditions']);
    }

    public function saveDynamicSegment()
    {
        $this->validate(['name' => 'required|string|max:255']);
        
        $service = app(ContactGroupService::class);
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'type' => 'dynamic',
            'rules' => $this->rules,
        ];

        if ($this->selectedId) {
            $group = ContactGroup::where('company_id', Auth::user()->company_id)->findOrFail($this->selectedId);
            $service->update(Auth::user(), $group, $data);
            $msg = 'Dynamic segment updated.';
        } else {
            $service->create(Auth::user(), $data);
            $msg = 'Dynamic segment created.';
        }

        $this->showDynamicSegmentModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }

    public function previewSegment()
    {
        $this->previewResults = app(ContactSegmentRuleService::class)->preview(Auth::user(), $this->rules);
        $this->showPreviewModal = true;
    }

    public function deleteGroup($id)
    {
        $group = ContactGroup::where('company_id', Auth::user()->company_id)->findOrFail($id);
        app(ContactGroupService::class)->delete(Auth::user(), $group);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Group deleted.']);
    }

    protected function resetForm()
    {
        $this->reset(['selectedId', 'name', 'description', 'color', 'previewResults']);
        $this->rules = [
            'match' => 'all',
            'conditions' => [
                ['field' => '', 'operator' => '', 'value' => '']
            ]
        ];
    }

    public function render()
    {
        $companyId = Auth::user()->company_id;
        
        $data = match($this->activeTab) {
            'tags' => app(ContactTagService::class)->listForCompany($companyId, ['search' => $this->search]),
            'static_groups' => app(ContactGroupService::class)->listForCompany($companyId, ['search' => $this->search, 'type' => 'static']),
            'dynamic_segments' => app(ContactGroupService::class)->listForCompany($companyId, ['search' => $this->search, 'type' => 'dynamic']),
        };

        $stats = [
            'tags_count' => ContactTag::where('company_id', $companyId)->count(),
            'static_count' => ContactGroup::where('company_id', $companyId)->where('type', 'static')->count(),
            'dynamic_count' => ContactGroup::where('company_id', $companyId)->where('type', 'dynamic')->count(),
        ];

        return view('livewire.contacts.audience-manager-page', [
            'items' => $data,
            'stats' => $stats,
            'availableFields' => app(ContactSegmentRuleService::class)->availableFields(),
        ])->layout('layouts.panel', ['title' => 'Audience Manager', 'activeNav' => 'contacts']);
    }
}
