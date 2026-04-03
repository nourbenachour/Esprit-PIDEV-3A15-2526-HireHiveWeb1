<?php

namespace App\Enum;

enum TypePost: string
{
    case IMAGE = 'IMAGE';
    case TEXT = 'TEXT';
    case VIDEO = 'VIDEO';
    case ARTICLE = 'ARTICLE';
}
