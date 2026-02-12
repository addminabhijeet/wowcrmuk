@if ($data->isEmpty())
    <p class="text-muted">No data found. Fetch a Google Sheet first.</p>
@else
    <div class="table-responsive scroll-sm">
        <table class="table bordered-table sm-table mb-0">
            <thead>
                <tr>
                    <th scope="col" class="text-center">Row</th>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Name</th>
                    <th scope="col" class="text-center">Email Address</th>
                    <th scope="col" class="text-center">Phone Number</th>
                    <th scope="col" class="text-center">Location</th>

                    <th scope="col" class="text-center">Relocation</th>
                    <th scope="col" class="text-center">Graduation Date</th>
                    <th scope="col" class="text-center">Immigration</th>
                    <th scope="col" class="text-center">Course</th>
                    <th scope="col" class="text-center">Amount</th>
                    <th scope="col" class="text-center">Qualification</th>

                    <th scope="col" class="text-center">1st Follow Up Remarks</th>
                    <th scope="col" class="text-center">Time Zone</th>
                    <th scope="col" class="text-center">Forwarded By</th>
                    <th scope="col" class="text-center">Resume</th>
                    <th scope="col" class="text-center">Remark</th>
                    <th scope="col" class="text-center">Status</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="sheet-table-body">
                @foreach ($data as $row)
                    <tr id="row-{{ $row->id }}" data-id="{{ $row->id }}">

                        <td>{{ $row->sheet_row_number }}</td>

                        {{-- Date --}}
                        <td>
                            <input type="text" class="form-control date-picker" data-key="Date"
                                value="{{ $row->Date ? \Carbon\Carbon::parse($row->Date)->format('m/d/Y') : '' }}">
                        </td>

                        {{-- Name --}}
                        <td>
                            <input type="text" class="form-control name-input" data-key="Name"
                                value="{{ $row->Name ?? '' }}" placeholder="Name">
                        </td>

                        {{-- Email Address --}}
                        <td>
                            <input type="email" class="form-control email-input" data-key="Email Address"
                                value="{{ $row->Email_Address ?? '' }}" placeholder="E-mail">
                        </td>

                        {{-- Phone Number --}}
                        <td>
                            <input type="tel" class="form-control phone-input" data-key="Phone Number"
                                maxlength="14" value="{{ $row->Phone_Number ?? '' }}" placeholder="US number">
                        </td>

                        {{-- Location --}}
                        <td>
                            <input type="text" class="form-control location-autocomplete" data-key="Location"
                                value="{{ $row->Location ?? '' }}" placeholder="Type location">
                        </td>



                        {{-- Relocation --}}
                        <td>
                            @php $relOptions = ['YES','NO']; @endphp
                            <select class="form-select dynamic-dropdown" data-key="Relocation">
                                <option value="">-- Relocation --</option>
                                @foreach ($relOptions as $option)
                                    <option value="{{ $option }}"
                                        {{ $row->Relocation === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        {{-- Graduation Date --}}
                        <td>
                            <input type="text" class="form-control date-picker" data-key="Graduation Date"
                                value="{{ $row->Graduation_Date ? \Carbon\Carbon::parse($row->Graduation_Date)->format('m/d/Y') : '' }}">
                        </td>

                        {{-- Immigration --}}
                        <td>
                            @php $immOptions = ['F1 CPT','F1 OPT','STEM OPT','H1B','B2','B1','H4','H4 EAD', 'GC/PR','USC']; @endphp
                            <select class="form-select dynamic-dropdown" data-key="Immigration">
                                <option value="">--Immigration --</option>
                                @foreach ($immOptions as $option)
                                    <option value="{{ $option }}"
                                        {{ $row->Immigration === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        {{-- Course --}}
                        <td>
                            @php $courseOptions = ['BA','SAS','JAVA','QA','SQL','PYTHON','DOT NET']; @endphp
                            <select class="form-select dynamic-dropdown" data-key="Course">
                                <option value="">-- Course --</option>
                                @foreach ($courseOptions as $option)
                                    <option value="{{ $option }}"
                                        {{ $row->Course === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        {{-- Amount --}}
                        <td>
                            <input type="text" class="form-control amount-input" data-key="Amount"
                                value="{{ $row->Amount !== null ? '$' . number_format($row->Amount, 2) : '' }}"
                                placeholder="Amount (469)">
                        </td>

                        {{-- Qualification --}}
                        <td>
                            @php
                                $qualificationOptions = [
                                    'Masters',
                                    'Masters of Science',
                                    'Bachelors',
                                    'PG',
                                    'MBA',
                                    'PG Diploma',
                                    'M.Tech',
                                    'B.Tech',
                                    'MA',
                                    'Associate Degree',
                                    'Aerospace Proj. Manag.',
                                ];
                            @endphp

                            <select class="form-select dynamic-dropdown" data-key="Qualification">
                                <option value="">-- Qualification --</option>
                                @foreach ($qualificationOptions as $option)
                                    <option value="{{ $option }}"
                                        {{ $row->Qualification === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </td>



                        {{-- 1st Follow Up Remarks --}}
                        <td>
                            @php $followOptions = ['Interested','Doubt need Clarification','Money Issue','Not Interested','Don\'t Call']; @endphp
                            <select class="form-select dynamic-dropdown" data-key="1st Follow Up Remarks">
                                <option value="">-- 1st Follow Up Remarks --</option>
                                @foreach ($followOptions as $option)
                                    <option value="{{ $option }}"
                                        {{ $row->First_Follow_Up_Remarks === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </td>



                        {{-- Time Zone --}}
                        <td>
                            @php $timezoneOptions = ['EST','CST','MST','PST']; @endphp
                            <select class="form-select dynamic-dropdown" data-key="Time Zone">
                                <option value="">-- Time Zone --</option>
                                @foreach ($timezoneOptions as $option)
                                    <option value="{{ $option }}"
                                        {{ $row->Time_Zone === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        {{-- Forwarded By --}}
                        <td>
                            <input type="text" class="form-control forwardedBy-input" data-key="forwardedBy"
                                value="{{ $row->forwarded_by ?? '' }}" placeholder="Forwarded By" readonly>
                        </td>

                        {{-- View (Resume) --}}
                        <td>
                            <input type="file" accept=".pdf, .doc, .docx" class="d-none resume-input"
                                data-key="View">

                            <button type="button" class="btn btn-sm btn-info upload-btn">
                                {{ !empty($row->resume) ? 'Change File' : 'Upload' }}
                            </button>

                            @if (!empty($row->resume))
                                <a href="{{ url('dashboard/junior/google-sheet/view-resume/' . $row->id) }}"
                                    target="_blank" class="btn btn-sm btn-primary view-btn">View File</a>

                                <a href="{{ url('dashboard/junior/google-sheet/download-resume/' . $row->id) }}"
                                    class="btn btn-sm btn-secondary download-btn">Download</a>
                            @else
                                <a href="#" target="_blank" class="btn btn-sm btn-primary view-btn d-none">View
                                    File</a>
                                <a href="#" download
                                    class="btn btn-sm btn-secondary download-btn d-none">Download</a>
                            @endif
                        </td>

                        {{-- Remark --}}
                        <td>
                            <input type="text" class="form-control remark-autocomplete" data-key="Remark"
                                value="{{ $row->Remark ?? '' }}" placeholder="Type remark">
                        </td>


                        {{-- Status --}}
                        <td>
                            @php $exeOptions = ['Called & Mailed','Not Interested','Not Connected','Did Not Pickup','Others','Ready To Pay','VM','Busy']; @endphp
                            <select class="form-select dynamic-dropdown" data-key="Exe Remarks">
                                <option value="">-- Status --</option>
                                @foreach ($exeOptions as $option)
                                    <option value="{{ $option }}"
                                        {{ $row->Exe_Remarks === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                    </tr>
                @endforeach
            </tbody>
            <script>
                $(document).ready(function() {
                    $('.save-btn').click(function() {
                        let rowId = $(this).data('id');
                        let $tr = $('#row-' + rowId);

                        // Collect row data
                        let data = {};
                        $tr.find('input, select').each(function() {
                            let key = $(this).data('key');
                            if (key) {
                                if ($(this).is('select')) {
                                    data[key] = $(this).val();
                                } else {
                                    data[key] = $(this).val();
                                }
                            }
                        });

                        let formData = new FormData();
                        formData.append('id', rowId);
                        formData.append('data', JSON.stringify(data));

                        // Attach resume file if uploaded
                        let fileInput = $tr.find('input.resume-input')[0];
                        if (fileInput && fileInput.files.length > 0) {
                            formData.append('resume', fileInput.files[0]);
                        }

                        $.ajax({
                            url: '{{ route('seniorupdate') }}',
                            type: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert(response.message);
                                    // Optionally update resume buttons dynamically
                                    if (response.row.resume_exists) {
                                        let viewBtn = $tr.find('.view-btn');
                                        viewBtn.attr('href',
                                                '/dashboard/senior/google-sheet/view-resume/' + rowId)
                                            .removeClass('d-none');
                                        let downloadBtn = $tr.find('.download-btn');
                                        downloadBtn.attr('href',
                                            '/dashboard/senior/google-sheet/download-resume/' +
                                            rowId).removeClass('d-none');
                                        $tr.find('.upload-btn').text('Change File');
                                    }
                                } else {
                                    alert(response.message);
                                }
                            },
                            error: function(err) {
                                alert('AJAX error: ' + err.responseText);
                            }
                        });
                    });

                    // Show file input when clicking upload
                    $('.upload-btn').click(function() {
                        $(this).closest('td').find('input.resume-input').click();
                    });
                });
            </script>
        </table>
@endif
</div>
{{-- Pagination --}}
@if ($data->hasPages())
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
        <div>
            {{ $data->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endif
