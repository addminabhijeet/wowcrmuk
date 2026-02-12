<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\SmtpSetting;



class SmtpSettingController extends Controller
{
    // Show the form to edit SMTP settings
    public function edit()
    {
        $smtp = SmtpSetting::first(); // Assume single record
        return view('smtp_settings.edit', compact('smtp'));
    }

    // Update SMTP settings
    public function update(Request $request)
    {
        $request->validate([
            'mailer' => 'required|string',
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'required|email',
            'password' => 'nullable|string',
            'encryption' => 'required|string',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        $smtp = SmtpSetting::first();
        if (!$smtp) {
            $smtp = new SmtpSetting();
        }

        $smtp->mailer = $request->mailer;
        $smtp->host = $request->host;
        $smtp->port = $request->port;
        $smtp->username = $request->username;
        if ($request->filled('password')) {
            $smtp->password = encrypt($request->password); // encrypt password
        }
        $smtp->encryption = $request->encryption;
        $smtp->from_address = $request->from_address;
        $smtp->from_name = $request->from_name;

        $smtp->save();

        return redirect()->back()->with('success', 'SMTP settings updated successfully!');
    }

    public function test(Request $request)
    {
        $smtp = SmtpSetting::first();

        if (!$smtp) {
            return response()->json([
                'status' => 'error',
                'message' => 'No SMTP settings found.'
            ]);
        }

        try {
            // Configure SMTP dynamically
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => $smtp->mailer ?? 'smtp',
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => decrypt($smtp->password),
                'mail.mailers.smtp.encryption' => $smtp->encryption,
                'mail.from.address' => $smtp->from_address,
                'mail.from.name' => $smtp->from_name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to configure SMTP: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ],
            ]);
        }

        $testEmail = trim($request->input('test_email'));

        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid test email address.'
            ]);
        }

        $subject = "Testing the Mail Functionality";
        $messageBody = "Hi Test,\n\nThis is a test email sent to verify SMTP configuration.\n\nBest,\nYour App";

        try {
            Mail::raw($messageBody, function ($message) use ($testEmail, $subject) {
                $message->to($testEmail)
                    ->subject($subject);
            });

            return response()->json([
                'status' => 'success',
                'message' => "✅ Test email sent successfully to {$testEmail}!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ Failed to send test email: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ],
            ]);
        }
    }


    public function sendPaymentMail(Request $request)
    {
        $receiverEmail = $request->input('receiverEmail');
        $candidateName = $request->input('candidateName');
        $paymentLink = $request->input('paymentLink');

        if (!$receiverEmail || !filter_var($receiverEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Invalid email']);
        }

        // Load SMTP ID = 1
        $smtp = SmtpSetting::where('id', 1)->first();

        if (!$smtp) {
            return response()->json(['success' => false, 'message' => 'SMTP settings not found']);
        }

        try {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => $smtp->mailer ?? 'smtp',
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => decrypt($smtp->password),
                'mail.mailers.smtp.encryption' => $smtp->encryption,
                'mail.from.address' => $smtp->from_address,
                'mail.from.name' => $smtp->from_name,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed SMTP Configuration']);
        }

        $subject = "Complete Your Enrollment – Secure Payment Link";

        $body = "
Hello {$candidateName},

We hope this message finds you well.

Please find below the secure payment link to complete your enrollment process with Synergie Systems. Kindly ensure the payment is processed at your earliest convenience to confirm your spot and initiate the next steps.

{$paymentLink}

Thank you for choosing Synergie Systems. We look forward to working with you.
";

        try {
            Mail::raw($body, function ($message) use ($receiverEmail, $subject) {
                $message->to($receiverEmail)
                    ->subject($subject);
            });

            return response()->json(['success' => true, 'message' => 'Mail sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Mail sending failed: ' . $e->getMessage()]);
        }
    }
}
