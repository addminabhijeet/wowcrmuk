<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSheetData extends Model
{
    protected $table = 'google_sheet_data';

    protected $fillable = [
        'sheet_row_number',
        'resume',
        'audio',
        'created_by',
        'created_at',
        'updated_at',
        'Date',
        'Name',
        'Email_Address',
        'Phone_Number',
        'Location',
        'Remark',
        'TransferRemark',
        'RejectedRemark',
        'Relocation',
        'Graduation_Date',
        'Immigration',
        'Course',
        'Amount',
        'Qualification',
        'Exe_Remarks',
        'First_Follow_Up_Remarks',
        'Time_Zone',
        'View',
        'PaymentDate',
        'TranId',
        'TranRef',
        'PaymentMethod',
        'PayeeName',
        'acceptance',
        'consultation',
        'acceptancesign',
        'consultationsign',
        'delivery',
        'payment',
        'followup',
        'rejected',
        'transfers',
        'updateresume',
        'transfer_date'
    ];


    protected $casts = [
        'data' => 'array', // auto decode JSON
        'Date' => 'date',
        'Amount' => 'decimal:2',
    ];

    public function getResumeUrlAttribute()
    {
        return $this->resume ? asset('storage/resumes/' . $this->resume) : null;
    }
}
