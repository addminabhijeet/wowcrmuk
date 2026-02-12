@extends('layout.layout')

@php
    $title = 'Database -> Candidate Details';
    $role = auth()->user()->role ?? '';
    if ($role === 'admin') {
        $subTitle = 'Super Admin';
    } elseif ($role === 'operation') {
        $subTitle = 'Operation Manager';
    } else {
        $subTitle = 'role';
    }
    $script = '<script src="' . asset('assets/js/homeOneChart.js') . '"></script>';
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-12">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">

                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-to-do-list" role="tabpanel"
                            aria-labelledby="pills-to-do-list-tab" tabindex="0">

                            <div class="table-responsive">
                                <!-- Add Candidate Button -->




                                <table class="table table-hover table-bordered align-middle mb-0">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th scope="col" class="text-center">No.</th>
                                            <th scope="col" class="text-center">Name</th>
                                            <th scope="col" class="text-center">Email Address</th>
                                            <th scope="col" class="text-center">Phone Number</th>
                                            <th scope="col" class="text-center">View</th>
                                            <th scope="col" class="text-center">Services</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($data as $index => $row)
                                            <tr class="text-center align-middle">
                                                <!-- No -->
                                                <td>{{ $row->sheet_row_number ?? $index + 1 }}</td>

                                                <!-- Name -->
                                                <td>
                                                    <span class="fw-medium text-sm">
                                                        {{ $row->Name ?? '-' }}
                                                    </span>
                                                </td>

                                                <!-- Email -->
                                                <td class="text-center">
                                                    <span class="fw-medium text-sm text-truncate"
                                                        style="max-width: 180px; display: inline-block;">
                                                        {{ $row->Email_Address ?? '-' }}
                                                    </span>
                                                </td>

                                                <!-- Phone -->
                                                <td>
                                                    <span class="fw-medium text-sm">
                                                        {{ $row->Phone_Number ?? '-' }}
                                                    </span>
                                                </td>

                                                <!-- View -->
                                                <td>
                                                    <a href="{{ route('all.associate.candidate', [$row->id, $row->forwarded_by]) }}"
                                                        class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-medium text-sm">
                                                        View Details
                                                    </a>
                                                </td>

                                                <!-- Services -->
                                                <td>
                                                    <a href="{{ route('all.associate.services', [$row->id, $row->forwarded_by]) }}"
                                                        class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-medium text-sm">
                                                        Services Details
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div> <!-- /.table-responsive -->

                        </div> <!-- /.tab-pane -->
                    </div> <!-- /.tab-content -->

                </div> <!-- /.card-body -->
            </div> <!-- /.card -->
        </div> <!-- /.col-12 -->
    </div> <!-- /.row -->

    <!-- Add Candidate Modal -->
    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1" aria-labelledby="addCandidateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content radius-10">

                <div class="modal-header">
                    <h5 class="modal-title" id="addCandidateModalLabel">Add New Candidate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="addCandidateForm" action="{{ route('all.associate.add') }}" method="POST">
                    @csrf

                    <div class="modal-body">

                        <!-- Error Message -->
                        <div id="candidateError" class="alert d-none mb-3"></div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" class="form-control radius-8" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control radius-8" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control radius-8" required>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Candidate</button>
                    </div>

                </form>

            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {

            $("#addCandidateForm").on("submit", function(e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr("action");
                let formData = form.serialize();

                $("#candidateError")
                    .removeClass("alert-danger alert-success")
                    .addClass("d-none")
                    .html("");

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    success: function(response) {

                        // ❌ Error Message
                        if (response.success === false) {
                            $("#candidateError")
                                .removeClass("d-none alert-success")
                                .addClass("alert-danger")
                                .html(response.message);
                            return;
                        }

                        // ✅ Success Message
                        if (response.success === true) {
                            $("#candidateError")
                                .removeClass("d-none alert-danger")
                                .addClass("alert-success")
                                .html(response.message);

                            // Clear form (optional)
                            form.trigger("reset");
                        }
                    },

                    error: function(xhr) {
                        let errorText = "Something went wrong.";

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorText = xhr.responseJSON.message;
                        }

                        $("#candidateError")
                            .removeClass("d-none alert-success")
                            .addClass("alert-danger")
                            .html(errorText);
                    }
                });
            });

        });
    </script>
@endsection
