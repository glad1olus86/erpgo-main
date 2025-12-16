<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Services\DocumentScannerService;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkerController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage worker')) {
            // === ДИАГНОСТИКА: Начало ===
            $startTime = microtime(true);
            DB::enableQueryLog();
            
            $workers = Worker::where('created_by', '=', Auth::user()->creatorId())
                ->with(['currentWorkAssignment.workPlace', 'currentAssignment.hotel', 'currentAssignment.room'])
                ->get();
            
            $workersTime = microtime(true) - $startTime;
            
            // Get filter data
            $filterStart = microtime(true);
            $hotels = \App\Models\Hotel::where('created_by', Auth::user()->creatorId())->get();
            $workplaces = \App\Models\WorkPlace::where('created_by', Auth::user()->creatorId())->get();
            $nationalities = Worker::where('created_by', Auth::user()->creatorId())
                ->whereNotNull('nationality')
                ->distinct()
                ->pluck('nationality')
                ->sort();
            $filterTime = microtime(true) - $filterStart;
            
            $queries = DB::getQueryLog();
            $totalTime = microtime(true) - $startTime;
            
            // Выводим в консоль браузера через view
            $diagnostics = [
                'total_time_ms' => round($totalTime * 1000, 2),
                'workers_query_ms' => round($workersTime * 1000, 2),
                'filter_queries_ms' => round($filterTime * 1000, 2),
                'total_queries' => count($queries),
                'workers_count' => $workers->count(),
                'queries' => collect($queries)->map(fn($q) => [
                    'sql' => $q['query'],
                    'time_ms' => $q['time']
                ])->toArray()
            ];
            
            Log::info('WorkerController::index diagnostics', $diagnostics);
            // === ДИАГНОСТИКА: Конец ===
            
            return view('worker.index', compact('workers', 'hotels', 'workplaces', 'nationalities', 'diagnostics'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Worker $worker)
    {
        if (Auth::user()->can('manage worker')) {
            if ($worker->created_by == Auth::user()->creatorId()) {
                $worker->load('currentAssignment', 'currentWorkAssignment.workPlace');
                $hotels = \App\Models\Hotel::where('created_by', Auth::user()->creatorId())
                    ->get()
                    ->pluck('name', 'id');

                // Load work places for assignment modal
                $workPlaces = \App\Models\WorkPlace::where('created_by', Auth::user()->creatorId())
                    ->get()
                    ->pluck('name', 'id');

                // Load recent audit events for this worker
                $recentEvents = \App\Models\AuditLog::where('subject_type', 'App\Models\Worker')
                    ->where('subject_id', $worker->id)
                    ->where('created_by', Auth::user()->creatorId())
                    ->latest()
                    ->limit(10)
                    ->get();

                return view('worker.show', compact('worker', 'hotels', 'workPlaces', 'recentEvents'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('create worker')) {
            return view('worker.create');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create worker')) {
            // Check plan limit
            if (!PlanLimitService::canCreateWorker()) {
                return redirect()->back()->with('error', __('Worker limit reached for your plan.'));
            }

            $validator = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'dob' => 'required|date',
                    'gender' => 'required',
                    'nationality' => 'required',
                    'registration_date' => 'required|date',
                    'document_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $worker = new Worker();
            $worker->first_name = $request->first_name;
            $worker->last_name = $request->last_name;
            $worker->dob = $request->dob;
            $worker->gender = $request->gender;
            $worker->nationality = $request->nationality;
            $worker->registration_date = $request->registration_date;
            $worker->phone = $request->phone;
            $worker->email = $request->email;
            $worker->created_by = Auth::user()->creatorId();

            // Check if we have a pre-scanned document from the scanner
            if ($request->filled('scanned_document_path')) {
                $scannedFile = $request->scanned_document_path;
                // Verify file exists in uploads folder
                if (file_exists(public_path('uploads/worker_documents/' . $scannedFile))) {
                    $worker->document_photo = $scannedFile;
                }
            }
            
            // If user uploaded a new document, it overrides the scanned one
            if ($request->hasFile('document_photo')) {
                $fileName = time() . '_doc_' . $request->document_photo->getClientOriginalName();
                $request->document_photo->move(public_path('uploads/worker_documents'), $fileName);
                $worker->document_photo = $fileName;
            }

            if ($request->hasFile('photo')) {
                $fileName = time() . '_photo_' . $request->photo->getClientOriginalName();
                $request->photo->move(public_path('uploads/worker_photos'), $fileName);
                $worker->photo = $fileName;
            }

            $worker->save();

            if ($request->input('redirect_to') === 'mobile') {
                return redirect()->route('mobile.workers.index')->with('success', __('Worker successfully created.'));
            }

            return redirect()->route('worker.index')->with('success', __('Worker successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(Worker $worker)
    {
        if (Auth::user()->can('edit worker')) {
            if ($worker->created_by == Auth::user()->creatorId()) {
                return view('worker.edit', compact('worker'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Worker $worker)
    {
        if (Auth::user()->can('edit worker')) {
            if ($worker->created_by == Auth::user()->creatorId()) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'dob' => 'required|date',
                        'gender' => 'required',
                        'nationality' => 'required',
                        'registration_date' => 'required|date',
                        'document_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $worker->first_name = $request->first_name;
                $worker->last_name = $request->last_name;
                $worker->dob = $request->dob;
                $worker->gender = $request->gender;
                $worker->nationality = $request->nationality;
                $worker->registration_date = $request->registration_date;
                $worker->phone = $request->phone;
                $worker->email = $request->email;

                if ($request->hasFile('document_photo')) {
                    $fileName = time() . '_doc_' . $request->document_photo->getClientOriginalName();
                    $request->document_photo->move(public_path('uploads/worker_documents'), $fileName);
                    $worker->document_photo = $fileName;
                }

                if ($request->hasFile('photo')) {
                    $fileName = time() . '_photo_' . $request->photo->getClientOriginalName();
                    $request->photo->move(public_path('uploads/worker_photos'), $fileName);
                    $worker->photo = $fileName;
                }

                $worker->save();

                if ($request->input('redirect_to') === 'mobile') {
                    return redirect()->route('mobile.workers.show', $worker->id)->with('success', __('Worker successfully updated.'));
                }

                return redirect()->route('worker.index')->with('success', __('Worker successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Request $request, Worker $worker)
    {
        if (Auth::user()->can('delete worker')) {
            if ($worker->created_by == Auth::user()->creatorId()) {
                $worker->delete();
                
                if ($request->input('redirect_to') === 'mobile') {
                    return redirect()->route('mobile.workers.index')->with('success', __('Worker successfully deleted.'));
                }
                
                return redirect()->route('worker.index')->with('success', __('Worker successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Check if worker with same name already exists
     * Returns list of potential duplicates
     */
    public function checkDuplicate(Request $request)
    {
        if (!Auth::user()->can('create worker')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $firstName = trim($request->first_name);
        $lastName = trim($request->last_name);

        // Find workers with same first and last name (case insensitive)
        $duplicates = Worker::where('created_by', Auth::user()->creatorId())
            ->whereRaw('LOWER(first_name) = ?', [strtolower($firstName)])
            ->whereRaw('LOWER(last_name) = ?', [strtolower($lastName)])
            ->get(['id', 'first_name', 'last_name', 'dob', 'nationality', 'created_at']);

        if ($duplicates->count() > 0) {
            return response()->json([
                'has_duplicates' => true,
                'duplicates' => $duplicates->map(function ($worker) {
                    return [
                        'id' => $worker->id,
                        'name' => $worker->first_name . ' ' . $worker->last_name,
                        'dob' => $worker->dob ? $worker->dob->format('d.m.Y') : null,
                        'nationality' => $worker->nationality,
                        'created_at' => $worker->created_at->format('d.m.Y'),
                    ];
                }),
                'message' => __('Worker with this first and last name already exists!'),
            ]);
        }

        return response()->json([
            'has_duplicates' => false,
        ]);
    }

    /**
     * Scan document image and extract worker data using Gemini API
     * Also saves the scanned document for later attachment to worker
     */
    public function scanDocument(Request $request)
    {
        if (!Auth::user()->can('create worker')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = Validator::make($request->all(), [
            'document_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $image = $request->file('document_image');
            $imagePath = $image->getRealPath();

            $scanner = new DocumentScannerService();
            $data = $scanner->scanDocument($imagePath);

            // Save the scanned document to uploads folder
            $fileName = time() . '_scan_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/worker_documents'), $fileName);

            return response()->json([
                'success' => true,
                'data' => $data,
                'scanned_document' => $fileName  // Return saved filename
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Scan error: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign workers to a work place
     */
    public function bulkAssign(Request $request)
    {
        if (!Auth::user()->can('manage work place')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'worker_ids' => 'required|string',
            'work_place_id' => 'required|exists:work_places,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $workerIds = explode(',', $request->worker_ids);
        $workPlace = \App\Models\WorkPlace::find($request->work_place_id);

        if ($workPlace->created_by != Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $assigned = 0;
        $skipped = 0;

        foreach ($workerIds as $workerId) {
            $worker = Worker::where('id', $workerId)
                ->where('created_by', Auth::user()->creatorId())
                ->first();

            if (!$worker) continue;

            // Skip if already has active work assignment
            if ($worker->currentWorkAssignment) {
                $skipped++;
                continue;
            }

            $assignment = new \App\Models\WorkAssignment();
            $assignment->worker_id = $worker->id;
            $assignment->work_place_id = $workPlace->id;
            $assignment->started_at = now();
            $assignment->created_by = Auth::user()->creatorId();
            $assignment->save();
            $assigned++;
        }

        $message = __('Workers assigned: :assigned', ['assigned' => $assigned]);
        if ($skipped > 0) {
            $message .= '. ' . __('Skipped (already working): :skipped', ['skipped' => $skipped]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk dismiss workers from their work places
     */
    public function bulkDismiss(Request $request)
    {
        if (!Auth::user()->can('manage work place')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'worker_ids' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $workerIds = explode(',', $request->worker_ids);
        $dismissed = 0;

        foreach ($workerIds as $workerId) {
            $worker = Worker::where('id', $workerId)
                ->where('created_by', Auth::user()->creatorId())
                ->first();

            if (!$worker) continue;

            $assignment = $worker->currentWorkAssignment;
            if ($assignment) {
                $assignment->ended_at = now();
                $assignment->save();
                $dismissed++;
            }
        }

        return redirect()->back()->with('success', __('Workers dismissed: :count', ['count' => $dismissed]));
    }

    /**
     * Bulk checkout workers from their rooms
     */
    public function bulkCheckout(Request $request)
    {
        if (!Auth::user()->can('manage worker')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = Validator::make($request->all(), [
            'worker_ids' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $workerIds = explode(',', $request->worker_ids);
        $checkedOut = 0;

        foreach ($workerIds as $workerId) {
            $worker = Worker::where('id', $workerId)
                ->where('created_by', Auth::user()->creatorId())
                ->first();

            if (!$worker) continue;

            $assignment = $worker->currentAssignment;
            if ($assignment) {
                $assignment->check_out_date = now();
                $assignment->save();
                $checkedOut++;
            }
        }

        return redirect()->back()->with('success', __('Workers checked out: :count', ['count' => $checkedOut]));
    }
}
