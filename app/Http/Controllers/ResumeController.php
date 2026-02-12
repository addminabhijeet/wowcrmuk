<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumeController extends Controller
{
    // Show all resumes
    public function index()
    {
        $resumes = Resume::with('uploader')->get();
        return view('resumes.index', compact('resumes'));
    }

    // Show create form
    public function create()
    {
        return view('resumes.create');
    }

    // Store resume
    public function store(Request $request)
    {
        $request->validate([
            'candidate_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'resume_file' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        $path = $request->file('resume_file')->store('resumes', 'public');

        Resume::create([
            'uploaded_by' => Auth::id(),
            'candidate_name' => $request->candidate_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'resume_file' => $path,
            'status' => 'pending_review',
        ]);

        return redirect()->route('resumes.index')->with('success', 'Resume uploaded successfully!');
    }

    public function upload(Request $request, $id)
    {
        $request->validate([
            'resume_file' => 'required|mimes:pdf|max:2048', // max 2MB
        ]);

        $resume = Resume::findOrFail($id);

        // Store new PDF
        $fileName = time() . '_' . $request->file('resume_file')->getClientOriginalName();
        $path = $request->file('resume_file')->storeAs('resumes', $fileName, 'public');

        // Update resume record
        $resume->resume_file = $fileName;
        $resume->save();

        return back()->with('success', 'Resume uploaded successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_review,forwarded_to_senior,accepted_by_senior,rejected_by_senior,customer_confirmation,paid,verified,in_training'
        ]);

        $resume = Resume::findOrFail($id);
        $resume->status = $request->status;
        $resume->save();

        return back()->with('success', 'Status updated successfully!');
    }


}
