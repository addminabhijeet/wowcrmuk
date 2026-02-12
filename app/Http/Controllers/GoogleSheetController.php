<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\GoogleSheetData;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\SmtpSetting;
use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



class GoogleSheetController extends Controller
{
    public function admin(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user');
        $page = $request->input('page', 1);

        $userPattern = "%:" . $authUser->id . "|senior";
        $zeroPattern = "%:0|senior";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern, $zeroPattern) {
            $q->where('created_by', $authUser->id . '|senior')
                ->orWhere('created_by', '0|senior')
                ->orWhere('created_by', 'LIKE', $userPattern)
                ->orWhere('created_by', 'LIKE', $zeroPattern);
        });

        // Filter by selected junior
        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
            });
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Pagination at query level (not collection level)
        $perPage = 10;
        $results = $query->orderBy('updated_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        // ✅ Transform only current page results
        $results->getCollection()->transform(function ($item) use ($authUser) {
            $forwardedBy = 'N/A';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    $roleLabel = ($role === 'senior') ? 'IT Senior Recruiter' : (($role === 'junior') ? 'IT Recruiter' : $role);

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        $juniorUsers = \App\Models\User::where('is_deleted', 0)
            ->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        // ✅ For AJAX pagination, return only table partial
        if ($request->ajax()) {
            return view('database.partials.senior_table', [
                'data' => $results,
                'juniorUsers' => $juniorUsers
            ])->render();
        }

        return view('database.admin', [
            'data' => $results,
            'juniorUsers' => $juniorUsers
        ]);
    }



    public function adminfetch(Request $request)
    {
        $request->validate([
            'sheet_link' => 'required|url'
        ]);

        // Extract spreadsheet ID
        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $request->sheet_link, $matches);
        $spreadsheetId = $matches[1] ?? null;

        if (!$spreadsheetId) {
            return back()->with('error', 'Invalid Google Sheet link');
        }

        // Fetch CSV
        $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";
        $csvData = @file_get_contents($csvUrl);

        if ($csvData === false) {
            return back()->with('error', 'Unable to fetch Google Sheet (maybe private?)');
        }

        $rows = array_map('str_getcsv', explode("\n", trim($csvData)));
        $header = array_shift($rows); // first row as column headers

        $rowIndex = 2;
        $user = Auth::user();

        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;
            if (count($row) !== count($header)) continue;

            $rowData = array_combine($header, $row);

            // Map CSV headers to database columns
            $mappedData = [
                'sheet_row_number' => $rowIndex,
                'Date' => isset($rowData['Date']) ? \Carbon\Carbon::createFromFormat('m/d/Y', $rowData['Date'])->format('Y-m-d') : null,
                'Name' => $rowData['Name'] ?? null,
                'Email_Address' => $rowData['Email Address'] ?? null,
                'Phone_Number' => $rowData['Phone Number'] ?? null,
                'Location' => $rowData['Location'] ?? null,
                'Relocation' => $rowData['Relocation'] ?? null,
                'Graduation_Date' => isset($rowData['Graduation Date']) ? \Carbon\Carbon::createFromFormat('m/d/Y', $rowData['Graduation Date'])->format('Y-m-d') : null,
                'Immigration' => $rowData['Immigration'] ?? null,
                'Course' => $rowData['Course'] ?? null,
                'Amount' => isset($rowData['Amount']) ? (float) str_replace(['$', ','], '', $rowData['Amount']) : null,
                'Qualification' => $rowData['Qualification'] ?? null,
                'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
                'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
                'Time_Zone' => $rowData['Time Zone'] ?? null,
                'View' => $rowData['View'] ?? null,
                'created_by' => "{$user->id}|{$user->role}",
            ];

            GoogleSheetData::updateOrCreate(
                ['sheet_row_number' => $rowIndex],
                $mappedData
            );

            $rowIndex++;
        }

        return redirect()->route('google.sheet.admin')->with('success', 'Data fetched successfully!');
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');

        // Fetch the latest record with the given email (use is_current = 1 for latest active)
        $record = GoogleSheetData::where('Email_Address', $email)
            ->orderBy('sheet_row_number', 'desc') // latest row first
            ->first();

        return response()->json([
            'exists' => (bool) $record,
            'data'   => $record
        ]);
    }



    public function adminupdate(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? null;
        $phone = $rowData['Phone Number'] ?? null;

        // Check for duplicate Email (ignore current record)
        if (!empty($email)) {
            $emailExists = GoogleSheetData::where('Email_Address', $email)
                ->where('id', '!=', $id)
                ->exists();

            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address already exists in records.'
                ]);
            }
        }

        // Check for duplicate Phone (ignore current record)
        if (!empty($phone)) {
            $phoneExists = GoogleSheetData::where('Phone_Number', $phone)
                ->where('id', '!=', $id)
                ->exists();

            if ($phoneExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number already exists in records.'
                ]);
            }
        }

        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            // Validate it's a PDF
            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            // Generate unique filename
            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                // Store the actual file content
                $filePath = $file->storeAs('resumes', $newName, 'public');

                // Delete old resume file if exists
                if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                    Storage::disk('public')->delete($row->resume);
                }

                $row->resume = $filePath; // Store file path instead of just filename

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Prepare update data
        $updateData = [
            'Date' => isset($rowData['Date']) && !empty($rowData['Date']) ?
                $this->parseDate($rowData['Date']) : null,
            'Name' => $rowData['Name'] ?? null,
            'Email_Address' => $email,
            'Phone_Number' => $phone,
            'Location' => $rowData['Location'] ?? null,
            'Relocation' => $rowData['Relocation'] ?? null,
            'Graduation_Date' => isset($rowData['Graduation Date']) && !empty($rowData['Graduation Date']) ?
                $this->parseDate($rowData['Graduation Date']) : null,
            'Immigration' => $rowData['Immigration'] ?? null,
            'Course' => $rowData['Course'] ?? null,
            'Amount' => isset($rowData['Amount']) ?
                $this->parseAmount($rowData['Amount']) : null,
            'Qualification' => $rowData['Qualification'] ?? null,
            'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
            'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
            'Time_Zone' => $rowData['Time Zone'] ?? null,
            'updated_at' => now(),
        ];

        // Only update resume if it was uploaded
        if ($request->hasFile('resume')) {
            $updateData['resume'] = $row->resume;
        }

        try {
            $row->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Row updated successfully',
                'id' => $row->id,
                'sheet_row_number' => $row->sheet_row_number,
                'resume_path' => !empty($row->resume) ? true : false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fill Full Detail to Save.'
            ]);
        }
    }


    public function adminstore(Request $request)
    {
        $rowData = json_decode($request->input('data'), true);

        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        $user = Auth::user();
        $maxRow = GoogleSheetData::max('sheet_row_number') ?? 0;
        $nextRow = $maxRow + 1;

        $record = new GoogleSheetData();
        $record->sheet_row_number = $nextRow;
        $record->created_by = $user->id . '|admin';

        // Map frontend keys to DB columns
        $columnMap = [
            'Date' => 'Date',
            'Name' => 'Name',
            'Email Address' => 'Email_Address',
            'Phone Number' => 'Phone_Number',
            'Location' => 'Location',
            'Relocation' => 'Relocation',
            'Graduation Date' => 'Graduation_Date',
            'Immigration' => 'Immigration',
            'Course' => 'Course',
            'Amount' => 'Amount',
            'Qualification' => 'Qualification',
            'Exe Remarks' => 'Exe_Remarks',
            '1st Follow Up Remarks' => 'First_Follow_Up_Remarks',
            'Time Zone' => 'Time_Zone',
        ];

        // Temporary storage for checking unique fields
        $email = null;
        $phone = null;

        // Assign values safely
        foreach ($rowData as $key => $val) {
            if (!isset($columnMap[$key])) continue;
            $column = $columnMap[$key];

            if (in_array($column, ['Date', 'Graduation_Date']) && !empty($val)) {
                $val = $this->parseDate($val);
            }

            if ($column === 'Amount' && !empty($val)) {
                $val = $this->parseAmount($val);
            }

            if ($column === 'Email_Address') {
                $email = $val;
            }

            if ($column === 'Phone_Number') {
                $phone = $val;
            }

            $record->$column = $val;
        }

        // --- Check for duplicate Email or Phone ---
        if (!empty($email)) {
            $emailExists = GoogleSheetData::where('Email_Address', $email)->exists();
            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address already exists in records.'
                ]);
            }
        }

        if (!empty($phone)) {
            $phoneExists = GoogleSheetData::where('Phone_Number', $phone)->exists();
            if ($phoneExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number already exists in records.'
                ]);
            }
        }

        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            // Validate it's a PDF
            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                // Store the actual file content
                $filePath = $file->storeAs('resumes', $newName, 'public');
                $record->resume = $filePath; // Store file path
            } catch (\Exception $e) {
                // Continue without resume if upload fails
                $record->resume = null;
            }
        }

        try {
            $record->save();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fill Full Detail to Save.'
            ]);
        }

        return response()->json([
            'success' => true,
            'id' => $record->id,
            'sheet_row_number' => $record->sheet_row_number,
            'resume_path' => !empty($record->resume) ? true : false
        ]);
    }


    // Add a method to serve the PDF files
    public function viewadminResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }

    // Add a method to download the PDF files
    public function downloadadminResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }

    public function senior(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value
        $page = $request->input('page', 1); // ✅ Ensure page input handled

        $userPattern = "%:" . $authUser->id . "|senior";
        $zeroPattern = "%:0|senior";

        $query = GoogleSheetData::where(function ($main) use ($authUser, $userPattern, $zeroPattern) {

            $main->where(function ($q) use ($authUser, $userPattern, $zeroPattern) {

                $q->where(function ($q2) use ($authUser, $userPattern, $zeroPattern) {
                    $q2->where('created_by', $authUser->id . '|senior')
                        ->orWhere('created_by', '0|senior')
                        ->orWhere('created_by', 'LIKE', $userPattern)
                        ->orWhere('created_by', 'LIKE', $zeroPattern);
                })
                    ->whereRaw(
                        "LENGTH(created_by) - LENGTH(REPLACE(created_by, '|senior', '')) = LENGTH('|senior')"
                    );
            })
                ->orWhere('created_by', $authUser->id . '|senior:0|senior');
        })
            // ✅ Transfer remark condition
            ->where(function ($q) {
                $q->whereNull('TransferRemark')
                    ->orWhere('TransferRemark', '');
            })
            // ✅ THIS guarantees ONLY transfers = 1 records are returned
            ->where('transfers', 1);







        // Filter by selected junior
        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
            });
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Changed sorting: order by 'id' descending (like 'Date' desc in junior)
        $results = $query->orderBy('updated_at', 'desc')->get();

        // ✅ Transform after getting all filtered data
        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // ✅ Apply pagination AFTER transformation (like junior)
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current(), 'query' => $request->query()]
        );


        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        // ✅ Handle AJAX pagination and search
        if ($request->ajax()) {
            return view('database.partials.senior_table', ['data' => $pagedData, 'juniorUsers' => $juniorUsers])->render();
        }


        return view('database.senior', ['data' => $pagedData, 'juniorUsers' => $juniorUsers]);
    }


    public function seniorfollow(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value
        $page = $request->input('page', 1); //  Ensure page input handled

        $userPattern = "%:" . $authUser->id . "|senior";
        $zeroPattern = "%:0|senior";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern, $zeroPattern) {
            $q->where(function ($q2) use ($authUser, $userPattern, $zeroPattern) {

                $q2->where('created_by', $authUser->id . '|senior')
                    ->orWhere('created_by', '0|senior')
                    ->orWhere('created_by', 'LIKE', $userPattern)
                    ->orWhere('created_by', 'LIKE', $zeroPattern);
            })
                // EXCLUSION: Do NOT show rows having more than one "|senior"
                ->whereRaw("LENGTH(created_by) - LENGTH(REPLACE(created_by, '|senior', '')) = LENGTH('|senior')");
        })->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 0);

        // Filter by selected junior
        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
            });
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Changed sorting: order by 'id' descending (like 'Date' desc in junior)
        $results = $query->orderBy('updated_at', 'desc')->get();

        // ✅ Transform after getting all filtered data
        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // ✅ Apply pagination AFTER transformation (like junior)
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current(), 'query' => $request->query()]
        );


        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        // ✅ Handle AJAX pagination and search
        if ($request->ajax()) {
            return view('database.partials.seniorfollow_table', ['data' => $pagedData, 'juniorUsers' => $juniorUsers])->render();
        }

        return view('database.seniorfollow', ['data' => $pagedData, 'juniorUsers' => $juniorUsers]);
    }


    public function career(Request $request)
    {
        $authUser = Auth::user();

        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user');
        $page = $request->input('page', 1);

        $query = GoogleSheetData::orderBy('id', 'desc');

        if ($rowId) {
            $query->where('id', $rowId);
        }

        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
            });
        }

        if ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $results = $query->get();

        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        $perPage = 10;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($page, $perPage),
            $transformed->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        $juniorUsers = \App\Models\User::where('is_deleted', 0)
            ->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        if ($request->ajax()) {
            return view(
                'database.partials.career_table',
                ['data' => $pagedData, 'juniorUsers' => $juniorUsers]
            )->render();
        }

        return view('database.career', ['data' => $pagedData, 'juniorUsers' => $juniorUsers]);
    }


    public function seniorcandm(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // ✅ NEW
        $page = $request->input('page', 1);

        // SUBSTRING_INDEX-based filter with second part check
        $query = GoogleSheetData::where(function ($q) use ($authUser) {

            $seniorPart = $authUser->id . '|senior';
            $q->whereRaw("SUBSTRING_INDEX(created_by, ':', 1) = ?", [$seniorPart]);
        })
            ->whereRaw("LENGTH(created_by) - LENGTH(REPLACE(created_by, ':', '')) = 0")
            ->where('transfers', 0);

        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // ✅ APPLY JUNIOR FILTER (NO LOGIC CHANGE)
        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
            });
        }

        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $results = $query->orderBy('updated_at', 'desc')->get();

        // 🔁 Transform forwarded_by
        $transformed = $results->map(function ($item) use ($authUser) {

            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // ✅ Apply pagination AFTER transformation (like junior)
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        if ($request->ajax()) {
            return view('database.partials.seniorcandm_table', ['data' => $pagedData, 'juniorUsers' => $juniorUsers])->render();
        }

        return view('database.seniorcandm',  ['data' => $pagedData, 'juniorUsers' => $juniorUsers]);
    }

    public function senioradmincandm(Request $request)
    {
        // Create a dummy object with id = 32 (without touching your inner logic)
        $authUser = (object)['id' => 32];

        $search = $request->input('search');
        $rowId = $request->input('row_id');

        // SUBSTRING_INDEX-based filter with second part check
        $query = GoogleSheetData::where(function ($q) use ($authUser) {
            $seniorPart = $authUser->id . '|senior';

            $q->whereRaw("SUBSTRING_INDEX(created_by, ':', 1) = ?", [$seniorPart])
                ->whereRaw("
              LENGTH(created_by) - LENGTH(REPLACE(created_by, ':', '')) >= 1
              AND
              SUBSTRING_INDEX(SUBSTRING_INDEX(created_by, ':', 2), ':', -1) LIKE '%|senior'
          ");
        });

        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('Date', 'desc')->paginate(10);

        // Map forwarded_by dynamically for multiple creators
        $data->getCollection()->transform(function ($item) use ($authUser) {

            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);

                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        if ($request->ajax()) {
            return view('database.partials.senior_table', compact('data'))->render();
        }

        return view('database.seniorcandm', compact('data'));
    }


    public function seniormodcandm(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user');
        $page = $request->input('page', 1);

        $juniorPart = $authUser->id . '|senior';

        $query = GoogleSheetData::where(function ($q) use ($juniorPart) {
            // Check first segment is junior
            $q->whereRaw("SUBSTRING_INDEX(created_by, ':', 1) = ?", [$juniorPart]);
        })
            ->where(function ($q) {
                // Check second segment is senior (any ID or 0)
                $q->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(created_by, ':', 2), ':', -1) LIKE '%|senior'");
            })
            ->where('transfers', 0); // ✅ show only transfer = 0

        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|senior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
            });
        }
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $results = $query->orderBy('updated_at', 'desc')->get();

        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';
            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';
                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }
                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current(), 'query' => $request->query()]
        );
        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        if ($request->ajax()) {
            return view('database.partials.seniormodcandm_table', ['data' => $pagedData, 'juniorUsers' => $juniorUsers])->render();
        }

        return view('database.seniormodcandm',  ['data' => $pagedData, 'juniorUsers' => $juniorUsers]);
    }


    public function seniormodcandmfollow(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user');
        $page = $request->input('page', 1);
        $userPattern = "%:" . $authUser->id . "|senior";
        $zeroPattern = "%:0|senior";

        $query = GoogleSheetData::where(function ($main) use ($authUser, $userPattern, $zeroPattern) {

            $main->where(function ($q) use ($authUser, $userPattern, $zeroPattern) {

                $q->where(function ($q2) use ($authUser, $userPattern, $zeroPattern) {
                    $q2->where('created_by', $authUser->id . '|senior')
                        ->orWhere('created_by', 'LIKE', $zeroPattern);
                })
                    ->whereRaw(
                        "LENGTH(created_by) - LENGTH(REPLACE(created_by, '|senior', '')) = LENGTH('|senior')"
                    );
            })
                ->orWhere('created_by', $authUser->id . '|senior:0|senior');
        })
            ->where(function ($q) {
                $q->whereNotNull('TransferRemark')
                    ->where('TransferRemark', '<>', '');
            })
            ->where('transfers', 0);

        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|senior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
            });
        }
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $results = $query->orderBy('updated_at', 'desc')->get();

        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';
            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';
                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }
                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current(), 'query' => $request->query()]
        );
        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        if ($request->ajax()) {
            return view('database.partials.seniormodcandmfollow_table', ['data' => $pagedData, 'juniorUsers' => $juniorUsers])->render();
        }

        return view('database.seniormodcandmfollow',  ['data' => $pagedData, 'juniorUsers' => $juniorUsers]);
    }

    public function seniormod(Request $request)
    {
        $authUser = (object) ['id' => null];
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value
        $page = $request->input('page', 1); // ✅ Ensure page input handled

        $userPattern = "%" . $authUser->id . "|junior";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern) {
            $q->where('created_by', $authUser->id . '|junior')
                ->whereRaw("RIGHT(created_by, LENGTH(?)) = ?", [$authUser->id . '|junior', $authUser->id . '|junior'])
                ->orWhere('created_by', 'LIKE', $userPattern);
        });

        // Filter by selected junior
        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->whereRaw("RIGHT(created_by, LENGTH(?)) = ?", [$juniorUserId . '|junior', $juniorUserId . '|junior']);
            });
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Changed sorting: order by 'id' descending (like 'Date' desc in junior)
        $results = $query->orderBy('updated_at', 'desc')->get();

        // ✅ Transform after getting all filtered data
        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // ✅ Apply pagination AFTER transformation (like junior)
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        // ✅ Handle AJAX pagination and search
        if ($request->ajax()) {
            return view('database.partials.seniormod_table', ['data' => $pagedData, 'juniorUsers' => $juniorUsers])->render();
        }

        return view('database.seniormod',  ['data' => $pagedData, 'juniorUsers' => $juniorUsers]);
    }

    public function seniortra(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user');
        $page = $request->input('page', 1);
        $userPattern = "%:" . $authUser->id . "|junior";
        $zeroPattern = "%:0|senior";

        $query = GoogleSheetData::where(function ($main) use ($authUser, $userPattern, $zeroPattern) {

            $main->where(function ($q) use ($authUser, $userPattern, $zeroPattern) {

                $q->where(function ($q2) use ($authUser, $userPattern, $zeroPattern) {
                    $q2->where('created_by', $authUser->id . '|junior')
                        ->orWhere('created_by', 'LIKE', $zeroPattern);
                })
                    ->whereRaw(
                        "LENGTH(created_by) - LENGTH(REPLACE(created_by, '|senior', '')) = LENGTH('|senior')"
                    );
            })
                ->orWhere('created_by', $authUser->id . '|junior:0|senior');
        })
            ->where(function ($q) {
                $q->whereNull('TransferRemark')
                    ->orWhere('TransferRemark', '');
            })
            ->where('transfers', 0);

        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
            });
        }
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $results = $query->orderBy('updated_at', 'desc')->get();

        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';
            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';
                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }
                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current(), 'query' => $request->query()]
        );
        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);
        if ($request->ajax()) {
            return view('database.partials.seniortra_table', ['data' => $pagedData, 'juniorUsers' => $juniorUsers])->render();
        }
        return view('database.seniortra',  ['data' => $pagedData, 'juniorUsers' => $juniorUsers]);
    }

    public function seniortrafollow(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value
        $page = $request->input('page', 1); // ✅ Ensure page input handled

        $userPattern = "%:" . $authUser->id . "|senior";
        $zeroPattern = "%:0|senior";

        $query = GoogleSheetData::where(function ($outer) use ($authUser, $userPattern, $zeroPattern) {

            $outer->where(function ($main) use ($authUser, $userPattern, $zeroPattern) {

                $main->where(function ($q) use ($authUser, $userPattern, $zeroPattern) {

                    $q->where(function ($q2) use ($authUser, $userPattern, $zeroPattern) {
                        $q2->where('created_by', $authUser->id . '|senior')
                            ->orWhere('created_by', '0|senior')
                            ->orWhere('created_by', 'LIKE', $userPattern)
                            ->orWhere('created_by', 'LIKE', $zeroPattern);
                    })
                        // ❌ Exclude rows having more than one "|senior"
                        ->whereRaw(
                            "LENGTH(created_by) - LENGTH(REPLACE(created_by, '|senior', '')) = LENGTH('|senior')"
                        );
                })

                    ->orWhere('created_by', $authUser->id . '|senior:0|senior');
            });
        })
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1);

        // Filter by selected junior
        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
            });
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Changed sorting: order by 'id' descending (like 'Date' desc in junior)
        $results = $query->orderBy('updated_at', 'desc')->get();

        // ✅ Transform after getting all filtered data
        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // ✅ Apply pagination AFTER transformation (like junior)
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current(), 'query' => $request->query()]
        );


        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        // ✅ Handle AJAX pagination and search
        if ($request->ajax()) {
            return view('database.partials.seniortrafollow_table', ['data' => $pagedData, 'juniorUsers' => $juniorUsers])->render();
        }

        return view('database.seniortrafollow',  ['data' => $pagedData, 'juniorUsers' => $juniorUsers]);
    }


    public function seniorcandmupdate(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // Properly map frontend keys to DB columns
        $updateData = [];

        if (array_key_exists('TransferRemark', $rowData)) {
            $updateData['TransferRemark'] = trim($rowData['TransferRemark']);
        }

        if (array_key_exists('Remark', $rowData)) {
            $updateData['Remark'] = trim($rowData['Remark']);
        }

        if (array_key_exists('1st Follow Up Remarks', $rowData)) {
            $updateData['First_Follow_Up_Remarks'] = $rowData['1st Follow Up Remarks'];
        }


        // if (!isset($updateData['Remark']) || $updateData['Remark'] === '') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Remark field is required before updating.'
        //     ]);
        // }

        // if (!isset($updateData['TransferRemark']) || $updateData['TransferRemark'] === '') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Transfer Remark field is required before updating.'
        //     ]);
        // }

        if (empty($updateData)) {
            return response()->json(['success' => false, 'message' => 'No valid fields to update']);
        }

        try {
            // ✅ Disable timestamps so updated_at is not touched
            $row->timestamps = false;

            // ✅ Force assign and save — avoids fillable restrictions
            foreach ($updateData as $key => $value) {
                $row->$key = $value;
            }

            $row->save();

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
                'updated_fields' => $updateData,
                'id' => $row->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ]);
        }
    }




    public function seniorpaid(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');

        $query = GoogleSheetData::where(function ($q) {
            // Removed user_id|senior check
            // Only keep the accountant part filter
            $q->where(function ($q2) {
                $q2->whereRaw("created_by = '0|accountant'")
                    ->orWhereRaw("created_by LIKE '0|accountant:%'")
                    ->orWhereRaw("created_by LIKE '%:0|accountant'")
                    ->orWhereRaw("created_by LIKE '%:0|accountant:%'");
            });
        });

        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('Date', 'desc')->paginate(10);

        $data->getCollection()->transform(function ($item) use ($authUser) {

            $forwardedBy = '';

            if (!empty($item->created_by)) {
                // Split by ':' to handle multiple forwarded entries
                $entries = explode(':', $item->created_by);

                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                // Join all names for forwarded chain
                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });


        if (request()->ajax()) {
            return view('database.partials.career_table', compact('data'))->render();
        }

        return view('database.seniorpaid', compact('data'));
    }

    public function seniorcon(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');

        $query = GoogleSheetData::where(function ($q) {

            $q->where(function ($q2) {

                // Fetch only rows with created_by containing ":0|senior" at the very end
                $q2->where('created_by', 'LIKE', '%:0|accountant:0|senior')
                    ->orWhere('created_by', 'LIKE', '0|accountant:0|senior')
                    ->orWhere(function ($qq) {
                        // Handle ANY_NUMBER|accountant:0|senior
                        $qq->where('created_by', 'LIKE', '%|accountant:0|senior');
                    });
            });
        });

        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('Date', 'desc')->paginate(10);

        $data->getCollection()->transform(function ($item) use ($authUser) {

            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);

                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        if (request()->ajax()) {
            return view('database.partials.career_table', compact('data'))->render();
        }

        return view('database.seniorcon', compact('data'));
    }


    // -----------------------------
    // AJAX Search Suggestions
    // -----------------------------
    public function seniorSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->query('query');
        $juniorUserId = $request->query('junior_user');

        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        $userPattern = "%:" . $authUser->id . "|senior";
        $zeroPattern = "%:0|senior";

        $results = GoogleSheetData::where(function ($q) use ($authUser, $userPattern, $zeroPattern) {
            $q->where('created_by', $authUser->id . '|senior')
                ->orWhere('created_by', '0|senior')
                ->orWhere('created_by', 'LIKE', $userPattern)
                ->orWhere('created_by', 'LIKE', $zeroPattern);
        })
            ->where(function ($q) use ($query) {
                $q->where('Name', 'LIKE', "%{$query}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
            });

        /* ✅ junior filter SAME as senior() */
        if ($juniorUserId) {
            $results->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', "%{$juniorUserId}|junior%")
                    ->orWhere('created_by', 'LIKE', "%{$juniorUserId}|senior%");
            });
        }

        $results = $results
            ->limit(10)
            ->get([
                'id',
                'sheet_row_number',
                'Name',
                'Email_Address',
                'Phone_Number',
                'Exe_Remarks',
                'created_by'
            ]);

        /* Transform forwarded_by */
        $results = $results->map(function ($item) use ($authUser) {

            $names = [];

            foreach (explode(':', $item->created_by ?? '') as $entry) {

                [$userId, $role] = array_pad(explode('|', $entry), 2, null);

                $roleLabel = match ($role) {
                    'senior' => 'IT Senior Recruiter',
                    'junior' => 'IT Recruiter',
                    default  => $role
                };

                if ($userId == $authUser->id) {
                    $names[] = "SELF ({$userId}) ({$roleLabel})";
                } elseif ($userId == 0) {
                    $names[] = "SYSTEM (0) ({$roleLabel})";
                } else {
                    $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                    $names[] = ($user->name ?? 'Unknown') . " ({$userId}) ({$roleLabel})";
                }
            }

            $item->forwarded_by = implode(' → ', $names);
            return $item;
        });

        return response()->json($results);
    }

    public function seniortrafollowSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->query('query');
        $juniorUserId = $request->query('junior_user');

        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        $userPattern = "%:" . $authUser->id . "|senior";
        $zeroPattern = "%:0|senior";

        $results = GoogleSheetData::where(function ($outer) use ($authUser, $userPattern, $zeroPattern) {

            $outer->where(function ($main) use ($authUser, $userPattern, $zeroPattern) {

                $main->where(function ($q) use ($authUser, $userPattern, $zeroPattern) {

                    $q->where('created_by', $authUser->id . '|senior')
                        ->orWhere('created_by', '0|senior')
                        ->orWhere('created_by', 'LIKE', $userPattern)
                        ->orWhere('created_by', 'LIKE', $zeroPattern);
                })
                    ->whereRaw(
                        "LENGTH(created_by) - LENGTH(REPLACE(created_by, '|senior', '')) = LENGTH('|senior')"
                    );
            })
                ->orWhere('created_by', $authUser->id . '|senior:0|senior');
        })
            ->whereNotNull('TransferRemark')
            ->where('TransferRemark', '!=', '')
            ->where('transfers', 1)

            /* 🔍 Search */
            ->where(function ($q) use ($query) {
                $q->where('Name', 'LIKE', "%{$query}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
            });

        /* ✅ Junior filter (EXACT same as seniortrafollow) */
        if ($juniorUserId) {
            $results->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', "%{$juniorUserId}|junior%")
                    ->orWhere('created_by', 'LIKE', "%{$juniorUserId}|senior%");
            });
        }

        $results = $results
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get([
                'id',
                'sheet_row_number',
                'Name',
                'Email_Address',
                'Phone_Number',
                'Exe_Remarks',
                'created_by'
            ]);

        /* -----------------------------
       Transform forwarded_by
    ----------------------------- */
        $results = $results->map(function ($item) use ($authUser) {

            $names = [];

            foreach (explode(':', $item->created_by ?? '') as $entry) {

                [$userId, $role] = array_pad(explode('|', $entry), 2, null);

                $roleLabel = match ($role) {
                    'senior' => 'IT Senior Recruiter',
                    'junior' => 'IT Recruiter',
                    default  => $role
                };

                if ($userId == $authUser->id) {
                    $names[] = "SELF ({$userId}) ({$roleLabel})";
                } elseif ($userId == 0) {
                    $names[] = "SYSTEM (0) ({$roleLabel})";
                } else {
                    $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                    $names[] = ($user->name ?? 'Unknown') . " ({$userId}) ({$roleLabel})";
                }
            }

            $item->forwarded_by = implode(' → ', $names);
            return $item;
        });

        return response()->json($results);
    }





    public function seniortraSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->input('query');
        $juniorUserId = $request->input('junior_user');

        $results = [];

        if ($query && strlen($query) >= 3) {
            $results = GoogleSheetData::where(function ($q) use ($authUser) {

                $userPattern = "%:" . $authUser->id . "|senior";
                $zeroPattern = "%:0|senior";

                $q->where('created_by', $authUser->id . '|senior')
                    ->orWhere('created_by', '0|senior')
                    ->orWhere('created_by', 'LIKE', $userPattern)
                    ->orWhere('created_by', 'LIKE', $zeroPattern);
            })
                // ✅ APPLY JUNIOR FILTER (SAME AS seniortra)
                ->when($juniorUserId, function ($q) use ($juniorUserId) {
                    $q->where(function ($sub) use ($juniorUserId) {
                        $sub->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                            ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
                    });
                })
                ->where(function ($q) use ($query) {
                    $q->where('Name', 'LIKE', "%{$query}%")
                        ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                        ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get([
                    'id',
                    'sheet_row_number',
                    'Name',
                    'Email_Address',
                    'Phone_Number',
                    'Exe_Remarks',
                    'created_by'
                ]);
        }

        $transformed = collect($results)->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            // Add transformed field
            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        return response()->json($transformed);
    }

    public function seniorupdatemodSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->input('query');
        $juniorUserId = $request->input('junior_user');

        $results = [];

        if ($query && strlen($query) >= 3) {
            $results = GoogleSheetData::where(function ($q) use ($authUser) {

                $userPattern = "%:" . $authUser->id . "|senior";
                $zeroPattern = "%:0|senior";

                $q->where('created_by', $authUser->id . '|senior')
                    ->orWhere('created_by', '0|senior')
                    ->orWhere('created_by', 'LIKE', $userPattern)
                    ->orWhere('created_by', 'LIKE', $zeroPattern);
            })
                // ✅ APPLY JUNIOR FILTER (SAME AS seniortra)
                ->when($juniorUserId, function ($q) use ($juniorUserId) {
                    $q->where(function ($sub) use ($juniorUserId) {
                        $sub->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                            ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
                    });
                })
                ->where(function ($q) use ($query) {
                    $q->where('Name', 'LIKE', "%{$query}%")
                        ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                        ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get([
                    'id',
                    'sheet_row_number',
                    'Name',
                    'Email_Address',
                    'Phone_Number',
                    'Exe_Remarks',
                    'created_by'
                ]);
        }

        $transformed = collect($results)->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            // Add transformed field
            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        return response()->json($transformed);
    }


    public function seniorfollowSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->input('query');
        $juniorUserId = $request->input('junior_user');

        $results = [];

        if ($query && strlen($query) >= 3) {
            $results = GoogleSheetData::where(function ($q) use ($authUser) {

                $userPattern = "%:" . $authUser->id . "|senior";
                $zeroPattern = "%:0|senior";

                $q->where('created_by', $authUser->id . '|senior')
                    ->orWhere('created_by', '0|senior')
                    ->orWhere('created_by', 'LIKE', $userPattern)
                    ->orWhere('created_by', 'LIKE', $zeroPattern);
            })
                // ✅ APPLY JUNIOR FILTER (SAME AS seniortra)
                ->when($juniorUserId, function ($q) use ($juniorUserId) {
                    $q->where(function ($sub) use ($juniorUserId) {
                        $sub->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                            ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
                    });
                })
                ->where(function ($q) use ($query) {
                    $q->where('Name', 'LIKE', "%{$query}%")
                        ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                        ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get([
                    'id',
                    'sheet_row_number',
                    'Name',
                    'Email_Address',
                    'Phone_Number',
                    'Exe_Remarks',
                    'created_by'
                ]);
        }

        $transformed = collect($results)->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            // Add transformed field
            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        return response()->json($transformed);
    }

    public function seniormodcandmSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->input('query');
        $juniorUserId = $request->input('junior_user');

        $results = [];

        if ($query && strlen($query) >= 3) {
            $results = GoogleSheetData::where(function ($q) use ($authUser) {

                $userPattern = "%:" . $authUser->id . "|senior";
                $zeroPattern = "%:0|senior";

                $q->where('created_by', $authUser->id . '|senior')
                    ->orWhere('created_by', '0|senior')
                    ->orWhere('created_by', 'LIKE', $userPattern)
                    ->orWhere('created_by', 'LIKE', $zeroPattern);
            })
                // ✅ APPLY JUNIOR FILTER (SAME AS seniortra)
                ->when($juniorUserId, function ($q) use ($juniorUserId) {
                    $q->where(function ($sub) use ($juniorUserId) {
                        $sub->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                            ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|senior%');
                    });
                })
                ->where(function ($q) use ($query) {
                    $q->where('Name', 'LIKE', "%{$query}%")
                        ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                        ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get([
                    'id',
                    'sheet_row_number',
                    'Name',
                    'Email_Address',
                    'Phone_Number',
                    'Exe_Remarks',
                    'created_by'
                ]);
        }

        $transformed = collect($results)->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            // Add transformed field
            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        return response()->json($transformed);
    }

    public function seniormodcandmfollowSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->query('query');
        $juniorUserId = $request->query('junior_user');

        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        $results = GoogleSheetData::where(function ($q) use ($authUser) {

            $q->where('created_by', $authUser->id . '|senior')
                ->orWhere('created_by', '0|senior')
                ->orWhere('created_by', 'LIKE', '%:' . $authUser->id . '|senior')
                ->orWhere('created_by', 'LIKE', '%:0|senior');
        })
            ->when($juniorUserId, function ($q) use ($juniorUserId) {
                $q->where(function ($sub) use ($juniorUserId) {
                    $sub->where('created_by', 'LIKE', "%{$juniorUserId}|junior%")
                        ->orWhere('created_by', 'LIKE', "%{$juniorUserId}|senior%");
                });
            })
            ->where(function ($q) use ($query) {
                $q->where('Name', 'LIKE', "%{$query}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get([
                'id',
                'sheet_row_number',
                'Name',
                'Email_Address',
                'Phone_Number',
                'Exe_Remarks',
                'created_by'
            ]);

        // Transform forwarded_by
        $results->each(function ($item) use ($authUser) {

            $names = [];

            foreach (explode(':', $item->created_by ?? '') as $entry) {
                [$uid, $role] = array_pad(explode('|', $entry), 2, null);

                if ($uid == $authUser->id) {
                    $names[] = "SELF ({$uid}) ({$role})";
                } elseif ($uid == 0) {
                    $names[] = "SYSTEM (0) ({$role})";
                } else {
                    $user = \App\Models\User::where('is_deleted', 0)->find($uid);
                    $names[] = ($user?->name ?? 'Unknown') . " ({$uid}) ({$role})";
                }
            }

            $item->forwarded_by = implode(' → ', $names);
        });

        return response()->json($results);
    }


    public function seniorpaidSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->input('query');

        $results = [];

        if ($query && strlen($query) >= 3) {
            $results = GoogleSheetData::where(function ($q) use ($authUser) {
                $userPattern = "%:" . $authUser->id . "|senior";
                $zeroPattern = "%:0|accountant";

                $q->where('created_by', $authUser->id . '|senior')
                    ->orWhere('created_by', '0|accountant')
                    ->orWhere('created_by', 'LIKE', $userPattern)
                    ->orWhere('created_by', 'LIKE', $zeroPattern);
            })
                ->where(function ($q) use ($query) {
                    $q->where('Name', 'LIKE', "%{$query}%")
                        ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                        ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get(['id', 'sheet_row_number', 'Name', 'Email_Address', 'Phone_Number', 'Exe_Remarks', 'created_by']);
        }
        // ✅ Transform the forwarded_by column like in senior()
        $transformed = collect($results)->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            // Add transformed field
            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        return response()->json($transformed);
    }

    public function careerSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->input('query');

        $results = [];

        // Search only when query length >= 3
        if ($query && strlen($query) >= 3) {
            $results = GoogleSheetData::where(function ($q) use ($query) {
                $q->where('Name', 'LIKE', "%{$query}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
            })
                ->limit(10)
                ->get([
                    'id',
                    'sheet_row_number',
                    'Name',
                    'Email_Address',
                    'Phone_Number',
                    'Exe_Remarks',
                    'created_by'
                ]);
        }

        // Transform forwarded_by field (same logic as before)
        $transformed = collect($results)->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        return response()->json($transformed);
    }



    public function seniorSuggestionsmod(Request $request)
    {
        $authUser = (object) ['id' => null];
        $query = $request->input('query');

        $results = [];

        if ($query && strlen($query) >= 3) {
            $results = GoogleSheetData::where(function ($q) use ($authUser) {
                $userPattern = "%" . $authUser->id . "|junior";

                $q->where('created_by', $authUser->id . '|junior')
                    ->whereRaw("RIGHT(created_by, LENGTH(?)) = ?", [$authUser->id . '|junior', $authUser->id . '|junior'])
                    ->orWhere('created_by', 'LIKE', $userPattern);
            })
                ->where(function ($q) use ($query) {
                    $q->where('Name', 'LIKE', "%{$query}%")
                        ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                        ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get(['id', 'sheet_row_number', 'Name', 'Email_Address', 'Phone_Number', 'Exe_Remarks', 'created_by']);
        }

        // ✅ Transform the forwarded_by column like in senior()
        $transformed = collect($results)->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            // Add transformed field
            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        return response()->json($transformed);
    }
    public function seniorcandmSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->query('query');
        $juniorUserId = $request->query('junior_user');

        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        $results = GoogleSheetData::where(function ($q) use ($authUser) {
            $q->whereRaw("SUBSTRING_INDEX(created_by, ':', 1) = ?", [$authUser->id . '|senior']);
        })
            ->whereRaw("LENGTH(created_by) - LENGTH(REPLACE(created_by, ':', '')) = 0")
            ->where('transfers', 0)

            // ✅ JUNIOR FILTER (OPTIONAL)
            ->when($juniorUserId, function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', "%{$juniorUserId}|junior%");
            })

            ->where(function ($q) use ($query) {
                $q->where('Name', 'LIKE', "%{$query}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
            })

            ->limit(10)
            ->get([
                'id',
                'sheet_row_number',
                'Name',
                'Email_Address',
                'Phone_Number',
                'created_by'
            ]);

        // 🔁 forwarded_by formatting
        $results->each(function ($item) use ($authUser) {

            $names = [];

            foreach (explode(':', $item->created_by) as $entry) {
                [$uid, $role] = array_pad(explode('|', $entry), 2, null);

                if ($uid == $authUser->id) {
                    $names[] = "SELF ({$uid}) ({$role})";
                } elseif ($uid == 0) {
                    $names[] = "SYSTEM (0) ({$role})";
                } else {
                    $user = \App\Models\User::find($uid);
                    $names[] = ($user?->name ?? 'Unknown') . " ({$uid}) ({$role})";
                }
            }

            $item->forwarded_by = implode(' → ', $names);
        });

        return response()->json($results);
    }


    public function juniorSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $queryText = $request->input('query');
        $juniorUserId = $request->input('junior_user');

        if (!$queryText || strlen($queryText) < 3) {
            return response()->json([]);
        }

        $userPattern = "%:" . $authUser->id . "|junior";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern) {
            $q->where(function ($q2) use ($authUser, $userPattern) {
                $q2->where('created_by', $authUser->id . '|junior')
                    ->orWhere('created_by', 'LIKE', $userPattern);
            })
                ->whereRaw(
                    "RIGHT(created_by, LENGTH(?)) = ?",
                    [$authUser->id . '|junior', $authUser->id . '|junior']
                );
        })
            ->where('transfers', '!=', 1);

        // ✅ Apply junior filter (same as junior())
        if ($juniorUserId) {
            $query->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
        }

        // ✅ Apply search
        $results = $query->where(function ($q) use ($queryText) {
            $q->where('Name', 'LIKE', "%{$queryText}%")
                ->orWhere('Email_Address', 'LIKE', "%{$queryText}%")
                ->orWhere('Phone_Number', 'LIKE', "%{$queryText}%");
        })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get([
                'id',
                'sheet_row_number',
                'Name',
                'Email_Address',
                'Phone_Number',
                'Exe_Remarks',
                'created_by'
            ]);

        // ✅ SAME forwarded_by transformation
        $transformed = $results->map(function ($item) use ($authUser) {
            $entries = explode(':', $item->created_by);
            $names = [];

            foreach ($entries as $entry) {
                $parts = explode('|', $entry);
                $userId = $parts[0] ?? null;
                $role   = $parts[1] ?? 'unknown';

                if ($userId == $authUser->id) {
                    $label = $role === 'senior' ? 'IT Senior Recruiter' : 'IT Recruiter';
                    $names[] = "SELF ({$userId}) ({$label})";
                } elseif ($userId == 0) {
                    $label = $role === 'senior' ? 'IT Senior Recruiter' : 'IT Recruiter';
                    $names[] = "SYSTEM (0) ({$label})";
                } else {
                    $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                    $name = $user ? $user->name : 'Unknown';
                    $label = $role === 'senior' ? 'IT Senior Recruiter' : 'IT Recruiter';
                    $names[] = "{$name} ({$userId}) ({$label})";
                }
            }

            $item->forwarded_by = implode(' → ', $names);
            return $item;
        });

        return response()->json($transformed);
    }

    public function juniorrejSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $queryText = $request->input('query');
        $juniorUserId = $request->input('junior_user');

        if (!$queryText || strlen($queryText) < 3) {
            return response()->json([]);
        }

        $userPattern = "%:" . $authUser->id . "|junior";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern) {
            $q->where(function ($q2) use ($authUser, $userPattern) {
                $q2->where('created_by', $authUser->id . '|junior')
                    ->orWhere('created_by', 'LIKE', $userPattern);
            })
                ->whereRaw(
                    "RIGHT(created_by, LENGTH(?)) = ?",
                    [$authUser->id . '|junior', $authUser->id . '|junior']
                );
        })
            ->where('transfers', '!=', 1)->where('rejected', 1);

        // ✅ Apply junior filter (same as junior())
        if ($juniorUserId) {
            $query->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
        }

        // ✅ Apply search
        $results = $query->where(function ($q) use ($queryText) {
            $q->where('Name', 'LIKE', "%{$queryText}%")
                ->orWhere('Email_Address', 'LIKE', "%{$queryText}%")
                ->orWhere('Phone_Number', 'LIKE', "%{$queryText}%");
        })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get([
                'id',
                'sheet_row_number',
                'Name',
                'Email_Address',
                'Phone_Number',
                'Exe_Remarks',
                'created_by'
            ]);

        // ✅ SAME forwarded_by transformation
        $transformed = $results->map(function ($item) use ($authUser) {
            $entries = explode(':', $item->created_by);
            $names = [];

            foreach ($entries as $entry) {
                $parts = explode('|', $entry);
                $userId = $parts[0] ?? null;
                $role   = $parts[1] ?? 'unknown';

                if ($userId == $authUser->id) {
                    $label = $role === 'senior' ? 'IT Senior Recruiter' : 'IT Recruiter';
                    $names[] = "SELF ({$userId}) ({$label})";
                } elseif ($userId == 0) {
                    $label = $role === 'senior' ? 'IT Senior Recruiter' : 'IT Recruiter';
                    $names[] = "SYSTEM (0) ({$label})";
                } else {
                    $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                    $name = $user ? $user->name : 'Unknown';
                    $label = $role === 'senior' ? 'IT Senior Recruiter' : 'IT Recruiter';
                    $names[] = "{$name} ({$userId}) ({$label})";
                }
            }

            $item->forwarded_by = implode(' → ', $names);
            return $item;
        });

        return response()->json($transformed);
    }


    public function juniorcandmSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $queryText = $request->input('query');
        $juniorUserId = $request->input('junior_user');

        if (!$queryText || strlen($queryText) < 3) {
            return response()->json([]);
        }

        $juniorPart = $authUser->id . '|junior';

        $query = GoogleSheetData::where(function ($q) use ($juniorPart) {
            // ✅ EXACT same logic as juniorcandm()
            $q->whereRaw(
                "SUBSTRING_INDEX(created_by, ':', 1) = ?",
                [$juniorPart]
            );
        })
            ->where(function ($q) {
                // ✅ second segment must be senior
                $q->whereRaw(
                    "SUBSTRING_INDEX(SUBSTRING_INDEX(created_by, ':', 2), ':', -1) LIKE '%|senior'"
                );
            })
            ->where('transfers', 0); // ✅ EXACT match

        // ✅ Same junior dropdown filter
        if ($juniorUserId) {
            $query->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
        }

        // ✅ Same search logic
        $results = $query->where(function ($q) use ($queryText) {
            $q->where('Name', 'LIKE', "%{$queryText}%")
                ->orWhere('Email_Address', 'LIKE', "%{$queryText}%")
                ->orWhere('Phone_Number', 'LIKE', "%{$queryText}%");
        })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get([
                'id',
                'sheet_row_number',
                'Name',
                'Email_Address',
                'Phone_Number',
                'Exe_Remarks',
                'created_by'
            ]);

        // ✅ SAME transform logic
        $transformed = $results->map(function ($item) use ($authUser) {
            $entries = explode(':', $item->created_by);
            $names = [];

            foreach ($entries as $entry) {
                $parts = explode('|', $entry);
                $userId = $parts[0] ?? null;
                $role   = $parts[1] ?? 'unknown';

                if ($userId == $authUser->id) {
                    $label = $role === 'senior'
                        ? 'IT Senior Recruiter'
                        : 'IT Recruiter';
                    $names[] = "SELF ({$userId}) ({$label})";
                } elseif ($userId == 0) {
                    $label = $role === 'senior'
                        ? 'IT Senior Recruiter'
                        : 'IT Recruiter';
                    $names[] = "SYSTEM (0) ({$label})";
                } else {
                    $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                    $name = $user ? $user->name : 'Unknown';
                    $label = $role === 'senior'
                        ? 'IT Senior Recruiter'
                        : 'IT Recruiter';
                    $names[] = "{$name} ({$userId}) ({$label})";
                }
            }

            $item->forwarded_by = implode(' → ', $names);
            return $item;
        });

        return response()->json($transformed);
    }

    public function juniortraSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $queryText = $request->input('query');
        $juniorUserId = $request->input('junior_user');

        if (!$queryText || strlen($queryText) < 3) {
            return response()->json([]);
        }

        $juniorPart = $authUser->id . '|junior';

        $query = GoogleSheetData::where(function ($q) use ($juniorPart) {
            // ✅ EXACT same first segment logic
            $q->whereRaw(
                "SUBSTRING_INDEX(created_by, ':', 1) = ?",
                [$juniorPart]
            );
        })
            ->where(function ($q) {
                // ✅ EXACT same second segment logic
                $q->whereRaw(
                    "SUBSTRING_INDEX(SUBSTRING_INDEX(created_by, ':', 2), ':', -1) LIKE '%|senior'"
                );
            })
            ->where('transfers', 1); // ✅ MUST be 1 (same as juniortra)

        // ✅ Same junior dropdown filter
        if ($juniorUserId) {
            $query->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
        }

        // ✅ Same search logic
        $results = $query->where(function ($q) use ($queryText) {
            $q->where('Name', 'LIKE', "%{$queryText}%")
                ->orWhere('Email_Address', 'LIKE', "%{$queryText}%")
                ->orWhere('Phone_Number', 'LIKE', "%{$queryText}%");
        })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get([
                'id',
                'sheet_row_number',
                'Name',
                'Email_Address',
                'Phone_Number',
                'Exe_Remarks',
                'created_by'
            ]);

        // ✅ SAME transform logic
        $transformed = $results->map(function ($item) use ($authUser) {
            $entries = explode(':', $item->created_by);
            $names = [];

            foreach ($entries as $entry) {
                $parts = explode('|', $entry);
                $userId = $parts[0] ?? null;
                $role   = $parts[1] ?? 'unknown';

                if ($userId == $authUser->id) {
                    $label = $role === 'senior'
                        ? 'IT Senior Recruiter'
                        : 'IT Recruiter';
                    $names[] = "SELF ({$userId}) ({$label})";
                } elseif ($userId == 0) {
                    $label = $role === 'senior'
                        ? 'IT Senior Recruiter'
                        : 'IT Recruiter';
                    $names[] = "SYSTEM (0) ({$label})";
                } else {
                    $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                    $name = $user ? $user->name : 'Unknown';
                    $label = $role === 'senior'
                        ? 'IT Senior Recruiter'
                        : 'IT Recruiter';
                    $names[] = "{$name} ({$userId}) ({$label})";
                }
            }

            $item->forwarded_by = implode(' → ', $names);
            return $item;
        });

        return response()->json($transformed);
    }

    // -----------------------------
    // AJAX Search Suggestions
    // -----------------------------
    public function accountantSuggestions(Request $request)
    {
        $authUser = Auth::user();
        $query = $request->input('query');

        $results = [];

        if ($query && strlen($query) >= 3) {
            $results = GoogleSheetData::where(function ($q) use ($authUser) {
                $userPattern = "%:" . $authUser->id . "|senior";
                $zeroPattern = "%:0|senior";

                $q->where('created_by', $authUser->id . '|senior')
                    ->orWhere('created_by', '0|senior')
                    ->orWhere('created_by', 'LIKE', $userPattern)
                    ->orWhere('created_by', 'LIKE', $zeroPattern);
            })
                ->where(function ($q) use ($query) {
                    $q->where('Name', 'LIKE', "%{$query}%")
                        ->orWhere('Email_Address', 'LIKE', "%{$query}%")
                        ->orWhere('Phone_Number', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get(['id', 'Name', 'Email_Address', 'Phone_Number']);
        }

        return response()->json($results);
    }

    public function seniorfetch(Request $request)
    {
        $request->validate([
            'sheet_link' => 'required|url'
        ]);

        // Extract spreadsheet ID
        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $request->sheet_link, $matches);
        $spreadsheetId = $matches[1] ?? null;

        if (!$spreadsheetId) {
            return back()->with('error', 'Invalid Google Sheet link');
        }

        // Fetch CSV
        $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";
        $csvData = @file_get_contents($csvUrl);

        if ($csvData === false) {
            return back()->with('error', 'Unable to fetch Google Sheet (maybe private?)');
        }

        $rows = array_map('str_getcsv', explode("\n", trim($csvData)));
        $header = array_shift($rows); // first row as column headers

        $rowIndex = 2;
        $user = Auth::user();

        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;
            if (count($row) !== count($header)) continue;

            $rowData = array_combine($header, $row);

            // Map CSV headers to database columns
            $mappedData = [
                'sheet_row_number' => $rowIndex,
                'Date' => isset($rowData['Date']) ? \Carbon\Carbon::createFromFormat('m/d/Y', $rowData['Date'])->format('Y-m-d') : null,
                'Name' => $rowData['Name'] ?? null,
                'Email_Address' => $rowData['Email Address'] ?? null,
                'Phone_Number' => $rowData['Phone Number'] ?? null,
                'Location' => $rowData['Location'] ?? null,
                'Relocation' => $rowData['Relocation'] ?? null,
                'Graduation_Date' => isset($rowData['Graduation Date']) ? \Carbon\Carbon::createFromFormat('m/d/Y', $rowData['Graduation Date'])->format('Y-m-d') : null,
                'Immigration' => $rowData['Immigration'] ?? null,
                'Course' => $rowData['Course'] ?? null,
                'Amount' => isset($rowData['Amount']) ? (float) str_replace(['$', ','], '', $rowData['Amount']) : null,
                'Qualification' => $rowData['Qualification'] ?? null,
                'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
                'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
                'Time_Zone' => $rowData['Time Zone'] ?? null,
                'View' => $rowData['View'] ?? null,
                'created_by' => "{$user->id}|{$user->role}",
            ];

            GoogleSheetData::updateOrCreate(
                ['sheet_row_number' => $rowIndex],
                $mappedData
            );

            $rowIndex++;
        }

        return redirect()->route('google.sheet.senior')->with('success', 'Data fetched successfully!');
    }

    public function seniorupdate(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? $row->Email_Address;
        $phone = $rowData['Phone Number'] ?? $row->Phone_Number;
        $name  = $rowData['Name'] ?? $row->Name;
        $date  = $rowData['Date'] ?? $row->Date;

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required.'
            ]);
        }

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.'
            ]);
        }
        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            // Allowed Word MIME types
            $allowed = [
                'application/pdf',
                'application/msword', // .doc
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            ];

            if (!in_array($file->getMimeType(), $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only PDF or Word files (.pdf, .doc, .docx) are allowed'
                ]);
            }

            // Generate unique filename
            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                // Store the actual file content
                $filePath = $file->storeAs('resumes', $newName, 'public');

                // Delete old resume file if exists
                if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                    Storage::disk('public')->delete($row->resume);
                }

                $row->resume = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }
        // --- Prepare update data with null defaults for empty fields ---
        $updateData = [
            'Date' => !empty($rowData['Date']) ? $this->parseDate($rowData['Date']) : null,
            'Name' => $rowData['Name'] ?? null,
            'Email_Address' => $email, // keep original email
            'Phone_Number' => $phone,  // keep original phone
            'Location' => $rowData['Location'] ?? null,
            'Remark' => $rowData['Remark'] ?? null,
            'TransferRemark' => $rowData['TransferRemark'] ?? null,
            'RejectedRemark' => $rowData['RejectedRemark'] ?? null,
            'Relocation' => $rowData['Relocation'] ?? null,
            'Graduation_Date' => !empty($rowData['Graduation Date']) ? $this->parseDate($rowData['Graduation Date']) : null,
            'Immigration' => $rowData['Immigration'] ?? null,
            'Course' => $rowData['Course'] ?? null,
            'Amount' => isset($rowData['Amount']) && $rowData['Amount'] !== '' ? $this->parseAmount($rowData['Amount']) : 469, // ✅ default 469
            'Qualification' => $rowData['Qualification'] ?? null,
            'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
            'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
            'Time_Zone' => $rowData['Time Zone'] ?? null,
            'updated_at' => now(),
        ];

        // Only update resume if it was uploaded
        if ($request->hasFile('resume')) {
            $updateData['resume'] = $row->resume;
        }

        // Start with existing created_by value
        $updateData['created_by'] = $row->created_by;

        if (isset($rowData['Exe Remarks'])) {
            $exeRemark = $rowData['Exe Remarks'];

            if ($exeRemark === 'Ready To Pay') {
                $authUser = Auth::user();

                // Replace "0|senior" with "auth_id|senior:0|accountant"
                if (preg_match('/0\|senior$/', $updateData['created_by'])) {
                    $updateData['created_by'] = preg_replace(
                        '/0\|senior$/',
                        $authUser->id . '|senior:0|accountant',
                        $updateData['created_by']
                    );
                }

                // Ensure ":0|accountant" exists at the end if missing
                if (strpos($updateData['created_by'], ':0|accountant') === false) {
                    $updateData['created_by'] .= ':0|accountant';
                }
            } elseif ($exeRemark === 'Called & Mailed') {

                $tag = $id . '|senior';
                $zerotag = '0|senior';

                // Get the last segment after the last colon
                $parts = explode(':', $updateData['created_by']);
                $lastPart = end($parts);

                // Append only if the last part exactly matches the tag
                if ($lastPart === $tag) {
                    $updateData['created_by'] .= ':' . $zerotag;
                }
            } else {
                // For all other remarks, apply "Revert To Junior" logic
                // Match any integer followed by "|junior"
                if (preg_match('/(\d+)\|junior/', $updateData['created_by'], $matches)) {
                    $juniorId = $matches[1]; // Extract the integer
                    $tag = $juniorId . '|junior';

                    // Append only if tag already exists in created_by
                    if (strpos($updateData['created_by'], $tag) !== false) {
                        $updateData['created_by'] .= ':' . $tag;
                    }
                }

                // Replace "0|senior" with actual senior ID (only if it ends with 0|senior)
                if (preg_match('/0\|senior$/', $updateData['created_by'])) {
                    $updateData['created_by'] = preg_replace(
                        '/0\|senior$/',
                        $id . '|senior',
                        $updateData['created_by']
                    );
                }
            }
        }

        // === Append remark if Exe Remarks changed ===
        $oldExeRemarks = $row->Exe_Remarks;

        $updatedBy = Auth::user()->name;
        $updatedAt = now()->format('d-m-Y H:i');

        if (
            isset($rowData['Exe Remarks']) &&
            $rowData['Exe Remarks'] !== $oldExeRemarks
        ) {
            $newRemarkEntry = "{$rowData['Exe Remarks']} | Updated by {$updatedBy} on {$updatedAt}";

            // Append to existing remark (keep history)
            $existingRemark =
                $rowData['Remark']
                ?? $row->Remark
                ?? '';

            $updateData['Remark'] = trim(
                $existingRemark
                    ? $existingRemark . PHP_EOL . $newRemarkEntry
                    : $newRemarkEntry
            );
        }

        // === Append RejectedRemark like Remark (keep history) ===
        $oldRejectedRemark = $row->RejectedRemark ?? '';

        if (
            isset($rowData['RejectedRemark']) &&
            $rowData['RejectedRemark'] !== '' &&
            $rowData['RejectedRemark'] !== $oldRejectedRemark
        ) {
            // RejectedRemark changed
            $newRejectedEntry = "{$rowData['RejectedRemark']} | Updated by {$updatedBy} on {$updatedAt}";

            $updateData['RejectedRemark'] = trim(
                $oldRejectedRemark
                    ? $oldRejectedRemark . PHP_EOL . $newRejectedEntry
                    : $newRejectedEntry
            );
        }

        // === Append TransferRemark like Remark (keep history) ===
        $oldTransferRemark = $row->TransferRemark ?? '';

        if (
            isset($rowData['TransferRemark']) &&
            $rowData['TransferRemark'] !== '' &&
            $rowData['TransferRemark'] !== $oldTransferRemark
        ) {
            // TransferRemark changed
            $newTransferEntry = "{$rowData['TransferRemark']} | Updated by {$updatedBy} on {$updatedAt}";

            $updateData['TransferRemark'] = trim(
                $oldTransferRemark
                    ? $oldTransferRemark . PHP_EOL . $newTransferEntry
                    : $newTransferEntry
            );
        }



        foreach ($updateData as $key => $value) {
            if ($value === '' && !in_array($key, ['Email_Address', 'Name', 'Date', 'Amount'])) {
                $updateData[$key] = null;
            }
        }

        try {
            $row->update($updateData);
            $user = Auth::user();
            $mailMessage = 'No email sent.';
            $name = $rowData['Name'] ?? null;
            $amount = isset($rowData['Amount']) ? $this->parseAmount($rowData['Amount']) : $row->Amount;

            // --- Send email if Exe_Remarks is "Called & Mailed" ---
            if (isset($rowData['Exe Remarks']) && $rowData['Exe Remarks'] === 'Called & Mailed' && !empty($email)) {
                try {
                    $smtp = SmtpSetting::where('user_id', $user->id)->first();
                    if (!$smtp) {
                        $mailMessage = 'SMTP settings not found.';
                    } else {
                        // Configure mailer dynamically (same as test() method)
                        config([
                            'mail.mailers.smtp.transport' => $smtp->mailer,
                            'mail.mailers.smtp.host' => $smtp->host,
                            'mail.mailers.smtp.port' => $smtp->port,
                            'mail.mailers.smtp.username' => $smtp->username,
                            'mail.mailers.smtp.password' => decrypt($smtp->password),
                            'mail.mailers.smtp.encryption' => $smtp->encryption,
                            'mail.from.address' => $smtp->from_address,
                            'mail.from.name' => $smtp->from_name,
                        ]);

                        // --- Fetch Email Template from Database ---
                        $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                        if ($template) {
                            $subject = $template->subject;
                            $messageBody = $template->body;
                        } else {
                            // Fallback if template not found
                            $subject = "Unlock Career Stability with Fortune 500 Projects !";
                            $messageBody =
                                "Hi {$name},\n\n" .
                                "I hope this message finds you well.\n\n" .
                                "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                                "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                                "What Makes Synergie Different?\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                                "Here’s what you can expect when you join our community:\n\n" .
                                "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                                "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                                "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                                "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                                "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                                "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                                "More Than a Paycheck — A Path to Prosperity\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                                "                  - Full-Time Roles: \$40–\$50/hr\n" .
                                "                  - Part-Time Roles: \$15–\$25/hr\n" .
                                "                  - Paid Internships available\n" .
                                "                  - 15% Salary Raise every 6 months based on performance\n" .
                                "                  - 12 Days Paid Vacation annually\n" .
                                "                  - Relocation Assistance for client deployments\n\n" .
                                "Comprehensive Benefits That Put You First\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                                "                  - Health, Dental & Vision Insurance\n" .
                                "                  - Short- & Long-Term Disability Insurance\n" .
                                "                  - Life Insurance & 401(k) Retirement Plan\n" .
                                "                  - Legal & Immigration Support\n" .
                                "                  - Tax Assistance & Transparent Payroll\n" .
                                "                  - Workers’ Compensation—your safety is our priority\n\n" .
                                "Support Tailored for International Talent\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                                "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                                "                  - Full STEM Extension & OPT/CPT Support\n" .
                                "                  - H1B Sponsorship after project onboarding\n" .
                                "                  - Relocation & Immigration Documentation\n" .
                                "                  - Ongoing Green Card Processing Assistance\n\n" .
                                "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                                "What You’ll Gain:\n\n" .
                                "                  - Live Zoom sessions & recorded expert sessions\n" .
                                "                  - Real-time project simulations & hands-on assignments\n" .
                                "                  - One-on-one resume branding & mock interviews\n" .
                                "                  - Global Certificate of Completion & recruiter access\n" .
                                "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                                "Let’s Take the First Step Together\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                                "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                                "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                                "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                                "Visit Our Website: https://www.synergiesystems.com/";
                        }

                        // --- Send Email (No Template Logic Changed) ---
                        Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                            $message->from($smtp->from_address, $smtp->from_name)
                                ->to($email)
                                ->subject($subject);
                        });

                        $mailMessage = "Email sent successfully to {$email}!";
                    }
                } catch (\Exception $e) {
                    $mailMessage = 'Failed to send email: ' . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Row updated successfully',
                'id' => $row->id,
                'sheet_row_number' => $row->sheet_row_number,
                'resume_path' => !empty($row->resume) ? true : false,
                'mail_message' => $mailMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fill Full Detail to Save.'
            ]);
        }
    }

    public function seniorupdatemod(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? $row->Email_Address;
        $phone = $rowData['Phone Number'] ?? $row->Phone_Number;
        $name  = $rowData['Name'] ?? $row->Name;
        $date  = $rowData['Date'] ?? $row->Date;

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required.'
            ]);
        }

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.'
            ]);
        }



        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            // Allowed Word MIME types
            $allowed = [
                'application/pdf',
                'application/msword', // .doc
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            ];

            if (!in_array($file->getMimeType(), $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only PDF or Word files (.pdf, .doc, .docx) are allowed'
                ]);
            }

            // Generate unique filename
            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                // Store the actual file content
                $filePath = $file->storeAs('resumes', $newName, 'public');

                // Delete old resume file if exists
                if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                    Storage::disk('public')->delete($row->resume);
                }

                $row->resume = $filePath; // Store file path instead of just filename

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // --- Prepare update data with null defaults for empty fields ---
        $updateData = [
            'Date' => !empty($rowData['Date']) ? $this->parseDate($rowData['Date']) : null,
            'Name' => $rowData['Name'] ?? null,
            'Email_Address' => $email, // keep original email
            'Phone_Number' => $phone,  // keep original phone
            'Location' => $rowData['Location'] ?? null,
            'Remark' => $rowData['Remark'] ?? null,
            'TransferRemark' => $rowData['TransferRemark'] ?? null,
            'Relocation' => $rowData['Relocation'] ?? null,
            'Graduation_Date' => !empty($rowData['Graduation Date']) ? $this->parseDate($rowData['Graduation Date']) : null,
            'Immigration' => $rowData['Immigration'] ?? null,
            'Course' => $rowData['Course'] ?? null,
            'Amount' => isset($rowData['Amount']) && $rowData['Amount'] !== '' ? $this->parseAmount($rowData['Amount']) : 469, // ✅ default 469
            'Qualification' => $rowData['Qualification'] ?? null,
            'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
            'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
            'Time_Zone' => $rowData['Time Zone'] ?? null,
            'updated_at' => now(),
        ];

        // Only update resume if it was uploaded
        if ($request->hasFile('resume')) {
            $updateData['resume'] = $row->resume;
        }

        // Start with existing created_by value
        $updateData['created_by'] = $row->created_by;

        if (isset($rowData['Exe Remarks'])) {
            $exeRemark = $rowData['Exe Remarks'];

            if ($exeRemark === 'Ready To Pay') {
                $authUser = Auth::user();

                // Ensure ":0|accountant" exists at the end if missing
                if (strpos($updateData['created_by'], ':0|senior') === false) {
                    $updateData['created_by'] .= ':0|senior';
                }

                // Replace "0|senior" with "auth_id|senior:0|accountant"
                if (preg_match('/0\|senior$/', $updateData['created_by'])) {
                    $updateData['created_by'] = preg_replace(
                        '/0\|senior$/',
                        $authUser->id . '|senior:0|accountant',
                        $updateData['created_by']
                    );
                }

                // Ensure ":0|accountant" exists at the end if missing
                if (strpos($updateData['created_by'], ':0|accountant') === false) {
                    $updateData['created_by'] .= ':0|accountant';
                }
            } elseif ($exeRemark === 'Called & Mailed') {
                $authUser = Auth::user();
                // If created_by ends with something like "123|junior"
                if (preg_match('/(\d+)\|junior$/', $updateData['created_by'])) {

                    // Append ":0|senior" only once
                    if (!str_ends_with($updateData['created_by'], ':0|senior')) {
                        $updateData['created_by'] .= ':' . $authUser->id . '|senior:0|senior';
                    }
                }
            }
        }

        foreach ($updateData as $key => $value) {
            if ($value === '' && !in_array($key, ['Email_Address', 'Name', 'Date', 'Amount', 'Remark'])) {
                $updateData[$key] = null;
            }
        }


        try {
            $row->update($updateData);
            $user = Auth::user();
            $mailMessage = 'No email sent.';
            $name = $rowData['Name'] ?? null;
            $amount = isset($rowData['Amount']) ? $this->parseAmount($rowData['Amount']) : $row->Amount;

            // --- Send email if Exe_Remarks is "Called & Mailed" ---
            if (isset($rowData['Exe Remarks']) && $rowData['Exe Remarks'] === 'Called & Mailed' && !empty($email)) {
                try {
                    $smtp = SmtpSetting::where('user_id', $user->id)->first();
                    if (!$smtp) {
                        return response()->json([
                            'message' => 'No SMTP settings found.'
                        ]);
                    } else {
                        // Configure mailer dynamically (same as test() method)
                        config([
                            'mail.mailers.smtp.transport' => $smtp->mailer,
                            'mail.mailers.smtp.host' => $smtp->host,
                            'mail.mailers.smtp.port' => $smtp->port,
                            'mail.mailers.smtp.username' => $smtp->username,
                            'mail.mailers.smtp.password' => decrypt($smtp->password),
                            'mail.mailers.smtp.encryption' => $smtp->encryption,
                            'mail.from.address' => $smtp->from_address,
                            'mail.from.name' => $smtp->from_name,
                        ]);

                        // --- Fetch Email Template from Database ---
                        $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                        if ($template) {
                            $subject = $template->subject;
                            $messageBody = $template->body;
                        } else {
                            // Fallback if template not found
                            $subject = "Unlock Career Stability with Fortune 500 Projects !";
                            $messageBody =
                                "Hi {$name},\n\n" .
                                "I hope this message finds you well.\n\n" .
                                "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                                "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                                "What Makes Synergie Different?\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                                "Here’s what you can expect when you join our community:\n\n" .
                                "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                                "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                                "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                                "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                                "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                                "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                                "More Than a Paycheck — A Path to Prosperity\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                                "                  - Full-Time Roles: \$40–\$50/hr\n" .
                                "                  - Part-Time Roles: \$15–\$25/hr\n" .
                                "                  - Paid Internships available\n" .
                                "                  - 15% Salary Raise every 6 months based on performance\n" .
                                "                  - 12 Days Paid Vacation annually\n" .
                                "                  - Relocation Assistance for client deployments\n\n" .
                                "Comprehensive Benefits That Put You First\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                                "                  - Health, Dental & Vision Insurance\n" .
                                "                  - Short- & Long-Term Disability Insurance\n" .
                                "                  - Life Insurance & 401(k) Retirement Plan\n" .
                                "                  - Legal & Immigration Support\n" .
                                "                  - Tax Assistance & Transparent Payroll\n" .
                                "                  - Workers’ Compensation—your safety is our priority\n\n" .
                                "Support Tailored for International Talent\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                                "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                                "                  - Full STEM Extension & OPT/CPT Support\n" .
                                "                  - H1B Sponsorship after project onboarding\n" .
                                "                  - Relocation & Immigration Documentation\n" .
                                "                  - Ongoing Green Card Processing Assistance\n\n" .
                                "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                                "What You’ll Gain:\n\n" .
                                "                  - Live Zoom sessions & recorded expert sessions\n" .
                                "                  - Real-time project simulations & hands-on assignments\n" .
                                "                  - One-on-one resume branding & mock interviews\n" .
                                "                  - Global Certificate of Completion & recruiter access\n" .
                                "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                                "Let’s Take the First Step Together\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                                "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                                "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                                "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                                "Visit Our Website: https://www.synergiesystems.com/";
                        }

                        // --- Send Email (No Template Logic Changed) ---
                        Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                            $message->from($smtp->from_address, $smtp->from_name)
                                ->to($email)
                                ->subject($subject);
                        });

                        $mailMessage = "Email sent successfully to {$email}!";
                    }
                } catch (\Exception $e) {
                    $mailMessage = 'Failed to send email: ' . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Row updated successfully',
                'id' => $row->id,
                'sheet_row_number' => $row->sheet_row_number,
                'resume_path' => !empty($row->resume) ? true : false,
                'mail_message' => $mailMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fill Full Detail to Save.'
            ]);
        }
    }

    public function seniorupdatecon(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? $row->Email_Address;
        $phone = $rowData['Phone Number'] ?? $row->Phone_Number;
        $name  = $rowData['Name'] ?? $row->Name;
        $date  = $rowData['Date'] ?? $row->Date;

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required.'
            ]);
        }

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.'
            ]);
        }
        // Check for duplicate Email (ignore current record)
        if (!empty($email)) {
            $emailExists = GoogleSheetData::where('Email_Address', $email)
                ->where('id', '!=', $id)
                ->exists();

            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address already exists in records.'
                ]);
            }
        }

        // Check for duplicate Phone (ignore current record)
        if (!empty($phone)) {
            $phoneExists = GoogleSheetData::where('Phone_Number', $phone)
                ->where('id', '!=', $id)
                ->exists();

            if ($phoneExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number already exists in records.'
                ]);
            }
        }

        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            // Allowed Word MIME types
            $allowed = [
                'application/pdf',
                'application/msword', // .doc
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            ];

            if (!in_array($file->getMimeType(), $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only PDF or Word files (.pdf, .doc, .docx) are allowed'
                ]);
            }


            // Generate unique filename
            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                // Store the actual file content
                $filePath = $file->storeAs('resumes', $newName, 'public');

                // Delete old resume file if exists
                if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                    Storage::disk('public')->delete($row->resume);
                }

                $row->resume = $filePath; // Store file path instead of just filename

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle audio file upload - Save actual file content
        if ($request->hasFile('audio')) {
            $audio = $request->file('audio');

            // Allowed audio mime types
            $allowedAudio = [
                'audio/mpeg',
                'audio/mp3',
                'audio/wav',
                'audio/x-wav',
                'audio/ogg',
                'audio/m4a',
                'audio/aac',
                'audio/flac',
                'audio/x-ms-wma',
                'audio/webm'
            ];

            if (!in_array($audio->getMimeType(), $allowedAudio)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid audio format. Allowed: MP3, WAV, M4A, OGG, AAC, FLAC, WMA'
                ]);
            }

            // Create unique filename
            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($audio->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $audio->getClientOriginalExtension();
            $newAudioName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                // Store in storage/app/public/audios
                $audioPath = $audio->storeAs('audios', $newAudioName, 'public');

                // Delete old audio if exists
                if ($row->audio && Storage::disk('public')->exists($row->audio)) {
                    Storage::disk('public')->delete($row->audio);
                }

                // Save DB path
                $row->audio = $audioPath;
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Audio upload failed: ' . $e->getMessage()
                ]);
            }
        }


        // --- Prepare update data with null defaults for empty fields ---
        $updateData = [
            'Date' => !empty($rowData['Date']) ? $this->parseDate($rowData['Date']) : null,
            'Name' => $rowData['Name'] ?? null,
            'Email_Address' => $email, // keep original email
            'Phone_Number' => $phone,  // keep original phone
            'Location' => $rowData['Location'] ?? null,
            'Remark' => $rowData['Remark'] ?? null,
            'TransferRemark' => $rowData['TransferRemark'] ?? null,
            'Relocation' => $rowData['Relocation'] ?? null,
            'Graduation_Date' => !empty($rowData['Graduation Date']) ? $this->parseDate($rowData['Graduation Date']) : null,
            'Immigration' => $rowData['Immigration'] ?? null,
            'Course' => $rowData['Course'] ?? null,
            'Amount' => isset($rowData['Amount']) && $rowData['Amount'] !== '' ? $this->parseAmount($rowData['Amount']) : 469, // ✅ default 469
            'Qualification' => $rowData['Qualification'] ?? null,
            'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
            'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
            'Time_Zone' => $rowData['Time Zone'] ?? null,
            'updated_at' => now(),
        ];

        // Only update resume if it was uploaded
        if ($request->hasFile('resume')) {
            $updateData['resume'] = $row->resume;
        }

        // Only update audio if it was uploaded
        if ($request->hasFile('audio')) {
            $updateData['audio'] = $row->audio;
        }


        // Start with existing created_by value
        $updateData['created_by'] = $row->created_by;

        if (isset($rowData['Exe Remarks'])) {
            $exeRemark = $rowData['Exe Remarks'];

            if ($exeRemark === 'Verification Completed') {
                $authUser = Auth::user();

                // ✔ Proceed ONLY if created_by ends with "0|senior"
                if (preg_match('/0\|senior$/', $updateData['created_by'])) {

                    // Replace ending "0|senior" → "auth_id|senior:0|accountant"
                    $updateData['created_by'] = preg_replace(
                        '/0\|senior$/',
                        $authUser->id . '|senior:0|accountant',
                        $updateData['created_by']
                    );

                    // Ensure ":0|accountant" exists only once at the end
                    if (!preg_match('/0\|accountant$/', $updateData['created_by'])) {
                        $updateData['created_by'] .= ':0|accountant';
                    }
                }
            } elseif ($exeRemark === 'Payment Completed') {
                $authUser = Auth::user();
                // If created_by ends with something like "123|junior"
                if (preg_match('/(\d+)\|junior$/', $updateData['created_by'])) {

                    // Append ":0|senior" only once
                    if (!str_ends_with($updateData['created_by'], ':0|senior')) {
                        $updateData['created_by'] .= ':' . $authUser->id . '|senior:0|senior';
                    }
                }
            }
        }

        foreach ($updateData as $key => $value) {
            if ($value === '' && !in_array($key, ['Email_Address', 'Name', 'Date', 'Amount', 'Remark'])) {
                $updateData[$key] = null;
            }
        }


        try {
            $row->update($updateData);
            $user = Auth::user();
            $mailMessage = 'No email sent.';
            $name = $rowData['Name'] ?? null;
            $amount = isset($rowData['Amount']) ? $this->parseAmount($rowData['Amount']) : $row->Amount;

            // --- Send email if Exe_Remarks is "Called & Mailed" ---
            if (isset($rowData['Exe Remarks']) && $rowData['Exe Remarks'] === 'Called & Mailed' && !empty($email)) {
                try {
                    $smtp = SmtpSetting::where('user_id', $user->id)->first();
                    if (!$smtp) {
                        return response()->json([
                            'message' => 'No SMTP settings found.'
                        ]);
                    } else {
                        // Configure mailer dynamically (same as test() method)
                        config([
                            'mail.mailers.smtp.transport' => $smtp->mailer,
                            'mail.mailers.smtp.host' => $smtp->host,
                            'mail.mailers.smtp.port' => $smtp->port,
                            'mail.mailers.smtp.username' => $smtp->username,
                            'mail.mailers.smtp.password' => decrypt($smtp->password),
                            'mail.mailers.smtp.encryption' => $smtp->encryption,
                            'mail.from.address' => $smtp->from_address,
                            'mail.from.name' => $smtp->from_name,
                        ]);

                        // --- Fetch Email Template from Database ---
                        $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                        if ($template) {
                            $subject = $template->subject;
                            $messageBody = $template->body;
                        } else {
                            // Fallback if template not found
                            $subject = "Unlock Career Stability with Fortune 500 Projects !";
                            $messageBody =
                                "Hi {$name},\n\n" .
                                "I hope this message finds you well.\n\n" .
                                "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                                "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                                "What Makes Synergie Different?\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                                "Here’s what you can expect when you join our community:\n\n" .
                                "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                                "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                                "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                                "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                                "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                                "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                                "More Than a Paycheck — A Path to Prosperity\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                                "                  - Full-Time Roles: \$40–\$50/hr\n" .
                                "                  - Part-Time Roles: \$15–\$25/hr\n" .
                                "                  - Paid Internships available\n" .
                                "                  - 15% Salary Raise every 6 months based on performance\n" .
                                "                  - 12 Days Paid Vacation annually\n" .
                                "                  - Relocation Assistance for client deployments\n\n" .
                                "Comprehensive Benefits That Put You First\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                                "                  - Health, Dental & Vision Insurance\n" .
                                "                  - Short- & Long-Term Disability Insurance\n" .
                                "                  - Life Insurance & 401(k) Retirement Plan\n" .
                                "                  - Legal & Immigration Support\n" .
                                "                  - Tax Assistance & Transparent Payroll\n" .
                                "                  - Workers’ Compensation—your safety is our priority\n\n" .
                                "Support Tailored for International Talent\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                                "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                                "                  - Full STEM Extension & OPT/CPT Support\n" .
                                "                  - H1B Sponsorship after project onboarding\n" .
                                "                  - Relocation & Immigration Documentation\n" .
                                "                  - Ongoing Green Card Processing Assistance\n\n" .
                                "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                                "What You’ll Gain:\n\n" .
                                "                  - Live Zoom sessions & recorded expert sessions\n" .
                                "                  - Real-time project simulations & hands-on assignments\n" .
                                "                  - One-on-one resume branding & mock interviews\n" .
                                "                  - Global Certificate of Completion & recruiter access\n" .
                                "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                                "Let’s Take the First Step Together\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                                "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                                "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                                "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                                "Visit Our Website: https://www.synergiesystems.com/";
                        }

                        // --- Send Email (No Template Logic Changed) ---
                        Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                            $message->from($smtp->from_address, $smtp->from_name)
                                ->to($email)
                                ->subject($subject);
                        });

                        $mailMessage = "Email sent successfully to {$email}!";
                    }
                } catch (\Exception $e) {
                    $mailMessage = 'Failed to send email: ' . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Row updated successfully',
                'id' => $row->id,
                'sheet_row_number' => $row->sheet_row_number,
                'resume_path' => !empty($row->resume) ? true : false,
                'mail_message' => $mailMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fill Full Detail to Save.'
            ]);
        }
    }



    public function seniorstore(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $rowData = json_decode($request->input('data'), true);
            if (empty($rowData)) {
                return response()->json(['success' => false, 'message' => 'No data provided']);
            }

            $email = $rowData['Email Address'] ?? null;
            $phone = $rowData['Phone Number'] ?? null;
            $name  = $rowData['Name'] ?? null;
            $date  = $rowData['Date'] ?? null;

            if (empty($name)) {
                return response()->json(['success' => false, 'message' => 'Name is required.']);
            }

            if (empty($date)) {
                return response()->json(['success' => false, 'message' => 'Date is required.']);
            }

            if (empty($email)) {
                return response()->json(['success' => false, 'message' => 'Email is required.']);
            }

            if (empty($phone)) {
                return response()->json(['success' => false, 'message' => 'Phone is required.']);
            }

            $user = Auth::user();

            //  Atomic duplicate email check
            if (!empty($email)) {
                $emailExistsForUser = GoogleSheetData::where('Email_Address', $email)
                    ->where('created_by', 'like', $user->id . '|%')
                    ->lockForUpdate()
                    ->exists();

                if ($emailExistsForUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email ID already exists for you.'
                    ]);
                }
            }

            //  Atomic sheet_row_number generation
            $nextRow = GoogleSheetData::lockForUpdate()->max('sheet_row_number') + 1;


            $record = new GoogleSheetData();
            $record->sheet_row_number = $nextRow;

            // NEW: mark only the current record as current
            $record->is_current = 1;

            // --- Column map ---
            $columnMap = [
                'Date' => 'Date',
                'Name' => 'Name',
                'Email Address' => 'Email_Address',
                'Phone Number' => 'Phone_Number',
                'Location' => 'Location',
                'Remark' => 'Remark',
                'Relocation' => 'Relocation',
                'Graduation Date' => 'Graduation_Date',
                'Immigration' => 'Immigration',
                'Course' => 'Course',
                'Amount' => 'Amount',
                'Qualification' => 'Qualification',
                'Exe Remarks' => 'Exe_Remarks',
                '1st Follow Up Remarks' => 'First_Follow_Up_Remarks',
                'Time Zone' => 'Time_Zone',
            ];

            $exeRemarksValue = null;
            $amount = null;

            foreach ($columnMap as $frontendKey => $dbColumn) {
                $val = $rowData[$frontendKey] ?? null;

                if (in_array($dbColumn, ['Date', 'Graduation_Date']) && !empty($val)) {
                    $val = $this->parseDate($val);
                }

                if ($dbColumn === 'Amount' && !empty($val)) {
                    $val = $this->parseAmount($val);
                    $amount = $val;
                }

                if ($dbColumn === 'Exe_Remarks') {
                    $exeRemarksValue = $val;
                }

                if (empty($val) && !in_array($dbColumn, ['Email_Address', 'Phone_Number'])) {
                    $val = null;
                }

                $record->$dbColumn = $val;
            }

            if ($exeRemarksValue === 'Called & Mailed') {

                $record->created_by = $user->id . '|senior:0|senior';
            } elseif ($exeRemarksValue === 'Ready To Pay') {

                $record->created_by = $user->id . '|senior:0|accountant';
            } else {

                $record->created_by = $user->id . '|senior';
            }

            // Handle resume file upload
            if ($request->hasFile('resume')) {
                $file = $request->file('resume');

                // Allow PDF + Word files
                $allowed = [
                    'application/pdf',
                    'application/msword', // .doc
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                ];

                if (!in_array($file->getMimeType(), $allowed)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only PDF or Word files (.pdf, .doc, .docx) are allowed'
                    ]);
                }

                $timestamp = now()->format('Ymd_His');
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

                try {
                    $filePath = $file->storeAs('resumes', $newName, 'public');
                    $record->resume = $filePath;
                } catch (\Exception $e) {
                    $record->resume = null;
                }
            }

            try {
                $record->save();
                $saveMessage = 'Record saved successfully.';
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fill Full Detail to Save.'
                ]);
            }

            // --- Email logic ---
            $mailMessage = 'No email sent.';
            // --- Send Email if Exe_Remarks is "Called & Mailed" ---
            if ($exeRemarksValue === 'Called & Mailed' && !empty($email)) {
                try {
                    $smtp = SmtpSetting::where('user_id', $user->id)->first();
                    if (!$smtp) {
                        return response()->json([
                            'message' => 'No SMTP settings found.'
                        ]);
                    } else {
                        // Configure mailer dynamically (same as test() method)
                        config([
                            'mail.mailers.smtp.transport' => $smtp->mailer,
                            'mail.mailers.smtp.host' => $smtp->host,
                            'mail.mailers.smtp.port' => $smtp->port,
                            'mail.mailers.smtp.username' => $smtp->username,
                            'mail.mailers.smtp.password' => decrypt($smtp->password),
                            'mail.mailers.smtp.encryption' => $smtp->encryption,
                            'mail.from.address' => $smtp->from_address,
                            'mail.from.name' => $smtp->from_name,
                        ]);

                        // --- Fetch Email Template from Database ---
                        $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                        if ($template) {
                            $subject = $template->subject;
                            $messageBody = $template->body;
                        } else {
                            // Fallback if template not found
                            $subject = "Unlock Career Stability with Fortune 500 Projects !";
                            $messageBody =
                                "Hi {$name},\n\n" .
                                "I hope this message finds you well.\n\n" .
                                "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                                "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                                "What Makes Synergie Different?\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                                "Here’s what you can expect when you join our community:\n\n" .
                                "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                                "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                                "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                                "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                                "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                                "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                                "More Than a Paycheck — A Path to Prosperity\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                                "                  - Full-Time Roles: \$40–\$50/hr\n" .
                                "                  - Part-Time Roles: \$15–\$25/hr\n" .
                                "                  - Paid Internships available\n" .
                                "                  - 15% Salary Raise every 6 months based on performance\n" .
                                "                  - 12 Days Paid Vacation annually\n" .
                                "                  - Relocation Assistance for client deployments\n\n" .
                                "Comprehensive Benefits That Put You First\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                                "                  - Health, Dental & Vision Insurance\n" .
                                "                  - Short- & Long-Term Disability Insurance\n" .
                                "                  - Life Insurance & 401(k) Retirement Plan\n" .
                                "                  - Legal & Immigration Support\n" .
                                "                  - Tax Assistance & Transparent Payroll\n" .
                                "                  - Workers’ Compensation—your safety is our priority\n\n" .
                                "Support Tailored for International Talent\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                                "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                                "                  - Full STEM Extension & OPT/CPT Support\n" .
                                "                  - H1B Sponsorship after project onboarding\n" .
                                "                  - Relocation & Immigration Documentation\n" .
                                "                  - Ongoing Green Card Processing Assistance\n\n" .
                                "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                                "What You’ll Gain:\n\n" .
                                "                  - Live Zoom sessions & recorded expert sessions\n" .
                                "                  - Real-time project simulations & hands-on assignments\n" .
                                "                  - One-on-one resume branding & mock interviews\n" .
                                "                  - Global Certificate of Completion & recruiter access\n" .
                                "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                                "Let’s Take the First Step Together\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                                "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                                "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                                "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                                "Visit Our Website: https://www.synergiesystems.com/";
                        }

                        // --- Send Email (No Template Logic Changed) ---
                        Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                            $message->from($smtp->from_address, $smtp->from_name)
                                ->to($email)
                                ->subject($subject);
                        });

                        $mailMessage = "Email sent successfully to {$email}!";
                    }
                } catch (\Exception $e) {
                    $mailMessage = 'Failed to send email: ' . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'id' => $record->id,
                'sheet_row_number' => $record->sheet_row_number,
                'resume_path' => !empty($record->resume) ? true : false,
                'mail_message' => $mailMessage,
                'save_message' => $saveMessage,
            ]);
        });
    }
    public function seniorstoremod(Request $request)
    {
        $rowData = json_decode($request->input('data'), true);

        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? null;
        $phone = $rowData['Phone Number'] ?? null;
        $name  = $rowData['Name'] ?? null;
        $date  = $rowData['Date'] ?? null;

        // --- Check required fields ---
        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required.'
            ]);
        }

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.'
            ]);
        }

        // Check for duplicate Email
        if (!empty($email)) {
            $emailExists = GoogleSheetData::where('Email_Address', $email)->exists();

            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address already exists in records.'
                ]);
            }
        }

        // Check for duplicate Phone
        if (!empty($phone)) {
            $phoneExists = GoogleSheetData::where('Phone_Number', $phone)->exists();

            if ($phoneExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number already exists in records.'
                ]);
            }
        }

        $user = Auth::user();
        $maxRow = GoogleSheetData::max('sheet_row_number') ?? 0;
        $nextRow = $maxRow + 1;

        $record = new GoogleSheetData();
        $record->sheet_row_number = $nextRow;
        $record->created_by = $user->id . '|senior';

        // Map frontend keys to DB columns
        $columnMap = [
            'Date' => 'Date',
            'Name' => 'Name',
            'Email Address' => 'Email_Address',
            'Phone Number' => 'Phone_Number',
            'Location' => 'Location',
            'Remark' => 'Remark',
            'Relocation' => 'Relocation',
            'Graduation Date' => 'Graduation_Date',
            'Immigration' => 'Immigration',
            'Course' => 'Course',
            'Amount' => 'Amount',
            'Qualification' => 'Qualification',
            'Exe Remarks' => 'Exe_Remarks',
            '1st Follow Up Remarks' => 'First_Follow_Up_Remarks',
            'Time Zone' => 'Time_Zone',
        ];

        $exeRemarksValue = null;
        $name = null;
        $amount = null;

        // Assign values safely, save null for empty non-number/email fields
        foreach ($columnMap as $frontendKey => $dbColumn) {
            $val = $rowData[$frontendKey] ?? null;

            if (in_array($dbColumn, ['Date', 'Graduation_Date']) && !empty($val)) {
                $val = $this->parseDate($val);
            }

            if ($dbColumn === 'Amount' && !empty($val)) {
                $val = $this->parseAmount($val);
                $amount = $val;
            }

            if ($dbColumn === 'Name') {
                $name = $val;
            }

            if ($dbColumn === 'Exe_Remarks') {
                $exeRemarksValue = $val;
            }

            // Save null for empty fields, including Amount
            if (empty($val) && !in_array($dbColumn, ['Email_Address', 'Phone_Number'])) {
                $val = null;
            }

            $record->$dbColumn = $val;
        }

        // Set created_by conditionally based on Exe_Remarks
        if ($exeRemarksValue === 'Called & Mailed') {
            $record->created_by = $user->id . '|senior:0|senior';
        } elseif ($exeRemarksValue === 'Ready To Pay') {
            $record->created_by = $user->id . '|senior:0|accountant';
        } else {
            $record->created_by = $user->id . '|senior';
        }

        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            // Validate it's a PDF
            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                // Store the actual file content
                $filePath = $file->storeAs('resumes', $newName, 'public');
                $record->resume = $filePath; // Store file path
            } catch (\Exception $e) {
                // Continue without resume if upload fails
                $record->resume = null;
            }
        }

        try {
            $record->save();
            $saveMessage = 'Record saved successfully.';
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fill Full Detail to Save.'
            ]);
        }

        // --- Email logic ---
        $mailMessage = 'No email sent.';
        // --- Send Email if Exe_Remarks is "Called & Mailed" ---
        if ($exeRemarksValue === 'Called & Mailed' && !empty($email)) {
            try {
                $smtp = SmtpSetting::where('user_id', $user->id)->first();
                if (!$smtp) {
                    return response()->json([
                        'message' => 'No SMTP settings found.'
                    ]);
                } else {
                    // Configure mailer dynamically (same as test() method)
                    config([
                        'mail.mailers.smtp.transport' => $smtp->mailer,
                        'mail.mailers.smtp.host' => $smtp->host,
                        'mail.mailers.smtp.port' => $smtp->port,
                        'mail.mailers.smtp.username' => $smtp->username,
                        'mail.mailers.smtp.password' => decrypt($smtp->password),
                        'mail.mailers.smtp.encryption' => $smtp->encryption,
                        'mail.from.address' => $smtp->from_address,
                        'mail.from.name' => $smtp->from_name,
                    ]);

                    // --- Fetch Email Template from Database ---
                    $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                    if ($template) {
                        $subject = $template->subject;
                        $messageBody = $template->body;
                    } else {
                        // Fallback if template not found
                        $subject = "Unlock Career Stability with Fortune 500 Projects !";
                        $messageBody =
                            "Hi {$name},\n\n" .
                            "I hope this message finds you well.\n\n" .
                            "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                            "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                            "What Makes Synergie Different?\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                            "Here’s what you can expect when you join our community:\n\n" .
                            "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                            "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                            "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                            "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                            "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                            "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                            "More Than a Paycheck — A Path to Prosperity\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                            "                  - Full-Time Roles: \$40–\$50/hr\n" .
                            "                  - Part-Time Roles: \$15–\$25/hr\n" .
                            "                  - Paid Internships available\n" .
                            "                  - 15% Salary Raise every 6 months based on performance\n" .
                            "                  - 12 Days Paid Vacation annually\n" .
                            "                  - Relocation Assistance for client deployments\n\n" .
                            "Comprehensive Benefits That Put You First\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                            "                  - Health, Dental & Vision Insurance\n" .
                            "                  - Short- & Long-Term Disability Insurance\n" .
                            "                  - Life Insurance & 401(k) Retirement Plan\n" .
                            "                  - Legal & Immigration Support\n" .
                            "                  - Tax Assistance & Transparent Payroll\n" .
                            "                  - Workers’ Compensation—your safety is our priority\n\n" .
                            "Support Tailored for International Talent\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                            "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                            "                  - Full STEM Extension & OPT/CPT Support\n" .
                            "                  - H1B Sponsorship after project onboarding\n" .
                            "                  - Relocation & Immigration Documentation\n" .
                            "                  - Ongoing Green Card Processing Assistance\n\n" .
                            "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                            "What You’ll Gain:\n\n" .
                            "                  - Live Zoom sessions & recorded expert sessions\n" .
                            "                  - Real-time project simulations & hands-on assignments\n" .
                            "                  - One-on-one resume branding & mock interviews\n" .
                            "                  - Global Certificate of Completion & recruiter access\n" .
                            "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                            "Let’s Take the First Step Together\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                            "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                            "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                            "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                            "Visit Our Website: https://www.synergiesystems.com/";
                    }

                    // --- Send Email (No Template Logic Changed) ---
                    Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                        $message->from($smtp->from_address, $smtp->from_name)
                            ->to($email)
                            ->subject($subject);
                    });

                    $mailMessage = "Email sent successfully to {$email}!";
                }
            } catch (\Exception $e) {
                $mailMessage = 'Failed to send email: ' . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'id' => $record->id,
            'sheet_row_number' => $record->sheet_row_number,
            'resume_path' => !empty($record->resume) ? true : false,
            'mail_message' => $mailMessage,
            'save_message' => $saveMessage,
        ]);
    }


    // Add a method to serve the PDF files
    public function viewseniorResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // --- If already PDF, return directly ---
        if ($extension === 'pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        // --- Convert DOC/DOCX to PDF ---
        if (in_array($extension, ['doc', 'docx'])) {

            // Load Word file using PHPWord
            $phpWord = IOFactory::load($filePath);

            // Create a temporary HTML file from Word content
            $tempHtml = storage_path('app/temp_' . time() . '.html');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            // Convert HTML to PDF via Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF content
            $pdfOutput = $dompdf->output();

            // Remove temp HTML
            unlink($tempHtml);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf"');
        }

        abort(415, 'Unsupported file format');
    }

    // Add a method to serve the PDF files
    public function viewseniorUpdateResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // --- If already PDF, return directly ---
        if ($extension === 'pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        // --- Convert DOC/DOCX to PDF ---
        if (in_array($extension, ['doc', 'docx'])) {

            // Load Word file using PHPWord
            $phpWord = IOFactory::load($filePath);

            // Create a temporary HTML file from Word content
            $tempHtml = storage_path('app/temp_' . time() . '.html');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            // Convert HTML to PDF via Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF content
            $pdfOutput = $dompdf->output();

            // Remove temp HTML
            unlink($tempHtml);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf"');
        }

        abort(415, 'Unsupported file format');
    }



    public function viewseniorAcceptanceSign($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->acceptancesign) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->acceptancesign);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // --- If already PDF, return directly ---
        if ($extension === 'pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        // --- Convert DOC/DOCX to PDF ---
        if (in_array($extension, ['doc', 'docx'])) {

            // Load Word file using PHPWord
            $phpWord = IOFactory::load($filePath);

            // Create a temporary HTML file from Word content
            $tempHtml = storage_path('app/temp_' . time() . '.html');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            // Convert HTML to PDF via Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF content
            $pdfOutput = $dompdf->output();

            // Remove temp HTML
            unlink($tempHtml);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf"');
        }

        abort(415, 'Unsupported file format');
    }

    public function viewseniorAcceptance($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->acceptance) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->acceptance);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // --- If already PDF, return directly ---
        if ($extension === 'pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        // --- Convert DOC/DOCX to PDF ---
        if (in_array($extension, ['doc', 'docx'])) {

            // Load Word file using PHPWord
            $phpWord = IOFactory::load($filePath);

            // Create a temporary HTML file from Word content
            $tempHtml = storage_path('app/temp_' . time() . '.html');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            // Convert HTML to PDF via Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF content
            $pdfOutput = $dompdf->output();

            // Remove temp HTML
            unlink($tempHtml);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf"');
        }

        abort(415, 'Unsupported file format');
    }


    public function viewseniorConsultation($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->consultation) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->consultation);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // --- If already PDF, return directly ---
        if ($extension === 'pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        // --- Convert DOC/DOCX to PDF ---
        if (in_array($extension, ['doc', 'docx'])) {

            // Load Word file using PHPWord
            $phpWord = IOFactory::load($filePath);

            // Create a temporary HTML file from Word content
            $tempHtml = storage_path('app/temp_' . time() . '.html');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            // Convert HTML to PDF via Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF content
            $pdfOutput = $dompdf->output();

            // Remove temp HTML
            unlink($tempHtml);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf"');
        }

        abort(415, 'Unsupported file format');
    }

    public function viewseniorDelivery($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->delivery) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->delivery);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // --- If already PDF, return directly ---
        if ($extension === 'pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        // --- Convert DOC/DOCX to PDF ---
        if (in_array($extension, ['doc', 'docx'])) {

            // Load Word file using PHPWord
            $phpWord = IOFactory::load($filePath);

            // Create a temporary HTML file from Word content
            $tempHtml = storage_path('app/temp_' . time() . '.html');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            // Convert HTML to PDF via Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF content
            $pdfOutput = $dompdf->output();

            // Remove temp HTML
            unlink($tempHtml);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf"');
        }

        abort(415, 'Unsupported file format');
    }

    public function viewseniorDeliverySign($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->deliverysign) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->deliverysign);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // --- If already PDF, return directly ---
        if ($extension === 'pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        // --- Convert DOC/DOCX to PDF ---
        if (in_array($extension, ['doc', 'docx'])) {

            // Load Word file using PHPWord
            $phpWord = IOFactory::load($filePath);

            // Create a temporary HTML file from Word content
            $tempHtml = storage_path('app/temp_' . time() . '.html');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            // Convert HTML to PDF via Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF content
            $pdfOutput = $dompdf->output();

            // Remove temp HTML
            unlink($tempHtml);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf"');
        }

        abort(415, 'Unsupported file format');
    }

    public function viewseniorPayment($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->payment) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->payment);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // --- If already PDF, return directly ---
        if ($extension === 'pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        // --- Convert DOC/DOCX to PDF ---
        if (in_array($extension, ['doc', 'docx'])) {

            // Load Word file using PHPWord
            $phpWord = IOFactory::load($filePath);

            // Create a temporary HTML file from Word content
            $tempHtml = storage_path('app/temp_' . time() . '.html');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            // Convert HTML to PDF via Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF content
            $pdfOutput = $dompdf->output();

            // Remove temp HTML
            unlink($tempHtml);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf"');
        }

        abort(415, 'Unsupported file format');
    }

    public function viewseniorPaymentSign($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->paymentsign) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->paymentsign);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // --- If already PDF, return directly ---
        if ($extension === 'pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        // --- Convert DOC/DOCX to PDF ---
        if (in_array($extension, ['doc', 'docx'])) {

            // Load Word file using PHPWord
            $phpWord = IOFactory::load($filePath);

            // Create a temporary HTML file from Word content
            $tempHtml = storage_path('app/temp_' . time() . '.html');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            // Convert HTML to PDF via Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF content
            $pdfOutput = $dompdf->output();

            // Remove temp HTML
            unlink($tempHtml);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf"');
        }

        abort(415, 'Unsupported file format');
    }

    // Add a method to download the PDF files
    public function downloadseniorResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }

    // Add a method to download the PDF files
    public function downloadseniorUpdateResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }

    public function downloadseniorAcceptance($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->acceptance) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->acceptance);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }
    public function downloadseniorConsultation($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->consultation) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->consultation);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }
    public function downloadseniorDelivery($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->delivery) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->delivery);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }
    public function downloadseniorDeliverySign($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->deliverysign) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->deliverysign);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }
    public function downloadseniorPayment($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->payment) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->payment);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }
    public function downloadseniorPaymentSign($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->paymentsign) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->paymentsign);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }


    public function viewseniorAudio($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->audio) {
            abort(404, 'Audio not found');
        }

        $filePath = storage_path('app/public/' . $row->audio);

        if (!file_exists($filePath)) {
            abort(404, 'Audio file missing');
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // List of acceptable audio formats
        $allowed = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'wma'];

        if (!in_array($extension, $allowed)) {
            abort(415, 'Unsupported audio format');
        }

        return response()->file($filePath, [
            'Content-Type' => mime_content_type($filePath),
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }

    public function downloadseniorAudio($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->audio) {
            abort(404, 'Audio not found');
        }

        $filePath = storage_path('app/public/' . $row->audio);

        if (!file_exists($filePath)) {
            abort(404, 'Audio file missing');
        }

        return response()->download($filePath, basename($filePath));
    }


    private function parseDate($dateString)
    {
        try {
            return \Carbon\Carbon::createFromFormat('m/d/Y', $dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseAmount($amountString)
    {
        if (is_null($amountString) || $amountString === '') {
            return null;
        }

        // If already numeric, just return float
        if (is_numeric($amountString)) {
            return (float) $amountString;
        }

        // Otherwise clean it
        return (float) str_replace(['$', ','], '', $amountString);
    }




    public function junior(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value
        $page = $request->input('page', 1); // ✅ Ensure page input handled
        $date = $request->input('date');

        $userPattern = "%:" . $authUser->id . "|junior";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern) {
            $q->where(function ($q2) use ($authUser, $userPattern) {

                $q2->where('created_by', $authUser->id . '|junior')
                    ->orWhere('created_by', 'LIKE', $userPattern);
            })
                // EXCLUSION: Do NOT show rows having more than one "|junior"
                ->whereRaw("RIGHT(created_by, LENGTH(?)) = ?", [$authUser->id . '|junior', $authUser->id . '|junior']);
        })->where('transfers', '!=', 1)->where('rejected', 0);


        // Filter by selected junior
        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
            })->where('transfers', '!=', 1);
        }

        if ($date) {
            $query->whereDate('updated_at', $date);
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Changed sorting: order by 'id' descending (like 'Date' desc in junior)
        $results = $query->orderBy('updated_at', 'desc')->get();

        // ✅ Transform after getting all filtered data
        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // ✅ Apply pagination AFTER transformation (like junior)
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            [
                'path'  => url()->current(),
                'query' => [
                    'search'      => $request->search,
                    'junior_user' => $request->junior_user,
                    'date'        => $request->date, // ✅ keep date
                ]
            ]
        );



        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        $todayDate = Carbon::now('America/New_York')->toDateString();
        $createdByKey = $authUser->id . '|junior';

        // Base query for today & this user
        $todayBaseQuery = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $todayDate);


        // Total calls today
        $StotalCalls = (clone $todayBaseQuery)->count();

        // Individual Exe Remark counts
        $ScalledAndMailedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereDate('followup', $todayDate)
            ->count();

        $SnotInterestedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Not Interested')
            ->whereNull('TransferRemark')
            ->count();

        $SinterestedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Interested')
            ->whereNull('TransferRemark')
            ->count();

        $SothersCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Others')
            ->whereNull('TransferRemark')
            ->count();

        $SvmCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'VM')
            ->whereNull('TransferRemark')
            ->count();

        $SbusyCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Busy')
            ->whereNull('TransferRemark')
            ->count();

        // Grouped array (easy to use in Blade / AJAX)
        $exeRemarkCounts = [
            'total_calls'       => $StotalCalls,
            'called_and_mailed' => $ScalledAndMailedCalls,
            'not_interested'    => $SnotInterestedCalls,
            'interested'        => $SinterestedCalls,
            'others'            => $SothersCalls,
            'vm'                => $SvmCalls,
            'busy'              => $SbusyCalls,
        ];

        if ($request->ajax()) {
            return view('database.partials.junior_table', [
                'data' => $pagedData,
                'juniorUsers' => $juniorUsers,
                'exeRemarkCounts' => $exeRemarkCounts
            ])->render();
        }


        return view('database.junior', [
            'data' => $pagedData,
            'juniorUsers' => $juniorUsers,
            'exeRemarkCounts' => $exeRemarkCounts
        ]);
    }


    public function juniorrej(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value
        $page = $request->input('page', 1); // ✅ Ensure page input handled
        $date = $request->input('date');

        $userPattern = "%:" . $authUser->id . "|junior";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern) {
            $q->where(function ($q2) use ($authUser, $userPattern) {

                $q2->where('created_by', $authUser->id . '|junior')
                    ->orWhere('created_by', 'LIKE', $userPattern);
            })
                // EXCLUSION: Do NOT show rows having more than one "|junior"
                ->whereRaw("RIGHT(created_by, LENGTH(?)) = ?", [$authUser->id . '|junior', $authUser->id . '|junior']);
        })->where('transfers', '!=', 1)->where('rejected', 1);


        // Filter by selected junior
        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
            })->where('transfers', '!=', 1);
        }

        if ($date) {
            $query->whereDate('updated_at', $date);
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Changed sorting: order by 'id' descending (like 'Date' desc in junior)
        $results = $query->orderBy('updated_at', 'desc')->get();

        // ✅ Transform after getting all filtered data
        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // ✅ Apply pagination AFTER transformation (like junior)
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            [
                'path'  => url()->current(),
                'query' => [
                    'search'      => $request->search,
                    'junior_user' => $request->junior_user,
                    'date'        => $request->date, // ✅ keep date
                ]
            ]
        );



        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        $todayDate = Carbon::now('America/New_York')->toDateString();
        $createdByKey = $authUser->id . '|junior';

        // Base query for today & this user
        $todayBaseQuery = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $todayDate);


        // Total calls today
        $StotalCalls = (clone $todayBaseQuery)->count();

        // Individual Exe Remark counts
        $ScalledAndMailedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereDate('followup', $todayDate)
            ->count();

        $SnotInterestedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Not Interested')
            ->whereNull('TransferRemark')
            ->count();

        $SinterestedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Interested')
            ->whereNull('TransferRemark')
            ->count();

        $SothersCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Others')
            ->whereNull('TransferRemark')
            ->count();

        $SvmCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'VM')
            ->whereNull('TransferRemark')
            ->count();

        $SbusyCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Busy')
            ->whereNull('TransferRemark')
            ->count();

        // Grouped array (easy to use in Blade / AJAX)
        $exeRemarkCounts = [
            'total_calls'       => $StotalCalls,
            'called_and_mailed' => $ScalledAndMailedCalls,
            'not_interested'    => $SnotInterestedCalls,
            'interested'        => $SinterestedCalls,
            'others'            => $SothersCalls,
            'vm'                => $SvmCalls,
            'busy'              => $SbusyCalls,
        ];

        if ($request->ajax()) {
            return view('database.partials.juniorrej_table', [
                'data' => $pagedData,
                'juniorUsers' => $juniorUsers,
                'exeRemarkCounts' => $exeRemarkCounts
            ])->render();
        }


        return view('database.juniorrej', [
            'data' => $pagedData,
            'juniorUsers' => $juniorUsers,
            'exeRemarkCounts' => $exeRemarkCounts
        ]);
    }


    public function juniorcandm(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user');
        $page = $request->input('page', 1);
        $date = $request->input('date');

        $juniorPart = $authUser->id . '|junior';

        $query = GoogleSheetData::where(function ($q) use ($juniorPart) {
            // Check first segment is junior
            $q->whereRaw("SUBSTRING_INDEX(created_by, ':', 1) = ?", [$juniorPart]);
        })
            ->where(function ($q) {
                // Check second segment is senior (any ID or 0)
                $q->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(created_by, ':', 2), ':', -1) LIKE '%|senior'");
            })
            ->where('transfers', 0); // ✅ show only transfer = 0

        // Filter by selected junior
        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
            })->where('transfers', '!=', 1);
        }

        if ($date) {
            $query->whereDate('updated_at', $date);
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Changed sorting: order by 'id' descending (like 'Date' desc in junior)
        $results = $query->orderBy('updated_at', 'desc')->get();

        // ✅ Transform after getting all filtered data
        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // ✅ Apply pagination AFTER transformation (like junior)
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            [
                'path'  => url()->current(),
                'query' => [
                    'search'      => $request->search,
                    'junior_user' => $request->junior_user,
                    'date'        => $request->date, // ✅ keep date
                ]
            ]
        );


        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);

        $todayDate = Carbon::now('America/New_York')->toDateString();
        $createdByKey = $authUser->id . '|junior';

        // Base query for today & this user
        $todayBaseQuery = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $todayDate);

        // Total calls today
        $StotalCalls = (clone $todayBaseQuery)->count();

        // Individual Exe Remark counts
        $ScalledAndMailedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereDate('followup', $todayDate)
            ->count();

        $SnotInterestedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Not Interested')
            ->whereNull('TransferRemark')
            ->count();

        $SinterestedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Interested')
            ->whereNull('TransferRemark')
            ->count();

        $SothersCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Others')
            ->whereNull('TransferRemark')
            ->count();

        $SvmCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'VM')
            ->whereNull('TransferRemark')
            ->count();

        $SbusyCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Busy')
            ->whereNull('TransferRemark')
            ->count();

        // Grouped array (easy to use in Blade / AJAX)
        $exeRemarkCounts = [
            'total_calls'       => $StotalCalls,
            'called_and_mailed' => $ScalledAndMailedCalls,
            'not_interested'    => $SnotInterestedCalls,
            'interested'        => $SinterestedCalls,
            'others'            => $SothersCalls,
            'vm'                => $SvmCalls,
            'busy'              => $SbusyCalls,
        ];

        // ✅ Handle AJAX pagination and search
        if ($request->ajax()) {
            return view('database.partials.juniorcandm_table', [
                'data' => $pagedData,
                'juniorUsers' => $juniorUsers,
                'exeRemarkCounts' => $exeRemarkCounts
            ])->render();
        }

        return view('database.juniorcandm', [
            'data' => $pagedData,
            'juniorUsers' => $juniorUsers,
            'exeRemarkCounts' => $exeRemarkCounts
        ]);
    }

    public function juniortra(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value
        $page = $request->input('page', 1); // ✅ Ensure page input handled
        $date = $request->input('date');

        $juniorPart = $authUser->id . '|junior';

        $query = GoogleSheetData::where(function ($q) use ($juniorPart) {
            // Check first segment is junior
            $q->whereRaw("SUBSTRING_INDEX(created_by, ':', 1) = ?", [$juniorPart]);
        })
            ->where(function ($q) {
                // Check second segment is senior (any ID or 0)
                $q->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(created_by, ':', 2), ':', -1) LIKE '%|senior'");
            })
            ->where('transfers', 1); // ✅ show only transfer = 0

        // Filter by selected junior
        if ($juniorUserId) {
            $query->where(function ($q) use ($juniorUserId) {
                $q->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%')
                    ->orWhere('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
            })->where('transfers', '!=', 1);
        }

        if ($date) {
            $query->whereDate('updated_at', $date);
        }


        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // ✅ Changed sorting: order by 'id' descending (like 'Date' desc in junior)
        $results = $query->orderBy('updated_at', 'desc')->get();

        // ✅ Transform after getting all filtered data
        $transformed = $results->map(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SELF ({$userId}) ({$roleLabel})";
                    } elseif ($userId == 0) {
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "SYSTEM (0) ({$roleLabel})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $roleLabel = ($role === 'senior')
                            ? 'IT Senior Recruiter'
                            : (($role === 'junior') ? 'IT Recruiter' : $role);
                        $names[] = "{$name} ({$userId}) ({$roleLabel})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // ✅ Apply pagination AFTER transformation (like junior)
        $perPage = 10;
        $currentPage = $page;
        $pagedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformed->forPage($currentPage, $perPage),
            $transformed->count(),
            $perPage,
            $currentPage,
            [
                'path' => url()->current(),
                'query' => $request->query() // ✅ keeps date, junior, search
            ]
        );



        $juniorUsers = \App\Models\User::where('is_deleted', 0)->whereIn('role', ['junior', 'senior'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);


        $todayDate = Carbon::now('America/New_York')->toDateString();
        $createdByKey = $authUser->id . '|junior';

        // Base query for today & this user
        $todayBaseQuery = GoogleSheetData::where('created_by', 'like', "{$createdByKey}%")
            ->whereDate('updated_at', $todayDate);

        // Total calls today
        $StotalCalls = (clone $todayBaseQuery)->count();

        // Individual Exe Remark counts
        $ScalledAndMailedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Called & Mailed')
            ->whereDate('followup', $todayDate)
            ->count();

        $SnotInterestedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Not Interested')
            ->whereNull('TransferRemark')
            ->count();

        $SinterestedCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Interested')
            ->whereNull('TransferRemark')
            ->count();

        $SothersCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Others')
            ->whereNull('TransferRemark')
            ->count();

        $SvmCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'VM')
            ->whereNull('TransferRemark')
            ->count();

        $SbusyCalls = (clone $todayBaseQuery)
            ->where('Exe_Remarks', 'Busy')
            ->whereNull('TransferRemark')
            ->count();

        // Grouped array (easy to use in Blade / AJAX)
        $exeRemarkCounts = [
            'total_calls'       => $StotalCalls,
            'called_and_mailed' => $ScalledAndMailedCalls,
            'not_interested'    => $SnotInterestedCalls,
            'interested'        => $SinterestedCalls,
            'others'            => $SothersCalls,
            'vm'                => $SvmCalls,
            'busy'              => $SbusyCalls,
        ];


        // ✅ Handle AJAX pagination and search
        if ($request->ajax()) {
            return view('database.partials.juniortra_table', ['data' => $pagedData, 'juniorUsers' => $juniorUsers, 'exeRemarkCounts' => $exeRemarkCounts])->render();
        }

        return view('database.juniortra', ['data' => $pagedData, 'juniorUsers' => $juniorUsers, 'exeRemarkCounts' => $exeRemarkCounts]);
    }

    public function juniorfetch(Request $request)
    {
        $request->validate([
            'sheet_link' => 'required|url'
        ]);

        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $request->sheet_link, $matches);
        $spreadsheetId = $matches[1] ?? null;

        if (!$spreadsheetId) {
            return back()->with('error', 'Invalid Google Sheet link');
        }

        $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";
        $csvData = @file_get_contents($csvUrl);

        if ($csvData === false) {
            return back()->with('error', 'Unable to fetch Google Sheet (maybe private?)');
        }

        $rows = array_map('str_getcsv', explode("\n", trim($csvData)));
        $header = array_shift($rows);

        $rowIndex = 2;
        $user = Auth::user();

        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;
            if (count($row) !== count($header)) continue;

            $rowData = array_combine($header, $row);

            // Map CSV headers to database columns
            $mappedData = [
                'sheet_row_number' => $rowIndex,
                'Date' => isset($rowData['Date']) ? \Carbon\Carbon::createFromFormat('m/d/Y', $rowData['Date'])->format('Y-m-d') : null,
                'Name' => $rowData['Name'] ?? null,
                'Email_Address' => $rowData['Email Address'] ?? null,
                'Phone_Number' => $rowData['Phone Number'] ?? null,
                'Location' => $rowData['Location'] ?? null,
                'Relocation' => $rowData['Relocation'] ?? null,
                'Graduation_Date' => isset($rowData['Graduation Date']) ? \Carbon\Carbon::createFromFormat('m/d/Y', $rowData['Graduation Date'])->format('Y-m-d') : null,
                'Immigration' => $rowData['Immigration'] ?? null,
                'Course' => $rowData['Course'] ?? null,
                'Amount' => isset($rowData['Amount']) ? (float) str_replace(['$', ','], '', $rowData['Amount']) : null,
                'Qualification' => $rowData['Qualification'] ?? null,
                'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
                'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
                'Time_Zone' => $rowData['Time Zone'] ?? null,
                'View' => $rowData['View'] ?? null,
                'created_by' => "{$user->id}|{$user->role}",
            ];

            GoogleSheetData::updateOrCreate(
                ['sheet_row_number' => $rowIndex],
                $mappedData
            );

            $rowIndex++;
        }

        return redirect()->route('google.sheet.junior')->with('success', 'Data fetched successfully!');
    }

    public function juniorupdatetra(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'ID is required'
            ]);
        }

        $row = GoogleSheetData::find($id);

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Row not found'
            ]);
        }

        try {
            // Existing logic (UNCHANGED)
            $row->transfers = 1;
            $row->updated_at = now();

            // ✅ NEW: store only date using Laravel timezone
            $row->transfer_date = Carbon::now()->toDateString();

            $row->save();

            return response()->json([
                'success' => true,
                'message' => 'Transfer updated successfully',
                'id' => $row->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ]);
        }
    }

    public function juniorupdaterej(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'ID is required'
            ]);
        }

        $row = GoogleSheetData::find($id);

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Row not found'
            ]);
        }

        try {
            // ✅ NEW CHECK (does not modify existing logic)
            if (is_null($row->RejectedRemark)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please Fill Rejected Remark'
                ]);
            }

            // Auth user
            $user = Auth::user();

            // Update rejected flag
            $row->rejected = 1;

            // Update Exe_Remarks to 'Others'
            $row->Exe_Remarks = 'Others';

            // Clear followup field
            $row->followup = null;
            $row->created_by = str_replace(':0|senior', '', $row->created_by);

            // Append update info to Remark
            $updatedBy = $user->name;
            $updatedAt = now()->format('Y-m-d H:i:s');

            $newRemarkEntry = "Called and Mailed Rejected | Updated by {$updatedBy} on {$updatedAt}";

            $existingRemark = $row->Remark ?? '';

            $row->Remark = trim(
                $existingRemark
                    ? $existingRemark . PHP_EOL . $newRemarkEntry
                    : $newRemarkEntry
            );

            $row->updated_at = now();
            $row->save();

            return response()->json([
                'success' => true,
                'message' => 'Rejected updated successfully',
                'id' => $row->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ]);
        }
    }


    public function juniorupdaterejected(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $id = $request->input('id');
            if (!$id) {
                return response()->json(['success' => false, 'message' => 'ID is required']);
            }

            //  Lock the row being updated
            $row = GoogleSheetData::where('id', $id)->lockForUpdate()->first();
            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Row not found']);
            }

            $rowData = json_decode($request->input('data'), true);
            if (empty($rowData)) {
                return response()->json(['success' => false, 'message' => 'No data provided']);
            }

            $email = $rowData['Email Address'] ?? $row->Email_Address;
            $phone = $rowData['Phone Number'] ?? $row->Phone_Number;
            $name  = $rowData['Name'] ?? $row->Name;
            $date  = $rowData['Date'] ?? $row->Date;

            if (empty($name)) {
                return response()->json(['success' => false, 'message' => 'Name is required.']);
            }

            if (empty($date)) {
                return response()->json(['success' => false, 'message' => 'Date is required.']);
            }

            $user = Auth::user();

            // Handle resume file upload - Save actual file content
            if ($request->hasFile('resume')) {
                $file = $request->file('resume');

                // Allowed Word MIME types
                $allowed = [
                    'application/pdf',
                    'application/msword', // .doc
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                ];

                if (!in_array($file->getMimeType(), $allowed)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only PDF or Word files (.pdf, .doc, .docx) are allowed'
                    ]);
                }

                // Generate unique filename
                $timestamp = now()->format('Ymd_His');
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

                try {
                    // Store the actual file content
                    $filePath = $file->storeAs('resumes', $newName, 'public');

                    // Delete old resume file if exists
                    if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                        Storage::disk('public')->delete($row->resume);
                    }

                    $row->resume = $filePath;
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
                }
            }

            // --- Prepare update data with null for empty fields ---
            $updateData = [
                'Date' => !empty($rowData['Date']) ? $this->parseDate($rowData['Date']) : null,
                'Name' => $rowData['Name'] ?? null,
                'Email_Address' => $email, // keep provided email
                'Phone_Number' => $phone,  // keep provided phone
                'Location' => $rowData['Location'] ?? null,
                'Remark' => $rowData['Remark'] ?? null,
                'RejectedRemark' => $rowData['RejectedRemark'] ?? null,
                'Relocation' => $rowData['Relocation'] ?? null,
                'Graduation_Date' => !empty($rowData['Graduation Date']) ? $this->parseDate($rowData['Graduation Date']) : null,
                'Immigration' => $rowData['Immigration'] ?? null,
                'Course' => $rowData['Course'] ?? null,
                'Amount' => isset($rowData['Amount']) && $rowData['Amount'] !== '' ? $this->parseAmount($rowData['Amount']) : 469, // ✅ default 469
                'Qualification' => $rowData['Qualification'] ?? null,
                'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
                'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
                'Time_Zone' => $rowData['Time Zone'] ?? null,
                'updated_at' => now(),
            ];

            // Only update resume if it was uploaded
            if ($request->hasFile('resume')) {
                $updateData['resume'] = $row->resume;
            }

            // === New created_by logic ===
            if (isset($rowData['Exe Remarks']) && $rowData['Exe Remarks'] === 'Called & Mailed') {

                // Append only once if not already present
                if (strpos($row->created_by, ':0|senior') === false) {
                    $updateData['created_by'] = $row->created_by . ':0|senior';
                } else {
                    $updateData['created_by'] = $row->created_by;
                }

                $updateData['rejected'] = 0;

                // === NEW: set followup date ONLY if null ===
                if (empty($row->followup)) {
                    $updateData['followup'] = \Carbon\Carbon::now('America/New_York')->format('Y-m-d');
                }
            } else {
                $updateData['created_by'] = $row->created_by;
            }

            // === Append remark if Exe Remarks changed ===
            $oldExeRemarks = $row->Exe_Remarks;

            $updatedBy = Auth::user()->name;
            $updatedAt = now()->format('d-m-Y H:i');

            if (
                isset($rowData['Exe Remarks']) &&
                $rowData['Exe Remarks'] !== $oldExeRemarks
            ) {
                $newRemarkEntry = "{$rowData['Exe Remarks']} | Updated by {$updatedBy} on {$updatedAt}";

                // Append to existing remark (keep history)
                $existingRemark =
                    $rowData['Remark']
                    ?? $row->Remark
                    ?? '';

                $updateData['Remark'] = trim(
                    $existingRemark
                        ? $existingRemark . PHP_EOL . $newRemarkEntry
                        : $newRemarkEntry
                );
            } else {
                // Exe Remarks NOT changed → still log update info
                $newRemarkEntry = "Updated by {$updatedBy} on {$updatedAt}";

                $existingRemark =
                    $rowData['Remark']
                    ?? $row->Remark
                    ?? '';

                $updateData['Remark'] = trim(
                    $existingRemark
                        ? $existingRemark . PHP_EOL . $newRemarkEntry
                        : $newRemarkEntry
                );
            }

            // === Append RejectedRemark like Remark (keep history) ===
            $oldRejectedRemark = $row->RejectedRemark ?? '';

            if (
                isset($rowData['RejectedRemark']) &&
                $rowData['RejectedRemark'] !== '' &&
                $rowData['RejectedRemark'] !== $oldRejectedRemark
            ) {
                // RejectedRemark changed
                $newRejectedEntry = "{$rowData['RejectedRemark']} | Updated by {$updatedBy} on {$updatedAt}";

                $updateData['RejectedRemark'] = trim(
                    $oldRejectedRemark
                        ? $oldRejectedRemark . PHP_EOL . $newRejectedEntry
                        : $newRejectedEntry
                );
            }


            foreach ($updateData as $key => $value) {
                if ($value === '' && !in_array($key, ['Email_Address', 'Remark', 'Name', 'Amount'])) {
                    $updateData[$key] = null;
                }
            }

            try {
                $user = Auth::user();
                // ONLY NOW update
                $row->update($updateData);


                $mailMessage = 'No email sent.';
                $name = $rowData['Name'] ?? null;
                $amount = isset($rowData['Amount']) ? $this->parseAmount($rowData['Amount']) : $row->Amount;

                // --- Send email if Exe_Remarks is "Called & Mailed" ---
                if (isset($rowData['Exe Remarks']) && $rowData['Exe Remarks'] === 'Called & Mailed' && !empty($email)) {
                    try {
                        $smtp = SmtpSetting::where('user_id', $user->id)->first();
                        if (!$smtp) {
                            return response()->json([
                                'message' => 'No SMTP settings found.'
                            ]);
                        } else {
                            // Configure mailer dynamically (same as test() method)
                            config([
                                'mail.mailers.smtp.transport' => $smtp->mailer,
                                'mail.mailers.smtp.host' => $smtp->host,
                                'mail.mailers.smtp.port' => $smtp->port,
                                'mail.mailers.smtp.username' => $smtp->username,
                                'mail.mailers.smtp.password' => decrypt($smtp->password),
                                'mail.mailers.smtp.encryption' => $smtp->encryption,
                                'mail.from.address' => $smtp->from_address,
                                'mail.from.name' => $smtp->from_name,
                            ]);

                            // --- Fetch Email Template from Database ---
                            $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                            if ($template) {
                                $subject = $template->subject;
                                $messageBody = $template->body;
                            } else {
                                // Fallback if template not found
                                $subject = "Unlock Career Stability with Fortune 500 Projects !";
                                $messageBody =
                                    "Hi {$name},\n\n" .
                                    "I hope this message finds you well.\n\n" .
                                    "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                                    "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                                    "What Makes Synergie Different?\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                                    "Here’s what you can expect when you join our community:\n\n" .
                                    "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                                    "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                                    "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                                    "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                                    "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                                    "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                                    "More Than a Paycheck — A Path to Prosperity\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                                    "                  - Full-Time Roles: \$40–\$50/hr\n" .
                                    "                  - Part-Time Roles: \$15–\$25/hr\n" .
                                    "                  - Paid Internships available\n" .
                                    "                  - 15% Salary Raise every 6 months based on performance\n" .
                                    "                  - 12 Days Paid Vacation annually\n" .
                                    "                  - Relocation Assistance for client deployments\n\n" .
                                    "Comprehensive Benefits That Put You First\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                                    "                  - Health, Dental & Vision Insurance\n" .
                                    "                  - Short- & Long-Term Disability Insurance\n" .
                                    "                  - Life Insurance & 401(k) Retirement Plan\n" .
                                    "                  - Legal & Immigration Support\n" .
                                    "                  - Tax Assistance & Transparent Payroll\n" .
                                    "                  - Workers’ Compensation—your safety is our priority\n\n" .
                                    "Support Tailored for International Talent\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                                    "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                                    "                  - Full STEM Extension & OPT/CPT Support\n" .
                                    "                  - H1B Sponsorship after project onboarding\n" .
                                    "                  - Relocation & Immigration Documentation\n" .
                                    "                  - Ongoing Green Card Processing Assistance\n\n" .
                                    "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                                    "What You’ll Gain:\n\n" .
                                    "                  - Live Zoom sessions & recorded expert sessions\n" .
                                    "                  - Real-time project simulations & hands-on assignments\n" .
                                    "                  - One-on-one resume branding & mock interviews\n" .
                                    "                  - Global Certificate of Completion & recruiter access\n" .
                                    "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                                    "Let’s Take the First Step Together\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                                    "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                                    "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                                    "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                                    "Visit Our Website: https://www.synergiesystems.com/";
                            }

                            // --- Send Email (No Template Logic Changed) ---
                            Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                                $message->from($smtp->from_address, $smtp->from_name)
                                    ->to($email)
                                    ->subject($subject);
                            });

                            $mailMessage = "Email sent successfully to {$email}!";
                        }
                    } catch (\Exception $e) {
                        $mailMessage = 'Failed to send email: ' . $e->getMessage();
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Row updated successfully',
                    'id' => $row->id,
                    'sheet_row_number' => $row->sheet_row_number,
                    'resume_path' => !empty($row->resume) ? true : false,
                    'mail_message' => $mailMessage
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fill Full Detail to Save.'
                ]);
            }
        });
    }



    public function juniorupdate(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $id = $request->input('id');
            if (!$id) {
                return response()->json(['success' => false, 'message' => 'ID is required']);
            }

            //  Lock the row being updated
            $row = GoogleSheetData::where('id', $id)->lockForUpdate()->first();
            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Row not found']);
            }

            $rowData = json_decode($request->input('data'), true);
            if (empty($rowData)) {
                return response()->json(['success' => false, 'message' => 'No data provided']);
            }

            $email = $rowData['Email Address'] ?? $row->Email_Address;
            $phone = $rowData['Phone Number'] ?? $row->Phone_Number;
            $name  = $rowData['Name'] ?? $row->Name;
            $date  = $rowData['Date'] ?? $row->Date;

            if (empty($name)) {
                return response()->json(['success' => false, 'message' => 'Name is required.']);
            }

            if (empty($date)) {
                return response()->json(['success' => false, 'message' => 'Date is required.']);
            }

            $user = Auth::user();

            // Handle resume file upload - Save actual file content
            if ($request->hasFile('resume')) {
                $file = $request->file('resume');

                // Allowed Word MIME types
                $allowed = [
                    'application/pdf',
                    'application/msword', // .doc
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                ];

                if (!in_array($file->getMimeType(), $allowed)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only PDF or Word files (.pdf, .doc, .docx) are allowed'
                    ]);
                }

                // Generate unique filename
                $timestamp = now()->format('Ymd_His');
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

                try {
                    // Store the actual file content
                    $filePath = $file->storeAs('resumes', $newName, 'public');

                    // Delete old resume file if exists
                    if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                        Storage::disk('public')->delete($row->resume);
                    }

                    $row->resume = $filePath;
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
                }
            }

            // --- Prepare update data with null for empty fields ---
            $updateData = [
                'Date' => !empty($rowData['Date']) ? $this->parseDate($rowData['Date']) : null,
                'Name' => $rowData['Name'] ?? null,
                'Email_Address' => $email, // keep provided email
                'Phone_Number' => $phone,  // keep provided phone
                'Location' => $rowData['Location'] ?? null,
                'Remark' => $rowData['Remark'] ?? null,
                'Relocation' => $rowData['Relocation'] ?? null,
                'Graduation_Date' => !empty($rowData['Graduation Date']) ? $this->parseDate($rowData['Graduation Date']) : null,
                'Immigration' => $rowData['Immigration'] ?? null,
                'Course' => $rowData['Course'] ?? null,
                'Amount' => isset($rowData['Amount']) && $rowData['Amount'] !== '' ? $this->parseAmount($rowData['Amount']) : 469, // ✅ default 469
                'Qualification' => $rowData['Qualification'] ?? null,
                'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
                'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
                'Time_Zone' => $rowData['Time Zone'] ?? null,
                'updated_at' => now(),
            ];

            // Only update resume if it was uploaded
            if ($request->hasFile('resume')) {
                $updateData['resume'] = $row->resume;
            }

            // === New created_by logic ===
            if (isset($rowData['Exe Remarks']) && $rowData['Exe Remarks'] === 'Called & Mailed') {

                // Append only once if not already present
                if (strpos($row->created_by, ':0|senior') === false) {
                    $updateData['created_by'] = $row->created_by . ':0|senior';
                } else {
                    $updateData['created_by'] = $row->created_by;
                }

                // === NEW: set followup date ONLY if null ===
                if (empty($row->followup)) {
                    $updateData['followup'] = \Carbon\Carbon::now('America/New_York')->format('Y-m-d');
                }
            } else {
                $updateData['created_by'] = $row->created_by;
            }

            // === Append remark if Exe Remarks changed ===
            $oldExeRemarks = $row->Exe_Remarks;

            $updatedBy = Auth::user()->name;
            $updatedAt = now()->format('d-m-Y H:i');

            if (
                isset($rowData['Exe Remarks']) &&
                $rowData['Exe Remarks'] !== $oldExeRemarks
            ) {
                $newRemarkEntry = "{$rowData['Exe Remarks']} | Updated by {$updatedBy} on {$updatedAt}";

                // Append to existing remark (keep history)
                $existingRemark =
                    $rowData['Remark']
                    ?? $row->Remark
                    ?? '';

                $updateData['Remark'] = trim(
                    $existingRemark
                        ? $existingRemark . PHP_EOL . $newRemarkEntry
                        : $newRemarkEntry
                );
            } else {
                // Exe Remarks NOT changed → still log update info
                $newRemarkEntry = "Updated by {$updatedBy} on {$updatedAt}";

                $existingRemark =
                    $rowData['Remark']
                    ?? $row->Remark
                    ?? '';

                $updateData['Remark'] = trim(
                    $existingRemark
                        ? $existingRemark . PHP_EOL . $newRemarkEntry
                        : $newRemarkEntry
                );
            }



            foreach ($updateData as $key => $value) {
                if ($value === '' && !in_array($key, ['Email_Address', 'Remark', 'Name', 'Amount'])) {
                    $updateData[$key] = null;
                }
            }

            try {
                $user = Auth::user();
                // ONLY NOW update
                $row->update($updateData);


                $mailMessage = 'No email sent.';
                $name = $rowData['Name'] ?? null;
                $amount = isset($rowData['Amount']) ? $this->parseAmount($rowData['Amount']) : $row->Amount;

                // --- Send email if Exe_Remarks is "Called & Mailed" ---
                if (isset($rowData['Exe Remarks']) && $rowData['Exe Remarks'] === 'Called & Mailed' && !empty($email)) {
                    try {
                        $smtp = SmtpSetting::where('user_id', $user->id)->first();
                        if (!$smtp) {
                            return response()->json([
                                'message' => 'No SMTP settings found.'
                            ]);
                        } else {
                            // Configure mailer dynamically (same as test() method)
                            config([
                                'mail.mailers.smtp.transport' => $smtp->mailer,
                                'mail.mailers.smtp.host' => $smtp->host,
                                'mail.mailers.smtp.port' => $smtp->port,
                                'mail.mailers.smtp.username' => $smtp->username,
                                'mail.mailers.smtp.password' => decrypt($smtp->password),
                                'mail.mailers.smtp.encryption' => $smtp->encryption,
                                'mail.from.address' => $smtp->from_address,
                                'mail.from.name' => $smtp->from_name,
                            ]);

                            // --- Fetch Email Template from Database ---
                            $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                            if ($template) {
                                $subject = $template->subject;
                                $messageBody = $template->body;
                            } else {
                                // Fallback if template not found
                                $subject = "Unlock Career Stability with Fortune 500 Projects !";
                                $messageBody =
                                    "Hi {$name},\n\n" .
                                    "I hope this message finds you well.\n\n" .
                                    "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                                    "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                                    "What Makes Synergie Different?\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                                    "Here’s what you can expect when you join our community:\n\n" .
                                    "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                                    "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                                    "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                                    "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                                    "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                                    "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                                    "More Than a Paycheck — A Path to Prosperity\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                                    "                  - Full-Time Roles: \$40–\$50/hr\n" .
                                    "                  - Part-Time Roles: \$15–\$25/hr\n" .
                                    "                  - Paid Internships available\n" .
                                    "                  - 15% Salary Raise every 6 months based on performance\n" .
                                    "                  - 12 Days Paid Vacation annually\n" .
                                    "                  - Relocation Assistance for client deployments\n\n" .
                                    "Comprehensive Benefits That Put You First\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                                    "                  - Health, Dental & Vision Insurance\n" .
                                    "                  - Short- & Long-Term Disability Insurance\n" .
                                    "                  - Life Insurance & 401(k) Retirement Plan\n" .
                                    "                  - Legal & Immigration Support\n" .
                                    "                  - Tax Assistance & Transparent Payroll\n" .
                                    "                  - Workers’ Compensation—your safety is our priority\n\n" .
                                    "Support Tailored for International Talent\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                                    "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                                    "                  - Full STEM Extension & OPT/CPT Support\n" .
                                    "                  - H1B Sponsorship after project onboarding\n" .
                                    "                  - Relocation & Immigration Documentation\n" .
                                    "                  - Ongoing Green Card Processing Assistance\n\n" .
                                    "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                                    "What You’ll Gain:\n\n" .
                                    "                  - Live Zoom sessions & recorded expert sessions\n" .
                                    "                  - Real-time project simulations & hands-on assignments\n" .
                                    "                  - One-on-one resume branding & mock interviews\n" .
                                    "                  - Global Certificate of Completion & recruiter access\n" .
                                    "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                                    "Let’s Take the First Step Together\n" .
                                    "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                    "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                                    "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                                    "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                                    "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                                    "Visit Our Website: https://www.synergiesystems.com/";
                            }

                            // --- Send Email (No Template Logic Changed) ---
                            Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                                $message->from($smtp->from_address, $smtp->from_name)
                                    ->to($email)
                                    ->subject($subject);
                            });

                            $mailMessage = "Email sent successfully to {$email}!";
                        }
                    } catch (\Exception $e) {
                        $mailMessage = 'Failed to send email: ' . $e->getMessage();
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Row updated successfully',
                    'id' => $row->id,
                    'sheet_row_number' => $row->sheet_row_number,
                    'resume_path' => !empty($row->resume) ? true : false,
                    'mail_message' => $mailMessage
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fill Full Detail to Save.'
                ]);
            }
        });
    }

    public function juniorcandmupdate(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        $user = Auth::user();

        // ✅ Map frontend keys to database columns
        $updateData = [];

        if (array_key_exists('Remark', $rowData)) {
            $newRemark = trim($rowData['Remark']);

            // Append only if remark has changed
            if ($newRemark !== $row->Remark) {
                $existingRemark = $row->Remark ?? '';

                $remarkEntry = $newRemark
                    . ' | Updated by ' . $user->name
                    . ' on ' . now()->format('d-m-Y H:i');

                $updateData['Remark'] = trim(
                    $existingRemark
                        ? $existingRemark . PHP_EOL . $remarkEntry
                        : $remarkEntry
                );
            }
        }

        if (array_key_exists('1st Follow Up Remarks', $rowData)) {
            $updateData['First_Follow_Up_Remarks'] = $rowData['1st Follow Up Remarks'];
        }

        // Validate Remark is mandatory if it exists
        if (array_key_exists('Remark', $updateData) && $updateData['Remark'] === '') {
            return response()->json([
                'success' => false,
                'message' => 'Remark field is required before updating.'
            ]);
        }

        if (empty($updateData)) {
            return response()->json(['success' => false, 'message' => 'No valid fields to update']);
        }

        try {
            $row->timestamps = false;

            foreach ($updateData as $key => $value) {
                $row->$key = $value;
            }

            $row->save();

            return response()->json([
                'success' => true,
                'message' => 'Remarks updated successfully',
                'updated_fields' => $updateData,
                'id' => $row->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ]);
        }
    }

    public function juniorstore(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $rowData = json_decode($request->input('data'), true);
            if (empty($rowData)) {
                return response()->json(['success' => false, 'message' => 'No data provided']);
            }

            $email = $rowData['Email Address'] ?? null;
            $phone = $rowData['Phone Number'] ?? null;
            $name  = $rowData['Name'] ?? null;
            $date  = $rowData['Date'] ?? null;

            if (empty($name)) {
                return response()->json(['success' => false, 'message' => 'Name is required.']);
            }

            if (empty($date)) {
                return response()->json(['success' => false, 'message' => 'Date is required.']);
            }

            if (empty($email)) {
                return response()->json(['success' => false, 'message' => 'Email is required.']);
            }

            if (empty($phone)) {
                return response()->json(['success' => false, 'message' => 'Phone is required.']);
            }

            $user = Auth::user();

            //  Atomic duplicate email check
            if (!empty($email)) {
                $emailExistsForUser = GoogleSheetData::where('Email_Address', $email)
                    ->where('created_by', 'like', $user->id . '|%')
                    ->lockForUpdate()
                    ->exists();

                if ($emailExistsForUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email ID already exists for you.'
                    ]);
                }
            }

            //  Atomic sheet_row_number generation
            $nextRow = GoogleSheetData::lockForUpdate()->max('sheet_row_number') + 1;


            $record = new GoogleSheetData();
            $record->sheet_row_number = $nextRow;

            // NEW: mark only the current record as current
            $record->is_current = 1;

            // --- Column map ---
            $columnMap = [
                'Date' => 'Date',
                'Name' => 'Name',
                'Email Address' => 'Email_Address',
                'Phone Number' => 'Phone_Number',
                'Location' => 'Location',
                'Remark' => 'Remark',
                'Relocation' => 'Relocation',
                'Graduation Date' => 'Graduation_Date',
                'Immigration' => 'Immigration',
                'Course' => 'Course',
                'Amount' => 'Amount',
                'Qualification' => 'Qualification',
                'Exe Remarks' => 'Exe_Remarks',
                '1st Follow Up Remarks' => 'First_Follow_Up_Remarks',
                'Time Zone' => 'Time_Zone',
            ];

            $exeRemarksValue = null;
            $amount = null;

            foreach ($columnMap as $frontendKey => $dbColumn) {
                $val = $rowData[$frontendKey] ?? null;

                if (in_array($dbColumn, ['Date', 'Graduation_Date']) && !empty($val)) {
                    $val = $this->parseDate($val);
                }

                if ($dbColumn === 'Amount' && !empty($val)) {
                    $val = $this->parseAmount($val);
                    $amount = $val;
                }

                if ($dbColumn === 'Exe_Remarks') {
                    $exeRemarksValue = $val;
                }

                if (empty($val) && !in_array($dbColumn, ['Email_Address', 'Phone_Number'])) {
                    $val = null;
                }

                $record->$dbColumn = $val;
            }

            // --- Append Exe Remarks into Remark (Audit Pattern) ---
            if (!empty($exeRemarksValue)) {

                $existingRemark = $record->Remark ?? '';

                $newRemarkEntry =
                    $exeRemarksValue .
                    ' | Added by ' . $user->name .
                    ' on ' . now()->format('d-m-Y H:i');

                $record->Remark = trim(
                    $existingRemark
                        ? $existingRemark . PHP_EOL . $newRemarkEntry
                        : $newRemarkEntry
                );
            }


            // --- created_by logic ---
            if ($exeRemarksValue === 'Called & Mailed') {

                $record->created_by = $user->id . '|junior:0|senior';

                // ✅ NEW: set followup date only if empty
                if (empty($record->followup)) {
                    $record->followup = \Carbon\Carbon::now('America/New_York')->format('Y-m-d');
                }
            } else {

                $record->created_by = $user->id . '|junior';
            }

            // --- Resume upload ---
            if ($request->hasFile('resume')) {
                $file = $request->file('resume');

                $allowed = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ];

                if (!in_array($file->getMimeType(), $allowed)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only PDF or Word files (.pdf, .doc, .docx) are allowed'
                    ]);
                }

                $timestamp = now()->format('Ymd_His');
                $filename  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $newName   = Str::slug($filename) . "_{$timestamp}.{$extension}";

                try {
                    $record->resume = $file->storeAs('resumes', $newName, 'public');
                } catch (\Exception $e) {
                    $record->resume = null;
                }
            }

            try {
                $record->save();
                $saveMessage = 'Record saved successfully.';
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fill Full Detail to Save.'
                ]);
            }

            $mailMessage = 'No email sent.';



            // --- Send Email if Exe_Remarks is "Called & Mailed" ---
            if ($exeRemarksValue === 'Called & Mailed' && !empty($email)) {
                try {
                    $smtp = SmtpSetting::where('user_id', $user->id)->first();
                    if (!$smtp) {
                        return response()->json([
                            'message' => 'No SMTP settings found.'
                        ]);
                    } else {
                        // Configure mailer dynamically (same as test() method)
                        config([
                            'mail.mailers.smtp.transport' => $smtp->mailer,
                            'mail.mailers.smtp.host' => $smtp->host,
                            'mail.mailers.smtp.port' => $smtp->port,
                            'mail.mailers.smtp.username' => $smtp->username,
                            'mail.mailers.smtp.password' => decrypt($smtp->password),
                            'mail.mailers.smtp.encryption' => $smtp->encryption,
                            'mail.from.address' => $smtp->from_address,
                            'mail.from.name' => $smtp->from_name,
                        ]);

                        // --- Fetch Email Template from Database ---
                        $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                        if ($template) {
                            $subject = $template->subject;
                            $messageBody = $template->body;
                        } else {
                            // Fallback if template not found
                            $subject = "Unlock Career Stability with Fortune 500 Projects !";
                            $messageBody =
                                "Hi {$name},\n\n" .
                                "I hope this message finds you well.\n\n" .
                                "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                                "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                                "What Makes Synergie Different?\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                                "Here’s what you can expect when you join our community:\n\n" .
                                "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                                "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                                "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                                "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                                "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                                "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                                "More Than a Paycheck — A Path to Prosperity\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                                "                  - Full-Time Roles: \$40–\$50/hr\n" .
                                "                  - Part-Time Roles: \$15–\$25/hr\n" .
                                "                  - Paid Internships available\n" .
                                "                  - 15% Salary Raise every 6 months based on performance\n" .
                                "                  - 12 Days Paid Vacation annually\n" .
                                "                  - Relocation Assistance for client deployments\n\n" .
                                "Comprehensive Benefits That Put You First\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                                "                  - Health, Dental & Vision Insurance\n" .
                                "                  - Short- & Long-Term Disability Insurance\n" .
                                "                  - Life Insurance & 401(k) Retirement Plan\n" .
                                "                  - Legal & Immigration Support\n" .
                                "                  - Tax Assistance & Transparent Payroll\n" .
                                "                  - Workers’ Compensation—your safety is our priority\n\n" .
                                "Support Tailored for International Talent\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                                "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                                "                  - Full STEM Extension & OPT/CPT Support\n" .
                                "                  - H1B Sponsorship after project onboarding\n" .
                                "                  - Relocation & Immigration Documentation\n" .
                                "                  - Ongoing Green Card Processing Assistance\n\n" .
                                "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                                "What You’ll Gain:\n\n" .
                                "                  - Live Zoom sessions & recorded expert sessions\n" .
                                "                  - Real-time project simulations & hands-on assignments\n" .
                                "                  - One-on-one resume branding & mock interviews\n" .
                                "                  - Global Certificate of Completion & recruiter access\n" .
                                "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                                "Let’s Take the First Step Together\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                                "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                                "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                                "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                                "Visit Our Website: https://www.synergiesystems.com/";
                        }

                        // --- Send Email (No Template Logic Changed) ---
                        Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                            $message->from($smtp->from_address, $smtp->from_name)
                                ->to($email)
                                ->subject($subject);
                        });

                        $mailMessage = "Email sent successfully to {$email}!";
                    }
                } catch (\Exception $e) {
                    $mailMessage = 'Failed to send email: ' . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'id' => $record->id,
                'sheet_row_number' => $record->sheet_row_number,
                'save_message' => $saveMessage,
                'mail_message' => $mailMessage,
                'resume_path' => !empty($record->resume) ? true : false
            ]);
        });
    }



    // Add a method to serve the PDF files
    public function viewJuniorResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // --- If already PDF, return directly ---
        if ($extension === 'pdf') {
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        }

        // --- Convert DOC/DOCX to PDF ---
        if (in_array($extension, ['doc', 'docx'])) {

            // Load Word file using PHPWord
            $phpWord = IOFactory::load($filePath);

            // Create a temporary HTML file from Word content
            $tempHtml = storage_path('app/temp_' . time() . '.html');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($tempHtml);

            // Convert HTML to PDF via Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(file_get_contents($tempHtml));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Output PDF content
            $pdfOutput = $dompdf->output();

            // Remove temp HTML
            unlink($tempHtml);

            return response($pdfOutput, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf"');
        }

        abort(415, 'Unsupported file format');
    }


    // Add a method to download the PDF files
    public function downloadjuniorResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }


    public function accountant(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value

        // Build patterns for LIKE match
        $userPattern = "%:" . $authUser->id . "|accountant";
        $zeroPattern = "%:0|accountant";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern, $zeroPattern) {
            // Direct match with "id|accountant"
            $q->where('created_by', $authUser->id . '|accountant')
                // Direct match with "0|accountant"
                ->orWhere('created_by', '0|accountant')
                // Matches if last part is ":id|accountant"
                ->orWhere('created_by', 'LIKE', $userPattern)
                // Matches if last part is ":0|accountant"
                ->orWhere('created_by', 'LIKE', $zeroPattern);
        })
            // Ensure it's truly the LAST part of created_by
            ->where(function ($q) use ($authUser) {
                $q->whereRaw("RIGHT(created_by, LENGTH(?)) = ?", [$authUser->id . '|accountant', $authUser->id . '|accountant'])
                    ->orWhereRaw("RIGHT(created_by, LENGTH(?)) = ?", ['0|accountant', '0|accountant'])
                    // ✅ MUST contain EXACTLY 2 occurrences of "accountant"
                    ->whereRaw("
            (LENGTH(created_by) - LENGTH(REPLACE(created_by, 'accountant', ''))) / LENGTH('accountant') = 2
        ");
            });



        // Filter by selected junior
        if ($juniorUserId) {
            $query->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // Pagination with appended filters for AJAX navigation
        $data = $query->orderBy('Date', 'desc')->paginate(10);
        $data->appends([
            'search' => $search,
            'row_id' => $rowId,
            'junior_user' => $juniorUserId,
        ]);

        // Map forwarded_by dynamically (multi-level like senior)
        $data->getCollection()->transform(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // Fetch junior users list for dropdown
        $juniorUsers = \App\Models\User::where('is_deleted', 0)->where('role', 'junior')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);


        // Handle AJAX request for both search and pagination
        if ($request->ajax()) {
            return view('database.partials.senior_table', compact('data'))->render();
        }

        return view('database.accountant', compact('data', 'juniorUsers'));
    }

    public function accountantcon(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value

        // Build patterns for LIKE match
        $userPattern = "%:" . $authUser->id . "|accountant";
        $zeroPattern = "%:0|accountant";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern, $zeroPattern) {
            // Direct match with "id|accountant"
            $q->where('created_by', $authUser->id . '|accountant')
                // Direct match with "0|accountant"
                ->orWhere('created_by', '0|accountant')
                // Matches if last part is ":id|accountant"
                ->orWhere('created_by', 'LIKE', $userPattern)
                // Matches if last part is ":0|accountant"
                ->orWhere('created_by', 'LIKE', $zeroPattern);
        })
            // Ensure it's truly the LAST part of created_by
            ->where(function ($q) use ($authUser) {
                $q->whereRaw("RIGHT(created_by, LENGTH(?)) = ?", [$authUser->id . '|accountant', $authUser->id . '|accountant'])
                    ->orWhereRaw("RIGHT(created_by, LENGTH(?)) = ?", ['0|accountant', '0|accountant'])
                    ->whereRaw("(LENGTH(created_by) - LENGTH(REPLACE(created_by, 'accountant', ''))) / LENGTH('accountant') = 1");
            });

        // Filter by selected junior
        if ($juniorUserId) {
            $query->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // Pagination with appended filters for AJAX navigation
        $data = $query->orderBy('Date', 'desc')->paginate(10);
        $data->appends([
            'search' => $search,
            'row_id' => $rowId,
            'junior_user' => $juniorUserId,
        ]);

        // Map forwarded_by dynamically (multi-level like senior)
        $data->getCollection()->transform(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // Fetch junior users list for dropdown
        $juniorUsers = \App\Models\User::where('is_deleted', 0)->where('role', 'junior')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);


        // Handle AJAX request for both search and pagination
        if ($request->ajax()) {
            return view('database.partials.senior_table', compact('data'))->render();
        }

        return view('database.accountantcon', compact('data', 'juniorUsers'));
    }

    public function accountantpaid(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');

        $query = GoogleSheetData::where(function ($q) {
            // Removed user_id|senior check
            // Only keep the accountant part filter
            $q->where(function ($q2) {
                $q2->whereRaw("created_by = '0|trainer'")
                    ->orWhereRaw("created_by LIKE '0|trainer:%'")
                    ->orWhereRaw("created_by LIKE '%:0|trainer'")
                    ->orWhereRaw("created_by LIKE '%:0|trainer:%'");
            });
        });

        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('Date', 'desc')->paginate(10);

        $data->getCollection()->transform(function ($item) use ($authUser) {

            $forwardedBy = '';

            if (!empty($item->created_by)) {
                // Split by ':' to handle multiple forwarded entries
                $entries = explode(':', $item->created_by);

                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                // Join all names for forwarded chain
                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });


        if (request()->ajax()) {
            return view('database.partials.senior_table', compact('data'))->render();
        }

        return view('database.accountantpaid', compact('data'));
    }

    public function associate(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');

        $query = GoogleSheetData::where(function ($q) {
            // Removed user_id|senior check
            // Only keep the accountant part filter
            $q->where(function ($q2) {
                $q2->whereRaw("created_by = '0|trainer'")
                    ->orWhereRaw("created_by LIKE '0|trainer:%'")
                    ->orWhereRaw("created_by LIKE '%:0|trainer'")
                    ->orWhereRaw("created_by LIKE '%:0|trainer:%'");
            });
        });

        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('Date', 'desc')->paginate(10);

        $data->getCollection()->transform(function ($item) use ($authUser) {

            $forwardedBy = '';

            if (!empty($item->created_by)) {
                // Split by ':' to handle multiple forwarded entries
                $entries = explode(':', $item->created_by);

                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                // Join all names for forwarded chain
                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });


        if (request()->ajax()) {
            return view('database.partials.senior_table', compact('data'))->render();
        }

        return view('database.associate', compact('data'));
    }

    public function seniorassociate(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');

        $query = GoogleSheetData::where(function ($q) {
            // Removed user_id|senior check
            // Only keep the accountant part filter
            $q->where(function ($q2) {
                $q2->whereRaw("created_by = '0|trainer'")
                    ->orWhereRaw("created_by LIKE '0|trainer:%'")
                    ->orWhereRaw("created_by LIKE '%:0|trainer'")
                    ->orWhereRaw("created_by LIKE '%:0|trainer:%'");
            });
        });

        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('Date', 'desc')->paginate(10);

        $data->getCollection()->transform(function ($item) use ($authUser) {

            $forwardedBy = '';

            if (!empty($item->created_by)) {
                // Split by ':' to handle multiple forwarded entries
                $entries = explode(':', $item->created_by);

                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                // Join all names for forwarded chain
                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });


        if (request()->ajax()) {
            return view('database.partials.senior_table', compact('data'))->render();
        }

        return view('database.seniorassociate', compact('data'));
    }

    public function candidateStore(Request $request)
    {
        // Validate
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // Check duplicate email
        if (GoogleSheetData::where('Email_Address', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email address already exists.'
            ]);
        }

        // Check duplicate phone
        if (GoogleSheetData::where('Phone_Number', $request->phone)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number already exists.'
            ]);
        }

        // Auto serial number
        $nextRow = (GoogleSheetData::max('sheet_row_number') ?? 0) + 1;

        // Create entry
        $candidate = GoogleSheetData::create([
            'sheet_row_number' => $nextRow,
            'Name'             => $request->name,
            'Email_Address'    => $request->email,
            'Phone_Number'     => $request->phone,
            'created_by'       => '0|senior:0|accountant:0|senior:0|accountant:0|accountant:0|trainer:0|accountant'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidate added successfully.',
            'data'    => $candidate
        ]);
    }





    public function writer(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');

        $query = GoogleSheetData::where(function ($q) {
            // Removed user_id|senior check
            // Only keep the accountant part filter
            $q->where(function ($q2) {
                $q2->whereRaw("created_by = '0|trainer'")
                    ->orWhereRaw("created_by LIKE '0|trainer:%'")
                    ->orWhereRaw("created_by LIKE '%:0|trainer'")
                    ->orWhereRaw("created_by LIKE '%:0|trainer:%'");
            });
        });

        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('Date', 'desc')->paginate(10);

        $data->getCollection()->transform(function ($item) use ($authUser) {

            $forwardedBy = '';

            if (!empty($item->created_by)) {
                // Split by ':' to handle multiple forwarded entries
                $entries = explode(':', $item->created_by);

                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                // Join all names for forwarded chain
                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });


        if (request()->ajax()) {
            return view('database.partials.senior_table', compact('data'))->render();
        }

        return view('database.writer', compact('data'));
    }


    public function accountantver(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value

        // Build patterns for LIKE match
        $userPattern = "%:" . $authUser->id . "|accountant";
        $zeroPattern = "%:0|accountant";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern, $zeroPattern) {
            // Direct match with "id|accountant"
            $q->where('created_by', $authUser->id . '|accountant')
                // Direct match with "0|accountant"
                ->orWhere('created_by', '0|accountant')
                // Matches if last part is ":id|accountant"
                ->orWhere('created_by', 'LIKE', $userPattern)
                // Matches if last part is ":0|accountant"
                ->orWhere('created_by', 'LIKE', $zeroPattern);
        })
            // Ensure it's truly the LAST part of created_by
            ->where(function ($q) use ($authUser) {
                $q->whereRaw("RIGHT(created_by, LENGTH(?)) = ?", [$authUser->id . '|accountant', $authUser->id . '|accountant'])
                    ->orWhereRaw("RIGHT(created_by, LENGTH(?)) = ?", ['0|accountant', '0|accountant'])
                    // ✅ MUST contain EXACTLY 3 occurrences of "accountant"
                    ->whereRaw("
            (LENGTH(created_by) - LENGTH(REPLACE(created_by, 'accountant', ''))) / LENGTH('accountant') = 3
        ");
            });



        // Filter by selected junior
        if ($juniorUserId) {
            $query->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // Pagination with appended filters for AJAX navigation
        $data = $query->orderBy('Date', 'desc')->paginate(10);
        $data->appends([
            'search' => $search,
            'row_id' => $rowId,
            'junior_user' => $juniorUserId,
        ]);

        // Map forwarded_by dynamically (multi-level like senior)
        $data->getCollection()->transform(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        // Fetch junior users list for dropdown
        $juniorUsers = \App\Models\User::where('is_deleted', 0)->where('role', 'junior')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);


        // Handle AJAX request for both search and pagination
        if ($request->ajax()) {
            return view('database.partials.senior_table', compact('data'))->render();
        }

        return view('database.accountantver', compact('data', 'juniorUsers'));
    }



    public function accountantfetch(Request $request)
    {
        $request->validate([
            'sheet_link' => 'required|url'
        ]);

        // Extract spreadsheet ID
        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $request->sheet_link, $matches);
        $spreadsheetId = $matches[1] ?? null;

        if (!$spreadsheetId) {
            return back()->with('error', 'Invalid Google Sheet link');
        }

        // Fetch CSV
        $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";
        $csvData = @file_get_contents($csvUrl);

        if ($csvData === false) {
            return back()->with('error', 'Unable to fetch Google Sheet (maybe private?)');
        }

        $rows = array_map('str_getcsv', explode("\n", trim($csvData)));
        $header = array_shift($rows); // first row as column headers

        $rowIndex = 2;
        $user = Auth::user();

        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;
            if (count($row) !== count($header)) continue;

            $rowData = array_combine($header, $row);

            // Map CSV headers to database columns
            $mappedData = [
                'sheet_row_number' => $rowIndex,
                'Date' => isset($rowData['Date']) ? \Carbon\Carbon::createFromFormat('m/d/Y', $rowData['Date'])->format('Y-m-d') : null,
                'Name' => $rowData['Name'] ?? null,
                'Email_Address' => $rowData['Email Address'] ?? null,
                'Phone_Number' => $rowData['Phone Number'] ?? null,
                'Location' => $rowData['Location'] ?? null,
                'Relocation' => $rowData['Relocation'] ?? null,
                'Graduation_Date' => isset($rowData['Graduation Date']) ? \Carbon\Carbon::createFromFormat('m/d/Y', $rowData['Graduation Date'])->format('Y-m-d') : null,
                'Immigration' => $rowData['Immigration'] ?? null,
                'Course' => $rowData['Course'] ?? null,
                'Amount' => isset($rowData['Amount']) ? (float) str_replace(['$', ','], '', $rowData['Amount']) : null,
                'Qualification' => $rowData['Qualification'] ?? null,
                'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
                'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
                'Time_Zone' => $rowData['Time Zone'] ?? null,
                'View' => $rowData['View'] ?? null,
                'created_by' => "{$user->id}|{$user->role}",
            ];

            GoogleSheetData::updateOrCreate(
                ['sheet_row_number' => $rowIndex],
                $mappedData
            );

            $rowIndex++;
        }

        return redirect()->route('google.sheet.accountant')->with('success', 'Data fetched successfully!');
    }

    public function accountantupdate(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? $row->Email_Address;
        $phone = $rowData['Phone Number'] ?? $row->Phone_Number;
        $name  = $rowData['Name'] ?? $row->Name;
        $date  = $rowData['Date'] ?? $row->Date;

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required.'
            ]);
        }

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.'
            ]);
        }

        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('resumes', $newName, 'public');

                if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                    Storage::disk('public')->delete($row->resume);
                }

                $row->resume = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle acceptance file upload
        if ($request->hasFile('acceptance')) {
            $file = $request->file('acceptance');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('acceptance', $newName, 'public');

                if ($row->acceptance && Storage::disk('public')->exists($row->acceptance)) {
                    Storage::disk('public')->delete($row->acceptance);
                }

                $row->acceptance = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle consultation file upload
        if ($request->hasFile('consultation')) {
            $file = $request->file('consultation');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('consultation', $newName, 'public');

                if ($row->consultation && Storage::disk('public')->exists($row->consultation)) {
                    Storage::disk('public')->delete($row->consultation);
                }

                $row->consultation = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle delivery file upload
        if ($request->hasFile('delivery')) {
            $file = $request->file('delivery');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('delivery', $newName, 'public');

                if ($row->delivery && Storage::disk('public')->exists($row->delivery)) {
                    Storage::disk('public')->delete($row->delivery);
                }

                $row->delivery = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle payment file upload
        if ($request->hasFile('payment')) {
            $file = $request->file('payment');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('payment', $newName, 'public');

                if ($row->payment && Storage::disk('public')->exists($row->payment)) {
                    Storage::disk('public')->delete($row->payment);
                }

                $row->payment = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // --- Prepare update data ---
        $updateData = [
            'Date' => !empty($rowData['Date']) ? $this->parseDate($rowData['Date']) : null,
            'Name' => $rowData['Name'] ?? null,
            'Email_Address' => $email,
            'Phone_Number' => $phone,
            'Location' => $rowData['Location'] ?? null,
            'Remark' => $rowData['Remark'] ?? null,
            'Relocation' => $rowData['Relocation'] ?? null,
            'Graduation_Date' => !empty($rowData['Graduation Date']) ? $this->parseDate($rowData['Graduation Date']) : null,
            'Immigration' => $rowData['Immigration'] ?? null,
            'Course' => $rowData['Course'] ?? null,
            'Amount' => isset($rowData['Amount']) && $rowData['Amount'] !== '' ? $this->parseAmount($rowData['Amount']) : 469,
            'Qualification' => $rowData['Qualification'] ?? null,
            'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
            'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
            'Time_Zone' => $rowData['Time Zone'] ?? null,
            'PaymentDate' => !empty($rowData['PaymentDate']) ? $this->parseDate($rowData['PaymentDate']) : null,
            'TranId' => $rowData['TranId'] ?? null,
            'TranRef' => $rowData['TranRef'] ?? null,
            'PaymentMethod' => $rowData['PaymentMethod'] ?? null,
            'PayeeName' => $rowData['PayeeName'] ?? null,
            'updated_at' => now(),
        ];

        if ($request->hasFile('resume')) {
            $updateData['resume'] = $row->resume;
        }

        // created_by logic preserved exactly
        $updateData['created_by'] = $row->created_by;

        if (isset($rowData['Exe Remarks'])) {
            $exeRemark = $rowData['Exe Remarks'];

            if ($exeRemark === 'Document Send') {
                $authUser = Auth::user();

                if (preg_match('/0\|accountant$/', $updateData['created_by'])) {
                    $updateData['created_by'] = preg_replace(
                        '/0\|accountant$/',
                        $authUser->id . '|accountant:0|accountant',
                        $updateData['created_by']
                    );
                }

                if (strpos($updateData['created_by'], ':0|accountant') === false) {
                    $updateData['created_by'] .= ':0|accountant';
                }
            }
        }

        foreach ($updateData as $key => $value) {
            if ($value === '' && !in_array($key, ['Email_Address', 'Name', 'Date', 'Amount'])) {
                $updateData[$key] = null;
            }
        }

        try {
            $row->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Row updated successfully',
                'id' => $row->id,
                'sheet_row_number' => $row->sheet_row_number,
                'resume_path' => !empty($row->resume) ? true : false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),  // Show real error
                'error_line' => $e->getLine(),  // Optional: line number
                'error_file' => $e->getFile(),  // Optional: which file
            ]);
        }
    }

    public function writterupdate(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? $row->Email_Address;
        $phone = $rowData['Phone Number'] ?? $row->Phone_Number;
        $name  = $rowData['Name'] ?? $row->Name;
        $date  = $rowData['Date'] ?? $row->Date;

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required.'
            ]);
        }

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.'
            ]);
        }

        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('resumes', $newName, 'public');

                if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                    Storage::disk('public')->delete($row->resume);
                }

                $row->resume = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle updateresume file upload - Save actual file content
        if ($request->hasFile('updateresume')) {
            $file = $request->file('updateresume');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('updateresume', $newName, 'public');

                if ($row->updateresume && Storage::disk('public')->exists($row->updateresume)) {
                    Storage::disk('public')->delete($row->updateresume);
                }

                $row->updateresume = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // --- Prepare update data ---
        $updateData = [
            'Date' => !empty($rowData['Date']) ? $this->parseDate($rowData['Date']) : null,
            'Name' => $rowData['Name'] ?? null,
            'Email_Address' => $email,
            'Phone_Number' => $phone,
            'Location' => $rowData['Location'] ?? null,
            'Remark' => $rowData['Remark'] ?? null,
            'Relocation' => $rowData['Relocation'] ?? null,
            'Graduation_Date' => !empty($rowData['Graduation Date']) ? $this->parseDate($rowData['Graduation Date']) : null,
            'Immigration' => $rowData['Immigration'] ?? null,
            'Course' => $rowData['Course'] ?? null,
            'Amount' => isset($rowData['Amount']) && $rowData['Amount'] !== '' ? $this->parseAmount($rowData['Amount']) : 469,
            'Qualification' => $rowData['Qualification'] ?? null,
            'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
            'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
            'Time_Zone' => $rowData['Time Zone'] ?? null,
            'PaymentDate' => !empty($rowData['PaymentDate']) ? $this->parseDate($rowData['PaymentDate']) : null,
            'TranId' => $rowData['TranId'] ?? null,
            'TranRef' => $rowData['TranRef'] ?? null,
            'PaymentMethod' => $rowData['PaymentMethod'] ?? null,
            'PayeeName' => $rowData['PayeeName'] ?? null,
            'updated_at' => now(),
        ];

        if ($request->hasFile('resume')) {
            $updateData['resume'] = $row->resume;
        }

        // created_by logic preserved exactly
        $updateData['created_by'] = $row->created_by;

        if (isset($rowData['Exe Remarks'])) {
            $exeRemark = $rowData['Exe Remarks'];

            if ($exeRemark === 'Document Send') {
                $authUser = Auth::user();

                if (preg_match('/0\|accountant$/', $updateData['created_by'])) {
                    $updateData['created_by'] = preg_replace(
                        '/0\|accountant$/',
                        $authUser->id . '|accountant:0|accountant',
                        $updateData['created_by']
                    );
                }

                if (strpos($updateData['created_by'], ':0|accountant') === false) {
                    $updateData['created_by'] .= ':0|accountant';
                }
            }
        }

        foreach ($updateData as $key => $value) {
            if ($value === '' && !in_array($key, ['Email_Address', 'Name', 'Date', 'Amount'])) {
                $updateData[$key] = null;
            }
        }

        try {
            $row->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Row updated successfully',
                'id' => $row->id,
                'sheet_row_number' => $row->sheet_row_number,
                'resume_path' => !empty($row->resume) ? true : false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),  // Show real error
                'error_line' => $e->getLine(),  // Optional: line number
                'error_file' => $e->getFile(),  // Optional: which file
            ]);
        }
    }


    public function accountantupdatecon(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // Extract main details
        $email = $rowData['Email Address'] ?? $row->Email_Address;
        $phone = $rowData['Phone Number'] ?? $row->Phone_Number;
        $name  = $rowData['Name'] ?? $row->Name;
        $date  = $rowData['Date'] ?? $row->Date;

        if (empty($name)) {
            return response()->json(['success' => false, 'message' => 'Name is required.']);
        }

        if (empty($date)) {
            return response()->json(['success' => false, 'message' => 'Date is required.']);
        }


        // File upload
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('resumes', $newName, 'public');

                if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                    Storage::disk('public')->delete($row->resume);
                }

                $row->resume = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Prepare update data
        $updateData = [
            'Date' => !empty($rowData['Date']) ? $this->parseDate($rowData['Date']) : null,
            'Name' => $rowData['Name'] ?? null,
            'Email_Address' => $email,
            'Phone_Number' => $phone,
            'Location' => $rowData['Location'] ?? null,
            'Remark' => $rowData['Remark'] ?? null,
            'Relocation' => $rowData['Relocation'] ?? null,
            'Graduation_Date' => !empty($rowData['Graduation Date']) ? $this->parseDate($rowData['Graduation Date']) : null,
            'Immigration' => $rowData['Immigration'] ?? null,
            'Course' => $rowData['Course'] ?? null,
            'Amount' => isset($rowData['Amount']) && $rowData['Amount'] !== '' ? $this->parseAmount($rowData['Amount']) : 469,
            'Qualification' => $rowData['Qualification'] ?? null,
            'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
            'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
            'Time_Zone' => $rowData['Time Zone'] ?? null,
            'updated_at' => now()
        ];

        if ($request->hasFile('resume')) {
            $updateData['resume'] = $row->resume;
        }

        // Keep created_by
        $updateData['created_by'] = $row->created_by;

        // created_by logic
        if (isset($rowData['Exe Remarks'])) {
            $exeRemark = $rowData['Exe Remarks'];

            if ($exeRemark === 'Payment Completed') {
                $authUser = Auth::user();

                if (preg_match('/0\|accountant$/', $updateData['created_by'])) {
                    $updateData['created_by'] = preg_replace(
                        '/0\|accountant$/',
                        $authUser->id . '|accountant:0|senior',
                        $updateData['created_by']
                    );
                }

                if (strpos($updateData['created_by'], ':0|senior') === false) {
                    $updateData['created_by'] .= ':0|senior';
                }
            } elseif ($exeRemark === 'Ready To Pay') {

                $tag = $id . '|accountant';
                $zerotag = '0|accountant';

                $parts = explode(':', $updateData['created_by']);
                $lastPart = end($parts);

                if ($lastPart === $tag) {
                    $updateData['created_by'] .= ':' . $zerotag;
                }
            }
        }

        foreach ($updateData as $key => $value) {
            if ($value === '' && !in_array($key, ['Email_Address', 'Name', 'Date', 'Amount'])) {
                $updateData[$key] = null;
            }
        }

        try {
            $row->update($updateData);

            $name   = $rowData['Name'] ?? $row->Name ?? '';
            $phone  = $rowData['Phone Number'] ?? $row->Phone_Number ?? '';
            $date   = $rowData['Date'] ?? $row->Date ?? '';
            $amount = isset($rowData['Amount']) ? $this->parseAmount($rowData['Amount']) : ($row->Amount ?? 0);
            $email  = $rowData['Email Address']
                ?? $rowData['Email_Address']
                ?? $row->Email_Address
                ?? '';

            // EMAIL-SENDING SECTION REMOVED

            $firstCallerName = $this->getFirstCallerName($row->created_by);

            $dataText =
                "Payment Processed Successfully – Please Review the Details\n" .
                "Candidate Name: {$name}\n" .
                "Candidate Email: {$email}\n" .
                "Candidate Phone: {$phone}\n" .
                "Date: {$date}\n" .
                "Paid Amount: \${$amount}\n" .
                "First Caller Name: {$firstCallerName}";

            $createdBy = $row->created_by ?? '';
            $parts = explode('|', $createdBy);

            $ids = [];

            if (is_numeric($parts[0])) {
                $ids[] = (int) $parts[0];
            }

            foreach ($parts as $index => $part) {
                if ($index === 0) continue;

                if (str_contains($part, ':')) {
                    [$role, $id] = explode(':', $part);
                    if (is_numeric($id)) {
                        $ids[] = (int) $id;
                    }
                }
            }

            $ids = array_unique(array_filter($ids, fn($x) => $x !== null));

            $ids[] = 1;
            $ids[] = 54;

            $ids = array_unique($ids);

            foreach ($ids as $notifyId) {
                Notification::create([
                    'type' => 'Payment',
                    'candidate_id' => $row->id,
                    'notifiable_role' => 'admin',
                    'notifiable_id' => $notifyId,
                    'data' => $dataText
                ]);
            }

            // Generate latest notification HTML
            $admin = User::find(1);
            $latestNotification = Notification::with(['user', 'candidate'])
                ->where('notifiable_id', 1)
                ->where('notifiable_role', 'admin')
                ->latest()
                ->first();

            $newNotificationHtml = "";

            if ($latestNotification) {
                $msg = $latestNotification->data ?? '';
                $userName = $latestNotification->user->name ?? 'Unknown User';
                $userEmail = $latestNotification->user->email ?? '';

                $candidate = $latestNotification->candidate;
                $candidateName = $candidate->Name ?? null;
                $candidateEmail = $candidate->Email_Address ?? null;
                $candidatePhone = $candidate->Phone_Number ?? null;
                $candidateCourse = $candidate->Course ?? null;

                $newNotificationHtml = view('notice.partials.single-notification', compact(
                    'msg',
                    'userName',
                    'userEmail',
                    'candidateName',
                    'candidateEmail',
                    'candidatePhone',
                    'candidateCourse'
                ))->render();
            }

            return response()->json([
                'success' => true,
                'message' => 'Row updated successfully',
                'id' => $row->id,
                'sheet_row_number' => $row->sheet_row_number,
                'resume_path' => !empty($row->resume) ? true : false,
                'refresh_notification' => true,
                'html' => $newNotificationHtml
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'error_trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function accountantupdatever(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? $row->Email_Address;
        $phone = $rowData['Phone Number'] ?? $row->Phone_Number;
        $name  = $rowData['Name'] ?? $row->Name;
        $date  = $rowData['Date'] ?? $row->Date;

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required.'
            ]);
        }

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.'
            ]);
        }

        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('resumes', $newName, 'public');

                if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                    Storage::disk('public')->delete($row->resume);
                }

                $row->resume = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle acceptance file upload
        if ($request->hasFile('acceptance')) {
            $file = $request->file('acceptance');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('acceptance', $newName, 'public');

                if ($row->acceptance && Storage::disk('public')->exists($row->acceptance)) {
                    Storage::disk('public')->delete($row->acceptance);
                }

                $row->acceptance = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle consultation sign file upload
        if ($request->hasFile('consultationsign')) {
            $file = $request->file('consultationsign');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('consultationsign', $newName, 'public');

                if ($row->consultationsign && Storage::disk('public')->exists($row->consultationsign)) {
                    Storage::disk('public')->delete($row->consultationsign);
                }

                $row->consultationsign = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle acceptance sign file upload
        if ($request->hasFile('acceptancesign')) {
            $file = $request->file('acceptancesign');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('acceptancesign', $newName, 'public');

                if ($row->acceptancesign && Storage::disk('public')->exists($row->acceptancesign)) {
                    Storage::disk('public')->delete($row->acceptancesign);
                }

                $row->acceptancesign = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle consultation file upload
        if ($request->hasFile('consultation')) {
            $file = $request->file('consultation');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('consultation', $newName, 'public');

                if ($row->consultation && Storage::disk('public')->exists($row->consultation)) {
                    Storage::disk('public')->delete($row->consultation);
                }

                $row->consultation = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle delivery file upload
        if ($request->hasFile('delivery')) {
            $file = $request->file('delivery');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('delivery', $newName, 'public');

                if ($row->delivery && Storage::disk('public')->exists($row->delivery)) {
                    Storage::disk('public')->delete($row->delivery);
                }

                $row->delivery = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle payment file upload
        if ($request->hasFile('payment')) {
            $file = $request->file('payment');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('payment', $newName, 'public');

                if ($row->payment && Storage::disk('public')->exists($row->payment)) {
                    Storage::disk('public')->delete($row->payment);
                }

                $row->payment = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle deliverysign file upload
        if ($request->hasFile('deliverysign')) {
            $file = $request->file('deliverysign');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('deliverysign', $newName, 'public');

                if ($row->deliverysign && Storage::disk('public')->exists($row->deliverysign)) {
                    Storage::disk('public')->delete($row->deliverysign);
                }

                $row->deliverysign = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle payment file upload
        if ($request->hasFile('paymentsign')) {
            $file = $request->file('paymentsign');

            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                $filePath = $file->storeAs('paymentsign', $newName, 'public');

                if ($row->paymentsign && Storage::disk('public')->exists($row->paymentsign)) {
                    Storage::disk('public')->delete($row->paymentsign);
                }

                $row->paymentsign = $filePath;
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // --- Prepare update data ---
        $updateData = [
            'Date' => !empty($rowData['Date']) ? $this->parseDate($rowData['Date']) : null,
            'Name' => $rowData['Name'] ?? null,
            'Email_Address' => $email,
            'Phone_Number' => $phone,
            'Location' => $rowData['Location'] ?? null,
            'Remark' => $rowData['Remark'] ?? null,
            'Relocation' => $rowData['Relocation'] ?? null,
            'Graduation_Date' => !empty($rowData['Graduation Date']) ? $this->parseDate($rowData['Graduation Date']) : null,
            'Immigration' => $rowData['Immigration'] ?? null,
            'Course' => $rowData['Course'] ?? null,
            'Amount' => isset($rowData['Amount']) && $rowData['Amount'] !== '' ? $this->parseAmount($rowData['Amount']) : 469,
            'Qualification' => $rowData['Qualification'] ?? null,
            'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
            'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
            'Time_Zone' => $rowData['Time Zone'] ?? null,
            'PaymentDate' => !empty($rowData['PaymentDate']) ? $this->parseDate($rowData['PaymentDate']) : null,
            'TranId' => $rowData['TranId'] ?? null,
            'TranRef' => $rowData['TranRef'] ?? null,
            'PaymentMethod' => $rowData['PaymentMethod'] ?? null,
            'PayeeName' => $rowData['PayeeName'] ?? null,
            'updated_at' => now(),
        ];

        if ($request->hasFile('resume')) {
            $updateData['resume'] = $row->resume;
        }

        // created_by logic preserved exactly
        $updateData['created_by'] = $row->created_by;

        if (isset($rowData['Exe Remarks'])) {
            $exeRemark = $rowData['Exe Remarks'];

            if ($exeRemark === 'Document Verified') {
                $authUser = Auth::user();

                if (preg_match('/0\|accountant$/', $updateData['created_by'])) {
                    $updateData['created_by'] = preg_replace(
                        '/0\|accountant$/',
                        $authUser->id . '|accountant:0|trainer',
                        $updateData['created_by']
                    );
                }

                if (strpos($updateData['created_by'], ':0|accountant') === false) {
                    $updateData['created_by'] .= ':0|accountant';
                }
            }
        }

        foreach ($updateData as $key => $value) {
            if ($value === '' && !in_array($key, ['Email_Address', 'Name', 'Date', 'Amount'])) {
                $updateData[$key] = null;
            }
        }

        try {
            $row->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Row updated successfully',
                'id' => $row->id,
                'sheet_row_number' => $row->sheet_row_number,
                'resume_path' => !empty($row->resume) ? true : false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),  // Show real error
                'error_line' => $e->getLine(),  // Optional: line number
                'error_file' => $e->getFile(),  // Optional: which file
            ]);
        }
    }


    private function getFirstCallerName($createdBy)
    {
        if (empty($createdBy)) {
            return 'N/A';
        }

        // Example format: "37|junior:5|senior"
        // Extract first segment until the "|"
        $parts = explode(':', $createdBy);
        $firstSegment = $parts[0];        // "37|junior"
        $subParts = explode('|', $firstSegment);

        if (count($subParts) < 2) {
            return 'N/A';
        }

        $juniorId = intval($subParts[0]); // 37

        // Fetch user
        $user = \App\Models\User::find($juniorId);

        return $user->name ?? 'N/A';
    }



    public function accountantstore(Request $request)
    {
        $rowData = json_decode($request->input('data'), true);

        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? null;
        $phone = $rowData['Phone Number'] ?? null;
        $name  = $rowData['Name'] ?? null;
        $date  = $rowData['Date'] ?? null;

        // --- Check required fields ---
        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required.'
            ]);
        }

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.'
            ]);
        }

        // Check for duplicate Email
        if (!empty($email)) {
            $emailExists = GoogleSheetData::where('Email_Address', $email)->exists();

            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address already exists in records.'
                ]);
            }
        }

        // Check for duplicate Phone
        if (!empty($phone)) {
            $phoneExists = GoogleSheetData::where('Phone_Number', $phone)->exists();

            if ($phoneExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number already exists in records.'
                ]);
            }
        }

        $user = Auth::user();
        $maxRow = GoogleSheetData::max('sheet_row_number') ?? 0;
        $nextRow = $maxRow + 1;

        $record = new GoogleSheetData();
        $record->sheet_row_number = $nextRow;
        $record->created_by = $user->id . '|accountant';

        // Map frontend keys to DB columns
        $columnMap = [
            'Date' => 'Date',
            'Name' => 'Name',
            'Email Address' => 'Email_Address',
            'Phone Number' => 'Phone_Number',
            'Location' => 'Location',
            'Remark' => 'Remark',
            'Relocation' => 'Relocation',
            'Graduation Date' => 'Graduation_Date',
            'Immigration' => 'Immigration',
            'Course' => 'Course',
            'Amount' => 'Amount',
            'Qualification' => 'Qualification',
            'Exe Remarks' => 'Exe_Remarks',
            '1st Follow Up Remarks' => 'First_Follow_Up_Remarks',
            'Time Zone' => 'Time_Zone',
        ];

        $exeRemarksValue = null;
        $name = null;
        $amount = null;

        // Assign values safely, save null for empty non-number/email fields
        foreach ($columnMap as $frontendKey => $dbColumn) {
            $val = $rowData[$frontendKey] ?? null;

            if (in_array($dbColumn, ['Date', 'Graduation_Date']) && !empty($val)) {
                $val = $this->parseDate($val);
            }

            if ($dbColumn === 'Amount' && !empty($val)) {
                $val = $this->parseAmount($val);
                $amount = $val;
            }

            if ($dbColumn === 'Name') {
                $name = $val;
            }

            if ($dbColumn === 'Exe_Remarks') {
                $exeRemarksValue = $val;
            }

            // Save null for empty fields, including Amount
            if (empty($val) && !in_array($dbColumn, ['Email_Address', 'Phone_Number'])) {
                $val = null;
            }

            $record->$dbColumn = $val;
        }

        // Set created_by conditionally based on Exe_Remarks
        if ($exeRemarksValue === 'Ready To Pay') {
            $record->created_by = $user->id . '|accountant:0|accountant';
        } elseif ($exeRemarksValue === 'Payment Completed') {
            $record->created_by = $user->id . '|accountant:0|trainer';
        } else {
            $record->created_by = $user->id . '|accountant';
        }

        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            // Validate it's a PDF
            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                // Store the actual file content
                $filePath = $file->storeAs('resumes', $newName, 'public');
                $record->resume = $filePath; // Store file path
            } catch (\Exception $e) {
                // Continue without resume if upload fails
                $record->resume = null;
            }
        }

        try {
            $record->save();
            $saveMessage = 'Record saved successfully.';
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fill Full Detail to Save.'
            ]);
        }

        // --- Email logic ---
        $mailMessage = 'No email sent.';
        // --- Send Email if Exe_Remarks is "Ready To Pay" ---
        if ($exeRemarksValue === 'Ready To Pay' && !empty($email)) {
            try {
                $smtp = SmtpSetting::where('user_id', $user->id)->first();
                if (!$smtp) {
                    return response()->json([
                        'message' => 'No SMTP settings found.'
                    ]);
                } else {
                    // Configure mailer dynamically (same as test() method)
                    config([
                        'mail.mailers.smtp.transport' => $smtp->mailer,
                        'mail.mailers.smtp.host' => $smtp->host,
                        'mail.mailers.smtp.port' => $smtp->port,
                        'mail.mailers.smtp.username' => $smtp->username,
                        'mail.mailers.smtp.password' => decrypt($smtp->password),
                        'mail.mailers.smtp.encryption' => $smtp->encryption,
                        'mail.from.address' => $smtp->from_address,
                        'mail.from.name' => $smtp->from_name,
                    ]);

                    // --- Fetch Email Template from Database ---
                    $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                    if ($template) {
                        $subject = $template->subject;
                        $messageBody = $template->body;
                    } else {
                        // Fallback if template not found
                        $subject = "Unlock Career Stability with Fortune 500 Projects !";
                        $messageBody =
                            "Hi {$name},\n\n" .
                            "I hope this message finds you well.\n\n" .
                            "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                            "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                            "What Makes Synergie Different?\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                            "Here’s what you can expect when you join our community:\n\n" .
                            "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                            "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                            "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                            "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                            "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                            "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                            "More Than a Paycheck — A Path to Prosperity\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                            "                  - Full-Time Roles: \$40–\$50/hr\n" .
                            "                  - Part-Time Roles: \$15–\$25/hr\n" .
                            "                  - Paid Internships available\n" .
                            "                  - 15% Salary Raise every 6 months based on performance\n" .
                            "                  - 12 Days Paid Vacation annually\n" .
                            "                  - Relocation Assistance for client deployments\n\n" .
                            "Comprehensive Benefits That Put You First\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                            "                  - Health, Dental & Vision Insurance\n" .
                            "                  - Short- & Long-Term Disability Insurance\n" .
                            "                  - Life Insurance & 401(k) Retirement Plan\n" .
                            "                  - Legal & Immigration Support\n" .
                            "                  - Tax Assistance & Transparent Payroll\n" .
                            "                  - Workers’ Compensation—your safety is our priority\n\n" .
                            "Support Tailored for International Talent\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                            "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                            "                  - Full STEM Extension & OPT/CPT Support\n" .
                            "                  - H1B Sponsorship after project onboarding\n" .
                            "                  - Relocation & Immigration Documentation\n" .
                            "                  - Ongoing Green Card Processing Assistance\n\n" .
                            "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                            "What You’ll Gain:\n\n" .
                            "                  - Live Zoom sessions & recorded expert sessions\n" .
                            "                  - Real-time project simulations & hands-on assignments\n" .
                            "                  - One-on-one resume branding & mock interviews\n" .
                            "                  - Global Certificate of Completion & recruiter access\n" .
                            "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                            "Let’s Take the First Step Together\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                            "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                            "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                            "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                            "Visit Our Website: https://www.synergiesystems.com/";
                    }

                    // --- Send Email (No Template Logic Changed) ---
                    Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                        $message->from($smtp->from_address, $smtp->from_name)
                            ->to($email)
                            ->subject($subject);
                    });

                    $mailMessage = "Email sent successfully to {$email}!";
                }
            } catch (\Exception $e) {
                $mailMessage = 'Failed to send email: ' . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'id' => $record->id,
            'sheet_row_number' => $record->sheet_row_number,
            'resume_path' => !empty($record->resume) ? true : false,
            'mail_message' => $mailMessage,
            'save_message' => $saveMessage,
        ]);
    }

    // Add a method to serve the PDF files
    public function viewaccountantResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }

    // Add a method to download the PDF files
    public function downloadaccountantResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }


    public function trainer(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');
        $juniorUserId = $request->input('junior_user'); // dropdown value

        $userPattern = "%:" . $authUser->id . "|trainer";
        $zeroPattern = "%:0|trainer";

        $query = GoogleSheetData::where(function ($q) use ($authUser, $userPattern, $zeroPattern) {
            $q->where('created_by', $authUser->id . '|trainer')
                ->orWhere('created_by', '0|trainer')
                ->orWhere('created_by', 'LIKE', $userPattern)
                ->orWhere('created_by', 'LIKE', $zeroPattern);
        });

        // Filter by selected junior
        if ($juniorUserId) {
            $query->where('created_by', 'LIKE', '%' . $juniorUserId . '|junior%');
        }

        // Search or specific row filter
        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        // Pagination with appended filters for AJAX navigation
        $data = $query->orderBy('Date', 'desc')->paginate(10);
        $data->appends([
            'search' => $search,
            'row_id' => $rowId,
            'junior_user' => $juniorUserId,
        ]);

        // Map forwarded_by dynamically
        $data->getCollection()->transform(function ($item) use ($authUser) {
            $forwardedBy = '';

            if (!empty($item->created_by)) {
                $entries = explode(':', $item->created_by);
                $names = [];

                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });

        $juniorUsers = \App\Models\User::where('is_deleted', 0)->where('role', 'junior')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'phone', 'gender']);


        // Handle AJAX request for both search and pagination
        if ($request->ajax()) {
            return view('database.partials.senior_table', compact('data'))->render();
        }

        return view('database.trainer', compact('data', 'juniorUsers'));
    }

    public function trainercompleted(Request $request)
    {
        $authUser = Auth::user();
        $search = $request->input('search');
        $rowId = $request->input('row_id');

        $query = GoogleSheetData::where(function ($q) {
            // Removed user_id|senior check
            // Only keep the completed part filter
            $q->where(function ($q2) {
                $q2->whereRaw("created_by = '0|completed'")
                    ->orWhereRaw("created_by LIKE '0|completed:%'")
                    ->orWhereRaw("created_by LIKE '%:0|completed'")
                    ->orWhereRaw("created_by LIKE '%:0|completed:%'");
            });
        });

        if ($rowId) {
            $query->where('id', $rowId);
        } elseif ($search && strlen($search) >= 3) {
            $query->where(function ($q) use ($search) {
                $q->where('Name', 'LIKE', "%{$search}%")
                    ->orWhere('Email_Address', 'LIKE', "%{$search}%")
                    ->orWhere('Phone_Number', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->orderBy('Date', 'desc')->paginate(10);

        $data->getCollection()->transform(function ($item) use ($authUser) {

            $forwardedBy = '';

            if (!empty($item->created_by)) {
                // Split by ':' to handle multiple forwarded entries
                $entries = explode(':', $item->created_by);

                $names = [];
                foreach ($entries as $entry) {
                    $parts = explode('|', $entry);
                    $userId = $parts[0] ?? null;
                    $role   = $parts[1] ?? 'unknown';

                    if ($userId == $authUser->id) {
                        $names[] = "SELF ({$userId}) ({$role})";
                    } elseif ($userId == 0) {
                        $names[] = "SYSTEM (0) ({$role})";
                    } else {
                        $user = \App\Models\User::where('is_deleted', 0)->find($userId);
                        $name = $user ? $user->name : 'Unknown';
                        $names[] = "{$name} ({$userId}) ({$role})";
                    }
                }

                // Join all names for forwarded chain
                $forwardedBy = implode(' → ', $names);
            } else {
                $forwardedBy = 'N/A';
            }

            $item->forwarded_by = $forwardedBy;
            return $item;
        });


        if (request()->ajax()) {
            return view('database.partials.senior_table', compact('data'))->render();
        }

        return view('database.trainercomplete', compact('data'));
    }




    public function trainerfetch(Request $request)
    {
        $request->validate([
            'sheet_link' => 'required|url'
        ]);

        // Extract spreadsheet ID
        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $request->sheet_link, $matches);
        $spreadsheetId = $matches[1] ?? null;

        if (!$spreadsheetId) {
            return back()->with('error', 'Invalid Google Sheet link');
        }

        // Fetch CSV
        $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";
        $csvData = @file_get_contents($csvUrl);

        if ($csvData === false) {
            return back()->with('error', 'Unable to fetch Google Sheet (maybe private?)');
        }

        $rows = array_map('str_getcsv', explode("\n", trim($csvData)));
        $header = array_shift($rows); // first row as column headers

        $rowIndex = 2;
        $user = Auth::user();

        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;
            if (count($row) !== count($header)) continue;

            $rowData = array_combine($header, $row);

            // Map CSV headers to database columns
            $mappedData = [
                'sheet_row_number' => $rowIndex,
                'Date' => isset($rowData['Date']) ? \Carbon\Carbon::createFromFormat('m/d/Y', $rowData['Date'])->format('Y-m-d') : null,
                'Name' => $rowData['Name'] ?? null,
                'Email_Address' => $rowData['Email Address'] ?? null,
                'Phone_Number' => $rowData['Phone Number'] ?? null,
                'Location' => $rowData['Location'] ?? null,
                'Relocation' => $rowData['Relocation'] ?? null,
                'Graduation_Date' => isset($rowData['Graduation Date']) ? \Carbon\Carbon::createFromFormat('m/d/Y', $rowData['Graduation Date'])->format('Y-m-d') : null,
                'Immigration' => $rowData['Immigration'] ?? null,
                'Course' => $rowData['Course'] ?? null,
                'Amount' => isset($rowData['Amount']) ? (float) str_replace(['$', ','], '', $rowData['Amount']) : null,
                'Qualification' => $rowData['Qualification'] ?? null,
                'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
                'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
                'Time_Zone' => $rowData['Time Zone'] ?? null,
                'View' => $rowData['View'] ?? null,
                'created_by' => "{$user->id}|{$user->role}",
            ];

            GoogleSheetData::updateOrCreate(
                ['sheet_row_number' => $rowIndex],
                $mappedData
            );

            $rowIndex++;
        }

        return redirect()->route('google.sheet.trainer')->with('success', 'Data fetched successfully!');
    }

    public function trainerupdate(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required']);
        }

        $row = GoogleSheetData::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Row not found']);
        }

        $rowData = json_decode($request->input('data'), true);
        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? $row->Email_Address;
        $phone = $rowData['Phone Number'] ?? $row->Phone_Number;
        $name  = $rowData['Name'] ?? $row->Name;
        $date  = $rowData['Date'] ?? $row->Date;

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required.'
            ]);
        }

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.'
            ]);
        }
        // Check for duplicate Email (ignore current record)
        if (!empty($email)) {
            $emailExists = GoogleSheetData::where('Email_Address', $email)
                ->where('id', '!=', $id)
                ->exists();

            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address already exists in records.'
                ]);
            }
        }

        // Check for duplicate Phone (ignore current record)
        if (!empty($phone)) {
            $phoneExists = GoogleSheetData::where('Phone_Number', $phone)
                ->where('id', '!=', $id)
                ->exists();

            if ($phoneExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number already exists in records.'
                ]);
            }
        }

        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            // Validate it's a PDF
            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            // Generate unique filename
            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                // Store the actual file content
                $filePath = $file->storeAs('resumes', $newName, 'public');

                // Delete old resume file if exists
                if ($row->resume && Storage::disk('public')->exists($row->resume)) {
                    Storage::disk('public')->delete($row->resume);
                }

                $row->resume = $filePath; // Store file path instead of just filename

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'File upload failed: ' . $e->getMessage()]);
            }
        }

        // --- Prepare update data with null defaults for empty fields ---
        $updateData = [
            'Date' => !empty($rowData['Date']) ? $this->parseDate($rowData['Date']) : null,
            'Name' => $rowData['Name'] ?? null,
            'Email_Address' => $email, // keep original email
            'Phone_Number' => $phone,  // keep original phone
            'Location' => $rowData['Location'] ?? null,
            'Remark' => $rowData['Remark'] ?? null,
            'Relocation' => $rowData['Relocation'] ?? null,
            'Graduation_Date' => !empty($rowData['Graduation Date']) ? $this->parseDate($rowData['Graduation Date']) : null,
            'Immigration' => $rowData['Immigration'] ?? null,
            'Course' => $rowData['Course'] ?? null,
            'Amount' => isset($rowData['Amount']) && $rowData['Amount'] !== '' ? $this->parseAmount($rowData['Amount']) : 469, // ✅ default 469
            'Qualification' => $rowData['Qualification'] ?? null,
            'Exe_Remarks' => $rowData['Exe Remarks'] ?? null,
            'First_Follow_Up_Remarks' => $rowData['1st Follow Up Remarks'] ?? null,
            'Time_Zone' => $rowData['Time Zone'] ?? null,
            'updated_at' => now(),
        ];

        // Only update resume if it was uploaded
        if ($request->hasFile('resume')) {
            $updateData['resume'] = $row->resume;
        }

        // Start with existing created_by value
        $updateData['created_by'] = $row->created_by;

        if (isset($rowData['Exe Remarks'])) {
            $exeRemark = $rowData['Exe Remarks'];

            if ($exeRemark === 'Training Completed') {
                $authUser = Auth::user();

                // Replace "0|trainer" with "auth_id|trainer:0|completed"
                if (preg_match('/0\|trainer$/', $updateData['created_by'])) {
                    $updateData['created_by'] = preg_replace(
                        '/0\|trainer$/',
                        $authUser->id . '|trainer:0|completed',
                        $updateData['created_by']
                    );
                }

                // Ensure ":0|completed" exists at the end if missing
                if (strpos($updateData['created_by'], ':0|completed') === false) {
                    $updateData['created_by'] .= ':0|completed';
                }
            } elseif ($exeRemark === 'Payment Completed') {
                $tag = $id . '|trainer';
                $zerotag = '0|trainer';

                // Get the last segment after the last colon
                $parts = explode(':', $updateData['created_by']);
                $lastPart = end($parts);

                // Append only if the last part exactly matches the tag
                if ($lastPart === $tag) {
                    $updateData['created_by'] .= ':' . $zerotag;
                }
            } else {
                // For all other remarks, apply "Revert To accountant" logic
                // Match any integer followed by "|accountant"
                if (preg_match('/(\d+)\|accountant/', $updateData['created_by'], $matches)) {
                    $accountantId = $matches[1]; // Extract the integer
                    $tag = $accountantId . '|accountant';
                    // Append only if tag already exists in created_by
                    if (strpos($updateData['created_by'], $tag) !== false) {
                        $updateData['created_by'] .= ':' . $tag;
                    }
                }

                // Replace "0|trainer" with actual trainer ID (only if it ends with 0|trainer)
                if (preg_match('/0\|trainer$/', $updateData['created_by'])) {
                    $updateData['created_by'] = preg_replace(
                        '/0\|trainer$/',
                        $id . '|trainer',
                        $updateData['created_by']
                    );
                }
            }
        }

        foreach ($updateData as $key => $value) {
            if ($value === '' && !in_array($key, ['Email_Address', 'Name', 'Date', 'Amount'])) {
                $updateData[$key] = null;
            }
        }

        try {
            $row->update($updateData);
            $user = Auth::user();
            $mailMessage = 'No email sent.';
            $name = $rowData['Name'] ?? null;
            $amount = isset($rowData['Amount']) ? $this->parseAmount($rowData['Amount']) : $row->Amount;

            // --- Send email if Exe_Remarks is "Training Completed" ---
            if (isset($rowData['Exe Remarks']) && $rowData['Exe Remarks'] === 'Training Completed' && !empty($email)) {
                try {
                    $smtp = SmtpSetting::where('user_id', $user->id)->first();
                    if (!$smtp) {
                        return response()->json([
                            'message' => 'No SMTP settings found.'
                        ]);
                    } else {
                        // Configure mailer dynamically (same as test() method)
                        config([
                            'mail.mailers.smtp.transport' => $smtp->mailer,
                            'mail.mailers.smtp.host' => $smtp->host,
                            'mail.mailers.smtp.port' => $smtp->port,
                            'mail.mailers.smtp.username' => $smtp->username,
                            'mail.mailers.smtp.password' => decrypt($smtp->password),
                            'mail.mailers.smtp.encryption' => $smtp->encryption,
                            'mail.from.address' => $smtp->from_address,
                            'mail.from.name' => $smtp->from_name,
                        ]);

                        // --- Fetch Email Template from Database ---
                        $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                        if ($template) {
                            $subject = $template->subject;
                            $messageBody = $template->body;
                        } else {
                            // Fallback if template not found
                            $subject = "Unlock Career Stability with Fortune 500 Projects !";
                            $messageBody =
                                "Hi {$name},\n\n" .
                                "I hope this message finds you well.\n\n" .
                                "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                                "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                                "What Makes Synergie Different?\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                                "Here’s what you can expect when you join our community:\n\n" .
                                "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                                "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                                "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                                "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                                "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                                "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                                "More Than a Paycheck — A Path to Prosperity\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                                "                  - Full-Time Roles: \$40–\$50/hr\n" .
                                "                  - Part-Time Roles: \$15–\$25/hr\n" .
                                "                  - Paid Internships available\n" .
                                "                  - 15% Salary Raise every 6 months based on performance\n" .
                                "                  - 12 Days Paid Vacation annually\n" .
                                "                  - Relocation Assistance for client deployments\n\n" .
                                "Comprehensive Benefits That Put You First\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                                "                  - Health, Dental & Vision Insurance\n" .
                                "                  - Short- & Long-Term Disability Insurance\n" .
                                "                  - Life Insurance & 401(k) Retirement Plan\n" .
                                "                  - Legal & Immigration Support\n" .
                                "                  - Tax Assistance & Transparent Payroll\n" .
                                "                  - Workers’ Compensation—your safety is our priority\n\n" .
                                "Support Tailored for International Talent\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                                "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                                "                  - Full STEM Extension & OPT/CPT Support\n" .
                                "                  - H1B Sponsorship after project onboarding\n" .
                                "                  - Relocation & Immigration Documentation\n" .
                                "                  - Ongoing Green Card Processing Assistance\n\n" .
                                "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                                "What You’ll Gain:\n\n" .
                                "                  - Live Zoom sessions & recorded expert sessions\n" .
                                "                  - Real-time project simulations & hands-on assignments\n" .
                                "                  - One-on-one resume branding & mock interviews\n" .
                                "                  - Global Certificate of Completion & recruiter access\n" .
                                "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                                "Let’s Take the First Step Together\n" .
                                "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                                "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                                "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                                "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                                "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                                "Visit Our Website: https://www.synergiesystems.com/";
                        }

                        // --- Send Email (No Template Logic Changed) ---
                        Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                            $message->from($smtp->from_address, $smtp->from_name)
                                ->to($email)
                                ->subject($subject);
                        });

                        $mailMessage = "Email sent successfully to {$email}!";
                    }
                } catch (\Exception $e) {
                    $mailMessage = 'Failed to send email: ' . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Row updated successfully',
                'id' => $row->id,
                'sheet_row_number' => $row->sheet_row_number,
                'resume_path' => !empty($row->resume) ? true : false,
                'mail_message' => $mailMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fill Full Detail to Save.'
            ]);
        }
    }

    public function trainerstore(Request $request)
    {
        $rowData = json_decode($request->input('data'), true);

        if (empty($rowData)) {
            return response()->json(['success' => false, 'message' => 'No data provided']);
        }

        // --- Extract Email & Phone for uniqueness check ---
        $email = $rowData['Email Address'] ?? null;
        $phone = $rowData['Phone Number'] ?? null;
        $name  = $rowData['Name'] ?? null;
        $date  = $rowData['Date'] ?? null;

        // --- Check required fields ---
        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Name is required.'
            ]);
        }

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.'
            ]);
        }

        // Check for duplicate Email
        if (!empty($email)) {
            $emailExists = GoogleSheetData::where('Email_Address', $email)->exists();

            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address already exists in records.'
                ]);
            }
        }

        // Check for duplicate Phone
        if (!empty($phone)) {
            $phoneExists = GoogleSheetData::where('Phone_Number', $phone)->exists();

            if ($phoneExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number already exists in records.'
                ]);
            }
        }

        $user = Auth::user();
        $maxRow = GoogleSheetData::max('sheet_row_number') ?? 0;
        $nextRow = $maxRow + 1;

        $record = new GoogleSheetData();
        $record->sheet_row_number = $nextRow;
        $record->created_by = $user->id . '|trainer';

        // Map frontend keys to DB columns
        $columnMap = [
            'Date' => 'Date',
            'Name' => 'Name',
            'Email Address' => 'Email_Address',
            'Phone Number' => 'Phone_Number',
            'Location' => 'Location',
            'Remark' => 'Remark',
            'Relocation' => 'Relocation',
            'Graduation Date' => 'Graduation_Date',
            'Immigration' => 'Immigration',
            'Course' => 'Course',
            'Amount' => 'Amount',
            'Qualification' => 'Qualification',
            'Exe Remarks' => 'Exe_Remarks',
            '1st Follow Up Remarks' => 'First_Follow_Up_Remarks',
            'Time Zone' => 'Time_Zone',
        ];

        $exeRemarksValue = null;
        $name = null;
        $amount = null;

        // Assign values safely, save null for empty non-number/email fields
        foreach ($columnMap as $frontendKey => $dbColumn) {
            $val = $rowData[$frontendKey] ?? null;

            if (in_array($dbColumn, ['Date', 'Graduation_Date']) && !empty($val)) {
                $val = $this->parseDate($val);
            }

            if ($dbColumn === 'Amount' && !empty($val)) {
                $val = $this->parseAmount($val);
                $amount = $val;
            }

            if ($dbColumn === 'Name') {
                $name = $val;
            }

            if ($dbColumn === 'Exe_Remarks') {
                $exeRemarksValue = $val;
            }

            // Save null for empty fields, including Amount
            if (empty($val) && !in_array($dbColumn, ['Email_Address', 'Phone_Number'])) {
                $val = null;
            }

            $record->$dbColumn = $val;
        }

        // Set created_by conditionally based on Exe_Remarks
        if ($exeRemarksValue === 'Ready To Pay') {
            $record->created_by = $user->id . '|trainer:0|trainer';
        } elseif ($exeRemarksValue === 'Payment Completed') {
            $record->created_by = $user->id . '|trainer:0|completed';
        } else {
            $record->created_by = $user->id . '|trainer';
        }

        // Handle resume file upload - Save actual file content
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');

            // Validate it's a PDF
            if ($file->getMimeType() !== 'application/pdf') {
                return response()->json(['success' => false, 'message' => 'Only PDF files are allowed']);
            }

            $timestamp = now()->format('Ymd_His');
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newName = Str::slug($filename) . "_{$timestamp}.{$extension}";

            try {
                // Store the actual file content
                $filePath = $file->storeAs('resumes', $newName, 'public');
                $record->resume = $filePath; // Store file path
            } catch (\Exception $e) {
                // Continue without resume if upload fails
                $record->resume = null;
            }
        }

        try {
            $record->save();
            $saveMessage = 'Record saved successfully.';
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fill Full Detail to Save.'
            ]);
        }

        // --- Email logic ---
        $mailMessage = 'No email sent.';
        // --- Send Email if Exe_Remarks is "Ready To Pay" ---
        if ($exeRemarksValue === 'Ready To Pay' && !empty($email)) {
            try {
                $smtp = SmtpSetting::where('user_id', $user->id)->first();
                if (!$smtp) {
                    return response()->json([
                        'message' => 'No SMTP settings found.'
                    ]);
                } else {
                    // Configure mailer dynamically (same as test() method)
                    config([
                        'mail.mailers.smtp.transport' => $smtp->mailer,
                        'mail.mailers.smtp.host' => $smtp->host,
                        'mail.mailers.smtp.port' => $smtp->port,
                        'mail.mailers.smtp.username' => $smtp->username,
                        'mail.mailers.smtp.password' => decrypt($smtp->password),
                        'mail.mailers.smtp.encryption' => $smtp->encryption,
                        'mail.from.address' => $smtp->from_address,
                        'mail.from.name' => $smtp->from_name,
                    ]);

                    // --- Fetch Email Template from Database ---
                    $template = EmailTemplate::where('name', 'Called_Mailed')->first();

                    if ($template) {
                        $subject = $template->subject;
                        $messageBody = $template->body;
                    } else {
                        // Fallback if template not found
                        $subject = "Unlock Career Stability with Fortune 500 Projects !";
                        $messageBody =
                            "Hi {$name},\n\n" .
                            "I hope this message finds you well.\n\n" .
                            "My name is {$smtp->from_name}, and I’m part of the Talent Acquisition Team at Synergie Systems INC., a respected workforce development and project management firm based in Delaware. We partner with some of the most renowned Fortune 500 companies across the U.S., delivering not just staffing solutions but long-term career success.\n\n" .
                            "After reviewing your profile, I believe you could be a strong fit for several exciting opportunities we currently have available. And more importantly, I believe we can offer you not just a job, but a career pathway built on stability, support, and growth.\n\n" .
                            "What Makes Synergie Different?\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "At Synergie, we understand that a fulfilling career is built on trust, purpose, and progress. That's why we go beyond recruitment—we invest in you. Our commitment is simple: to help you grow, thrive, and achieve your highest potential.\n\n" .
                            "Here’s what you can expect when you join our community:\n\n" .
                            "                  - Direct Project Placements with Fortune 500 and Tier 1 clients\n" .
                            "                  - Full-time employment with Synergie—never just a short-term contract\n" .
                            "                  - Real-world project experience with today’s most in-demand tools and technologies\n" .
                            "                  - Dedicated support from day one: resume branding, interview prep, and onboarding guidance\n" .
                            "                  - Zero Bond Policy—because your freedom and career choices matter\n" .
                            "                  - Support for OPT, CPT, STEM OPT, H1B & Green Card sponsorships\n\n" .
                            "More Than a Paycheck — A Path to Prosperity\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "We believe that when you bring value, you deserve to be valued. That’s why we offer a transparent, competitive compensation structure designed to reward your dedication and drive.\n\n" .
                            "                  - Full-Time Roles: \$40–\$50/hr\n" .
                            "                  - Part-Time Roles: \$15–\$25/hr\n" .
                            "                  - Paid Internships available\n" .
                            "                  - 15% Salary Raise every 6 months based on performance\n" .
                            "                  - 12 Days Paid Vacation annually\n" .
                            "                  - Relocation Assistance for client deployments\n\n" .
                            "Comprehensive Benefits That Put You First\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "At Synergie, we care for your career—and your well-being. We provide:\n\n" .
                            "                  - Health, Dental & Vision Insurance\n" .
                            "                  - Short- & Long-Term Disability Insurance\n" .
                            "                  - Life Insurance & 401(k) Retirement Plan\n" .
                            "                  - Legal & Immigration Support\n" .
                            "                  - Tax Assistance & Transparent Payroll\n" .
                            "                  - Workers’ Compensation—your safety is our priority\n\n" .
                            "Support Tailored for International Talent\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "We take pride in guiding hundreds of F1/OPT/CPT/STEM OPT professionals every year toward long-term success in the U.S.:\n\n" .
                            "                  - Offer Letters, Client Confirmations & Employer Letters\n" .
                            "                  - Full STEM Extension & OPT/CPT Support\n" .
                            "                  - H1B Sponsorship after project onboarding\n" .
                            "                  - Relocation & Immigration Documentation\n" .
                            "                  - Ongoing Green Card Processing Assistance\n\n" .
                            "Not Quite Job-Ready? We’ll Bridge That Gap\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "Sometimes, all it takes is one last push to unlock your dream opportunity. That’s why we offer a 4-week industry-focused workshop, designed by experts with over a decade of experience to prepare you for real-world success.\n\n" .
                            "What You’ll Gain:\n\n" .
                            "                  - Live Zoom sessions & recorded expert sessions\n" .
                            "                  - Real-time project simulations & hands-on assignments\n" .
                            "                  - One-on-one resume branding & mock interviews\n" .
                            "                  - Global Certificate of Completion & recruiter access\n" .
                            "                  - 100% Fee Refund with your first project paycheck (Only \${$amount}—one-time, fully refundable)\n\n" .
                            "Let’s Take the First Step Together\n" .
                            "-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n\n" .
                            "If you’re seeking more than just another role—if you’re looking for a career that recognizes your potential, offers true support, and opens doors to the future you deserve—then Synergie is here for you.\n\n" .
                            "This is your opportunity to move forward with confidence, backed by a team that believes in you and works tirelessly to help you succeed.\n\n" .
                            "Please feel free to reply to this email or reach me directly over the phone if you’d like to learn more or take the next step.\n\n" .
                            "Wishing you success in every path you choose—but hoping we’ll have the honor of being part of your journey.\n\n" .
                            "Visit Our Website: https://www.synergiesystems.com/";
                    }

                    // --- Send Email (No Template Logic Changed) ---
                    Mail::raw($messageBody, function ($message) use ($email, $subject, $smtp) {
                        $message->from($smtp->from_address, $smtp->from_name)
                            ->to($email)
                            ->subject($subject);
                    });

                    $mailMessage = "Email sent successfully to {$email}!";
                }
            } catch (\Exception $e) {
                $mailMessage = 'Failed to send email: ' . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'id' => $record->id,
            'sheet_row_number' => $record->sheet_row_number,
            'resume_path' => !empty($record->resume) ? true : false,
            'mail_message' => $mailMessage,
            'save_message' => $saveMessage,
        ]);
    }

    // Add a method to serve the PDF files
    public function viewtrainerResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }

    // Add a method to download the PDF files
    public function downloadtrainerResume($id)
    {
        $row = GoogleSheetData::find($id);

        if (!$row || !$row->resume) {
            abort(404);
        }

        $filePath = storage_path('app/public/' . $row->resume);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, basename($filePath));
    }
}
