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



  public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120', // max 5MB
        ]);

        $file = $request->file('file');

        // Generate unique name
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

        // Move to public/profile
        $file->move(public_path('profile'), $fileName);

        // Generate URL
        $url = url('profile/' . $fileName);

        // You can now store $url in DB as needed
        return response()->json([
            'message' => 'File uploaded successfully.',
            'url' => $url,
        ]);
    }





<form id="uploadForm" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>

<script>
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: '/api/upload',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert('Uploaded URL: ' + response.url);
            }
        });
    });
</script>

