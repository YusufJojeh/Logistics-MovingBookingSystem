<?php
// Simple multilanguage support
$langs = [
    'en' => [
        'welcome' => 'Welcome to Logistics & Moving Booking System',
        'login' => 'Login',
        'register' => 'Register',
        'services' => 'Services',
        'contact' => 'Contact Us',
        'logout' => 'Logout',
    ],
    'ar' => [
        'welcome' => 'مرحبًا بكم في منصة حجز خدمات النقل والنقل اللوجستي',
        'login' => 'تسجيل الدخول',
        'register' => 'إنشاء حساب',
        'services' => 'الخدمات',
        'contact' => 'اتصل بنا',
        'logout' => 'تسجيل الخروج',
    ]
];

function __($key) {
    global $langs;
    $lang = $_SESSION['lang'] ?? 'en';
    return $langs[$lang][$key] ?? $key;
} 