<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Beneficiary;
use App\Models\Employer;

class BackfillOnboardingDocuments extends Command
{
    protected $signature = 'backfill:onboarding-docs';
    protected $description = 'Move old documents into public storage and update document JSON for beneficiaries & employers';

    public function handle()
    {
        $this->info("Starting onboarding document backfill...");

        // Backfill beneficiaries
        $this->backfillBeneficiaries();

        // Backfill employers
        $this->backfillEmployers();

        $this->info("Backfill completed.");
        return 0;
    }

    private function backfillBeneficiaries()
    {
        $this->info("Backfilling beneficiaries...");

        Beneficiary::all()->each(function ($beneficiary) {
            $docs = $beneficiary->documents ?? [];

            if (empty($docs)) {
                $this->info("  #{$beneficiary->id} has no documents.");
                return;
            }

            $updatedDocs = [];

            foreach ($docs as $entry) {
                if (!is_array($entry) || empty($entry['path'])) {
                    continue;
                }

                $oldPath = $entry['path'];

                // Check if file already exists under public disk
                if (Storage::disk('public')->exists($oldPath)) {
                    // File already in correct location
                    $updatedDocs[] = $entry;
                    continue;
                }

                // If file exists in storage/app (private)
                $privatePath = storage_path("app/{$oldPath}");
                if (file_exists($privatePath)) {
                    // Move file to public disk
                    $newPath = "documents/users/{$beneficiary->id}/" . basename($oldPath);
                    Storage::disk('public')->put($newPath, file_get_contents($privatePath));
                    $this->info("  Moved beneficiary #{$beneficiary->id} doc: {$newPath}");

                    // Update entry
                    $entry['path'] = $newPath;
                    $updatedDocs[] = $entry;

                    continue;
                }

                // File not found anywhere
                $this->warn("  Beneficiary #{$beneficiary->id} missing file: {$oldPath}");
            }

            $beneficiary->documents = $updatedDocs;
            $beneficiary->save();
        });
    }

    private function backfillEmployers()
    {
        $this->info("Backfilling employers...");

        Employer::all()->each(function ($employer) {
            $docs = $employer->documents ?? [];

            if (empty($docs)) {
                $this->info("  #{$employer->id} has no documents.");
                return;
            }

            $updatedDocs = [];

            foreach ($docs as $entry) {
                if (!is_array($entry) || empty($entry['path'])) {
                    continue;
                }

                $oldPath = $entry['path'];

                if (Storage::disk('public')->exists($oldPath)) {
                    $updatedDocs[] = $entry;
                    continue;
                }

                $privatePath = storage_path("app/{$oldPath}");
                if (file_exists($privatePath)) {
                    $newPath = "documents/employers/{$employer->id}/" . basename($oldPath);
                    Storage::disk('public')->put($newPath, file_get_contents($privatePath));
                    $this->info("  Moved employer #{$employer->id} doc: {$newPath}");

                    $entry['path'] = $newPath;
                    $updatedDocs[] = $entry;
                    continue;
                }

                $this->warn("  Employer #{$employer->id} missing file: {$oldPath}");
            }

            $employer->documents = $updatedDocs;
            $employer->save();
        });
    }
}
