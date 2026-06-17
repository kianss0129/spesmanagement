
<template>
    <div class="min-h-screen bg-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl bg-white shadow-xl">
                <div class="border-b border-slate-200 bg-slate-900 px-6 py-6 text-white sm:px-8">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.3em] text-sky-200">SPES Online Application</p>
                            <h1 class="mt-2 text-3xl font-semibold">City Government of San Fernando, Pampanga</h1>
                            <p class="mt-2 max-w-3xl text-sm text-slate-200">
                                Complete the five-step application to express interest in the Special Program for the Employment of Students.
                            </p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur-sm">
                            <p class="text-xs uppercase tracking-[0.2em] text-sky-100">Current progress</p>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-2 w-40 overflow-hidden rounded-full bg-white/20">
                                    <div class="h-full rounded-full bg-emerald-400" :style="{ width: `${stepProgress}%` }"></div>
                                </div>
                                <span class="text-sm font-semibold">{{ stepProgress }}%</span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="grid gap-6 px-6 py-6 lg:grid-cols-[280px,1fr] lg:px-8">
                    <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-semibold text-slate-800">Application steps</p>
                        <div class="mt-4 space-y-3">
                            <button
                                v-for="step in steps"
                                :key="step.number"
                                type="button"
                                class="w-full rounded-xl border px-4 py-3 text-left transition"
                                :class="getStepClasses(step.number)"
                                @click="setActiveStep(step.number)"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold"
                                        :class="getStepBadgeClasses(step.number)">
                                        <span v-if="isStepCompleted(step.number)">✓</span>
                                        <span v-else>{{ step.number }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold" :class="isStepActive(step.number) ? 'text-slate-900' : 'text-slate-700'">{{ step.title }}</p>
                                        <p class="text-xs text-slate-500">{{ step.description }}</p>
                                    </div>
                                </div>
                                <p v-if="isStepActive(step.number)" class="mt-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-sky-700">
                                    Current
                                </p>
                            </button>
                        </div>
                    </aside>


                    <section class="space-y-5">
                        <div v-if="submitMessage" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ submitMessage }}
                        </div>


                        <div v-if="Object.keys(validationErrors).length" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <p class="font-semibold">Please review the highlighted fields.</p>
                            <ul class="mt-2 list-disc pl-5">
                                <li v-for="(message, field) in validationErrors" :key="field">{{ Array.isArray(message) ? message[0] : message }}</li>
                            </ul>
                        </div>


                        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm text-slate-500">Step {{ activeStep }} of {{ steps.length }}</p>
                                    <h2 class="mt-1 text-2xl font-semibold text-slate-900">{{ activeStepDetails.title }}</h2>
                                    <p class="mt-2 text-sm text-slate-600">{{ activeStepDetails.description }}</p>
                                </div>
                                <div class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                                    {{ activeStepDetails.title }}
                                </div>
                            </div>


                            <div class="mt-6 space-y-5">
                                <div v-if="activeStep === 1">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <!-- USERNAME -->
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">
                                                Username
                                            </label>
                                            <input
                                                v-model="form.username"
                                                type="text"
                                                readonly
                                                class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2.5"/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">First Name <span class="text-rose-500">*</span></label>
                                            <input v-model="form.first_name" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Middle Name</label>
                                            <input v-model="form.middle_name" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Last Name <span class="text-rose-500">*</span></label>
                                            <input v-model="form.last_name" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Suffix <span class="text-slate-400">(optional)</span></label>
                                            <input v-model="form.suffix" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Birth Date <span class="text-rose-500">*</span></label>
                                            <input v-model="form.birth_date" type="date" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Age <span class="text-rose-500">*</span></label>
                                            <input v-model="form.age" type="number" min="1" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" @change="validateAge" />
                                            <p v-if="ageValidationError" class="mt-2 text-sm text-rose-600">{{ ageValidationError }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Sex <span class="text-rose-500">*</span></label>
                                            <select v-model="form.sex" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5">
                                                <option value="">Select sex</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Civil Status <span class="text-rose-500">*</span></label>
                                            <select v-model="form.civil_status" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5">
                                                <option value="">Select civil status</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Widowed">Widowed</option>
                                                <option value="Separated">Separated</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Place of Birth <span class="text-rose-500">*</span></label>
                                            <input v-model="form.place_of_birth" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Citizenship <span class="text-rose-500">*</span></label>
                                            <input v-model="form.citizenship" type="text" readonly class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Contact Number <span class="text-rose-500">*</span></label>
                                            <div class="relative mt-1">
                                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-semibold text-slate-500">+63</div>
                                                <input
                                                    :value="getPhoneDisplay('contact_number')"
                                                    @input="handlePhoneInput('contact_number', $event)"
                                                    @keydown="handlePhoneKeydown"
                                                    @paste="handlePhonePaste('contact_number', $event)"
                                                    type="tel"
                                                    inputmode="numeric"
                                                    maxlength="10"
                                                    placeholder="9123456789"
                                                    class="w-full rounded-xl border border-slate-300 py-2.5 pl-12 pr-3"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Email Address <span class="text-rose-500">*</span></label>
                                            <input v-model="form.email" type="email" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Facebook Account <span class="text-slate-400">(optional)</span></label>
                                            <input v-model="form.facebook_account" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Beneficiary Category</label>
                                            <input
                                                :value="categoryLabel"
                                                type="text"
                                                readonly
                                                class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-700 font-medium cursor-not-allowed"
                                            />
                                        </div>
                                    </div>

                                    <!-- Skills and Competencies -->
                                    <div class="mt-6">
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                            <h3 class="text-sm font-semibold text-slate-900">Skills and Competencies</h3>
                                            <p class="mt-2 text-sm text-slate-600">Select a job position to view the job description.</p>
                                            
                                            <!-- Job Selection Dropdown -->
                                            <div class="mt-4">
                                                <label class="block text-sm font-medium text-slate-700 mb-2">Job Position <span class="text-rose-500">*</span></label>
                                                <select 
                                                    @change="(e) => updateJobSkills(parseInt(e.target.value) || null)"
                                                    :disabled="jobsLoading"
                                                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 disabled:opacity-50">
                                                    <option value="">Select a job position</option>
                                                    <option v-for="job in availableJobs" :key="job.id" :value="job.id">
                                                        {{ job.title }}
                                                    </option>
                                                </select>
                                            </div>
                                            
                                            <!-- Job Description with Checkbox -->
                                            <div v-if="!selectedJobId" class="mt-4 text-center text-sm text-slate-600 py-6">
                                                Select a job position to view the description
                                            </div>
                                            
                                            <div v-else-if="selectedJobDescription" class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                                                <div>
                                                    <p class="text-xs font-semibold text-blue-700 uppercase tracking-[0.1em]">Job Description</p>
                                                    <p class="mt-2 text-sm text-slate-700 leading-relaxed">{{ selectedJobDescription }}</p>
                                                </div>
                                            </div>

                                            <!-- Skills Grid -->
                                            <div v-if="selectedJobId && selectedJobSkills.length > 0" class="mt-6">
                                                <h4 class="text-sm font-semibold text-slate-900 mb-4">Required Skills & Competencies</h4>
                                                <div class="grid gap-3 md:grid-cols-2">
                                                    <label v-for="skill in selectedJobSkills" :key="skill.id" class="flex items-start gap-3 p-4 rounded-lg border border-slate-200 hover:border-sky-300 hover:bg-sky-50 cursor-pointer transition">
                                                        <input type="checkbox" 
                                                            v-model="form.skills"
                                                            :value="skill.id"
                                                            @change="saveDraft()"
                                                            class="mt-1 h-4 w-4 rounded border-slate-300 text-sky-600 flex-shrink-0" />
                                                        <div class="flex-1">
                                                            <p class="font-medium text-slate-900">{{ skill.name }}</p>
                                                            <p class="text-xs text-slate-600 mt-1">{{ skill.category || skill.skill_category?.name || 'Skill' }}</p>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div v-else-if="selectedJobId" class="mt-6 rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
                                                No required skills were posted for this job.
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div v-if="activeStep === 2">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Present Address <span class="text-rose-500">*</span></label>
                                            <input v-model="form.present_address" type="text" placeholder="House/Lot/Street" class="mt-1 w-full rounded-xl border px-3 py-2.5" :class="validationErrors.present_address ? 'border-rose-500 bg-rose-50' : 'border-slate-300'" />
                                            <p v-if="validationErrors.present_address" class="mt-1 text-xs text-rose-600">{{ validationErrors.present_address }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Barangay <span class="text-rose-500">*</span></label>
                                            <select v-model="form.barangay" class="mt-1 w-full rounded-xl border px-3 py-2.5" :class="validationErrors.barangay ? 'border-rose-500 bg-rose-50' : 'border-slate-300'">
                                                <option value="">Select Barangay</option>
                                                <option v-for="brgy in barangayList" :key="brgy" :value="brgy">{{ brgy }}</option>
                                            </select>
                                            <p v-if="validationErrors.barangay" class="mt-1 text-xs text-rose-600">{{ validationErrors.barangay }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">City <span class="text-rose-500">*</span></label>
                                            <input value="San Fernando" type="text" readonly class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2.5 text-slate-700 font-medium cursor-not-allowed" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Province <span class="text-rose-500">*</span></label>
                                            <input value="Pampanga" type="text" readonly class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2.5 text-slate-700 font-medium cursor-not-allowed" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Father's Name</label>
                                            <input v-model="form.father_name" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Father's Contact</label>
                                            <div class="relative mt-1">
                                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-semibold text-slate-500">+63</div>
                                                <input
                                                    :value="getPhoneDisplay('father_contact')"
                                                    @input="handlePhoneInput('father_contact', $event)"
                                                    @keydown="handlePhoneKeydown"
                                                    @paste="handlePhonePaste('father_contact', $event)"
                                                    type="tel"
                                                    inputmode="numeric"
                                                    maxlength="10"
                                                    placeholder="9123456789"
                                                    class="w-full rounded-xl border border-slate-300 py-2.5 pl-12 pr-3"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Father's Occupation</label>
                                            <input v-model="form.father_occupation" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Mother's Name</label>
                                            <input v-model="form.mother_name" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Mother's Contact</label>
                                            <div class="relative mt-1">
                                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-semibold text-slate-500">+63</div>
                                                <input
                                                    :value="getPhoneDisplay('mother_contact')"
                                                    @input="handlePhoneInput('mother_contact', $event)"
                                                    @keydown="handlePhoneKeydown"
                                                    @paste="handlePhonePaste('mother_contact', $event)"
                                                    type="tel"
                                                    inputmode="numeric"
                                                    maxlength="10"
                                                    placeholder="9123456789"
                                                    class="w-full rounded-xl border border-slate-300 py-2.5 pl-12 pr-3"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Mother's Occupation</label>
                                            <input v-model="form.mother_occupation" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-slate-700">Family Income <span class="text-rose-500">*</span></label>
                                            <select v-model="form.family_income" class="mt-1 w-full rounded-xl border px-3 py-2.5" :class="validationErrors.family_income ? 'border-rose-500 bg-rose-50' : 'border-slate-300'">
                                                <option value="">Select family income</option>
                                                <option value="Below ₱5,000">Below ₱5,000</option>
                                                <option value="₱5,001–₱10,000">₱5,001–₱10,000</option>
                                                <option value="₱10,001–₱15,000">₱10,001–₱15,000</option>
                                                <option value="Above ₱15,000">Above ₱15,000</option>
                                            </select>
                                            <p v-if="validationErrors.family_income" class="mt-1 text-xs text-rose-600">{{ validationErrors.family_income }}</p>
                                        </div>
                                    </div>
                                </div>


                                <div v-if="activeStep === 3">
                                    <div v-if="form.category === 'student'" class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">School Name <span class="text-rose-500">*</span></label>
                                            <input v-model="form.school_name" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">School Address <span class="text-rose-500">*</span></label>
                                            <input v-model="form.school_address" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Education Level <span class="text-rose-500">*</span></label>
                                            <select v-model="form.education_level" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5">
                                                <option value="">Select level</option>
                                                <option value="Junior High School">Junior High School</option>
                                                <option value="Senior High School">Senior High School</option>
                                                <option value="College">College</option>
                                                <option value="Vocational">Vocational</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">School Year <span class="text-rose-500">*</span></label>
                                            <select v-model="form.school_year" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5">
                                                <option value="">Select school year</option>
                                                <option value="2024-2025">2024-2025</option>
                                                <option value="2025-2026">2025-2026</option>
                                                <option value="2026-2027">2026-2027</option>
                                                <option value="2027-2028">2027-2028</option>
                                                <option value="2028-2029">2028-2029</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Year Level <span class="text-rose-500">*</span></label>
                                            <select v-model="form.year_level" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5">
                                                <option value="">Select year level</option>
                                                <option value="Grade 11">Grade 11</option>
                                                <option value="Grade 12">Grade 12</option>
                                                <option value="1st Year">1st Year</option>
                                                <option value="2nd Year">2nd Year</option>
                                                <option value="3rd Year">3rd Year</option>
                                                <option value="4th Year">4th Year</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Course / Strand</label>
                                            <input v-model="form.course" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                    </div>


                                    <div v-if="form.category === 'osy'" class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Last School Attended <span class="text-rose-500">*</span></label>
                                            <input v-model="form.last_school_attended" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Highest Attainment <span class="text-rose-500">*</span></label>
                                            <input v-model="form.highest_attainment" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Year Last Attended <span class="text-rose-500">*</span></label>
                                            <input v-model="form.year_last_attended" type="text" placeholder="e.g. 2022" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                    </div>


                                    <div v-if="form.category === 'dependent'" class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Parent / Guardian Name <span class="text-rose-500">*</span></label>
                                            <input v-model="form.parent_guardian_name" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Relationship <span class="text-rose-500">*</span></label>
                                            <input v-model="form.relationship" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-slate-700">Reason for Displacement <span class="text-rose-500">*</span></label>
                                            <input v-model="form.displacement_reason" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Former Employer / Company</label>
                                            <input v-model="form.former_employer" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Date of Job Displacement</label>
                                            <input v-model="form.displacement_date" type="date" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5" />
                                        </div>
                                    </div>
                                </div>


                                <div v-if="activeStep === 4">
                                    <div class="grid gap-4 lg:grid-cols-[1fr,auto]">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Upload supporting documents <span class="text-rose-500">*</span></label>
                                            <p class="mt-1 text-sm text-slate-500">Upload clear images or PDFs. You can zoom and adjust document images before saving. Accepted formats: PDF, JPG, PNG. Max size: 5MB per image, 10MB for PDF.</p>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 p-4">
                                            <p class="text-sm font-semibold text-slate-800">Need help?</p>
                                            <ul class="mt-2 space-y-2 text-sm text-slate-600">
                                                <li>• Clear photo of your valid ID</li>
                                                <li>• School enrollment or proof of study</li>
                                                <li>• Barangay or household documents if applicable</li>
                                            </ul>
                                        </div>
                                    </div>


                                    <div class="mt-4 space-y-4">
                                        <div v-for="document in requiredDocuments" :key="document.key" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                            <p class="text-sm font-semibold text-slate-800 mb-3">{{ document.label }}</p>
                                            
                                            <!-- Show previously uploaded file if it exists -->
                                            <div v-if="existingDocuments[document.key] && !documentSelections[document.key]" class="mb-3 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">✓</span>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-emerald-800">Previously uploaded: {{ existingDocuments[document.key].name }}</p>
                                                    <p class="text-xs text-emerald-600">Upload a new file below only if you want to replace it.</p>
                                                </div>
                                            </div>

                                            <ImageCropUpload
                                                :label="document.label"
                                                :help="existingDocuments[document.key] ? 'Upload a new file to replace the existing one, or leave as-is.' : document.help"
                                                @update:file="(file) => handleDocumentUpload(document.key, file)"
                                            />
                                            <p v-if="documentSelectionErrors[document.key]" class="mt-2 text-sm text-rose-600">
                                                {{ documentSelectionErrors[document.key] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>


                                <div v-if="activeStep === 5">
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 space-y-5">
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-900">Review your application</h3>
                                            <p class="mt-1 text-sm text-slate-600">Make sure all your answers are correct before submitting. You can go back to any step to edit details.</p>
                                        </div>

                                        <!-- APPLICANT -->
                                        <div class="rounded-xl bg-white border border-slate-200 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 mb-3">Applicant Information</p>
                                            <div class="grid gap-2 sm:grid-cols-2 text-sm">
                                                <div><span class="text-slate-500">Full Name:</span> <span class="font-medium text-slate-900">{{ form.first_name }} {{ form.middle_name }} {{ form.last_name }} {{ form.suffix }}</span></div>
                                                <div><span class="text-slate-500">Email:</span> <span class="font-medium text-slate-900">{{ form.email }}</span></div>
                                                <div><span class="text-slate-500">Contact:</span> <span class="font-medium text-slate-900">{{ form.contact_number || 'Not provided' }}</span></div>
                                                <div><span class="text-slate-500">Birthdate:</span> <span class="font-medium text-slate-900">{{ form.birth_date || 'Not provided' }}</span></div>
                                                <div><span class="text-slate-500">Age:</span> <span class="font-medium text-slate-900">{{ form.age || 'N/A' }}</span></div>
                                                <div><span class="text-slate-500">Sex:</span> <span class="font-medium text-slate-900">{{ form.sex || 'Not selected' }}</span></div>
                                                <div><span class="text-slate-500">Civil Status:</span> <span class="font-medium text-slate-900">{{ form.civil_status || 'Not selected' }}</span></div>
                                                <div><span class="text-slate-500">Category:</span> <span class="font-medium text-slate-900">{{ categoryLabel }}</span></div>
                                            </div>
                                        </div>

                                        <!-- ADDRESS -->
                                        <div class="rounded-xl bg-white border border-slate-200 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 mb-3">Address</p>
                                            <div class="grid gap-2 sm:grid-cols-2 text-sm">
                                                <div class="sm:col-span-2"><span class="text-slate-500">Present Address:</span> <span class="font-medium text-slate-900">{{ form.present_address || 'Not provided' }}</span></div>
                                                <div><span class="text-slate-500">Barangay:</span> <span class="font-medium text-slate-900">{{ form.barangay || 'Not selected' }}</span></div>
                                                <div><span class="text-slate-500">City:</span> <span class="font-medium text-slate-900">{{ form.city }}</span></div>
                                                <div><span class="text-slate-500">Province:</span> <span class="font-medium text-slate-900">{{ form.province }}</span></div>
                                            </div>
                                        </div>

                                        <!-- FAMILY -->
                                        <div class="rounded-xl bg-white border border-slate-200 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 mb-3">Family Background</p>
                                            <div class="grid gap-2 sm:grid-cols-2 text-sm">
                                                <div><span class="text-slate-500">Father:</span> <span class="font-medium text-slate-900">{{ form.father_name || 'N/A' }}</span></div>
                                                <div><span class="text-slate-500">Father Contact:</span> <span class="font-medium text-slate-900">{{ form.father_contact || 'N/A' }}</span></div>
                                                <div><span class="text-slate-500">Father Occupation:</span> <span class="font-medium text-slate-900">{{ form.father_occupation || 'N/A' }}</span></div>
                                                <div><span class="text-slate-500">Mother:</span> <span class="font-medium text-slate-900">{{ form.mother_name || 'N/A' }}</span></div>
                                                <div><span class="text-slate-500">Mother Contact:</span> <span class="font-medium text-slate-900">{{ form.mother_contact || 'N/A' }}</span></div>
                                                <div><span class="text-slate-500">Mother Occupation:</span> <span class="font-medium text-slate-900">{{ form.mother_occupation || 'N/A' }}</span></div>
                                                <div class="sm:col-span-2"><span class="text-slate-500">Family Income:</span> <span class="font-medium text-slate-900">{{ form.family_income || 'Not selected' }}</span></div>
                                            </div>
                                        </div>

                                        <!-- EDUCATION (Student) -->
                                        <div v-if="form.category === 'student'" class="rounded-xl bg-white border border-slate-200 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 mb-3">Student Information</p>
                                            <div class="grid gap-2 sm:grid-cols-2 text-sm">
                                                <div class="sm:col-span-2"><span class="text-slate-500">School Name:</span> <span class="font-medium text-slate-900">{{ form.school_name || 'Not provided' }}</span></div>
                                                <div class="sm:col-span-2"><span class="text-slate-500">School Address:</span> <span class="font-medium text-slate-900">{{ form.school_address || 'Not provided' }}</span></div>
                                                <div><span class="text-slate-500">Education Level:</span> <span class="font-medium text-slate-900">{{ form.education_level || 'Not selected' }}</span></div>
                                                <div><span class="text-slate-500">School Year:</span> <span class="font-medium text-slate-900">{{ form.school_year || 'Not selected' }}</span></div>
                                                <div><span class="text-slate-500">Year Level:</span> <span class="font-medium text-slate-900">{{ form.year_level || 'Not selected' }}</span></div>
                                                <div><span class="text-slate-500">Course / Strand:</span> <span class="font-medium text-slate-900">{{ form.course || 'N/A' }}</span></div>
                                            </div>
                                        </div>

                                        <!-- EDUCATION (OSY) -->
                                        <div v-if="form.category === 'osy'" class="rounded-xl bg-white border border-slate-200 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 mb-3">OSY Information</p>
                                            <div class="grid gap-2 sm:grid-cols-2 text-sm">
                                                <div class="sm:col-span-2"><span class="text-slate-500">Last School Attended:</span> <span class="font-medium text-slate-900">{{ form.last_school_attended || 'Not provided' }}</span></div>
                                                <div><span class="text-slate-500">Highest Attainment:</span> <span class="font-medium text-slate-900">{{ form.highest_attainment || 'Not provided' }}</span></div>
                                                <div><span class="text-slate-500">Year Last Attended:</span> <span class="font-medium text-slate-900">{{ form.year_last_attended || 'Not provided' }}</span></div>
                                            </div>
                                        </div>

                                        <!-- EDUCATION (Dependent) -->
                                        <div v-if="form.category === 'dependent'" class="rounded-xl bg-white border border-slate-200 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 mb-3">Dependent of Displaced Worker</p>
                                            <div class="grid gap-2 sm:grid-cols-2 text-sm">
                                                <div><span class="text-slate-500">Parent/Guardian:</span> <span class="font-medium text-slate-900">{{ form.parent_guardian_name || 'Not provided' }}</span></div>
                                                <div><span class="text-slate-500">Relationship:</span> <span class="font-medium text-slate-900">{{ form.relationship || 'Not provided' }}</span></div>
                                                <div class="sm:col-span-2"><span class="text-slate-500">Displacement Reason:</span> <span class="font-medium text-slate-900">{{ form.displacement_reason || 'Not provided' }}</span></div>
                                                <div><span class="text-slate-500">Former Employer:</span> <span class="font-medium text-slate-900">{{ form.former_employer || 'N/A' }}</span></div>
                                                <div><span class="text-slate-500">Displacement Date:</span> <span class="font-medium text-slate-900">{{ form.displacement_date || 'N/A' }}</span></div>
                                            </div>
                                        </div>

                                        <!-- DOCUMENTS -->
                                        <div class="rounded-xl bg-white border border-slate-200 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 mb-3">Required Documents</p>
                                            <div class="grid gap-2 sm:grid-cols-2 text-sm">
                                                <div v-for="doc in requiredDocumentsForCategory" :key="doc.key" class="flex items-center gap-2">
                                                    <span
                                                        class="inline-flex h-5 w-5 items-center justify-center rounded-full text-xs"
                                                        :class="(documentSelections[doc.key] || existingDocuments[doc.key]) ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-500'"
                                                    >
                                                        {{ (documentSelections[doc.key] || existingDocuments[doc.key]) ? '✓' : '–' }}
                                                    </span>
                                                    <span :class="(documentSelections[doc.key] || existingDocuments[doc.key]) ? 'text-slate-900 font-medium' : 'text-slate-500'">
                                                        {{ doc.label }}
                                                    </span>
                                                    <span v-if="documentSelections[doc.key]" class="ml-auto text-xs text-emerald-600 font-medium">New upload</span>
                                                    <span v-else-if="existingDocuments[doc.key]" class="ml-auto text-xs text-sky-600 font-medium">Previously uploaded</span>
                                                    <span v-else class="ml-auto text-xs text-amber-600 font-medium">Not uploaded</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- DECLARATION -->
                                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
                                            <input v-model="form.declaration" type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300" />
                                            <div class="text-sm text-slate-700">
                                                <span>I have read and agree to the </span>
                                                <button type="button" @click="openTermsModal" class="text-sky-600 underline hover:text-sky-700 font-semibold">Terms and Conditions</button>
                                                <span> for SPES Beneficiary Onboarding. I declare that the information I provided is true and correct to the best of my knowledge.</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <!-- Terms & Conditions Modal -->
                            <div v-if="showTermsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
                                    <!-- Modal Header -->
                                    <div class="sticky top-0 border-b border-slate-200 bg-slate-900 px-6 py-4 text-white flex items-center justify-between">
                                        <h2 class="text-xl font-semibold">SPES Beneficiary Terms and Conditions</h2>
                                        <button @click="closeTermsModal" class="text-slate-300 hover:text-white">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>


                                    <!-- Modal Content -->
                                    <div class="max-h-96 overflow-y-auto px-6 py-5 text-sm text-slate-700 space-y-4">
                                        <section>
                                            <h3 class="font-semibold text-slate-900 mb-2">1. Program Eligibility</h3>
                                            <p>You must be between 15-24 years old and either a Student, Out-of-School Youth (OSY), or Dependent to be eligible for the SPES program. Participants over 24 years of age are not eligible.</p>
                                        </section>


                                        <section>
                                            <h3 class="font-semibold text-slate-900 mb-2">2. Document Requirements</h3>
                                            <p>You agree to submit accurate and genuine documents including a valid government-issued ID, proof of school enrollment or academic records, and other supporting documents as required. False or forged documents may result in disqualification and legal action.</p>
                                        </section>


                                        <section>
                                            <h3 class="font-semibold text-slate-900 mb-2">3. Data Privacy and Protection</h3>
                                            <p>Your personal information will be collected, processed, and stored in accordance with the Data Privacy Act of 2012. All data will be used solely for SPES program administration and will not be shared with third parties without your consent.</p>
                                        </section>


                                        <section>
                                            <h3 class="font-semibold text-slate-900 mb-2">4. Program Participation Requirements</h3>
                                            <p>As a SPES beneficiary, you agree to maintain regular attendance, comply with program rules and regulations, and communicate any changes in your employment or academic status to the PESO office.</p>
                                        </section>


                                        <section>
                                            <h3 class="font-semibold text-slate-900 mb-2">5. Information Accuracy</h3>
                                            <p>You certify that all information provided in this application is true, accurate, and complete to the best of your knowledge. Any misrepresentation or omission of material facts may result in disqualification from the program.</p>
                                        </section>


                                        <section>
                                            <h3 class="font-semibold text-slate-900 mb-2">6. Right to Disqualification</h3>
                                            <p>The PESO office reserves the right to disqualify applicants who do not meet the eligibility requirements, submit fraudulent documents, or violate program policies.</p>
                                        </section>


                                        <section>
                                            <h3 class="font-semibold text-slate-900 mb-2">7. Contact Information</h3>
                                            <p>For inquiries or concerns about the SPES program, please contact the PESO Office at City Government of San Fernando, Pampanga or visit our office during business hours.</p>
                                        </section>
                                    </div>


                                    <!-- Modal Footer -->
                                    <div class="sticky bottom-0 border-t border-slate-200 bg-slate-50 px-6 py-4 flex justify-end gap-3">
                                        <button @click="closeTermsModal" class="rounded-xl border border-slate-300 px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-100">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>


                            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-300 px-4 py-2.5 font-semibold text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="activeStep === 1"
                                    @click="previousStep"
                                >
                                    Previous
                                </button>


                                <div class="ml-auto flex gap-3">
                                    <button
                                        v-if="activeStep < steps.length"
                                        type="button"
                                        class="rounded-xl bg-sky-600 px-5 py-2.5 font-semibold text-black"
                                        @click="nextStep"
                                    >
                                        Next
                                    </button>
                                                                <button
                                    v-if="activeStep === steps.length"
                                    type="button"
                                    :disabled="submitting"
                                    @click="submitForm"
                                    class="rounded-xl bg-emerald-600 px-5 py-2.5 font-semibold text-white disabled:opacity-50 flex items-center gap-2"
                                >
                                    <svg
                                        v-if="submitting"
                                        class="w-4 h-4 animate-spin"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        />


                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8v8z"
                                        />
                                    </svg>


                                    {{ submitting ? 'Submitting...' : 'Submit Application' }}
                                </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>


<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import ImageCropUpload from '@/Components/ImageCropUpload.vue';
import 'vue3-toastify/dist/index.css';


const STORAGE_KEY = 'spes_onboarding_draft';
const page = usePage();


const steps = [
    { number: 1, title: 'Personal Information', description: 'Tell us about yourself and your basic contact details.' },
    { number: 2, title: 'Family and Background', description: 'Share your address and household background information.' },
    { number: 3, title: 'Education / Livelihood', description: 'Complete your school, work, or dependent details.' },
    { number: 4, title: 'Document Uploads', description: 'Attach the supporting files required for validation.' },
    { number: 5, title: 'Review and Submit', description: 'Confirm every detail and submit your SPES application.' },
];


const defaultForm = () => ({
    username: page.props.user?.name || '',
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    birth_date: '',
    age: '',
    sex: '',
    civil_status: '',
    place_of_birth: '',
    citizenship: 'Filipino',
    contact_number: '',
    email: page.props.user?.email || '',
    facebook_account: '',
    category: page.props.category || 'student',
    present_address: '',
    barangay: '',
    city: 'San Fernando',
    province: 'Pampanga',
    father_name: '',
    father_contact: '',
    father_occupation: '',
    mother_name: '',
    mother_contact: '',
    mother_occupation: '',
    family_income: '',
    school_name: '',
    school_address: '',
    education_level: '',
    school_year: '',
    year_level: '',
    course: '',
    last_school_attended: '',
    highest_attainment: '',
    year_last_attended: '',
    parent_guardian_name: '',
    relationship: '',
    displacement_reason: '',
    former_employer: '',
    displacement_date: '',
    previous_spes: 'No',
    spes_count: '',
    skills: [],
    declaration: false,
    documents: [],
});


const form = ref(defaultForm());
const activeStep = ref(1);
const submitting = ref(false);
const submitMessage = ref('');
const validationErrors = ref({});
const documentSelectionErrors = ref({});
const showTermsModal = ref(false);
const ageValidationError = ref('');
const allSkills = ref([]);
const skillsLoading = ref(false);
const availableJobs = ref([]);
const jobsLoading = ref(false);
const selectedJobId = ref(null);
const selectedJobSkills = ref([]);
const selectedJobDescription = ref('');

// Track previously uploaded documents from the backend (already saved in DB)
const existingDocuments = ref({});


const allowedDocumentExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
const allowedDocumentExtensionsString = allowedDocumentExtensions.map((extension) => `.${extension}`).join(',');
const videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'm4v'];


const studentDocuments = [
    {
        key: 'valid_id',
        label: 'Valid ID',
        help: 'Upload a clear government-issued ID or school identification card.',
    },
    {
        key: 'school_enrollment',
        label: 'School Enrollment / Proof of Study',
        help: 'Upload an enrollment form, registration slip, or proof of studies.',
    },
    {
        key: 'barangay_certificate',
        label: 'Barangay Certificate',
        help: 'Upload your latest barangay certificate, if applicable.',
    },
];

const osyDocuments = [
    {
        key: 'valid_id',
        label: 'Valid ID',
        help: 'Upload a clear government-issued ID or any valid identification card.',
    },
    {
        key: 'birth_certificate',
        label: 'Birth Certificate',
        help: 'Upload a certified copy of your birth certificate.',
    },
    {
        key: 'barangay_certificate',
        label: 'Barangay Certificate of Residency',
        help: 'Upload your barangay certificate of residency.',
    },
    {
        key: 'osy_certificate',
        label: 'Certificate of Out-of-School Youth Status',
        help: 'Upload your OSY certificate or proof of out-of-school status.',
    },
];

const dependentDocuments = [
    {
        key: 'birth_certificate',
        label: 'Birth Certificate',
        help: 'Upload a certified copy of your birth certificate.',
    },
    {
        key: 'income_proof',
        label: 'Proof of Family Income',
        help: 'Upload proof of family income (e.g., certificate of indigency, income certificate).',
    },
    {
        key: 'displacement_proof',
        label: 'Proof of Displacement',
        help: 'Upload documentation proving your parent/guardian\'s job displacement.',
    },
    {
        key: 'parent_valid_id',
        label: 'Parent / Guardian Valid ID',
        help: 'Upload a copy of your parent/guardian\'s valid ID.',
    },
];

const requiredDocuments = computed(() => {
    if (form.value.category === 'osy') {
        return osyDocuments;
    }
    if (form.value.category === 'dependent') {
        return dependentDocuments;
    }
    return studentDocuments;
});


const documentSelections = ref({
    // Student documents
    valid_id: null,
    school_enrollment: null,
    
    // OSY documents
    birth_certificate: null,
    osy_certificate: null,
    
    // Dependent documents
    income_proof: null,
    displacement_proof: null,
    parent_valid_id: null,
    
    // Common to multiple categories
    barangay_certificate: null,
});


const loadSkills = async () => {
    skillsLoading.value = true;
    try {
        const response = await fetch('/onboarding/skills');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        console.log('Skills data received:', data);
        
        // Handle both grouped object format and array format
        let skillsList = [];
        if (Array.isArray(data)) {
            skillsList = data;
        } else if (typeof data === 'object' && data !== null) {
            Object.values(data).forEach(item => {
                if (Array.isArray(item)) {
                    skillsList.push(...item);
                } else if (typeof item === 'object' && item.id) {
                    skillsList.push(item);
                }
            });
        }
        
        console.log('Processed skills list:', skillsList);
        allSkills.value = skillsList;
    } catch (error) {
        console.error('Error loading skills:', error);
        toast.error('Failed to load skills');
    } finally {
        skillsLoading.value = false;
    }
};


const loadAvailableJobs = async () => {
    jobsLoading.value = true;
    try {
        const response = await fetch('/onboarding/jobs');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        console.log('Jobs data received:', data);
        availableJobs.value = data.data || data || [];
    } catch (error) {
        console.error('Error loading jobs:', error);
        toast.error('Failed to load available jobs');
    } finally {
        jobsLoading.value = false;
    }
};


const updateJobSkills = async (jobId) => {
    if (!jobId) {
        selectedJobSkills.value = [];
        selectedJobDescription.value = '';
        form.value.skills = [];
        return;
    }

    selectedJobId.value = jobId;
    
    // Extract job details to get skills and description
    const selectedJob = availableJobs.value.find(j => j.id === jobId);
    if (selectedJob) {
        selectedJobSkills.value = Array.isArray(selectedJob.skills)
            ? selectedJob.skills.map((skill) => ({
                ...skill,
                category: skill.category || skill.skill_category?.name || skill.skillCategory?.name || 'Skill',
            }))
            : [];
        selectedJobDescription.value = selectedJob.description || '';
    } else {
        selectedJobSkills.value = [];
        selectedJobDescription.value = '';
    }
    
    // Clear previously selected skills when changing job
    form.value.skills = [];
    saveDraft();
};


const categoryLabel = computed(() => {
    return form.value.category === 'student' ? 'Student' : form.value.category === 'osy' ? 'Out-of-School Youth' : 'Dependent of Displaced Worker';
});

const barangayList = [
    'Alasas', 'Baliti', 'Bulaon', 'Calulut', 'Dela Paz Norte', 'Dela Paz Sur',
    'Del Carmen', 'Del Pilar', 'Del Rosario', 'Dolores', 'Juliana', 'Lara',
    'Lourdes', 'Magliman', 'Maimpis', 'Malino', 'Malpitic', 'Pandaras',
    'Panipuan', 'Pulung Bulu', 'Quebiawan', 'Saguin', 'San Agustin', 'San Felipe',
    'San Isidro', 'San Jose', 'San Juan', 'San Nicolas', 'San Pedro', 'Santa Lucia',
    'Santa Teresita', 'Santo Niño', 'Santo Rosario', 'Sindalan', 'Telabastagan',
];


const educationSummary = computed(() => {
    if (form.value.category === 'student') {
        return [form.value.school_name, form.value.education_level, form.value.school_year].filter(Boolean).join(' • ') || 'Student details pending';
    }


    if (form.value.category === 'osy') {
        return [form.value.last_school_attended, form.value.highest_attainment, form.value.year_last_attended].filter(Boolean).join(' • ') || 'OSY details pending';
    }


    return [form.value.parent_guardian_name, form.value.relationship].filter(Boolean).join(' • ') || 'Dependent details pending';
});


const selectedDocumentCount = computed(() => Object.values(documentSelections.value).filter(Boolean).length);

const requiredDocumentsForCategory = computed(() => {
    if (form.value.category === 'student') {
        return [
            { key: 'valid_id', label: 'Valid ID' },
            { key: 'school_enrollment', label: 'School Enrollment / Proof of Study' },
            { key: 'barangay_certificate', label: 'Barangay Certificate' },
        ];
    }

    if (form.value.category === 'osy') {
        return [
            { key: 'valid_id', label: 'Valid ID' },
            { key: 'birth_certificate', label: 'Birth Certificate' },
            { key: 'barangay_certificate', label: 'Barangay Certificate of Residency' },
            { key: 'osy_certificate', label: 'Certificate of Out-of-School Youth Status' },
        ];
    }

    if (form.value.category === 'dependent') {
        return [
            { key: 'birth_certificate', label: 'Birth Certificate' },
            { key: 'income_proof', label: 'Proof of Family Income' },
            { key: 'displacement_proof', label: 'Proof of Displacement' },
            { key: 'parent_valid_id', label: 'Parent / Guardian Valid ID' },
        ];
    }

    return [
        { key: 'valid_id', label: 'Valid ID' },
        { key: 'barangay_certificate', label: 'Barangay Certificate' },
    ];
});

const formatDocLabel = (key) => {
    return String(key || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

const stepProgress = computed(() => Math.round((activeStep.value / steps.length) * 100));
const activeStepDetails = computed(() => steps[activeStep.value - 1] || steps[0]);


const getPreferredCategory = () => page.props.category || page.props.user?.beneficiary_type || 'student';


const normalizePhoneValue = (value = '') => {
    const digits = String(value || '').replace(/\D/g, '').slice(0, 10);


    return digits ? `+63${digits}` : '';
};


const getPhoneDisplay = (field) => {
    const rawValue = String(form.value[field] || '');


    if (! rawValue) {
        return '';
    }


    return rawValue.startsWith('+63') ? rawValue.replace(/^\+63/, '') : rawValue.replace(/\D/g, '').slice(0, 10);
};


const handlePhoneInput = (field, event) => {
    form.value[field] = normalizePhoneValue(event.target.value);
    saveDraft();
};

const handlePhoneKeydown = (event) => {
    const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'];
    if (allowed.includes(event.key)) return;
    if (event.ctrlKey || event.metaKey) return;
    if (!/^[0-9]$/.test(event.key)) {
        event.preventDefault();
    }
};

const handlePhonePaste = (field, event) => {
    event.preventDefault();
    const pasted = (event.clipboardData || window.clipboardData).getData('text');
    const digits = pasted.replace(/\D/g, '').slice(0, 10);
    form.value[field] = normalizePhoneValue(digits);
};

const getLocalPhoneDigits = (value = '') => {
    return String(value || '').replace(/^\+63/, '').replace(/\D/g, '').slice(0, 10);
};


const syncDocumentsFromSelections = () => {
    form.value.documents = Object.values(documentSelections.value).filter((file) => file instanceof File);
};


const validateDocumentFile = (file) => {
    if (!file) return null;
   
    const fileExtension = file.name.split('.').pop().toLowerCase();


    if (videoExtensions.includes(fileExtension)) {
        return `Video files (${file.name}) are not allowed.`;
    }


    if (! allowedDocumentExtensions.includes(fileExtension)) {
        return `Only PDF, JPG, JPEG, and PNG files are allowed.`;
    }


    const maxSize = fileExtension === 'pdf' ? 10 * 1024 * 1024 : 5 * 1024 * 1024;
    if (file.size > maxSize) {
        return `${file.name} exceeds the ${fileExtension === 'pdf' ? '10MB' : '5MB'} size limit.`;
    }


    return null;
};


const handleDocumentUpload = (key, file) => {
    documentSelectionErrors.value[key] = '';


    if (! file) {
        documentSelections.value[key] = null;
        syncDocumentsFromSelections();
        saveDraft();
        return;
    }


    const error = validateDocumentFile(file);


    if (error) {
        documentSelectionErrors.value[key] = error;
        validationErrors.value.documents = error;
        documentSelections.value[key] = null;
        syncDocumentsFromSelections();
        saveDraft();
        return;
    }


    documentSelections.value[key] = file;
    validationErrors.value.documents = '';
    syncDocumentsFromSelections();
    saveDraft();
};


const removeDocument = (key) => {
    documentSelections.value[key] = null;
    documentSelectionErrors.value[key] = '';
    syncDocumentsFromSelections();
    saveDraft();
};


const saveDraft = () => {
    const payload = {
        ...form.value,
        documents: [],
        activeStep: activeStep.value,
    };


    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
};


const hydrateForm = () => {
    const beneficiary = page.props.beneficiary;


    if (! beneficiary) {
        return;
    }


    form.value = {
        ...defaultForm(),
        username: page.props.user?.name || '',
        first_name: beneficiary.first_name || '',
        middle_name: beneficiary.middle_name || '',
        last_name: beneficiary.last_name || '',
        suffix: beneficiary.suffix || '',
        birth_date: beneficiary.birth_date || beneficiary.birthdate || '',
        age: beneficiary.age || '',
        sex: beneficiary.sex || beneficiary.gender || '',
        civil_status: beneficiary.civil_status || '',
        place_of_birth: beneficiary.place_of_birth || '',
        citizenship: beneficiary.citizenship || 'Filipino',
        contact_number: normalizePhoneValue(beneficiary.contact_number || beneficiary.phone || ''),
        email: beneficiary.email || page.props.user?.email || '',
        facebook_account: beneficiary.facebook_account || '',
        // Use beneficiary's category if available, otherwise use preferred category
        category: beneficiary.category || beneficiary.beneficiary_type || getPreferredCategory(),
        present_address: beneficiary.present_address || '',
        barangay: beneficiary.barangay || '',
        city: beneficiary.city || 'San Fernando',
        province: beneficiary.province || 'Pampanga',
        father_name: beneficiary.father_name || '',
        father_contact: normalizePhoneValue(beneficiary.father_contact || ''),
        father_occupation: beneficiary.father_occupation || '',
        mother_name: beneficiary.mother_name || '',
        mother_contact: normalizePhoneValue(beneficiary.mother_contact || ''),
        mother_occupation: beneficiary.mother_occupation || '',
        family_income: beneficiary.family_income || '',
        school_name: beneficiary.school_name || '',
        school_address: beneficiary.school_address || '',
        education_level: beneficiary.education_level || '',
        school_year: beneficiary.school_year || '',
        year_level: beneficiary.year_level || '',
        course: beneficiary.course || '',
        last_school_attended: beneficiary.last_school_attended || '',
        highest_attainment: beneficiary.highest_attainment || '',
        year_last_attended: beneficiary.year_last_attended || '',
        parent_guardian_name: beneficiary.parent_guardian_name || '',
        relationship: beneficiary.relationship || '',
        displacement_reason: beneficiary.displacement_reason || '',
        former_employer: beneficiary.former_employer || '',
        displacement_date: beneficiary.displacement_date || '',
        previous_spes: beneficiary.previous_spes ? 'Yes' : 'No',
        spes_count: beneficiary.spes_count || '',
        skills: beneficiary.skills && Array.isArray(beneficiary.skills) 
            ? beneficiary.skills.map(skill => skill.id || skill)
            : [],
        declaration: false,
        documents: [],
    };

    // Hydrate existing documents from the beneficiary record
    const docs = Array.isArray(beneficiary.documents) ? beneficiary.documents : [];
    docs.forEach((doc) => {
        if (doc.type && doc.path) {
            existingDocuments.value[doc.type] = {
                path: doc.path,
                name: doc.name || doc.type,
                uploaded_at: doc.uploaded_at || null,
            };
        }
    });
};


const loadDraft = () => {
    const savedDraft = localStorage.getItem(STORAGE_KEY);


    if (! savedDraft) {
        return;
    }


    try {
        const parsed = JSON.parse(savedDraft);
        const savedStep = Number(parsed.activeStep ?? parsed.currentStep ?? 1);
        const preferredCategory = getPreferredCategory();
        const hasDraftProgress = [
            parsed.first_name,
            parsed.last_name,
            parsed.email,
            parsed.contact_number,
            parsed.present_address,
            parsed.barangay,
            parsed.city,
            parsed.family_income,
            parsed.school_name,
            parsed.last_school_attended,
            parsed.parent_guardian_name,
            parsed.documents,
        ].some((value) => Array.isArray(value) ? value.length > 0 : value !== undefined && value !== null && value !== '');


        form.value = {
            ...defaultForm(),
            ...parsed,
            // Always use the registered category from backend, not the saved draft
            // This ensures OSY/Dependent users see the correct fields
            category: preferredCategory,
            // City and Province are always fixed
            city: 'San Fernando',
            province: 'Pampanga',
            contact_number: normalizePhoneValue(parsed.contact_number || ''),
            father_contact: normalizePhoneValue(parsed.father_contact || ''),
            mother_contact: normalizePhoneValue(parsed.mother_contact || ''),
            skills: Array.isArray(parsed.skills) ? parsed.skills : [],
            documents: [],
        };

        // If the saved draft had a different category, reset category-specific fields
        if (parsed.category && parsed.category !== preferredCategory) {
            resetCategorySpecificFields();
        }

        activeStep.value = Number.isFinite(savedStep)
            ? Math.max(1, Math.min(steps.length, savedStep))
            : 1;


        if (! hasDraftProgress) {
            activeStep.value = 1;
        }


        if (activeStep.value === steps.length && parsed.declaration !== true) {
            activeStep.value = steps.length - 1;
        }
    } catch (error) {
        localStorage.removeItem(STORAGE_KEY);
    }
};


const resetCategorySpecificFields = () => {
    form.value.school_name = '';
    form.value.school_address = '';
    form.value.education_level = '';
    form.value.school_year = '';
    form.value.year_level = '';
    form.value.course = '';
    form.value.last_school_attended = '';
    form.value.highest_attainment = '';
    form.value.year_last_attended = '';
    form.value.parent_guardian_name = '';
    form.value.relationship = '';
    form.value.displacement_reason = '';
    form.value.former_employer = '';
    form.value.displacement_date = '';
    
    // Reset all document selections when category changes
    documentSelections.value = {
        valid_id: null,
        school_enrollment: null,
        birth_certificate: null,
        osy_certificate: null,
        displacement_proof: null,
        parent_valid_id: null,
        barangay_certificate: null,
    };
};


const handleCategoryChange = () => {
    resetCategorySpecificFields();
    saveDraft();
};


const isStepCompleted = (stepNumber) => stepNumber < activeStep.value;
const isStepActive = (stepNumber) => stepNumber === activeStep.value;


const getStepClasses = (stepNumber) => {
    if (isStepActive(stepNumber)) {
        return 'border-sky-500 bg-sky-50 shadow-sm';
    }


    if (isStepCompleted(stepNumber)) {
        return 'border-emerald-200 bg-emerald-50';
    }


    return 'border-slate-200 bg-white';
};


const getStepBadgeClasses = (stepNumber) => {
    if (isStepActive(stepNumber)) {
        return 'bg-sky-600 text-white';
    }


    if (isStepCompleted(stepNumber)) {
        return 'bg-emerald-600 text-white';
    }


    return 'bg-slate-200 text-slate-600';
};


const setActiveStep = (stepNumber) => {
    const boundedStep = Math.max(1, Math.min(steps.length, stepNumber));
    activeStep.value = boundedStep;
    validationErrors.value = {};
    saveDraft();
};


const validateAge = () => {
    ageValidationError.value = '';
    const age = parseInt(form.value.age);
   
    if (!age || isNaN(age)) {
        return;
    }
   
    if (age > 30) {
        ageValidationError.value = 'You are not eligible for SPES. This program is only for beneficiaries aged 15-30 years old.';
    }
};


const openTermsModal = (event) => {
    event?.preventDefault();
    showTermsModal.value = true;
};


const closeTermsModal = () => {
    showTermsModal.value = false;
};


const validateStep = (step) => {
    validationErrors.value = {};


    if (step === 1) {
        const requiredStep1 = {
            first_name: 'First Name',
            last_name: 'Last Name',
            birth_date: 'Birth Date',
            age: 'Age',
            sex: 'Sex',
            civil_status: 'Civil Status',
            place_of_birth: 'Place of Birth',
            citizenship: 'Citizenship',
            contact_number: 'Contact Number',
            email: 'Email Address',
        };
        Object.entries(requiredStep1).forEach(([field, label]) => {
            if (! form.value[field]) {
                validationErrors.value[field] = `${label} is required.`;
            }
        });


        // Age verification
        const age = parseInt(form.value.age);
        if (age > 30) {
            validationErrors.value.age = 'You are not eligible for SPES. This program is only for beneficiaries aged 15-30 years old.';
        }
        if (age < 15) {
            validationErrors.value.age = 'You must be at least 15 years old to apply for SPES.';
        }

        // Contact number validation (must be exactly 10 digits after +63)
        const phoneDigits = getLocalPhoneDigits(form.value.contact_number);
        if (phoneDigits.length > 0 && phoneDigits.length !== 10) {
            validationErrors.value.contact_number = 'Contact number must be exactly 10 digits (e.g., 9123456789).';
        }
    }


    if (step === 2) {
        const requiredStep2 = {
            present_address: 'Present Address',
            barangay: 'Barangay',
            family_income: 'Family Income',
        };
        Object.entries(requiredStep2).forEach(([field, label]) => {
            if (! form.value[field]) {
                validationErrors.value[field] = `${label} is required.`;
            }
        });
    }


    if (step === 3) {
        // Only validate fields for the selected category
        if (form.value.category === 'student') {
            const requiredStudent = {
                school_name: 'School Name',
                school_address: 'School Address',
                education_level: 'Education Level',
                school_year: 'School Year',
                year_level: 'Year Level',
            };
            Object.entries(requiredStudent).forEach(([field, label]) => {
                if (!form.value[field]) {
                    validationErrors.value[field] = `${label} is required.`;
                }
            });
        } else if (form.value.category === 'osy') {
            const requiredOsy = {
                last_school_attended: 'Last School Attended',
                highest_attainment: 'Highest Attainment',
                year_last_attended: 'Year Last Attended',
            };
            Object.entries(requiredOsy).forEach(([field, label]) => {
                if (!form.value[field]) {
                    validationErrors.value[field] = `${label} is required.`;
                }
            });
        } else if (form.value.category === 'dependent') {
            const requiredDependent = {
                parent_guardian_name: 'Parent / Guardian Name',
                relationship: 'Relationship',
                displacement_reason: 'Reason for Displacement',
            };
            Object.entries(requiredDependent).forEach(([field, label]) => {
                if (!form.value[field]) {
                    validationErrors.value[field] = `${label} is required.`;
                }
            });
        }
    }


    if (step === 4) {
        const missingDocs = [];
        requiredDocumentsForCategory.value.forEach((doc) => {
            // A document is satisfied if the user uploaded a new one OR it already exists from a previous submission
            if (!documentSelections.value[doc.key] && !existingDocuments.value[doc.key]) {
                missingDocs.push(doc.label);
            }
        });
        if (missingDocs.length > 0) {
            validationErrors.value.documents = `Please upload the following: ${missingDocs.join(', ')}`;
        }
    }


    if (step === 5) {
        if (! form.value.declaration) {
            validationErrors.value.declaration = 'Please confirm the declaration before submitting.';
        }
    }


    return Object.keys(validationErrors.value).length === 0;
};


const nextStep = () => {
    if (! validateStep(activeStep.value)) {
        return;
    }


    if (activeStep.value < steps.length) {
        activeStep.value += 1;
        saveDraft();
    }
};


const previousStep = () => {
    if (activeStep.value > 1) {
        activeStep.value -= 1;
        saveDraft();
    }
};


const submitForm = () => {
    syncDocumentsFromSelections();


    if (! validateStep(5)) {
        return;
    }


    submitting.value = true;
    validationErrors.value = {};


    // Build the complete submission object with all fields and files
    const submissionData = {
        // Basic Information
        first_name: form.value.first_name,
        middle_name: form.value.middle_name || '',
        last_name: form.value.last_name,
        suffix: form.value.suffix || '',
        birth_date: form.value.birth_date,
        age: form.value.age,
        sex: form.value.sex,
        civil_status: form.value.civil_status,
        place_of_birth: form.value.place_of_birth,
        citizenship: form.value.citizenship,
        contact_number: form.value.contact_number,
        email: form.value.email,
        facebook_account: form.value.facebook_account || '',
        category: form.value.category,
        skills: form.value.skills || [],
        
        // Family / Address
        present_address: form.value.present_address,
        barangay: form.value.barangay,
        city: form.value.city,
        province: form.value.province,
        father_name: form.value.father_name || '',
        father_contact: form.value.father_contact || '',
        father_occupation: form.value.father_occupation || '',
        mother_name: form.value.mother_name || '',
        mother_contact: form.value.mother_contact || '',
        mother_occupation: form.value.mother_occupation || '',
        family_income: form.value.family_income,
        
        // Previous SPES
        previous_spes: form.value.previous_spes || 'No',
        spes_count: form.value.previous_spes === 'Yes' ? form.value.spes_count || '' : '',
        
        // Declaration
        declaration: form.value.declaration ? 'on' : '',
    };

    // Document files — attach all uploaded documents based on category
    requiredDocumentsForCategory.value.forEach((doc) => {
        if (documentSelections.value[doc.key]) {
            submissionData[doc.key] = documentSelections.value[doc.key];
        }
    });
    
    // Add category-specific fields
    if (form.value.category === 'student') {
        submissionData.school_name = form.value.school_name || '';
        submissionData.school_address = form.value.school_address || '';
        submissionData.education_level = form.value.education_level || '';
        submissionData.school_year = form.value.school_year || '';
        submissionData.year_level = form.value.year_level || '';
        submissionData.course = form.value.course || '';
    } else if (form.value.category === 'osy') {
        submissionData.last_school_attended = form.value.last_school_attended || '';
        submissionData.highest_attainment = form.value.highest_attainment || '';
        submissionData.year_last_attended = form.value.year_last_attended || '';
    } else if (form.value.category === 'dependent') {
        submissionData.parent_guardian_name = form.value.parent_guardian_name || '';
        submissionData.relationship = form.value.relationship || '';
        submissionData.displacement_reason = form.value.displacement_reason || '';
        submissionData.former_employer = form.value.former_employer || '';
        submissionData.displacement_date = form.value.displacement_date || '';
    }


    router.post(route('onboarding.submit'), submissionData, {
        onStart: () => {
            submitting.value = true;
            toast.info('Submitting application...', {
                autoClose: 1500,
            });
        },


        onSuccess: () => {
            localStorage.removeItem(STORAGE_KEY);
            toast.success(
                'SPES application submitted successfully!',
                {
                    autoClose: 3000,
                }
            );


            setTimeout(() => {
                window.location.href = route('dashboard');
            }, 2000);
        },


        onError: (errors) => {
            validationErrors.value = errors;
            
            // Log the errors for debugging
            console.error('Submission errors:', errors);


            toast.error(
                'Submission failed. Please check required fields.'
            );
        },


        onFinish: () => {
            submitting.value = false;
        },
    });
};


watch(form, saveDraft, { deep: true });


onMounted(() => {
    hydrateForm();
    loadDraft();
    
    // URL ?step parameter takes priority over localStorage draft step
    const urlParams = new URLSearchParams(window.location.search);
    const stepParam = parseInt(urlParams.get('step'));
    if (stepParam && stepParam >= 1 && stepParam <= steps.length) {
        activeStep.value = stepParam;
    }

    loadSkills();
    loadAvailableJobs();
});
</script>

