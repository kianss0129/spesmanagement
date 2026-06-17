<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (! Schema::hasColumn('beneficiaries', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('beneficiaries', 'suffix')) {
                $table->string('suffix')->nullable()->after('last_name');
            }
            if (! Schema::hasColumn('beneficiaries', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('suffix');
            }
            if (! Schema::hasColumn('beneficiaries', 'age')) {
                $table->unsignedInteger('age')->nullable()->after('birth_date');
            }
            if (! Schema::hasColumn('beneficiaries', 'sex')) {
                $table->string('sex')->nullable()->after('age');
            }
            if (! Schema::hasColumn('beneficiaries', 'civil_status')) {
                $table->string('civil_status')->nullable()->after('sex');
            }
            if (! Schema::hasColumn('beneficiaries', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable()->after('civil_status');
            }
            if (! Schema::hasColumn('beneficiaries', 'citizenship')) {
                $table->string('citizenship')->nullable()->after('place_of_birth');
            }
            if (! Schema::hasColumn('beneficiaries', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('beneficiaries', 'facebook_account')) {
                $table->string('facebook_account')->nullable()->after('contact_number');
            }
            if (! Schema::hasColumn('beneficiaries', 'category')) {
                $table->string('category')->nullable()->after('facebook_account');
            }
            if (! Schema::hasColumn('beneficiaries', 'present_address')) {
                $table->text('present_address')->nullable()->after('category');
            }
            if (! Schema::hasColumn('beneficiaries', 'barangay')) {
                $table->string('barangay')->nullable()->after('present_address');
            }
            if (! Schema::hasColumn('beneficiaries', 'city')) {
                $table->string('city')->nullable()->after('barangay');
            }
            if (! Schema::hasColumn('beneficiaries', 'province')) {
                $table->string('province')->nullable()->after('city');
            }
            if (! Schema::hasColumn('beneficiaries', 'father_name')) {
                $table->string('father_name')->nullable()->after('province');
            }
            if (! Schema::hasColumn('beneficiaries', 'father_contact')) {
                $table->string('father_contact')->nullable()->after('father_name');
            }
            if (! Schema::hasColumn('beneficiaries', 'father_occupation')) {
                $table->string('father_occupation')->nullable()->after('father_contact');
            }
            if (! Schema::hasColumn('beneficiaries', 'mother_name')) {
                $table->string('mother_name')->nullable()->after('father_occupation');
            }
            if (! Schema::hasColumn('beneficiaries', 'mother_contact')) {
                $table->string('mother_contact')->nullable()->after('mother_name');
            }
            if (! Schema::hasColumn('beneficiaries', 'mother_occupation')) {
                $table->string('mother_occupation')->nullable()->after('mother_contact');
            }
            if (! Schema::hasColumn('beneficiaries', 'family_income')) {
                $table->string('family_income')->nullable()->after('mother_occupation');
            }
            if (! Schema::hasColumn('beneficiaries', 'school_name')) {
                $table->string('school_name')->nullable()->after('family_income');
            }
            if (! Schema::hasColumn('beneficiaries', 'school_address')) {
                $table->text('school_address')->nullable()->after('school_name');
            }
            if (! Schema::hasColumn('beneficiaries', 'education_level')) {
                $table->string('education_level')->nullable()->after('school_address');
            }
            if (! Schema::hasColumn('beneficiaries', 'school_year')) {
                $table->string('school_year')->nullable()->after('education_level');
            }
            if (! Schema::hasColumn('beneficiaries', 'course')) {
                $table->string('course')->nullable()->after('school_year');
            }
            if (! Schema::hasColumn('beneficiaries', 'last_school_attended')) {
                $table->string('last_school_attended')->nullable()->after('course');
            }
            if (! Schema::hasColumn('beneficiaries', 'highest_attainment')) {
                $table->string('highest_attainment')->nullable()->after('last_school_attended');
            }
            if (! Schema::hasColumn('beneficiaries', 'year_last_attended')) {
                $table->string('year_last_attended')->nullable()->after('highest_attainment');
            }
            if (! Schema::hasColumn('beneficiaries', 'parent_guardian_name')) {
                $table->string('parent_guardian_name')->nullable()->after('year_last_attended');
            }
            if (! Schema::hasColumn('beneficiaries', 'relationship')) {
                $table->string('relationship')->nullable()->after('parent_guardian_name');
            }
            if (! Schema::hasColumn('beneficiaries', 'displacement_reason')) {
                $table->text('displacement_reason')->nullable()->after('relationship');
            }
            if (! Schema::hasColumn('beneficiaries', 'former_employer')) {
                $table->string('former_employer')->nullable()->after('displacement_reason');
            }
            if (! Schema::hasColumn('beneficiaries', 'displacement_date')) {
                $table->date('displacement_date')->nullable()->after('former_employer');
            }
            if (! Schema::hasColumn('beneficiaries', 'previous_spes')) {
                $table->boolean('previous_spes')->nullable()->after('displacement_date');
            }
            if (! Schema::hasColumn('beneficiaries', 'spes_count')) {
                $table->unsignedInteger('spes_count')->nullable()->after('previous_spes');
            }
            if (! Schema::hasColumn('beneficiaries', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('spes_count');
            }
            if (! Schema::hasColumn('beneficiaries', 'onboarding_step')) {
                $table->unsignedInteger('onboarding_step')->default(1)->after('submitted_at');
            }
            if (! Schema::hasColumn('beneficiaries', 'completion_percentage')) {
                $table->unsignedInteger('completion_percentage')->default(0)->after('onboarding_step');
            }
        });
    }


    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $columns = [
                'middle_name',
                'suffix',
                'birth_date',
                'age',
                'sex',
                'civil_status',
                'place_of_birth',
                'citizenship',
                'contact_number',
                'facebook_account',
                'category',
                'present_address',
                'barangay',
                'city',
                'province',
                'father_name',
                'father_contact',
                'father_occupation',
                'mother_name',
                'mother_contact',
                'mother_occupation',
                'family_income',
                'school_name',
                'school_address',
                'education_level',
                'school_year',
                'course',
                'last_school_attended',
                'highest_attainment',
                'year_last_attended',
                'parent_guardian_name',
                'relationship',
                'displacement_reason',
                'former_employer',
                'displacement_date',
                'previous_spes',
                'spes_count',
                'submitted_at',
                'onboarding_step',
                'completion_percentage',
            ];


            foreach ($columns as $column) {
                if (Schema::hasColumn('beneficiaries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};