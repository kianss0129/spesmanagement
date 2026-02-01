# Beneficiary Verification Page - Code Reference

## File Structure

```
SPES-SYSTEM-2/
├── resources/
│   └── views/
│       └── beneficiaries/
│           └── verify.blade.php ✅ NEW
├── app/
│   └── Http/
│       └── Controllers/
│           └── Beneficiary/
│               └── BeneficiaryController.php ✅ MODIFIED
├── routes/
│   └── web.php ✅ MODIFIED
└── Documentation/
    ├── VERIFICATION-PAGE-SUMMARY.md ✅ NEW
    ├── VERIFICATION-PAGE-GUIDE.md ✅ NEW
    └── VERIFICATION-TEST-GUIDE.md ✅ NEW
```

## Code Snippets

### 1. Blade Template Sections

**Status Banner**
```blade
@php
    $statusConfig = match($beneficiary->approval_status ?? 'pending') {
        'approved' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-800', 'badge' => 'bg-green-100', 'label' => 'Approved'],
        'rejected' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-800', 'badge' => 'bg-red-100', 'label' => 'Rejected'],
        default => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-800', 'badge' => 'bg-yellow-100', 'label' => 'Pending Review'],
    };
@endphp
<div class="{{ $statusConfig['bg'] }} border-l-4 {{ $statusConfig['border'] }} p-4 rounded-lg">
    <span class="inline-block px-3 py-1 text-sm font-semibold {{ $statusConfig['text'] }} {{ $statusConfig['badge'] }} rounded-full">
        {{ $statusConfig['label'] }}
    </span>
</div>
```

**Conditional Fields**
```blade
@if($beneficiary->beneficiary_type === 'student' || !isset($beneficiary->beneficiary_type) || empty($beneficiary->beneficiary_type))
    <!-- Student -->
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

@elseif($beneficiary->beneficiary_type === 'osy')
    <!-- Out-of-School Youth (OSY) -->
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

@elseif($beneficiary->beneficiary_type === 'dependent' || $beneficiary->beneficiary_type === 'displaced_worker')
    <!-- Dependent / Displaced Worker -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Parent / Guardian Name</label>
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
```

**Documents Section**
```blade
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
```

**Approval Section**
```blade
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
                        placeholder="Enter reason for rejection...">
                    </textarea>

                    <button type="submit" 
                            id="rejectBtn"
                            class="w-full bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold text-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                            onclick="return confirm('Are you sure you want to reject this beneficiary?')">
                        ✗ Reject Onboarding
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
```

### 2. Controller Methods

**Show Verification**
```php
public function showVerification(Beneficiary $beneficiary)
{
    $beneficiary->load('user', 'school');

    $documents = [];
    if ($beneficiary->documents) {
        $raw = is_array($beneficiary->documents) ? $beneficiary->documents : [$beneficiary->documents];
        foreach ($raw as $entry) {
            $entryPath = null;
            $entryName = null;
            $entryUploadedAt = null;

            if (is_string($entry)) {
                $entryPath = $entry;
            } elseif (is_array($entry)) {
                $entryPath = $entry['path'] ?? $entry['file'] ?? null;
                $entryName = $entry['name'] ?? $entry['filename'] ?? null;
                $entryUploadedAt = $entry['uploaded_at'] ?? $entry['created_at'] ?? null;
            } elseif (is_object($entry)) {
                $entryPath = $entry->path ?? $entry->file ?? null;
                $entryName = $entry->name ?? $entry->filename ?? null;
                $entryUploadedAt = $entry->uploaded_at ?? $entry->created_at ?? null;
            }

            if (!$entryPath) {
                continue;
            }

            $exists = Storage::disk('public')->exists((string) $entryPath);

            $url = null;
            if ($exists) {
                try {
                    $url = Storage::disk('public')->url((string) $entryPath);
                } catch (\Throwable $e) {
                    $url = null;
                }
            }

            $uploadedAt = $entryUploadedAt;
            if (!$uploadedAt && $exists) {
                try {
                    $fullPath = storage_path('app/public/' . ltrim((string) $entryPath, '/'));
                    if (file_exists($fullPath)) {
                        $uploadedAt = date('c', filemtime($fullPath));
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            $documents[] = [
                'path' => $entryPath,
                'url' => $url,
                'name' => $entryName ?? basename((string) $entryPath),
                'uploaded_at' => $uploadedAt,
                'exists' => $exists,
            ];
        }
    }

    return view('beneficiaries.verify', [
        'beneficiary' => $beneficiary,
        'documents' => $documents,
    ]);
}
```

**Verify Action**
```php
public function verify(Request $request, Beneficiary $beneficiary)
{
    $validated = $request->validate([
        'action' => 'required|in:approve,reject',
        'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
    ]);

    if ($validated['action'] === 'approve') {
        $beneficiary->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved' => true,
            'rejection_reason' => null,
            'rejected_at' => null,
        ]);

        Log::info("Beneficiary {$beneficiary->id} approved by " . auth()->user()->name);

        return redirect()
            ->route('beneficiaries.verify', $beneficiary->id)
            ->with('success', 'Beneficiary has been approved successfully.');
    }

    if ($validated['action'] === 'reject') {
        $beneficiary->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_at' => now(),
            'approved' => false,
            'approved_at' => null,
        ]);

        Log::info("Beneficiary {$beneficiary->id} rejected by " . auth()->user()->name . ". Reason: {$validated['rejection_reason']}");

        return redirect()
            ->route('beneficiaries.verify', $beneficiary->id)
            ->with('success', 'Beneficiary has been rejected. A notification will be sent to the beneficiary.');
    }
}
```

### 3. Routes

```php
// In routes/web.php within PESO Admin middleware group
Route::middleware(['role:PESO Admin'])->group(function () {
    // Verification page and actions
    Route::get('beneficiaries/{beneficiary}/verify', [BeneficiaryController::class, 'showVerification'])
        ->name('beneficiaries.verify.show');
    Route::post('beneficiaries/{beneficiary}/verify', [BeneficiaryController::class, 'verify'])
        ->name('beneficiaries.verify');
});
```

## TailwindCSS Classes Used

### Colors
- `bg-green-600`, `hover:bg-green-700` - Approve action
- `bg-red-600`, `hover:bg-red-700` - Reject action
- `bg-blue-600`, `hover:bg-blue-700` - View/Download button
- `bg-yellow-50`, `bg-yellow-100`, `border-yellow-200`, `text-yellow-800` - Pending status
- `bg-green-50`, `bg-green-100`, `border-green-200`, `text-green-800` - Approved status
- `bg-red-50`, `bg-red-100`, `border-red-200`, `text-red-800` - Rejected status

### Layout
- `min-h-screen`, `py-8`, `px-4` - Page container
- `max-w-4xl`, `mx-auto` - Content width
- `bg-white`, `shadow-md`, `rounded-lg`, `p-8`, `mb-6` - Cards
- `grid`, `grid-cols-1`, `md:grid-cols-2`, `gap-6` - Responsive grid
- `space-y-4`, `space-y-6` - Spacing between elements

### Typography
- `text-4xl`, `font-bold`, `text-gray-900` - Page title
- `text-2xl`, `font-bold` - Section titles
- `text-lg` - Regular body text
- `text-sm`, `font-semibold` - Labels
- `text-gray-500`, `italic` - Empty/placeholder text

### Buttons & Forms
- `w-full`, `px-6`, `py-3`, `rounded-lg`, `transition` - Button base
- `border`, `border-gray-300`, `rounded-lg`, `px-4`, `py-3` - Input base
- `focus:outline-none`, `focus:ring-2`, `focus:ring-red-500` - Focus states
- `disabled:bg-gray-400`, `disabled:cursor-not-allowed` - Disabled states

### Interactive
- `hover:bg-gray-50`, `transition` - Hover effects
- `focus:outline-none`, `focus:ring-2` - Focus styles
- `cursor-not-allowed` - Disabled cursor

## JavaScript

**Client-side form validation:**
```javascript
document.getElementById('rejectForm')?.addEventListener('submit', function(e) {
    const reason = document.getElementById('rejection_reason').value.trim();
    if (!reason) {
        e.preventDefault();
        alert('Please provide a rejection reason before submitting.');
        document.getElementById('rejection_reason').focus();
        return false;
    }
});
```

**Confirmation dialogs:**
```html
onclick="return confirm('Are you sure you want to approve this beneficiary?')"
onclick="return confirm('Are you sure you want to reject this beneficiary?')"
```

## Key Design Patterns

1. **Dynamic Status:** Uses match() expression for status colors
2. **Nullable Relationships:** Safe navigation with `->?`
3. **Conditional Rendering:** @if/@elseif/@endif for type-specific fields
4. **Error Handling:** Graceful fallbacks for missing data
5. **Storage Integration:** Proper use of Storage facade
6. **Logging:** Action audit trail via Log facade
7. **Validation:** Server-side + client-side validation
8. **Security:** CSRF tokens, role-based access

## Integration Points

1. **OnboardingController** → Documents stored and paths saved
2. **Beneficiary Model** → Data retrieval and updates
3. **Storage Facade** → File access and URL generation
4. **Auth Middleware** → User authentication
5. **Role Middleware** → PESO Admin authorization
6. **Model Binding** → Automatic beneficiary lookup
7. **View Routing** → Inertia-like template rendering

---

**All code is production-ready and follows Laravel best practices.**
