<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function acceptance(Request $request)
    {
        $name          = $request->input('name');
        $email         = $request->input('email');
        $amount        = $request->input('amount');
        $course        = $request->input('course');
        $remark        = $request->input('remark');
        $tranId        = $request->input('tranId');
        $tranRef       = $request->input('tranRef');
        $paymentDateRaw = $request->input('paymentDate');
        if ($paymentDateRaw && $paymentDateRaw !== 'N/A' && strtotime($paymentDateRaw) !== false) {
            $paymentDate = \Carbon\Carbon::parse($paymentDateRaw)->format('F d, Y');
        } else {
            $paymentDate = 'N/A';
        }
        $paymentMethod = $request->input('paymentMethod');
        $payeeName     = $request->input('payeeName');
        $Location     = $request->input('Location');
        $phone_Number = $request->input('Phone_Number');

        return view('pdf.preacceptance', compact(
            'name',
            'email',
            'amount',
            'course',
            'remark',
            'tranId',
            'tranRef',
            'paymentDate',
            'paymentMethod',
            'phone_Number',
            'Location',
            'payeeName'
        ));
    }


    public function consultation(Request $request)
    {
        $name          = $request->input('name');
        $email         = $request->input('email');
        $amount        = $request->input('amount');
        $course        = $request->input('course');
        $remark        = $request->input('remark');
        $tranId        = $request->input('tranId');
        $tranRef       = $request->input('tranRef');
        $paymentDateRaw = $request->input('paymentDate');
        if ($paymentDateRaw && $paymentDateRaw !== 'N/A' && strtotime($paymentDateRaw) !== false) {
            $paymentDate = \Carbon\Carbon::parse($paymentDateRaw)->format('F d, Y');
        } else {
            $paymentDate = 'N/A';
        }
        $paymentMethod = $request->input('paymentMethod');
        $payeeName     = $request->input('payeeName');
        $Location     = $request->input('Location');
        $phone_Number     = $request->input('Phone_Number');


        return view('pdf.consultation', compact(
            'name',
            'email',
            'amount',
            'course',
            'remark',
            'tranId',
            'tranRef',
            'paymentDate',
            'paymentMethod',
            'phone_Number',
            'Location',
            'payeeName'
        ));
    }

    public function delivery(Request $request)
    {
        $name          = $request->input('name');
        $email         = $request->input('email');
        $amount        = $request->input('amount');
        $course        = $request->input('course');
        $remark        = $request->input('remark');
        $tranId        = $request->input('tranId');
        $tranRef       = $request->input('tranRef');
        $paymentDateRaw = $request->input('paymentDate');
        if ($paymentDateRaw && $paymentDateRaw !== 'N/A' && strtotime($paymentDateRaw) !== false) {
            $paymentDate = \Carbon\Carbon::parse($paymentDateRaw)->format('F d, Y');
        } else {
            $paymentDate = 'N/A';
        }
        $paymentMethod = $request->input('paymentMethod');
        $payeeName     = $request->input('payeeName');
        $Location     = $request->input('Location');
        $phone_Number     = $request->input('Phone_Number');

        return view('pdf.predelivery', compact(
            'name',
            'email',
            'amount',
            'course',
            'remark',
            'tranId',
            'tranRef',
            'paymentDate',
            'phone_Number',
            'Location',
            'paymentMethod',
            'phone_Number',
            'Location',
            'payeeName'
        ));
    }

    public function payment(Request $request)
    {
        $name          = $request->input('name');
        $email         = $request->input('email');
        $amount        = $request->input('amount');
        $course        = $request->input('course');
        $remark        = $request->input('remark');
        $tranId        = $request->input('tranId');
        $tranRef       = $request->input('tranRef');
        $paymentDateRaw = $request->input('paymentDate');
        if ($paymentDateRaw && $paymentDateRaw !== 'N/A' && strtotime($paymentDateRaw) !== false) {
            $paymentDate = \Carbon\Carbon::parse($paymentDateRaw)->format('F d, Y');
        } else {
            $paymentDate = 'N/A';
        }
        $paymentMethod = $request->input('paymentMethod');
        $payeeName     = $request->input('payeeName');
        $Location     = $request->input('Location');
        $phone_Number     = $request->input('Phone_Number');

        return view('pdf.prepayment', compact(
            'name',
            'email',
            'amount',
            'course',
            'remark',
            'tranId',
            'tranRef',
            'paymentDate',
            'paymentMethod',
            'phone_Number',
            'Location',
            'payeeName'
        ));
    }

    public function deliveryuk(Request $request)
    {
        $name          = $request->input('name');
        $email         = $request->input('email');
        $amount        = $request->input('amount');
        $course        = $request->input('course');
        $remark        = $request->input('remark');
        $tranId        = $request->input('tranId');
        $tranRef       = $request->input('tranRef');
        $paymentMethod = $request->input('paymentMethod');
        $payeeName     = $request->input('payeeName');
        $paymentDateRaw = $request->input('paymentDate');
        if ($paymentDateRaw && $paymentDateRaw !== 'N/A' && strtotime($paymentDateRaw) !== false) {
            $paymentDate = \Carbon\Carbon::parse($paymentDateRaw)->format('F d, Y');
        } else {
            $paymentDate = 'N/A';
        }
        $Location     = $request->input('Location');
        $phone_Number     = $request->input('Phone_Number');

        return view('pdf.deliveryuk', compact(
            'name',
            'email',
            'amount',
            'course',
            'remark',
            'tranId',
            'tranRef',
            'paymentDate',
            'paymentMethod',
            'phone_Number',
            'Location',
            'payeeName'
        ));
    }


    public function paymentuk(Request $request)
    {
        $name          = $request->input('name');
        $email         = $request->input('email');
        $amount        = $request->input('amount');
        $course        = $request->input('course');
        $remark        = $request->input('remark');
        $tranId        = $request->input('tranId');
        $tranRef       = $request->input('tranRef');
        $paymentDateRaw = $request->input('paymentDate');
        if ($paymentDateRaw && $paymentDateRaw !== 'N/A' && strtotime($paymentDateRaw) !== false) {
            $paymentDate = \Carbon\Carbon::parse($paymentDateRaw)->format('F d, Y');
        } else {
            $paymentDate = 'N/A';
        }
        $paymentMethod = $request->input('paymentMethod');
        $payeeName     = $request->input('payeeName');
        $Location     = $request->input('Location');
        $phone_Number     = $request->input('Phone_Number');

        return view('pdf.paymentuk', compact(
            'name',
            'email',
            'amount',
            'course',
            'remark',
            'tranId',
            'tranRef',
            'paymentDate',
            'paymentMethod',
            'phone_Number',
            'Location',
            'payeeName'
        ));
    }
}
