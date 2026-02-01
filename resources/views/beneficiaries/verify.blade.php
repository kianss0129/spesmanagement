@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">
                Onboarding Verification - {{ $beneficiary->first_name }} {{ $beneficiary->last_name }}
            </h1>
            <p class="text-gray-600 text-lg">Review beneficiary onboarding submissions</p>
        </div>

        <!-- Status Banner -->
        <div class="mb-6">
            @php
                $statusConfig = match($beneficiary->approval_status ?? 'pending') {
                    'approved' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-800', 'badge' => 'bg-green-100', 'label' => 'Approved'],
                    'rejected' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-800', 'badge' => 'bg-red-100', 'label' => 'Rejected'],
                    default => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-800', 'badge' => 'bg-yellow-100', 'label' => 'Pending Review'],
                };
            @endphp
            <div class="{{ $statusConfig['bg'] }} border-l-4 {{ $statusConfig['border'] }} p-4 rounded-lg">
                <div class="flex items-center">
                    <span class="inline-block px-3 py-1 text-sm font-semibold {{ $statusConfig['text'] }} {{ $statusConfig['badge'] }} rounded-full">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Beneficiary Information Section -->
        <div class="bg-white shadow-md rounded-lg p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Beneficiary Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                    <p class="text-lg text-gray-900">{{ $beneficiary->first_name }} {{ $beneficiary->last_name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                    <p class="text-lg text-gray-900">{{ $beneficiary->email }}</p>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number</label>
                    <p class="text-lg text-gray-900">
                        @if($beneficiary->phone)
                            {{ $beneficiary->phone }}
                        @else
                            <span class="text-gray-500 italic">Not provided</span>
                        @endif
                    </p>
                </div>

                <!-- Submission Date -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Submission Date</label>
                    <p class="text-lg text-gray-900">
                        @if($beneficiary->onboarding_completed_at)
                            {{ $beneficiary->onboarding_completed_at->format('F j, Y \\a\\t h:i A') }}
                        @else
                            <span class="text-gray-500 italic">Not yet submitted</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Conditional Fields Based on Beneficiary Type -->
            <div class="border-t pt-6">
                @if($beneficiary->beneficiary_type === 'student' || !isset($beneficiary->beneficiary_type) || empty($beneficiary->beneficiary_type))
                    <!-- Student -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">School</label>
                            <p class="text-lg text-gray-900">
                                @if($beneficiary->school?->name)
                                    {{ $beneficiary->school->name }}
                                @else
                                    <span class="text-gray-500 italic">Not provided</span>
                                @endif
                            </p>
                        </div>
                    </div>

                @elseif($beneficiary->beneficiary_type === 'osy')
                    <!-- Out-of-School Youth (OSY) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Skills / Training</label>
                            <p class="text-lg text-gray-900">
                                @if($beneficiary->skills)
                                    {{ $beneficiary->skills }}
                                @else
                                    <span class="text-gray-500 italic">Not provided</span>
                                @endif
                            </p>
                        </div>
                    </div>

                @elseif($beneficiary->beneficiary_type === 'dependent' || $beneficiary->beneficiary_type === 'displaced_worker')
                    <!-- Dependent / Displaced Worker -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                @if($beneficiary->beneficiary_type === 'dependent')
                                    Parent / Guardian Name
                                @else
                                    Parent / Guardian Name
                                @endif
                            </label>
                            <p class="text-lg text-gray-900">
                                @if($beneficiary->parent_name)
                                    {{ $beneficiary->parent_name }}
                                @else
                                    <span class="text-gray-500 italic">Not provided</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Displacement Reason</label>
                            <p class="text-lg text-gray-900">
                                @if($beneficiary->displacement_reason)
                                    {{ $beneficiary->displacement_reason }}
                                @else
                                    <span class="text-gray-500 italic">Not provided</span>
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Documents Section -->
        <div class="bg-white shadow-md rounded-lg p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Submitted Documents</h2>

            @if(!$documents || count($documents) === 0)
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <p class="text-gray-500 text-lg">No documents submitted</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($documents as $index => $document)
                        <div class="border rounded-lg p-4 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">
                                    <svg class="inline-block w-5 h-5 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $document['name'] ?? 'Document ' . ($index + 1) }}
                                </p>
                                @if($document['uploaded_at'])
                                    <p class="text-sm text-gray-600 mt-1">
                                        Uploaded: {{ \Carbon\Carbon::parse($document['uploaded_at'])->format('F j, Y \\a\\t h:i A') }}
                                    </p>
                                @endif
                                @if(!$document['exists'])
                                    <p class="text-sm text-red-600 mt-1">
                                        ❌ File missing or deleted
                                    </p>
                                @endif
                            </div>

                            <div class="ml-4">
                                @if($document['exists'] && $document['url'])
                                    <a href="{{ $document['url'] }}" 
                                       target="_blank" 
                                       class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium text-sm">
                                        View / Download
                                    </a>
                                @else
                                    <button disabled 
                                            class="inline-block bg-gray-300 text-gray-600 px-4 py-2 rounded-lg cursor-not-allowed font-medium text-sm">
                                        Unavailable
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Verification Actions Section -->
        @if(!$beneficiary->approval_status || $beneficiary->approval_status === 'pending')
            <div class="bg-white shadow-md rounded-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Verification Actions</h2>

                <div class="space-y-6">
                    <!-- Approve Button -->
                    <form action="{{ route('beneficiaries.verify', $beneficiary->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" 
                                class="w-full bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold text-lg"
                                onclick="return confirm('Are you sure you want to approve this beneficiary?')">
                            ✓ Approve Beneficiary Onboarding
                        </button>
                    </form>

                    <!-- Reject Form -->
                    <div class="border-t pt-6">
                        <form action="{{ route('beneficiaries.verify', $beneficiary->id) }}" method="POST" id="rejectForm">
                            @csrf
                            <input type="hidden" name="action" value="reject">

                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Rejection Reason (Required if rejecting)
                            </label>
                            <textarea 
                                name="rejection_reason"
                                id="rejection_reason"
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent mb-4"
                                rows="4"
                                placeholder="Enter reason for rejection (e.g., Incomplete documents, Invalid information, School not verified, etc.)...">
                            </textarea>

                            <button type="submit" 
                                    id="rejectBtn"
                                    class="w-full bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold text-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                                    onclick="return confirm('Are you sure you want to reject this beneficiary? This action requires a rejection reason.')">
                                ✗ Reject Onboarding
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <!-- Approved/Rejected Status Display -->
            <div class="bg-white shadow-md rounded-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Verification Status</h2>

                @if($beneficiary->approval_status === 'approved')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <p class="text-green-800 text-lg font-semibold">
                            ✓ This beneficiary has been approved
                        </p>
                        @if($beneficiary->approved_at)
                            <p class="text-green-700 mt-2">
                                Approved on: {{ $beneficiary->approved_at->format('F j, Y \\a\\t h:i A') }}
                            </p>
                        @endif
                    </div>
                @elseif($beneficiary->approval_status === 'rejected')
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <p class="text-red-800 text-lg font-semibold">
                            ✗ This beneficiary has been rejected
                        </p>
                        @if($beneficiary->rejection_reason)
                            <p class="text-red-700 mt-2">
                                <strong>Reason:</strong> {{ $beneficiary->rejection_reason }}
                            </p>
                        @endif
                        @if($beneficiary->rejected_at)
                            <p class="text-red-700 mt-2">
                                Rejected on: {{ $beneficiary->rejected_at->format('F j, Y \\a\\t h:i A') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>

<script>
    // Validate rejection reason before submit
    document.getElementById('rejectForm')?.addEventListener('submit', function(e) {
        const reason = document.getElementById('rejection_reason').value.trim();
        if (!reason) {
            e.preventDefault();
            alert('Please provide a rejection reason before submitting.');
            document.getElementById('rejection_reason').focus();
            return false;
        }
    });
</script>

@endsection
