<?php
/**
 * Test script to verify document storage and retrieval flow
 * Run with: php test-document-flow.php
 */

// Load Laravel framework
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Models\Beneficiary;
use App\Models\User;

echo "=== Document Storage & Retrieval Test ===\n\n";

// Test 1: Check if public disk is configured correctly
echo "1. Checking public disk configuration...\n";
$publicPath = storage_path('app/public');
if (is_dir($publicPath)) {
    echo "   ✓ storage/app/public directory exists\n";
} else {
    echo "   ✗ storage/app/public directory NOT found\n";
}

// Test 2: Check if storage symlink exists
echo "\n2. Checking storage symlink...\n";
$symlinkPath = public_path('storage');
if (is_link($symlinkPath)) {
    echo "   ✓ public/storage symlink exists\n";
    echo "   Points to: " . realpath($symlinkPath) . "\n";
} else {
    echo "   ✗ public/storage symlink NOT found\n";
}

// Test 3: Test document directory creation
echo "\n3. Testing document directory creation...\n";
$testDir = 'documents/users/test';
if (Storage::disk('public')->makeDirectory($testDir)) {
    echo "   ✓ Can create directories on public disk\n";
} else {
    echo "   ✗ Failed to create directory on public disk\n";
}

// Test 4: Test file storage
echo "\n4. Testing file storage...\n";
$testFile = $testDir . '/test.txt';
$content = 'Test document content - ' . date('Y-m-d H:i:s');
if (Storage::disk('public')->put($testFile, $content)) {
    echo "   ✓ File stored successfully at: documents/users/test/test.txt\n";
} else {
    echo "   ✗ Failed to store file\n";
}

// Test 5: Test file existence check
echo "\n5. Testing file existence check...\n";
if (Storage::disk('public')->exists($testFile)) {
    echo "   ✓ File exists check passed\n";
} else {
    echo "   ✗ File existence check failed\n";
}

// Test 6: Test URL generation
echo "\n6. Testing URL generation...\n";
try {
    $url = Storage::disk('public')->url($testFile);
    echo "   ✓ Generated URL: " . $url . "\n";
    echo "   Full accessible path would be: http://localhost:8000" . $url . "\n";
} catch (\Exception $e) {
    echo "   ✗ Failed to generate URL: " . $e->getMessage() . "\n";
}

// Test 7: Check for existing beneficiary documents
echo "\n7. Checking existing beneficiary documents...\n";
$beneficiaries = Beneficiary::whereNotNull('documents')->limit(3)->get();
if ($beneficiaries->count() > 0) {
    echo "   Found " . $beneficiaries->count() . " beneficiaries with documents:\n";
    foreach ($beneficiaries as $beneficiary) {
        echo "\n   Beneficiary ID: " . $beneficiary->id . "\n";
        $documents = $beneficiary->documents ?? [];
        if (is_string($documents)) {
            $documents = [$documents];
        }
        echo "   Document count: " . count($documents) . "\n";
        foreach ($documents as $i => $doc) {
            if (is_string($doc)) {
                $path = $doc;
                echo "     [$i] Path: $path\n";
                $exists = Storage::disk('public')->exists($path);
                echo "         Exists: " . ($exists ? "✓ YES" : "✗ NO") . "\n";
                if ($exists) {
                    try {
                        $url = Storage::disk('public')->url($path);
                        echo "         URL: $url\n";
                    } catch (\Exception $e) {
                        echo "         URL Generation Failed: " . $e->getMessage() . "\n";
                    }
                }
            } elseif (is_array($doc)) {
                echo "     [$i] Array: " . json_encode($doc) . "\n";
            }
        }
    }
} else {
    echo "   No beneficiaries with documents found yet\n";
}

// Clean up test file
echo "\n8. Cleaning up test files...\n";
if (Storage::disk('public')->exists($testFile)) {
    Storage::disk('public')->delete($testFile);
    echo "   ✓ Test file deleted\n";
}
if (Storage::disk('public')->exists($testDir)) {
    Storage::disk('public')->deleteDirectory($testDir);
    echo "   ✓ Test directory deleted\n";
}

echo "\n=== Test Complete ===\n";
