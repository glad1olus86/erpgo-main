<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WorkerController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage worker')) {
            $workers = Worker::where('created_by', '=', Auth::user()->creatorId())->get();
            return view('worker.index', compact('workers'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Worker $worker)
    {
        if (Auth::user()->can('manage worker')) {
            if ($worker->created_by == Auth::user()->creatorId()) {
                return view('worker.show', compact('worker'));
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

                return redirect()->route('worker.index')->with('success', __('Worker successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Worker $worker)
    {
        if (Auth::user()->can('delete worker')) {
            if ($worker->created_by == Auth::user()->creatorId()) {
                $worker->delete();
                return redirect()->route('worker.index')->with('success', __('Worker successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
