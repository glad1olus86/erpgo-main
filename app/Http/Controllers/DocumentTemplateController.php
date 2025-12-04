<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Services\DocumentTemplateService;
use App\Services\DocumentGeneratorService;
use App\Services\DocumentAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateController extends Controller
{
    protected DocumentTemplateService $templateService;
    protected DocumentGeneratorService $generatorService;
    protected DocumentAuditService $auditService;

    public function __construct(
        DocumentTemplateService $templateService,
        DocumentGeneratorService $generatorService,
        DocumentAuditService $auditService
    ) {
        $this->templateService = $templateService;
        $this->generatorService = $generatorService;
        $this->auditService = $auditService;
    }

    /**
     * Display a listing of templates
     */
    public function index()
    {
        if (!Auth::user()->can('document_template_read')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        $templates = $this->templateService->getAll();

        return view('documents.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        if (!Auth::user()->can('document_template_create')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        $variables = $this->generatorService->getAvailableVariables();

        return view('documents.create', compact('variables'));
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('document_template_create')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $template = $this->templateService->create($request->all());
        $this->auditService->logTemplateCreated($template);

        return redirect()->route('documents.index')
            ->with('success', __('Шаблон успешно создан'));
    }

    /**
     * Show the form for editing the template
     */
    public function edit(DocumentTemplate $template)
    {
        if (!Auth::user()->can('document_template_edit')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        // Multi-tenancy check
        if ($template->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Шаблон не найден'));
        }

        $variables = $this->generatorService->getAvailableVariables();

        return view('documents.edit', compact('template', 'variables'));
    }

    /**
     * Update the template
     */
    public function update(Request $request, DocumentTemplate $template)
    {
        if (!Auth::user()->can('document_template_edit')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        // Multi-tenancy check
        if ($template->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Шаблон не найден'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $template->toArray();
        $template = $this->templateService->update($template, $request->all());
        $this->auditService->logTemplateUpdated($template, $oldValues);

        return redirect()->route('documents.index')
            ->with('success', __('Шаблон успешно обновлен'));
    }

    /**
     * Remove the template
     */
    public function destroy(DocumentTemplate $template)
    {
        if (!Auth::user()->can('document_template_delete')) {
            return redirect()->back()->with('error', __('Недостаточно прав'));
        }

        // Multi-tenancy check
        if ($template->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Шаблон не найден'));
        }

        $this->auditService->logTemplateDeleted($template);
        $this->templateService->delete($template);

        return redirect()->route('documents.index')
            ->with('success', __('Шаблон успешно удален'));
    }
}
