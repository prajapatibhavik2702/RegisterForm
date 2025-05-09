<?php

namespace App\Http\Controllers;

abstract class Controller
{
    $fourthHighestSalary = DB::table('salaries')
    ->select('amount')
    ->distinct() // Ensure only unique salary amounts are considered
    ->orderByDesc('amount') // Highest first
    ->skip(3) // Skip top 3 unique salaries
    ->take(1) // Take the 4th unique salary
    ->first();
}
