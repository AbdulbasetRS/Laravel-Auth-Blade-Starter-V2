<?php
return [
    'overview' => 'نظرة عامة',
    'overview_body' => 'هذا المشروع نظام إداري (Admin Panel) مبني على Laravel 12، بواجهة أمامية وأخرى للإدارة منفصلتين تمامًا، مع دعم كامل للغتين العربية والإنجليزية (RTL/LTR).',
    'stack' => 'الـ Tech Stack',
    'no_vite' => 'بدون Vite',
    'when_needed' => 'عند الحاجة',
    'structure' => 'بنية المجلدات',
    'structure_body' => 'Controllers/Frontend و Controllers/Admin منفصلان، Repositories/Contracts لكل Data Access، Blade Components لكل UI متكرر.',
    'design_system' => 'Design System',
    'design_system_body' => 'كل Dropdown في النظام يستخدم <code>&lt;x-dropdown&gt;</code> الموحّد. كل Input يستخدم <code>&lt;x-form.field&gt;</code> لعرض الأخطاء (Icon + Popover بدل نص ثابت). الخط الإنجليزي هو <code>Manrope</code>، والعربي <code>Tajawal / Cairo</code>.',
    'localization' => 'Localization',
    'localization_body' => 'يعتمد المشروع على mcamara/laravel-localization بالكامل — لا يوجد نظام ترجمة موازٍ. تبديل اللغة = Navigation حقيقي لرابط مُعرَّب.',
    'validation' => 'Validation',
    'validation_body' => 'لا نصوص Validation ثابتة أسفل الحقول. الخطأ يظهر كـ Border أحمر + أيقونة، والرسالة تظهر عند Hover أو Click أو Tap فقط.',
    'living_doc_note' => 'ملاحظة: هذه الصفحة "حيّة" — يجب تحديثها يدويًا كلما أُضيفت Feature جديدة للمشروع.',
];
