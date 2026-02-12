<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Resume;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    // List all payments (Admin/Accountant)
    public function index()
    {
        $payments = Payment::with(['customer', 'resume'])->get();
        return view('payments.index', compact('payments'));
    }

    // Show form to create new payment (for customer after resume approval)
    public function create($resumeId)
    {
        $resume = Resume::findOrFail($resumeId);
        return view('payments.create', compact('resume'));
    }

    // Store payment
    public function store(Request $request)
    {
        $request->validate([
            'resume_id' => 'required|exists:resumes,id',
            'amount' => 'required|numeric',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        Payment::create([
            'customer_id' => Auth::id(),
            'resume_id' => $request->resume_id,
            'amount' => $request->amount,
            'status' => 'paid',
            'transaction_id' => $request->transaction_id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Payment successful!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_review,forwarded_to_senior,accepted_by_senior,rejected_by_senior,customer_confirmation,paid,verified,in_training'
        ]);

        $payment = Payment::findOrFail($id);
        $payment->status = $request->status;
        $payment->save();

        return back()->with('success', 'Status updated successfully!');
    }

    public function traupdateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_review,forwarded_to_senior,accepted_by_senior,rejected_by_senior,customer_confirmation,paid,verified,in_training'
        ]);

        $training = Training::findOrFail($id);
        $training->status = $request->status;
        $training->save();

        return back()->with('success', 'Status updated successfully!');
    }
}
