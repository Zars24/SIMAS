<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = Subject::query()
            ->withCount('schedules')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q')->toString().'%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)->orWhere('code', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.subjects.index', [
            'subjects' => $subjects,
            'filters' => $request->only(['q']),
        ]);
    }

    public function create(): View
    {
        return view('admin.subjects.create', [
            'subject' => new Subject(),
        ]);
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);

        $subject = Subject::create($data);

        AuditLogger::log('subject.created', $subject, ['name' => $subject->name]);

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', "Mata pelajaran {$subject->name} berhasil dibuat.");
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.edit', [
            'subject' => $subject,
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);

        $subject->update($data);

        AuditLogger::log('subject.updated', $subject, ['name' => $subject->name]);

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->schedules()->exists()) {
            return back()->with('error', 'Mata pelajaran masih dipakai pada jadwal. Hapus jadwalnya terlebih dahulu.');
        }

        AuditLogger::log('subject.deleted', $subject, ['name' => $subject->name]);

        $subject->delete();

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
