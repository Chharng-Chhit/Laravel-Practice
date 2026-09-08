<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $department = Department::all();
        return $department;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name'      => 'required|string|max:255',
            'school'    => 'required|string|max:255'
        ]);

        // $department = Department::create($validate);
        $department = Department::create([
            'name'  => $validate['name'],
            'school'=> $validate['school']
        ]);

        return $department;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validate = $request->validate([
            'name'      => 'required|string|max:255',
            'school'    => 'required|string|max:255'
        ]);

        $department = Department::findOrFail($id);
        $department->update($validate);
        return $department;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $department = Department::findOrFail($id)->delete();
    }
}
