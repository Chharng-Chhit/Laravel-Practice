<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $name = $request->name;
        $email = $request->input('email');

        return ['Data inserted'];
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $name = $request->name;
        // $email = $request->input('email');

        // $all = $request->all();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'age' => 'nullable|integer|min:1',
        ]);


        return [
            "name" => $validated["name"],
            "email" => $validated["email"],
            "all" => $validated['age']
        ];

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return "Student Controler show()";
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return "Student Controler update()";
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return "Student Controler destroy()";
    }
}
