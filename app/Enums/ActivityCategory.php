<?php

namespace App\Enums;

enum ActivityCategory: string
{
    case Tutoring = 'tutoring';
    case Extracurricular = 'extracurricular';
    case Mentorship = 'mentorship';
    case Other = 'other';
}
