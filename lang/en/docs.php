<?php
return [
    'overview' => 'Overview',
    'overview_body' => 'This project is an Admin Panel built on Laravel 12, with fully separate Frontend and Admin interfaces, and full Arabic/English (RTL/LTR) support.',
    'stack' => 'Tech Stack',
    'no_vite' => 'no Vite',
    'when_needed' => 'when needed',
    'structure' => 'Folder Structure',
    'structure_body' => 'Controllers/Frontend and Controllers/Admin are fully separate, Repositories/Contracts for every Data Access, Blade Components for every recurring UI piece.',
    'design_system' => 'Design System',
    'design_system_body' => 'Every dropdown in the system uses the unified <code>&lt;x-dropdown&gt;</code>. Every input uses <code>&lt;x-form.field&gt;</code> to show errors (icon + popover instead of static text). English font is <code>Manrope</code>, Arabic is <code>Tajawal / Cairo</code>.',
    'localization' => 'Localization',
    'localization_body' => 'The project relies entirely on mcamara/laravel-localization — no parallel translation system. Switching language is real navigation to a localized URL.',
    'validation' => 'Validation',
    'validation_body' => 'No static validation text under fields. Errors show as a red border + icon, and the message only appears on hover, click, or tap.',
    'living_doc_note' => 'Note: this page is "living" — it must be updated manually every time a new feature is added to the project.',
];
