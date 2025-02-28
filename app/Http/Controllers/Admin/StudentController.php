<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;
use App\Imports\StudentsImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //get students
        $students = Student::when(request()->q, function($students) {
            $students = $students->where('name', 'like', '%'. request()->q . '%');
        })->with('classroom')->latest()->paginate(5);

        //append query string to pagination links
        $students->appends(['q' => request()->q]);

        //render with inertia
        return inertia('Admin/Students/Index', [
            'students' => $students,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //get classrooms
        $classrooms = Classroom::all();

        //render with inertia
        return inertia('Admin/Students/Create', [
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validate request
        $request->validate([
            'name'          => 'required|string|max:255',
            'nisn'          => 'required|unique:students',
            'gender'        => 'required|string',
            'password'      => 'required|confirmed',
            'classroom_id'  => 'required'
        ]);

        //create student
        Student::create([
            'name'          => $request->name,
            'nisn'          => $request->nisn,
            'gender'        => $request->gender,
            'password'      => $request->password,
            'classroom_id'  => $request->classroom_id
        ]);

        //redirect
        return redirect()->route('admin.students.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //get student
        $student = Student::findOrFail($id);

        //get classrooms
        $classrooms = Classroom::all();

        //render with inertia
        return inertia('Admin/Students/Edit', [
            'student' => $student,
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        //validate request
        $request->validate([
            'name'          => 'required|string|max:255',
            'nisn'          => 'required|unique:students,nisn,'.$student->id,
            'gender'        => 'required|string',
            'classroom_id'  => 'required',
            'password'      => 'confirmed'
        ]);

        //check passwordy
        if($request->password == "") {

            //update student without password
            $student->update([
                'name'          => $request->name,
                'nisn'          => $request->nisn,
                'gender'        => $request->gender,
                'classroom_id'  => $request->classroom_id
            ]);

        } else {

            //update student with password
            $student->update([
                'name'          => $request->name,
                'nisn'          => $request->nisn,
                'gender'        => $request->gender,
                'password'      => $request->password,
                'classroom_id'  => $request->classroom_id
            ]);

        }

        //redirect
        return redirect()->route('admin.students.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //get student
        $student = Student::findOrFail($id);

        //delete student
        $student->delete();

        //redirect
        return redirect()->route('admin.students.index');
    }

    public function import()
    {
        return inertia('Admin/Students/Import');
    }
    
    /**
     * storeImport
     *
     * @param  mixed $request
     * @return void
     */
    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        // import data
        Excel::import(new StudentsImport(), $request->file('file'));

        //redirect
        return redirect()->route('admin.students.index');
    }
}
