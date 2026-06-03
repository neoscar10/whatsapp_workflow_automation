<?php

namespace App\Livewire\SuperAdmin;

use App\Models\VerificationTemplate;
use App\Models\DocumentType;
use Livewire\Component;

class VerificationTemplateConfig extends Component
{
    // Selected Template state
    public $selectedTemplateId = null;

    // Template Modal state & properties
    public $showTemplateModal = false;
    public $editingTemplateId = null;
    public $templateName = '';
    public $templateCountryCode = '';
    public $templateDescription = '';
    public $templateIsActive = true;
    public $templateSortOrder = 0;

    // Document Type Modal state & properties
    public $showDocumentModal = false;
    public $editingDocumentId = null;
    public $docName = '';
    public $docDescription = '';
    public $docPlaceholder = '';
    public $docAcceptedFormats = 'pdf,jpg,png,jpeg';
    public $docMaxSizeMb = 10;
    public $docIsRequired = true;
    public $docIsActive = true;
    public $docSortOrder = 0;

    // Confirmation Modals for disabling
    public $confirmingDisableTemplateId = null;
    public $confirmingDisableDocumentId = null;

    public function mount()
    {
        // Select the first template by default if available
        $firstTemplate = VerificationTemplate::orderBy('sort_order')->orderBy('name')->first();
        if ($firstTemplate) {
            $this->selectedTemplateId = $firstTemplate->id;
        }
    }

    public function selectTemplate($id)
    {
        $this->selectedTemplateId = $id;
        $this->closeDocumentModal();
    }

    // --- TEMPLATE CRUD ACTIONS ---

    public function openCreateTemplateModal()
    {
        $this->resetTemplateForm();
        $this->showTemplateModal = true;
    }

    public function openEditTemplateModal($id)
    {
        $template = VerificationTemplate::findOrFail($id);
        $this->editingTemplateId = $template->id;
        $this->templateName = $template->name;
        $this->templateCountryCode = $template->country_code;
        $this->templateDescription = $template->description;
        $this->templateIsActive = (bool) $template->is_active;
        $this->templateSortOrder = (int) $template->sort_order;

        $this->showTemplateModal = true;
    }

    public function saveTemplate()
    {
        $this->validate([
            'templateName' => 'required|string|max:255',
            'templateCountryCode' => ['nullable', 'string', 'size:2', \Illuminate\Validation\Rule::in(array_keys(\App\Models\Company::$countries))],
            'templateDescription' => 'nullable|string',
            'templateIsActive' => 'required|boolean',
            'templateSortOrder' => 'required|integer|min:0',
        ]);

        $data = [
            'name' => $this->templateName,
            'country_code' => $this->templateCountryCode ? strtoupper($this->templateCountryCode) : null,
            'description' => $this->templateDescription,
            'is_active' => $this->templateIsActive,
            'sort_order' => $this->templateSortOrder,
        ];

        if ($this->editingTemplateId) {
            VerificationTemplate::findOrFail($this->editingTemplateId)->update($data);
            session()->flash('success_templates', 'Verification checklist updated successfully.');
        } else {
            $newTemplate = VerificationTemplate::create($data);
            $this->selectedTemplateId = $newTemplate->id;
            session()->flash('success_templates', 'Verification checklist created successfully.');
        }

        $this->closeTemplateModal();
    }

    public function requestDisableTemplate($id)
    {
        $template = VerificationTemplate::findOrFail($id);
        if ($template->is_active) {
            $this->confirmingDisableTemplateId = $id;
        } else {
            $this->toggleTemplateStatus($id);
        }
    }

    public function confirmDisableTemplate()
    {
        if ($this->confirmingDisableTemplateId) {
            $this->toggleTemplateStatus($this->confirmingDisableTemplateId);
            $this->confirmingDisableTemplateId = null;
        }
    }

    public function cancelDisableTemplate()
    {
        $this->confirmingDisableTemplateId = null;
    }

    public function toggleTemplateStatus($id)
    {
        $template = VerificationTemplate::findOrFail($id);
        $template->update(['is_active' => !$template->is_active]);
        session()->flash('success_templates', 'Checklist active status updated.');
    }

    public function deleteTemplate($id)
    {
        $template = VerificationTemplate::findOrFail($id);
        $template->delete();

        if ($this->selectedTemplateId === $id) {
            $first = VerificationTemplate::orderBy('sort_order')->orderBy('name')->first();
            $this->selectedTemplateId = $first ? $first->id : null;
        }

        session()->flash('success_templates', 'Verification checklist deleted successfully.');
    }

    public function moveUpTemplate($id)
    {
        $current = VerificationTemplate::findOrFail($id);
        $previous = VerificationTemplate::where('sort_order', '<', $current->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previous) {
            $oldOrder = $current->sort_order;
            $current->update(['sort_order' => $previous->sort_order]);
            $previous->update(['sort_order' => $oldOrder]);
        } else {
            // Decrement if already at the top to clear order conflict
            $current->update(['sort_order' => max(0, $current->sort_order - 1)]);
        }
    }

    public function moveDownTemplate($id)
    {
        $current = VerificationTemplate::findOrFail($id);
        $next = VerificationTemplate::where('sort_order', '>', $current->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($next) {
            $oldOrder = $current->sort_order;
            $current->update(['sort_order' => $next->sort_order]);
            $next->update(['sort_order' => $oldOrder]);
        } else {
            $current->update(['sort_order' => $current->sort_order + 1]);
        }
    }

    public function closeTemplateModal()
    {
        $this->showTemplateModal = false;
        $this->resetTemplateForm();
    }

    private function resetTemplateForm()
    {
        $this->editingTemplateId = null;
        $this->templateName = '';
        $this->templateCountryCode = 'IN';
        $this->templateDescription = '';
        $this->templateIsActive = true;
        
        // Default to next order value
        $maxOrder = VerificationTemplate::max('sort_order');
        $this->templateSortOrder = $maxOrder !== null ? $maxOrder + 1 : 0;
        
        $this->resetErrorBag();
    }

    // --- DOCUMENT TYPE CRUD ACTIONS ---

    public function openCreateDocumentModal()
    {
        $this->resetDocumentForm();
        $this->showDocumentModal = true;
    }

    public function openEditDocumentModal($id)
    {
        $doc = DocumentType::findOrFail($id);
        $this->editingDocumentId = $doc->id;
        $this->docName = $doc->name;
        $this->docDescription = $doc->description;
        $this->docPlaceholder = $doc->placeholder;
        $this->docAcceptedFormats = $doc->accepted_formats;
        $this->docMaxSizeMb = (int) $doc->max_size_mb;
        $this->docIsRequired = (bool) $doc->is_required;
        $this->docIsActive = (bool) $doc->is_active;
        $this->docSortOrder = (int) $doc->sort_order;

        $this->showDocumentModal = true;
    }

    public function saveDocument()
    {
        if (!$this->selectedTemplateId) {
            return;
        }

        $this->validate([
            'docName' => 'required|string|max:255',
            'docDescription' => 'nullable|string',
            'docPlaceholder' => 'nullable|string',
            'docAcceptedFormats' => 'required|string',
            'docMaxSizeMb' => 'required|integer|min:1|max:50',
            'docIsRequired' => 'required|boolean',
            'docIsActive' => 'required|boolean',
            'docSortOrder' => 'required|integer|min:0',
        ]);

        $data = [
            'verification_template_id' => $this->selectedTemplateId,
            'name' => $this->docName,
            'description' => $this->docDescription,
            'placeholder' => $this->docPlaceholder,
            'accepted_formats' => $this->docAcceptedFormats,
            'max_size_mb' => $this->docMaxSizeMb,
            'is_required' => $this->docIsRequired,
            'is_active' => $this->docIsActive,
            'sort_order' => $this->docSortOrder,
        ];

        if ($this->editingDocumentId) {
            DocumentType::findOrFail($this->editingDocumentId)->update($data);
            session()->flash('success_documents', 'Document requirement updated successfully.');
        } else {
            DocumentType::create($data);
            session()->flash('success_documents', 'Document requirement added successfully.');
        }

        $this->closeDocumentModal();
    }

    public function toggleDocumentRequiredStatus($id)
    {
        $doc = DocumentType::findOrFail($id);
        $doc->update(['is_required' => !$doc->is_required]);
        session()->flash('success_documents', 'Document requirement status toggled.');
    }

    public function requestDisableDocument($id)
    {
        $doc = DocumentType::findOrFail($id);
        if ($doc->is_active) {
            $this->confirmingDisableDocumentId = $id;
        } else {
            $this->toggleDocumentStatus($id);
        }
    }

    public function confirmDisableDocument()
    {
        if ($this->confirmingDisableDocumentId) {
            $this->toggleDocumentStatus($this->confirmingDisableDocumentId);
            $this->confirmingDisableDocumentId = null;
        }
    }

    public function cancelDisableDocument()
    {
        $this->confirmingDisableDocumentId = null;
    }

    public function toggleDocumentStatus($id)
    {
        $doc = DocumentType::findOrFail($id);
        $doc->update(['is_active' => !$doc->is_active]);
        session()->flash('success_documents', 'Document active status toggled.');
    }

    public function reorderDocuments($fromId, $toId)
    {
        if (!$this->selectedTemplateId) {
            return;
        }

        $documents = DocumentType::where('verification_template_id', $this->selectedTemplateId)
            ->orderBy('sort_order')
            ->get();

        $fromIndex = $documents->search(fn($doc) => $doc->id === $fromId);
        $toIndex = $documents->search(fn($doc) => $doc->id === $toId);

        if ($fromIndex !== false && $toIndex !== false && $fromIndex !== $toIndex) {
            $dragged = $documents->pull($fromIndex);
            $documents->splice($toIndex, 0, [$dragged]);

            foreach ($documents as $index => $doc) {
                $doc->update(['sort_order' => $index + 1]);
            }

            session()->flash('success_documents', 'Document requirement order updated.');
        }
    }

    public function deleteDocument($id)
    {
        $doc = DocumentType::findOrFail($id);
        $doc->delete();
        session()->flash('success_documents', 'Document requirement deleted successfully.');
    }

    public function moveUpDocument($id)
    {
        if (!$this->selectedTemplateId) {
            return;
        }

        $current = DocumentType::findOrFail($id);
        $previous = DocumentType::where('verification_template_id', $this->selectedTemplateId)
            ->where('sort_order', '<', $current->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previous) {
            $oldOrder = $current->sort_order;
            $current->update(['sort_order' => $previous->sort_order]);
            $previous->update(['sort_order' => $oldOrder]);
        } else {
            $current->update(['sort_order' => max(0, $current->sort_order - 1)]);
        }
    }

    public function moveDownDocument($id)
    {
        if (!$this->selectedTemplateId) {
            return;
        }

        $current = DocumentType::findOrFail($id);
        $next = DocumentType::where('verification_template_id', $this->selectedTemplateId)
            ->where('sort_order', '>', $current->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($next) {
            $oldOrder = $current->sort_order;
            $current->update(['sort_order' => $next->sort_order]);
            $next->update(['sort_order' => $oldOrder]);
        } else {
            $current->update(['sort_order' => $current->sort_order + 1]);
        }
    }

    public function closeDocumentModal()
    {
        $this->showDocumentModal = false;
        $this->resetDocumentForm();
    }

    private function resetDocumentForm()
    {
        $this->editingDocumentId = null;
        $this->docName = '';
        $this->docDescription = '';
        $this->docPlaceholder = '';
        $this->docAcceptedFormats = 'pdf,jpg,png,jpeg';
        $this->docMaxSizeMb = 10;
        $this->docIsRequired = true;
        $this->docIsActive = true;

        if ($this->selectedTemplateId) {
            $maxOrder = DocumentType::where('verification_template_id', $this->selectedTemplateId)->max('sort_order');
            $this->docSortOrder = $maxOrder !== null ? $maxOrder + 1 : 0;
        } else {
            $this->docSortOrder = 0;
        }

        $this->resetErrorBag();
    }

    public function render()
    {
        // Get all templates ordered by sort_order
        $templates = VerificationTemplate::orderBy('sort_order')->orderBy('name')->get();

        // Get document requirements for selected template
        $documents = [];
        $selectedTemplate = null;

        if ($this->selectedTemplateId) {
            $selectedTemplate = VerificationTemplate::find($this->selectedTemplateId);
            if ($selectedTemplate) {
                $documents = $selectedTemplate->documentTypes;
            }
        }

        return view('livewire.super-admin.verification-template-config', [
            'templates' => $templates,
            'documents' => $documents,
            'selectedTemplate' => $selectedTemplate,
        ])
        ->layout('layouts.super-admin', [
            'title' => 'Verification Config',
            'activeNav' => 'verification-templates',
        ]);
    }
}
