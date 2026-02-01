<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Employer;

class BackfillEmployerDocuments extends Command
{
    protected $signature = 'backfill:employer-docs';
    protected $description = 'Move existing employer documents into public storage and update JSON paths';

    public function handle()
    {
        $this->info('Starting backfill for employer documents...');

        Employer::all()->each(function ($employer) {
            $docs = $employer->documents ?? [];

            if (empty($docs)) {
                $this->info("Employer #{$employer->id} has no documents to backfill.");
                return;
            }

            $updatedDocs = [];

            foreach ($docs as $entry) {
                // Skip malformed or non‑array entries
                if (!is_array($entry) || empty($entry['path'])) {
                    continue;
                }

                $oldPath = $entry['path'];

                // If already on public disk, keep it
                if (Storage::disk('public')->exists($oldPath)) {
                    $updatedDocs[] = $entry;
                    continue;
                }

                // Check if it exists in default disk (storage/app)
                $privateFullPath = storage_path('app/' . $oldPath);
                if (file_exists($privateFullPath)) {
                    // Build new public path
                    $newPath = "documents/employers/{$employer->id}/" . basename($oldPath);

                    // Copy to public disk
                    Storage::disk('public')->put(
                        $newPath,
                        file_get_contents($privateFullPath)
                    );

                    $this->info("Moved employer #{$employer->id} doc: {$newPath}");

                    // Update entry path
                    $entry['path'] = $newPath;
                    $updatedDocs[] = $entry;
                    continue;
                }

                // File not found anywhere
                $this->warn("File for employer #{$employer->id} not found: {$oldPath}");
            }

            $employer->documents = $updatedDocs;
            $employer->save();
        });

        $this->info('Employer document backfill completed.');
        return 0;
    }
}
