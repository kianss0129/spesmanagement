# Beneficiary Verification Page - Visual Reference

## Page Layout Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                         BENEFICIARY VERIFICATION PAGE               │
│                                                                      │
│  Onboarding Verification - John Student                             │
│  Review beneficiary onboarding submissions                          │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ 🟨 PENDING REVIEW                                            │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ Beneficiary Information                                      │  │
│  ├──────────────────────────────────────────────────────────────┤  │
│  │                                                              │  │
│  │ ┌────────────────────────────┬────────────────────────────┐ │  │
│  │ │ Full Name                  │ Email Address              │ │  │
│  │ │ John Student               │ john@test.com              │ │  │
│  │ ├────────────────────────────┼────────────────────────────┤ │  │
│  │ │ Phone Number               │ Submission Date            │ │  │
│  │ │ +1 555-1234                │ Feb 1, 2026 at 10:30 AM    │ │  │
│  │ └────────────────────────────┴────────────────────────────┘ │  │
│  │                                                              │  │
│  │ ┌────────────────────────────┐                             │  │
│  │ │ School                     │                             │  │
│  │ │ Test High School           │                             │  │
│  │ └────────────────────────────┘                             │  │
│  │                                                              │  │
│  │ [For OSY: Shows Skills/Training]                            │  │
│  │ [For Dependent: Shows Parent Name & Reason]                │  │
│  │                                                              │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ Submitted Documents                                          │  │
│  ├──────────────────────────────────────────────────────────────┤  │
│  │                                                              │  │
│  │ ┌────────────────────────────────────────┬──────────────┐  │  │
│  │ │ 📄 Final Report                        │ [View]       │  │  │
│  │ │ Uploaded: Feb 1, 2026 at 9:00 AM      │ Download     │  │  │
│  │ └────────────────────────────────────────┴──────────────┘  │  │
│  │                                                              │  │
│  │ ┌────────────────────────────────────────┬──────────────┐  │  │
│  │ │ 📄 Form 1005                           │ [View]       │  │  │
│  │ │ Uploaded: Feb 1, 2026 at 8:30 AM      │ Download     │  │  │
│  │ └────────────────────────────────────────┴──────────────┘  │  │
│  │                                                              │  │
│  │ ┌────────────────────────────────────────┬──────────────┐  │  │
│  │ │ 📄 Deleted Certificate                 │              │  │  │
│  │ │ ❌ File missing or deleted             │ Unavailable  │  │  │
│  │ └────────────────────────────────────────┴──────────────┘  │  │
│  │                                                              │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │ Verification Actions                                         │  │
│  ├──────────────────────────────────────────────────────────────┤  │
│  │                                                              │  │
│  │ ┌──────────────────────────────────────────────────────────┐ │  │
│  │ │ ✓ Approve Beneficiary Onboarding                  [BTN] │ │  │
│  │ └──────────────────────────────────────────────────────────┘ │  │
│  │                                                              │  │
│  │ ─────────────────────────────────────────────────────────   │  │
│  │                                                              │  │
│  │ Rejection Reason (Required if rejecting)                   │  │
│  │                                                              │  │
│  │ ┌──────────────────────────────────────────────────────────┐ │  │
│  │ │ [textarea: Enter reason for rejection...]               │ │  │
│  │ │                                                          │ │  │
│  │ │                                                          │ │  │
│  │ └──────────────────────────────────────────────────────────┘ │  │
│  │                                                              │  │
│  │ ┌──────────────────────────────────────────────────────────┐ │  │
│  │ │ ✗ Reject Onboarding                              [BTN] │ │  │
│  │ └──────────────────────────────────────────────────────────┘ │  │
│  │                                                              │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

## Status Banner Variations

### Pending Review (Yellow)
```
┌──────────────────────────────────────────────────────────────┐
│ 🟨 PENDING REVIEW                                            │
└──────────────────────────────────────────────────────────────┘
```

### Approved (Green)
```
┌──────────────────────────────────────────────────────────────┐
│ 🟩 Approved                                                  │
│                                                              │
│ This beneficiary has been approved                          │
│ Approved on: February 1, 2026 at 10:30 AM                 │
│                                                              │
│ [No action buttons available]                               │
└──────────────────────────────────────────────────────────────┘
```

### Rejected (Red)
```
┌──────────────────────────────────────────────────────────────┐
│ 🟥 This beneficiary has been rejected                        │
│                                                              │
│ Reason: Documents incomplete and missing signatures         │
│ Rejected on: February 1, 2026 at 11:00 AM                 │
│                                                              │
│ [No action buttons available]                               │
└──────────────────────────────────────────────────────────────┘
```

## Conditional Fields by Type

### Student Type
```
┌─────────────────────────────────────────────────────────────┐
│ School                                                      │
│ Test High School                                            │
└─────────────────────────────────────────────────────────────┘
```

### OSY (Out-of-School Youth) Type
```
┌─────────────────────────────────────────────────────────────┐
│ Skills / Training                                           │
│ Carpentry and Basic Welding                                 │
└─────────────────────────────────────────────────────────────┘
```

### Dependent / Displaced Worker Type
```
┌──────────────────────────────────┬──────────────────────────┐
│ Parent / Guardian Name           │ Displacement Reason      │
├──────────────────────────────────┼──────────────────────────┤
│ Jane Parent                       │ Economic hardship        │
└──────────────────────────────────┴──────────────────────────┘
```

## Documents Display States

### File Exists
```
┌────────────────────────────────────────────────────────────┐
│ 📄 Final Report                                     [View] │
│ Uploaded: Feb 1, 2026 at 10:30 AM                        │
└────────────────────────────────────────────────────────────┘
```

### File Missing
```
┌────────────────────────────────────────────────────────────┐
│ 📄 Certificate                              [Unavailable] │
│ ❌ File missing or deleted                                │
└────────────────────────────────────────────────────────────┘
```

### No Documents
```
┌────────────────────────────────────────────────────────────┐
│                                                            │
│                No documents submitted                      │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

## Form Elements

### Approve Button
```
┌──────────────────────────────────────────────────────────┐
│ ✓ Approve Beneficiary Onboarding                   [BTN] │
│                                                          │
│ Green button (bg-green-600, hover:bg-green-700)         │
│ Full width, 48px tall, 1.125rem font                    │
│ Triggers confirmation dialog                            │
└──────────────────────────────────────────────────────────┘
```

### Reject Textarea & Button
```
Rejection Reason (Required if rejecting)

┌──────────────────────────────────────────────────────────┐
│ [Enter reason for rejection...]                         │
│                                                          │
│ (4 rows high, border-gray-300, focus ring-red-500)      │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│ ✗ Reject Onboarding                              [BTN] │
│                                                          │
│ Red button (bg-red-600, hover:bg-red-700)               │
│ Disabled if textarea empty (bg-gray-400)                │
│ Triggers confirmation dialog                            │
└──────────────────────────────────────────────────────────┘
```

## Responsive Breakpoints

### Mobile (< 768px)
```
┌────────────────────┐
│ Full Name          │
│ John Student       │
└────────────────────┘

┌────────────────────┐
│ Email Address      │
│ john@test.com      │
└────────────────────┘

┌────────────────────┐
│ Phone Number       │
│ +1 555-1234        │
└────────────────────┘

┌────────────────────┐
│ Submission Date    │
│ Feb 1, 2026 10:30  │
└────────────────────┘

[Single column, full width, 16px padding]
```

### Tablet & Desktop (≥ 768px)
```
┌──────────────────────────┬──────────────────────────┐
│ Full Name                │ Email Address            │
│ John Student             │ john@test.com            │
├──────────────────────────┼──────────────────────────┤
│ Phone Number             │ Submission Date          │
│ +1 555-1234              │ Feb 1, 2026 at 10:30 AM  │
└──────────────────────────┴──────────────────────────┘

[2-column grid, 24px gap, 32px padding]
```

## Color Palette

### Status Colors
| Status | Background | Border | Text | Badge |
|--------|-----------|--------|------|-------|
| Pending | `bg-yellow-50` | `border-yellow-200` | `text-yellow-800` | `bg-yellow-100` |
| Approved | `bg-green-50` | `border-green-200` | `text-green-800` | `bg-green-100` |
| Rejected | `bg-red-50` | `border-red-200` | `text-red-800` | `bg-red-100` |

### Button Colors
| Action | Normal | Hover |
|--------|--------|-------|
| Approve | `bg-green-600` | `bg-green-700` |
| Reject | `bg-red-600` | `bg-red-700` |
| View | `bg-blue-600` | `bg-blue-700` |
| Disabled | `bg-gray-400` | (no hover) |

### Text Colors
| Type | Color |
|------|-------|
| Title | `text-gray-900` |
| Label | `text-gray-700` |
| Body | `text-gray-900` |
| Meta | `text-gray-600` |
| Placeholder | `text-gray-500` |

## Typography

### Sizes
| Element | Size | Weight |
|---------|------|--------|
| Page Title | `text-4xl` | `font-bold` |
| Section Title | `text-2xl` | `font-bold` |
| Label | `text-sm` | `font-semibold` |
| Body | `text-lg` | `font-normal` |
| Meta | `text-sm` | `font-normal` |
| Button | `text-lg` | `font-semibold` |

## Spacing

### Margins
| Element | Margin |
|---------|--------|
| Page Title | `mb-2` |
| Subtitle | `mb-8` |
| Status Banner | `mb-6` |
| Section | `mb-6` |
| Field Row | `gap-6` |
| Document Item | `space-y-4` |
| Action Buttons | `space-y-6` |

### Padding
| Element | Padding |
|---------|---------|
| Container | `py-8 px-4` |
| Card | `p-8` |
| Status Banner | `p-4` |
| Document Item | `p-4` |
| Textarea | `px-4 py-3` |

## Interactive States

### Button States
```
Normal:
[Button Text] - Clickable, hover color changes

Hover:
[Button Text] - Darker shade, cursor pointer

Disabled:
[Button Text] - Gray background, cursor not-allowed

Focus:
[Button Text] - Ring-2 visible around button
```

### Form Input States
```
Empty:
[Empty textarea] - Border gray-300, placeholder visible

Focused:
[Focused textarea] - Outline none, ring-2 ring-red-500

Filled:
[Text in textarea] - Border gray-300, text visible

Disabled (Reject button without reason):
[Disabled button] - Background gray-400, cursor not-allowed
```

## Animations & Transitions

### Button Hover
```
Approve: bg-green-600 → bg-green-700 (200ms)
Reject: bg-red-600 → bg-red-700 (200ms)
View: bg-blue-600 → bg-blue-700 (200ms)
```

### Document Item
```
Hover: background transparent → bg-gray-50 (smooth transition)
```

### Focus States
```
Focus Ring: 2px solid ring-red-500 (textareas)
```

---

## Example Screenshots Text

### Pending Review Page
```
John Student - Pending Review

Name: John Student          | Email: john@test.com
Phone: +1 555-1234         | Submitted: Feb 1, 10:30 AM
School: Test High School   

Documents:
  • Final Report (View)
  • Form 1005 (View)
  
[✓ Approve Beneficiary Onboarding]

Rejection Reason: [textarea]
[✗ Reject Onboarding]
```

### Approved Status Page
```
Jane Employer - Approved

Name: Jane Employer         | Email: jane@test.com
Phone: +1 555-5678         | Submitted: Feb 1, 10:30 AM

Company: ABC Services      | Status: ✓ APPROVED
                            | Date: Feb 1, 2026 at 10:45 AM

Documents:
  • DTI Certificate (View)
  • BIR Certificate (View)

[Status locked - No further action available]
```

### Rejected Status Page
```
Bob Worker - Rejected

Name: Bob Worker            | Email: bob@test.com
Phone: +1 555-9876         | Submitted: Feb 1, 10:30 AM

Parent/Guardian: John Worker | ✗ REJECTED
Reason: Economic hardship    | Date: Feb 1, 2026 at 11:00 AM

Documents:
  • Identification (View)
  • ❌ Declaration (File missing)

Rejection Reason:
"Documents are incomplete and missing required certifications from employer."

[Status locked - No further action available]
```

---

## Accessibility Features

- ✅ Proper semantic HTML
- ✅ Form labels with "for" attributes
- ✅ CSRF token in all forms
- ✅ Color not sole indicator (✓✗ text + colors)
- ✅ Focus visible states on buttons
- ✅ Proper button types (type="submit", type="button")
- ✅ Confirmation dialogs for destructive actions
- ✅ Readable font sizes
- ✅ Sufficient color contrast
- ✅ Responsive touch targets (44px minimum)

---

This completes the visual reference for the beneficiary verification page!
