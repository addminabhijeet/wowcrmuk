@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Login History</h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>User Name</th>
                <th>Email</th>
                <th>IP Address</th>
                <th>User Agent</th>
                <th>Logged In At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logins as $index => $login)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $login->user->name ?? 'Unknown' }}</td>
                    <td>{{ $login->user->email ?? 'N/A' }}</td>
                    <td>{{ $login->ip_address }}</td>
                    <td style="max-width: 300px; word-break: break-word;">
                        {{ $login->user_agent }}
                    </td>
                    <td>{{ $login->logged_in_at->format('d M Y, h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No login records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
