<?php

namespace App\Enum;

enum Visibility: string
{
    case PUBLIC = 'PUBLIC';
    case CANDIDAT = 'CANDIDAT';
    case RECRUITER = 'RECRUITER';
}
