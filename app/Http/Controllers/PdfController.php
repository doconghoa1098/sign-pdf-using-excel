<?php

namespace App\Http\Controllers;

class PdfController extends Controller
{
    function index()
    {
        return view('pdf-preview');
    }
}
