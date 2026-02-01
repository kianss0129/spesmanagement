<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Beneficiary;
use App\Models\School;
use Illuminate\Support\Facades\Storage;

class BackfillBeneficiaries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'beneficiaries:backfill';

    /**
     * The console command description.
     */
    protected $description = 'Backfill phone, school, and documents for existing beneficiaries to display correctly in PESO';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Starting backfill of beneficiaries...");

        $beneficiaries = Beneficiary::all();

        foreach ($beneficiaries as $b) {

            $updated = false;

            // --- Phone ---
            if (empty($b->phone)) {
                $this->warn("Beneficiary {$b->id} has empty phone.");
                // Optionally prompt or skip. Currently leaving as-is
            }

            // --- School ---
            if ($b->school_id && ! $b->school) {
                $this->warn("Beneficiary {$b->id} has school_id {$b->school_id} but school relation missing.");
                // If school record exists, attach it
                $school = School::find($b->school_id);
                if ($school) {
                    $b->school()->associate($school);
                    $updated = true;
                }
            }

            // --- Documents ---
            if (!is_array($b->documents)) {
                $docs = [];
                if (!empty($b->documents)) {
                    // If stored as JSON string, decode
                    $decoded = json_decode($b->documents, true);
                    if (is_array($decoded)) {
                        $docs = $decoded;
                    }
                }

                // Scan storage folder for this user
                $userId = $b->id; // assuming documents stored by beneficiary ID
                $folder = "public/documents/users/{$userId}";

                if (Storage::exists($folder)) {
                    $files = Storage::files($folder);
                    foreach ($files as $file) {
                        $filename = basename($file);
                        if (!in_array($file, $docs)) {
                            $docs[] = $file;
                        }
                    }
                }

                if (!empty($docs)) {
                    $b->documents = $docs;
                    $updated = true;
                    $this->info("Updated documents for beneficiary {$b->id}");
                }
            }

            if ($updated) {
                $b->save();
                $this->info("Beneficiary {$b->id} backfilled successfully.");
            }
        }

        $this->info("Backfill completed.");
        return 0;
    }
}
